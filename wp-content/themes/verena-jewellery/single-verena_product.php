<?php
/**
 * Single fashion jewellery piece.
 *
 * @package Verena_Jewellery
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

while ( have_posts() ) :
	the_post();

	$post_id = get_the_ID();
	$sku = get_field( 'sku', $post_id );
	$weight = get_field( 'weight_grams', $post_id );
	$making_charge = get_field( 'making_charge', $post_id );
	$purity_label = get_field( 'purity_label', $post_id ) ?: '17K';
	$gold_color = get_field( 'gold_color', $post_id ) ?: 'yellow';
	$status = get_field( 'status', $post_id ) ?: 'available';
	$price = verena_compute_product_price( $weight, $making_charge, $purity_label );
	$is_available = 'available' === $status;
	?>
	<main class="container section">
		<div class="product-detail">
			<div class="product-detail__gallery">
				<?php if ( has_post_thumbnail() ) : ?>
					<?php the_post_thumbnail( 'large' ); ?>
				<?php endif; ?>
				<?php if ( has_block( 'gallery' ) || strpos( get_the_content(), 'wp-block-gallery' ) !== false ) : ?>
					<div class="mt-4"><?php the_content(); ?></div>
				<?php endif; ?>
			</div>

			<div class="product-detail__info">
				<div class="row" style="justify-content:space-between;align-items:flex-start;">
					<p class="eyebrow mb-0">SKU <?php echo esc_html( $sku ); ?></p>
					<?php verena_status_badge( $status ); ?>
				</div>
				<h1><?php the_title(); ?></h1>

				<p class="product-price-big">
					<?php echo $price ? esc_html( verena_format_idr( $price ) ) : 'Hubungi kami untuk harga'; ?>
				</p>

				<dl class="product-detail__meta">
					<div>
						<dt>Berat</dt>
						<dd><?php echo esc_html( verena_format_grams( $weight ) ); ?></dd>
					</div>
					<div>
						<dt>Kadar</dt>
						<dd><?php echo esc_html( $purity_label ); ?></dd>
					</div>
					<div>
						<dt>Warna Emas</dt>
						<dd><?php echo esc_html( verena_gold_color_label( $gold_color ) ); ?></dd>
					</div>
				</dl>

				<?php if ( ! empty( get_the_content() ) && strpos( get_the_content(), 'wp-block-gallery' ) === false ) : ?>
					<div class="mb-0"><?php the_content(); ?></div>
				<?php endif; ?>

				<div class="mt-4">
					<?php if ( $is_available ) : ?>
						<?php
						verena_whatsapp_button(
							verena_wa_message_product_inquiry( get_the_title(), $sku, get_permalink() ),
							'Tanya / Pesan via WhatsApp',
							'btn-block'
						);
						?>
					<?php else : ?>
						<p class="text-muted">Piece ini sudah tidak tersedia. Tertarik dengan desain serupa?</p>
						<?php verena_whatsapp_button( verena_wa_message_custom_order(), 'Tanya Piece Serupa', 'btn-block' ); ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</main>
	<?php
endwhile;

get_footer();
