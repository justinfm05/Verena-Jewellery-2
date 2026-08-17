<?php
/**
 * Single Berita (news) article — the theme's only native-post single
 * template (single-verena_product.php handles the separate product post
 * type), so every published post uses this view.
 *
 * @package Verena_Jewellery
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

while ( have_posts() ) :
	the_post();
	?>
	<main class="container section section-narrow">
		<a href="<?php echo esc_url( verena_page_url( 'berita' ) ); ?>" class="bullion-back-link">
			<svg width="30" height="30" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M15 6l-6 6 6 6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
			<span>Kembali ke Berita</span>
		</a>

		<article>
			<p class="eyebrow"><?php echo esc_html( get_the_date( 'j F Y' ) ); ?></p>
			<h1><?php the_title(); ?></h1>

			<?php if ( has_post_thumbnail() ) : ?>
				<div class="card__image" style="aspect-ratio:16/9; border-radius:var(--radius-lg); margin:var(--space-3) 0;">
					<?php the_post_thumbnail( 'large' ); ?>
				</div>
			<?php endif; ?>

			<div class="stack">
				<?php the_content(); ?>
			</div>
		</article>
	</main>
	<?php
endwhile;
get_footer();
