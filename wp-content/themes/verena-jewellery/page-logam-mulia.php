<?php
/**
 * Bullion brand overview. Create a WP Page with slug "logam-mulia". Then create
 * three CHILD pages under it with slugs "antam", "ubs", "emasku" (so their
 * URLs become /logam-mulia/antam/ etc.) and assign each the "Bullion Brand"
 * page template (template-bullion-brand.php) — the template matches the
 * brand by the page's own slug against the verena_bullion_brand taxonomy.
 *
 * @package Verena_Jewellery
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
while ( have_posts() ) : the_post();

	$brands = array(
		'antam'  => 'Antam',
		'ubs'    => 'UBS',
		'emasku' => 'Emasku',
	);
	?>
	<main class="container section">
		<div class="text-center section-narrow" style="margin-bottom:var(--space-4);">
			<p class="eyebrow">Emas Batangan</p>
			<h1><?php the_title(); ?></h1>
			<div class="text-muted"><?php the_content(); ?></div>
		</div>

		<div class="grid">
			<?php foreach ( $brands as $slug => $name ) : ?>
				<?php
				$child_page = get_page_by_path( verena_page_slug( 'bullion' ) . '/' . $slug );
				$brand_url = $child_page ? get_permalink( $child_page ) : verena_page_url( 'bullion', $slug . '/' );
				$count = count( verena_get_bullion_by_brand( $slug ) );
				?>
				<a class="card" href="<?php echo esc_url( $brand_url ); ?>">
					<div class="card__body">
						<h3 class="card__title"><?php echo esc_html( $name ); ?></h3>
						<p class="text-muted mb-0"><?php echo esc_html( $count ); ?> denominasi tersedia</p>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
	</main>
	<?php
endwhile;
get_footer();
