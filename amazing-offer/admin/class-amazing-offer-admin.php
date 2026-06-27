<?php
/**
 * Admin dashboard controller.
 *
 * @package Amazing_Offer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin controller.
 */
class Amazing_Offer_Admin {

	/**
	 * Settings manager.
	 *
	 * @var Amazing_Offer_Settings
	 */
	protected $settings;

	/**
	 * Products manager.
	 *
	 * @var Amazing_Offer_Products
	 */
	protected $products;

	/**
	 * Admin page hook suffix.
	 *
	 * @var string
	 */
	protected $hook_suffix = '';

	/**
	 * Constructor.
	 *
	 * @param Amazing_Offer_Settings $settings Settings.
	 * @param Amazing_Offer_Products $products Products.
	 */
	public function __construct( Amazing_Offer_Settings $settings, Amazing_Offer_Products $products ) {
		$this->settings = $settings;
		$this->products = $products;
	}

	/**
	 * Register admin hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_post_amazing_offer_save_settings', array( $this, 'handle_save_settings' ) );
		add_filter( 'plugin_action_links_' . AMAZING_OFFER_PLUGIN_BASENAME, array( $this, 'add_settings_link' ) );
	}

	/**
	 * Add the top-level admin menu.
	 *
	 * @return void
	 */
	public function add_menu() {
		$this->hook_suffix = add_menu_page(
			__( 'Amazing Offer', 'amazing-offer' ),
			__( 'Amazing Offer', 'amazing-offer' ),
			'manage_options',
			'amazing-offer',
			array( $this, 'render_page' ),
			'dashicons-megaphone',
			56
		);
	}

	/**
	 * Add a Settings link on the plugins screen.
	 *
	 * @param array $links Existing links.
	 * @return array
	 */
	public function add_settings_link( $links ) {
		$url  = admin_url( 'admin.php?page=amazing-offer' );
		$link = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'تنظیمات', 'amazing-offer' ) . '</a>';
		array_unshift( $links, $link );
		return $links;
	}

	/**
	 * Enqueue admin assets on the plugin page only.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_assets( $hook ) {
		if ( $hook !== $this->hook_suffix ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style(
			'amazing-offer-admin',
			AMAZING_OFFER_PLUGIN_URL . 'admin/css/amazing-offer-admin.css',
			array( 'wp-color-picker', 'dashicons' ),
			AMAZING_OFFER_VERSION
		);

		wp_enqueue_script(
			'amazing-offer-admin',
			AMAZING_OFFER_PLUGIN_URL . 'admin/js/amazing-offer-admin.js',
			array( 'jquery', 'wp-color-picker', 'jquery-ui-sortable' ),
			AMAZING_OFFER_VERSION,
			true
		);

		wp_localize_script(
			'amazing-offer-admin',
			'amazingOfferAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'amazing_offer_admin' ),
				'i18n'    => array(
					'loading'      => __( 'در حال بارگذاری...', 'amazing-offer' ),
					'noResults'    => __( 'نتیجه‌ای یافت نشد.', 'amazing-offer' ),
					'added'        => __( 'افزوده شد', 'amazing-offer' ),
					'alreadyAdded' => __( 'این محصول قبلاً اضافه شده است.', 'amazing-offer' ),
					'saved'        => __( 'ذخیره شد ✓', 'amazing-offer' ),
					'error'        => __( 'خطا رخ داد', 'amazing-offer' ),
					'confirmRemove' => __( 'این محصول حذف شود؟', 'amazing-offer' ),
					'copied'       => __( 'کپی شد! ✓', 'amazing-offer' ),
				),
			)
		);
	}

	/**
	 * Render the dashboard page with tabs.
	 *
	 * @return void
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'دسترسی کافی ندارید.', 'amazing-offer' ) );
		}

		$tabs       = array(
			'products' => array(
				'label' => __( 'مدیریت محصولات', 'amazing-offer' ),
				'icon'  => 'dashicons-cart',
			),
			'settings' => array(
				'label' => __( 'تنظیمات ویجت', 'amazing-offer' ),
				'icon'  => 'dashicons-admin-settings',
			),
			'support'  => array(
				'label' => __( 'حمایت از سازنده', 'amazing-offer' ),
				'icon'  => 'dashicons-heart',
			),
		);
		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'products'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $tabs[ $active_tab ] ) ) {
			$active_tab = 'products';
		}

		// Expose helpers to partials.
		$settings = $this->settings;
		$products = $this->products;

		require AMAZING_OFFER_PLUGIN_DIR . 'admin/partials/amazing-offer-admin-display.php';
	}

	/**
	 * Handle the settings form submission (admin-post).
	 *
	 * @return void
	 */
	public function handle_save_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'دسترسی کافی ندارید.', 'amazing-offer' ) );
		}

		check_admin_referer( 'amazing_offer_save_settings', 'amazing_offer_settings_nonce' );

		$raw = isset( $_POST['amazing_offer'] ) ? wp_unslash( $_POST['amazing_offer'] ) : array(); // phpcs:ignore WordPress.Security.ValidationSanitization.InputNotSanitized -- sanitized in settings class.

		if ( isset( $_POST['amazing_offer_reset'] ) ) {
			$this->settings->reset();
			$status = 'reset';
		} else {
			$this->settings->update( (array) $raw );
			$status = 'saved';
		}

		$redirect = add_query_arg(
			array(
				'page'   => 'amazing-offer',
				'tab'    => 'settings',
				'status' => $status,
			),
			admin_url( 'admin.php' )
		);
		wp_safe_redirect( $redirect );
		exit;
	}
}
