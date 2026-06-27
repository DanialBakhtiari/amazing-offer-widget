# Changelog

All notable changes to **Amazing Offer Widget** are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [1.0.1] - 2026-06-27

### Fixed
- Slider navigation arrows now stay pinned to the left/right edges of the
  slider instead of dropping below the cards on themes that reset `position`.
  Selectors were scoped under `.amazing-offer-wrapper` for higher specificity.

## [1.0.0] - 2026-06-27

### Added
- Initial release.
- Elementor widget "پیشنهاد شگفت‌انگیز" with content, slider, and full style controls.
- RTL Persian admin dashboard with three tabs: products, settings, support.
- Auto-load on-sale products, manual live search, drag & drop ordering.
- Countdown timer (midnight / fixed duration / fixed end date).
- Display modes: auto slider, manual slider, static grid.
- AJAX add-to-cart with loading state and WooCommerce fragment refresh.
- Extra modules: days countdown, stock, buyers count.
- `[amazing_offer]` shortcode with `limit`, `title`, `source`, `category`, `mode` attributes.
- Modular, filter-driven architecture (`amazing_offer_product_sources`, `amazing_offer_modules`).
