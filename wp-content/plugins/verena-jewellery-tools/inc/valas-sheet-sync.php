<?php
/**
 * Pulls a "Valas" (currency exchange) tab from a Google Sheet — published
 * to the web as CSV, same pattern as the bullion/buyback-karat sheets — on
 * the same 5-minute schedule, caching a currency-code-keyed IDR rate for the
 * Valas page.
 *
 * NOT YET LIVE: VERENA_JT_VALAS_SHEET_CSV_URL is a placeholder. This is
 * scaffolding for a page the owner wants to exist ahead of the actual
 * project — the Valas Google Sheet doesn't exist yet. Once it does, publish
 * its currency tab to the web (File > Share > Publish to web > CSV, same as
 * the bullion sheet) and paste the resulting URL in here. Until then the
 * sync silently no-ops (never fetches, never errors) and the Valas page
 * shows its empty state.
 *
 * @package Verena_Jewellery_Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'VERENA_JT_VALAS_SHEET_CSV_URL', '' );

/**
 * Parse the published CSV into a currency-code-keyed array of IDR rates.
 * Matched by header text ("Mata Uang" / "Kurs") rather than fixed column
 * position, same defensive approach as the other sheet parsers, so
 * reordering columns in the sheet doesn't silently break the site.
 *
 * @param string $csv_body Raw CSV response body.
 * @return array<string, float|null>
 */
function verena_jt_parse_valas_csv( $csv_body ) {
	$stream = fopen( 'php://temp', 'r+' );
	fwrite( $stream, $csv_body );
	rewind( $stream );

	$rows = array();
	while ( ( $row = fgetcsv( $stream ) ) !== false ) {
		$rows[] = $row;
	}
	fclose( $stream );

	$col_currency = null;
	$col_rate     = null;
	$header_index = null;

	foreach ( $rows as $i => $row ) {
		foreach ( $row as $c => $cell ) {
			$norm = strtolower( trim( (string) $cell ) );
			if ( false !== strpos( $norm, 'mata uang' ) || false !== strpos( $norm, 'currency' ) ) {
				$col_currency = $c;
				$header_index = $i;
			} elseif ( false !== strpos( $norm, 'kurs' ) || false !== strpos( $norm, 'rate' ) || false !== strpos( $norm, 'harga' ) ) {
				$col_rate     = $c;
				$header_index = $i;
			}
		}
		if ( null !== $col_currency && null !== $col_rate ) {
			break;
		}
	}

	$rates = array();
	if ( null === $header_index ) {
		return $rates;
	}

	for ( $i = $header_index + 1; $i < count( $rows ); $i++ ) {
		$row  = $rows[ $i ];
		$code = isset( $row[ $col_currency ] ) ? strtoupper( trim( $row[ $col_currency ] ) ) : '';
		if ( '' === $code ) {
			break; // End of the table.
		}
		$rates[ $code ] = verena_jt_bullion_parse_price( $row[ $col_rate ] ?? '' );
	}

	return $rates;
}

/**
 * Fetch the published sheet and cache the parsed result. No-ops (returns
 * false without making a request) while VERENA_JT_VALAS_SHEET_CSV_URL is
 * still empty. Runs every 5 minutes via WP-Cron once a real URL is set.
 *
 * `changed_at` is tracked per currency code, same approach as the bullion
 * and buyback-karat syncs: each sync compares the newly parsed rate against
 * what was cached before, and only bumps that currency's `changed_at` when
 * its own rate actually differs.
 *
 * @return bool
 */
function verena_jt_sync_valas_sheet() {
	if ( '' === VERENA_JT_VALAS_SHEET_CSV_URL ) {
		return false;
	}

	$response = wp_remote_get( VERENA_JT_VALAS_SHEET_CSV_URL, array( 'timeout' => 15 ) );

	if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		return false;
	}

	$old   = get_option( 'verena_valas_cache', false );
	$rates = verena_jt_parse_valas_csv( wp_remote_retrieve_body( $response ) );
	$now   = time();

	$changed_at = array();
	foreach ( $rates as $code => $rate ) {
		$old_rate            = $old && isset( $old['rates'][ $code ] ) ? $old['rates'][ $code ] : null;
		$old_changed_at      = $old && isset( $old['changed_at'][ $code ] ) ? $old['changed_at'][ $code ] : null;
		$changed_at[ $code ] = ( $old_rate === $rate && $old_changed_at ) ? $old_changed_at : $now;
	}

	update_option(
		'verena_valas_cache',
		array(
			'rates'      => $rates,
			'changed_at' => $changed_at,
			'fetched_at' => $now,
		),
		false
	);
	return true;
}
add_action( 'verena_jt_sync_valas_sheet_event', 'verena_jt_sync_valas_sheet' );

/**
 * Public accessor for the theme: currency code => IDR rate, a per-currency
 * `changed_at`, and `fetched_at`. Returns all-empty (never fetches) while
 * VERENA_JT_VALAS_SHEET_CSV_URL is still a placeholder — see the file
 * docblock for what to do once the real Valas sheet exists.
 *
 * @return array{rates: array<string, float|null>, changed_at: array<string, int|null>, fetched_at: int|null}
 */
function verena_get_valas_data() {
	if ( '' === VERENA_JT_VALAS_SHEET_CSV_URL ) {
		return array(
			'rates'      => array(),
			'changed_at' => array(),
			'fetched_at' => null,
		);
	}

	$data = get_option( 'verena_valas_cache', false );
	if ( false === $data ) {
		verena_jt_sync_valas_sheet();
		$data = get_option( 'verena_valas_cache', false );
	}
	if ( ! $data ) {
		return array(
			'rates'      => array(),
			'changed_at' => array(),
			'fetched_at' => null,
		);
	}
	return $data;
}

/**
 * Schedule the sync every 5 minutes, reusing the interval the bullion sync
 * registers (verena_five_minutes). No-ops while the sheet URL is empty —
 * wp_schedule_event() itself is harmless to call, the event handler is what
 * short-circuits.
 */
function verena_jt_valas_schedule_sync() {
	if ( ! wp_next_scheduled( 'verena_jt_sync_valas_sheet_event' ) ) {
		wp_schedule_event( time(), 'verena_five_minutes', 'verena_jt_sync_valas_sheet_event' );
	}
}
add_action( 'init', 'verena_jt_valas_schedule_sync' );

/**
 * Unschedule the sync on plugin deactivation.
 */
function verena_jt_valas_unschedule_sync() {
	$timestamp = wp_next_scheduled( 'verena_jt_sync_valas_sheet_event' );
	if ( $timestamp ) {
		wp_unschedule_event( $timestamp, 'verena_jt_sync_valas_sheet_event' );
	}
}
