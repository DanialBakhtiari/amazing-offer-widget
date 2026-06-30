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
		add_action( 'wp_ajax_ao_so_preview', array( $this, 'ajax_preview' ) );
		add_action( 'wp_ajax_ao_so_import', array( $this, 'ajax_import' ) );
		add_action( 'admin_post_ao_so_export', array( $this, 'handle_export' ) );
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

		// The live preview reuses the real front-end assets so it matches
		// production exactly. They are not registered in admin context, so
		// enqueue them here directly.
		wp_enqueue_style( 'ao-so-swiper', AMAZING_OFFER_SO_URL . 'public/vendor/swiper/swiper-bundle.min.css', array(), '11.2.10' );
		wp_enqueue_style( 'ao-so-public', AMAZING_OFFER_SO_URL . 'public/css/ao-so-public.css', array( 'ao-so-swiper', 'dashicons' ), AMAZING_OFFER_SO_VERSION );
		wp_enqueue_script( 'ao-so-swiper', AMAZING_OFFER_SO_URL . 'public/vendor/swiper/swiper-bundle.min.js', array(), '11.2.10', true );
		wp_enqueue_script( 'ao-so-public', AMAZING_OFFER_SO_URL . 'public/js/ao-so-public.js', array( 'ao-so-swiper' ), AMAZING_OFFER_SO_VERSION, true );
		wp_localize_script(
			'ao-so-public',
			'amazingOfferSOData',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'amazing_offer_public' ),
				'i18n'    => array(
					'prev' => __( 'قبلی', 'amazing-offer' ),
					'next' => __( 'بعدی', 'amazing-offer' ),
				),
			)
		);

		wp_enqueue_style(
			'ao-so-admin',
			AMAZING_OFFER_SO_URL . 'admin/css/ao-so-admin.css',
			array( 'wp-color-picker', 'dashicons' ),
			AMAZING_OFFER_SO_VERSION
		);
		wp_enqueue_script(
			'ao-so-admin',
			AMAZING_OFFER_SO_URL . 'admin/js/ao-so-admin.js',
			array( 'jquery', 'wp-color-picker', 'jquery-ui-sortable', 'ao-so-public' ),
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
		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		if ( ! $id || ! $this->repository->get( $id ) || ! current_user_can( 'edit_post', $id ) ) {
			wp_send_json_error( array( 'message' => __( 'اجازهٔ تکثیر این طرح را ندارید.', 'amazing-offer' ) ), 403 );
		}
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
		if ( ! $id || ! current_user_can( 'delete_post', $id ) ) {
			wp_send_json_error( array( 'message' => __( 'اجازهٔ حذف این طرح را ندارید.', 'amazing-offer' ) ), 403 );
		}
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
		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		if ( ! $id || ! current_user_can( 'edit_post', $id ) ) {
			wp_send_json_error( array( 'message' => __( 'اجازهٔ تغییر این طرح را ندارید.', 'amazing-offer' ) ), 403 );
		}
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
		if ( ! current_user_can( 'edit_post', $id ) ) {
			wp_send_json_error( array( 'message' => __( 'اجازهٔ ویرایش این طرح را ندارید.', 'amazing-offer' ) ), 403 );
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

	/**
	 * AJAX: render a live preview from the (unsaved) editor config.
	 *
	 * @return void
	 */
	public function ajax_preview() {
		$this->verify();

		$raw = isset( $_POST['config'] ) ? wp_unslash( $_POST['config'] ) : array(); // phpcs:ignore WordPress.Security.ValidationSanitization.InputNotSanitized -- sanitized in schema.
		if ( ! is_array( $raw ) ) {
			$raw = array();
		}

		// Sanitize + normalize without persisting, then render the same markup
		// production uses (no published-post guard so drafts preview too).
		$config = Amazing_Offer_SO_Schema::merge_defaults( Amazing_Offer_SO_Schema::sanitize( $raw ) );
		$html   = Amazing_Offer_SO_Render::render_config( $config, $this->settings, $this->products );

		wp_send_json_success( array( 'html' => $html ) );
	}

	/**
	 * admin-post: download a template as a JSON file.
	 *
	 * @return void
	 */
	public function handle_export() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'دسترسی کافی ندارید.', 'amazing-offer' ) );
		}
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		check_admin_referer( 'ao_so_export_' . $id );

		if ( ! $this->repository->get( $id ) || ! current_user_can( 'edit_post', $id ) ) {
			wp_die( esc_html__( 'اجازهٔ دسترسی به این طرح را ندارید.', 'amazing-offer' ) );
		}

		$json = Amazing_Offer_SO_Export::to_json( $id );

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="special-offer-' . $id . '.json"' );
		header( 'Content-Length: ' . strlen( $json ) );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- raw JSON file body.
		exit;
	}

	/**
	 * AJAX: import a template from an uploaded JSON file.
	 *
	 * @return void
	 */
	public function ajax_import() {
		$this->verify();

		if ( empty( $_FILES['file'] ) || ! isset( $_FILES['file']['tmp_name'] ) || ! is_uploaded_file( $_FILES['file']['tmp_name'] ) ) {
			wp_send_json_error( array( 'message' => __( 'فایلی ارسال نشد.', 'amazing-offer' ) ) );
		}

		$size = isset( $_FILES['file']['size'] ) ? (int) $_FILES['file']['size'] : 0;
		if ( $size <= 0 || $size > 524288 ) { // 512 KB cap.
			wp_send_json_error( array( 'message' => __( 'حجم فایل نامعتبر است (حداکثر ۵۱۲ کیلوبایت).', 'amazing-offer' ) ) );
		}

		$content = file_get_contents( $_FILES['file']['tmp_name'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- reading a local upload temp file.
		$data    = json_decode( $content, true );

		if ( ! is_array( $data ) ) {
			wp_send_json_error( array( 'message' => __( 'فایل JSON معتبر نیست.', 'amazing-offer' ) ) );
		}

		$id = Amazing_Offer_SO_Export::import( $data, $this->repository );
		if ( is_wp_error( $id ) ) {
			wp_send_json_error( array( 'message' => $id->get_error_message() ) );
		}

		wp_send_json_success( array( 'id' => $id ) );
	}
}
