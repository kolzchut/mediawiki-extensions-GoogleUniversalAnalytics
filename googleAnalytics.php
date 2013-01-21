<?php
//v3.1.0: Optional "Enhanced Link Attribution" (https://support.google.com/analytics/bin/answer.py?hl=en&utm_id=ad&answer=2558867)

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

$wgExtensionCredits['other'][] = array(
	'path'           => __FILE__,
	'name'           => 'Google Analytics Integration for Kol-Zchut',
	'version'        => '3.1.0',
	'author'         => 'Tim Laqua, Dror Snir',
	'descriptionmsg' => 'googleanalytics-desc',
	'url'            => 'https://www.mediawiki.org/wiki/Extension:Google_Analytics_Integration',
);

$wgExtensionMessagesFiles['googleAnalytics'] = dirname(__FILE__) . '/googleAnalytics.i18n.php';

$wgHooks['BeforePageDisplay'][]  = 'efGoogleAnalyticsHook';
//$wgHooks['SkinAfterBottomScripts'][]  = 'efGoogleAnalyticsHook';

/* Dror - New */
$wgGoogleAnalyticsAccount = null;
$wgGoogleAnalyticsDomainName = null;
$wgGoogleAnalyticsCookiePath = null;
$wgGoogleAnalyticsSegmentByGroup = false;
$wgGoogleAnalyticsTrackExtLinks = true;
$wgGoogleAnalyticsEnahncedLinkAttribution = false;
	//if you enable this, you must also select "Use enhanced link attribution" in GA settings!
$wgGoogleAnalyticsIgnoreGroups = array( 'bot', 'sysop' );

/* the following config variables are no longer in use: */
//$wgGoogleAnalyticsIgnoreSysops = true;
//$wgGoogleAnalyticsIgnoreBots = true;

/*
function efGoogleAnalyticsHook( $skin, &$text ) {
	$out->addHeadItem( 'GoogleAnalyticsIntegration', efAddGoogleAnalytics() );
	return true;
}
*/

function efGoogleAnalyticsHook( OutputPage &$out, Skin &$skin ) {
	$user = $out->getUser();
	$out->addHeadItem( 'GoogleAnalyticsIntegration', efAddGoogleAnalytics( $user ) );
	return true;
}

function efAddGoogleAnalytics( User $user) {
	global $wgGoogleAnalyticsAccount, $wgGoogleAnalyticsIgnoreGroups,
			$wgGoogleAnalyticsSegmentByGroup, $wgGoogleAnalyticsTrackExtLinks,
			$wgGoogleAnalyticsDomainName, $wgGoogleAnalyticsCookiePath,	
			$wgGoogleAnalyticsEnahncedLinkAttribution;
			

	if ( is_null( $wgGoogleAnalyticsAccount ) ) {
		$msg = "<!-- You forgot to configure Google Analytics. " . 
				"Please set \$wgGoogleAnalyticsAccount to your Google Analytics account number. -->\n";
		return $msg;

	}
	
	if ( isset( $wgGoogleAnalyticsIgnoreGroups ) && is_array( $wgGoogleAnalyticsIgnoreGroups ) ) {
		$excluded_groups = array_intersect( $wgGoogleAnalyticsIgnoreGroups, $user->getEffectiveGroups() );
		if ( count( $excluded_groups ) > 0 ) {
			$excluded_groups = implode( ', ', $excluded_groups );
			return "\n<!-- Google Analytics tracking is disabled for the following groups: {$excluded_groups} -->\n";
		}	
	}
	
   /* Else: we load the script */
   
   $script = "<script>
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '{$wgGoogleAnalyticsAccount}']);";
    
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
  
  $script .= "
  _gaq.push(['_trackPageview']);";

  if ( isset( $wgGoogleAnalyticsCookiePath ) ) {
		$script .= "
		_gaq.push(['_cookiePathCopy', '{$wgGoogleAnalyticsCookiePath}']);";
  }
  
  $script .="
  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();";
  
  if( isset( $wgGoogleAnalyticsEnahncedLinkAttribution) && $wgGoogleAnalyticsEnahncedLinkAttribution == true ) {
  	$script .= "
  	var pluginUrl = '//www.google-analytics.com/plugins/ga/inpage_linkid.js';
	_gaq.push(['_require', 'inpage_linkid', pluginUrl]);";
  }
  
  if( isset( $wgGoogleAnalyticsTrackExtLinks ) && $wgGoogleAnalyticsTrackExtLinks == true ) {
  	  $script .= "
  function recordOutboundLink(category, action, label, value, noninteraction) {
    try {
      var myTracker=_gat._getTrackerByName();
      _gaq.push(['myTracker._trackEvent', category , action, label, value, noninteraction ]);
    } catch(err){}
  }

$(document).ready(function() {
  jQuery('a.external').click( function(e) {
     var url = $(this).attr( 'href' );
     var host = e.currentTarget.host.replace(':80','')
     recordOutboundLink( 'Outbound Links', host, url, null, true );
  });
});";
  };

  // And finally...
  $script .="
  </script>";

  return $script;
}
