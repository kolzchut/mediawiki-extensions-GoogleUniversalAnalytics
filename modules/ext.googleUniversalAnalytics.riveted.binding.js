( function ( $, mw ) {
	'use strict';
	mw.googleAnalytics = mw.googleAnalytics || {};	// Might not be defined yet

	mw.googleAnalytics.riveted = riveted;
	var config = mw.config.get( 'wgGoogleUniversalAnalyticsRivetedConfig' );
	mw.googleAnalytics.riveted.init( config );
}( jQuery, mediaWiki ) );
