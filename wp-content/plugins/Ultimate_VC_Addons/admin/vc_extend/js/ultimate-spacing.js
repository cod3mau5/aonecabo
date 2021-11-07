( function ( $, window, undefined ) {
	function update_inputs( t ) {
		const status = t.find( '.ult-spacing-all' ).attr( 'data-status' );
		const unit = t.find( '.ult-unit-spacing option:selected' ).val();

		if ( status === 'hide-me' ) {
			//  Add {single} input values
			var vals = '';
			t.find( '.ult-spacing-single .ultimate-spacing-input' ).each(
				function ( index, el ) {
					const spacing_type = $( el ).attr( 'data-id' ) || '';
					const val = $( el ).val() || '';
					if ( $.isNumeric( val ) ) {
						vals += spacing_type + ':' + val + unit + ';';
					}
				}
			);
			t.find( '.ultimate-spacing-value' ).val( vals );
		} else {
			//  Add {all} spacing values
			var vals = '';
			const alls = t.find( '.ult-spacing-all .ultimate-spacing-input' );
			const spacing_type = alls.attr( 'data-id' ) || '';

			const val = alls.val() || '';
			if ( $.isNumeric( val ) ) {
				vals += spacing_type + ':' + val + unit + ';';
			}
			t.find( '.ultimate-spacing-value' ).val( vals );
		}
	}

	/* Toggle inputs */
	function toggle_update_inputs( t ) {
		t.find( '.ult-spacing-expand' ).toggleClass(
			'ult-spacing-expand-section'
		);

		const status = t.find( '.ult-spacing-all' ).attr( 'data-status' );
		const unit = t.find( '.ult-unit-spacing option:selected' ).val();

		if ( status === 'hide-all' ) {
			t.find( '.ult-spacing-all' ).hide();
			t.find( '.ult-spacing-single' ).show();
			t.find( '.ult-spacing-all' ).attr( 'data-status', 'hide-me' );

			//  Add {single} input values
			var vals = '';
			t.find( '.ult-spacing-single .ultimate-spacing-input' ).each(
				function ( index, el ) {
					const spacing_type = $( el ).attr( 'data-id' ) || '';
					const val = $( el ).val() || '';
					if ( $.isNumeric( val ) ) {
						vals += spacing_type + ':' + val + unit + ';';
					}
				}
			);
			t.find( '.ultimate-spacing-value' ).val( vals );
		} else {
			t.find( '.ult-spacing-all' ).show();
			t.find( '.ult-spacing-single' ).hide();
			t.find( '.ult-spacing-all' ).attr( 'data-status', 'hide-all' );

			//  Add {all} spacing values
			var vals = '';
			const alls = t.find( '.ult-spacing-all .ultimate-spacing-input' );
			const spacing_type = alls.attr( 'data-id' ) || '';

			const val = alls.val() || '';
			if ( $.isNumeric( val ) ) {
				vals += spacing_type + ':' + val + unit + ';';
			}
			t.find( '.ultimate-spacing-value' ).val( vals );
		}
	}

	$( '.ultimate-spacing' ).each( function ( index, element ) {
		const t = $( element );
		get_values_from_hidden_field( t );
		set_values_from_hidden_field( t );
	} );

	function get_values_from_hidden_field( t ) {
		const mv = t.find( '.ultimate-spacing-value' ).val() || null;

		if ( typeof mv !== 'undefined' && mv != null ) {
			const vals = mv.split( ';' );
			$.each( vals, function ( i, vl ) {
				if ( vl != '' ) {
					t.find( '.ultimate-spacing-inputs' ).each( function (
						input_index,
						elem
					) {
						const splitval = vl.split( ':' );
						const dataid = $( elem ).attr( 'data-id' );
						if ( dataid == splitval[ 0 ] ) {
							const tmp = splitval[ 1 ].match( /\d+/ );
							const b = splitval[ 1 ].split( tmp );
							let unit = 'px';
							if (
								typeof b[ 1 ] !== 'undefined' &&
								b[ 1 ] != null
							) {
								unit = b[ 1 ];
							}
							t.find( '.ult-unit-spacing' ).val( unit ); // set border select unit

							//var unit = $(elem).attr("data-unit");
							mval = splitval[ 1 ].split( unit );
							$( elem ).val( mval[ 0 ] );
						}

						//  Toggle Inputs
						if (
							splitval[ 0 ] === 'margin' ||
							splitval[ 0 ] === 'padding'
						) {
							t.find( '.ult-spacing-all' ).show();
							t.find( '.ult-spacing-single' ).hide();
							t.find( '.ult-spacing-all' ).attr(
								'data-status',
								'hide-all'
							);
							t.find( '.ult-spacing-expand' ).toggleClass(
								'ult-spacing-expand-section'
							);
						} else {
							t.find( '.ult-spacing-all' ).hide();
							t.find( '.ult-spacing-single' ).show();
							t.find( '.ult-spacing-all' ).attr(
								'data-status',
								'hide-me'
							);
						}
					} );
				}
			} );
		} else {
			////   Here, Doen't have any save values. So,
			//// Hide all
			t.find( '.ult-spacing-single' ).hide();

			t.find( '.ult-spacing-expand' ).toggleClass(
				'ult-spacing-expand-section'
			);

			////   Add defaults to input
			t.find( '.ultimate-spacing-inputs' ).each( function (
				input_index,
				elem
			) {
				const d = $( elem ).attr( 'data-default' );
				$( elem ).val( d );
			} );

			//  Add to hidden
			const unit =
				t.find( '.ultimate-spacing-value' ).attr( 'data-unit' ) || 'px';
			t.find( '.ult-unit-spacing' ).val( unit );
			let nval = '';
			//var unit = t.find(".ult-unit-spacing option:selected").val();
			t.find( '.ult-spacing-single .ultimate-spacing-input' ).each(
				function ( index, elm ) {
					const ival = $( elm ).val();
					if ( $.isNumeric( ival ) ) {
						const item =
							$( elm ).attr( 'data-id' ) +
							':' +
							$( elm ).val() +
							unit +
							';';
						nval += item;
					}
				}
			);
			t.find( '.ultimate-spacing-value' ).val( nval );
		}
	}

	function set_values_from_hidden_field( t ) {
		// 1. Expand / Collapse
		t.find( '.ult-spacing-expand' ).click( function ( event ) {
			toggle_update_inputs( t );
		} );

		// 2. Unit change
		t.find( '.ult-unit-spacing' ).change( function () {
			update_inputs( t );
		} );

		//  Single
		t.find( '.ult-spacing-single .ultimate-spacing-input' ).on(
			'change',
			function ( e ) {
				let nval = '';
				//  add all spacing widths, margins, paddings
				t.find( '.ult-spacing-single .ultimate-spacing-input' ).each(
					function ( index, elm ) {
						const ival = $( elm ).val();
						const unit = t
							.find( '.ult-unit-spacing option:selected' )
							.val();
						if ( $.isNumeric( ival ) ) {
							const item =
								$( elm ).attr( 'data-id' ) +
								':' +
								$( elm ).val() +
								unit +
								';';
							nval += item;
						}
					}
				);
				t.find( '.ultimate-spacing-value' ).val( nval );
			}
		);

		//  All
		t.find( '.ult-spacing-all .ultimate-spacing-input' ).on(
			'change',
			function ( e ) {
				let nval = '';
				//  add all spacing widths, margins, paddings
				t.find( '.ult-spacing-all .ultimate-spacing-input' ).each(
					function ( index, elm ) {
						const ival = $( elm ).val();
						const unit = t
							.find( '.ult-unit-spacing option:selected' )
							.val();
						if ( $.isNumeric( ival ) ) {
							const item =
								$( elm ).attr( 'data-id' ) +
								':' +
								$( elm ).val() +
								unit +
								';';
							nval += item;
						}
					}
				);
				t.find( '.ultimate-spacing-value' ).val( nval );
			}
		);
	}
} )( jQuery, window );
