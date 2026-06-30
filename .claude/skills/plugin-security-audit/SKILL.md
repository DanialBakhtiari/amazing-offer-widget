---
name: plugin-security-audit
description: Audit this WordPress/WooCommerce plugin (Amazing Offer + Special Offer module) for security issues. Use when asked to "security audit", "review for vulnerabilities", "check escaping/nonces", "is this plugin safe", or before a release. Reports findings by severity with fixes.
---

# Plugin Security Audit

Audit the Amazing Offer plugin and its `modules/special-offer/` module for common WordPress vulnerabilities. Produce a severity-ranked report with file:line and a concrete fix per finding. Do not change behavior unless asked to fix.

## What to check (WordPress-specific)

1. **Output escaping (XSS).** Every echoed value must be escaped at the point of output: `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()` for HTML, `esc_js()` for inline JS. Search for `echo`, `print`, `<?=`, and `printf`/`sprintf` into HTML. WooCommerce price HTML (`get_price_html()`, `wc_price()`) is pre-escaped → `wp_kses_post()` is correct; do not double-escape. JSON injected into attributes must use `esc_attr( wp_json_encode( ... ) )`.
2. **Input sanitization.** Every `$_POST`/`$_GET`/`$_REQUEST`/`$_FILES` read must be unslashed (`wp_unslash`) and sanitized (`sanitize_text_field`, `absint`, `sanitize_hex_color`, `sanitize_key`, `esc_url_raw`). Config blobs are sanitized centrally in `Amazing_Offer_SO_Schema::sanitize()` — verify every persist path routes through it.
3. **Nonce / CSRF.** Every state-changing AJAX handler calls `check_ajax_referer()`; every `admin-post` handler calls `check_admin_referer()`; every admin form has `wp_nonce_field()`. SO admin reuses the `amazing_offer_admin` nonce; the front-end add-to-cart uses `amazing_offer_public`.
4. **Capability checks.** Admin/AJAX mutations require `current_user_can( 'manage_options' )`. The SO REST list route uses a `manage_options` permission_callback. No privileged action should rely on nonce alone.
5. **SQL.** No string-concatenated SQL. All product/template queries go through `WP_Query` / `get_posts` / `wc_get_product`. Any `$wpdb` call must use `$wpdb->prepare()`.
6. **File upload (import).** `is_uploaded_file()` check, size cap, `json_decode` with array validation, `_format` allow-list, then schema sanitize before persist. Never `eval`/`unserialize` untrusted input.
7. **Style/class injection.** Colors pass `sanitize_hex_color`; enums (`effect`, `timer_type`, `slider_mode`, `source.type`, `banner.position`) pass `in_array()` allow-lists. No raw user value reaches a `style=`/`class=` attribute unescaped.
8. **Secrets / logging.** No hardcoded keys/secrets; no logging of sensitive data.
9. **Isolation.** Confirm the module never writes legacy options (`amazing_offer_settings`/`amazing_offer_products`) and uses only `amazing_offer_so_*` keys + the `ao_special_offer` CPT + `_ao_so_config` meta.

## Procedure

1. `grep` for every `echo`/`print`/`printf` in `modules/special-offer/` and the partials; confirm escaping at each.
2. `grep` for `$_POST`/`$_GET`/`$_FILES`/`$_REQUEST`; confirm unslash + sanitize + nonce + capability for each handler.
3. List every `wp_ajax_*`, `admin_post_*`, and `register_rest_route` callback; verify the nonce + capability pair.
4. Verify `_ao_so_config` is never registered with `show_in_rest => true` and the CPT is `show_in_rest => false`.
5. Re-run the stubbed-WP test harnesses if logic changed.

## Output format

`severity (critical/high/medium/low) — file:line — problem — fix`. End with a checklist of items 1–9 marked pass/fail and a one-line verdict.
