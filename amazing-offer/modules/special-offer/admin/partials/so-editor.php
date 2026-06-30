<?php
/**
 * Template editor screen (tabbed form, AJAX-saved).
 *
 * @package Amazing_Offer\Special_Offer
 *
 * @var WP_Post                     $post       Template post.
 * @var array                      $config     Loaded + normalized config.
 * @var Amazing_Offer_SO_Repository $repository Repository.
 * @var Amazing_Offer_Products      $products   Products manager.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render a toggle switch bound to a config key path.
 *
 * @param string $name    Full input name, e.g. config[show_nav].
 * @param bool   $checked Current state.
 * @return void
 */
$ao_toggle = function ( $name, $checked ) {
	printf(
		'<label class="ao-so-switch"><input type="hidden" name="%1$s" value="0"><input type="checkbox" name="%1$s" value="1" %2$s><span class="ao-so-slider-ui"></span></label>',
		esc_attr( $name ),
		checked( (bool) $checked, true, false )
	);
};

$cats = array();
if ( taxonomy_exists( 'product_cat' ) ) {
	$terms = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false, 'number' => 200 ) );
	if ( ! is_wp_error( $terms ) ) {
		$cats = $terms;
	}
}

// Pre-resolve selected products for the picker list.
$selected = array();
foreach ( $config['source']['product_ids'] as $pid ) {
	$data = $products->get_product_data( (int) $pid );
	if ( ! empty( $data ) ) {
		$selected[] = $data;
	}
}

$src      = $config['source'];
$style    = $config['style'];
$banner   = $config['banner'];
$resp_m   = $config['responsive']['mobile'];
$resp_t   = $config['responsive']['tablet'];
$resp_d   = isset( $config['responsive']['desktop'] ) ? $config['responsive']['desktop'] : array();
?>
<div class="wrap ao-so-wrap ao-so-editor" dir="rtl" id="ao-so-editor" data-id="<?php echo esc_attr( $post->ID ); ?>">

	<div class="ao-so-topbar">
		<div class="ao-so-brand">
			<a class="ao-so-back" href="<?php echo esc_url( admin_url( 'admin.php?page=' . Amazing_Offer_SO_Admin::PAGE_SLUG ) ); ?>"><span class="dashicons dashicons-arrow-right-alt"></span></a>
			<div>
				<input type="text" id="ao-so-title" value="<?php echo esc_attr( $post->post_title ); ?>" class="ao-so-title-input">
				<p><code>[special_offer id="<?php echo esc_attr( $post->ID ); ?>"]</code></p>
			</div>
		</div>
		<div class="ao-so-topbar-actions">
			<span class="ao-so-save-feedback" aria-live="polite"></span>
			<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ao_so_export&id=' . $post->ID ), 'ao_so_export_' . $post->ID ) ); ?>"><span class="dashicons dashicons-download"></span> <?php esc_html_e( 'خروجی', 'amazing-offer' ); ?></a>
			<button type="button" class="button button-primary button-hero" id="ao-so-save"><span class="dashicons dashicons-saved"></span> <?php esc_html_e( 'ذخیره', 'amazing-offer' ); ?></button>
		</div>
	</div>

	<div class="ao-so-editor-layout">
		<div class="ao-so-editor-main">
			<nav class="ao-so-tabs">
				<a class="ao-so-tab is-active" data-tab="products"><span class="dashicons dashicons-cart"></span> <?php esc_html_e( 'محصولات', 'amazing-offer' ); ?></a>
				<a class="ao-so-tab" data-tab="style"><span class="dashicons dashicons-art"></span> <?php esc_html_e( 'استایل', 'amazing-offer' ); ?></a>
				<a class="ao-so-tab" data-tab="slider"><span class="dashicons dashicons-images-alt2"></span> <?php esc_html_e( 'اسلایدر', 'amazing-offer' ); ?></a>
				<a class="ao-so-tab" data-tab="timer"><span class="dashicons dashicons-clock"></span> <?php esc_html_e( 'تایمر', 'amazing-offer' ); ?></a>
				<a class="ao-so-tab" data-tab="card"><span class="dashicons dashicons-id"></span> <?php esc_html_e( 'کارت', 'amazing-offer' ); ?></a>
				<a class="ao-so-tab" data-tab="banner"><span class="dashicons dashicons-format-image"></span> <?php esc_html_e( 'بنر', 'amazing-offer' ); ?></a>
				<a class="ao-so-tab" data-tab="responsive"><span class="dashicons dashicons-smartphone"></span> <?php esc_html_e( 'ریسپانسیو', 'amazing-offer' ); ?></a>
			</nav>

			<form id="ao-so-form" onsubmit="return false;">

			<!-- Products -->
			<section class="ao-so-panel is-active" data-panel="products">
				<div class="ao-so-field">
					<label><?php esc_html_e( 'منبع محصولات', 'amazing-offer' ); ?></label>
					<div class="ao-so-radio-group">
						<label><input type="radio" name="config[source][type]" value="manual" <?php checked( $src['type'], 'manual' ); ?>> <?php esc_html_e( 'انتخاب دستی', 'amazing-offer' ); ?></label>
						<label><input type="radio" name="config[source][type]" value="saved" <?php checked( $src['type'], 'saved' ); ?>> <?php esc_html_e( 'لیست ذخیره‌شدهٔ این طرح', 'amazing-offer' ); ?></label>
						<label><input type="radio" name="config[source][type]" value="sale" <?php checked( $src['type'], 'sale' ); ?>> <?php esc_html_e( 'همهٔ تخفیف‌دارها', 'amazing-offer' ); ?></label>
						<label><input type="radio" name="config[source][type]" value="category" <?php checked( $src['type'], 'category' ); ?>> <?php esc_html_e( 'دستهٔ خاص', 'amazing-offer' ); ?></label>
					</div>
				</div>

				<div class="ao-so-field ao-so-dep" data-dep-type="category">
					<label><?php esc_html_e( 'دسته', 'amazing-offer' ); ?></label>
					<select name="config[source][category]">
						<option value="0"><?php esc_html_e( '— انتخاب دسته —', 'amazing-offer' ); ?></option>
						<?php foreach ( $cats as $cat ) : ?>
							<option value="<?php echo esc_attr( $cat->term_id ); ?>" <?php selected( (int) $src['category'], (int) $cat->term_id ); ?>><?php echo esc_html( $cat->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="ao-so-field">
					<label><?php esc_html_e( 'حداکثر تعداد محصول', 'amazing-offer' ); ?></label>
					<input type="number" min="1" max="50" name="config[source][limit]" value="<?php echo esc_attr( $src['limit'] ); ?>">
				</div>

				<div class="ao-so-dep" data-dep-type="sale">
					<div class="ao-so-field ao-so-field-inline">
						<label><?php esc_html_e( 'همگام‌سازی خودکار تخفیف‌دارها', 'amazing-offer' ); ?></label>
						<?php $ao_toggle( 'config[source][auto_sync]', $src['auto_sync'] ); ?>
					</div>
				</div>

				<div class="ao-so-dep" data-dep-type="manual,saved">
					<div class="ao-so-field">
						<label><?php esc_html_e( 'افزودن محصول', 'amazing-offer' ); ?></label>
						<div class="ao-so-picker">
							<button type="button" class="button" id="ao-so-load-sale"><?php esc_html_e( 'بارگذاری تخفیف‌دارها', 'amazing-offer' ); ?></button>
							<div class="ao-so-search-wrap">
								<input type="text" id="ao-so-search" placeholder="<?php esc_attr_e( 'جستجوی نام محصول (حداقل ۲ کاراکتر)...', 'amazing-offer' ); ?>" autocomplete="off">
								<div class="ao-so-search-results" id="ao-so-search-results"></div>
							</div>
						</div>
						<div class="ao-so-sale-results" id="ao-so-sale-results"></div>
					</div>

					<div class="ao-so-field">
						<label><?php esc_html_e( 'محصولات انتخاب‌شده (کشیدن برای ترتیب)', 'amazing-offer' ); ?></label>
						<ul class="ao-so-selected" id="ao-so-selected">
							<?php foreach ( $selected as $p ) : ?>
								<li data-id="<?php echo esc_attr( $p['id'] ); ?>">
									<span class="dashicons dashicons-menu ao-so-drag"></span>
									<img src="<?php echo esc_url( $p['image'] ); ?>" alt="">
									<span class="ao-so-sel-name"><?php echo esc_html( $p['name'] ); ?></span>
									<input type="hidden" name="config[source][product_ids][]" value="<?php echo esc_attr( $p['id'] ); ?>">
									<button type="button" class="ao-so-sel-remove"><span class="dashicons dashicons-no-alt"></span></button>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</section>

			<!-- Style -->
			<section class="ao-so-panel" data-panel="style">
				<div class="ao-so-field"><label><?php esc_html_e( 'عنوان', 'amazing-offer' ); ?></label><input type="text" name="config[title]" value="<?php echo esc_attr( $config['title'] ); ?>"></div>
				<div class="ao-so-field"><label><?php esc_html_e( 'زیرعنوان', 'amazing-offer' ); ?></label><input type="text" name="config[subtitle]" value="<?php echo esc_attr( $config['subtitle'] ); ?>"></div>
				<div class="ao-so-field ao-so-field-inline"><label><?php esc_html_e( 'رنگ متن عنوان', 'amazing-offer' ); ?></label><input type="text" class="ao-so-color" name="config[title_color]" value="<?php echo esc_attr( $config['title_color'] ); ?>" data-default-color=""></div>
				<div class="ao-so-field ao-so-field-inline"><label><?php esc_html_e( 'رنگ متن زیرعنوان', 'amazing-offer' ); ?></label><input type="text" class="ao-so-color" name="config[subtitle_color]" value="<?php echo esc_attr( $config['subtitle_color'] ); ?>" data-default-color=""></div>
				<div class="ao-so-field ao-so-field-inline"><label><?php esc_html_e( 'آیکون برق کنار عنوان', 'amazing-offer' ); ?></label><?php $ao_toggle( 'config[show_icon]', $config['show_icon'] ); ?></div>
				<div class="ao-so-field ao-so-field-inline"><label><?php esc_html_e( 'رنگ دکمه', 'amazing-offer' ); ?></label><input type="text" class="ao-so-color" name="config[button_color]" value="<?php echo esc_attr( $config['button_color'] ); ?>"></div>
				<div class="ao-so-field ao-so-field-inline"><label><?php esc_html_e( 'رنگ هاور دکمه', 'amazing-offer' ); ?></label><input type="text" class="ao-so-color" name="config[button_hover_color]" value="<?php echo esc_attr( $config['button_hover_color'] ); ?>"></div>
				<div class="ao-so-field ao-so-field-inline"><label><?php esc_html_e( 'رنگ بَج تخفیف', 'amazing-offer' ); ?></label><input type="text" class="ao-so-color" name="config[badge_color]" value="<?php echo esc_attr( $config['badge_color'] ); ?>"></div>
				<div class="ao-so-field ao-so-field-inline"><label><?php esc_html_e( 'رنگ پس‌زمینهٔ کارت', 'amazing-offer' ); ?></label><input type="text" class="ao-so-color" name="config[style][card_bg]" value="<?php echo esc_attr( $style['card_bg'] ); ?>"></div>
				<div class="ao-so-field ao-so-field-inline"><label><?php esc_html_e( 'رنگ پس‌زمینهٔ بخش (اختیاری)', 'amazing-offer' ); ?></label><input type="text" class="ao-so-color" name="config[style][section_bg]" value="<?php echo esc_attr( $style['section_bg'] ); ?>"></div>
				<div class="ao-so-field"><label><?php esc_html_e( 'گردی گوشهٔ کارت (px)', 'amazing-offer' ); ?></label><input type="number" min="0" max="60" name="config[style][radius]" value="<?php echo esc_attr( $style['radius'] ); ?>"></div>
				<div class="ao-so-field"><label><?php esc_html_e( 'فاصلهٔ بین کارت‌ها (px)', 'amazing-offer' ); ?></label><input type="number" min="0" max="80" name="config[style][gap]" value="<?php echo esc_attr( $style['gap'] ); ?>"></div>

				<h3><?php esc_html_e( 'متن کارت', 'amazing-offer' ); ?></h3>
				<div class="ao-so-field"><label><?php esc_html_e( 'تعداد خط عنوان (۱ تا ۳)', 'amazing-offer' ); ?></label><input type="number" min="1" max="3" name="config[title_lines]" value="<?php echo esc_attr( $config['title_lines'] ); ?>"></div>
				<div class="ao-so-field ao-so-field-inline"><label><?php esc_html_e( 'نمایش توضیحات کوتاه محصول', 'amazing-offer' ); ?></label><?php $ao_toggle( 'config[show_description]', $config['show_description'] ); ?></div>
				<div class="ao-so-field"><label><?php esc_html_e( 'تعداد خط توضیحات (۱ تا ۴)', 'amazing-offer' ); ?></label><input type="number" min="1" max="4" name="config[desc_lines]" value="<?php echo esc_attr( $config['desc_lines'] ); ?>"></div>

				<h3><?php esc_html_e( 'دکمهٔ «دیدن همه»', 'amazing-offer' ); ?></h3>
				<div class="ao-so-field ao-so-field-inline"><label><?php esc_html_e( 'نمایش دکمه', 'amazing-offer' ); ?></label><?php $ao_toggle( 'config[show_see_all]', $config['show_see_all'] ); ?></div>
				<div class="ao-so-field"><label><?php esc_html_e( 'متن دکمه', 'amazing-offer' ); ?></label><input type="text" name="config[see_all_text]" value="<?php echo esc_attr( $config['see_all_text'] ); ?>"></div>
				<div class="ao-so-field"><label><?php esc_html_e( 'لینک دکمه', 'amazing-offer' ); ?></label><input type="url" name="config[see_all_url]" value="<?php echo esc_attr( $config['see_all_url'] ); ?>"></div>
			</section>

			<!-- Slider -->
			<section class="ao-so-panel" data-panel="slider">
				<div class="ao-so-field">
					<label><?php esc_html_e( 'افکت', 'amazing-offer' ); ?></label>
					<div class="ao-so-radio-group ao-so-radio-inline">
						<?php foreach ( array( 'slide' => 'اسلاید', 'fade' => 'محو', 'coverflow' => 'کاورفلو', 'cards' => 'کارتی', 'grid' => 'گرید ثابت' ) as $val => $lbl ) : ?>
							<label><input type="radio" name="config[effect]" value="<?php echo esc_attr( $val ); ?>" <?php checked( $config['effect'], $val ); ?>> <?php echo esc_html( $lbl ); ?></label>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="ao-so-field">
					<label><?php esc_html_e( 'حالت پخش', 'amazing-offer' ); ?></label>
					<div class="ao-so-radio-group ao-so-radio-inline">
						<label><input type="radio" name="config[slider_mode]" value="auto" <?php checked( $config['slider_mode'], 'auto' ); ?>> <?php esc_html_e( 'خودکار', 'amazing-offer' ); ?></label>
						<label><input type="radio" name="config[slider_mode]" value="manual" <?php checked( $config['slider_mode'], 'manual' ); ?>> <?php esc_html_e( 'دستی', 'amazing-offer' ); ?></label>
					</div>
				</div>
				<div class="ao-so-field"><label><?php esc_html_e( 'سرعت/تأخیر (ms)', 'amazing-offer' ); ?></label><input type="number" min="500" max="5000" step="100" name="config[slider_speed]" value="<?php echo esc_attr( $config['slider_speed'] ); ?>"></div>
				<div class="ao-so-field ao-so-field-grid3">
					<div><label><?php esc_html_e( 'کارت موبایل', 'amazing-offer' ); ?></label><input type="number" min="1" max="2" name="config[slider_cards_mobile]" value="<?php echo esc_attr( $config['slider_cards_mobile'] ); ?>"></div>
					<div><label><?php esc_html_e( 'کارت تبلت', 'amazing-offer' ); ?></label><input type="number" min="2" max="3" name="config[slider_cards_tablet]" value="<?php echo esc_attr( $config['slider_cards_tablet'] ); ?>"></div>
					<div><label><?php esc_html_e( 'کارت دسکتاپ', 'amazing-offer' ); ?></label><input type="number" min="3" max="6" name="config[slider_cards_desktop]" value="<?php echo esc_attr( $config['slider_cards_desktop'] ); ?>"></div>
				</div>
				<div class="ao-so-field ao-so-field-inline"><label><?php esc_html_e( 'لوپ', 'amazing-offer' ); ?></label><?php $ao_toggle( 'config[slider_loop]', $config['slider_loop'] ); ?></div>
				<div class="ao-so-field ao-so-field-inline"><label><?php esc_html_e( 'دکمه‌های ناوبری', 'amazing-offer' ); ?></label><?php $ao_toggle( 'config[show_nav]', $config['show_nav'] ); ?></div>
				<div class="ao-so-field ao-so-field-inline"><label><?php esc_html_e( 'نقاط (dots)', 'amazing-offer' ); ?></label><?php $ao_toggle( 'config[show_dots]', $config['show_dots'] ); ?></div>
				<div class="ao-so-field ao-so-field-inline"><label><?php esc_html_e( 'توقف هنگام هاور', 'amazing-offer' ); ?></label><?php $ao_toggle( 'config[pause_on_hover]', $config['pause_on_hover'] ); ?></div>
			</section>

			<!-- Timer -->
			<section class="ao-so-panel" data-panel="timer">
				<div class="ao-so-field ao-so-field-inline"><label><?php esc_html_e( 'نمایش تایمر', 'amazing-offer' ); ?></label><?php $ao_toggle( 'config[show_timer]', $config['show_timer'] ); ?></div>
				<div class="ao-so-field">
					<label><?php esc_html_e( 'نوع تایمر', 'amazing-offer' ); ?></label>
					<div class="ao-so-radio-group">
						<label><input type="radio" name="config[timer_type]" value="midnight" <?php checked( $config['timer_type'], 'midnight' ); ?>> <?php esc_html_e( 'هر روز تا نیمه‌شب', 'amazing-offer' ); ?></label>
						<label><input type="radio" name="config[timer_type]" value="duration" <?php checked( $config['timer_type'], 'duration' ); ?>> <?php esc_html_e( 'مدت ثابت از اولین بازدید', 'amazing-offer' ); ?></label>
						<label><input type="radio" name="config[timer_type]" value="fixed_date" <?php checked( $config['timer_type'], 'fixed_date' ); ?>> <?php esc_html_e( 'تاریخ پایان مشخص', 'amazing-offer' ); ?></label>
					</div>
				</div>
				<div class="ao-so-field ao-so-dep" data-dep-timer="duration"><label><?php esc_html_e( 'مدت (ساعت)', 'amazing-offer' ); ?></label><input type="number" min="1" max="720" name="config[timer_duration]" value="<?php echo esc_attr( $config['timer_duration'] ); ?>"></div>
				<div class="ao-so-field ao-so-dep" data-dep-timer="fixed_date"><label><?php esc_html_e( 'تاریخ و ساعت پایان', 'amazing-offer' ); ?></label><input type="datetime-local" name="config[timer_end_date]" value="<?php echo esc_attr( $config['timer_end_date'] ); ?>"></div>
			</section>

			<!-- Card -->
			<section class="ao-so-panel" data-panel="card">
				<div class="ao-so-field ao-so-field-inline"><label><?php esc_html_e( 'قیمت اصلی خط‌خورده', 'amazing-offer' ); ?></label><?php $ao_toggle( 'config[show_original_price]', $config['show_original_price'] ); ?></div>
				<div class="ao-so-field ao-so-field-inline"><label><?php esc_html_e( 'بَج درصد تخفیف', 'amazing-offer' ); ?></label><?php $ao_toggle( 'config[show_discount_badge]', $config['show_discount_badge'] ); ?></div>
				<div class="ao-so-field ao-so-field-inline"><label><?php esc_html_e( 'دکمهٔ افزودن به سبد', 'amazing-offer' ); ?></label><?php $ao_toggle( 'config[show_add_to_cart]', $config['show_add_to_cart'] ); ?></div>
				<div class="ao-so-field"><label><?php esc_html_e( 'متن دکمه', 'amazing-offer' ); ?></label><input type="text" name="config[cart_button_text]" value="<?php echo esc_attr( $config['cart_button_text'] ); ?>"></div>
			</section>

			<!-- Banner -->
			<section class="ao-so-panel" data-panel="banner">
				<div class="ao-so-field">
					<label><?php esc_html_e( 'موقعیت بنر', 'amazing-offer' ); ?></label>
					<div class="ao-so-radio-group ao-so-radio-inline">
						<?php foreach ( array( 'hidden' => 'مخفی', 'right' => 'راست', 'left' => 'چپ', 'top' => 'بالا' ) as $val => $lbl ) : ?>
							<label><input type="radio" name="config[banner][position]" value="<?php echo esc_attr( $val ); ?>" <?php checked( $banner['position'], $val ); ?>> <?php echo esc_html( $lbl ); ?></label>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="ao-so-field">
					<label><?php esc_html_e( 'حالت برازش تصویر', 'amazing-offer' ); ?></label>
					<div class="ao-so-radio-group ao-so-radio-inline">
						<label><input type="radio" name="config[banner][fit]" value="cover" <?php checked( $banner['fit'], 'cover' ); ?>> <?php esc_html_e( 'پرکردن کامل (برش لبه‌ها)', 'amazing-offer' ); ?></label>
						<label><input type="radio" name="config[banner][fit]" value="contain" <?php checked( $banner['fit'], 'contain' ); ?>> <?php esc_html_e( 'نمایش کامل (بدون برش)', 'amazing-offer' ); ?></label>
					</div>
				</div>
				<div class="ao-so-banner-rec" id="ao-so-banner-rec"><?php esc_html_e( 'برای محاسبهٔ ابعاد پیشنهادی، موقعیت بنر را تنظیم کنید.', 'amazing-offer' ); ?></div>
				<div class="ao-so-field">
					<label><?php esc_html_e( 'تصویر بنر', 'amazing-offer' ); ?></label>
					<div class="ao-so-media">
						<input type="hidden" name="config[banner][image_id]" id="ao-so-banner-id" value="<?php echo esc_attr( $banner['image_id'] ); ?>">
						<input type="url" name="config[banner][image]" id="ao-so-banner-url" placeholder="https://..." value="<?php echo esc_attr( $banner['image'] ); ?>">
						<button type="button" class="button" id="ao-so-banner-pick"><?php esc_html_e( 'انتخاب از کتابخانه', 'amazing-offer' ); ?></button>
					</div>
				</div>
				<div class="ao-so-field"><label><?php esc_html_e( 'لینک بنر', 'amazing-offer' ); ?></label><input type="url" name="config[banner][link]" value="<?php echo esc_attr( $banner['link'] ); ?>"></div>
				<div class="ao-so-field"><label><?php esc_html_e( 'متن جایگزین (alt)', 'amazing-offer' ); ?></label><input type="text" name="config[banner][alt]" value="<?php echo esc_attr( $banner['alt'] ); ?>"></div>
			</section>

			<!-- Responsive -->
			<section class="ao-so-panel" data-panel="responsive">
				<p class="ao-so-hint"><?php esc_html_e( 'فقط مقادیری که اینجا تعیین کنید بریک‌پوینت پایه را override می‌کنند؛ بقیه از تنظیمات اسلایدر ارث می‌برند.', 'amazing-offer' ); ?></p>
				<h3><?php esc_html_e( 'موبایل', 'amazing-offer' ); ?></h3>
				<div class="ao-so-field"><label><?php esc_html_e( 'تعداد کارت', 'amazing-offer' ); ?></label><input type="number" min="1" max="6" name="config[responsive][mobile][cards]" value="<?php echo isset( $resp_m['cards'] ) ? esc_attr( $resp_m['cards'] ) : ''; ?>" placeholder="<?php esc_attr_e( 'ارث‌بری', 'amazing-offer' ); ?>"></div>
				<div class="ao-so-field ao-so-field-inline"><label><?php esc_html_e( 'مخفی‌کردن تایمر', 'amazing-offer' ); ?></label><?php $ao_toggle( 'config[responsive][mobile][hide_timer]', ! empty( $resp_m['hide_timer'] ) ); ?></div>
				<div class="ao-so-field ao-so-field-inline"><label><?php esc_html_e( 'مخفی‌کردن بنر', 'amazing-offer' ); ?></label><?php $ao_toggle( 'config[responsive][mobile][hide_banner]', ! empty( $resp_m['hide_banner'] ) ); ?></div>
				<h3><?php esc_html_e( 'تبلت', 'amazing-offer' ); ?></h3>
				<div class="ao-so-field"><label><?php esc_html_e( 'تعداد کارت', 'amazing-offer' ); ?></label><input type="number" min="1" max="6" name="config[responsive][tablet][cards]" value="<?php echo isset( $resp_t['cards'] ) ? esc_attr( $resp_t['cards'] ) : ''; ?>" placeholder="<?php esc_attr_e( 'ارث‌بری', 'amazing-offer' ); ?>"></div>
				<div class="ao-so-field ao-so-field-inline"><label><?php esc_html_e( 'مخفی‌کردن تایمر', 'amazing-offer' ); ?></label><?php $ao_toggle( 'config[responsive][tablet][hide_timer]', ! empty( $resp_t['hide_timer'] ) ); ?></div>

				<h3><?php esc_html_e( 'دسکتاپ', 'amazing-offer' ); ?></h3>
				<div class="ao-so-field"><label><?php esc_html_e( 'تعداد کارت', 'amazing-offer' ); ?></label><input type="number" min="1" max="6" name="config[responsive][desktop][cards]" value="<?php echo isset( $resp_d['cards'] ) ? esc_attr( $resp_d['cards'] ) : ''; ?>" placeholder="<?php esc_attr_e( 'ارث‌بری از تب اسلایدر', 'amazing-offer' ); ?>"></div>
				<div class="ao-so-field ao-so-field-inline"><label><?php esc_html_e( 'مخفی‌کردن تایمر', 'amazing-offer' ); ?></label><?php $ao_toggle( 'config[responsive][desktop][hide_timer]', ! empty( $resp_d['hide_timer'] ) ); ?></div>
			</section>

			</form>
		</div>

		<!-- Live preview placeholder (wired in Phase 4) -->
		<aside class="ao-so-editor-preview">
			<div class="ao-so-preview-toolbar">
				<button type="button" class="ao-so-device is-active" data-device="desktop"><span class="dashicons dashicons-desktop"></span></button>
				<button type="button" class="ao-so-device" data-device="tablet"><span class="dashicons dashicons-tablet"></span></button>
				<button type="button" class="ao-so-device" data-device="mobile"><span class="dashicons dashicons-smartphone"></span></button>
			</div>
			<div class="ao-so-preview-stage" id="ao-so-preview" data-device="desktop">
				<div class="ao-so-preview-empty"><?php esc_html_e( 'پیش‌نمایش زنده در فاز بعد فعال می‌شود.', 'amazing-offer' ); ?></div>
			</div>
		</aside>
	</div>
</div>
