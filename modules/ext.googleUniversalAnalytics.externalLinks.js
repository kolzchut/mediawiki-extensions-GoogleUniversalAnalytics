( function ( $, mw ) {
	var gaUtils = mw.googleAnalytics.utils;

	$( 'body' ).on( 'click', 'a.external, a.extiw, .interlanguage-link > a', function( e ) {
		var targetUrl = $( this ).attr( 'href' );
		var targetHost = e.currentTarget.host.replace( /:\d{2,4}/g, '' );
		var linkType = '';
		if( $(this).hasClass( 'external' ) ) {
			linkType = 'Outbound Links';
		} else if( $(this).hasClass( 'extiw' ) ) {
			linkType = 'Outbound Interwiki';
		} else {
			linkType = 'Language Links';
		}

		gaUtils.recordClickEvent( e, {
			eventCategory: linkType,
			eventAction: targetHost,
			eventLabel: targetUrl
		} );
	});

}( jQuery, mediaWiki ) );
