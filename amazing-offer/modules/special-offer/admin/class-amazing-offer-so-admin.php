<?php
/**
 * Special Offer admin: template manager + tabbed editor.
 *
 * Uses add_submenu_page under the existing Amazing Offer menu (NOT a filtered
 * tab — the core tab partial is a hardcoded switch). All AJAX reuses the core
 * `amazing_offer_admin` nonce + manage_options check.
 *
 * @package Amazing_Offer\Special_Offer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin controller.
 */
class Amazing_Offer_SO_Admin {

	const PAGE_SLUG = 'amazing-offer-special';
	const NONCE     = 'amazing_offer_admin';

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
	 * Template repository.
	 *
	 * @var Amazing_Offer_SO_Repository
	 */
	protected $repository;

	/**
	 * Submenu hook suffix.
	 *
	 * @var string
	 */
	protected $hook_suffix = '';

	/**
	 * Constructor.
	 *
	 * @param Amazing_Offer_Settings      $settings   Settings.
	 * @param Amazing_Offer_Products      $products   Products.
	 * @param Amazing_Offer_SO_Repository $repository Repository.
	 */
	public function __construct( $settings, $products, $repository ) {
		$this->settings   = $settings;
		$this->products   = $products;
		$this->repository = $repository;
	}

	/**
	 * Register admin hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'admin_menu', array( $this, 'add_menu' ), 20 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		add_action( 'wp_ajax_ao_so_create', array( $this, 'ajax_create' ) );
		add_action( 'wp_ajax_ao_so_duplicate', array( $this, 'ajax_duplicate' ) );
		add_action( 'wp_ajax_ao_so_delete', array( $this, 'ajax_delete' ) );
		add_action( 'wp_ajax_ao_so_toggle', array( $this, 'ajax_toggle' ) );
		add_action( 'wp_ajax_ao_so_reorder', array( $this, 'ajax_reorder' ) );
		add_action( 'wp_ajax_ao_so_save', array( $this, 'ajax_save' ) );
	}

	/**
	 * Add the submenu page.
	 *
	 * @return void
	 */
	public function add_menu() {
		$this->hook_suffix = add_submenu_page(
			'amazing-offer',
			__( 'پیشنهاد ویژه', 'amazing-offer' ),
			__( 'پیشنهاد ویژه', 'amazing-offer' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Enqueue admin assets on this page only.
	 *
	 * @param string $hook Current admin hook.
	 * @return void
	 */
	public function enqueue_assets( $hook ) {
		if ( $hook !== $this->hook_suffix ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style(
			'ao-so-admin',
			AMAZING_OFFER_SO_URL . 'admin/css/ao-so-admin.css',
			array( 'wp-color-picker', 'dashicons' ),
			AMAZING_OFFER_SO_VERSION
		);
		wp_enqueue_script(
			'ao-so-admin',
			AMAZING_OFFER_SO_URL . 'admin/js/ao-so-admin.js',
			array( 'jquery', 'wp-color-picker', 'jquery-ui-sortable' ),
			AMAZING_OFFER_SO_VERSION,
			true
		);

		wp_localize_script(
			'ao-so-admin',
			'amazingOfferSOAdmin',
			array(
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( self::NONCE ),
				'listUrl'  => admin_url( 'admin.php?page=' . self::PAGE_SLUG ),
				'editUrl'  => admin_url( 'admin.php?page=' . self::PAGE_SLUG . '&action=edit&id=' ),
				'i18n'     => array(
					'loading'       => __( 'در حال بارگذاری...', 'amazing-offer' ),
					'noResults'     => __( 'نتیجه‌ای یافت نشد.', 'amazing-offer' ),
					'saved'         => __( 'ذخیره شد ✓', 'amazing-offer' ),
					'error'         => __( 'خطا رخ داد', 'amazing-offer' ),
					'confirmDelete' => __( 'این طرح حذف شود؟ این عمل قابل بازگشت نیست.', 'amazing-offer' ),
					'confirmRemove' => __( 'این محصول از طرح حذف شود؟', 'amazing-offer' ),
					'newTitle'      => __( 'نام طرح جدید:', 'amazing-offer' ),
					'alreadyAdded'  => __( 'این محصول قبلاً اضافه شده است.', 'amazing-offer' ),
					'selectMedia'   => __( 'انتخاب تصویر بنر', 'amazing-offer' ),
					'useImage'      => __( 'استفاده از این تصویر', 'amazing-offer' ),
				),
			)
		);
	}

	/**
	 * Render the list or the editor depending on the action.
	 *
	 * @return void
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'دسترسی کافی ندارید.', 'amazing-offer' ) );
		}

		$action  = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : 'list'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$post_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$repository = $this->repository;
		$products   = $this->products;

		if ( 'edit' === $action && $post_id && $repository->get( $post_id ) ) {
			$post   = $repository->get( $post_id );
			$config = Amazing_Offer_SO_Schema::load( $post_id );
			require AMAZING_OFFER_SO_DIR . 'admin/partials/so-editor.php';
			return;
		}

		$templates = $repository->all();
		require AMAZING_OFFER_SO_DIR . 'admin/partials/so-list.php';
	}

	/**
	 * Verify nonce + capability for AJAX.
	 *
	 * @return void
	 */
	private function verify() {
		if ( ! check_ajax_referer( self::NONCE, 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'بررسی امنیتی ناموفق بود.', 'amazing-offer' ) ), 403 );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'دسترسی کافی ندارید.', 'amazing-offer' ) ), 403 );
		}
	}

	/**
	 * AJAX: create a new template.
	 *
	 * @return void
	 */
	public function ajax_create() {
		$this->verify();
		$title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
		if ( '' === $title ) {
			$title = __( 'طرح بدون نام', 'amazing-offer' );
		}
		$id = $this->repository->create( $title );
		if ( is_wp_error( $id ) ) {
			wp_send_json_error( array( 'message' => $id->get_error_message() ) );
		}
		wp_send_json_success( array( 'id' => $id, 'editUrl' => admin_url( 'admin.php?page=' . self::PAGE_SLUG . '&action=edit&id=' . $id ) ) );
	}

	/**
	 * AJAX: duplicate a template.
	 *
	 * @return void
	 */
	public function ajax_duplicate() {
		$this->verify();
		$id  = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$new = $this->repository->duplicate( $id );
		if ( is_wp_error( $new ) || ! $new ) {
			wp_send_json_error( array( 'message' => __( 'تکثیر ناموفق بود.', 'amazing-offer' ) ) );
		}
		wp_send_json_success( array( 'id' => $new ) );
	}

	/**
	 * AJAX: delete a template.
	 *
	 * @return void
	 */
	public function ajax_delete() {
		$this->verify();
		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		if ( $this->repository->delete( $id ) ) {
			wp_send_json_success();
		}
		wp_send_json_error( array( 'message' => __( 'حذف ناموفق بود.', 'amazing-offer' ) ) );
	}

	/**
	 * AJAX: toggle active state.
	 *
	 * @return void
	 */
	public function ajax_toggle() {
		$this->verify();
		$id     = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$active = ! empty( $_POST['active'] ) && 'false' !== $_POST['active'];
		if ( $this->repository->set_status( $id, $active ) ) {
			wp_send_json_success( array( 'active' => $active ) );
		}
		wp_send_json_error( array( 'message' => __( 'تغییر وضعیت ناموفق بود.', 'amazing-offer' ) ) );
	}

	/**
	 * AJAX: reorder templates.
	 *
	 * @return void
	 */
	public function ajax_reorder() {
		$this->verify();
		$ids = array();
		if ( isset( $_POST['ids'] ) && is_array( $_POST['ids'] ) ) {
			$ids = array_map( 'absint', wp_unslash( $_POST['ids'] ) );
		}
		$this->repository->reorder( $ids );
		wp_send_json_success();
	}

	/**
	 * AJAX: save the editor form for one template.
	 *
	 * @return void
	 */
	public function ajax_save() {
		$this->verify();

		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		if ( ! $id || ! $this->repository->get( $id ) ) {
			wp_send_json_error( array( 'message' => __( 'طرح یافت نشد.', 'amazing-offer' ) ) );
		}

		// Update the internal title.
		if ( isset( $_POST['title'] ) ) {
			$title = sanitize_text_field( wp_unslash( $_POST['title'] ) );
			if ( '' !== $title ) {
				wp_update_post( array( 'ID' => $id, 'post_title' => $title ) );
			}
		}

		// Config is sanitized inside the schema; raw decode only.
		$raw = isset( $_POST['config'] ) ? wp_unslash( $_POST['config'] ) : array(); // phpcs:ignore WordPress.Security.ValidationSanitization.InputNotSanitized -- sanitized in schema.
		if ( ! is_array( $raw ) ) {
			$raw = array();
		}

		Amazing_Offer_SO_Schema::save( $id, $raw );

		wp_send_json_success( array( 'message' => __( 'ذخیره شد ✓', 'amazing-offer' ) ) );
	}
}
