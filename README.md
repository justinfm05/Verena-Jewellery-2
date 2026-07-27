# Verena Jewellery — Website

WordPress theme + plugin for [verenajewellery.com](https://verenajewellery.com) — a fine gold jewellery and custom-design brand in Jakarta (est. 1999). WhatsApp-first sales funnel (no cart/checkout — every buy/inquire button deep-links to WhatsApp).

## What's in here

| Folder | What it is |
|---|---|
| `wp-content/themes/verena-jewellery/` | The theme — all the pages, design, layout |
| `wp-content/plugins/verena-jewellery-tools/` | The plugin — products, gold prices, calculator, WhatsApp links, the daily "Harga Emas Hari Ini" screen |
| `dist/` | Ready-to-upload `.zip` files (this is what you install in WordPress) |
| `verena-design-system/` | Brand colours, fonts, and design reference |

## How to update the live site

1. Make/receive changes to the files in `wp-content/`.
2. Grab the matching `.zip` from `dist/`.
3. In WordPress admin: **Appearance → Themes → Add New → Upload Theme** (or **Plugins → Add New → Upload Plugin**) → choose the zip → **Replace current with uploaded**.
4. Top bar → **Purge SG Cache**.

## Design & tokens

- Colours: forest `#24281C`, gold `#C9A24B`, champagne `#F2E9CC`
- Fonts: Cormorant Garamond (headings) + Manrope (body)
- WhatsApp number: `628111099399`

## Notes

- Gold prices are entered daily in wp-admin → **Verena Jewellery → Harga Emas Hari Ini** (not in code).
- Requires the free **Advanced Custom Fields** plugin.
