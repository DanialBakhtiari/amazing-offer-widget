/**
 * Special Offer — front-end Swiper init, countdown timer, and add-to-cart.
 *
 * Scoped to .ao-so-* nodes only; never touches legacy [amazing_offer] output.
 */
( function () {
	'use strict';

	var DATA = window.amazingOfferSOData || {};
	var I18N = DATA.i18n || {};

	var reduceMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	/**
	 * Initialize one Special Offer Swiper instance.
	 *
	 * @param {HTMLElement} el The .ao-so-slider.swiper node.
	 * @return {void}
	 */
	function initSlider( el ) {
		if ( ! window.Swiper || el.classList.contains( 'ao-so-ready' ) ) {
			return;
		}
		el.classList.add( 'ao-so-ready' );

		var cfg = {};
		try {
			cfg = JSON.parse( el.getAttribute( 'data-swiper' ) || '{}' );
		} catch ( e ) {
			cfg = {};
		}

		var cards = cfg.cards || { mobile: 1, tablet: 2, desktop: 3 };
		var gap = parseInt( cfg.gap, 10 ) || 16;
		var effect = reduceMotion ? 'slide' : ( cfg.effect || 'slide' );
		var single = ( effect === 'fade' || effect === 'cards' );

		var resp = cfg.responsive || { mobile: {}, tablet: {} };
		var mob = resp.mobile || {};
		var tab = resp.tablet || {};

		var options = {
			effect: effect,
			speed: reduceMotion ? 0 : ( parseInt( cfg.speed, 10 ) || 600 ),
			loop: !! cfg.loop,
			spaceBetween: gap,
			// Key breakpoints off the container width so the admin device
			// preview (constrained width) shows real per-device output.
			breakpointsBase: 'container',
			a11y: {
				enabled: true,
				prevSlideMessage: I18N.prev || 'Previous',
				nextSlideMessage: I18N.next || 'Next'
			},
			keyboard: { enabled: true }
		};

		if ( single ) {
			options.slidesPerView = 1;
		} else {
			options.breakpoints = {
				0: {
					slidesPerView: mob.cards || cards.mobile || 1,
					spaceBetween: ( mob.gap != null ) ? mob.gap : gap
				},
				600: {
					slidesPerView: tab.cards || cards.tablet || 2,
					spaceBetween: ( tab.gap != null ) ? tab.gap : gap
				},
				1024: {
					slidesPerView: cards.desktop || 3,
					spaceBetween: gap
				}
			};
		}

		if ( effect === 'fade' ) {
			options.fadeEffect = { crossFade: true };
		} else if ( effect === 'coverflow' ) {
			options.centeredSlides = true;
			options.coverflowEffect = { rotate: 30, depth: 120, modifier: 1, slideShadows: false };
		} else if ( effect === 'cards' ) {
			options.cardsEffect = { perSlideOffset: 8, perSlideRotate: 2 };
		}

		if ( cfg.autoplay && ! reduceMotion ) {
			options.autoplay = {
				delay: parseInt( cfg.speed, 10 ) || 3000,
				disableOnInteraction: false,
				pauseOnMouseEnter: !! cfg.pauseOnHover
			};
		}

		if ( cfg.dots ) {
			options.pagination = { el: el.querySelector( '.swiper-pagination' ), clickable: true };
		}
		if ( cfg.nav ) {
			options.navigation = {
				prevEl: el.querySelector( '.swiper-button-prev' ),
				nextEl: el.querySelector( '.swiper-button-next' )
			};
		}

		// eslint-disable-next-line no-new
		new window.Swiper( el, options );
	}

	/**
	 * Drive a countdown timer element.
	 *
	 * @param {HTMLElement} el Timer container.
	 * @return {void}
	 */
	function initTimer( el ) {
		if ( el.classList.contains( 'ao-so-timer-ready' ) ) {
			return;
		}
		el.classList.add( 'ao-so-timer-ready' );

		var cfg = {};
		try {
			cfg = JSON.parse( el.getAttribute( 'data-timer' ) || '{}' );
		} catch ( e ) {
			return;
		}

		var target = resolveTarget( cfg );
		if ( ! target ) {
			return;
		}

		var nums = {
			d: el.querySelector( '.ao-so-days' ),
			h: el.querySelector( '.ao-so-hours' ),
			m: el.querySelector( '.ao-so-minutes' ),
			s: el.querySelector( '.ao-so-seconds' )
		};

		function pad( n ) { return ( n < 10 ? '0' : '' ) + n; }

		function tick() {
			var diff = Math.max( 0, target - Date.now() );
			var sec = Math.floor( diff / 1000 );
			if ( nums.d ) { nums.d.textContent = pad( Math.floor( sec / 86400 ) ); }
			if ( nums.h ) { nums.h.textContent = pad( Math.floor( ( sec % 86400 ) / 3600 ) ); }
			if ( nums.m ) { nums.m.textContent = pad( Math.floor( ( sec % 3600 ) / 60 ) ); }
			if ( nums.s ) { nums.s.textContent = pad( sec % 60 ); }
			if ( diff <= 0 ) { clearInterval( iv ); }
		}

		tick();
		var iv = setInterval( tick, 1000 );
	}

	/**
	 * Resolve a timer target timestamp (ms).
	 *
	 * @param {Object} cfg Timer config.
	 * @return {number}    Target or 0.
	 */
	function resolveTarget( cfg ) {
		var now = new Date();
		if ( cfg.type === 'fixed_date' && cfg.endDate ) {
			var t = new Date( cfg.endDate ).getTime();
			return isNaN( t ) ? 0 : t;
		}
		if ( cfg.type === 'duration' ) {
			var hours = parseInt( cfg.duration, 10 ) || 24;
			var key = 'amazingOfferSOTimerStart';
			var start = null;
			try { start = window.localStorage.getItem( key ); } catch ( e ) { start = null; }
			if ( ! start ) {
				start = Date.now();
				try { window.localStorage.setItem( key, start ); } catch ( e2 ) {}
			}
			return parseInt( start, 10 ) + hours * 3600 * 1000;
		}
		return new Date( now.getFullYear(), now.getMonth(), now.getDate() + 1, 0, 0, 0, 0 ).getTime();
	}

	/**
	 * Add a product to the WooCommerce cart via AJAX.
	 *
	 * @param {string|number} productId Product id.
	 * @param {HTMLElement}   button    Trigger button.
	 * @return {void}
	 */
	function addToCart( productId, button ) {
		if ( ! DATA.ajaxUrl || button.classList.contains( 'is-loading' ) ) {
			return;
		}
		button.classList.add( 'is-loading' );

		var body = new URLSearchParams();
		body.append( 'action', 'amazing_offer_add_to_cart' );
		body.append( 'nonce', DATA.nonce );
		body.append( 'product_id', productId );
		body.append( 'quantity', 1 );

		fetch( DATA.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString()
		} )
			.then( function ( r ) { return r.json(); } )
			.then( function ( res ) {
				button.classList.remove( 'is-loading' );
				var label = button.querySelector( '.ao-so-btn-text' );
				if ( res && res.success ) {
					button.classList.add( 'is-success' );
					if ( label ) { label.textContent = I18N.added || '✓'; }
					if ( window.jQuery ) { window.jQuery( document.body ).trigger( 'wc_fragment_refresh' ); }
					document.body.dispatchEvent( new CustomEvent( 'amazing_offer_added_to_cart', { detail: res.data } ) );
					setTimeout( function () {
						button.classList.remove( 'is-success' );
						if ( label && button.getAttribute( 'data-label' ) ) { label.textContent = button.getAttribute( 'data-label' ); }
					}, 2200 );
				} else if ( label ) {
					label.textContent = ( res && res.data && res.data.message ) || I18N.error || 'Error';
				}
			} )
			.catch( function () { button.classList.remove( 'is-loading' ); } );
	}

	/**
	 * Initialize every Special Offer node within a root.
	 *
	 * @param {ParentNode} root Scope.
	 * @return {void}
	 */
	function boot( root ) {
		root = root || document;
		root.querySelectorAll( '.ao-so-slider.swiper' ).forEach( initSlider );
		root.querySelectorAll( '.ao-so-timer' ).forEach( initTimer );
	}

	document.addEventListener( 'click', function ( e ) {
		var btn = e.target.closest( '.ao-so-add-to-cart' );
		if ( btn && ! btn.disabled ) {
			var label = btn.querySelector( '.ao-so-btn-text' );
			if ( label && ! btn.getAttribute( 'data-label' ) ) {
				btn.setAttribute( 'data-label', label.textContent );
			}
			addToCart( btn.getAttribute( 'data-product-id' ), btn );
		}
	} );

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', function () { boot( document ); } );
	} else {
		boot( document );
	}

	// Elementor editor preview re-init.
	if ( window.jQuery ) {
		window.jQuery( window ).on( 'elementor/frontend/init', function () {
			if ( window.elementorFrontend && window.elementorFrontend.hooks ) {
				window.elementorFrontend.hooks.addAction(
					'frontend/element_ready/amazing-offer-special.default',
					function ( $scope ) { boot( $scope[ 0 ] ); }
				);
			}
		} );
	}

	// Expose for the admin live-preview to re-init after AJAX re-render.
	window.amazingOfferSOBoot = boot;

} )();
