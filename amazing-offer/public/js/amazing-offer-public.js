/**
 * Amazing Offer — front-end slider, timer, and add-to-cart.
 *
 * Vanilla JS, no external dependencies.
 */
( function () {
	'use strict';

	var DATA = window.amazingOfferData || {};
	var I18N = DATA.i18n || {};

	/**
	 * Responsive slider with auto-play, swipe, nav and dots.
	 */
	function AmazingOfferSlider( element ) {
		this.root = element;
		this.track = element.querySelector( '.amazing-offer-track' );
		this.cards = Array.prototype.slice.call( element.querySelectorAll( '.amazing-offer-card' ) );
		this.wrapper = element.closest( '.amazing-offer-wrapper' );

		var cfg = {};
		try {
			cfg = JSON.parse( element.getAttribute( 'data-slider' ) || '{}' );
		} catch ( e ) {
			cfg = {};
		}
		this.cfg = cfg;
		this.mode = cfg.mode || 'auto';
		this.index = 0;
		this.timer = null;
		this.perView = this.computePerView();

		this.init();
	}

	AmazingOfferSlider.prototype.init = function () {
		if ( ! this.track || ! this.cards.length ) {
			return;
		}

		if ( this.mode === 'grid' ) {
			this.layoutGrid();
			return;
		}

		this.layout();
		this.buildDots();
		this.bindEvents();
		this.update();

		if ( this.mode === 'auto' ) {
			this.autoPlay();
		}
	};

	/**
	 * Number of cards visible at the current viewport width.
	 *
	 * @return {number} Cards per view.
	 */
	AmazingOfferSlider.prototype.computePerView = function () {
		var c = this.cfg.cards || { mobile: 1, tablet: 2, desktop: 3 };
		var w = window.innerWidth;
		if ( w <= 600 ) {
			return Math.max( 1, c.mobile || 1 );
		}
		if ( w <= 1024 ) {
			return Math.max( 1, c.tablet || 2 );
		}
		return Math.max( 1, c.desktop || 3 );
	};

	/**
	 * Size each card to the current per-view value.
	 *
	 * @return {void}
	 */
	AmazingOfferSlider.prototype.layout = function () {
		this.perView = this.computePerView();
		var gap = 16;
		var basis = 'calc((100% - ' + ( ( this.perView - 1 ) * gap ) + 'px) / ' + this.perView + ')';
		this.cards.forEach( function ( card ) {
			card.style.flexBasis = basis;
			card.style.maxWidth = basis;
		} );
		this.maxIndex = Math.max( 0, this.cards.length - this.perView );
		if ( this.index > this.maxIndex ) {
			this.index = this.maxIndex;
		}
	};

	/**
	 * Grid layout (no sliding).
	 *
	 * @return {void}
	 */
	AmazingOfferSlider.prototype.layoutGrid = function () {
		var per = this.computePerView();
		var gap = 16;
		var basis = 'calc((100% - ' + ( ( per - 1 ) * gap ) + 'px) / ' + per + ')';
		this.cards.forEach( function ( card ) {
			card.style.flexBasis = basis;
			card.style.maxWidth = basis;
		} );
	};

	/**
	 * Build pagination dots.
	 *
	 * @return {void}
	 */
	AmazingOfferSlider.prototype.buildDots = function () {
		this.dotsWrap = this.wrapper ? this.wrapper.querySelector( '.amazing-offer-dots' ) : null;
		if ( ! this.dotsWrap ) {
			return;
		}
		this.dotsWrap.innerHTML = '';
		var pages = this.maxIndex + 1;
		var self = this;
		for ( var i = 0; i < pages; i++ ) {
			( function ( idx ) {
				var b = document.createElement( 'button' );
				b.type = 'button';
				b.setAttribute( 'aria-label', 'slide ' + ( idx + 1 ) );
				b.addEventListener( 'click', function () {
					self.goTo( idx );
					self.restart();
				} );
				self.dotsWrap.appendChild( b );
			} )( i );
		}
	};

	/**
	 * Wire nav, swipe, hover and resize.
	 *
	 * @return {void}
	 */
	AmazingOfferSlider.prototype.bindEvents = function () {
		var self = this;

		var prev = this.wrapper ? this.wrapper.querySelector( '.amazing-offer-prev' ) : null;
		var next = this.wrapper ? this.wrapper.querySelector( '.amazing-offer-next' ) : null;

		// RTL: visual "prev" (right arrow) moves toward earlier slides.
		if ( prev ) {
			prev.addEventListener( 'click', function () { self.prev(); self.restart(); } );
		}
		if ( next ) {
			next.addEventListener( 'click', function () { self.next(); self.restart(); } );
		}

		if ( this.cfg.pauseOnHover && this.mode === 'auto' ) {
			this.root.addEventListener( 'mouseenter', function () { self.stop(); } );
			this.root.addEventListener( 'mouseleave', function () { self.autoPlay(); } );
		}

		// Pointer swipe.
		this.startX = 0;
		this.dragging = false;

		this.root.addEventListener( 'pointerdown', function ( e ) {
			self.dragging = true;
			self.startX = e.clientX;
			self.stop();
		} );
		window.addEventListener( 'pointerup', function ( e ) {
			if ( ! self.dragging ) {
				return;
			}
			self.dragging = false;
			var diff = e.clientX - self.startX;
			if ( Math.abs( diff ) > 40 ) {
				// RTL: drag right -> previous, drag left -> next.
				if ( diff > 0 ) {
					self.prev();
				} else {
					self.next();
				}
				self.restart();
			} else if ( self.mode === 'auto' ) {
				self.autoPlay();
			}
		} );

		// Resize relayout (debounced).
		var rt = null;
		window.addEventListener( 'resize', function () {
			clearTimeout( rt );
			rt = setTimeout( function () {
				self.layout();
				self.buildDots();
				self.update();
			}, 200 );
		} );
	};

	/**
	 * Apply current index transform and sync dots/nav.
	 *
	 * @return {void}
	 */
	AmazingOfferSlider.prototype.update = function () {
		var card = this.cards[ 0 ];
		if ( ! card ) {
			return;
		}
		var step = card.getBoundingClientRect().width + 16;
		// RTL track moves to the right (positive translateX).
		this.track.style.transform = 'translateX(' + ( this.index * step ) + 'px)';

		if ( this.dotsWrap ) {
			var dots = this.dotsWrap.querySelectorAll( 'button' );
			for ( var i = 0; i < dots.length; i++ ) {
				dots[ i ].classList.toggle( 'is-active', i === this.index );
			}
		}
	};

	AmazingOfferSlider.prototype.goTo = function ( i ) {
		var loop = !! this.cfg.loop;
		if ( i < 0 ) {
			i = loop ? this.maxIndex : 0;
		} else if ( i > this.maxIndex ) {
			i = loop ? 0 : this.maxIndex;
		}
		this.index = i;
		this.update();
	};

	AmazingOfferSlider.prototype.next = function () {
		this.goTo( this.index + 1 );
	};

	AmazingOfferSlider.prototype.prev = function () {
		this.goTo( this.index - 1 );
	};

	AmazingOfferSlider.prototype.autoPlay = function () {
		var self = this;
		this.stop();
		var speed = parseInt( this.cfg.speed, 10 ) || 3000;
		this.timer = setInterval( function () {
			self.next();
		}, speed );
	};

	AmazingOfferSlider.prototype.stop = function () {
		if ( this.timer ) {
			clearInterval( this.timer );
			this.timer = null;
		}
	};

	AmazingOfferSlider.prototype.restart = function () {
		if ( this.mode === 'auto' ) {
			this.autoPlay();
		}
	};

	AmazingOfferSlider.prototype.destroy = function () {
		this.stop();
	};

	/* ------------------------------------------------------------------ */
	/* Countdown timer                                                    */
	/* ------------------------------------------------------------------ */

	/**
	 * Drive a single countdown timer element.
	 *
	 * @param {HTMLElement} el Timer container.
	 * @return {void}
	 */
	function setupTimer( el ) {
		var cfg = {};
		try {
			cfg = JSON.parse( el.getAttribute( 'data-timer' ) || '{}' );
		} catch ( e ) {
			return;
		}

		var target = resolveTarget( cfg, el );
		if ( ! target ) {
			return;
		}

		var nums = {
			d: el.querySelector( '.ao-days' ),
			h: el.querySelector( '.ao-hours' ),
			m: el.querySelector( '.ao-minutes' ),
			s: el.querySelector( '.ao-seconds' )
		};

		function pad( n ) {
			return ( n < 10 ? '0' : '' ) + n;
		}

		function tick() {
			var diff = target - Date.now();
			if ( diff <= 0 ) {
				diff = 0;
			}
			var sec = Math.floor( diff / 1000 );
			var d = Math.floor( sec / 86400 );
			var h = Math.floor( ( sec % 86400 ) / 3600 );
			var m = Math.floor( ( sec % 3600 ) / 60 );
			var s = sec % 60;

			if ( nums.d ) { nums.d.textContent = pad( d ); }
			if ( nums.h ) { nums.h.textContent = pad( h ); }
			if ( nums.m ) { nums.m.textContent = pad( m ); }
			if ( nums.s ) { nums.s.textContent = pad( s ); }

			if ( diff <= 0 ) {
				clearInterval( iv );
			}
		}

		tick();
		var iv = setInterval( tick, 1000 );
	}

	/**
	 * Resolve a timer's target timestamp (ms).
	 *
	 * @param {Object}      cfg Timer config.
	 * @param {HTMLElement} el  Timer element (for per-visitor storage key).
	 * @return {number}         Target timestamp or 0.
	 */
	function resolveTarget( cfg, el ) {
		var now = new Date();

		if ( cfg.type === 'fixed_date' && cfg.endDate ) {
			var t = new Date( cfg.endDate ).getTime();
			return isNaN( t ) ? 0 : t;
		}

		if ( cfg.type === 'duration' ) {
			var hours = parseInt( cfg.duration, 10 ) || 24;
			var key = 'amazingOfferTimerStart';
			var start = null;
			try {
				start = window.localStorage.getItem( key );
			} catch ( e ) {
				start = null;
			}
			if ( ! start ) {
				start = Date.now();
				try {
					window.localStorage.setItem( key, start );
				} catch ( e2 ) {}
			}
			return parseInt( start, 10 ) + hours * 3600 * 1000;
		}

		// Default: midnight today.
		var midnight = new Date( now.getFullYear(), now.getMonth(), now.getDate() + 1, 0, 0, 0, 0 );
		return midnight.getTime();
	}

	/* ------------------------------------------------------------------ */
	/* Add to cart                                                        */
	/* ------------------------------------------------------------------ */

	/**
	 * Add a product to the WooCommerce cart via AJAX.
	 *
	 * @param {number}      productId Product id.
	 * @param {HTMLElement} button    Trigger button.
	 * @return {void}
	 */
	function amazingOfferAddToCart( productId, button ) {
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
				var label = button.querySelector( '.ao-btn-text' );
				if ( res && res.success ) {
					button.classList.add( 'is-success' );
					if ( label ) {
						label.textContent = I18N.added || 'افزوده شد ✓';
					}
					document.body.dispatchEvent(
						new CustomEvent( 'amazing_offer_added_to_cart', { detail: res.data } )
					);
					// Trigger WooCommerce fragments refresh if present.
					if ( window.jQuery ) {
						window.jQuery( document.body ).trigger( 'wc_fragment_refresh' );
					}
					setTimeout( function () {
						button.classList.remove( 'is-success' );
						if ( label ) {
							label.textContent = button.getAttribute( 'data-label' ) || label.textContent;
						}
					}, 2200 );
				} else {
					if ( label ) {
						label.textContent = ( res && res.data && res.data.message ) || I18N.error || 'خطا';
					}
				}
			} )
			.catch( function () {
				button.classList.remove( 'is-loading' );
			} );
	}

	/* ------------------------------------------------------------------ */
	/* Bootstrap                                                          */
	/* ------------------------------------------------------------------ */

	function boot() {
		document.querySelectorAll( '.amazing-offer-slider' ).forEach( function ( el ) {
			new AmazingOfferSlider( el );
		} );

		document.querySelectorAll( '.amazing-offer-timer' ).forEach( function ( el ) {
			setupTimer( el );
		} );

		document.addEventListener( 'click', function ( e ) {
			var btn = e.target.closest( '.amazing-offer-add-to-cart' );
			if ( btn && ! btn.disabled ) {
				var label = btn.querySelector( '.ao-btn-text' );
				if ( label && ! btn.getAttribute( 'data-label' ) ) {
					btn.setAttribute( 'data-label', label.textContent );
				}
				amazingOfferAddToCart( btn.getAttribute( 'data-product-id' ), btn );
			}
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}

	// Re-init inside Elementor editor preview.
	if ( window.elementorFrontend ) {
		window.jQuery( window ).on( 'elementor/frontend/init', function () {
			if ( window.elementorFrontend.hooks ) {
				window.elementorFrontend.hooks.addAction(
					'frontend/element_ready/amazing-offer-widget.default',
					function ( $scope ) {
						var el = $scope[ 0 ].querySelector( '.amazing-offer-slider' );
						if ( el ) {
							new AmazingOfferSlider( el );
						}
						var tm = $scope[ 0 ].querySelector( '.amazing-offer-timer' );
						if ( tm ) {
							setupTimer( tm );
						}
					}
				);
			}
		} );
	}

	// Expose for debugging/extension.
	window.AmazingOfferSlider = AmazingOfferSlider;

} )();
