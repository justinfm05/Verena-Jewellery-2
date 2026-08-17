<?php
/**
 * Plugin Name: Verena Jewellery Tools
 * Description: Custom post types, pricing engine, WhatsApp checkout links, and the gold net-worth calculator for Verena Jewellery. Requires Advanced Custom Fields.
 * Version: 1.3.0
 * Author: Verena Jewellery
 * Text Domain: verena-jewellery-tools
 *
 * @package Verena_Jewellery_Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

define( 'VERENA_JT_VERSION', '1.3.0' );
define( 'VERENA_JT_DIR', plugin_dir_path( __FILE__ ) );
define( 'VERENA_JT_URL', plugin_dir_url( __FILE__ ) );

require_once VERENA_JT_DIR . 'db/schema.php';
require_once VERENA_JT_DIR . 'inc/post-types.php';
require_once VERENA_JT_DIR . 'inc/rewrites.php';
require_once VERENA_JT_DIR . 'inc/acf-fields.php';
require_once VERENA_JT_DIR . 'inc/formatting.php';
require_once VERENA_JT_DIR . 'inc/pricing.php';
require_once VERENA_JT_DIR . 'inc/whatsapp.php';
require_once VERENA_JT_DIR . 'inc/settings-page.php';
require_once VERENA_JT_DIR . 'inc/showcase-media-page.php';
require_once VERENA_JT_DIR . 'inc/hero-slides-page.php';
require_once VERENA_JT_DIR . 'inc/gold-price-page.php';
require_once VERENA_JT_DIR . 'inc/purity-options-page.php';
require_once VERENA_JT_DIR . 'inc/rest-calculator.php';
require_once VERENA_JT_DIR . 'inc/admin-leads-page.php';
require_once VERENA_JT_DIR . 'inc/quick-edit-status.php';
require_once VERENA_JT_DIR . 'inc/bullion-sheet-sync.php';
require_once VERENA_JT_DIR . 'inc/buyback-karat-sheet-sync.php';
require_once VERENA_JT_DIR . 'inc/valas-sheet-sync.php';

/**
 * Activation: create custom tables, seed default purity options, flush rewrite rules.
 */
function verena_jt_activate() {
	verena_jt_create_tables();
	verena_jt_seed_purity_options();

	// Post types and custom rewrite rules must be registered before flushing.
	verena_jt_register_post_types();
	verena_jt_register_rewrites();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'verena_jt_activate' );

/**
 * Re-run dbDelta whenever the plugin's version bumps, even without a
 * deactivate/reactivate cycle — activation hooks only fire once, but schema
 * changes (like the bullion price-history table added in 1.3.0) need to
 * reach sites where the plugin is already active. dbDelta only applies the
 * diff, so this is a no-op once a site is already current.
 */
function verena_jt_maybe_upgrade_db() {
	if ( get_option( 'verena_jt_db_version' ) !== VERENA_JT_VERSION ) {
		verena_jt_create_tables();
		update_option( 'verena_jt_db_version', VERENA_JT_VERSION );
	}
}
add_action( 'init', 'verena_jt_maybe_upgrade_db' );

/**
 * Deactivation: just flush rewrite rules. Never drop custom tables on
 * deactivation — pricing/lead data must survive a plugin toggle.
 */
function verena_jt_deactivate() {
	verena_jt_bullion_unschedule_sync();
	verena_jt_buyback_karat_unschedule_sync();
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'verena_jt_deactivate' );

/**
 * Warn in wp-admin if Advanced Custom Fields isn't active, since the
 * verena_product / verena_bullion edit screens depend on it for their
 * meta-box fields (sku, weight, prices, etc).
 */
function verena_jt_admin_notice_missing_acf() {
	if ( ! class_exists( 'ACF' ) ) {
		echo '<div class="notice notice-error"><p><strong>Verena Jewellery Tools</strong> requires the free <strong>Advanced Custom Fields</strong> plugin to be installed and active. Product/bullion detail fields will not appear until it is.</p></div>';
	}
}
add_action( 'admin_notices', 'verena_jt_admin_notice_missing_acf' );
