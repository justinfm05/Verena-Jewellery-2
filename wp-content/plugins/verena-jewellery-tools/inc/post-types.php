<?php
/**
 * Custom post types and taxonomies: fashion jewellery products and
 * gold bullion listings.
 *
 * @package Verena_Jewellery_Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function verena_jt_register_post_types() {

	// ------------------------------------------------------------------
	// Fashion jewellery: verena_product
	// ------------------------------------------------------------------
	register_post_type(
		'verena_product',
		array(
			'labels'       => array(
				'name'               => 'Jewellery Pieces',
				'singular_name'      => 'Jewellery Piece',
				'add_new_item'       => 'Add New Piece',
				'edit_item'          => 'Edit Piece',
				'all_items'          => 'Jewellery Pieces',
				'search_items'       => 'Search Pieces',
				'not_found'          => 'No pieces found',
			),
			'public'       => true,
			'has_archive'  => false, // catalog is a page template, not the CPT archive.
			'rewrite'      => array( 'slug' => 'fashion' ),
			'menu_icon'    => 'dashicons-money-alt',
			'supports'     => array( 'title', 'editor', 'thumbnail' ),
			'show_in_rest' => true,
		)
	);

	register_taxonomy(
		'verena_category',
		'verena_product',
		array(
			'labels'            => array(
				'name'          => 'Categories',
				'singular_name' => 'Category',
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_admin_column' => true,
			'rewrite'           => array( 'slug' => 'fashion-category' ),
			'show_in_rest'      => true,
		)
	);

	// ------------------------------------------------------------------
	// Gold bullion: verena_bullion
	// ------------------------------------------------------------------
	register_post_type(
		'verena_bullion',
		array(
			'labels'       => array(
				'name'          => 'Bullion Listings',
				'singular_name' => 'Bullion Listing',
				'add_new_item'  => 'Add New Bullion Listing',
				'edit_item'     => 'Edit Bullion Listing',
				'all_items'     => 'Bullion Listings',
				'search_items'  => 'Search Bullion',
				'not_found'     => 'No bullion listings found',
			),
			'public'       => true,
			'has_archive'  => false,
			'rewrite'      => array( 'slug' => 'bullion-item' ),
			'menu_icon'    => 'dashicons-tag',
			'supports'     => array( 'title' ),
			'show_in_rest' => true,
		)
	);

	register_taxonomy(
		'verena_bullion_brand',
		'verena_bullion',
		array(
			'labels'            => array(
				'name'          => 'Brand',
				'singular_name' => 'Brand',
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_admin_column' => true,
			'rewrite'           => array( 'slug' => 'bullion-brand' ),
			'show_in_rest'      => true,
		)
	);
}
add_action( 'init', 'verena_jt_register_post_types' );

/**
 * Seed the three bullion brand terms on first load so the admin bullion
 * screen and public /bullion/{brand}/ pages have something to attach to
 * immediately, without the owner having to create taxonomy terms by hand.
 */
function verena_jt_seed_bullion_brand_terms() {
	if ( get_option( 'verena_jt_bullion_terms_seeded' ) ) {
		return;
	}

	$brands = array(
		'antam'  => 'Antam',
		'ubs'    => 'UBS',
		'emasku' => 'Emasku',
	);

	foreach ( $brands as $slug => $name ) {
		if ( ! term_exists( $slug, 'verena_bullion_brand' ) ) {
			wp_insert_term( $name, 'verena_bullion_brand', array( 'slug' => $slug ) );
		}
	}

	update_option( 'verena_jt_bullion_terms_seeded', 1 );
}
add_action( 'init', 'verena_jt_seed_bullion_brand_terms', 20 );

/**
 * Product status labels, shared between the ACF field choices, the public
 * status badge, and Quick Edit — single source of truth so they can't drift.
 */
function verena_jt_product_statuses() {
	return array(
		'available' => 'Available',
		'reserved'  => 'Reserved',
		'sold'      => 'Sold',
	);
}
