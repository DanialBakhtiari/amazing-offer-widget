<?php
/**
 * Template list screen.
 *
 * @package Amazing_Offer\Special_Offer
 *
 * @var Amazing_Offer_SO_Repository $repository Repository.
 * @var WP_Post[]                   $templates  Templates ordered by menu_order.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap ao-so-wrap" dir="rtl">

	<div class="ao-so-topbar">
		<div class="ao-so-brand">
			<span class="dashicons dashicons-tag"></span>
			<div>
				<h1><?php esc_html_e( 'پیشنهاد ویژه', 'amazing-offer' ); ?></h1>
				<p><?php esc_html_e( 'طرح‌های نامحدود اسلایدر محصولات با تایمر و افکت', 'amazing-offer' ); ?></p>
			</div>
		</div>
		<div class="ao-so-topbar-actions">
			<button type="button" class="button" id="ao-so-import-btn">
				<span class="dashicons dashicons-upload"></span>
				<?php esc_html_e( 'ورود از فایل', 'amazing-offer' ); ?>
			</button>
			<input type="file" id="ao-so-import-file" accept="application/json,.json" style="display:none">
			<button type="button" class="button button-primary button-hero" id="ao-so-new">
				<span class="dashicons dashicons-plus-alt2"></span>
				<?php esc_html_e( 'ساخت طرح جدید', 'amazing-offer' ); ?>
			</button>
		</div>
	</div>

	<div class="ao-so-card-box">
		<table class="widefat ao-so-table" id="ao-so-table">
			<thead>
				<tr>
					<th class="ao-so-col-handle"></th>
					<th class="ao-so-col-name"><?php esc_html_e( 'نام طرح', 'amazing-offer' ); ?></th>
					<th class="ao-so-col-count"><?php esc_html_e( 'محصولات', 'amazing-offer' ); ?></th>
					<th class="ao-so-col-shortcode"><?php esc_html_e( 'شورت‌کد', 'amazing-offer' ); ?></th>
					<th class="ao-so-col-status"><?php esc_html_e( 'فعال', 'amazing-offer' ); ?></th>
					<th class="ao-so-col-actions"><?php esc_html_e( 'عملیات', 'amazing-offer' ); ?></th>
				</tr>
			</thead>
			<tbody id="ao-so-rows">
				<?php if ( empty( $templates ) ) : ?>
					<tr class="ao-so-empty-row"><td colspan="6"><?php esc_html_e( 'هنوز طرحی ساخته نشده. روی «ساخت طرح جدید» بزنید.', 'amazing-offer' ); ?></td></tr>
				<?php else : ?>
					<?php
					foreach ( $templates as $tpl ) :
						$config = Amazing_Offer_SO_Schema::load( $tpl->ID );
						$count  = count( $config['source']['product_ids'] );
						$active = ( 'publish' === $tpl->post_status );
						?>
						<tr data-id="<?php echo esc_attr( $tpl->ID ); ?>">
							<td class="ao-so-col-handle"><span class="dashicons dashicons-menu ao-so-drag"></span></td>
							<td class="ao-so-col-name">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . Amazing_Offer_SO_Admin::PAGE_SLUG . '&action=edit&id=' . $tpl->ID ) ); ?>">
									<strong><?php echo esc_html( $tpl->post_title ); ?></strong>
								</a>
							</td>
							<td class="ao-so-col-count"><?php echo esc_html( $count ); ?></td>
							<td class="ao-so-col-shortcode"><code>[special_offer id="<?php echo esc_attr( $tpl->ID ); ?>"]</code></td>
							<td class="ao-so-col-status">
								<label class="ao-so-switch">
									<input type="checkbox" class="ao-so-toggle" <?php checked( $active ); ?>>
									<span class="ao-so-slider-ui"></span>
								</label>
							</td>
							<td class="ao-so-col-actions">
								<a class="button button-small" href="<?php echo esc_url( admin_url( 'admin.php?page=' . Amazing_Offer_SO_Admin::PAGE_SLUG . '&action=edit&id=' . $tpl->ID ) ); ?>"><?php esc_html_e( 'ویرایش', 'amazing-offer' ); ?></a>
								<button type="button" class="button button-small ao-so-duplicate"><?php esc_html_e( 'تکثیر', 'amazing-offer' ); ?></button>
								<a class="button button-small" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ao_so_export&id=' . $tpl->ID ), 'ao_so_export_' . $tpl->ID ) ); ?>"><?php esc_html_e( 'خروجی', 'amazing-offer' ); ?></a>
								<button type="button" class="button button-small ao-so-delete"><span class="dashicons dashicons-trash"></span></button>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
		<p class="ao-so-hint"><?php esc_html_e( 'برای تغییر ترتیب، ردیف‌ها را بکشید و رها کنید (ذخیره خودکار).', 'amazing-offer' ); ?></p>
	</div>
</div>
