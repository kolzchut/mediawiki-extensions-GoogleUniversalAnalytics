<?php

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

$wgExtensionCredits['other'][] = array(
	'path'           => __FILE__,
	'name'           => 'Google Analytics Integration for Kol-Zchut',
	'version'        => '3.2.4',
	'author'         => 'Tim Laqua, Dror S.',
	'descriptionmsg' => 'googleanalytics-desc',
	'url'            => 'https://www.mediawiki.org/wiki/Extension:Google_Analytics_Integration',
);

$wgMessagesDirs['googleAnalytics'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['googleAnalytics'] = dirname(__FILE__) . '/googleAnalytics.i18n.php';

$wgHooks['BeforePageDisplay'][]  = 'efGoogleAnalyticsHook';
//$wgHooks['SkinAfterBottomScripts'][]  = 'efGoogleAnalyticsHook';
$wgHooks['OutputPageMakeCategoryLinks'][] = 'onOutputPageMakeCategoryLinks'; // Get categories

$wgGroupPermissions['bot']['noanalytics'] = true;

/* Dror - New */
$wgGoogleAnalyticsAccount = null;
$wgGoogleAnalyticsDomainName = null;
$wgGoogleAnalyticsCookiePath = null;
$wgGoogleAnalyticsSegmentByGroup = false;
$wgGoogleAnalyticsTrackExtLinks = true;
$wgGoogleAnalyticsEnahncedLinkAttribution = false;
	//if you enable this, you must also select "Use enhanced link attribution" in GA settings!
$wgGoogleAnalyticsDemographics = false;	// Use dc.js to track demographics



$normalCats = array();
/*
 * We don't want to log hidden categories
 */
function onOutputPageMakeCategoryLinks( &$out, $categories, &$links ) {
	global $normalCats;
	$normalCats = array_keys( $categories, 'normal' );
	return true;
}

function efGoogleAnalyticsHook( OutputPage &$out, Skin &$skin ) {
	$out->addHeadItem( 'GoogleAnalyticsIntegration', efAddGoogleAnalytics( $out ) );
	return true;
}

function efAddGoogleAnalytics( OutputPage &$out) {
	global $wgGoogleAnalyticsAccount,
			$wgGoogleAnalyticsSegmentByGroup, $wgGoogleAnalyticsTrackExtLinks,
			$wgGoogleAnalyticsDomainName, $wgGoogleAnalyticsCookiePath,	
			$wgGoogleAnalyticsEnahncedLinkAttribution, $wgGoogleAnalyticsPageGrouping,
			$wgGoogleAnalyticsDemographics;


	if ( is_null( $wgGoogleAnalyticsAccount ) ) {
		$msg = "<!-- You forgot to configure Google Analytics. " . 
				"Please set \$wgGoogleAnalyticsAccount to your Google Analytics account number. -->\n";
		return $msg;
	}

	if ( $out->getUser()->isAllowed( 'noanalytics' ) ) {
		$msg = wfMessage( 'googleanalytics-disabled' )->text();
		return "\n<!-- {$msg} -->\n";
	}
	
   /* Else: we load the script */
   /* Starts with regular GA.js queue initializing, but adds
	* custom code to make sure '_setAccount' is always first (using 'unshift').
    * This allows other extensions to shove things in the queue
    * without knowing anything about it, by doing something like (don't forget ResourceLoader encapsulation!):
    * var _gaq = _gaq || []; _gaq.push(cmd);
    */
   $script = <<<JS
<script>
	var _gaq = _gaq || [];
	cmd = ['_setAccount', '{$wgGoogleAnalyticsAccount}'];
	if (!_gaq.unshift){
		_gaq.push(cmd);
	} else {
	 _gaq.unshift(cmd);
	}
JS;

  if( isset( $wgGoogleAnalyticsDomainName ) ) {
  	$script .= "
  _gaq.push(['_setDomainName', '{$wgGoogleAnalyticsDomainName}']);";
  }
  
  if( isset( $wgGoogleAnalyticsCookiePath ) ) {
  	$script .= "
  	_gaq.push(['_setCookiePath', '{$wgGoogleAnalyticsCookiePath}']);";
  }
  if( $wgGoogleAnalyticsSegmentByGroup === true ) {
    $script .= "
	  _gaq.push(['_setCustomVar',
		1,								// first slot 
		'User Groups',					// custom variable name
		mw.config.get( 'wgUserGroups' ).toString(),	// custom variable filtered in GA
		2						// custom variable scope - session-level
	]);";
  }
  
    if( $wgGoogleAnalyticsPageGrouping === true ) {
    	$title = $out->getTitle();
		$ns = $title->getNamespace();
    	if( isset( $ns ) && in_array( $ns, array( NS_CATEGORY, NS_FILE, NS_SPECIAL, NS_MEDIAWIKI ) ) ) {
    		$script .= "\n/* Namespace excluded from page grouping */\n";
    	} else {
			global $normalCats; // We don't want to log hidden categories
			if ( count( $normalCats ) > 1 ) {
				$normalCats[0] = Title::makeTitleSafe( NS_CATEGORY, $normalCats[0] )->getText();
				$normalCats[1] = Title::makeTitleSafe( NS_CATEGORY, $normalCats[1] )->getText();
				$grouping = $normalCats[1] . '/' . $normalCats[0];
				$script .= "
	  _gaq.push(['_setPageGroup', '1', '{$grouping}']);
	  _gaq.push(['_setPageGroup', '2', '{$normalCats[1]}']);
	  _gaq.push(['_setPageGroup', '3', '{$normalCats[0]}']);
	";
			};
		};
	};
  
  $script .= "
  _gaq.push(['_trackPageview']);";

  if ( isset( $wgGoogleAnalyticsCookiePath ) ) {
		$script .= "
		_gaq.push(['_cookiePathCopy', '{$wgGoogleAnalyticsCookiePath}']);";
  }

  if( $wgGoogleAnalyticsDemographics === true ) {
	  $gaSource = "('https:' == document.location.protocol ? 'https://' : 'http://') + 'stats.g.doubleclick.net/dc.js'";
  } else {
	  $gaSource = "('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js'";
  }
  $script .="
  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = {$gaSource};
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();";
  
  if( $wgGoogleAnalyticsEnahncedLinkAttribution === true ) {
  	$script .= "
  	var pluginUrl = '//www.google-analytics.com/plugins/ga/inpage_linkid.js';
	_gaq.push(['_require', 'inpage_linkid', pluginUrl]);";
  }
  
  if( $wgGoogleAnalyticsTrackExtLinks === true ) {
  	  $script .= <<<JS

	function trackEvent(category, action, label, value, noninteraction) {
		try {
		  _gaq.push(['_trackEvent', category , action, label, value, noninteraction ]);
		} catch(err) {
		}
	}

	function trackOutboundLink( link ) {

	}

	$(document).ready( function() {
	  jQuery( '.mw-body' ).on( 'click', 'a.external, a.extiw', function( e ) {
	     var url = $( this ).attr( 'href' );
	     var host = e.currentTarget.host.replace( ':80', '' );
	     var category = $(this).hasClass( 'external' ) ? 'Outbound Links' : 'Outbound Interwiki';
	     trackEvent( category, host, url, undefined, true );
	  });
	});
JS;
  };

  // And finally...
  $script .="\n</script>\n";

  return $script;
}
