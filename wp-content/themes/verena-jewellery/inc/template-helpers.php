<?php
/**
 * Small reusable render helpers shared across page templates.
 *
 * @package Verena_Jewellery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A styled <a> that opens WhatsApp with a prefilled message. The single
 * place every "buy / inquire / sell" action in the theme routes through —
 * there is no cart or checkout anywhere in this site.
 *
 * @param string $message
 * @param string $label
 * @param string $classes Extra CSS classes.
 */
function verena_whatsapp_button( $message, $label = 'Chat di WhatsApp', $classes = '' ) {
	printf(
		'<a class="btn btn-whatsapp %1$s" href="%2$s" target="_blank" rel="noopener">%3$s</a>',
		esc_attr( $classes ),
		esc_url( verena_build_wa_link( $message ) ),
		esc_html( $label )
	);
}

/**
 * Small line-icon set used for service cards and section headers. Kept as
 * inline SVG (not an icon font/library) so there's no extra asset to load —
 * consistent 1.5px stroke, 24x24 viewBox, matches the brand's minimal line.
 *
 * @param string $name ring|gem|repair|bar|coin|calculator
 * @param string $classes Extra CSS classes for the wrapping <span>.
 */
function verena_icon( $name, $classes = '' ) {
	$icons = array(
		'ring'       => '<circle cx="12" cy="15" r="6"/><path d="M9 9l3-6 3 6"/>',
		'gem'        => '<path d="M6 3h12l3 5-9 13L3 8z"/><path d="M3 8h18M9 3l3 5 3-5M9 8l3 13 3-13"/>',
		'repair'     => '<path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-2.5 2.5-2-2 2.5-2.5z"/>',
		'bar'        => '<path d="M4 17l2-9h12l2 9z"/><path d="M4 17h16"/>',
		'coin'       => '<circle cx="12" cy="12" r="9"/><path d="M9.5 9.5c0-1 1-1.8 2.5-1.8s2.5.8 2.5 1.6c0 2.2-5 1.4-5 3.6 0 .9 1.1 1.6 2.5 1.6s2.5-.7 2.5-1.6"/><path d="M12 6.5v11"/>',
		'calculator' => '<rect x="5" y="3" width="14" height="18" rx="1.5"/><path d="M8 7h8M8 12h.01M12 12h.01M16 12h.01M8 16h.01M12 16h.01M16 16h.01"/>',
	);

	if ( ! isset( $icons[ $name ] ) ) {
		return;
	}

	printf(
		'<span class="icon-badge %1$s"><svg viewBox="0 0 24 24">%2$s</svg></span>',
		esc_attr( $classes ),
		$icons[ $name ] // phpcs:ignore -- fixed, trusted inline SVG markup, not user input.
	);
}

/**
 * Human label for a gold_color ACF value (yellow|white|rose).
 *
 * @param string $value
 * @return string
 */
function verena_gold_color_label( $value ) {
	$labels = array(
		'yellow' => 'Yellow Gold',
		'white'  => 'White Gold',
		'rose'   => 'Rose Gold',
	);
	return $labels[ $value ] ?? ucfirst( (string) $value );
}

/**
 * Status badge for a fashion piece (Available / Reserved / Sold).
 *
 * @param string $status
 */
function verena_status_badge( $status ) {
	$statuses = verena_jt_product_statuses();
	$label = $statuses[ $status ] ?? 'Available';
	printf( '<span class="status-badge status-%s">%s</span>', esc_attr( $status ), esc_html( $label ) );
}

/**
 * Fetch all verena_bullion posts for a given brand slug (antam/ubs/emasku),
 * ordered by denomination.
 *
 * @param string $brand_slug
 * @return WP_Post[]
 */
function verena_get_bullion_by_brand( $brand_slug ) {
	$query = new WP_Query(
		array(
			'post_type'      => 'verena_bullion',
			'posts_per_page' => -1,
			'orderby'        => 'meta_value_num',
			'meta_key'       => 'denomination_grams',
			'order'          => 'ASC',
			'tax_query'      => array(
				array(
					'taxonomy' => 'verena_bullion_brand',
					'field'    => 'slug',
					'terms'    => $brand_slug,
				),
			),
		)
	);
	return $query->posts;
}

/**
 * Human label for a bullion listing, e.g. "1 gr" or "Antam 2024, 5 gr".
 *
 * @param int $post_id
 * @return string
 */
function verena_bullion_label( $post_id ) {
	$denomination = get_field( 'denomination_grams', $post_id );
	$year = get_field( 'year', $post_id );
	$series = get_field( 'series', $post_id );

	$label = verena_format_grams( $denomination );
	if ( $series ) {
		$label = $series . ' ' . $label;
	}
	if ( $year ) {
		$label .= ' (' . $year . ')';
	}
	return $label;
}

/**
 * Fashion product catalog query, optionally filtered by category slug.
 *
 * @param string|null $category_slug
 * @return WP_Query
 */
function verena_get_fashion_query( $category_slug = null ) {
	$args = array(
		'post_type'      => 'verena_product',
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'DESC',
	);

	if ( $category_slug ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'verena_category',
				'field'    => 'slug',
				'terms'    => $category_slug,
			),
		);
	}

	return new WP_Query( $args );
}
