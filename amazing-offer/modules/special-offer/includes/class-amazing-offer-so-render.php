<?php
/**
 * Special Offer renderer (Swiper-based), parallel to the legacy renderer.
 *
 * Mirrors the legacy card/timer markup but uses .ao-so-* classes and its own
 * enqueue action so the two slider engines never collide. Reuses the core
 * products manager for product data verbatim.
 *
 * @package Amazing_Offer\Special_Offer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renderer.
 */
class Amazing_Offer_SO_Render {

	/**
	 * Render a template by post id.
	 *
	 * @param int                    $post_id  Template id.
	 * @param Amazing_Offer_Settings $settings Core settings (defaults source).
	 * @param Amazing_Offer_Products $products Core products manager.
	 * @return string
	 */
	public static function render( $post_id, $settings, $products ) {
		$post = get_post( $post_id );
		if ( ! $post || Amazing_Offer_SO_CPT::POST_TYPE !== $post->post_type || 'publish' !== $post->post_status ) {
			return '';
		}

		$config = Amazing_Offer_SO_Schema::load( $post_id );
		return self::render_config( $config, $settings, $products );
	}

	/**
	 * Render from a config array directly (used by the live preview, which
	 * renders unsaved/draft config without the published-post guard).
	 *
	 * @param array                  $config   Normalized config.
	 * @param Amazing_Offer_Settings $settings Core settings.
	 * @param Amazing_Offer_Products $products Core products manager.
	 * @return string
	 */
	public static function render_config( array $config, $settings, $products ) {
		$items = self::get_products( $config, $products );

		// Signal the public controller to enqueue Swiper + module assets.
		do_action( 'amazing_offer_so_render' );

		if ( empty( $items ) ) {
			return '<div class="ao-so-empty">' . esc_html__( 'در حال حاضر پیشنهادی موجود نیست.', 'amazing-offer' ) . '</div>';
		}

		$effect    = in_array( $config['effect'], Amazing_Offer_SO_Schema::effects(), true ) ? $config['effect'] : 'slide';
		$is_grid   = ( 'grid' === $effect );
		$uid       = 'ao-so-' . wp_rand( 1000, 99999 );

		$swiper_data = array(
			'effect'       => $effect,
			'speed'        => (int) $config['slider_speed'],
			'autoplay'     => ( 'auto' === $config['slider_mode'] ),
			'loop'         => (bool) $config['slider_loop'],
			'pauseOnHover' => (bool) $config['pause_on_hover'],
			'nav'          => (bool) $config['show_nav'],
			'dots'         => (bool) $config['show_dots'],
			'cards'        => array(
				'mobile'  => (int) $config['slider_cards_mobile'],
				'tablet'  => (int) $config['slider_cards_tablet'],
				'desktop' => (int) $config['slider_cards_desktop'],
			),
			'gap'          => (int) $config['style']['gap'],
			'responsive'   => $config['responsive'],
		);

		$timer_data = array(
			'show'     => (bool) $config['show_timer'],
			'type'     => $config['timer_type'],
			'duration' => (int) $config['timer_duration'],
			'endDate'  => $config['timer_end_date'],
		);

		$style_vars = sprintf(
			'--ao-primary:%1$s;--ao-primary-hover:%2$s;--ao-badge:%3$s;--ao-radius:%4$dpx;--ao-gap:%5$dpx;--ao-card-bg:%6$s;%7$s',
			esc_attr( $config['button_color'] ),
			esc_attr( $config['button_hover_color'] ),
			esc_attr( $config['badge_color'] ),
			(int) $config['style']['radius'],
			(int) $config['style']['gap'],
			esc_attr( $config['style']['card_bg'] ),
			'' !== $config['style']['section_bg'] ? '--ao-section-bg:' . esc_attr( $config['style']['section_bg'] ) . ';' : ''
		);

		$banner   = $config['banner'];
		$has_left = ( 'right' === $banner['position'] || 'left' === $banner['position'] ) && ( $banner['image'] || $banner['image_id'] );

		ob_start();
		?>
		<div class="ao-so-wrapper ao-so-effect-<?php echo esc_attr( $effect ); ?> <?php echo $has_left ? 'ao-so-has-banner ao-so-banner-' . esc_attr( $banner['position'] ) : ''; ?>"
			dir="rtl" style="<?php echo $style_vars; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pieces escaped above. ?>">

			<?php if ( 'top' === $banner['position'] && ( $banner['image'] || $banner['image_id'] ) ) : ?>
				<?php echo self::render_banner( $banner ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped within. ?>
			<?php endif; ?>

			<div class="ao-so-header">
				<div class="ao-so-titles">
					<h2 class="ao-so-title">
						<?php if ( ! empty( $config['show_icon'] ) ) : ?>
							<span class="ao-so-icon dashicons dashicons-superhero-alt" aria-hidden="true"></span>
						<?php endif; ?>
						<?php echo esc_html( $config['title'] ); ?>
					</h2>
					<?php if ( ! empty( $config['subtitle'] ) ) : ?>
						<p class="ao-so-subtitle"><?php echo esc_html( $config['subtitle'] ); ?></p>
					<?php endif; ?>
				</div>

				<?php if ( ! empty( $config['show_timer'] ) ) : ?>
					<div class="ao-so-timer" data-timer="<?php echo esc_attr( wp_json_encode( $timer_data ) ); ?>">
						<div class="ao-so-timer-box"><span class="ao-so-num ao-so-days">00</span><span class="ao-so-lbl"><?php esc_html_e( 'روز', 'amazing-offer' ); ?></span></div>
						<div class="ao-so-timer-box"><span class="ao-so-num ao-so-hours">00</span><span class="ao-so-lbl"><?php esc_html_e( 'ساعت', 'amazing-offer' ); ?></span></div>
						<div class="ao-so-timer-box"><span class="ao-so-num ao-so-minutes">00</span><span class="ao-so-lbl"><?php esc_html_e( 'دقیقه', 'amazing-offer' ); ?></span></div>
						<div class="ao-so-timer-box"><span class="ao-so-num ao-so-seconds">00</span><span class="ao-so-lbl"><?php esc_html_e( 'ثانیه', 'amazing-offer' ); ?></span></div>
					</div>
				<?php endif; ?>
			</div>

			<div class="ao-so-body">
				<?php if ( $has_left ) : ?>
					<?php echo self::render_banner( $banner ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped within. ?>
				<?php endif; ?>

				<?php if ( $is_grid ) : ?>
					<div class="ao-so-grid">
						<?php foreach ( $items as $item ) : ?>
							<?php echo self::render_card( $item, $config ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped within. ?>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<div class="ao-so-slider swiper" id="<?php echo esc_attr( $uid ); ?>" data-swiper="<?php echo esc_attr( wp_json_encode( $swiper_data ) ); ?>">
						<div class="swiper-wrapper">
							<?php foreach ( $items as $item ) : ?>
								<div class="swiper-slide">
									<?php echo self::render_card( $item, $config ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped within. ?>
								</div>
							<?php endforeach; ?>
						</div>
						<?php if ( ! empty( $config['show_dots'] ) ) : ?>
							<div class="swiper-pagination"></div>
						<?php endif; ?>
						<?php if ( ! empty( $config['show_nav'] ) ) : ?>
							<button type="button" class="swiper-button-prev ao-so-nav" aria-label="<?php esc_attr_e( 'قبلی', 'amazing-offer' ); ?>"></button>
							<button type="button" class="swiper-button-next ao-so-nav" aria-label="<?php esc_attr_e( 'بعدی', 'amazing-offer' ); ?>"></button>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>

			<?php echo self::render_jsonld( $items ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON-encoded + escaped within. ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render the promo banner.
	 *
	 * @param array $banner Banner config.
	 * @return string
	 */
	protected static function render_banner( $banner ) {
		$src = $banner['image'];
		if ( ! $src && $banner['image_id'] ) {
			$src = wp_get_attachment_image_url( (int) $banner['image_id'], 'large' );
		}
		if ( ! $src ) {
			return '';
		}

		$img = '<img src="' . esc_url( $src ) . '" alt="' . esc_attr( $banner['alt'] ) . '" loading="lazy" class="ao-so-banner-img" />';
		if ( $banner['link'] ) {
			$img = '<a href="' . esc_url( $banner['link'] ) . '" class="ao-so-banner-link">' . $img . '</a>';
		}
		return '<div class="ao-so-banner">' . $img . '</div>';
	}

	/**
	 * Render a single product card.
	 *
	 * @param array $item   Product data.
	 * @param array $config Resolved config.
	 * @return string
	 */
	protected static function render_card( array $item, array $config ) {
		ob_start();
		?>
		<div class="ao-so-card" data-product-id="<?php echo esc_attr( $item['id'] ); ?>">
			<div class="ao-so-card-media">
				<?php if ( ! empty( $config['show_discount_badge'] ) && $item['discount_percent'] > 0 ) : ?>
					<span class="ao-so-badge"><?php echo esc_html( $item['discount_percent'] ); ?>٪</span>
				<?php endif; ?>
				<a href="<?php echo esc_url( $item['permalink'] ); ?>" class="ao-so-card-link">
					<img src="<?php echo esc_url( $item['image'] ); ?>" alt="<?php echo esc_attr( $item['name'] ); ?>" loading="lazy" />
				</a>
			</div>
			<div class="ao-so-card-body">
				<h3 class="ao-so-card-title"><a href="<?php echo esc_url( $item['permalink'] ); ?>"><?php echo esc_html( $item['name'] ); ?></a></h3>
				<div class="ao-so-card-price">
					<?php if ( ! empty( $config['show_original_price'] ) && $item['regular_price'] > $item['sale_price'] && $item['sale_price'] > 0 ) : ?>
						<del class="ao-so-regular-price"><?php echo wp_kses_post( $item['regular_price_html'] ); ?></del>
					<?php endif; ?>
					<span class="ao-so-sale-price"><?php echo wp_kses_post( $item['price_html'] ); ?></span>
				</div>
				<?php if ( ! empty( $config['show_add_to_cart'] ) ) : ?>
					<?php if ( $item['is_purchasable'] ) : ?>
						<button type="button" class="ao-so-add-to-cart" data-product-id="<?php echo esc_attr( $item['id'] ); ?>">
							<span class="ao-so-btn-text"><?php echo esc_html( $config['cart_button_text'] ); ?></span>
							<span class="ao-so-btn-spinner" aria-hidden="true"></span>
						</button>
					<?php else : ?>
						<button type="button" class="ao-so-add-to-cart is-disabled" disabled><?php esc_html_e( 'ناموجود', 'amazing-offer' ); ?></button>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Optional JSON-LD ItemList for SEO (filterable, default on).
	 *
	 * @param array $items Product data.
	 * @return string
	 */
	protected static function render_jsonld( array $items ) {
		if ( ! apply_filters( 'amazing_offer_so_jsonld', true ) ) {
			return '';
		}

		$elements = array();
		$position = 1;
		foreach ( $items as $item ) {
			$elements[] = array(
				'@type'    => 'ListItem',
				'position' => $position++,
				'item'     => array(
					'@type' => 'Product',
					'name'  => wp_strip_all_tags( $item['name'] ),
					'image' => $item['image'],
					'url'   => $item['permalink'],
					'offers' => array(
						'@type'         => 'Offer',
						'price'         => $item['price'],
						'priceCurrency' => function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : '',
						'availability'  => 'instock' === $item['stock_status'] ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
					),
				),
			);
		}

		$data = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'ItemList',
			'itemListElement' => $elements,
		);

		return '<script type="application/ld+json">' . wp_json_encode( $data ) . '</script>';
	}

	/**
	 * Resolve products for a template's source.
	 *
	 * @param array                  $config   Resolved config.
	 * @param Amazing_Offer_Products $products Core products manager.
	 * @return array
	 */
	protected static function get_products( array $config, $products ) {
		$source = $config['source'];
		$limit  = (int) $source['limit'];
		$items  = array();

		switch ( $source['type'] ) {
			case 'sale':
				$items = $products->get_sale_products( $limit );
				break;

			case 'category':
				$items = self::get_category_products( $limit, (int) $source['category'], $products );
				break;

			case 'manual':
			case 'saved':
			default:
				foreach ( $source['product_ids'] as $pid ) {
					$data = $products->get_product_data( (int) $pid );
					if ( ! empty( $data ) ) {
						$items[] = $data;
					}
				}
				if ( $limit > 0 ) {
					$items = array_slice( $items, 0, $limit );
				}
				break;
		}

		return $items;
	}

	/**
	 * Query products in a category.
	 *
	 * @param int                    $limit    Limit.
	 * @param int                    $category Term id.
	 * @param Amazing_Offer_Products $products Core products manager.
	 * @return array
	 */
	protected static function get_category_products( $limit, $category, $products ) {
		if ( ! $category ) {
			return array();
		}

		$query = new WP_Query(
			array(
				'post_type'        => 'product',
				'post_status'      => 'publish',
				'posts_per_page'   => absint( $limit ),
				'no_found_rows'    => true,
				'suppress_filters' => false,
				'fields'           => 'ids',
				'tax_query'        => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'term_id',
						'terms'    => $category,
					),
				),
			)
		);

		$items = array();
		foreach ( $query->posts as $pid ) {
			$data = $products->get_product_data( (int) $pid );
			if ( ! empty( $data ) ) {
				$items[] = $data;
			}
		}
		return $items;
	}
}
