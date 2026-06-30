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

		// Import from JSON file.
		$( '#ao-so-import-btn' ).on( 'click', function () {
			$( '#ao-so-import-file' ).trigger( 'click' );
		} );
		$( '#ao-so-import-file' ).on( 'change', function () {
			var file = this.files && this.files[ 0 ];
			if ( ! file ) {
				return;
			}
			var fd = new FormData();
			fd.append( 'action', 'ao_so_import' );
			fd.append( 'nonce', cfg.nonce );
			fd.append( 'file', file );
			$.ajax( {
				url: cfg.ajaxUrl,
				method: 'POST',
				data: fd,
				processData: false,
				contentType: false
			} ).done( function ( res ) {
				if ( res.success ) {
					window.location.href = cfg.editUrl + res.data.id;
				} else {
					window.alert( ( res.data && res.data.message ) || i18n.error );
				}
			} ).fail( function () {
				window.alert( i18n.error );
			} );
			this.value = '';
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

		// Color pickers (instant cosmetic + debounced re-render on change).
		if ( $.fn.wpColorPicker ) {
			$( '.ao-so-color' ).wpColorPicker( {
				change: function () { setTimeout( function () { applyCosmetic(); renderPreview(); }, 20 ); },
				clear: function () { setTimeout( function () { applyCosmetic(); renderPreview(); }, 20 ); }
			} );
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
			$( '#ao-so-selected' ).sortable( { handle: '.ao-so-drag', axis: 'y', update: function () { renderPreview(); } } );
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
			renderPreview();
			return true;
		}

		$editor.on( 'click', '.ao-so-sel-remove', function () {
			if ( window.confirm( i18n.confirmRemove ) ) {
				$( this ).closest( 'li' ).remove();
				renderPreview();
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
					// Build elements and attach the product via jQuery .data()
					// (NOT a DOM attribute) so product names can never break out.
					var $wrap = $( '#ao-so-sale-results' ).empty();
					res.data.products.forEach( function ( p ) {
						var $b = $( '<button type="button" class="ao-so-sale-item"><img alt=""><span></span></button>' );
						$b.find( 'img' ).attr( 'src', p.image );
						$b.find( 'span' ).text( p.name );
						$b.data( 'product', p );
						$wrap.append( $b );
					} );
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
					var $results = $( '#ao-so-search-results' ).empty().addClass( 'is-open' );
					res.data.products.forEach( function ( p ) {
						var $it = $( '<div class="ao-so-search-item"><img alt=""><span></span></div>' );
						$it.find( 'img' ).attr( 'src', p.image );
						$it.find( 'span' ).text( p.name );
						$it.data( 'product', p );
						$results.append( $it );
					} );
				} );
			}, 300 );
		} );
		$editor.on( 'click', '.ao-so-search-item', function () {
			var p = $( this ).data( 'product' );
			if ( ! p ) {
				return; // the "no results" row carries no product data.
			}
			if ( ! addSelected( p ) ) {
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

		// Device preview toggle: change container width + re-render so Swiper
		// recomputes its container-based breakpoints for that device.
		$( '.ao-so-device' ).on( 'click', function () {
			$( '.ao-so-device' ).removeClass( 'is-active' );
			$( this ).addClass( 'is-active' );
			$( '#ao-so-preview' ).attr( 'data-device', $( this ).data( 'device' ) );
			renderPreview();
		} );

		/* ----- Live preview engine (two-tier: instant cosmetic + debounced server render) ----- */
		var previewTimer = null;

		function fval( name ) {
			var $f = $( '[name="' + name + '"]' );
			return $f.length ? $f.val() : '';
		}

		function applyCosmetic() {
			var w = $( '#ao-so-preview .ao-so-wrapper' )[ 0 ];
			if ( ! w ) {
				return;
			}
			var vars = {
				'--ao-primary': fval( 'config[button_color]' ),
				'--ao-primary-hover': fval( 'config[button_hover_color]' ),
				'--ao-badge': fval( 'config[badge_color]' ),
				'--ao-card-bg': fval( 'config[style][card_bg]' ),
				'--ao-radius': ( parseInt( fval( 'config[style][radius]' ), 10 ) || 0 ) + 'px',
				'--ao-gap': ( parseInt( fval( 'config[style][gap]' ), 10 ) || 0 ) + 'px'
			};
			Object.keys( vars ).forEach( function ( k ) {
				if ( vars[ k ] !== '' && vars[ k ] != null ) {
					w.style.setProperty( k, vars[ k ] );
				}
			} );
			var sect = fval( 'config[style][section_bg]' );
			if ( sect ) {
				w.style.setProperty( '--ao-section-bg', sect );
			}
			var btn = fval( 'config[cart_button_text]' );
			if ( btn ) {
				$( '#ao-so-preview .ao-so-btn-text' ).text( btn );
			}
			$( '#ao-so-preview .ao-so-title' ).css( 'color', fval( 'config[title_color]' ) || '' );
			$( '#ao-so-preview .ao-so-subtitle' ).css( 'color', fval( 'config[subtitle_color]' ) || '' );
		}

		// Recommend a banner image size from the ACTUAL rendered layout so the
		// chosen image matches the card area and is not deformed.
		function ratioStr( w, h ) {
			function gcd( a, b ) { return b ? gcd( b, a % b ) : a; }
			var g = gcd( w, h ) || 1;
			var rw = Math.round( w / g ), rh = Math.round( h / g );
			if ( rw > 21 || rh > 21 ) {
				return ( Math.round( ( w / h ) * 100 ) / 100 ) + ' : 1';
			}
			return rw + ' : ' + rh;
		}

		function updateBannerRec() {
			var $rec = $( '#ao-so-banner-rec' );
			if ( ! $rec.length ) {
				return;
			}
			var pos = $( 'input[name="config[banner][position]"]:checked' ).val() || 'hidden';
			if ( 'hidden' === pos ) {
				$rec.text( i18n.bannerHidden || '' );
				return;
			}
			var w, h;
			var bannerEl = $( '#ao-so-preview .ao-so-banner' )[ 0 ];
			if ( bannerEl ) {
				var r = bannerEl.getBoundingClientRect();
				w = Math.round( r.width );
				h = Math.round( r.height );
			} else {
				var area = $( '#ao-so-preview .ao-so-body' )[ 0 ] || $( '#ao-so-preview .ao-so-slider' )[ 0 ] || $( '#ao-so-preview .ao-so-wrapper' )[ 0 ];
				if ( ! area ) { $rec.text( '' ); return; }
				var ar = area.getBoundingClientRect();
				if ( 'top' === pos ) { w = Math.round( ar.width ); h = Math.round( ar.width * 0.22 ); }
				else { w = Math.round( ar.width * 0.28 ); h = Math.round( ar.height ); }
			}
			if ( w <= 0 || h <= 0 ) { $rec.text( '' ); return; }
			// Suggest 2x for retina/quality.
			var rw = w * 2, rh = h * 2;
			$rec.html(
				( i18n.bannerRec || '' ) + ' <b>' + rw + ' × ' + rh + '</b> ' + ( i18n.bannerPx || '' ) +
				' &nbsp;(' + ( i18n.bannerRatio || '' ) + ' ' + ratioStr( w, h ) + ')'
			);
		}

		function doRenderPreview() {
			var $stage = $( '#ao-so-preview' );
			var data = $( '#ao-so-form' ).serialize() +
				'&action=ao_so_preview&nonce=' + encodeURIComponent( cfg.nonce ) +
				'&id=' + encodeURIComponent( templateId );
			$.post( cfg.ajaxUrl, data ).done( function ( res ) {
				if ( res && res.success ) {
					$stage.html( res.data.html );
					if ( window.amazingOfferSOBoot ) {
						window.amazingOfferSOBoot( $stage[ 0 ] );
					}
					applyCosmetic();
					setTimeout( updateBannerRec, 60 );
				}
			} );
		}

		function renderPreview() {
			clearTimeout( previewTimer );
			previewTimer = setTimeout( doRenderPreview, 300 );
		}

		// Instant cosmetic patches (sub-frame, no server round-trip).
		$editor.on( 'input',
			'input[name="config[style][radius]"], input[name="config[style][gap]"], input[name="config[cart_button_text]"], input[name="config[style][card_bg]"], input[name="config[style][section_bg]"]',
			applyCosmetic
		);
		// Debounced structural re-render for everything else.
		$editor.on( 'change', '#ao-so-form input, #ao-so-form select, #ao-so-form textarea', renderPreview );

		// First paint.
		doRenderPreview();

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
