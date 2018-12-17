( function ( $, mw ) {
	var gaUtils = mw.googleAnalytics.utils;

	function identifyLinkType( $element ) {
		if ( $element.hasClass( 'external' ) ) {
			if ( $element.hasClass( 'phonenum' ) ) {
				return 'Phone Links';
			}
			return 'Outbound Links';
		}
		if ( $element.hasClass( 'extiw' ) ) {
			return 'Outbound Interwiki';
		}

		// None of the above, so it must be an interlanguage link we picked up
		return 'Language Links';
	}

	$( 'body' ).on( 'click', 'a.external, a.extiw, .interlanguage-link > a', function ( e ) {
		var targetUrl = $( this ).attr( 'href' ),
			targetHost = e.currentTarget.host.replace( /:\d{2,4}/g, '' ),
			linkType = identifyLinkType( $( this ) );

		gaUtils.recordClickEvent( e, {
			eventCategory: linkType,
			eventAction: targetHost,
			eventLabel: targetUrl
		} );
	} );

}( jQuery, mediaWiki ) );
