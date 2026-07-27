/**
 * VerenaLogo — the Verena Jewellery brand mark.
 *
 * Two lockups, matching the brand's supplied artwork:
 *   variant="stacked"     gem + V swoosh above the wordmark, centered.
 *                         For heroes, splash/loading states, square social.
 *   variant="horizontal"  gem + V swoosh at left, wordmark at right.
 *                         For the nav bar and anywhere horizontal space allows.
 *
 * ─────────────────────────────────────────────────────────────────────────
 * SWAPPING IN THE REAL ARTWORK
 *
 * The mark below is drawn in SVG so this file stays self-contained (no image
 * imports to resolve when the component is uploaded to Claude Design). When
 * the official .svg/.png lockups are available, replace the <GemMark /> and
 * <Wordmark /> internals with:
 *
 *   <img src="/assets/verena-mark.svg" alt="" className="h-full w-auto" />
 *
 * Nothing outside this file needs to change — every other component in the
 * library consumes VerenaLogo, never the artwork directly.
 * ─────────────────────────────────────────────────────────────────────────
 *
 * The mark is a single ink color so it inverts cleanly: champagne on the
 * forest-green ground, forest green on champagne. Pass `ink` to control it.
 */

const INK = {
  champagne: '#EDE3C0',
  forest: '#1E2A1E',
  gold: '#C9A24B',
};

/* The fine line-art gem sitting above the V swoosh. Stroked, never filled —
   the openness is what keeps the mark quiet rather than heraldic. */
function GemMark({ ink }) {
  return (
    <svg
      viewBox="0 0 120 112"
      fill="none"
      stroke={ink}
      strokeLinecap="round"
      strokeLinejoin="round"
      className="h-full w-auto"
      aria-hidden="true"
      focusable="false"
    >
      {/* Crown — table, shoulders, girdle */}
      <path d="M46 20 H74 L82 37 H38 Z" strokeWidth="1.6" />
      {/* Crown facets */}
      <path d="M46 20 L52 37 M74 20 L68 37" strokeWidth="1" opacity="0.75" />
      {/* Pavilion */}
      <path d="M38 37 L60 68 L82 37" strokeWidth="1.6" />
      {/* Pavilion facets converging on the culet */}
      <path d="M52 37 L60 68 M68 37 L60 68" strokeWidth="1" opacity="0.75" />

      {/* The V swoosh — calligraphic, left arm weighted, right arm tapering
          upward into a flourish. Two strokes of differing weight stand in for
          a true variable-width pen stroke. */}
      <path d="M30 74 C 36 92, 48 103, 60 106" strokeWidth="3" />
      <path d="M60 106 C 74 101, 88 86, 96 62" strokeWidth="1.3" />
    </svg>
  );
}

/* "Verena" in script over "JEWELLERY" in tracked small caps — the two-tier
   typographic relationship is the load-bearing part of the identity. */
function Wordmark({ ink, align = 'center' }) {
  return (
    <span
      className={[
        'flex flex-col leading-none',
        align === 'center' ? 'items-center' : 'items-start',
      ].join(' ')}
      style={{ color: ink }}
    >
      <span
        className="text-[1.9em] leading-[1.05]"
        style={{ fontFamily: "'Pinyon Script', 'Great Vibes', 'Cormorant Garamond', cursive" }}
      >
        Verena
      </span>
      <span
        className="text-[0.52em] uppercase"
        style={{
          fontFamily: "'Inter', ui-sans-serif, system-ui, sans-serif",
          letterSpacing: '0.42em',
          /* Tracking adds space after the final letter too; pull it back so
             the word stays optically centered under the script above. */
          textIndent: '0.42em',
          fontWeight: 400,
        }}
      >
        Jewellery
      </span>
    </span>
  );
}

export default function VerenaLogo({
  variant = 'horizontal',
  ink = INK.champagne,
  className = '',
  /* Rendered as the accessible name. Set to "" on decorative instances that
     sit next to a visible "Verena Jewellery" text label, so screen readers
     don't announce the brand twice. */
  title = 'Verena Jewellery',
}) {
  const labelProps = title
    ? { role: 'img', 'aria-label': title }
    : { 'aria-hidden': 'true' };

  return (
    <span className={['inline-flex select-none', className].join(' ')} {...labelProps}>
      {/* Self-contained font loading. Once the host app loads these families
          globally, this <style> block can be deleted from every component. */}
      <style>{`
        @import url('https://fonts.googleapis.com/css2?family=Pinyon+Script&family=Inter:wght@400;500&display=swap');
      `}</style>

      {variant === 'stacked' ? (
        <span className="flex flex-col items-center gap-[0.45em]">
          <span className="h-[2.6em]">
            <GemMark ink={ink} />
          </span>
          <Wordmark ink={ink} align="center" />
        </span>
      ) : (
        <span className="flex flex-row items-center gap-[0.6em]">
          <span className="h-[2.35em] shrink-0">
            <GemMark ink={ink} />
          </span>
          <Wordmark ink={ink} align="start" />
        </span>
      )}
    </span>
  );
}

export { INK };

/* ── Preview ──────────────────────────────────────────────────────────────
   Delete before shipping; kept so the file renders standalone in Claude Design.
*/
export function VerenaLogoPreview() {
  return (
    <div className="grid gap-px bg-[#C9A24B]/30 sm:grid-cols-2">
      <div className="flex items-center justify-center bg-[#1E2A1E] p-12">
        <VerenaLogo variant="stacked" ink={INK.champagne} className="text-[22px]" />
      </div>
      <div className="flex items-center justify-center bg-[#F2E9CC] p-12">
        <VerenaLogo variant="horizontal" ink={INK.forest} className="text-[18px]" />
      </div>
    </div>
  );
}
