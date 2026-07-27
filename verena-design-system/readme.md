# Verena Jewellery — Design System

Verena Jewellery is a fine-jewellery brand. This system is built from the brand's logo lockup (`uploads/02 Logo Verena.PNG`) and its brand mascot — a yellow teddy bear plush wearing a brown "VERENA JEWELLERY" branded tee (`uploads/IMG_6753.jpeg`, `uploads/IMG_6754.jpeg`).

No Figma file, codebase, or product copy was supplied, so this system is built brand-guidelines-first: colors and mood are extracted directly from the logo; typography, components, and the UI kit are original constructions in that spirit, not copied from an existing product.

## Sources
- `uploads/02 Logo Verena.PNG` — the only ground-truth asset (copied to `assets/logo.png`).
- No Figma link, GitHub repo, or codebase was attached.

## Color extraction
Sampled directly from the logo: deep forest-green background `#24281C`, warm cream/gold mark `#F2E2BA`. All palette tokens in `tokens/colors.css` are tints/shades built from these two anchors plus a desaturated warm-neutral scale — no invented hues.

## Content fundamentals
No product copy was supplied. Provisional tone (update once real copy is available): quiet, confident luxury — short declarative lines, sentence case (not ALL CAPS, matching the logo's restrained "JEWELLERY" small-caps treatment used only for the lockup itself), no emoji, no exclamation marks. Speaks to "you" (the wearer), never "our customers." Example provisional lines used in the UI kit: "Cast in gold. Made to last." / "Each piece is finished by hand."

## Visual foundations
- **Color**: two-tone luxury palette — deep forest-green (`--green-700` `#24281C`) as the dominant dark/inverse surface, warm cream-gold (`--gold-300` `#F2E2BA`) as the light/accent surface. Warm neutral grays (desaturated from the green) carry body text and light-mode surfaces. Max two background colors: cream-white light mode, deep green dark/feature sections.
- **Type**: `Cormorant Garamond` (serif, display/headings — elegant, high-contrast, jewelry-appropriate) + `Jost` (geometric sans, body/UI, echoes the wide letter-spacing of the "JEWELLERY" wordmark) + `Mrs Saint Delafield` (script, sparing decorative accent only — mirrors the "Verena" script mark, never for body copy). **Font substitution flag**: no font files were provided; all three are the closest Google Fonts matches to the logo's script and small-caps letterforms — swap in real brand fonts if Verena has them.
- **Spacing**: 4px base scale (4/8/12/16/24/32/48/64/96/128).
- **Backgrounds**: flat color only — no gradients, no patterns/textures, no photographic backgrounds (none were supplied). Full-bleed deep-green sections used sparingly for emphasis (hero, footer).
- **Corners**: mostly square/sharp (2–4px) to read as refined and jewellery-precise; a single pill radius reserved for buttons and tags only.
- **Cards**: white surface, 1px `--border-subtle` hairline or a soft two-layer shadow (`--shadow-card`), never both heavily; no colored left-border accent.
- **Shadows**: soft, low-contrast, warm-tinted (shadows use the green ink, not pure black) — `--shadow-card` for resting cards, `--shadow-elevated` for modals/hover.
- **Hover states**: gold text/underline brightens one step (`--accent` → `--accent-hover`); dark buttons lighten slightly; no color inversion tricks.
- **Press states**: 2% scale-down (0.98) + no color change, fast (150ms).
- **Animation**: minimal — 150–250ms ease-standard fades and transform-only transitions; no bounce, no spring, nothing decorative.
- **Borders**: 1px hairline, `--border-subtle` on light surfaces, `rgba(cream,0.2)` on dark surfaces.
- **Transparency/blur**: reserved for sticky nav (frosted cream over content on scroll) only.
- **Imagery vibe**: none supplied — all UI kit imagery uses labeled placeholder slots, warm-neutral fill, no invented photography.

## Iconography
No icon set, icon font, or SVGs were supplied in source material. Components use **Lucide** (CDN, `unpkg.com/lucide@latest`) as a stand-in — thin 1.5px stroke, no fill, which matches the logo's monoline geometry. This is a flagged substitution: swap for Verena's real icon set if one exists. No emoji, no unicode glyphs used as icons.

## Mascot
A yellow teddy-bear plush in a brown "Verena Jewellery" branded tee is the brand's physical mascot (`assets/mascot-bear-front.jpg`, `assets/mascot-bear-back.jpg`). I can't generate a realistic 3D render/illustration of it — no image-generation tool is available to me here. Use the real photos as the mascot asset; if a polished 3D/illustrated version is needed, that requires an illustrator or an external image-gen tool.

## Intentional additions
No component source was provided, so this is an original, brand-guidelines-only component set: Button, IconButton, Input, Select, Badge/Tag, Card, ProductCard. Sized for a jewellery e-commerce context.

## Index
- `assets/logo.png` — Verena Jewellery logo lockup (dark-green background baked in; no transparent/mark-only version available).
- `assets/mascot-bear-front.jpg`, `assets/mascot-bear-back.jpg` — brand mascot plush reference photos.
- `tokens/colors.css`, `typography.css`, `spacing.css` — design tokens; `styles.css` imports all three.
- `components/core/` — Button, IconButton, Badge, Card, ProductCard, Input, Select (`.jsx` + `.d.ts` + `.prompt.md` + card HTML each dir).
- `guidelines/` — foundation specimen cards (colors, type, spacing, brand), including the hero banner treatment ("Cast in gold. Made to last.").
- `SKILL.md` — portable skill definition for use in Claude Code.
