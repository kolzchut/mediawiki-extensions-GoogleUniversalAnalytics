/* global riveted */
( function ( $, mw ) {
	'use strict';
	var config = mw.config.get( 'wgGoogleUniversalAnalyticsRivetedConfig' );

	mw.googleAnalytics = mw.googleAnalytics || {};	// Might not be defined yet
	mw.googleAnalytics.riveted = riveted;
	mw.googleAnalytics.riveted.init( config );
}( jQuery, mediaWiki ) );
