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
} );
