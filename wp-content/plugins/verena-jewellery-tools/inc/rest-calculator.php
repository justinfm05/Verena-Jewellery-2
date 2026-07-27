<?php
/**
 * REST API for the gold net-worth calculator's "save & get shareable link"
 * feature. Public and unauthenticated by nature (visitors aren't logged
 * in), so every write is guarded by a nonce, a honeypot field, and basic
 * per-IP rate limiting to keep the leads table free of spam.
 *
 * @package Verena_Jewellery_Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'VERENA_JT_MAX_ITEMS_PER_LIST', 30 );
define( 'VERENA_JT_MAX_SAVES_PER_HOUR', 10 );

function verena_jt_register_rest_routes() {
	register_rest_route(
		'verena/v1',
		'/calculator',
		array(
			'methods'             => 'POST',
			'callback'            => 'verena_jt_rest_create_calculator_list',
			'permission_callback' => '__return_true',
		)
	);

	register_rest_route(
		'verena/v1',
		'/calculator/(?P<slug>[a-zA-Z0-9]+)',
		array(
			array(
				'methods'             => 'GET',
				'callback'            => 'verena_jt_rest_get_calculator_list',
				'permission_callback' => '__return_true',
			),
			array(
				'methods'             => 'PUT',
				'callback'            => 'verena_jt_rest_update_calculator_list',
				'permission_callback' => '__return_true',
			),
		)
	);

	register_rest_route(
		'verena/v1',
		'/rates',
		array(
			'methods'             => 'GET',
			'callback'            => 'verena_jt_rest_get_rates',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'verena_jt_register_rest_routes' );

/**
 * Current gold rate, buyback rate, and purity options — used by the
 * calculator's JS to compute live totals without a page reload. Also
 * inlined at page-render time (see page-gold-calculator.php); this route
 * exists so the calculator can silently refresh if a visitor leaves the
 * tab open across a rate change.
 */
function verena_jt_rest_get_rates( WP_REST_Request $request ) {
	$gold_rates = verena_get_all_current_gold_rates();
	$buyback_rate = verena_get_current_buyback_rate();

	$gold_rates_by_purity = array();
	foreach ( $gold_rates as $purity_label => $rate ) {
		$gold_rates_by_purity[ $purity_label ] = (int) $rate['sell_price_per_gram'];
	}

	return rest_ensure_response(
		array(
			'gold_rate_per_gram_by_purity' => $gold_rates_by_purity,
			'buyback_rate_per_gram'        => $buyback_rate ? (int) $buyback_rate['price_per_gram'] : null,
			'purity_options'               => verena_get_purity_options(),
		)
	);
}

/**
 * Shared validation for an incoming request: nonce, honeypot, rate limit,
 * and item-shape sanitization. Returns a WP_Error on any failure, or the
 * sanitized items array + contact fields on success.
 *
 * @param WP_REST_Request $request
 * @return array|WP_Error
 */
function verena_jt_validate_calculator_payload( WP_REST_Request $request ) {
	$nonce = $request->get_param( 'security' );
	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'verena_calculator' ) ) {
		return new WP_Error( 'verena_invalid_nonce', 'Invalid or expired request. Please refresh the page and try again.', array( 'status' => 403 ) );
	}

	// Honeypot: a field real visitors never see or fill in. Any value here
	// means a bot filled every field on the form.
	if ( ! empty( $request->get_param( 'website' ) ) ) {
		return new WP_Error( 'verena_spam_detected', 'Request rejected.', array( 'status' => 400 ) );
	}

	$ip = verena_jt_get_client_ip();
	$rate_key = 'verena_calc_rate_' . md5( $ip );
	$attempts = (int) get_transient( $rate_key );
	if ( $attempts >= VERENA_JT_MAX_SAVES_PER_HOUR ) {
		return new WP_Error( 'verena_rate_limited', 'Too many requests. Please try again later.', array( 'status' => 429 ) );
	}
	set_transient( $rate_key, $attempts + 1, HOUR_IN_SECONDS );

	$raw_items = $request->get_param( 'items' );
	if ( ! is_array( $raw_items ) || empty( $raw_items ) ) {
		return new WP_Error( 'verena_no_items', 'Add at least one item before saving.', array( 'status' => 400 ) );
	}
	if ( count( $raw_items ) > VERENA_JT_MAX_ITEMS_PER_LIST ) {
		return new WP_Error( 'verena_too_many_items', 'A list can have at most ' . VERENA_JT_MAX_ITEMS_PER_LIST . ' items.', array( 'status' => 400 ) );
	}

	$items = array();
	$total = 0;
	foreach ( $raw_items as $raw_item ) {
		$description = isset( $raw_item['description'] ) ? sanitize_text_field( $raw_item['description'] ) : '';
		$weight = isset( $raw_item['weight_grams'] ) ? (float) $raw_item['weight_grams'] : 0;
		$purity_label = isset( $raw_item['purity_label'] ) ? sanitize_text_field( $raw_item['purity_label'] ) : '';

		if ( $weight <= 0 || null === verena_purity_fraction_bps( $purity_label ) ) {
			continue; // Skip malformed rows rather than failing the whole save.
		}

		$estimate = verena_compute_buyback_estimate( $weight, $purity_label );
		if ( null === $estimate ) {
			return new WP_Error( 'verena_no_rate', 'No buyback rate has been set yet — please check back shortly.', array( 'status' => 503 ) );
		}

		$items[] = array(
			'description'     => $description,
			'weight_grams'    => $weight,
			'purity_label'    => $purity_label,
			'estimated_value' => $estimate,
		);
		$total += $estimate;
	}

	if ( empty( $items ) ) {
		return new WP_Error( 'verena_no_valid_items', 'None of the items provided were valid.', array( 'status' => 400 ) );
	}

	return array(
		'items'            => $items,
		'total'            => $total,
		'contact_name'     => sanitize_text_field( (string) $request->get_param( 'contact_name' ) ),
		'contact_whatsapp' => sanitize_text_field( (string) $request->get_param( 'contact_whatsapp' ) ),
	);
}

function verena_jt_get_client_ip() {
	// Behind SiteGround's proxy/CDN, prefer the forwarded header when present.
	if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$forwarded = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
		return trim( $forwarded[0] );
	}
	return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0.0.0.0';
}

function verena_jt_generate_unique_slug() {
	global $wpdb;
	$table = $wpdb->prefix . 'verena_calc_lists';

	do {
		$slug = substr( str_replace( array( '+', '/', '=' ), '', base64_encode( wp_generate_password( 16, false ) ) ), 0, 10 );
		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE slug = %s", $slug ) );
	} while ( $exists );

	return $slug;
}

function verena_jt_rest_create_calculator_list( WP_REST_Request $request ) {
	$validated = verena_jt_validate_calculator_payload( $request );
	if ( is_wp_error( $validated ) ) {
		return $validated;
	}

	global $wpdb;
	$slug = verena_jt_generate_unique_slug();
	$now = current_time( 'mysql' );

	$wpdb->insert(
		$wpdb->prefix . 'verena_calc_lists',
		array(
			'slug'             => $slug,
			'contact_name'     => $validated['contact_name'],
			'contact_whatsapp' => $validated['contact_whatsapp'],
			'items_json'       => wp_json_encode( $validated['items'] ),
			'total_value'      => $validated['total'],
			'created_at'       => $now,
			'updated_at'       => $now,
		),
		array( '%s', '%s', '%s', '%s', '%d', '%s', '%s' )
	);

	return rest_ensure_response(
		array(
			'slug'          => $slug,
			'shareable_url' => home_url( '/gold-calculator/' . $slug . '/' ),
			'total'         => $validated['total'],
		)
	);
}

function verena_jt_rest_update_calculator_list( WP_REST_Request $request ) {
	$validated = verena_jt_validate_calculator_payload( $request );
	if ( is_wp_error( $validated ) ) {
		return $validated;
	}

	global $wpdb;
	$table = $wpdb->prefix . 'verena_calc_lists';
	$slug = sanitize_text_field( $request->get_param( 'slug' ) );

	$existing_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE slug = %s", $slug ) );
	if ( ! $existing_id ) {
		return new WP_Error( 'verena_not_found', 'That saved list could not be found.', array( 'status' => 404 ) );
	}

	$update = array(
		'items_json'  => wp_json_encode( $validated['items'] ),
		'total_value' => $validated['total'],
		'updated_at'  => current_time( 'mysql' ),
	);
	$formats = array( '%s', '%d', '%s' );

	if ( $validated['contact_name'] ) {
		$update['contact_name'] = $validated['contact_name'];
		$formats[] = '%s';
	}
	if ( $validated['contact_whatsapp'] ) {
		$update['contact_whatsapp'] = $validated['contact_whatsapp'];
		$formats[] = '%s';
	}

	$wpdb->update( $table, $update, array( 'id' => $existing_id ), $formats, array( '%d' ) );

	return rest_ensure_response(
		array(
			'slug'          => $slug,
			'shareable_url' => home_url( '/gold-calculator/' . $slug . '/' ),
			'total'         => $validated['total'],
		)
	);
}

function verena_jt_rest_get_calculator_list( WP_REST_Request $request ) {
	global $wpdb;
	$table = $wpdb->prefix . 'verena_calc_lists';
	$slug = sanitize_text_field( $request->get_param( 'slug' ) );

	$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE slug = %s", $slug ), ARRAY_A );
	if ( ! $row ) {
		return new WP_Error( 'verena_not_found', 'That saved list could not be found.', array( 'status' => 404 ) );
	}

	return rest_ensure_response(
		array(
			'slug'             => $row['slug'],
			'items'            => json_decode( $row['items_json'], true ),
			'total'            => (int) $row['total_value'],
			'contact_name'     => $row['contact_name'],
			'contact_whatsapp' => $row['contact_whatsapp'],
		)
	);
}

/**
 * Fetch a saved list by slug directly, for use by the page template
 * (server-side render) rather than the client-side JS fetch — avoids a
 * loading flash when someone opens a shareable link.
 *
 * @param string $slug
 * @return array|null
 */
function verena_jt_get_calculator_list_by_slug( $slug ) {
	global $wpdb;
	$table = $wpdb->prefix . 'verena_calc_lists';
	$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE slug = %s", sanitize_text_field( $slug ) ), ARRAY_A );

	if ( ! $row ) {
		return null;
	}

	return array(
		'slug'             => $row['slug'],
		'items'            => json_decode( $row['items_json'], true ),
		'total'            => (int) $row['total_value'],
		'contact_name'     => $row['contact_name'],
		'contact_whatsapp' => $row['contact_whatsapp'],
	);
}
