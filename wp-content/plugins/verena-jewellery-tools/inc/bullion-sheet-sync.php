<?php
/**
 * Pulls live bullion prices from the shop's shared Google Sheet (published
 * to the web as CSV) on an hourly schedule, and caches the parsed result for
 * the Logam Mulia page to display. The sheet can't be reorganized without
 * breaking its own internal formulas, so rows/columns are matched by their
 * label text rather than fixed position wherever possible, so small edits to
 * the sheet don't silently break the site.
 *
 * @package Verena_Jewellery_Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'VERENA_JT_BULLION_SHEET_CSV_URL', 'https://docs.google.com/spreadsheets/d/e/2PACX-1vQkylBZX8yOCLPo_ANxpPIaoPSel637DzwdwdKBUqIuuuhe2rpOm7Fh79jeSzNgThCHE6upPQzSDfbW/pub?gid=1094756045&single=true&output=csv' );

/**
 * Collapse whitespace/dash variations so sheet labels can be matched
 * reliably (the sheet has inconsistent spacing, e.g. "ANTM  2021 - 2024").
 *
 * @param string $label
 * @return string
 */
function verena_jt_normalize_label( $label ) {
	$label = trim( (string) $label );
	$label = preg_replace( '/\s+/', ' ', $label );
	$label = str_replace( ' - ', '-', $label );
	return strtoupper( $label );
}

/**
 * Parse one price cell. The sheet is locked to plain numbers with "." as
 * the only decimal marker and no thousands-grouping punctuation at all
 * (e.g. "1417500" -> 1417500.0, never "1.417.500" or "1,417,500" — comma
 * is not a supported separator here in any position). "-"/"."/"" -> null.
 *
 * @param string $raw
 * @return float|null
 */
function verena_jt_bullion_parse_price( $raw ) {
	$raw = trim( (string) $raw );
	if ( '' === $raw || '-' === $raw || '.' === $raw ) {
		return null;
	}
	$number = (float) str_replace( ' ', '', $raw );
	return $number > 0 ? $number : null;
}

/**
 * Buyback total for one denomination: weight x the rate for whichever tier
 * applies to that specific brand/type row — >=50g uses the "100 GR dan
 * 50 GR" rate, <50g uses the "Per Gram Kecil" rate (both are per-gram).
 *
 * @param array<string, array{large: float|null, small: float|null}> $buyback
 * @param string $key  Normalized description row, e.g. "ANTM 2026".
 * @param float  $gram
 * @return int|null
 */
function verena_jt_bullion_buyback_amount( $buyback, $key, $gram ) {
	if ( empty( $buyback[ $key ] ) ) {
		return null;
	}
	$tier = ( $gram >= 50 ) ? 'large' : 'small';
	$rate = $buyback[ $key ][ $tier ];
	// Some types (e.g. UBS Retro, Antam Press Hijau/Retro Tidur) only have
	// one of the two rate columns filled in on the sheet — that means a
	// single flat rate for every gram size, not "no price below/above 50g".
	// Fall back to whichever tier does have a rate before giving up.
	if ( null === $rate ) {
		$rate = $buyback[ $key ][ 'large' === $tier ? 'small' : 'large' ];
	}
	return null === $rate ? null : verena_round_idr( $gram * $rate );
}

/**
 * Parse the published CSV into display-ready rows for the three Logam Mulia
 * tables, with buyback already computed per denomination.
 *
 * Antam rows carry 7 types — 2026, 2025, 2021-2024, Non RM <2020, Antam
 * Press Hijau, Antam Retro Tegak, Antam Retro Tidur — each as its own
 * {sell, buyback} pair. UBS rows additionally carry `buyback_retro` (the
 * "UBS RETRO + GAL24" rate — no sell price of its own, sheet has no
 * separate column for it). Only Antam 2026/Emasku/UBS are ever shown on the
 * public Logam Mulia table; the full type set (all 7 Antam types, UBS
 * Baru/Retro) is used by the Antam checkout page's Tipe picker and the Jual
 * Emas calculator's Logam Mulia Merek picker.
 *
 * @param string $csv_body Raw CSV response body.
 * @return array{antam: array, emasku: array, ubs: array}
 */
function verena_jt_bullion_parse_csv( $csv_body ) {
	$stream = fopen( 'php://temp', 'r+' );
	fwrite( $stream, $csv_body );
	rewind( $stream );

	$rows = array();
	while ( ( $row = fgetcsv( $stream ) ) !== false ) {
		$rows[] = $row;
	}
	fclose( $stream );

	// --- Buyback block: find it by its "HARGA TERIMA" marker anywhere in a row. ---
	$buyback               = array();
	$buyback_header_index = null;
	foreach ( $rows as $i => $row ) {
		foreach ( $row as $cell ) {
			if ( false !== stripos( (string) $cell, 'harga terima' ) ) {
				$buyback_header_index = $i;
				break 2;
			}
		}
	}
	if ( null !== $buyback_header_index ) {
		for ( $i = $buyback_header_index + 2; $i < count( $rows ); $i++ ) {
			$row  = $rows[ $i ];
			$desc = isset( $row[10] ) ? trim( $row[10] ) : '';
			if ( '' === $desc ) {
				continue;
			}
			$buyback[ verena_jt_normalize_label( $desc ) ] = array(
				'large' => verena_jt_bullion_parse_price( $row[8] ?? '' ),
				'small' => verena_jt_bullion_parse_price( $row[9] ?? '' ),
			);
		}
	}

	// --- Main sell-price grid: find it by its "Berat" header row. ---
	$antam  = array();
	$emasku = array();
	$ubs    = array();

	$grid_header_index = null;
	foreach ( $rows as $i => $row ) {
		if ( isset( $row[1] ) && false !== stripos( (string) $row[1], 'berat' ) ) {
			$grid_header_index = $i;
			break;
		}
	}

	if ( null !== $grid_header_index ) {
		// Antam columns, fixed positions confirmed directly against the sheet
		// (columns B-K: Berat, 2026, 2025, 2021-2024, Non RM <2020, Antam
		// Press Hijau, Antam Retro Tegak, Antam Retro Tidur, Emasku, UBS).
		// All 7 types have a matching "HARGA TERIMA" buyback rate.
		$antam_col = array(
			'2026'              => 2,
			'2025'              => 3,
			'2021-2024'         => 4,
			'Non RM <2020'      => 5,
			'Antam Press Hijau' => 6,
			'Antam Retro Tegak' => 7,
			'Antam Retro Tidur' => 8,
		);
		// Explicit map to each type's "HARGA TERIMA" description text, rather
		// than building it as 'ANTM ' . $type_label — the last three types
		// above already have "Antam" in their own label (needed standalone on
		// the checkout page's Tipe picker), so naive concatenation produces
		// "ANTM ANTAM RETRO TEGAK" instead of the sheet's actual "ANTM RETRO
		// TEGAK", silently breaking their buyback lookup.
		$antam_buyback_desc = array(
			'2026'              => 'ANTM 2026',
			'2025'              => 'ANTM 2025',
			'2021-2024'         => 'ANTM 2021-2024',
			'Non RM <2020'      => 'ANTM NON RM <2020',
			'Antam Press Hijau' => 'ANTM PRESS HIJAU',
			'Antam Retro Tegak' => 'ANTM RETRO TEGAK',
			'Antam Retro Tidur' => 'ANTM RETRO TIDUR',
		);

		for ( $i = $grid_header_index + 2; $i < count( $rows ); $i++ ) {
			$row  = $rows[ $i ];
			// Berat/gram is locked to plain numbers, "." as the only decimal
			// marker (e.g. "0.5" for half a gram, never "0,5") — same
			// convention as verena_jt_bullion_parse_price().
			$gram = isset( $row[1] ) ? trim( $row[1] ) : '';
			if ( '' === $gram || ! is_numeric( $gram ) ) {
				break; // End of the grid.
			}
			$gram = (float) $gram;

			$antam_sells  = array();
			$antam_has_any = false;
			foreach ( $antam_col as $type_label => $col_index ) {
				$sell = verena_jt_bullion_parse_price( $row[ $col_index ] ?? '' );
				if ( null !== $sell ) {
					$antam_has_any = true;
				}
				$antam_sells[ $type_label ] = array(
					'sell'    => $sell,
					'buyback' => ( null !== $sell )
						? verena_jt_bullion_buyback_amount( $buyback, verena_jt_normalize_label( $antam_buyback_desc[ $type_label ] ), $gram )
						: null,
				);
			}
			if ( $antam_has_any ) {
				// Union (+), not array_merge(): '2026'/'2025' are numeric-
				// looking string keys, which PHP silently casts to real int
				// keys — array_merge() would then renumber them (0, 1, ...)
				// instead of preserving '2026'/'2025', silently breaking the
				// Tipe lookup on the Antam checkout page.
				$antam[] = array( 'gram' => $gram ) + $antam_sells;
			}

			$emasku_sell = verena_jt_bullion_parse_price( $row[9] ?? '' );
			if ( null !== $emasku_sell ) {
				$emasku_key = ( $gram >= 250 ) ? 'EMASKU 250/500/1000' : 'EMASKU';
				$emasku[]   = array(
					'gram'    => $gram,
					'sell'    => $emasku_sell,
					'buyback' => verena_jt_bullion_buyback_amount( $buyback, $emasku_key, $gram ),
				);
			}

			$ubs_sell = verena_jt_bullion_parse_price( $row[10] ?? '' );
			if ( null !== $ubs_sell ) {
				$ubs[] = array(
					'gram'    => $gram,
					'sell'    => $ubs_sell,
					'buyback' => verena_jt_bullion_buyback_amount( $buyback, 'UBS BARU', $gram ),
					// UBS Retro has no sell price of its own on this sheet (no
					// separate column) — only used by the Jual Emas calculator's
					// Merek picker, which only ever needs a buyback rate.
					'buyback_retro' => verena_jt_bullion_buyback_amount( $buyback, 'UBS RETRO + GAL24', $gram ),
				);
			}
		}
	}

	return array(
		'antam'  => $antam,
		'emasku' => $emasku,
		'ubs'    => $ubs,
	);
}

/**
 * Fetch the published sheet and cache the parsed result. Runs every 5
 * minutes via WP-Cron; also called on-demand the first time the cache is
 * empty.
 *
 * `changed_at` is tracked per brand (antam/emasku/ubs) rather than one
 * shared timestamp: each sync compares the newly parsed rows against what
 * was cached before, and only bumps that brand's `changed_at` when its own
 * rows actually differ. Otherwise every 5-minute sync (which happens on
 * whichever page view triggers WP-Cron's due check) would make "Harga
 * terakhir diperbarui" look like it changed even when the price didn't.
 * Stores plain UTC timestamps (not current_time()'s site-offset timestamp)
 * so the theme can convert to Jakarta time explicitly and unambiguously,
 * regardless of the site's own configured WordPress timezone setting.
 *
 * @return bool
 */
function verena_jt_sync_bullion_sheet() {
	$response = wp_remote_get( VERENA_JT_BULLION_SHEET_CSV_URL, array( 'timeout' => 15 ) );

	if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		return false;
	}

	$old  = get_option( 'verena_bullion_sheet_cache', false );
	$data = verena_jt_bullion_parse_csv( wp_remote_retrieve_body( $response ) );
	$now  = time();

	$changed_at = array();
	foreach ( array( 'antam', 'emasku', 'ubs' ) as $key ) {
		$old_rows              = $old && isset( $old[ $key ] ) ? $old[ $key ] : null;
		$old_changed_at        = $old && isset( $old['changed_at'][ $key ] ) ? $old['changed_at'][ $key ] : null;
		$changed_at[ $key ]    = ( null !== $old_rows && $old_rows === $data[ $key ] && $old_changed_at )
			? $old_changed_at
			: $now;
	}
	$data['changed_at'] = $changed_at;
	$data['fetched_at'] = $now;

	update_option( 'verena_bullion_sheet_cache', $data, false );
	return true;
}
add_action( 'verena_jt_sync_bullion_sheet_event', 'verena_jt_sync_bullion_sheet' );

/**
 * Public accessor for the theme: the three tables' display-ready rows, a
 * per-brand `changed_at` (use this for "Harga terakhir diperbarui" — it only
 * moves when that brand's prices actually change), and `fetched_at` (the
 * last time the sheet was checked at all, changed or not). Falls back to an
 * immediate fetch if the cache is empty (e.g. right after activation, before
 * the first scheduled run has fired).
 *
 * @return array{antam: array, emasku: array, ubs: array, changed_at: array{antam: int|null, emasku: int|null, ubs: int|null}, fetched_at: int|null}
 */
function verena_get_bullion_sheet_data() {
	$data = get_option( 'verena_bullion_sheet_cache', false );
	if ( false === $data ) {
		verena_jt_sync_bullion_sheet();
		$data = get_option( 'verena_bullion_sheet_cache', false );
	}
	return $data ? $data : array(
		'antam'      => array(),
		'emasku'     => array(),
		'ubs'        => array(),
		'changed_at' => array(
			'antam'  => null,
			'emasku' => null,
			'ubs'    => null,
		),
		'fetched_at' => null,
	);
}

/**
 * Register a 5-minute WP-Cron interval — core only ships hourly/twicedaily/daily.
 *
 * @param array $schedules
 * @return array
 */
function verena_jt_add_five_minute_schedule( $schedules ) {
	$schedules['verena_five_minutes'] = array(
		'interval' => 5 * MINUTE_IN_SECONDS,
		'display'  => __( 'Every 5 minutes', 'verena-jewellery-tools' ),
	);
	return $schedules;
}
add_filter( 'cron_schedules', 'verena_jt_add_five_minute_schedule' );

/**
 * Schedule the sync every 5 minutes. Re-schedules automatically if an older
 * version of this plugin already scheduled it on a different interval (e.g.
 * the original hourly schedule), so existing installs pick up the change
 * without needing to be deactivated/reactivated.
 */
function verena_jt_bullion_schedule_sync() {
	$desired_schedule = 'verena_five_minutes';
	$current_schedule = wp_get_schedule( 'verena_jt_sync_bullion_sheet_event' );

	if ( $current_schedule && $current_schedule !== $desired_schedule ) {
		wp_unschedule_event( wp_next_scheduled( 'verena_jt_sync_bullion_sheet_event' ), 'verena_jt_sync_bullion_sheet_event' );
		$current_schedule = false;
	}

	if ( ! $current_schedule ) {
		wp_schedule_event( time(), $desired_schedule, 'verena_jt_sync_bullion_sheet_event' );
	}
}
add_action( 'init', 'verena_jt_bullion_schedule_sync' );

/**
 * Unschedule the sync on plugin deactivation.
 */
function verena_jt_bullion_unschedule_sync() {
	$timestamp = wp_next_scheduled( 'verena_jt_sync_bullion_sheet_event' );
	if ( $timestamp ) {
		wp_unschedule_event( $timestamp, 'verena_jt_sync_bullion_sheet_event' );
	}
}
