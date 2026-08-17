/**
 * Header interactions: mobile menu toggle.
 * Plain open/closed state toggle, no animation library (matches the design
 * handoff's behaviour spec).
 */
document.addEventListener( 'DOMContentLoaded', function () {
	// Mobile slide-down menu.
	const menuToggle = document.querySelector( '[data-menu-toggle]' );
	const mobileNav = document.querySelector( '[data-mobile-nav]' );
	if ( menuToggle && mobileNav ) {
		menuToggle.addEventListener( 'click', function () {
			const open = mobileNav.classList.toggle( 'is-open' );
			menuToggle.setAttribute( 'aria-expanded', String( open ) );
		} );
	}

	// Keep the gold price ticker's sticky offset (--header-h, used by
	// .gold-ticker in style.css) in sync with the nav bar's actual rendered
	// height, so the ticker sticks flush below the nav on every screen size
	// instead of relying on one hardcoded pixel value.
	const siteHeader = document.querySelector( '.site-header' );
	if ( siteHeader && window.ResizeObserver ) {
		const setHeaderHeight = function () {
			document.documentElement.style.setProperty( '--header-h', siteHeader.offsetHeight + 'px' );
		};
		new ResizeObserver( setHeaderHeight ).observe( siteHeader );
		setHeaderHeight();
	}
} );
