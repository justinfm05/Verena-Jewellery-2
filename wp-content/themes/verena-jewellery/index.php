<?php
/**
 * Fallback template (required for every WP theme). Most real traffic hits
 * the more specific page-*.php / single-verena_product.php templates.
 *
 * @package Verena_Jewellery
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
<main class="container section">
	<?php if ( have_posts() ) : ?>
		<div class="stack">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article>
					<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<?php the_excerpt(); ?>
				</article>
				<?php
			endwhile;
			?>
		</div>
	<?php else : ?>
		<p>Nothing found.</p>
	<?php endif; ?>
</main>
<?php get_footer(); ?>
