<?php
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'Google Universal Analytics Integration for Kol-Zchut',
	'version' => 'kz-3.3.0 (based on upstream 3.0.1)',
	'author' => array(
		'Tim Laqua',
		'[https://www.mediawiki.org/wiki/User:DavisNT Davis Mosenkovs]',
		'Dror S. [FFS] ([http://www.kolzchut.org.il Kol-Zchut])'
	),
	'descriptionmsg' => 'googleuniversalanalytics-desc',
	'url' => 'https://www.mediawiki.org/wiki/Extension:Google_Analytics_Integration',
);

$wgMessagesDirs['GoogleUniversalAnalytics'] = __DIR__ . '/i18n';

/*** Default configuration ***/
// Google Universal Analytics account id (e.g. "UA-12345678-1")
$wgGoogleUniversalAnalyticsAccount = null;

// Don't store last octet (or last 80 bits of IPv6 address) in Google Universal Analytics
// For more info see https://support.google.com/analytics/answer/2763052?hl=en
$wgGoogleUniversalAnalyticsAnonymizeIP = true;

// HTML code for other web analytics (can be used along with Google Universal Analytics)
$wgGoogleUniversalAnalyticsOtherCode = null;

// Array with NUMERIC namespace IDs where web analytics code should NOT be included.
$wgGoogleUniversalAnalyticsIgnoreNsIDs = array();

// Array with page names (see magic word {{FULLPAGENAME}}) where web analytics code should NOT be included.
$wgGoogleUniversalAnalyticsIgnorePages = array();

// Array with special pages where web analytics code should NOT be included.
$wgGoogleUniversalAnalyticsIgnoreSpecials = array( 'Userlogin', 'Userlogout', 'Preferences', 'ChangePassword' );

// It is possible to use 'noanalytics' permission to exclude specific groups from web analytics. */
$wgGroupPermissions['bot']['noanalytics'] = true;

/* Dror - New */
$wgGoogleUniversalAnalyticsCookiePath = null;
$wgGoogleUniversalAnalyticsDomainName = null;
$wgGoogleUniversalAnalyticsSegmentByGroup = false;
$wgGoogleUniversalAnalyticsSegmentByGroupDimension = 1; // Use dimension1 by default
$wgGoogleUniversalAnalyticsTrackExtLinks = true;
$wgGoogleUniversalAnalyticsEnahncedLinkAttribution = false;
$wgGoogleUniversalAnalyticsRemarketing = false;
// Kol-Zchut specific! Classify page into Content Groups, based on the first 2 visible categories.
$wgGoogleUniversalAnalyticsPageGrouping = false;


/*****************************/

$wgAutoloadClasses['GoogleUniversalAnalyticsHooks'] = __DIR__ . '/GoogleUniversalAnalytics.hooks.php';
$wgHooks['SkinAfterBottomScripts'][] = 'GoogleUniversalAnalyticsHooks::onSkinAfterBottomScripts';
$wgHooks['BeforePageDisplay'][]  = 'GoogleUniversalAnalyticsHooks::onBeforePageDisplay';
$wgHooks['OutputPageMakeCategoryLinks'][] = 'GoogleUniversalAnalyticsHooks::onOutputPageMakeCategoryLinks'; // Get categories
$wgHooks['UnitTestsList'][] = 'GoogleUniversalAnalyticsHooks::onUnitTestsList';


$wgResourceModules['ext.googleUniversalAnalytics.utils'] = array(
		'scripts' => array(
				'modules/ext.googleUniversalAnalytics.utils.js',
		),
		'localBasePath' => __DIR__,
		'remoteExtPath' => 'GoogleUniversalAnalytics',
		'position' => 'bottom'
);

$wgResourceModules['ext.googleUniversalAnalytics.externalLinks'] = array(
		'scripts' => array(
				'modules/ext.googleUniversalAnalytics.externalLinks.js',
		),
		'dependencies' => 'ext.googleUniversalAnalytics.utils',
		'localBasePath' => __DIR__,
		'remoteExtPath' => 'GoogleUniversalAnalytics',
		'position' => 'bottom'
);

