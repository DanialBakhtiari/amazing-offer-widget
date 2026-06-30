---
name: special-offer-extend
description: Guide for safely extending the Special Offer module (add a config field, slider effect, card option, product source, or output surface) without breaking backward compatibility. Use when asked to "add an option to special offer", "add a new effect/source", "extend the special offer module", or "add a field to the template editor".
---

# Extending the Special Offer Module

The module lives in `modules/special-offer/` with the `Amazing_Offer_SO_*` class prefix. One template (طرح) = one `ao_special_offer` post; its full config is one versioned `_ao_so_config` meta blob layered over core defaults. It is 100% additive and toggleable via the `amazing_offer_modules` filter + the `amazing_offer_so_enabled` option.

## The golden rule: additive, never destructive

Backward compatibility is mandatory. Existing templates must keep rendering after any change. Old rows are healed by defaults-merge on read, so **adding** keys is safe; **renaming/removing/restructuring** keys requires a migration step.

## Add a simple config field (no migration needed)

1. Add the default in `Amazing_Offer_SO_Schema::get_defaults()` (top-level, or inside `style`/`source`/`banner`).
2. Add a sanitizer branch in `Amazing_Offer_SO_Schema::sanitize()` (text/bool/color/int/enum — mirror the existing patterns; enums use `in_array()` allow-lists).
3. If nested, ensure `merge_defaults()` backfills it (it deep-merges `style`/`banner`/`source`; add new nested blocks there).
4. Add the form control in `admin/partials/so-editor.php` with `name="config[...]"` (use the `$ao_toggle` helper for switches; hidden `0` + checkbox `1`).
5. Consume it in `includes/class-amazing-offer-so-render.php` (and, if cosmetic, in the live-preview CSS-var map in `admin/js/ao-so-admin.js::applyCosmetic`).

`wp_parse_args` is **shallow** — newly-added *nested* keys must be merged explicitly in `merge_defaults()`, or old rows render without them.

## Add a slider effect

1. Add the value to `Amazing_Offer_SO_Schema::effects()` (allow-list) and the editor radio group.
2. Add its Swiper config branch in `public/js/ao-so-public.js::initSlider` (e.g. `effectName + 'Effect'` params). Effects needing a single slide must set `slidesPerView = 1`.

## Add a product source

1. Add the type to `source_types()` + the editor radios.
2. Handle it in `Amazing_Offer_SO_Render::get_products()`. Reuse `Amazing_Offer_Products::get_product_data()` so missing/unpublished products are skipped safely.

## Add an output surface

Always delegate to `Amazing_Offer_SO_Render::render( $id, $settings, $products )` (or `render_config()` for unsaved config) so every surface stays identical. Use a module-prefixed asset handle and the `amazing_offer_so_render` action — never the legacy `amazing_offer_render`.

## A structural change (requires migration)

1. Bump `Amazing_Offer_SO_Schema::SCHEMA_VERSION`.
2. Add a `migrate_N_to_M()` method to `Amazing_Offer_SO_Migrator` that transforms an old blob to the new shape. It runs lazily per-row on read (never a mass activation loop) and forward-compat leaves newer rows untouched.

## Do NOT touch

Legacy options (`amazing_offer_settings`/`amazing_offer_products`), the legacy renderer/vanilla slider, the `amazing_offer_render` action, legacy CSS classes (`.amazing-offer-*` vs the module's `.ao-so-*`), or asset handles `amazing-offer-public`/`amazing-offer-admin`. After any change, run the stubbed-WP test harnesses and `plugin-security-audit`.
