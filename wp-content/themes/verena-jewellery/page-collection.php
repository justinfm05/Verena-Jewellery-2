<?php
/**
 * Fashion jewellery catalog. Create a WP Page with slug "collection" for this
 * to be used automatically (WordPress's page-{slug}.php convention).
 *
 * @package Verena_Jewellery
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$active_category = isset( $_GET['category'] ) ? sanitize_title( wp_unslash( $_GET['category'] ) ) : '';
$categories = get_terms( array( 'taxonomy' => 'verena_category', 'hide_empty' => true ) );
$query = verena_get_fashion_query( $active_category ?: null );
?>

<main class="container section">
	<div class="text-center section-narrow" style="margin-bottom:var(--space-4);">
		<p class="eyebrow">Koleksi</p>
		<h1><?php the_title(); ?></h1>
		<p class="text-muted">Setiap piece dibuat satu-satunya. Harga dihitung dari berat &times; kadar emas hari ini + biaya pembuatan, dan otomatis diperbarui setiap hari.</p>
	</div>

	<?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
		<div class="filter-bar">
			<a class="filter-pill <?php echo '' === $active_category ? 'is-active' : ''; ?>" href="<?php echo esc_url( get_permalink() ); ?>">Semua</a>
			<?php foreach ( $categories as $term ) : ?>
				<a class="filter-pill <?php echo $active_category === $term->slug ? 'is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'category', $term->slug, get_permalink() ) ); ?>">
					<?php echo esc_html( $term->name ); ?>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ( $query->have_posts() ) : ?>
		<div class="grid">
			<?php foreach ( $query->posts as $post ) : ?>
				<?php get_template_part( 'template-parts/product-card', null, array( 'post_id' => $post->ID ) ); ?>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<div class="empty-state">
			<p>Belum ada piece di kategori ini. Cek kembali segera, atau hubungi kami untuk permintaan khusus.</p>
			<?php verena_whatsapp_button( verena_wa_message_custom_order(), 'Tanya di WhatsApp' ); ?>
		</div>
	<?php endif; ?>
</main>

<?php get_footer(); ?>
