/**
 * Header interactions: mobile menu toggle + desktop "Layanan" dropdown.
 * Plain open/closed state toggles, no animation library (matches the design
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

	// Desktop "Layanan" dropdown.
	const dropdown = document.querySelector( '[data-dropdown]' );
	const dropToggle = document.querySelector( '[data-dropdown-toggle]' );
	if ( dropdown && dropToggle ) {
		dropToggle.addEventListener( 'click', function ( e ) {
			e.stopPropagation();
			const open = dropdown.classList.toggle( 'is-open' );
			dropToggle.setAttribute( 'aria-expanded', String( open ) );
		} );

		// Close on outside click or Escape.
		document.addEventListener( 'click', function ( e ) {
			if ( ! dropdown.contains( e.target ) ) {
				dropdown.classList.remove( 'is-open' );
				dropToggle.setAttribute( 'aria-expanded', 'false' );
			}
		} );
		document.addEventListener( 'keydown', function ( e ) {
			if ( 'Escape' === e.key ) {
				dropdown.classList.remove( 'is-open' );
				dropToggle.setAttribute( 'aria-expanded', 'false' );
			}
		} );
	}
} );
