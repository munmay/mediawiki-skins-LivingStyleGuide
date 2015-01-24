$( function () {
	$( '.navbar-header' ).click( function ( e ) {
		e.preventDefault();
		$( '#site-wrap' ).toggleClass( 'site-shift' );
	} );
} );
