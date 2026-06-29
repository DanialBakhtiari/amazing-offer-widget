/**
 * Special Offer — admin manager + editor scripts.
 */
( function ( $ ) {
	'use strict';

	var cfg = window.amazingOfferSOAdmin || {};
	var i18n = cfg.i18n || {};

	function post( action, data ) {
		return $.post( cfg.ajaxUrl, $.extend( { action: action, nonce: cfg.nonce }, data || {} ) );
	}

	function esc( s ) {
		return $( '<div>' ).text( s == null ? '' : s ).html();
	}

	/* ------------------------------------------------------------------ */
	/* List screen                                                        */
	/* ------------------------------------------------------------------ */
	function initList() {
		var $table = $( '#ao-so-table' );
		if ( ! $table.length ) {
			return;
		}

		// New template.
		$( '#ao-so-new' ).on( 'click', function () {
			var title = window.prompt( i18n.newTitle, '' );
			if ( title === null ) {
				return;
			}
			post( 'ao_so_create', { title: title } ).done( function ( res ) {
				if ( res.success ) {
					window.location.href = res.data.editUrl;
				} else {
					window.alert( ( res.data && res.data.message ) || i18n.error );
				}
			} );
		} );

		// Duplicate.
		$table.on( 'click', '.ao-so-duplicate', function () {
			var id = $( this ).closest( 'tr' ).data( 'id' );
			post( 'ao_so_duplicate', { id: id } ).done( function ( res ) {
				if ( res.success ) {
					window.location.reload();
				} else {
					window.alert( ( res.data && res.data.message ) || i18n.error );
				}
			} );
		} );

		// Delete.
		$table.on( 'click', '.ao-so-delete', function () {
			if ( ! window.confirm( i18n.confirmDelete ) ) {
				return;
			}
			var $row = $( this ).closest( 'tr' );
			post( 'ao_so_delete', { id: $row.data( 'id' ) } ).done( function ( res ) {
				if ( res.success ) {
					$row.fadeOut( 200, function () { $( this ).remove(); } );
				} else {
					window.alert( ( res.data && res.data.message ) || i18n.error );
				}
			} );
		} );

		// Toggle active.
		$table.on( 'change', '.ao-so-toggle', function () {
			var id = $( this ).closest( 'tr' ).data( 'id' );
			post( 'ao_so_toggle', { id: id, active: this.checked ? 1 : 0 } ).fail( function () {
				window.alert( i18n.error );
			} );
		} );

		// Drag-drop reorder (auto-save).
		if ( $.fn.sortable ) {
			$( '#ao-so-rows' ).sortable( {
				handle: '.ao-so-drag',
				axis: 'y',
				update: function () {
					var ids = [];
					$( '#ao-so-rows tr[data-id]' ).each( function () { ids.push( $( this ).data( 'id' ) ); } );
					post( 'ao_so_reorder', { ids: ids } );
				}
			} );
		}
	}

	/* ------------------------------------------------------------------ */
	/* Editor screen                                                      */
	/* ------------------------------------------------------------------ */
	function initEditor() {
		var $editor = $( '#ao-so-editor' );
		if ( ! $editor.length ) {
			return;
		}
		var templateId = $editor.data( 'id' );

		// Color pickers.
		if ( $.fn.wpColorPicker ) {
			$( '.ao-so-color' ).wpColorPicker();
		}

		// Tabs.
		$( '.ao-so-tab' ).on( 'click', function () {
			var tab = $( this ).data( 'tab' );
			$( '.ao-so-tab' ).removeClass( 'is-active' );
			$( this ).addClass( 'is-active' );
			$( '.ao-so-panel' ).removeClass( 'is-active' );
			$( '.ao-so-panel[data-panel="' + tab + '"]' ).addClass( 'is-active' );
		} );

		// Source-type dependent visibility.
		function syncSourceDeps() {
			var type = $( 'input[name="config[source][type]"]:checked' ).val() || 'manual';
			$( '.ao-so-dep[data-dep-type]' ).each( function () {
				var allowed = ( $( this ).data( 'dep-type' ) + '' ).split( ',' );
				$( this ).toggle( allowed.indexOf( type ) !== -1 );
			} );
		}
		$editor.on( 'change', 'input[name="config[source][type]"]', syncSourceDeps );
		syncSourceDeps();

		// Timer-type dependent visibility.
		function syncTimerDeps() {
			var t = $( 'input[name="config[timer_type]"]:checked' ).val();
			$( '.ao-so-dep[data-dep-timer]' ).each( function () {
				$( this ).toggle( $( this ).data( 'dep-timer' ) === t );
			} );
		}
		$editor.on( 'change', 'input[name="config[timer_type]"]', syncTimerDeps );
		syncTimerDeps();

		// Sortable selected products.
		if ( $.fn.sortable ) {
			$( '#ao-so-selected' ).sortable( { handle: '.ao-so-drag', axis: 'y' } );
		}

		// Helpers to build a selected product row.
		function selExists( id ) {
			return $( '#ao-so-selected li[data-id="' + id + '"]' ).length > 0;
		}
		function addSelected( p ) {
			if ( selExists( p.id ) ) {
				return false;
			}
			var $li = $(
				'<li data-id="' + esc( p.id ) + '">' +
					'<span class="dashicons dashicons-menu ao-so-drag"></span>' +
					'<img src="' + esc( p.image ) + '" alt="">' +
					'<span class="ao-so-sel-name">' + esc( p.name ) + '</span>' +
					'<input type="hidden" name="config[source][product_ids][]" value="' + esc( p.id ) + '">' +
					'<button type="button" class="ao-so-sel-remove"><span class="dashicons dashicons-no-alt"></span></button>' +
				'</li>'
			);
			$( '#ao-so-selected' ).append( $li );
			return true;
		}

		$editor.on( 'click', '.ao-so-sel-remove', function () {
			if ( window.confirm( i18n.confirmRemove ) ) {
				$( this ).closest( 'li' ).remove();
			}
		} );

		// Load on-sale products (reuse core endpoint).
		$( '#ao-so-load-sale' ).on( 'click', function () {
			var $btn = $( this ).prop( 'disabled', true );
			$( '#ao-so-sale-results' ).html( '<p>' + esc( i18n.loading ) + '</p>' );
			post( 'amazing_offer_load_sale_products', { limit: 30 } )
				.done( function ( res ) {
					if ( ! res.success || ! res.data.products.length ) {
						$( '#ao-so-sale-results' ).html( '<p>' + esc( i18n.noResults ) + '</p>' );
						return;
					}
					var html = '';
					res.data.products.forEach( function ( p ) {
						html += '<button type="button" class="ao-so-sale-item" data-product=\'' + esc( JSON.stringify( p ) ) + '\'>' +
							'<img src="' + esc( p.image ) + '" alt=""><span>' + esc( p.name ) + '</span></button>';
					} );
					$( '#ao-so-sale-results' ).html( html );
				} )
				.always( function () { $btn.prop( 'disabled', false ); } );
		} );
		$editor.on( 'click', '.ao-so-sale-item', function () {
			var p = $( this ).data( 'product' );
			if ( p ) { addSelected( p ); }
			$( this ).prop( 'disabled', true ).css( 'opacity', 0.5 );
		} );

		// Live search (reuse core endpoint).
		var searchTimer = null;
		$( '#ao-so-search' ).on( 'keyup', function () {
			var kw = $.trim( this.value );
			clearTimeout( searchTimer );
			if ( kw.length < 2 ) {
				$( '#ao-so-search-results' ).removeClass( 'is-open' ).empty();
				return;
			}
			searchTimer = setTimeout( function () {
				post( 'amazing_offer_search_products', { keyword: kw } ).done( function ( res ) {
					if ( ! res.success || ! res.data.products.length ) {
						$( '#ao-so-search-results' ).addClass( 'is-open' ).html( '<div class="ao-so-search-item">' + esc( i18n.noResults ) + '</div>' );
						return;
					}
					var html = '';
					res.data.products.forEach( function ( p ) {
						html += '<div class="ao-so-search-item" data-product=\'' + esc( JSON.stringify( p ) ) + '\'>' +
							'<img src="' + esc( p.image ) + '" alt=""><span>' + esc( p.name ) + '</span></div>';
					} );
					$( '#ao-so-search-results' ).addClass( 'is-open' ).html( html );
				} );
			}, 300 );
		} );
		$editor.on( 'click', '.ao-so-search-item[data-product]', function () {
			var p = $( this ).data( 'product' );
			if ( p && ! addSelected( p ) ) {
				window.alert( i18n.alreadyAdded );
			}
			$( '#ao-so-search-results' ).removeClass( 'is-open' ).empty();
			$( '#ao-so-search' ).val( '' );
		} );
		$( document ).on( 'click', function ( e ) {
			if ( ! $( e.target ).closest( '.ao-so-search-wrap' ).length ) {
				$( '#ao-so-search-results' ).removeClass( 'is-open' );
			}
		} );

		// Banner media picker.
		$( '#ao-so-banner-pick' ).on( 'click', function ( e ) {
			e.preventDefault();
			if ( ! window.wp || ! window.wp.media ) {
				return;
			}
			var frame = window.wp.media( { title: i18n.selectMedia, button: { text: i18n.useImage }, multiple: false } );
			frame.on( 'select', function () {
				var att = frame.state().get( 'selection' ).first().toJSON();
				$( '#ao-so-banner-id' ).val( att.id );
				$( '#ao-so-banner-url' ).val( att.url );
			} );
			frame.open();
		} );

		// Device preview toggle (preview itself wired in Phase 4).
		$( '.ao-so-device' ).on( 'click', function () {
			$( '.ao-so-device' ).removeClass( 'is-active' );
			$( this ).addClass( 'is-active' );
			$( '#ao-so-preview' ).attr( 'data-device', $( this ).data( 'device' ) );
		} );

		// Save.
		$( '#ao-so-save' ).on( 'click', function () {
			var $btn = $( this ).prop( 'disabled', true );
			var $fb = $( '.ao-so-save-feedback' ).text( i18n.loading );
			var data = $( '#ao-so-form' ).serialize() +
				'&action=ao_so_save&nonce=' + encodeURIComponent( cfg.nonce ) +
				'&id=' + encodeURIComponent( templateId ) +
				'&title=' + encodeURIComponent( $( '#ao-so-title' ).val() );
			$.post( cfg.ajaxUrl, data )
				.done( function ( res ) {
					$fb.text( ( res.success && res.data.message ) ? res.data.message : ( ( res.data && res.data.message ) || i18n.error ) );
				} )
				.fail( function () { $fb.text( i18n.error ); } )
				.always( function () {
					$btn.prop( 'disabled', false );
					setTimeout( function () { $fb.text( '' ); }, 3000 );
				} );
		} );
	}

	$( function () {
		initList();
		initEditor();
	} );

} )( jQuery );
