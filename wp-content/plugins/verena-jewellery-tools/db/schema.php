<?php
/**
 * Custom database tables: gold rate history, buyback rate history, and
 * saved gold net-worth calculator lists.
 *
 * @package Verena_Jewellery_Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create (or update, via dbDelta) all custom tables used by this plugin.
 * Safe to call on every activation — dbDelta only applies the diff.
 */
function verena_jt_create_tables() {
	global $wpdb;

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$charset_collate = $wpdb->get_charset_collate();

	$gold_rate_log = $wpdb->prefix . 'verena_gold_rate_log';
	$buyback_rate_log = $wpdb->prefix . 'verena_buyback_rate_log';
	$calc_lists = $wpdb->prefix . 'verena_calc_lists';
	$bullion_price_history = $wpdb->prefix . 'verena_bullion_price_history';

	// One row per change = a free audit trail. "Current" rate = latest row.
	$sql = "CREATE TABLE {$gold_rate_log} (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		purity_label VARCHAR(20) NOT NULL DEFAULT '17K',
		sell_price_per_gram BIGINT UNSIGNED NOT NULL,
		created_at DATETIME NOT NULL,
		created_by BIGINT UNSIGNED NULL,
		PRIMARY KEY  (id),
		KEY created_at (created_at)
	) {$charset_collate};

	CREATE TABLE {$buyback_rate_log} (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		price_per_gram BIGINT UNSIGNED NOT NULL,
		created_at DATETIME NOT NULL,
		created_by BIGINT UNSIGNED NULL,
		PRIMARY KEY  (id),
		KEY created_at (created_at)
	) {$charset_collate};

	CREATE TABLE {$calc_lists} (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		slug VARCHAR(32) NOT NULL,
		contact_name VARCHAR(191) NULL,
		contact_whatsapp VARCHAR(32) NULL,
		items_json LONGTEXT NOT NULL,
		total_value BIGINT UNSIGNED NOT NULL DEFAULT 0,
		created_at DATETIME NOT NULL,
		updated_at DATETIME NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY slug (slug),
		KEY created_at (created_at)
	) {$charset_collate};

	CREATE TABLE {$bullion_price_history} (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		brand VARCHAR(20) NOT NULL,
		recorded_date DATE NOT NULL,
		sell_price BIGINT UNSIGNED NOT NULL,
		created_at DATETIME NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY brand_date (brand, recorded_date)
	) {$charset_collate};";

	dbDelta( $sql );
}

/**
 * Seed the karat -> purity-fraction table used by the buyback estimator and
 * the gold net-worth calculator, if it hasn't been set yet. Stored as a
 * single wp_option (array of {label, fraction_bps, sort_order}) since it's
 * a short, admin-editable list rather than something that needs its own table.
 *
 * Fractions are in basis points (7500 = 75.00%). The 17K row is seeded at
 * 75% per the shop's own description of their SNI-labelled stock and should
 * be confirmed by the owner in wp-admin before launch.
 */
function verena_jt_seed_purity_options() {
	if ( false !== get_option( 'verena_purity_options' ) ) {
		return;
	}

	$defaults = array(
		array(
			'label'       => '24K',
			'fraction_bps' => 9999,
			'sort_order'  => 10,
		),
		array(
			'label'       => '22K',
			'fraction_bps' => 9160,
			'sort_order'  => 20,
		),
		array(
			'label'       => '18K',
			'fraction_bps' => 7500,
			'sort_order'  => 30,
		),
		array(
			'label'       => '17K',
			'fraction_bps' => 7500,
			'sort_order'  => 40,
		),
		array(
			'label'       => '16K',
			'fraction_bps' => 7000,
			'sort_order'  => 50,
		),
		array(
			'label'       => '14K',
			'fraction_bps' => 5850,
			'sort_order'  => 60,
		),
		array(
			'label'       => 'Tidak yakin / belum dicek',
			'fraction_bps' => 5000,
			'sort_order'  => 70,
		),
	);

	add_option( 'verena_purity_options', $defaults );
}
