<?php
/**
 * Shared front-end renderer for the offer slider.
 *
 * Used by both the shortcode and the Elementor widget so markup stays in sync.
 *
 * @package Amazing_Offer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renderer.
 */
class Amazing_Offer_Render {

	/**
	 * Render the slider markup.
	 *
	 * @param array                  $args     Overrides for settings/source.
	 * @param Amazing_Offer_Settings $settings Settings manager.
	 * @param Amazing_Offer_Products $products Products manager.
	 * @return string
	 */
	public static function render( array $args, Amazing_Offer_Settings $settings, Amazing_Offer_Products $products ) {
		$config = wp_parse_args( $args, $settings->get_all() );

		$source   = isset( $args['source'] ) ? $args['source'] : 'saved';
		$limit    = isset( $args['limit'] ) ? absint( $args['limit'] ) : 20;
		$category = isset( $args['category'] ) ? absint( $args['category'] ) : 0;

		$items = self::get_products( $source, $limit, $category, $products );

		// Mark public assets as needed so the enqueue hook loads them.
		do_action( 'amazing_offer_render' );

		if ( empty( $items ) ) {
			return '<div class="amazing-offer-empty">' . esc_html__( 'در حال حاضر پیشنهادی موجود نیست.', 'amazing-offer' ) . '</div>';
		}

		$slider_data = array(
			'mode'         => $config['slider_mode'],
			'speed'        => (int) $config['slider_speed'],
			'loop'         => (bool) $config['slider_loop'],
			'pauseOnHover' => (bool) $config['pause_on_hover'],
			'cards'        => array(
				'mobile'  => (int) $config['slider_cards_mobile'],
				'tablet'  => (int) $config['slider_cards_tablet'],
				'desktop' => (int) $config['slider_cards_desktop'],
			),
		);

		$timer_data = array(
			'show'     => (bool) $config['show_timer'],
			'type'     => $config['timer_type'],
			'duration' => (int) $config['timer_duration'],
			'endDate'  => $config['timer_end_date'],
		);

		$uid = 'ao-' . wp_rand( 1000, 9999 );

		ob_start();
		?>
		<div class="amazing-offer-wrapper" dir="rtl"
			style="--ao-primary: <?php echo esc_attr( $config['button_color'] ); ?>; --ao-primary-hover: <?php echo esc_attr( $config['button_hover_color'] ); ?>; --ao-badge: <?php echo esc_attr( $config['badge_color'] ); ?>;">

			<div class="amazing-offer-header">
				<div class="amazing-offer-titles">
					<h2 class="amazing-offer-title">
						<?php if ( ! empty( $config['show_icon'] ) ) : ?>
							<span class="amazing-offer-icon dashicons dashicons-superhero-alt" aria-hidden="true"></span>
						<?php endif; ?>
						<?php echo esc_html( $config['title'] ); ?>
					</h2>
					<?php if ( ! empty( $config['subtitle'] ) ) : ?>
						<p class="amazing-offer-subtitle"><?php echo esc_html( $config['subtitle'] ); ?></p>
					<?php endif; ?>
				</div>

				<?php if ( ! empty( $config['show_timer'] ) ) : ?>
					<div class="amazing-offer-timer" data-timer="<?php echo esc_attr( wp_json_encode( $timer_data ) ); ?>">
						<div class="ao-timer-box"><span class="ao-timer-num ao-days">00</span><span class="ao-timer-label"><?php esc_html_e( 'روز', 'amazing-offer' ); ?></span></div>
						<div class="ao-timer-box"><span class="ao-timer-num ao-hours">00</span><span class="ao-timer-label"><?php esc_html_e( 'ساعت', 'amazing-offer' ); ?></span></div>
						<div class="ao-timer-box"><span class="ao-timer-num ao-minutes">00</span><span class="ao-timer-label"><?php esc_html_e( 'دقیقه', 'amazing-offer' ); ?></span></div>
						<div class="ao-timer-box"><span class="ao-timer-num ao-seconds">00</span><span class="ao-timer-label"><?php esc_html_e( 'ثانیه', 'amazing-offer' ); ?></span></div>
					</div>
				<?php endif; ?>
			</div>

			<div class="amazing-offer-slider amazing-offer-mode-<?php echo esc_attr( $config['slider_mode'] ); ?>"
				id="<?php echo esc_attr( $uid ); ?>"
				data-slider="<?php echo esc_attr( wp_json_encode( $slider_data ) ); ?>">

				<div class="amazing-offer-track">
					<?php foreach ( $items as $item ) : ?>
						<?php echo self::render_card( $item, $config ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped within. ?>
					<?php endforeach; ?>
				</div>

				<?php if ( ! empty( $config['show_nav'] ) && 'grid' !== $config['slider_mode'] ) : ?>
					<button type="button" class="amazing-offer-nav amazing-offer-prev" aria-label="<?php esc_attr_e( 'قبلی', 'amazing-offer' ); ?>">
						<span class="dashicons dashicons-arrow-right-alt2"></span>
					</button>
					<button type="button" class="amazing-offer-nav amazing-offer-next" aria-label="<?php esc_attr_e( 'بعدی', 'amazing-offer' ); ?>">
						<span class="dashicons dashicons-arrow-left-alt2"></span>
					</button>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $config['show_dots'] ) && 'grid' !== $config['slider_mode'] ) : ?>
				<div class="amazing-offer-dots" aria-hidden="true"></div>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render a single product card.
	 *
	 * @param array $item   Product data.
	 * @param array $config Resolved settings.
	 * @return string
	 */
	protected static function render_card( array $item, array $config ) {
		ob_start();
		?>
		<div class="amazing-offer-card" data-product-id="<?php echo esc_attr( $item['id'] ); ?>">
			<div class="amazing-offer-card-media">
				<?php if ( ! empty( $config['show_discount_badge'] ) && $item['discount_percent'] > 0 ) : ?>
					<span class="amazing-offer-badge"><?php echo esc_html( $item['discount_percent'] ); ?>٪</span>
				<?php endif; ?>
				<a href="<?php echo esc_url( $item['permalink'] ); ?>" class="amazing-offer-card-link">
					<img src="<?php echo esc_url( $item['image'] ); ?>" alt="<?php echo esc_attr( $item['name'] ); ?>" loading="lazy" />
				</a>
			</div>

			<div class="amazing-offer-card-body">
				<h3 class="amazing-offer-card-title">
					<a href="<?php echo esc_url( $item['permalink'] ); ?>"><?php echo esc_html( $item['name'] ); ?></a>
				</h3>

				<div class="amazing-offer-card-price">
					<?php if ( ! empty( $config['show_original_price'] ) && $item['regular_price'] > $item['sale_price'] && $item['sale_price'] > 0 ) : ?>
						<del class="amazing-offer-regular-price"><?php echo wp_kses_post( $item['regular_price_html'] ); ?></del>
					<?php endif; ?>
					<span class="amazing-offer-sale-price"><?php echo wp_kses_post( $item['price_html'] ); ?></span>
				</div>

				<?php echo self::render_modules( $item, $config ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped within. ?>

				<?php if ( ! empty( $config['show_add_to_cart'] ) ) : ?>
					<?php if ( $item['is_purchasable'] ) : ?>
						<button type="button" class="amazing-offer-add-to-cart" data-product-id="<?php echo esc_attr( $item['id'] ); ?>">
							<span class="ao-btn-text"><?php echo esc_html( $config['cart_button_text'] ); ?></span>
							<span class="ao-btn-spinner" aria-hidden="true"></span>
						</button>
					<?php else : ?>
						<button type="button" class="amazing-offer-add-to-cart is-disabled" disabled>
							<?php esc_html_e( 'ناموجود', 'amazing-offer' ); ?>
						</button>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render optional module rows (days left, stock, buyers).
	 *
	 * @param array $item   Product data.
	 * @param array $config Resolved settings.
	 * @return string
	 */
	protected static function render_modules( array $item, array $config ) {
		$rows = array();

		if ( ! empty( $config['show_stock'] ) && null !== $item['stock_quantity'] && $item['stock_quantity'] > 0 ) {
			$rows[] = '<div class="amazing-offer-module ao-module-stock"><span class="dashicons dashicons-archive"></span> '
				. sprintf(
					/* translators: %d: number of items left in stock. */
					esc_html__( 'تنها %d عدد باقی مانده', 'amazing-offer' ),
					(int) $item['stock_quantity']
				) . '</div>';
		}

		if ( ! empty( $config['show_buyers_count'] ) ) {
			$base   = (int) $config['buyers_count_base'];
			// Deterministic per-product offset so the number is stable per render.
			$buyers = $base + ( $item['id'] % 50 );
			$rows[] = '<div class="amazing-offer-module ao-module-buyers"><span class="dashicons dashicons-groups"></span> '
				. sprintf(
					/* translators: %d: number of buyers. */
					esc_html__( '%d نفر این محصول را خریده‌اند', 'amazing-offer' ),
					(int) $buyers
				) . '</div>';
		}

		if ( empty( $rows ) ) {
			return '';
		}

		return '<div class="amazing-offer-modules">' . implode( '', $rows ) . '</div>';
	}

	/**
	 * Resolve products for a given source.
	 *
	 * @param string                 $source   sale | saved | category.
	 * @param int                    $limit    Limit.
	 * @param int                    $category Category term id.
	 * @param Amazing_Offer_Products $products Products manager.
	 * @return array
	 */
	protected static function get_products( $source, $limit, $category, Amazing_Offer_Products $products ) {
		// Allow extensions to register custom sources.
		$custom = apply_filters( 'amazing_offer_product_sources', array() );
		if ( isset( $custom[ $source ] ) && is_callable( $custom[ $source ]['callback'] ) ) {
			return (array) call_user_func( $custom[ $source ]['callback'], $limit, $category );
		}

		switch ( $source ) {
			case 'sale':
				return $products->get_sale_products( $limit );

			case 'category':
				return self::get_category_products( $limit, $category, $products );

			case 'saved':
			default:
				$items = $products->get_saved_products();
				if ( $limit > 0 ) {
					$items = array_slice( $items, 0, $limit );
				}
				return $items;
		}
	}

	/**
	 * Get products from a specific category.
	 *
	 * @param int                    $limit    Limit.
	 * @param int                    $category Category term id.
	 * @param Amazing_Offer_Products $products Products manager.
	 * @return array
	 */
	protected static function get_category_products( $limit, $category, Amazing_Offer_Products $products ) {
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
		foreach ( $query->posts as $product_id ) {
			$data = $products->get_product_data( (int) $product_id );
			if ( ! empty( $data ) ) {
				$items[] = $data;
			}
		}
		return $items;
	}
}
