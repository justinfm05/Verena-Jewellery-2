<?php
/**
 * IDR currency and gram-weight formatting helpers.
 *
 * @package Verena_Jewellery_Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Format a whole-Rupiah integer as "Rp1.234.000" (period thousands
 * separator, no decimals — standard id-ID convention). Amounts are always
 * handled as integers throughout this plugin to avoid floating-point
 * rounding bugs; this function only ever formats, never computes.
 *
 * @param int $amount Whole Rupiah amount.
 * @return string
 */
function verena_format_idr( $amount ) {
	$amount = (int) round( $amount );

	if ( class_exists( 'NumberFormatter' ) ) {
		$formatter = new NumberFormatter( 'id_ID', NumberFormatter::CURRENCY );
		return $formatter->formatCurrency( $amount, 'IDR' );
	}

	// Fallback if the intl extension isn't available on the host.
	return 'Rp' . number_format( $amount, 0, ',', '.' );
}

/**
 * Format a gram weight using id-ID decimal/thousands conventions, trimming
 * trailing zeroes (e.g. 5 not 5.000, but 2.5 stays 2.5).
 *
 * @param float $grams
 * @return string
 */
function verena_format_grams( $grams ) {
	$grams = (float) $grams;
	$formatted = rtrim( rtrim( number_format( $grams, 3, ',', '.' ), '0' ), ',' );
	return $formatted . ' gr';
}

/**
 * Round a computed IDR price to the nearest configured increment.
 * Defaults to the nearest Rp100 — confirm with the shop owner if a
 * different rounding convention (e.g. nearest Rp1.000) is preferred and
 * adjust the 'verena_price_rounding' option accordingly.
 *
 * @param float $amount
 * @return int
 */
function verena_round_idr( $amount ) {
	$increment = (int) get_option( 'verena_price_rounding', 100 );
	if ( $increment < 1 ) {
		$increment = 1;
	}
	return (int) ( round( $amount / $increment ) * $increment );
}
