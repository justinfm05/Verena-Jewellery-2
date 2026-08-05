<?php
/**
 * Pulls the "Jual Emas (Kadar)" tab of the shop's shared Google Sheet
 * (published to the web as CSV, same spreadsheet as the bullion sheet) on
 * the same 5-minute schedule, caching a per-karat buyback price-per-gram
 * range (lower/upper) for the Jual Emas page's Perhiasan calculator.
 *
 * This is a direct per-karat lookup, not a flat-rate x purity-fraction
 * calculation — unlike Logam Mulia items on that same page, which still
 * price off verena_get_current_buyback_rate() (see pricing.php).
 *
 * @package Verena_Jewellery_Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'VERENA_JT_BUYBACK_KARAT_SHEET_CSV_URL', 'https://docs.google.com/spreadsheets/d/e/2PACX-1vQkylBZX8yOCLPo_ANxpPIaoPSel637DzwdwdKBUqIuuuhe2rpOm7Fh79jeSzNgThCHE6upPQzSDfbW/pub?gid=1316715461&single=true&output=csv' );

/**
 * Parse the published CSV into a karat-label-keyed array of
 * {lower, upper} price-per-gram. Matched by header text ("Kadar Emas" /
 * "Lower Range" / "Upper Range") rather than fixed column position, same
 * defensive approach as the bullion sheet parser, so reordering columns in
 * the sheet doesn't silently break the site.
 *
 * @param string $csv_body Raw CSV response body.
 * @return array<string, array{lower: float|null, upper: float|null}>
 */
function verena_jt_parse_buyback_karat_csv( $csv_body ) {
	$stream = fopen( 'php://temp', 'r+' );
	fwrite( $stream, $csv_body );
	rewind( $stream );

	$rows = array();
	while ( ( $row = fgetcsv( $stream ) ) !== false ) {
		$rows[] = $row;
	}
	fclose( $stream );

	$col_kadar    = null;
	$col_lower    = null;
	$col_upper    = null;
	$header_index = null;

	foreach ( $rows as $i => $row ) {
		foreach ( $row as $c => $cell ) {
			$norm = strtolower( trim( (string) $cell ) );
			if ( false !== strpos( $norm, 'kadar' ) ) {
				$col_kadar    = $c;
				$header_index = $i;
			} elseif ( false !== strpos( $norm, 'lower' ) ) {
				$col_lower = $c;
			} elseif ( false !== strpos( $norm, 'upper' ) ) {
				$col_upper = $c;
			}
		}
		if ( null !== $col_kadar && null !== $col_lower && null !== $col_upper ) {
			break;
		}
	}

	$karats = array();
	if ( null === $header_index ) {
		return $karats;
	}

	for ( $i = $header_index + 1; $i < count( $rows ); $i++ ) {
		$row   = $rows[ $i ];
		$label = isset( $row[ $col_kadar ] ) ? trim( $row[ $col_kadar ] ) : '';
		if ( '' === $label ) {
			break; // End of the table.
		}
		$karats[ $label ] = array(
			'lower' => verena_jt_bullion_parse_price( $row[ $col_lower ] ?? '' ),
			'upper' => verena_jt_bullion_parse_price( $row[ $col_upper ] ?? '' ),
		);
	}

	return $karats;
}

/**
 * Fetch the published sheet and cache the parsed result. Runs every 5
 * minutes via WP-Cron (same interval the bullion sync registers); also
 * called on-demand the first time the cache is empty.
 *
 * @return bool
 */
function verena_jt_sync_buyback_karat_sheet() {
	$response = wp_remote_get( VERENA_JT_BUYBACK_KARAT_SHEET_CSV_URL, array( 'timeout' => 15 ) );

	if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		return false;
	}

	$karats = verena_jt_parse_buyback_karat_csv( wp_remote_retrieve_body( $response ) );

	update_option(
		'verena_buyback_karat_cache',
		array(
			'karats'     => $karats,
			'fetched_at' => time(),
		),
		false
	);
	return true;
}
add_action( 'verena_jt_sync_buyback_karat_sheet_event', 'verena_jt_sync_buyback_karat_sheet' );

/**
 * Public accessor for the theme: karat label => {lower, upper} price per
 * gram, plus fetched_at. Falls back to an immediate fetch if the cache is
 * empty (e.g. right after activation, before the first scheduled run has
 * fired).
 *
 * @return array{karats: array<string, array{lower: float|null, upper: float|null}>, fetched_at: int|null}
 */
function verena_get_buyback_karat_data() {
	$data = get_option( 'verena_buyback_karat_cache', false );
	if ( false === $data ) {
		verena_jt_sync_buyback_karat_sheet();
		$data = get_option( 'verena_buyback_karat_cache', false );
	}
	return $data ? $data : array(
		'karats'     => array(),
		'fetched_at' => null,
	);
}

/**
 * Schedule the sync every 5 minutes, reusing the interval the bullion sync
 * registers (verena_five_minutes).
 */
function verena_jt_buyback_karat_schedule_sync() {
	if ( ! wp_next_scheduled( 'verena_jt_sync_buyback_karat_sheet_event' ) ) {
		wp_schedule_event( time(), 'verena_five_minutes', 'verena_jt_sync_buyback_karat_sheet_event' );
	}
}
add_action( 'init', 'verena_jt_buyback_karat_schedule_sync' );

/**
 * Unschedule the sync on plugin deactivation.
 */
function verena_jt_buyback_karat_unschedule_sync() {
	$timestamp = wp_next_scheduled( 'verena_jt_sync_buyback_karat_sheet_event' );
	if ( $timestamp ) {
		wp_unschedule_event( $timestamp, 'verena_jt_sync_buyback_karat_sheet_event' );
	}
}
