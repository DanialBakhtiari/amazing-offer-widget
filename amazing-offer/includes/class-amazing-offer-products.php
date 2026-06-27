<?php
/**
 * WooCommerce product retrieval and AJAX handlers.
 *
 * @package Amazing_Offer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Products manager.
 */
class Amazing_Offer_Products {

	/**
	 * Option key for the saved product id list.
	 *
	 * @var string
	 */
	const PRODUCTS_OPTION = 'amazing_offer_products';

	/**
	 * Register AJAX hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		// Admin-only handlers.
		add_action( 'wp_ajax_amazing_offer_load_sale_products', array( $this, 'ajax_load_sale_products' ) );
		add_action( 'wp_ajax_amazing_offer_search_products', array( $this, 'ajax_search_products' ) );
		add_action( 'wp_ajax_amazing_offer_save_products', array( $this, 'ajax_save_products' ) );

		// Front-end add to cart (logged in + guests).
		add_action( 'wp_ajax_amazing_offer_add_to_cart', array( $this, 'ajax_add_to_cart' ) );
		add_action( 'wp_ajax_nopriv_amazing_offer_add_to_cart', array( $this, 'ajax_add_to_cart' ) );
	}

	/**
	 * Get on-sale products from WooCommerce.
	 *
	 * @param int $limit Maximum number of products.
	 * @return array Array of product data arrays.
	 */
	public function get_sale_products( $limit = 20 ) {
		if ( ! function_exists( 'wc_get_product_ids_on_sale' ) ) {
			return array();
		}

		$on_sale_ids = wc_get_product_ids_on_sale();
		if ( empty( $on_sale_ids ) ) {
			return array();
		}

		$query = new WP_Query(
			array(
				'post_type'           => 'product',
				'post_status'         => 'publish',
				'posts_per_page'      => absint( $limit ),
				'post__in'            => array_map( 'absint', $on_sale_ids ),
				'orderby'             => 'post__in',
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
				'suppress_filters'    => false,
				'fields'              => 'ids',
			)
		);

		$products = array();
		foreach ( $query->posts as $product_id ) {
			$data = $this->get_product_data( (int) $product_id );
			if ( ! empty( $data ) ) {
				$products[] = $data;
			}
		}

		return $products;
	}

	/**
	 * Search products by keyword.
	 *
	 * @param string $keyword Search term.
	 * @param int    $limit   Maximum results.
	 * @return array
	 */
	public function search_products( $keyword, $limit = 10 ) {
		$keyword = sanitize_text_field( $keyword );
		if ( strlen( $keyword ) < 2 ) {
			return array();
		}

		$query = new WP_Query(
			array(
				'post_type'           => 'product',
				'post_status'         => 'publish',
				'posts_per_page'      => absint( $limit ),
				's'                   => $keyword,
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
				'suppress_filters'    => false,
				'fields'              => 'ids',
			)
		);

		$products = array();
		foreach ( $query->posts as $product_id ) {
			$data = $this->get_product_data( (int) $product_id );
			if ( ! empty( $data ) ) {
				$products[] = $data;
			}
		}

		return $products;
	}

	/**
	 * Get the saved product id list.
	 *
	 * @return int[]
	 */
	public function get_saved_product_ids() {
		$ids = get_option( self::PRODUCTS_OPTION, array() );
		if ( ! is_array( $ids ) ) {
			return array();
		}
		return array_values( array_map( 'absint', $ids ) );
	}

	/**
	 * Get full data for every saved product.
	 *
	 * @return array
	 */
	public function get_saved_products() {
		$products = array();
		foreach ( $this->get_saved_product_ids() as $product_id ) {
			$data = $this->get_product_data( $product_id );
			if ( ! empty( $data ) ) {
				$products[] = $data;
			}
		}
		return $products;
	}

	/**
	 * Persist the saved product id list (order preserved).
	 *
	 * @param int[] $product_ids Product ids.
	 * @return bool
	 */
	public function save_products( array $product_ids ) {
		$ids = array_values( array_unique( array_filter( array_map( 'absint', $product_ids ) ) ) );
		return update_option( self::PRODUCTS_OPTION, $ids );
	}

	/**
	 * Build a normalized data array for a product.
	 *
	 * @param int $product_id Product id.
	 * @return array Empty array if the product is invalid.
	 */
	public function get_product_data( $product_id ) {
		$product = wc_get_product( $product_id );
		if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
			return array();
		}

		$regular_price = (float) $product->get_regular_price();
		$sale_price    = (float) $product->get_sale_price();
		$active_price  = (float) $product->get_price();

		$discount_percent = 0;
		if ( $regular_price > 0 && $sale_price > 0 && $sale_price < $regular_price ) {
			$discount_percent = (int) round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 );
		}

		$image_id  = $product->get_image_id();
		$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'woocommerce_thumbnail' ) : wc_placeholder_img_src( 'woocommerce_thumbnail' );

		return array(
			'id'               => $product->get_id(),
			'name'             => $product->get_name(),
			'permalink'        => get_permalink( $product->get_id() ),
			'image'            => $image_url,
			'regular_price'    => $regular_price,
			'sale_price'       => $sale_price,
			'price'            => $active_price,
			'regular_price_html' => wc_price( $regular_price ),
			'price_html'       => $product->get_price_html(),
			'discount_percent' => $discount_percent,
			'stock_status'     => $product->get_stock_status(),
			'stock_quantity'   => $product->get_stock_quantity(),
			'is_purchasable'   => $product->is_purchasable() && $product->is_in_stock(),
			'is_on_sale'       => $product->is_on_sale(),
			'type'             => $product->get_type(),
		);
	}

	/**
	 * Verify nonce and admin capability for admin AJAX requests.
	 *
	 * @return void Dies with JSON error when invalid.
	 */
	private function verify_admin_request() {
		if ( ! check_ajax_referer( 'amazing_offer_admin', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'بررسی امنیتی ناموفق بود.', 'amazing-offer' ) ), 403 );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'دسترسی کافی ندارید.', 'amazing-offer' ) ), 403 );
		}
	}

	/**
	 * AJAX: load on-sale products.
	 *
	 * @return void
	 */
	public function ajax_load_sale_products() {
		$this->verify_admin_request();

		$limit    = isset( $_POST['limit'] ) ? absint( $_POST['limit'] ) : 20;
		$products = $this->get_sale_products( $limit );

		wp_send_json_success( array( 'products' => $products ) );
	}

	/**
	 * AJAX: search products by keyword.
	 *
	 * @return void
	 */
	public function ajax_search_products() {
		$this->verify_admin_request();

		$keyword  = isset( $_POST['keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) : '';
		$products = $this->search_products( $keyword, 10 );

		wp_send_json_success( array( 'products' => $products ) );
	}

	/**
	 * AJAX: save selected products (with order).
	 *
	 * @return void
	 */
	public function ajax_save_products() {
		$this->verify_admin_request();

		$ids = array();
		if ( isset( $_POST['product_ids'] ) && is_array( $_POST['product_ids'] ) ) {
			$ids = array_map( 'absint', wp_unslash( $_POST['product_ids'] ) );
		}

		$saved = $this->save_products( $ids );

		if ( $saved ) {
			wp_send_json_success(
				array(
					'message'  => __( 'محصولات با موفقیت ذخیره شد.', 'amazing-offer' ),
					'products' => $this->get_saved_products(),
				)
			);
		}

		wp_send_json_error( array( 'message' => __( 'تغییری برای ذخیره وجود نداشت.', 'amazing-offer' ) ) );
	}

	/**
	 * AJAX: add a product to the cart from the front-end.
	 *
	 * @return void
	 */
	public function ajax_add_to_cart() {
		if ( ! check_ajax_referer( 'amazing_offer_public', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'بررسی امنیتی ناموفق بود.', 'amazing-offer' ) ), 403 );
		}

		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$quantity   = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 1;
		$quantity   = max( 1, $quantity );

		if ( ! $product_id || ! function_exists( 'WC' ) || is_null( WC()->cart ) ) {
			wp_send_json_error( array( 'message' => __( 'محصول نامعتبر است.', 'amazing-offer' ) ) );
		}

		$product = wc_get_product( $product_id );
		if ( ! $product || ! $product->is_purchasable() || ! $product->is_in_stock() ) {
			wp_send_json_error( array( 'message' => __( 'این محصول قابل خرید نیست.', 'amazing-offer' ) ) );
		}

		$added = WC()->cart->add_to_cart( $product_id, $quantity );

		if ( $added ) {
			wp_send_json_success(
				array(
					'message'       => __( 'به سبد خرید اضافه شد.', 'amazing-offer' ),
					'cart_count'    => WC()->cart->get_cart_contents_count(),
					'cart_total'    => WC()->cart->get_cart_total(),
					'cart_url'      => wc_get_cart_url(),
				)
			);
		}

		wp_send_json_error( array( 'message' => __( 'افزودن به سبد ناموفق بود.', 'amazing-offer' ) ) );
	}
}
