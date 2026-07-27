<?php
/**
 * Fashion piece card, used on the homepage and catalog grid.
 * Expects $args['post_id'].
 *
 * @package Verena_Jewellery
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_id       = $args['post_id'] ?? get_the_ID();
$weight        = get_field( 'weight_grams', $post_id );
$making_charge = get_field( 'making_charge', $post_id );
$purity_label  = get_field( 'purity_label', $post_id ) ?: '17K';
$status        = get_field( 'status', $post_id ) ?: 'available';
$price         = verena_compute_product_price( $weight, $making_charge, $purity_label );

$terms    = get_the_terms( $post_id, 'verena_category' );
$category = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : 'Koleksi';
$title    = get_the_title( $post_id );
?>
<a class="card" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
	<div class="card__image">
		<?php if ( has_post_thumbnail( $post_id ) ) : ?>
			<?php echo get_the_post_thumbnail( $post_id, 'medium_large' ); ?>
		<?php else : ?>
			<span class="card__image-note">product shot: <?php echo esc_html( $title ); ?></span>
		<?php endif; ?>
	</div>
	<div class="card__body">
		<div class="row" style="justify-content:space-between;align-items:flex-start;gap:8px;">
			<span class="card__cat"><?php echo esc_html( $category ); ?></span>
			<?php verena_status_badge( $status ); ?>
		</div>
		<h3 class="card__title"><?php echo esc_html( $title ); ?></h3>
		<p class="card__meta"><?php echo esc_html( verena_format_grams( $weight ) ); ?> &middot; <?php echo esc_html( $purity_label ); ?></p>
		<p class="card__price">
			<?php echo $price ? esc_html( verena_format_idr( $price ) ) : 'Hubungi kami untuk harga'; ?>
		</p>
	</div>
</a>
