<?php
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

$wgExtensionCredits['other'][] = array(
	'path'           => __FILE__,
	'name'           => 'Google Analytics Integration',
	'version'        => '3.0.3',
	'author'         => 'Tim Laqua, Dror Snir',
	'descriptionmsg' => 'googleanalytics-desc',
	'url'            => 'https://www.mediawiki.org/wiki/Extension:Google_Analytics_Integration',
);

$wgExtensionMessagesFiles['googleAnalytics'] = dirname(__FILE__) . '/googleAnalytics.i18n.php';

#$wgHooks['BeforePageDisplay'][]  = 'efGoogleAnalyticsHook';
$wgHooks['SkinAfterBottomScripts'][]  = 'efGoogleAnalyticsHook';

/* Dror - New */
$wgGoogleAnalyticsAccount = '';
$wgGoogleAnalyticsDomainName = '';
$wgGoogleAnalyticsCookiePath = '';
$wgGoogleAnalyticsCookiePathCopy = '';
$wgGoogleAnalyticsSegmentByGroup = false;
$wgGoogleAnalyticsIgnoreGroups = array( 'bot', 'sysop' );

/* Old - to be removed eventually */
$wgGoogleAnalyticsIgnoreSysops = true;
$wgGoogleAnalyticsIgnoreBots = true;
/* end old */

/* temporary while not using BeforePageDisplayHook
function efGoogleAnalyticsHook( &$out, &$skin ) {
	$out->addHeadItem( 'GoogleAnalyticsIntegration', efAddGoogleAnalytics() );
	return true;
}
*/
function efGoogleAnalyticsHook( $skin, &$text ) {
	$text .= efAddGoogleAnalytics();
	return true;
}

function efAddGoogleAnalytics() {
	global $wgGoogleAnalyticsAccount, $wgGoogleAnalyticsIgnoreSysops, $wgGoogleAnalyticsIgnoreBots, $wgUser;
	global $wgGoogleAnalyticsDomainName, $wgGoogleAnalyticsCookiePath, $wgGoogleAnalyticsCookiePathCopy, $wgGoogleAnalyticsSegmentByGroup;

	if ( $wgGoogleAnalyticsAccount === '' ) {
		return "\n<!-- Set \$wgGoogleAnalyticsAccount to your account # provided by Google Analytics. -->\n";
	}
	
	if ( $wgUser->isAllowed( 'bot' ) && $wgGoogleAnalyticsIgnoreBots ) {
		return "\n<!-- Google Analytics tracking is disabled for bots -->\n";
	}

	if ( $wgUser->isAllowed( 'protect' ) && $wgGoogleAnalyticsIgnoreSysops ) {
		return "\n<!-- Google Analytics tracking is disabled for users with 'protect' rights (I.E. sysops) -->\n";
	}

    /* Else: */
	return <<<GASCRIPT
<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '{$wgGoogleAnalyticsAccount}']);
  if( '{$wgGoogleAnalyticsDomainName}' != '' ) {
  	_gaq.push(['_setDomainName', '{$wgGoogleAnalyticsDomainName}']);
  }
  if( '{$wgGoogleAnalyticsCookiePath}' != '' ) {
  	_gaq.push(['_setCookiePath', '{$wgGoogleAnalyticsCookiePath}']);
  }
  if( '{$wgGoogleAnalyticsSegmentByGroup}' == true ) {
	  _gaq.push(['_setCustomVar',
		1,						// first slot 
		'User Groups',					// custom variable name 
		mw.config.get( 'wgUserGroups' ).toString(),	// custom variable value - an array covnerted to string, later using "contains" inside GA
		2						// custom variable scope - session-level
	]);

  }
  
  _gaq.push(['_trackPageview']);

  if ( '{$wgGoogleAnalyticsCookiePath}' != '' && '{$wgGoogleAnalyticsCookiePathCopy}' != '' ) {
		_gaq.push(['_cookiePathCopy', '{$wgGoogleAnalyticsCookiePathCopy}']);
  }

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>
GASCRIPT;
}

///Alias for efAddGoogleAnalytics - backwards compatibility.
function addGoogleAnalytics() { return efAddGoogleAnalytics(); }
