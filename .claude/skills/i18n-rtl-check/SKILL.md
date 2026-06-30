---
name: i18n-rtl-check
description: Verify translatability, RTL correctness, and Persian number/date display across the plugin. Use when asked to "check i18n", "verify translations", "check RTL", "are strings translatable", or before a release/locale handoff.
---

# i18n / RTL Check

Confirm every user-facing string is translatable and the UI/output is correct right-to-left and Persian-friendly.

## Checklist

1. **Text domain.** Every translation call uses the literal domain `amazing-offer`. Search for `__(`, `_e(`, `esc_html__(`, `esc_html_e(`, `esc_attr__(`, `esc_attr_e(`, `_x(`, `_n(` and confirm the second/last arg is `'amazing-offer'`. Flag any hardcoded user-facing string not wrapped.
2. **No variables as the string.** The first argument must be a literal, never a variable or concatenation. Use `printf`/`sprintf` with placeholders (`%s`, `%d`) and a `translators:` comment for ordered/explained placeholders.
3. **JS strings.** Admin/editor JS strings are passed via `wp_localize_script` (`amazingOfferSOAdmin.i18n`, `amazingOfferSOData.i18n`); the block uses `wp.i18n.__` with the domain. No bare hardcoded UI strings in JS.
4. **POT.** `languages/amazing-offer.pot` exists; after adding strings, note that it should be regenerated (`wp i18n make-pot . languages/amazing-offer.pot`).
5. **RTL.** Front-end wrappers carry `dir="rtl"`; admin screens use `dir="rtl"`. CSS uses logical properties or RTL-aware values (`margin-inline-start`, `right/left` chosen for RTL). Swiper gets RTL automatically from the `dir="rtl"` container — verify nav arrows point the right way.
6. **Persian numerals/dates.** Where the design calls for Persian digits, confirm conversion is applied (or documented as theme-dependent). Timer digits use `tabular-nums`. Dates from `current_time()` respect site locale.
7. **Mixed LTR tokens.** Card numbers, prices, and shortcodes that are inherently LTR (e.g. `[special_offer id="3"]`, monospace card numbers) are wrapped/styled `direction: ltr` so they don't reorder inside RTL text.

## Output

List each violation as `file:line — issue — fix`. End with a pass/fail per checklist item.
