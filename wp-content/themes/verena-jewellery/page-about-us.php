<?php
/**
 * About. Create a WP Page with slug "about-us".
 *
 * @package Verena_Jewellery
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$shop = verena_shop_info();
while ( have_posts() ) : the_post();
	?>
	<section class="page-header">
		<div class="container section-narrow">
			<p class="eyebrow">Tentang Kami</p>
			<h1>Toko emas kepercayaan, <em>sejak <?php echo esc_html( $shop['established'] ); ?>.</em></h1>
			<p>Berdiri sejak <?php echo esc_html( $shop['established'] ); ?>, <?php echo esc_html( $shop['name'] ); ?> hadir dengan perhiasan emas berkualitas dan logam mulia berbagai gramasi — dipercaya pelanggan di Jakarta selama lebih dari dua dekade.</p>
		</div>
	</section>

	<main class="container section section-narrow">
		<div class="stack"><?php the_content(); ?></div>

		<div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));">
			<div class="service-card text-center">
				<p class="card__price" style="font-size:1.8rem;"><?php echo esc_html( $shop['established'] ); ?></p>
				<p class="text-muted mb-0">Berdiri Sejak</p>
			</div>
			<div class="service-card text-center">
				<p class="card__price" style="font-size:1.8rem;">100%</p>
				<p class="text-muted mb-0">Transaksi Online via WhatsApp</p>
			</div>
			<div class="service-card text-center">
				<p class="card__price" style="font-size:1.8rem;">1:1</p>
				<p class="text-muted mb-0">Setiap Piece Satu-Satunya</p>
			</div>
		</div>

		<div class="service-card mt-4 about-contact-card">
			<h3>Kunjungi &amp; Hubungi Kami</h3>
			<p class="text-muted mb-0">📍 <?php echo esc_html( $shop['address'] ); ?></p>
			<p class="text-muted mb-0">🕒 <?php echo esc_html( $shop['hours'] ); ?></p>
			<p class="text-muted">☎️ <?php echo esc_html( $shop['phone'] ); ?></p>
			<div class="row">
				<?php verena_whatsapp_button( 'Halo Verena Jewellery, saya ingin bertanya.', 'Chat di WhatsApp' ); ?>
				<?php if ( $shop['instagram'] ) : ?>
					<a class="btn btn-outline" href="<?php echo esc_url( $shop['instagram'] ); ?>" target="_blank" rel="noopener">Instagram</a>
				<?php endif; ?>
			</div>
		</div>
	</main>
	<?php
endwhile;
get_footer();
