<?php
/**
 * Core pricing engine. Prices are never stored on a product — everything
 * here reads the latest rate row and computes on demand, so a gold rate
 * update instantly reflects across every product at that karat, the
 * buyback estimator, and the net-worth calculator. The shop stocks 16K-24K
 * in yellow/white/rose gold (per its actual catalog), so rates are tracked
 * per karat, not as one shop-wide number.
 *
 * @package Verena_Jewellery_Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Latest gold sell rate for a given purity (IDR per gram), or null if no
 * rate has ever been set for that purity. Each karat the shop stocks (16K
 * through 24K, per the shop's actual range) gets its own independent rate
 * and history — a fashion piece's price always uses ITS OWN karat's rate,
 * never a single shop-wide number.
 *
 * @param string $purity_label One of the labels from verena_get_purity_options(), e.g. "17K".
 * @return array{sell_price_per_gram:int, purity_label:string, created_at:string}|null
 */
function verena_get_current_gold_rate( $purity_label = '17K' ) {
	global $wpdb;
	$table = $wpdb->prefix . 'verena_gold_rate_log';

	$row = $wpdb->get_row(
		$wpdb->prepare( "SELECT * FROM {$table} WHERE purity_label = %s ORDER BY created_at DESC, id DESC LIMIT 1", $purity_label ),
		ARRAY_A
	);

	return $row ? $row : null;
}

/**
 * The current rate for every purity that has ever had one set, keyed by
 * purity label. Used by the admin settings screen to show/edit all rates
 * at once.
 *
 * @return array<string, array{sell_price_per_gram:int, purity_label:string, created_at:string}>
 */
function verena_get_all_current_gold_rates() {
	global $wpdb;
	$table = $wpdb->prefix . 'verena_gold_rate_log';

	$rows = $wpdb->get_results(
		"SELECT t1.* FROM {$table} t1
		 INNER JOIN (
		   SELECT purity_label, MAX(created_at) AS max_created_at
		   FROM {$table}
		   GROUP BY purity_label
		 ) t2 ON t1.purity_label = t2.purity_label AND t1.created_at = t2.max_created_at",
		ARRAY_A
	);

	$by_purity = array();
	foreach ( (array) $rows as $row ) {
		$by_purity[ $row['purity_label'] ] = $row;
	}
	return $by_purity;
}

/**
 * Latest buyback rate (IDR per gram of fine/24K-equivalent gold), or null.
 *
 * @return array{price_per_gram:int, created_at:string}|null
 */
function verena_get_current_buyback_rate() {
	global $wpdb;
	$table = $wpdb->prefix . 'verena_buyback_rate_log';

	$row = $wpdb->get_row( "SELECT * FROM {$table} ORDER BY created_at DESC, id DESC LIMIT 1", ARRAY_A );

	return $row ? $row : null;
}

/**
 * Record a new gold rate. Appends rather than overwrites, so the log
 * table also serves as a free rate-history audit trail.
 *
 * @param int $sell_price_per_gram
 * @param string $purity_label
 * @return void
 */
function verena_record_gold_rate( $sell_price_per_gram, $purity_label = '17K' ) {
	global $wpdb;
	$wpdb->insert(
		$wpdb->prefix . 'verena_gold_rate_log',
		array(
			'purity_label'        => $purity_label,
			'sell_price_per_gram' => max( 0, (int) $sell_price_per_gram ),
			'created_at'          => current_time( 'mysql' ),
			'created_by'          => get_current_user_id(),
		),
		array( '%s', '%d', '%s', '%d' )
	);
}

/**
 * Record a new buyback rate.
 *
 * @param int $price_per_gram
 * @return void
 */
function verena_record_buyback_rate( $price_per_gram ) {
	global $wpdb;
	$wpdb->insert(
		$wpdb->prefix . 'verena_buyback_rate_log',
		array(
			'price_per_gram' => max( 0, (int) $price_per_gram ),
			'created_at'     => current_time( 'mysql' ),
			'created_by'     => get_current_user_id(),
		),
		array( '%d', '%s', '%d' )
	);
}

/**
 * Compute the sell price for a fashion piece: weight x today's gold rate
 * FOR THAT PIECE'S OWN KARAT, plus its making charge.
 *
 * @param float  $weight_grams
 * @param int    $making_charge
 * @param string $purity_label The piece's own karat, e.g. "17K", "22K".
 * @return int|null Whole-Rupiah price, or null if no rate has been set for that karat yet.
 */
function verena_compute_product_price( $weight_grams, $making_charge, $purity_label = '17K' ) {
	$rate = verena_get_current_gold_rate( $purity_label );
	if ( ! $rate ) {
		return null;
	}

	$raw = ( (float) $weight_grams * (int) $rate['sell_price_per_gram'] ) + (int) $making_charge;
	return verena_round_idr( $raw );
}

/**
 * All karat -> purity-fraction options, sorted for display.
 *
 * @return array<int, array{label:string, fraction_bps:int, sort_order:int}>
 */
function verena_get_purity_options() {
	$options = get_option( 'verena_purity_options', array() );
	usort(
		$options,
		function ( $a, $b ) {
			return $a['sort_order'] <=> $b['sort_order'];
		}
	);
	return $options;
}

/**
 * Look up a single purity option by its label.
 *
 * @param string $label
 * @return array{label:string, fraction_bps:int, sort_order:int}|null
 */
function verena_get_purity_option( $label ) {
	foreach ( verena_get_purity_options() as $option ) {
		if ( $option['label'] === $label ) {
			return $option;
		}
	}
	return null;
}

/**
 * Purity fraction (in basis points) for ANY karat label. Prefers a purity the
 * shop has explicitly set in wp-admin (so it keeps control of the karats it
 * cares about); otherwise derives it straight from the karat number, since
 * "NK" gold is N/24 pure. This lets the public calculator offer the full
 * 1K–24K range without needing 24 admin rows. Returns null for labels that are
 * neither a stored option nor an "NK" value.
 *
 * @param string $label e.g. "22K", "9K", or a stored label like "Tidak yakin / belum dicek".
 * @return int|null Basis points (7500 = 75.00%), or null if unrecognised.
 */
function verena_purity_fraction_bps( $label ) {
	$option = verena_get_purity_option( $label );
	if ( $option ) {
		return (int) $option['fraction_bps'];
	}
	if ( preg_match( '/^\s*(\d{1,2})\s*K\s*$/i', (string) $label, $m ) ) {
		$k = (int) $m[1];
		if ( $k >= 1 && $k <= 24 ) {
			return (int) round( $k / 24 * 10000 );
		}
	}
	return null;
}

/**
 * Compute an indicative buyback estimate for a single item: weight x
 * purity fraction x today's buyback rate. Always an estimate — final price
 * requires in-person testing, and callers must present it as such.
 *
 * @param float  $weight_grams
 * @param string $purity_label Any karat label ("1K".."24K") or stored purity label.
 * @return int|null Whole-Rupiah estimate, or null if inputs/rate are missing.
 */
function verena_compute_buyback_estimate( $weight_grams, $purity_label ) {
	$rate = verena_get_current_buyback_rate();
	$bps  = verena_purity_fraction_bps( $purity_label );

	if ( ! $rate || null === $bps || $weight_grams <= 0 ) {
		return null;
	}

	$fraction = $bps / 10000;
	$raw = (float) $weight_grams * $fraction * (int) $rate['price_per_gram'];

	return verena_round_idr( $raw );
}

/**
 * Standard bilingual-friendly disclaimer shown next to every buyback /
 * net-worth-calculator estimate and echoed into WhatsApp messages, so it
 * survives even if a message gets screenshotted or forwarded.
 *
 * @return string
 */
function verena_estimate_disclaimer() {
	return 'Estimasi ini hanya perkiraan awal berdasarkan berat dan kadar yang Anda masukkan. Harga final ditentukan setelah pengecekan dan pengujian kadar emas secara langsung di toko kami.';
}
