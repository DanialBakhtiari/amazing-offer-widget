<div align="right">

# 🔥 Amazing Offer Widget

افزونه‌ی وردپرس برای نمایش **محصولات تخفیف‌دار ووکامرس** به صورت اسلایدر حرفه‌ای، همراه با ویجت المنتور، تایمر شمارش معکوس و داشبورد مدیریتی کامل فارسی (RTL).

</div>

<p align="center">
  <img alt="Version"      src="https://img.shields.io/badge/version-1.1.1-e04a1f.svg">
  <img alt="WordPress"    src="https://img.shields.io/badge/WordPress-6.0%2B-21759b.svg">
  <img alt="WooCommerce"  src="https://img.shields.io/badge/WooCommerce-7.0%2B-96588a.svg">
  <img alt="Elementor"    src="https://img.shields.io/badge/Elementor-3.0%2B-92003b.svg">
  <img alt="PHP"          src="https://img.shields.io/badge/PHP-7.4%2B-777bb4.svg">
  <img alt="License"      src="https://img.shields.io/badge/license-GPLv2-blue.svg">
</p>

---

## ✨ Features / امکانات

- 🎯 **ویجت اختصاصی المنتور** با کنترل‌های کامل محتوا و استایل (کارت، قیمت، دکمه)
- 🛠️ **داشبورد مدیریتی فارسی و RTL** با سه تب: محصولات، تنظیمات، حمایت
- ⚡ **افزودن خودکار** محصولات تخفیف‌دار + **افزودن دستی** با جستجوی زنده‌ی AJAX
- ↕️ **مرتب‌سازی Drag & Drop** محصولات
- ⏳ **تایمر شمارش معکوس**: تا نیمه‌شب / مدت ثابت از اولین بازدید / تاریخ پایان مشخص
- 🖼️ **حالت‌های نمایش**: اسلایدر خودکار، اسلایدر دستی، گرید ثابت
- 🛒 **افزودن به سبد با AJAX** و loading state
- 📦 **ماژول‌های اضافه**: روزشمار، موجودی، تعداد خریداران
- 🔧 **شورت‌کد** `[amazing_offer]`
- 🧩 **معماری ماژولار و قابل گسترش** با فیلترهای وردپرس
- 📱 کاملاً **Responsive** و **RTL**
- 🚫 بدون CDN خارجی، بدون Composer — کاملاً standalone

### 🆕 ماژول «پیشنهاد ویژه» (v1.1.0)

افزایشی، مستقل و قابل خاموش‌کردن (سوییچ مستر `amazing_offer_so_enabled`). با غیرفعال‌شدن، هیچ اثری روی بقیه‌ی افزونه ندارد.

- ♾️ **طرح‌های نامحدود** (هر طرح = یک نمونه‌ی مستقل با محصولات/استایل/اسلایدر خودش)
- 🧰 مدیریت کامل: ساخت، ویرایش، **تکثیر**، حذف، فعال/غیرفعال، **Drag & Drop**
- 🎨 **پیش‌نمایش زنده** دو لایه (کازمتیک آنی + ساختاری دیبونس) با سه حالت دستگاه
- 🎞️ افکت‌های **Swiper 11** (لوکال): `slide` / `fade` / `coverflow` / `cards` + گرید
- 🛍️ منابع محصول: انتخاب دستی / همه‌ی تخفیف‌دارها / دسته
- 🖼️ بنر تبلیغاتی، رنگ/گرادیان، شخصی‌سازی کارت، **override ریسپانسیو** هر بریک‌پوینت
- 🔘 دکمهٔ **«دیدن همه»** + **line-clamp قابل تنظیم** عنوان/توضیحات (هم‌ارتفاعی کارت‌ها)
- 📤 **Export / Import** هر طرح به JSON
- 🔌 سه خروجی با یک renderer مشترک: شورت‌کد `[special_offer id="N"]`، **بلوک گوتنبرگ**، **ویجت المنتور**
- 🔍 JSON-LD اختیاری برای سئو · ♿ a11y و `prefers-reduced-motion`

---

## 📦 Installation / نصب

### روش ۱ — از فایل ZIP
1. ساخت فایل zip:
   ```bash
   zip -r amazing-offer.zip amazing-offer/ -x "*.DS_Store" "*__MACOSX*"
   ```
2. پنل وردپرس → **افزونه‌ها → افزودن → بارگذاری افزونه** → آپلود `amazing-offer.zip`
3. فعال‌سازی (ووکامرس باید فعال باشد).

### روش ۲ — دستی
پوشه‌ی `amazing-offer/` را در مسیر زیر کپی کنید:
```
wp-content/plugins/amazing-offer/
```
سپس از منوی افزونه‌ها فعال کنید.

> پس از فعال‌سازی، منوی **«Amazing Offer»** در پنل مدیریت ظاهر می‌شود.

---

## 🚀 Usage / استفاده

### شورت‌کد

```text
[amazing_offer]
[amazing_offer limit="6" title="تخفیف ویژه" source="sale"]
[amazing_offer source="category" category="15" mode="grid"]
```

| Attribute  | مقادیر                       | پیش‌فرض      | توضیح                       |
|------------|------------------------------|--------------|-----------------------------|
| `limit`    | عدد                          | از تنظیمات   | تعداد محصولات               |
| `title`    | متن                          | از تنظیمات   | override عنوان              |
| `source`   | `sale` \| `saved` \| `category` | `saved`   | منبع محصولات                |
| `category` | شناسه دسته                   | —            | فقط با `source=category`    |
| `mode`     | `auto` \| `manual` \| `grid` | از تنظیمات   | حالت نمایش                  |

### ویجت المنتور
در ویرایشگر المنتور، دسته‌ی **Amazing Offer** → ویجت **«پیشنهاد شگفت‌انگیز»** را بکشید.

### ماژول «پیشنهاد ویژه»
منوی **Amazing Offer ← پیشنهاد ویژه** → طرح بسازید و آن را با هر کدام از این‌ها نمایش دهید:

```text
[special_offer id="12"]
```

یا بلوک گوتنبرگ **«پیشنهاد ویژه»** / ویجت المنتور **«پیشنهاد ویژه»** را اضافه کرده و طرح را انتخاب کنید.

---

## 🗂️ Project Structure / ساختار

```
amazing-offer/
├── amazing-offer.php          # فایل اصلی + autoloader
├── uninstall.php              # پاک‌سازی هنگام حذف
├── readme.txt                 # readme مخزن وردپرس
├── includes/                  # کلاس‌های هسته (settings, products, render, loader)
├── admin/                     # داشبورد ادمین (controller, css, js, partials)
├── public/                    # خروجی فرانت (controller, css, js)
├── elementor/                 # رجیستر و ویجت المنتور
├── modules/
│   └── special-offer/         # ماژول «پیشنهاد ویژه» (CPT, schema, renderer, admin, block, elementor, swiper)
└── languages/                 # amazing-offer.pot

.claude/skills/                # plugin-security-audit, special-offer-extend, i18n-rtl-check, release-manager
```

---

## 🧩 Extending / گسترش

### افزودن منبع محصول جدید
```php
add_filter( 'amazing_offer_product_sources', function ( $sources ) {
    $sources['featured'] = array(
        'label'    => 'محصولات ویژه',
        'callback' => 'my_get_featured_products', // function( $limit, $category ): array
    );
    return $sources;
} );
```

### افزودن ماژول جدید
```php
add_filter( 'amazing_offer_modules', function ( $modules ) {
    $modules['flash_sale'] = array(
        'label' => 'فلش سیل',
        'class' => 'Amazing_Offer_Module_Flash_Sale',
        'file'  => plugin_dir_path( __FILE__ ) . 'modules/class-flash-sale.php',
    );
    return $modules;
} );
```

---

## 🔒 Security / امنیت

- همه‌ی AJAX handlerها با `check_ajax_referer` و `current_user_can( 'manage_options' )` محافظت می‌شوند.
- ورودی‌ها با `sanitize_text_field`، `absint`، `sanitize_hex_color` پاک‌سازی می‌شوند.
- خروجی‌ها با `esc_html`، `esc_attr`، `esc_url`، `wp_kses_post` escape می‌شوند.

---

## 🛠️ Compatibility / سازگاری

| نرم‌افزار    | حداقل نسخه |
|--------------|-----------|
| WordPress    | 6.0       |
| WooCommerce  | 7.0       |
| Elementor    | 3.0       |
| PHP          | 7.4       |

اگر ووکامرس غیرفعال باشد، افزونه بدون خطا یک پیام هشدار نمایش می‌دهد.

---

## 📋 Changelog

نگاه کنید به [CHANGELOG.md](CHANGELOG.md).

---

## 📄 License / مجوز

[GPL v2 or later](LICENSE) — © [Danial Bakhtiari](https://danialbakhtiari.com)

---

## ☕ Support / حمایت

اگر این افزونه برایتان مفید بود، از سازنده حمایت کنید:
**[danialbakhtiari.com](https://danialbakhtiari.com)**
