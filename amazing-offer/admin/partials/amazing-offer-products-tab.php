<?php
/**
 * Products management tab.
 *
 * @package Amazing_Offer
 *
 * @var Amazing_Offer_Products $products Products manager.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$saved_products = $products->get_saved_products();
?>
<div class="amazing-offer-products-tab">

	<!-- Section 1: auto-load sale products -->
	<div class="amazing-offer-card-box">
		<div class="amazing-offer-card-head">
			<h2><span class="dashicons dashicons-tag"></span> <?php esc_html_e( 'افزودن خودکار محصولات تخفیف‌دار', 'amazing-offer' ); ?></h2>
			<button type="button" class="button button-primary" id="ao-load-sale">
				<span class="dashicons dashicons-update"></span>
				<?php esc_html_e( 'بارگذاری محصولات تخفیف‌دار', 'amazing-offer' ); ?>
			</button>
		</div>

		<div id="ao-sale-results" class="amazing-offer-sale-results"></div>

		<div class="amazing-offer-card-foot" id="ao-sale-actions" style="display:none;">
			<label class="ao-check-all"><input type="checkbox" id="ao-select-all"> <?php esc_html_e( 'انتخاب همه', 'amazing-offer' ); ?></label>
			<button type="button" class="button button-primary" id="ao-add-selected">
				<?php esc_html_e( 'افزودن موارد انتخاب شده', 'amazing-offer' ); ?>
			</button>
		</div>
	</div>

	<!-- Section 2: manual product search -->
	<div class="amazing-offer-card-box">
		<div class="amazing-offer-card-head">
			<h2><span class="dashicons dashicons-search"></span> <?php esc_html_e( 'افزودن دستی محصول', 'amazing-offer' ); ?></h2>
		</div>

		<div class="amazing-offer-search-wrap">
			<input type="text" id="ao-search-input" placeholder="<?php esc_attr_e( 'نام محصول را تایپ کنید (حداقل ۲ کاراکتر)...', 'amazing-offer' ); ?>" autocomplete="off">
			<div id="ao-search-results" class="amazing-offer-search-results"></div>
		</div>
	</div>

	<!-- Section 3: active products table -->
	<div class="amazing-offer-card-box">
		<div class="amazing-offer-card-head">
			<h2><span class="dashicons dashicons-list-view"></span> <?php esc_html_e( 'محصولات فعال در ویجت', 'amazing-offer' ); ?></h2>
			<button type="button" class="button button-primary" id="ao-save-order">
				<span class="dashicons dashicons-saved"></span>
				<?php esc_html_e( 'ذخیره ترتیب', 'amazing-offer' ); ?>
			</button>
		</div>

		<table class="widefat amazing-offer-products-table" id="ao-products-table">
			<thead>
				<tr>
					<th class="ao-col-handle"></th>
					<th class="ao-col-image"><?php esc_html_e( 'تصویر', 'amazing-offer' ); ?></th>
					<th class="ao-col-name"><?php esc_html_e( 'نام', 'amazing-offer' ); ?></th>
					<th class="ao-col-price"><?php esc_html_e( 'قیمت', 'amazing-offer' ); ?></th>
					<th class="ao-col-discount"><?php esc_html_e( 'تخفیف', 'amazing-offer' ); ?></th>
					<th class="ao-col-remove"><?php esc_html_e( 'حذف', 'amazing-offer' ); ?></th>
				</tr>
			</thead>
			<tbody id="ao-products-body">
				<?php if ( empty( $saved_products ) ) : ?>
					<tr class="ao-empty-row"><td colspan="6"><?php esc_html_e( 'هنوز محصولی اضافه نشده است.', 'amazing-offer' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $saved_products as $p ) : ?>
						<tr data-product-id="<?php echo esc_attr( $p['id'] ); ?>">
							<td class="ao-col-handle"><span class="dashicons dashicons-menu ao-drag-handle"></span></td>
							<td class="ao-col-image"><img src="<?php echo esc_url( $p['image'] ); ?>" alt="" width="48" height="48"></td>
							<td class="ao-col-name"><?php echo esc_html( $p['name'] ); ?></td>
							<td class="ao-col-price"><?php echo wp_kses_post( $p['price_html'] ); ?></td>
							<td class="ao-col-discount">
								<?php if ( $p['discount_percent'] > 0 ) : ?>
									<span class="ao-discount-badge"><?php echo esc_html( $p['discount_percent'] ); ?>٪</span>
								<?php else : ?>
									—
								<?php endif; ?>
							</td>
							<td class="ao-col-remove"><button type="button" class="button-link ao-remove-row"><span class="dashicons dashicons-trash"></span></button></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>

		<p class="amazing-offer-hint">
			<?php esc_html_e( 'برای تغییر ترتیب، ردیف‌ها را با آیکون منو بکشید و رها کنید. سپس «ذخیره ترتیب» را بزنید.', 'amazing-offer' ); ?>
		</p>
	</div>

	<div class="amazing-offer-shortcode-hint">
		<strong><?php esc_html_e( 'شورت‌کد:', 'amazing-offer' ); ?></strong>
		<code>[amazing_offer]</code>
		<?php esc_html_e( 'را در هر صفحه یا نوشته قرار دهید.', 'amazing-offer' ); ?>
	</div>
</div>
