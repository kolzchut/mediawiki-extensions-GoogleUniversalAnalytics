/** Helper functions for click tracking */
( function( $, mw ) {
	'use strict';

	mw.googleAnalytics = mw.googleAnalytics || {};	// Might not be defined yet

	var mwGA = mw.googleAnalytics;

	mw.googleAnalytics.utils = {
		isLinkWillOpenInNewTab: function( event ) {
			return (
					event.ctrlKey
					|| event.shiftKey
					|| event.metaKey // apple
					|| (event.button && event.button === 1)
					|| $( event.target).attr( 'target' ) === '_blank'
			);
		},

		isShouldOverrideClickHandler: function(domEvent ) {
			return (
				mwGA.utils.isGoogleAnalyticsLoaded()
				&& typeof( navigator.sendBeacon ) === 'undefined'
				&& !mw.promoter.isLinkWillOpenInNewTab( domEvent )
			);
		},

		defaultClickHandler: function( domEvent ) {
			if( mwGA.utils.isShouldOverrideClickHandler( domEvent ) ) {
				domEvent.preventDefault();
				// Worse case scenario: the hitCallback never called back...
				// https://developers.google.com/analytics/devguides/collection/analyticsjs/sending-hits#handling_timeouts
				setTimeout(
					function() {
						mwGA.utils.goToEventTargetUrl( domEvent );
					}, 1000
				);
			}
		},

		goToEventTargetUrl: function( domEvent ) {
			if( mwGA.utils.isShouldOverrideClickHandler( domEvent ) ) {
				document.location = domEvent.target.href;
			}
		},

		isGoogleAnalyticsLoaded: function() {
			if( window.ga && window.ga.create ) {
				return true;
			}

			// Google Analytics was not loaded
			return false;
		},

		// gaEventProps = { eventCategory, eventAction, eventLabel, nonInteraction, eventValue }
		recordEvent: function( gaEventProps ) {
			if( !mwGA.utils.isGoogleAnalyticsLoaded() ) {
				return;
			}

			if( gaEventProps.nonInteraction !== false ) {
				gaEventProps.nonInteraction = true;
			}
			gaEventProps.hitCallback = gaEventProps.hitCallback || null;
			gaEventProps.transport = 'beacon';


			window.ga( 'send', 'event', gaEventProps );
		},

		recordClickEvent: function( domEvent, gaEventProps ) {
			gaEventProps.hitCallback = function() { mwGA.utils.goToEventTargetUrl( domEvent ); };
			if( gaEventProps.nonInteraction !== true ) {
				gaEventProps.nonInteraction = false;
			}

			mwGA.utils.defaultClickHandler( domEvent );
			mwGA.utils.recordEvent( gaEventProps );

		}
	};

})( jQuery, mediaWiki );
