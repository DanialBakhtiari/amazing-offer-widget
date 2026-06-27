/**
 * Amazing Offer — admin dashboard scripts.
 */
( function ( $ ) {
	'use strict';

	var cfg = window.amazingOfferAdmin || {};
	var i18n = cfg.i18n || {};

	/**
	 * POST helper.
	 *
	 * @param {string} action AJAX action.
	 * @param {Object} data   Extra payload.
	 * @return {jqXHR}        jQuery ajax promise.
	 */
	function post( action, data ) {
		return $.post(
			cfg.ajaxUrl,
			$.extend( { action: action, nonce: cfg.nonce }, data || {} )
		);
	}

	/**
	 * Escape HTML for safe injection.
	 *
	 * @param {string} str Raw string.
	 * @return {string}    Escaped string.
	 */
	function esc( str ) {
		return $( '<div>' ).text( str == null ? '' : str ).html();
	}

	/**
	 * True when a product row already exists in the active table.
	 *
	 * @param {number} id Product id.
	 * @return {boolean}  Whether present.
	 */
	function rowExists( id ) {
		return $( '#ao-products-body tr[data-product-id="' + id + '"]' ).length > 0;
	}

	/**
	 * Append a product row to the active table.
	 *
	 * @param {Object} p Product data.
	 * @return {void}
	 */
	function addRow( p ) {
		if ( rowExists( p.id ) ) {
			return;
		}
		$( '#ao-products-body .ao-empty-row' ).remove();

		var discount = p.discount_percent > 0
			? '<span class="ao-discount-badge">' + esc( p.discount_percent ) + '٪</span>'
			: '—';

		var $row = $(
			'<tr data-product-id="' + esc( p.id ) + '">' +
				'<td class="ao-col-handle"><span class="dashicons dashicons-menu ao-drag-handle"></span></td>' +
				'<td class="ao-col-image"><img src="' + esc( p.image ) + '" alt="" width="48" height="48"></td>' +
				'<td class="ao-col-name">' + esc( p.name ) + '</td>' +
				'<td class="ao-col-price">' + ( p.price_html || '' ) + '</td>' +
				'<td class="ao-col-discount">' + discount + '</td>' +
				'<td class="ao-col-remove"><button type="button" class="button-link ao-remove-row"><span class="dashicons dashicons-trash"></span></button></td>' +
			'</tr>'
		);
		$( '#ao-products-body' ).append( $row );
	}

	/**
	 * Collect ordered product ids from the active table.
	 *
	 * @return {number[]} Ordered ids.
	 */
	function collectIds() {
		var ids = [];
		$( '#ao-products-body tr[data-product-id]' ).each( function () {
			ids.push( $( this ).data( 'product-id' ) );
		} );
		return ids;
	}

	$( function () {

		// Color pickers.
		if ( $.fn.wpColorPicker ) {
			$( '.ao-color-picker' ).wpColorPicker();
		}

		// Sortable table.
		if ( $.fn.sortable ) {
			$( '#ao-products-body' ).sortable( {
				handle: '.ao-drag-handle',
				placeholder: 'ao-sortable-placeholder',
				axis: 'y'
			} );
		}

		/* --- Timer dependent fields --- */
		function syncTimerFields() {
			var val = $( 'input[name="amazing_offer[timer_type]"]:checked' ).val();
			$( '.ao-timer-dep' ).removeClass( 'is-visible' );
			$( '.ao-timer-dep[data-dep="' + val + '"]' ).addClass( 'is-visible' );
		}
		if ( $( 'input[name="amazing_offer[timer_type]"]' ).length ) {
			syncTimerFields();
			$( document ).on( 'change', 'input[name="amazing_offer[timer_type]"]', syncTimerFields );
		}

		/* --- Load sale products --- */
		$( '#ao-load-sale' ).on( 'click', function () {
			var $btn = $( this );
			$btn.prop( 'disabled', true );
			$( '#ao-sale-results' ).html( '<p>' + esc( i18n.loading ) + '</p>' );

			post( 'amazing_offer_load_sale_products', { limit: 30 } )
				.done( function ( res ) {
					if ( ! res.success || ! res.data.products.length ) {
						$( '#ao-sale-results' ).html( '<p>' + esc( i18n.noResults ) + '</p>' );
						return;
					}
					var html = '';
					res.data.products.forEach( function ( p ) {
						html +=
							'<label class="ao-sale-item">' +
								'<input type="checkbox" class="ao-sale-check" value="' + esc( p.id ) + '">' +
								'<img src="' + esc( p.image ) + '" alt="">' +
								'<span class="ao-sale-info">' +
									'<span class="ao-sale-name">' + esc( p.name ) + '</span>' +
									'<span class="ao-sale-price">' + ( p.price_html || '' ) +
										( p.discount_percent > 0 ? ' <span class="ao-sale-discount">' + esc( p.discount_percent ) + '٪</span>' : '' ) +
									'</span>' +
								'</span>' +
							'</label>';
					} );
					$( '#ao-sale-results' ).html( html ).data( 'products', res.data.products );
					$( '#ao-sale-actions' ).show();
				} )
				.fail( function () {
					$( '#ao-sale-results' ).html( '<p>' + esc( i18n.error ) + '</p>' );
				} )
				.always( function () {
					$btn.prop( 'disabled', false );
				} );
		} );

		// Select all sale checkboxes.
		$( '#ao-select-all' ).on( 'change', function () {
			$( '.ao-sale-check' ).prop( 'checked', this.checked );
		} );

		// Add selected sale products.
		$( '#ao-add-selected' ).on( 'click', function () {
			var products = $( '#ao-sale-results' ).data( 'products' ) || [];
			$( '.ao-sale-check:checked' ).each( function () {
				var id = parseInt( this.value, 10 );
				var p = products.filter( function ( x ) { return parseInt( x.id, 10 ) === id; } )[ 0 ];
				if ( p ) {
					addRow( p );
				}
			} );
			$( '.ao-sale-check, #ao-select-all' ).prop( 'checked', false );
		} );

		/* --- Live search --- */
		var searchTimer = null;
		$( '#ao-search-input' ).on( 'keyup', function () {
			var kw = $.trim( this.value );
			clearTimeout( searchTimer );
			if ( kw.length < 2 ) {
				$( '#ao-search-results' ).removeClass( 'is-open' ).empty();
				return;
			}
			searchTimer = setTimeout( function () {
				post( 'amazing_offer_search_products', { keyword: kw } )
					.done( function ( res ) {
						if ( ! res.success || ! res.data.products.length ) {
							$( '#ao-search-results' ).addClass( 'is-open' )
								.html( '<div class="ao-search-item">' + esc( i18n.noResults ) + '</div>' );
							return;
						}
						var html = '';
						res.data.products.forEach( function ( p ) {
							html +=
								'<div class="ao-search-item" data-product=\'' + esc( JSON.stringify( p ) ) + '\'>' +
									'<img src="' + esc( p.image ) + '" alt="">' +
									'<span class="ao-search-name">' + esc( p.name ) + '</span>' +
									'<button type="button" class="button button-small ao-search-add">' + esc( i18n.added || 'افزودن' ) + '</button>' +
								'</div>';
						} );
						$( '#ao-search-results' ).addClass( 'is-open' ).html( html );
					} );
			}, 300 );
		} );

		// Add from search.
		$( document ).on( 'click', '.ao-search-add', function () {
			var p = $( this ).closest( '.ao-search-item' ).data( 'product' );
			if ( p ) {
				if ( rowExists( p.id ) ) {
					window.alert( i18n.alreadyAdded );
				} else {
					addRow( p );
				}
			}
			$( '#ao-search-results' ).removeClass( 'is-open' ).empty();
			$( '#ao-search-input' ).val( '' );
		} );

		// Close search when clicking outside.
		$( document ).on( 'click', function ( e ) {
			if ( ! $( e.target ).closest( '.amazing-offer-search-wrap' ).length ) {
				$( '#ao-search-results' ).removeClass( 'is-open' );
			}
		} );

		/* --- Remove row --- */
		$( document ).on( 'click', '.ao-remove-row', function () {
			if ( window.confirm( i18n.confirmRemove ) ) {
				$( this ).closest( 'tr' ).remove();
			}
		} );

		/* --- Save order / list --- */
		$( '#ao-save-order' ).on( 'click', function () {
			var $btn = $( this );
			$btn.prop( 'disabled', true );
			post( 'amazing_offer_save_products', { product_ids: collectIds() } )
				.done( function ( res ) {
					var msg = res.success ? ( res.data.message || i18n.saved ) : ( res.data.message || i18n.error );
					$btn.after( '<span class="ao-inline-feedback" style="margin-right:10px;color:#1a8a3b;font-weight:600">' + esc( msg ) + '</span>' );
					setTimeout( function () { $( '.ao-inline-feedback' ).remove(); }, 2500 );
				} )
				.fail( function () {
					window.alert( i18n.error );
				} )
				.always( function () {
					$btn.prop( 'disabled', false );
				} );
		} );

		/* --- Copy card number --- */
		$( document ).on( 'click', '.ao-copy-card', function () {
			var card = $( this ).data( 'card' ).toString();
			var $fb = $( '.ao-copy-feedback' );

			function done() {
				$fb.text( i18n.copied );
				setTimeout( function () { $fb.text( '' ); }, 2500 );
			}

			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				navigator.clipboard.writeText( card ).then( done ).catch( function () {
					fallbackCopy( card );
					done();
				} );
			} else {
				fallbackCopy( card );
				done();
			}
		} );

		/**
		 * Legacy clipboard fallback.
		 *
		 * @param {string} text Text to copy.
		 * @return {void}
		 */
		function fallbackCopy( text ) {
			var $tmp = $( '<textarea>' ).val( text ).css( { position: 'fixed', opacity: 0 } ).appendTo( 'body' );
			$tmp[ 0 ].select();
			try { document.execCommand( 'copy' ); } catch ( e ) {}
			$tmp.remove();
		}
	} );

} )( jQuery );
