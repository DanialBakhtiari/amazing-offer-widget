---
name: release-manager
description: Prepare an Amazing Offer plugin release — bump version, update changelog, run pre-release checks, and tag. Use when asked to "cut a release", "bump the version", "prepare a release", "update changelog", or "tag a new version".
---

# Release Manager

Drive a safe release of the Amazing Offer plugin.

## Version sources (keep in sync)

- `amazing-offer.php` header `Version:` and the `AMAZING_OFFER_VERSION` constant.
- `readme.txt` `Stable tag:`.
- `modules/special-offer/special-offer.php` `AMAZING_OFFER_SO_VERSION` (module version; bump only when the module changes).
- `CHANGELOG.md` and `README.md`.

Use semver: PATCH for fixes, MINOR for additive features (e.g. the Special Offer module = a MINOR bump), MAJOR for breaking changes (avoid — the module is built to never break back-compat).

## Pre-release checklist (must all pass)

1. **No regressions.** Legacy `[amazing_offer]`, its settings, and the legacy Elementor widget behave identically. Disabling the module (`amazing_offer_so_enabled = false` or removing the filter) returns the plugin to its prior behavior.
2. **Lint + tests.** `php -l` clean on all PHP; `node --check` clean on all JS; the stubbed-WP harnesses pass.
3. **Security.** Run `plugin-security-audit`; resolve any high/critical finding.
4. **i18n/RTL.** Run `i18n-rtl-check`.
5. **Back-compat data.** Schema changes ship a migration; old templates still render. Uninstall removes only `amazing_offer_*` / `amazing_offer_so_*` data and `ao_special_offer` posts — legacy and non-module data intact.
6. **Docs.** `CHANGELOG.md` has a dated entry; `README.md` features updated; `readme.txt` `Tested up to:` current.

## Steps

1. Bump the version in every source above.
2. Write the `CHANGELOG.md` entry (added / changed / fixed).
3. Run lint + tests + the two audit skills; fix findings.
4. Commit (`Release vX.Y.Z`), then annotated tag: `git tag -a vX.Y.Z -m "Amazing Offer vX.Y.Z"`.
5. Push branch + tag: `git push origin main --follow-tags`.
6. Optionally build the install zip and attach it to a GitHub Release.

Note: `amazing_offer_version` option is set with `add_option` (once); if a future migration keys off it, add an upgrade routine that `update_option`s it on version change.
