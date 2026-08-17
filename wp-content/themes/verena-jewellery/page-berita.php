<?php
/**
 * Berita (news) listing. Create a WP Page titled "Berita", slug "berita" —
 * matches this theme's page-{slug}.php convention. Lists native WordPress
 * posts (Posts menu in wp-admin), newest first — publishing an article there
 * is all it takes for it to appear here, no extra setup. Individual articles
 * render via the theme's single.php.
 *
 * Existing purely to give the site real, crawlable, regularly-updated
 * content for SEO — every published post is its own indexable page with its
 * own title/URL.
 *
 * @package Verena_Jewellery
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$paged = max( 1, get_query_var( 'paged' ) ? (int) get_query_var( 'paged' ) : ( isset( $_GET['paged'] ) ? (int) $_GET['paged'] : 1 ) );
$query = new WP_Query(
	array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 9,
		'paged'          => $paged,
	)
);
?>

<main class="container section">
	<div class="text-center section-narrow" style="margin-bottom:var(--space-4);">
		<p class="eyebrow">Berita</p>
		<h1><?php the_title(); ?></h1>
		<p class="text-muted">Kabar, tips, dan info terbaru seputar emas dan perhiasan dari Verena Jewellery.</p>
	</div>

	<?php if ( $query->have_posts() ) : ?>
		<div class="grid">
			<?php foreach ( $query->posts as $post ) : ?>
				<?php get_template_part( 'template-parts/news-card', null, array( 'post_id' => $post->ID ) ); ?>
			<?php endforeach; ?>
		</div>

		<?php if ( $query->max_num_pages > 1 ) : ?>
			<div class="pagination">
				<?php
				echo paginate_links(
					array(
						'base'      => esc_url_raw( add_query_arg( 'paged', '%#%' ) ),
						'format'    => '',
						'current'   => $paged,
						'total'     => $query->max_num_pages,
						'prev_text' => '&larr; Sebelumnya',
						'next_text' => 'Berikutnya &rarr;',
					)
				);
				?>
			</div>
		<?php endif; ?>
	<?php else : ?>
		<div class="empty-state">
			<p>Belum ada berita saat ini. Cek kembali segera.</p>
		</div>
	<?php endif; ?>
</main>

<?php
wp_reset_postdata();
get_footer();
