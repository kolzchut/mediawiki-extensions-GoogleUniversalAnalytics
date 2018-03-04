<?php

class GoogleUniversalAnalyticsHooks {
	private static $normalCats = [];
	private static $ignoredPageGroupingNamespaces = [
		NS_CATEGORY, NS_FILE, NS_SPECIAL, NS_MEDIAWIKI
	];

	 // We don't want to log hidden categories,
	 // this is the only place where that distinction is available
	public static function onOutputPageMakeCategoryLinks( OutputPage &$out, $categories, &$links ) {
		self::$normalCats = array_keys( $categories, 'normal' );

		return true;
	}

	public static function onBeforePageDisplay( OutputPage &$out, Skin &$skin ) {
		global $wgGoogleUniversalAnalyticsRiveted, $wgGoogleUniversalAnalyticsScrollDepth;

		$out->addHeadItem( 'GoogleUniversalAnalyticsIntegration', self::addGoogleAnalytics( $out ) );

		if ( $wgGoogleUniversalAnalyticsScrollDepth === true ) {
			$out->addModules( 'ext.googleUniversalAnalytics.scrolldepth.init' );
		}
		if ( $wgGoogleUniversalAnalyticsRiveted === true ) {
			$out->addModules( 'ext.googleUniversalAnalytics.riveted' );
		}

		return true;
	}

	public static function onResourceLoaderGetConfigVars( &$vars ) {
		global $wgGoogleUniversalAnalyticsRiveted, $wgGoogleUniversalAnalyticsRivetedConfig,
		       $wgGoogleUniversalAnalyticsScrollDepth, $wgGoogleUniversalAnalyticsScrollDepthConfig;

		if ( $wgGoogleUniversalAnalyticsScrollDepth === true ) {
			$vars['wgGoogleUniversalAnalyticsScrollDepthConfig'] = $wgGoogleUniversalAnalyticsScrollDepthConfig;
		}
		if ( $wgGoogleUniversalAnalyticsRiveted === true ) {
			$vars['wgGoogleUniversalAnalyticsRivetedConfig'] = $wgGoogleUniversalAnalyticsRivetedConfig;
		}

		return true;
	}

	public static function addGoogleAnalytics( OutputPage &$out ) {
		global $wgGoogleUniversalAnalyticsAccount,
				$wgGoogleUniversalAnalyticsAnonymizeIP,
				$wgGoogleUniversalAnalyticsTrackExtLinks,
				$wgGoogleUniversalAnalyticsSegmentByGroup,
				$wgGoogleUniversalAnalyticsSegmentByGroupDimension,
				$wgGoogleUniversalAnalyticsCookiePath,
				$wgGoogleUniversalAnalyticsDomainName,
				$wgGoogleUniversalAnalyticsPageGrouping,
				$wgGoogleUniversalAnalyticsEnahncedLinkAttribution,
				$wgGoogleUniversalAnalyticsRemarketing;


		if ( is_null( $wgGoogleUniversalAnalyticsAccount ) ) {
			$msg = self::messageToComment( 'googleuniversalanalytics-error-not-configured' );

			return $msg;
		}

		if ( $out->getUser()->isAllowed( 'noanalytics' ) ) {
			return self::messageToComment( 'googleuniversalanalytics-disabled-for-user' );
		}

		if ( self::isIgnoredPage( $out->getTitle() ) ) {
			return self::messageToComment( 'googleuniversalanalytics-disabled-for-page' );
		}

		/* Else: we load the script */
		$script = '<!-- Begin Google Analytics -->' . PHP_EOL;
		$script .= '<script>' . PHP_EOL . self::getBasicSnippet();
		$extraCreateParams = '';

		if ( !empty( $wgGoogleUniversalAnalyticsCookiePath ) ) {
			$extraCreateParams = ", {'cookiePath': '{$wgGoogleUniversalAnalyticsCookiePath}'}";
		};

		$cookieDomain = $wgGoogleUniversalAnalyticsDomainName ?: 'auto';


		$script .= "ga('create', '{$wgGoogleUniversalAnalyticsAccount}', '{$cookieDomain}'" . $extraCreateParams . ");" . PHP_EOL;


		if( $wgGoogleUniversalAnalyticsSegmentByGroup === true && is_int( $wgGoogleUniversalAnalyticsSegmentByGroupDimension ) ) {
			// The following should be fine with caching, and simply always get "*" for anon users
			$userGroups = implode( ',', $out->getUser()->getEffectiveGroups() );
			$dimension = 'dimension' . $wgGoogleUniversalAnalyticsSegmentByGroupDimension;
			$script .="ga('set', '{$dimension}', '{$userGroups}');" . PHP_EOL;
		}

		if ( !empty( $wgGoogleUniversalAnalyticsPageGrouping ) ) {
			$title = $out->getTitle();
			$ns = $title->getNamespace();
			if ( isset( $ns ) && in_array( $ns, self::$ignoredPageGroupingNamespaces ) ) {
				$script .= PHP_EOL."/* Namespace excluded from page grouping */".PHP_EOL;
			} else {
				$normalCats = self::$normalCats;
				if ( count( $normalCats ) > 1 ) {
					$normalCats[0] = Title::makeTitleSafe( NS_CATEGORY, $normalCats[0] )->getText();
					$normalCats[1] = Title::makeTitleSafe( NS_CATEGORY, $normalCats[1] )->getText();
					$script .= "ga('set', 'contentGroup2', '{$normalCats[1]}');" . PHP_EOL
							. "ga('set', 'contentGroup3', '{$normalCats[0]}');" . PHP_EOL;
				};
			};
		};

		if ( $wgGoogleUniversalAnalyticsRemarketing === true ) {
			$script .= "ga('require', 'displayfeatures');" . PHP_EOL;
		}

		if ( $wgGoogleUniversalAnalyticsEnahncedLinkAttribution === true ) {
			$script .= "ga('require', 'linkid', 'linkid.js');" . PHP_EOL;
		}

		if ( $wgGoogleUniversalAnalyticsAnonymizeIP === true ) {
			$script .= "ga('set', 'anonymizeIp', true);" . PHP_EOL;
		};

		Hooks::run( 'GoogleAnalytics::SendPageView', [ &$out, &$script ] );

		// And finally... send the pageview
		$script .= "ga('send', 'pageview');" . PHP_EOL;

		// And end the script
		$script .= "</script>" . PHP_EOL;
		$script .= '<!-- End Google Analytics -->' . PHP_EOL;

		// Add module for tracking external links using events
		if ( $wgGoogleUniversalAnalyticsTrackExtLinks === true ) {
			$out->addModules( 'ext.googleUniversalAnalytics.externalLinks' );
		}

		return $script;
	}

	private static function getBasicSnippet() {
		$snippet = <<<SNIPPET
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
SNIPPET;

		return $snippet . PHP_EOL;
	}

	public static function onSkinAfterBottomScripts( Skin $skin, &$text = '' ) {
		global $wgGoogleUniversalAnalyticsOtherCode;

		if ( $wgGoogleUniversalAnalyticsOtherCode === null
			 || $skin->getUser()->isAllowed( 'noanalytics' )
		     || self::isIgnoredPage( $skin->getTitle() )
		) {
			return true;
		}

		$text .= $wgGoogleUniversalAnalyticsOtherCode . PHP_EOL;

		return true;
	}

	private static function isIgnoredPage( Title $title ) {
		global $wgGoogleUniversalAnalyticsIgnoreNsIDs,
		       $wgGoogleUniversalAnalyticsIgnorePages,
		       $wgGoogleUniversalAnalyticsIgnoreSpecials;

		$ignoreSpecials = count( array_filter( $wgGoogleUniversalAnalyticsIgnoreSpecials,
				function ( $v ) use ( $title ) {
					return $title->isSpecial( $v );
				} ) ) > 0;

		return (
			$ignoreSpecials
			|| in_array( $title->getNamespace(), $wgGoogleUniversalAnalyticsIgnoreNsIDs, true )
			|| in_array( $title->getPrefixedText(), $wgGoogleUniversalAnalyticsIgnorePages, true )
		);
	}

	protected static function messageToComment( $messageName = '' ) {
		if ( empty( $messageName ) ) {
			throw( new Exception( 'missing a message name!' ) );
		}

		return PHP_EOL . '<!-- ' . wfMessage( $messageName )->text() . ' -->' . PHP_EOL;

	}

	public static function onUnitTestsList( array &$files ) {
		// @codeCoverageIgnoreStart
		$directoryIterator = new RecursiveDirectoryIterator( __DIR__ . '/tests/' );

		/**
		 * @var SplFileInfo $fileInfo
		 */
		$ourFiles = [];
		foreach ( new RecursiveIteratorIterator( $directoryIterator ) as $fileInfo ) {
			if ( substr( $fileInfo->getFilename(), -8 ) === 'Test.php' ) {
				$ourFiles[] = $fileInfo->getPathname();
			}
		}

		$files = array_merge( $files, $ourFiles );
		return true;
		// @codeCoverageIgnoreEnd
	}
}
