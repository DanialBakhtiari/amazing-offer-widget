<?php
/**
 * Main dashboard wrapper with tab navigation.
 *
 * @package Amazing_Offer
 *
 * @var array                    $tabs       Tab definitions.
 * @var string                   $active_tab Active tab key.
 * @var Amazing_Offer_Settings   $settings   Settings manager.
 * @var Amazing_Offer_Products   $products   Products manager.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap amazing-offer-wrap" dir="rtl">

	<div class="amazing-offer-topbar">
		<div class="amazing-offer-brand">
			<span class="dashicons dashicons-megaphone"></span>
			<div>
				<h1><?php esc_html_e( 'Amazing Offer', 'amazing-offer' ); ?></h1>
				<p><?php esc_html_e( 'اسلایدر محصولات تخفیف‌دار ووکامرس', 'amazing-offer' ); ?></p>
			</div>
		</div>
		<span class="amazing-offer-version">v<?php echo esc_html( AMAZING_OFFER_VERSION ); ?></span>
	</div>

	<nav class="amazing-offer-tabs">
		<?php foreach ( $tabs as $key => $tab ) : ?>
			<a class="amazing-offer-tab <?php echo $active_tab === $key ? 'is-active' : ''; ?>"
				href="<?php echo esc_url( admin_url( 'admin.php?page=amazing-offer&tab=' . $key ) ); ?>">
				<span class="dashicons <?php echo esc_attr( $tab['icon'] ); ?>"></span>
				<?php echo esc_html( $tab['label'] ); ?>
			</a>
		<?php endforeach; ?>
	</nav>

	<div class="amazing-offer-panel">
		<?php
		switch ( $active_tab ) {
			case 'settings':
				require AMAZING_OFFER_PLUGIN_DIR . 'admin/partials/amazing-offer-settings-tab.php';
				break;
			case 'support':
				require AMAZING_OFFER_PLUGIN_DIR . 'admin/partials/amazing-offer-support-tab.php';
				break;
			case 'products':
			default:
				require AMAZING_OFFER_PLUGIN_DIR . 'admin/partials/amazing-offer-products-tab.php';
				break;
		}
		?>
	</div>
</div>
