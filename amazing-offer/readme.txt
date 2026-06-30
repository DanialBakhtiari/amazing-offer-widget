=== Amazing Offer Widget ===
Contributors: danialbakhtiari
Tags: woocommerce, offer, sale, slider, elementor, discount, countdown
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A powerful WooCommerce product offer slider with an Elementor widget, full admin dashboard, and countdown timer.

== Description ==

Amazing Offer Widget نمایش محصولات تخفیف‌دار ووکامرس را به صورت یک اسلایدر زیبا و کاملاً responsive ممکن می‌کند.

ویژگی‌ها:

* ویجت اختصاصی المنتور با کنترل‌های استایل کامل
* داشبورد مدیریتی فارسی و RTL با سه تب: محصولات، تنظیمات، حمایت
* افزودن خودکار محصولات تخفیف‌دار یا افزودن دستی با جستجوی زنده
* مرتب‌سازی محصولات با drag & drop
* تایمر شمارش معکوس (نیمه‌شب / مدت ثابت / تاریخ مشخص)
* حالت‌های نمایش: اسلایدر خودکار، اسلایدر دستی، گرید ثابت
* افزودن به سبد خرید با AJAX
* ماژول‌های اضافه: روزشمار، موجودی، تعداد خریداران
* شورت‌کد `[amazing_offer]`
* معماری ماژولار و قابل گسترش با فیلترها

== Installation ==

1. پوشه `amazing-offer` را در `wp-content/plugins/` قرار دهید یا فایل ZIP را از طریق پنل وردپرس آپلود کنید.
2. پلاگین را فعال کنید (ووکامرس باید فعال باشد).
3. به منوی «Amazing Offer» در پنل مدیریت بروید.
4. محصولات را اضافه کرده و تنظیمات را انجام دهید.
5. از شورت‌کد `[amazing_offer]` یا ویجت المنتور استفاده کنید.

== Shortcode ==

`[amazing_offer]`
`[amazing_offer limit="6" title="تخفیف ویژه" source="sale"]`
`[amazing_offer source="category" category="15" mode="grid"]`

Attributes: limit, title, source (sale|saved|category), category, mode (auto|manual|grid)

== Frequently Asked Questions ==

= آیا به المنتور نیاز است؟ =
خیر. شورت‌کد بدون المنتور کار می‌کند. ویجت المنتور فقط در صورت فعال بودن المنتور اضافه می‌شود.

= اگر ووکامرس غیرفعال باشد چه می‌شود؟ =
پلاگین یک پیام هشدار نمایش می‌دهد و بدون خطا غیرفعال می‌ماند.

== Changelog ==

= 1.1.1 =
* «پیشنهاد ویژه»: افزودن دکمهٔ «دیدن همه» (متن/لینک قابل تنظیم) و line-clamp قابل تنظیم برای عنوان (۱ تا ۳ خط) و توضیحات کوتاه محصول (۱ تا ۴ خط) برای هم‌ارتفاع‌ماندن کارت‌ها.

= 1.1.0 =
* افزودن ماژول «پیشنهاد ویژه»: طرح‌های نامحدود با پیش‌نمایش زنده، افکت‌های Swiper (slide/fade/coverflow/cards)، export/import، شورت‌کد [special_offer]، بلوک گوتنبرگ و ویجت المنتور. کاملاً افزایشی و قابل خاموش‌کردن.

= 1.0.0 =
* انتشار اولیه.

== Upgrade Notice ==

= 1.0.0 =
نسخه اول.
