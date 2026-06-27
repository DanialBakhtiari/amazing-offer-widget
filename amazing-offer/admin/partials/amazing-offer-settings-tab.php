<?php
/**
 * Slider settings tab.
 *
 * @package Amazing_Offer
 *
 * @var Amazing_Offer_Settings $settings Settings manager.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$s = $settings->get_all();

/**
 * Helper to print a toggle switch.
 *
 * @param string $name    Setting key.
 * @param bool   $checked Current state.
 * @return void
 */
$ao_toggle = function ( $name, $checked ) {
	printf(
		'<label class="ao-switch"><input type="hidden" name="amazing_offer[%1$s]" value="0"><input type="checkbox" name="amazing_offer[%1$s]" value="1" %2$s><span class="ao-slider-ui"></span></label>',
		esc_attr( $name ),
		checked( (bool) $checked, true, false )
	);
};

$status = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>
<?php if ( 'saved' === $status ) : ?>
	<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'تنظیمات ذخیره شد.', 'amazing-offer' ); ?></p></div>
<?php elseif ( 'reset' === $status ) : ?>
	<div class="notice notice-warning is-dismissible"><p><?php esc_html_e( 'تنظیمات به حالت پیش‌فرض بازگشت.', 'amazing-offer' ); ?></p></div>
<?php endif; ?>

<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="amazing-offer-settings-form">
	<input type="hidden" name="action" value="amazing_offer_save_settings">
	<?php wp_nonce_field( 'amazing_offer_save_settings', 'amazing_offer_settings_nonce' ); ?>

	<!-- Title settings -->
	<div class="amazing-offer-card-box">
		<div class="amazing-offer-card-head"><h2><span class="dashicons dashicons-editor-textcolor"></span> <?php esc_html_e( 'تنظیمات عنوان', 'amazing-offer' ); ?></h2></div>
		<div class="ao-field"><label><?php esc_html_e( 'عنوان بخش', 'amazing-offer' ); ?></label><input type="text" name="amazing_offer[title]" value="<?php echo esc_attr( $s['title'] ); ?>"></div>
		<div class="ao-field ao-field-inline"><label><?php esc_html_e( 'نمایش آیکون برق کنار عنوان', 'amazing-offer' ); ?></label><?php $ao_toggle( 'show_icon', $s['show_icon'] ); ?></div>
		<div class="ao-field"><label><?php esc_html_e( 'زیرعنوان', 'amazing-offer' ); ?></label><input type="text" name="amazing_offer[subtitle]" value="<?php echo esc_attr( $s['subtitle'] ); ?>"></div>
	</div>

	<!-- Timer settings -->
	<div class="amazing-offer-card-box">
		<div class="amazing-offer-card-head"><h2><span class="dashicons dashicons-clock"></span> <?php esc_html_e( 'تنظیمات تایمر', 'amazing-offer' ); ?></h2></div>
		<div class="ao-field ao-field-inline"><label><?php esc_html_e( 'نمایش تایمر شمارش معکوس', 'amazing-offer' ); ?></label><?php $ao_toggle( 'show_timer', $s['show_timer'] ); ?></div>

		<div class="ao-field">
			<label><?php esc_html_e( 'نوع تایمر', 'amazing-offer' ); ?></label>
			<div class="ao-radio-group">
				<label><input type="radio" name="amazing_offer[timer_type]" value="midnight" <?php checked( $s['timer_type'], 'midnight' ); ?>> <?php esc_html_e( 'هر روز تا نیمه‌شب', 'amazing-offer' ); ?></label>
				<label><input type="radio" name="amazing_offer[timer_type]" value="duration" <?php checked( $s['timer_type'], 'duration' ); ?>> <?php esc_html_e( 'مدت زمان ثابت از اولین بازدید', 'amazing-offer' ); ?></label>
				<label><input type="radio" name="amazing_offer[timer_type]" value="fixed_date" <?php checked( $s['timer_type'], 'fixed_date' ); ?>> <?php esc_html_e( 'تاریخ پایان مشخص', 'amazing-offer' ); ?></label>
			</div>
		</div>

		<div class="ao-field ao-timer-dep" data-dep="duration"><label><?php esc_html_e( 'مدت زمان (ساعت)', 'amazing-offer' ); ?></label><input type="number" min="1" max="720" name="amazing_offer[timer_duration]" value="<?php echo esc_attr( $s['timer_duration'] ); ?>"></div>
		<div class="ao-field ao-timer-dep" data-dep="fixed_date"><label><?php esc_html_e( 'تاریخ و ساعت پایان', 'amazing-offer' ); ?></label><input type="datetime-local" name="amazing_offer[timer_end_date]" value="<?php echo esc_attr( $s['timer_end_date'] ); ?>"></div>
	</div>

	<!-- Slider settings -->
	<div class="amazing-offer-card-box">
		<div class="amazing-offer-card-head"><h2><span class="dashicons dashicons-images-alt2"></span> <?php esc_html_e( 'تنظیمات اسلایدر', 'amazing-offer' ); ?></h2></div>

		<div class="ao-field">
			<label><?php esc_html_e( 'حالت نمایش', 'amazing-offer' ); ?></label>
			<div class="ao-radio-group">
				<label><input type="radio" name="amazing_offer[slider_mode]" value="auto" <?php checked( $s['slider_mode'], 'auto' ); ?>> <?php esc_html_e( 'اسلایدر خودکار', 'amazing-offer' ); ?></label>
				<label><input type="radio" name="amazing_offer[slider_mode]" value="manual" <?php checked( $s['slider_mode'], 'manual' ); ?>> <?php esc_html_e( 'اسلایدر دستی', 'amazing-offer' ); ?></label>
				<label><input type="radio" name="amazing_offer[slider_mode]" value="grid" <?php checked( $s['slider_mode'], 'grid' ); ?>> <?php esc_html_e( 'گرید ثابت', 'amazing-offer' ); ?></label>
			</div>
		</div>

		<div class="ao-field"><label><?php esc_html_e( 'سرعت اسلایدر (میلی‌ثانیه)', 'amazing-offer' ); ?></label>
			<div class="ao-range-wrap">
				<input type="range" min="500" max="5000" step="100" name="amazing_offer[slider_speed]" value="<?php echo esc_attr( $s['slider_speed'] ); ?>" oninput="this.nextElementSibling.textContent=this.value+' ms'">
				<output><?php echo esc_html( $s['slider_speed'] ); ?> ms</output>
			</div>
		</div>

		<div class="ao-field ao-field-grid3">
			<div><label><?php esc_html_e( 'کارت در موبایل', 'amazing-offer' ); ?></label><input type="number" min="1" max="2" name="amazing_offer[slider_cards_mobile]" value="<?php echo esc_attr( $s['slider_cards_mobile'] ); ?>"></div>
			<div><label><?php esc_html_e( 'کارت در تبلت', 'amazing-offer' ); ?></label><input type="number" min="2" max="3" name="amazing_offer[slider_cards_tablet]" value="<?php echo esc_attr( $s['slider_cards_tablet'] ); ?>"></div>
			<div><label><?php esc_html_e( 'کارت در دسکتاپ', 'amazing-offer' ); ?></label><input type="number" min="3" max="6" name="amazing_offer[slider_cards_desktop]" value="<?php echo esc_attr( $s['slider_cards_desktop'] ); ?>"></div>
		</div>

		<div class="ao-field ao-field-inline"><label><?php esc_html_e( 'حالت لوپ (Loop)', 'amazing-offer' ); ?></label><?php $ao_toggle( 'slider_loop', $s['slider_loop'] ); ?></div>
		<div class="ao-field ao-field-inline"><label><?php esc_html_e( 'نمایش دکمه‌های ناوبری', 'amazing-offer' ); ?></label><?php $ao_toggle( 'show_nav', $s['show_nav'] ); ?></div>
		<div class="ao-field ao-field-inline"><label><?php esc_html_e( 'نمایش نقاط (Dots)', 'amazing-offer' ); ?></label><?php $ao_toggle( 'show_dots', $s['show_dots'] ); ?></div>
		<div class="ao-field ao-field-inline"><label><?php esc_html_e( 'توقف هنگام هاور', 'amazing-offer' ); ?></label><?php $ao_toggle( 'pause_on_hover', $s['pause_on_hover'] ); ?></div>
	</div>

	<!-- Card settings -->
	<div class="amazing-offer-card-box">
		<div class="amazing-offer-card-head"><h2><span class="dashicons dashicons-id"></span> <?php esc_html_e( 'تنظیمات کارت محصول', 'amazing-offer' ); ?></h2></div>
		<div class="ao-field ao-field-inline"><label><?php esc_html_e( 'نمایش قیمت اصلی خط‌خورده', 'amazing-offer' ); ?></label><?php $ao_toggle( 'show_original_price', $s['show_original_price'] ); ?></div>
		<div class="ao-field ao-field-inline"><label><?php esc_html_e( 'نمایش بَج درصد تخفیف', 'amazing-offer' ); ?></label><?php $ao_toggle( 'show_discount_badge', $s['show_discount_badge'] ); ?></div>
		<div class="ao-field ao-field-inline"><label><?php esc_html_e( 'رنگ بَج تخفیف', 'amazing-offer' ); ?></label><input type="text" class="ao-color-picker" name="amazing_offer[badge_color]" value="<?php echo esc_attr( $s['badge_color'] ); ?>"></div>
		<div class="ao-field ao-field-inline"><label><?php esc_html_e( 'نمایش دکمه افزودن به سبد', 'amazing-offer' ); ?></label><?php $ao_toggle( 'show_add_to_cart', $s['show_add_to_cart'] ); ?></div>
		<div class="ao-field"><label><?php esc_html_e( 'متن دکمه', 'amazing-offer' ); ?></label><input type="text" name="amazing_offer[cart_button_text]" value="<?php echo esc_attr( $s['cart_button_text'] ); ?>"></div>
		<div class="ao-field ao-field-inline"><label><?php esc_html_e( 'رنگ دکمه', 'amazing-offer' ); ?></label><input type="text" class="ao-color-picker" name="amazing_offer[button_color]" value="<?php echo esc_attr( $s['button_color'] ); ?>"></div>
		<div class="ao-field ao-field-inline"><label><?php esc_html_e( 'رنگ هاور دکمه', 'amazing-offer' ); ?></label><input type="text" class="ao-color-picker" name="amazing_offer[button_hover_color]" value="<?php echo esc_attr( $s['button_hover_color'] ); ?>"></div>
	</div>

	<!-- Extra modules -->
	<div class="amazing-offer-card-box">
		<div class="amazing-offer-card-head"><h2><span class="dashicons dashicons-admin-plugins"></span> <?php esc_html_e( 'تنظیمات ماژول‌های اضافه', 'amazing-offer' ); ?></h2></div>
		<div class="ao-field ao-field-inline"><label><?php esc_html_e( 'ماژول روزشمار (تعداد روز باقیمانده)', 'amazing-offer' ); ?></label><?php $ao_toggle( 'show_countdown_days', $s['show_countdown_days'] ); ?></div>
		<div class="ao-field ao-field-inline"><label><?php esc_html_e( 'ماژول موجودی (تعداد باقیمانده)', 'amazing-offer' ); ?></label><?php $ao_toggle( 'show_stock', $s['show_stock'] ); ?></div>
		<div class="ao-field ao-field-inline"><label><?php esc_html_e( 'ماژول تعداد خریداران', 'amazing-offer' ); ?></label><?php $ao_toggle( 'show_buyers_count', $s['show_buyers_count'] ); ?></div>
		<div class="ao-field"><label><?php esc_html_e( 'عدد پایه تعداد خریداران', 'amazing-offer' ); ?></label><input type="number" min="0" name="amazing_offer[buyers_count_base]" value="<?php echo esc_attr( $s['buyers_count_base'] ); ?>"></div>
	</div>

	<div class="amazing-offer-form-actions">
		<button type="submit" class="button button-primary button-hero"><span class="dashicons dashicons-saved"></span> <?php esc_html_e( 'ذخیره تنظیمات', 'amazing-offer' ); ?></button>
		<button type="submit" name="amazing_offer_reset" value="1" class="button button-secondary" onclick="return confirm('<?php echo esc_js( __( 'همه تنظیمات به پیش‌فرض بازگردد؟', 'amazing-offer' ) ); ?>');"><?php esc_html_e( 'بازنشانی', 'amazing-offer' ); ?></button>
	</div>
</form>
