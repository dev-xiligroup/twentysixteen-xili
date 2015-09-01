/**
 * Live-update changed settings in real time in the Customizer preview.
 */

( function( $ ) {
	var api = wp.customize;

	// copyright tagline.
	api( 'copyright', function( value ) {
		value.bind( function( to ) {
			$( '.site-copyright' ).text( to );
		} );
	} );

} )( jQuery );
