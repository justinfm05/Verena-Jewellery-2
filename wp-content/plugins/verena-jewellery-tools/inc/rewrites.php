<?php
/**
 * URL routing for shareable gold-calculator links:
 * /gold-calculator/{slug}/ loads the calculator page template with that
 * saved list pre-populated (see theme's page-gold-calculator.php).
 *
 * The base /gold-calculator/ page itself is an ordinary WP Page the owner
 * creates with the slug "gold-calculator" — WordPress's own template
 * hierarchy already maps that to page-gold-calculator.php, so only the
 * {slug} sub-path needs a custom rewrite rule.
 *
 * @package Verena_Jewellery_Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function verena_jt_register_rewrites() {
	add_rewrite_rule(
		'^gold-calculator/([a-zA-Z0-9]+)/?$',
		'index.php?pagename=gold-calculator&verena_calc_slug=$matches[1]',
		'top'
	);
}
add_action( 'init', 'verena_jt_register_rewrites' );

add_filter(
	'query_vars',
	function ( $vars ) {
		$vars[] = 'verena_calc_slug';
		return $vars;
	}
);
