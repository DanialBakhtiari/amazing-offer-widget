# Changelog

All notable changes to **Amazing Offer Widget** are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [1.1.6] - 2026-06-30

### Fixed
- Desktop card-count changes had no effect on wide screens while tablet changes
  did. The front-end Swiper used `breakpointsBase: 'container'` (added for the
  preview), so on a laptop the slider container stayed below 1024px (banner +
  padding) and never hit the desktop breakpoint. Removed it; the front-end now
  uses window-based breakpoints (desktop viewport → desktop cards). The admin
  preview keeps working via its explicit forced-device slidesPerView path.
- Theme/Elementor `img { height: auto }` (specificity 0,1,1) overrode the card
  and banner image heights and broke the layout. The image rules are now scoped
  under `.ao-so-wrapper` (0,2,1) so they win.

### Added
- Banner fit mode (cover = fill & crop, contain = show whole image) so the
  banner never deforms.
- A live "recommended banner image size" hint in the editor, computed from the
  actual rendered card area, so authors pick an image that fits the layout.

## [1.1.5] - 2026-06-30

### Fixed
- Card price block repeated the same price up to three times (WooCommerce's
  `get_price_html()` already includes the struck regular for sale items, and the
  renderer also printed the regular separately). Rewrote it to show the struck
  regular once + the sale price once (or a single price when not on sale), in a
  fixed-min-height column so the card layout never shifts.
- Cards (and their image box) now stay equal height under all conditions: the
  card fills the equalized slide (`height: 100%`) and the media is a fixed 1:1
  ratio box with `object-fit: cover`.

### Added
- Configurable title and subtitle text colors.
- Desktop card-count override in the Responsive tab.
- Editor live preview now shows each device's REAL cards-per-view (desktop = 3,
  etc.) instead of being driven by the narrow preview pane's container width.

## [1.1.4] - 2026-06-30

### Fixed
- Module asset cache-busting: `AMAZING_OFFER_SO_VERSION` was hardcoded to
  `1.0.0`, so the `?ver=` query on the module CSS/JS never changed and browsers
  kept serving stale files after an update (a hard refresh was needed to see
  fixes like 1.1.3). The module version now tracks the plugin version, so every
  release automatically busts the asset cache.

## [1.1.3] - 2026-06-30

### Fixed
- Special Offer editor: live-preview cards grew without bound after loading or
  selecting products. The preview column was a flexbox item with the default
  `min-width: auto`, so it expanded to fit the slider content, which gave Swiper
  more width, which enlarged the slides — an unbounded width feedback loop. Added
  `min-width: 0` to the preview column and constrained the preview stage
  (`width/max-width: 100%`, `overflow: hidden`). Front-end output is unchanged.

## [1.1.2] - 2026-06-30

### Fixed
- **Critical (white-screen / WSOD):** the module loader globbed every
  `modules/*/*.php` and required them alphabetically, so a module's class file
  loaded before its bootstrap defined the constants it depends on
  (`AMAZING_OFFER_SO_DIR`), fataling on every request. The loader now requires
  only each module's entry point (`modules/<name>/<name>.php`). Added a
  full load+init smoke test to prevent recurrence.

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
