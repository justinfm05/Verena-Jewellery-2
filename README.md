# Verena Jewellery — Website

WordPress theme + plugin for **[verenajewellery.com](https://verenajewellery.com)** — a fine gold jewellery and custom-design brand in Jakarta (est. 1999). The site is **WhatsApp-first**: there is **no cart or checkout anywhere** — every "buy / inquire / sell" button is a `wa.me` deep link with a pre-filled Indonesian message. Site language is **Indonesian**. Hosted on **SiteGround** (has SG caching).

> **This README doubles as the AI/onboarding context doc.** If you are an LLM picking this up cold, read the whole thing first — especially **§10 Domain knowledge** and **§13 Decision log**, which encode choices that are easy to get wrong.

**Current versions:** theme **2.0.5**, plugin **1.2.0**.
**WhatsApp number:** `628111099399` (dial `+62 811-1099-399`).

---

## 1. TL;DR for future sessions

- Two deliverables: a **theme** and a **plugin**, both under `wp-content/`. They deploy as **.zip uploads** to WordPress (see `dist/`). There is **no build step** and **no CI**.
- The dev machine has **no PHP, no WP, no Docker** — you cannot run the site locally or `php -l`. Verify PHP by reading + balancing braces/parens. Node **is** available for JS checks and small scripts.
- **Gold prices are data, not code.** Staff type them daily in wp-admin → **Verena Jewellery → Harga Emas Hari Ini**. That one screen feeds the ticker, all product prices, and the calculator. Do **not** wire the site to the store's Excel sheet (deliberately avoided — see §10).
- Requires the free **Advanced Custom Fields (ACF)** plugin to be active.

---

## 2. Repository layout

| Path | What it is |
|---|---|
| `wp-content/themes/verena-jewellery/` | The theme — all pages, design, layout, front-end JS |
| `wp-content/plugins/verena-jewellery-tools/` | The plugin — post types, pricing engine, gold-price admin, calculator REST, WhatsApp helpers |
| `dist/` | Ready-to-upload zips (`verena-jewellery.zip`, `verena-jewellery-tools.zip`) — this is what you install in WordPress |
| `verena-design-system/` | Brand reference: tokens, colours, fonts, component prompts (not deployed) |
| `README.md` | This file |
| `.gitignore` | Excludes `.DS_Store`, `.claude/`, and `*.xlsx/*.csv` (price sheets stay out of git) |

Git remote: `origin` → `github.com/justinfm05/Verena-Jewellery-2` (collaboration repo).

---

## 3. Tech stack & constraints

- **WordPress classic theme** (PHP templates), **no build step** — `style.css` is hand-authored so it can be edited and deployed straight to SiteGround without a Node toolchain.
- **Alpine.js** (from CDN, deferred) is the only front-end runtime dependency — used by the custom-order form and the calculator for reactivity.
- **Google Fonts** enqueued in `functions.php`.
- Deploy = zip the folder → upload in wp-admin → **Replace** → **Purge SG Cache**. Version bumps bust the cache (see §11).

---

## 4. Design system (forest / gold / champagne)

CSS custom properties live at the top of `wp-content/themes/verena-jewellery/style.css`.

**Colours**
| Token | Hex | Use |
|---|---|---|
| `--forest` | `#24281C` | Dark chrome: header, footer, hero, testimonials, dropdowns |
| `--forest-dark` | `#162016` | Gold ticker bar |
| `--gold` | `#C9A24B` | CTAs, eyebrows, links, labels |
| `--gold-bright` | `#D4AF37` | Hover state for gold |
| `--gold-ink` | `#A08B4A` | Eyebrow/label on light backgrounds |
| `--champagne` | `#F2E9CC` | Headings/nav on dark; light section bg |
| `--ivory` / `--cream` | `#FBF7EA` / `#FFFDF5` | Light content base / raised cards |
| `--ink` | `#1E2A1E`/`#24281C` | Body text on light |

> **Forest colour history:** the design spec used `#1E2A1E`, but the stacked logo PNG has a baked-in background of **`#24281C`** (sampled from the file). `--forest` was retuned to `#24281C` so the logo blends seamlessly into the header/footer with no visible rectangle. The hero scrim gradients were updated to match.

**Type:** `Cormorant Garamond` (italic, display/headings) + `Manrope` (body/UI/nav). Nav/labels: uppercase, letter-spacing, 600–700 weight.

**Rhythm:** dark forest chrome alternates with light champagne content bands. Radius 3–4px. Container widths 1280px (header/ticker) / 1200px (content).

**Logos** (`theme/assets/img/`): `verena-logo-stacked.png` is the site logo (header + footer), sized to 56px and hard-capped so a large source image can't blow up the header (also covers a Customizer-set custom logo). Horizontal variants exist but aren't used. **PNGs are ~1MB — should be optimised before heavy traffic.**

---

## 5. Theme architecture

- **`header.php`** → gold ticker (`template-parts/gold-ticker.php`) + sticky nav (Koleksi, Cincin Kawin, Custom Design, **Layanan** dropdown, Tentang Kami) + WhatsApp CTA + mobile menu. Nav is **hard-coded** (not a WP menu).
- **`footer.php`** → 3-col (logo/blurb/social circles, store info, Google Map iframe) + bottom bar.
- **`front-page.php`** (homepage, automatic) → full-bleed hero (image + left scrim) → collections band → testimonials (forest) → Instagram feed.
- **Page templates** use WordPress's `page-{slug}.php` convention (auto-applies when a Page has that slug):

| Template | Slug | Purpose |
|---|---|---|
| `page-collection.php` | `collection` | Jewellery catalog (queries `verena_product`) |
| `page-custom-orders.php` | `custom-orders` | Interactive WhatsApp inquiry form (Alpine) |
| `page-logam-mulia.php` | `logam-mulia` | Gold-bar brand overview (Antam/UBS/EMASKU) |
| `page-repairs.php` | `repairs` | Repairs service |
| `page-buyback-emas.php` | `buyback-emas` | Single-item buyback estimator |
| `page-gold-calculator.php` | `gold-calculator` | **Kalkulator Emas** (see §9) |
| `page-about-us.php` | `about-us` | Brand story |
| `page-contact-us.php` | `contact-us` | Store info + map + WhatsApp |

- **Central slug map:** `verena_page_slug()` / `verena_page_url()` in `functions.php`. All internal nav/footer/CTA links route through it, so **renaming a slug is a one-line change — but you must also rename the matching `page-{slug}.php` file.**
- **Data helpers** (`functions.php`): `verena_gold_rates()` (ticker — reads live plugin rates), `verena_testimonials()`, `verena_instagram_posts()` (placeholders), `verena_wa_url()`, `verena_wa_icon()`, `verena_shop_info()` (option-backed shop details).
- **`assets/js/main.js`** → mobile menu toggle + Layanan dropdown (open/close, outside-click, Esc).
- **`inc/template-helpers.php`** → `verena_whatsapp_button()`, `verena_icon()`, `verena_status_badge()`, product/bullion query + formatting helpers.

Footer links to `/privacy/` and `/terms/` — those WP pages don't exist yet (create them or update the links).

---

## 6. Plugin architecture (`verena-jewellery-tools`)

- **Custom post types:** `verena_product` (jewellery pieces) and `verena_bullion` (gold bars). Their edit-screen fields come from **ACF** (`inc/acf-fields.php`) — sku, weight, making charge, purity, status, etc. Plugin shows an admin notice if ACF is missing.
- **Pricing engine** (`inc/pricing.php`) — prices are **never stored on a product**; everything computes on demand from the latest rate, so one rate update reflects everywhere:
  - Per-karat **sell rates** → table `wp_verena_gold_rate_log` (append-only; "current" = latest row per `purity_label`).
  - **Buyback rate** → table `wp_verena_buyback_rate_log` (single fine-gold / 24K-equivalent per gram).
  - **Purity options** → `wp_option` `verena_purity_options` (karat label → `fraction_bps`, 7500 = 75%). Seeded 24K/22K/18K/17K/16K/14K + "Tidak yakin". Note the shop's real values: 24K=9999, 22K=9160, 18K=7500, **17K=7500**, 16K=7000, 14K=5850.
  - `verena_purity_fraction_bps($label)` → returns the stored fraction, else derives `N/24` for any `"NK"` (1–24). This is what lets the calculator offer any karat. Used by the buyback estimate + REST validation.
  - `verena_compute_product_price()` = weight × per-karat sell rate + making charge.
  - `verena_compute_buyback_estimate()` = weight × purity fraction × buyback rate. **Always indicative** — final price after in-store testing (see `verena_estimate_disclaimer()`).
- **Admin screens** (top-level "Verena Jewellery" menu, dashicon money):
  - **`inc/gold-price-page.php`** — "**Harga Emas Hari Ini**", the **landing page**. Dead-simple daily entry: 24K/22K/18K/17K/16K sell + buyback. Mobile-friendly, forgiving input (accepts `1.850.000` or `1850000`), non-destructive (blank = unchanged), keeps history. Saving drives the ticker + product prices + calculator.
  - **`inc/settings-page.php`** — full shop info (name, WhatsApp, address, hours, IG, price rounding) + all per-karat rates + rate history. Registers the admin menu.
  - **`inc/purity-options-page.php`** — edit karat → fraction table.
  - **`inc/admin-leads-page.php`** — saved calculator lists / leads.
- **REST** (`inc/rest-calculator.php`): `POST/PUT /verena/v1/calculator` (save/update a shareable calculator list → `wp_verena_calc_lists`), `GET /verena/v1/calculator/{slug}`, `GET /verena/v1/rates`. Public but guarded by nonce + honeypot + per-IP rate limit.
- **Shop info:** `verena_shop_info()` reads options (defaults: WhatsApp `628111099399`, established `1999`, ITC Fatmawati address, IG `verenajewellery.id`).

---

## 7. Gold price data flow (single source of truth)

```
Staff → wp-admin "Harga Emas Hari Ini" (inc/gold-price-page.php)
        │  writes per-karat sell rates + buyback rate (append-only log tables)
        ▼
  ┌─────────────┬───────────────────────┬──────────────────────────┐
  ▼             ▼                       ▼                          ▼
Ticker      Product prices          Kalkulator Emas            Buyback estimator
(theme      (verena_compute_        (weight × purity ×          (single-item page)
 verena_    product_price)           buyback rate)
 gold_rates)
```

The theme ticker (`verena_gold_rates()`) reads the latest per-karat rates from the plugin and shows the date they were last set; it falls back to demo numbers only if the plugin is inactive or no rate exists.

---

## 8. Deployment

1. Edit files in `wp-content/…`.
2. Rebuild the zip(s). From `wp-content/themes/` (or `…/plugins/`):
   ```bash
   zip -rq ../../dist/verena-jewellery.zip verena-jewellery -x '*.DS_Store'
   zip -rq ../../dist/verena-jewellery-tools.zip verena-jewellery-tools -x '*.DS_Store'
   ```
3. **Bump the version** (this is what busts SiteGround + browser cache):
   - Theme: `VERENA_THEME_VERSION` in `functions.php` **and** the `Version:` header in `style.css`.
   - Plugin: `Version:` header **and** `VERENA_JT_VERSION` in `verena-jewellery-tools.php`.
4. In wp-admin: **Plugins → Add New → Upload Plugin** and/or **Appearance → Themes → Add New → Upload Theme** → **Replace current/active with uploaded**.
5. Top bar → **Purge SG Cache**.

**First-time / full setup order:** install plugin → install & activate **ACF** → install theme → **Settings → Permalinks → Save** → create Pages with the slugs in §5 → enter prices on "Harga Emas Hari Ini".

---

## 9. Kalkulator Emas (the buyback funnel tool)

`page-gold-calculator.php` + Alpine.js. Two item categories:
- **Perhiasan** (jewellery): weight (grams) + **karat**.
- **Logam Mulia** (bars): denomination × quantity, treated as 24K.

Value per item = `grams × purity fraction × today's buyback rate`. Running total, per-category subtotals, a **"Jual ke Verena via WhatsApp"** CTA that sends the itemised list, and **save-and-share** (returns `/gold-calculator/{slug}/`).

**Karat selector** — a curated list of the gold grades Indonesians actually own, labelled **by karat** (what customers know) with the gold **%** as a helper. Order + values:

`24K·99,9%` · `22K·91,6%` · `21K·87,5%` · `18K·75%` · **`17K·75%` (default)** · `16K·70%` · `14K·58,5%` · `9K·37,5%` · `Belum tahu` (in-store check).

- **17K and 18K are intentionally kept as SEPARATE options** even though both are 75% — the customer picks whatever is stamped on their piece. (This was an explicit user decision; do not merge them.)
- Percentages come from the shop's Purity Options where set, else `N/24`. `%` display truncates (so 24K shows 99,9%, not 100%).
- Includes a **"Cara Pakai" 3-step how-to** and a collapsible **"Apa itu karat?"** education block with a **stamp → karat → %** table (999/916/875/750/700/375) — because most customers know their gold by the stamp, not by "karat".

---

## 10. Domain knowledge (READ — easy to get wrong)

- **Indonesians identify gold by the fineness stamp / percentage** (750, 916, 999), **not** by international karat. Selecting purely by karat 1–24 confuses people and creates fake duplicates.
- **SNI (Indonesian standard) defines 17K as 750 = 75%, the same as 18K.** 17K is the **most common** gold and the calculator's default. This is correct, not a bug.
- The **store keeps its own daily price Excel** ("Price List LM VERENA.xlsx" — a heavily formatted, formula-driven reseller sheet for gold bars). It is **deliberately NOT connected to the website**: it's built for humans/printing and would be fragile to parse, silently corrupting prices. Website prices are entered **manually** via the daily screen. Do not re-introduce spreadsheet parsing.
- **WhatsApp is the entire sales funnel.** Number `628111099399`. (The original design `.dc.html` files had a typo `6281110993399`; the correct number is confirmed by the store's own price sheet: `+62-811-1099-399`.)

---

## 11. Conventions for future changes

- **Match existing style:** classic PHP templates, tabs for indentation, semantic CSS classes already defined in `style.css`, inline SVG icons (no icon font).
- **Slugs:** change in `verena_page_slug()` **and** rename the `page-{slug}.php` file together.
- **New karat grades / purities:** edit `$grade_defs` in `page-gold-calculator.php`; server support is automatic via `verena_purity_fraction_bps()`.
- **Always bump versions** on any theme/plugin change (cache-busting) and rebuild the matching zip.
- **Never commit** `*.xlsx/*.csv` price sheets or `.claude/` (see `.gitignore`).

---

## 12. Bilingual plan (not yet built)

Indonesian is primary. English is planned via **TranslatePress (free)** — it translates the *rendered output*, so it handles the theme's hard-coded Indonesian strings without refactoring the nav into a WP menu. Set default = Indonesian, add English. Not installed yet.

---

## 13. Decision log (what was done this build, and why)

- Rebuilt the theme from a v1.2 dark/Inter design to the **2024 forest/gold/champagne + Cormorant/Manrope** handoff (homepage, header, ticker, footer, product card, custom-order form).
- Retuned `--forest` `#1E2A1E → #24281C` to match the **stacked logo's** background so it blends seamlessly.
- Switched the site logo to the **stacked** lockup; hard-capped its height (fixes a giant-logo bug when a Customizer logo is set).
- Renamed page slugs to the client's scheme (`collection`, `logam-mulia`, `buyback-emas`, `about-us`, `contact-us`) via a central slug map; added a real Contact page.
- Added the **"Harga Emas Hari Ini"** daily price screen and **connected the ticker** to live rates (previously placeholders). Rejected auto-reading the store Excel as too fragile.
- Reworked the **Kalkulator Emas**: full karat coverage via `N/24` fallback, karat-led labels **with gold %**, a curated Indonesian grade list, **17K/18K kept separate**, default **17K**, plus how-to + karat education.

---

## 14. Open TODOs / deferred

- **Logam Mulia bar price table** on the LM page (phase 2) — needs a non-fragile data-entry method for the many brand/gram values.
- Optionally add **emas muda** grades (e.g. 42%/420, 30%/300) to the calculator.
- One-page **Indonesian instruction sheet** for staff (daily price screen).
- Replace placeholder imagery (hero, products, Instagram) with real photography; **optimise the ~1MB logo PNGs**.
- Create **Privacy** and **Terms** pages (footer links to `/privacy/`, `/terms/`).
- Confirm the **17K purity value** (currently 75% per SNI) if the shop wants it different.
- Install **TranslatePress** for the ID/EN toggle.
