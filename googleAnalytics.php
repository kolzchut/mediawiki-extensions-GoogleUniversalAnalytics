<?php
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'Google Analytics Integration for Kol-Zchut',
	'version' => '3.2.5 (based on upstream 3.0.1)',
	'author' => array(
		'Tim Laqua',
		'[https://www.mediawiki.org/wiki/User:DavisNT Davis Mosenkovs]',
		'Dror S. [FFS] ([http://www.kolzchut.org.il Kol-Zchut])'
	),
	'descriptionmsg' => 'googleanalytics-desc',
	'url' => 'https://www.mediawiki.org/wiki/Extension:Google_Analytics_Integration',
);

$wgMessagesDirs['googleAnalytics'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['googleAnalytics'] = __DIR__ . '/googleAnalytics.i18n.php';

/*** Default configuration ***/

// Google Universal Analytics account id (e.g. "UA-12345678-1")
$wgGoogleAnalyticsAccount = null;

// HTML code for other web analytics (can be used along with Google Analytics)
$wgGoogleAnalyticsOtherCode = null;

// Array with NUMERIC namespace IDs where web analytics code should NOT be included.
$wgGoogleAnalyticsIgnoreNsIDs = array();

// Array with page names (see magic word {{FULLPAGENAME}}) where web analytics code should NOT be included.
$wgGoogleAnalyticsIgnorePages = array();

// Array with special pages where web analytics code should NOT be included.
$wgGoogleAnalyticsIgnoreSpecials = array( 'Userlogin', 'Userlogout', 'Preferences', 'ChangePassword' );

/* WARNING! The following options were removed in version 3.0:
 *   $wgGoogleAnalyticsAddASAC
 *   $wgGoogleAnalyticsIgnoreSysops
 *   $wgGoogleAnalyticsIgnoreBots
 * It is possible (and advised) to use 'noanalytics' permission to exclude specific groups from web analytics. */

$wgGroupPermissions['bot']['noanalytics'] = true;

/* Dror - New */
// https://developers.google.com/analytics/devguides/collection/gajs/methods/gaJSApiDomainDirectory#_gat.GA_Tracker_._setDomainName
$wgGoogleAnalyticsDomainName = null;
// https://developers.google.com/analytics/devguides/collection/gajs/methods/gaJSApiDomainDirectory#_gat.GA_Tracker_._setCookiePath
$wgGoogleAnalyticsCookiePath = null;
// Push the user's groups into a custom var, allowing later segmentation in the GA dashboard
$wgGoogleAnalyticsSegmentByGroup = false;
// Track clicks on external links (including interwiki interlanguage), using events
$wgGoogleAnalyticsTrackExtLinks = true;
// Enable EnhancedLinkAttribution (see https://support.google.com/analytics/answer/2558867).
// if you enable this, you must also select "Use enhanced link attribution" in GA settings!
$wgGoogleAnalyticsEnahncedLinkAttribution = false;
// Use DoubleClick's dc.js instead of ga.js, in order to track demographics / remarketing.
// Seems to be blocked more often by users. See https://support.google.com/analytics/answer/2444872
$wgGoogleAnalyticsDemographics = false;
// Kol-Zchut specific! Classify page into Content Groups, based on the first 2 visible categories.
$wgGoogleAnalyticsPageGrouping = false;


/*****************************/

$wgAutoloadClasses['GoogleAnalyticsHooks'] = __DIR__ . '/googleAnalytics.hooks.php';
$wgHooks['SkinAfterBottomScripts'][] = 'GoogleAnalyticsHooks::onSkinAfterBottomScripts';
$wgHooks['BeforePageDisplay'][]  = 'GoogleAnalyticsHooks::onBeforePageDisplay';
$wgHooks['OutputPageMakeCategoryLinks'][] = 'GoogleAnalyticsHooks::onOutputPageMakeCategoryLinks'; // Get categories

