jQuery( document ).ready( function () {
	jQuery( 'body' ).on( 'click', '.ui-tabs-anchor', function () {
		const c = jQuery( this ).closest( 'div.vc_row ' ).data( 'title' );
		'Edit Creative Link' == c &&
			( jQuery( '.vc_col-xs-5' )
				.children()
				.not( '.vc_checkbox' )
				.css( 'display', 'none' ),
			jQuery( '.vc_border' )
				.children()
				.not( '.vc_padding' )
				.css( 'display', 'none' ),
			jQuery( '.vc_padding' ).css( 'margin', '0px' ) );
	} );
} );
