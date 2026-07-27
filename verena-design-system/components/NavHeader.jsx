import { useEffect, useRef, useState } from 'react';

/**
 * NavHeader — Component 0
 *
 * The deep forest-green bar that frames every page. Horizontal logo lockup at
 * left, navigation at center-right, and a gold "Chat via WhatsApp" button as
 * the single primary action.
 *
 * Design notes
 * - Most traffic arrives from Instagram's in-app browser on mobile, where the
 *   viewport is short and the browser adds its own chrome. The bar is kept to
 *   64px on mobile (80px from `lg`) and the WhatsApp action stays visible at
 *   every breakpoint — it never collapses into the hamburger, because it is
 *   the conversion path.
 * - Nav labels are Indonesian and run long ("Servis & Perbaikan"). Nothing is
 *   fixed-width or truncated; the desktop bar wraps to the drawer below `lg`
 *   rather than compressing.
 * - A 1px gold hairline separates the bar from the page instead of a shadow.
 *   Shadows read as software; a fine rule reads as print.
 *
 * Self-contained: no imports beyond React. In the full project, delete the
 * local VerenaLogo below and use `import VerenaLogo from './VerenaLogo'`.
 */

const WA_NUMBER = '628111099399';
const waLink = (message) =>
  `https://wa.me/${WA_NUMBER}?text=${encodeURIComponent(message)}`;

const NAV_LINKS = [
  { label: 'Fashion Jewellery', href: '/fashion/' },
  { label: 'Emas Batangan', href: '/bullion/' },
  { label: 'Custom Order', href: '/custom-orders/' },
  { label: 'Servis & Perbaikan', href: '/repairs/' },
  { label: 'Tentang Kami', href: '/about/' },
];

/* Secondary destinations — surfaced in the mobile drawer only, where there is
   room for them without crowding the primary five. */
const NAV_SECONDARY = [
  { label: 'Kalkulator Emas', href: '/gold-calculator/' },
  { label: 'Jual Emas Anda (Buyback)', href: '/buyback/' },
];

/* ── Local copy of the brand mark ───────────────────────────────────────── */
function VerenaLogo({ ink = '#EDE3C0', className = '' }) {
  return (
    <span className={['inline-flex select-none', className].join(' ')} role="img" aria-label="Verena Jewellery">
      <style>{`@import url('https://fonts.googleapis.com/css2?family=Pinyon+Script&family=Inter:wght@400;500&display=swap');`}</style>
      <span className="flex flex-row items-center gap-[0.6em]">
        <span className="h-[2.35em] shrink-0">
          <svg viewBox="0 0 120 112" fill="none" stroke={ink} strokeLinecap="round" strokeLinejoin="round" className="h-full w-auto" aria-hidden="true" focusable="false">
            <path d="M46 20 H74 L82 37 H38 Z" strokeWidth="1.6" />
            <path d="M46 20 L52 37 M74 20 L68 37" strokeWidth="1" opacity="0.75" />
            <path d="M38 37 L60 68 L82 37" strokeWidth="1.6" />
            <path d="M52 37 L60 68 M68 37 L60 68" strokeWidth="1" opacity="0.75" />
            <path d="M30 74 C 36 92, 48 103, 60 106" strokeWidth="3" />
            <path d="M60 106 C 74 101, 88 86, 96 62" strokeWidth="1.3" />
          </svg>
        </span>
        <span className="flex flex-col items-start leading-none" style={{ color: ink }}>
          <span className="text-[1.9em] leading-[1.05]" style={{ fontFamily: "'Pinyon Script', 'Great Vibes', 'Cormorant Garamond', cursive" }}>
            Verena
          </span>
          <span
            className="text-[0.52em] uppercase"
            style={{ fontFamily: "'Inter', ui-sans-serif, system-ui, sans-serif", letterSpacing: '0.42em', textIndent: '0.42em' }}
          >
            Jewellery
          </span>
        </span>
      </span>
    </span>
  );
}

function WhatsAppIcon({ className = 'h-4 w-4' }) {
  return (
    <svg viewBox="0 0 24 24" fill="currentColor" className={className} aria-hidden="true" focusable="false">
      <path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.96-.94 1.16-.17.2-.35.22-.64.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.49-1.77-1.66-2.06-.17-.3-.02-.46.13-.6.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.61-.92-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37s-1.04 1.02-1.04 2.48 1.06 2.88 1.21 3.08c.15.2 2.1 3.2 5.08 4.49.71.3 1.26.49 1.69.63.71.22 1.36.19 1.87.12.57-.09 1.76-.72 2.01-1.41.25-.7.25-1.29.17-1.42-.07-.13-.27-.2-.57-.35z" />
      <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.46 1.32 4.96L2 22l5.25-1.38a9.87 9.87 0 0 0 4.79 1.22h.01c5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2zm0 18.15h-.01a8.2 8.2 0 0 1-4.19-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.19 8.19 0 0 1-1.26-4.38c0-4.54 3.7-8.23 8.25-8.23a8.23 8.23 0 0 1 8.24 8.24c0 4.54-3.7 8.23-8.24 8.23z" />
    </svg>
  );
}

export default function NavHeader({
  links = NAV_LINKS,
  secondaryLinks = NAV_SECONDARY,
  /* Marks the current page so it can be styled and announced correctly. */
  activeHref = '/fashion/',
}) {
  const [open, setOpen] = useState(false);
  const panelRef = useRef(null);
  const toggleRef = useRef(null);

  /* Close on Escape, and lock background scroll while the drawer is open so
     the page underneath doesn't scroll on iOS. */
  useEffect(() => {
    if (!open) return undefined;

    const onKeyDown = (event) => {
      if (event.key === 'Escape') {
        setOpen(false);
        toggleRef.current?.focus();
      }
    };

    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    document.addEventListener('keydown', onKeyDown);

    return () => {
      document.body.style.overflow = previousOverflow;
      document.removeEventListener('keydown', onKeyDown);
    };
  }, [open]);

  /* Move focus into the drawer when it opens, so keyboard and screen-reader
     users land inside the panel rather than back at the top of the document. */
  useEffect(() => {
    if (open) panelRef.current?.focus();
  }, [open]);

  const waHref = waLink(
    'Halo Verena Jewellery, saya ingin bertanya mengenai koleksi perhiasan emas.'
  );

  return (
    <header className="sticky top-0 z-50 border-b border-[#C9A24B]/35 bg-[#1E2A1E]">
      <style>{`@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap');`}</style>

      <div
        className="mx-auto flex h-16 max-w-[1400px] items-center gap-4 px-4 sm:px-6 lg:h-20 lg:px-10"
        style={{ fontFamily: "'Inter', ui-sans-serif, system-ui, sans-serif" }}
      >
        {/* Brand */}
        <a
          href="/"
          className="shrink-0 rounded-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-[#C9A24B] focus-visible:ring-offset-4 focus-visible:ring-offset-[#1E2A1E]"
        >
          <VerenaLogo ink="#EDE3C0" className="text-[13px] lg:text-[15px]" />
        </a>

        {/* Desktop navigation */}
        <nav aria-label="Navigasi utama" className="ml-auto hidden lg:block">
          <ul className="flex items-center gap-8">
            {links.map((link) => {
              const isActive = link.href === activeHref;
              return (
                <li key={link.href}>
                  <a
                    href={link.href}
                    aria-current={isActive ? 'page' : undefined}
                    className={[
                      'group relative inline-block whitespace-nowrap py-2 text-[13px] tracking-[0.08em] transition-colors',
                      'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#C9A24B] focus-visible:ring-offset-4 focus-visible:ring-offset-[#1E2A1E]',
                      isActive ? 'text-[#EDE3C0]' : 'text-[#EDE3C0]/75 hover:text-[#EDE3C0]',
                    ].join(' ')}
                  >
                    {link.label}
                    {/* Gold underline: always present for the active page, drawn
                        in on hover for the rest. */}
                    <span
                      aria-hidden="true"
                      className={[
                        'absolute -bottom-0.5 left-0 h-px bg-[#C9A24B] transition-all duration-300',
                        isActive ? 'w-full' : 'w-0 group-hover:w-full',
                      ].join(' ')}
                    />
                  </a>
                </li>
              );
            })}
          </ul>
        </nav>

        {/* Primary action — never collapses into the hamburger */}
        <a
          href={waHref}
          target="_blank"
          rel="noopener noreferrer"
          className="ml-auto inline-flex shrink-0 items-center gap-2 rounded-full bg-[#C9A24B] px-4 py-2.5 text-[12px] font-medium tracking-[0.06em] text-[#1E2A1E] transition-colors hover:bg-[#D4AF37] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#EDE3C0] focus-visible:ring-offset-2 focus-visible:ring-offset-[#1E2A1E] lg:ml-8 lg:px-5 lg:py-3 lg:text-[13px]"
        >
          <WhatsAppIcon className="h-4 w-4" />
          {/* The label shortens on the narrowest phones so the bar never wraps,
              but the icon keeps the action recognisable. */}
          <span className="hidden sm:inline">Chat via WhatsApp</span>
          <span className="sm:hidden">Chat</span>
        </a>

        {/* Mobile drawer toggle */}
        <button
          ref={toggleRef}
          type="button"
          onClick={() => setOpen((value) => !value)}
          aria-expanded={open}
          aria-controls="verena-mobile-nav"
          className="-mr-2 inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-sm text-[#EDE3C0] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#C9A24B] lg:hidden"
        >
          <span className="sr-only">{open ? 'Tutup menu' : 'Buka menu'}</span>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" className="h-5 w-5" aria-hidden="true">
            {open ? <path d="M6 6l12 12M18 6L6 18" /> : <path d="M3 7h18M3 12h18M3 17h18" />}
          </svg>
        </button>
      </div>

      {/* Mobile drawer */}
      <div
        id="verena-mobile-nav"
        ref={panelRef}
        tabIndex={-1}
        hidden={!open}
        className="border-t border-[#C9A24B]/25 bg-[#1E2A1E] lg:hidden"
        style={{ fontFamily: "'Inter', ui-sans-serif, system-ui, sans-serif" }}
      >
        <nav aria-label="Navigasi seluler" className="max-h-[calc(100vh-4rem)] overflow-y-auto px-4 py-6 sm:px-6">
          <ul className="flex flex-col">
            {links.map((link) => {
              const isActive = link.href === activeHref;
              return (
                <li key={link.href}>
                  <a
                    href={link.href}
                    aria-current={isActive ? 'page' : undefined}
                    onClick={() => setOpen(false)}
                    className={[
                      'flex items-center justify-between border-b border-[#EDE3C0]/10 py-4 text-[15px] transition-colors',
                      'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#C9A24B]',
                      isActive ? 'text-[#C9A24B]' : 'text-[#EDE3C0] hover:text-[#C9A24B]',
                    ].join(' ')}
                  >
                    {link.label}
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.25" className="h-4 w-4 opacity-50" aria-hidden="true">
                      <path d="M9 6l6 6-6 6" />
                    </svg>
                  </a>
                </li>
              );
            })}
          </ul>

          <p className="mt-7 text-[10px] uppercase tracking-[0.32em] text-[#EDE3C0]/45">
            Layanan Lainnya
          </p>
          <ul className="mt-3 flex flex-col gap-3">
            {secondaryLinks.map((link) => (
              <li key={link.href}>
                <a
                  href={link.href}
                  onClick={() => setOpen(false)}
                  className="text-[14px] text-[#EDE3C0]/75 transition-colors hover:text-[#C9A24B] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#C9A24B]"
                >
                  {link.label}
                </a>
              </li>
            ))}
          </ul>

          {/* Reassurance at the bottom of the drawer — the 1999 date is the
              brand's strongest single trust signal, so it appears wherever a
              customer might hesitate. */}
          <p className="mt-8 border-t border-[#EDE3C0]/10 pt-6 text-[12px] leading-relaxed text-[#EDE3C0]/60">
            Toko emas &amp; berlian kepercayaan sejak 1999.
            <br />
            ITC Fatmawati, Jakarta Selatan.
          </p>
        </nav>
      </div>
    </header>
  );
}

/* ── Preview ──────────────────────────────────────────────────────────────
   Delete before shipping; kept so the file renders standalone in Claude Design.
*/
export function NavHeaderPreview() {
  return (
    <div className="min-h-screen bg-[#F2E9CC]">
      <NavHeader />
      <div className="mx-auto max-w-[1400px] px-6 py-16">
        <p className="text-[#1E2A1E]/60">Konten halaman…</p>
      </div>
    </div>
  );
}
