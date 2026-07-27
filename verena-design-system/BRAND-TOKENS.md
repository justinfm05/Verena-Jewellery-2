# Verena Jewellery — Design Tokens

Reference sheet for the component library. Every component uses Tailwind
**arbitrary values** (`bg-[#1E2A1E]`) rather than named theme keys, so each file
drops into Claude Design with no `tailwind.config.js` dependency.

## Color

| Token | Hex | Role |
|---|---|---|
| Forest (base) | `#1E2A1E` | Nav, footer, hero overlays, dark sections |
| Forest (raised) | `#232F22` | Cards and panels sitting *on* forest |
| Champagne (page) | `#F2E9CC` | Default page background — replaces white |
| Champagne (ink) | `#EDE3C0` | Text and the logo on forest grounds |
| Gold | `#C9A24B` | CTAs, hairlines, karat badges |
| Gold (hover) | `#D4AF37` | Interactive gold state only |
| WhatsApp green | `#25D366` | Reserved — only where the WA brand is literal |

**Never**: `#FFFFFF`, or any cool gray. Both fight the warm ground and read as
generic SaaS. Where a lighter surface is needed, tint champagne upward, don't
desaturate toward white.

**Gold is rationed.** Buttons, 1px rules, small badges. Never a background
field larger than a button — gold at scale competes with the logo and reads
costume rather than fine.

### Contrast (WCAG 2.1)

| Pair | Ratio | Verdict |
|---|---|---|
| Champagne `#EDE3C0` on Forest `#1E2A1E` | ~12.0:1 | AAA |
| Gold `#C9A24B` on Forest `#1E2A1E` | ~6.2:1 | AA all sizes |
| Forest `#1E2A1E` on Gold `#C9A24B` | ~6.2:1 | AA — the CTA pairing |
| Forest `#1E2A1E` on Champagne `#F2E9CC` | ~13.6:1 | AAA |

Muted text uses champagne at 60–75% opacity on forest, which stays above 4.5:1.
Do **not** drop below 55% — that crosses the AA floor.

## Typography

| Role | Family | Treatment |
|---|---|---|
| Display / headline | Cormorant Garamond | 300–400 weight, tight leading |
| Logo script | Pinyon Script | Wordmark only, never body copy |
| Eyebrow / label | Inter | Uppercase, `0.28–0.42em` tracking, 10–12px |
| Body / UI | Inter | 400–500, 14–17px |

The eyebrow treatment echoes the logo's "JEWELLERY" tier and is the main device
tying sections back to the mark. When letter-spacing an uppercase string, add a
matching `text-indent` so it stays optically centered — tracking adds space
after the final letter too.

## Layout

- Max content width `1400px`; page gutters `16px → 24px (sm) → 40px (lg)`.
- Product imagery is **4:5 portrait**. Jewellery is small and vertical; square
  crops waste the frame.
- Section rhythm: `py-16` mobile, `py-24` desktop. Generous, because the
  photography is the argument.
- Dividers are 1px gold at 25–35% opacity. No drop shadows anywhere — a fine
  rule reads as print, a shadow reads as software.

## Conversion

Every terminal action is a WhatsApp deep link. There is no cart.

```js
const WA_NUMBER = '628111099399';
const waLink = (message) => `https://wa.me/${WA_NUMBER}?text=${encodeURIComponent(message)}`;
```

Messages are pre-filled, in Indonesian, and **context-aware** — a product card
names the piece, the custom form summarizes the brief. The customer should never
open WhatsApp to a blank box.

## Bilingual

Copy mixes Indonesian and English. Indonesian strings run 20–40% longer.

- No fixed-width buttons, no `truncate` on nav or headings.
- Assume every label can wrap to two lines.
- Test against the longest real strings: "Servis & Perbaikan", "Jual Emas Anda
  (Buyback)", "Cincin Kawin Emas Kuning 17K".

## Domain model

Grounded in the existing WordPress build (`wp-content/`), not invented:

- Karat range **16K–24K**, in yellow / white / rose gold.
- Gold rates are tracked **per karat**, not as one shop-wide number. A piece's
  price always uses its own karat's rate.
- Prices are **never stored on a product** — always computed from the live rate.
  This is why every CTA is "Tanya Harga", never "Add to Cart".
- Catalog splits **Fashion Jewellery** vs. **Emas Batangan** (bullion: Antam,
  UBS, Emasku).
- Services: Custom Order, Servis & Perbaikan, Buyback, Kalkulator Emas.
- Stock states: Available / Reserved / Sold.

## Assets

`VerenaLogo.jsx` currently draws the mark as inline SVG so the library has no
unresolved image imports. When the official lockups are exported, swap the
internals of that one file — no other component references the artwork directly.
