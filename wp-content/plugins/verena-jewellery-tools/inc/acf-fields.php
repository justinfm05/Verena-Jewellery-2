<?php
/**
 * ACF field group definitions for verena_product and verena_bullion.
 *
 * Registered in code (not the ACF UI) so the field structure ships with the
 * plugin and doesn't depend on an export/import step during deploy.
 *
 * Deliberately stays on ACF's free tier: no Repeater/Gallery/Options-Page
 * fields (those are ACF PRO only). Extra product photos beyond the featured
 * image are added as a native Gutenberg Gallery block in the post content
 * instead of an ACF gallery field.
 *
 * @package Verena_Jewellery_Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function verena_jt_register_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	// ------------------------------------------------------------------
	// verena_product
	// ------------------------------------------------------------------
	$status_choices = array();
	foreach ( verena_jt_product_statuses() as $value => $label ) {
		$status_choices[ $value ] = $label;
	}

	// Purity choices are read from the same admin-editable list (Verena
	// Jewellery > Purity Options) the buyback estimator and gold calculator
	// use — one canonical set of karats for the whole site, not a second
	// hardcoded list that could drift out of sync.
	$purity_choices = array();
	foreach ( verena_get_purity_options() as $option ) {
		if ( 'Tidak yakin / belum dicek' === $option['label'] ) {
			continue; // That catch-all is only meaningful for customer-declared buyback items, not the shop's own stock.
		}
		$purity_choices[ $option['label'] ] = $option['label'];
	}

	$gold_color_choices = array(
		'yellow' => 'Yellow Gold',
		'white'  => 'White Gold',
		'rose'   => 'Rose Gold',
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_verena_product',
			'title'    => 'Piece Details',
			'fields'   => array(
				array(
					'key'           => 'field_verena_sku',
					'label'         => 'SKU',
					'name'          => 'sku',
					'type'          => 'text',
					'required'      => 1,
					'instructions'  => 'Unique identifier for this one-off piece, e.g. VR-0042.',
				),
				array(
					'key'           => 'field_verena_weight_grams',
					'label'         => 'Weight (grams)',
					'name'          => 'weight_grams',
					'type'          => 'number',
					'required'      => 1,
					'step'          => '0.001',
					'min'           => 0,
					'instructions'  => 'Used with today\'s gold rate to compute the sell price automatically.',
				),
				array(
					'key'          => 'field_verena_purity_label',
					'label'        => 'Karat / Purity',
					'name'         => 'purity_label',
					'type'         => 'select',
					'choices'      => $purity_choices,
					'default_value' => '17K',
					'required'     => 1,
					'ui'           => 1,
					'instructions' => 'Sets which karat\'s rate (from Verena Jewellery > Settings) this piece is priced against. Manage the karat list under Purity Options.',
				),
				array(
					'key'           => 'field_verena_gold_color',
					'label'         => 'Gold Color',
					'name'          => 'gold_color',
					'type'          => 'select',
					'choices'       => $gold_color_choices,
					'default_value' => 'yellow',
					'required'      => 1,
					'ui'            => 1,
				),
				array(
					'key'          => 'field_verena_making_charge',
					'label'        => 'Making Charge (IDR)',
					'name'         => 'making_charge',
					'type'         => 'number',
					'required'     => 1,
					'min'          => 0,
					'instructions' => 'Craftsmanship charge added on top of the gold value.',
				),
				array(
					'key'           => 'field_verena_status',
					'label'         => 'Status',
					'name'          => 'status',
					'type'          => 'select',
					'choices'       => $status_choices,
					'default_value' => 'available',
					'required'      => 1,
					'ui'            => 1,
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'verena_product',
					),
				),
			),
		)
	);

	// ------------------------------------------------------------------
	// verena_bullion
	// ------------------------------------------------------------------
	acf_add_local_field_group(
		array(
			'key'      => 'group_verena_bullion',
			'title'    => 'Bullion Details',
			'fields'   => array(
				array(
					'key'          => 'field_verena_denomination_grams',
					'label'        => 'Denomination (grams)',
					'name'         => 'denomination_grams',
					'type'         => 'number',
					'required'     => 1,
					'step'         => '0.001',
					'min'          => 0,
				),
				array(
					'key'          => 'field_verena_series',
					'label'        => 'Series',
					'name'         => 'series',
					'type'         => 'text',
					'instructions' => 'Optional, e.g. Emasku "Gold" / "Prime". Leave blank if not applicable.',
				),
				array(
					'key'          => 'field_verena_year',
					'label'        => 'Mint Year',
					'name'         => 'year',
					'type'         => 'number',
					'instructions' => 'Antam bars are priced by mint year. Leave blank for brands that aren\'t year-specific.',
				),
				array(
					'key'      => 'field_verena_sell_price',
					'label'    => 'Sell Price (IDR)',
					'name'     => 'sell_price',
					'type'     => 'number',
					'required' => 1,
					'min'      => 0,
				),
				array(
					'key'      => 'field_verena_buyback_price',
					'label'    => 'Buyback Price (IDR)',
					'name'     => 'buyback_price',
					'type'     => 'number',
					'required' => 1,
					'min'      => 0,
				),
				array(
					'key'           => 'field_verena_in_stock',
					'label'         => 'In Stock',
					'name'          => 'in_stock',
					'type'          => 'true_false',
					'default_value' => 1,
					'ui'            => 1,
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'verena_bullion',
					),
				),
			),
		)
	);
}
add_action( 'acf/init', 'verena_jt_register_acf_fields' );
