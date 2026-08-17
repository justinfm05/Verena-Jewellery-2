<?php
/**
 * Berita (news) article card, used on the Berita listing page.
 * Expects $args['post_id'].
 *
 * @package Verena_Jewellery
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_id = $args['post_id'] ?? get_the_ID();
$title   = get_the_title( $post_id );
?>
<a class="card" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
	<div class="card__image">
		<?php if ( has_post_thumbnail( $post_id ) ) : ?>
			<?php echo get_the_post_thumbnail( $post_id, 'medium_large' ); ?>
		<?php else : ?>
			<span class="card__image-note">Verena Jewellery</span>
		<?php endif; ?>
	</div>
	<div class="card__body">
		<span class="card__cat"><?php echo esc_html( get_the_date( 'j F Y', $post_id ) ); ?></span>
		<h3 class="card__title"><?php echo esc_html( $title ); ?></h3>
		<p class="card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt( $post_id ), 20 ) ); ?></p>
	</div>
</a>
