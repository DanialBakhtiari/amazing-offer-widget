# Changelog

All notable changes to **Amazing Offer Widget** are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [1.1.1] - 2026-06-30

### Added
- Special Offer: a configurable **"See all"** header button (toggle, custom
  text and link).
- Special Offer: **configurable line-clamp** for the card title (1–3 lines) and
  an optional **short-description** block (1–4 lines), each with a fixed height
  so cards stay equal regardless of content length.
- Core `get_product_data()` now also returns `short_description` (additive;
  legacy output unaffected).

## [1.1.0] - 2026-06-30

### Added
- **Special Offer module (`پیشنهاد ویژه`)** — an additive, independently
  toggleable module (master switch `amazing_offer_so_enabled`, default on) for
  unlimited independent slider "templates" (طرح):
  - UI-less `ao_special_offer` CPT; full per-template config in one versioned
    `_ao_so_config` meta layered over core defaults; lazy per-row migration.
  - Admin manager: create / duplicate / delete / activate-toggle / drag-drop
    order, and a tabbed editor (products, style, slider, timer, card, banner,
    responsive) with a two-tier **live preview** (instant CSS-variable cosmetic
    updates + debounced server re-render) and 3 device modes.
  - Product sources: manual pick (live search + load on-sale), all on-sale,
    category, with per-template ordering.
  - **Swiper 11** (vendored locally, no CDN) effects: slide, fade, coverflow,
    cards, plus a static grid; RTL, a11y, and `prefers-reduced-motion` aware.
  - Per-template JSON **export / import** (import lands as a draft).
  - Three output surfaces sharing one renderer: `[special_offer id="N"]`
    shortcode, a server-rendered Gutenberg block, and an Elementor widget.
  - Optional JSON-LD `ItemList` for SEO.
- Project skills under `.claude/skills/`: `plugin-security-audit`,
  `special-offer-extend`, `i18n-rtl-check`, `release-manager`.

### Changed
- Core now consumes the documented `amazing_offer_modules` filter (inert when no
  module registers). Uninstall additionally removes Special Offer templates,
  their meta, and `amazing_offer_so_*` options (legacy data untouched).

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
