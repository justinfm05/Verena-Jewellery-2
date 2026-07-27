<?php
/**
 * WhatsApp deep-link builder and message templates. Every "buy / inquire /
 * sell" action on the site ends here — there is deliberately no on-site
 * cart or checkout anywhere in this plugin.
 *
 * @package Verena_Jewellery_Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The shop's WhatsApp number in wa.me format (digits only, country code,
 * no leading +/00). Falls back to the number confirmed at build time if the
 * settings page value hasn't been filled in yet.
 *
 * @return string
 */
function verena_whatsapp_number() {
	$number = get_option( 'verena_whatsapp_number', '628111099399' );
	return preg_replace( '/\D+/', '', $number );
}

/**
 * Build a wa.me deep link with a prefilled, URL-encoded message.
 *
 * @param string $message Plain-text message. Keep it short — see note below.
 * @return string
 */
function verena_build_wa_link( $message ) {
	return sprintf(
		'https://wa.me/%s?text=%s',
		verena_whatsapp_number(),
		rawurlencode( $message )
	);
}

/**
 * Message for inquiring about a specific fashion piece.
 *
 * @param string $name
 * @param string $sku
 * @param string $url
 * @return string
 */
function verena_wa_message_product_inquiry( $name, $sku, $url ) {
	return "Halo Verena Jewellery, saya tertarik dengan piece \"{$name}\" (SKU: {$sku}).\n{$url}";
}

/**
 * Message for a custom-order request.
 *
 * @return string
 */
function verena_wa_message_custom_order() {
	return 'Halo Verena Jewellery, saya ingin konsultasi untuk pesanan custom jewellery.';
}

/**
 * Message for a repair request. Explicitly tells the customer to attach
 * photos once the chat opens, since wa.me links can't carry attachments.
 *
 * @return string
 */
function verena_wa_message_repair() {
	return 'Halo Verena Jewellery, saya ingin tanya soal servis/perbaikan jewellery. (Foto akan saya kirimkan di chat ini.)';
}

/**
 * Message for a single-item buyback inquiry, including the disclaimer.
 *
 * @param float  $weight_grams
 * @param string $purity_label
 * @param int    $estimate
 * @return string
 */
function verena_wa_message_buyback( $weight_grams, $purity_label, $estimate ) {
	$weight_str = verena_format_grams( $weight_grams );
	$estimate_str = verena_format_idr( $estimate );
	$disclaimer = verena_estimate_disclaimer();

	return "Halo Verena Jewellery, saya ingin jual emas bekas.\nBerat: {$weight_str}\nKadar: {$purity_label}\nEstimasi awal: {$estimate_str}\n\n{$disclaimer}";
}

/**
 * Message for a bullion inquiry (buy or sell direction).
 *
 * @param string $brand
 * @param string $denomination
 * @param string $direction "beli" (buy) or "jual" (sell/buyback)
 * @return string
 */
function verena_wa_message_bullion( $brand, $denomination, $direction = 'beli' ) {
	return "Halo Verena Jewellery, saya ingin {$direction} emas batangan {$brand} {$denomination}.";
}

/**
 * Message for "sell everything" from the gold net-worth calculator: an
 * itemized breakdown plus total. Long lists are truncated to keep the
 * wa.me URL from becoming unwieldy — the full list still lives at the
 * shareable calculator link, which is included instead.
 *
 * @param array  $items List of ['description'=>string,'weight_grams'=>float,'purity_label'=>string,'estimated_value'=>int].
 * @param int    $total
 * @param string $shareable_url
 * @return string
 */
function verena_wa_message_calculator_sell( $items, $total, $shareable_url = '' ) {
	$lines = array( 'Halo Verena Jewellery, saya ingin jual emas berikut:' );

	$max_lines = 8;
	$count = 0;
	foreach ( $items as $item ) {
		if ( $count >= $max_lines ) {
			$remaining = count( $items ) - $max_lines;
			$lines[] = "...dan {$remaining} item lainnya.";
			break;
		}
		$desc = $item['description'] !== '' ? $item['description'] : 'Item';
		$weight_str = verena_format_grams( $item['weight_grams'] );
		$lines[] = "- {$desc}: {$weight_str}, kadar {$item['purity_label']} (~" . verena_format_idr( $item['estimated_value'] ) . ')';
		$count++;
	}

	$lines[] = '';
	$lines[] = 'Estimasi total: ' . verena_format_idr( $total );
	if ( $shareable_url ) {
		$lines[] = "Daftar lengkap: {$shareable_url}";
	}
	$lines[] = '';
	$lines[] = verena_estimate_disclaimer();

	return implode( "\n", $lines );
}
