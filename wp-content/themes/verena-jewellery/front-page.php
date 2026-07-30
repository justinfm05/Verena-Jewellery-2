<?php
/**
 * Homepage: hero, featured collections, testimonials, Instagram feed.
 *
 * @package Verena_Jewellery
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$shop        = verena_shop_info();
$hero_img    = get_template_directory_uri() . '/assets/img/hero-placeholder.png';
$wa_hero     = verena_wa_url( 'Halo Verena Jewellery, saya ingin bertanya tentang koleksi perhiasan.' );

$featured       = verena_get_fashion_query();
$featured_items = array_slice( $featured->posts, 0, 4 );

/* Placeholder collection cards from the design handoff, used only when no
   real products have been published yet. */
$placeholder_products = array(
	array( 'name' => 'Cincin Kawin Klasik',        'category' => 'Cincin Kawin',  'karat' => '18K', 'weight' => '3.2 gram' ),
	array( 'name' => 'Kalung Rantai Rose',         'category' => 'Kalung',        'karat' => '22K', 'weight' => '5.0 gram' ),
	array( 'name' => 'Gelang Bola Emas',           'category' => 'Gelang',        'karat' => '18K', 'weight' => '4.1 gram' ),
	array( 'name' => 'Cincin Custom Batu Permata', 'category' => 'Custom Design', 'karat' => '18K', 'weight' => '2.8 gram' ),
);
?>

<!-- Hero -->
<section class="hero">
	<img class="hero__img" src="<?php echo esc_url( $hero_img ); ?>" alt="Cincin kawin emas kuning 17K, sepasang, di atas kain beludru" />
	<div class="hero__scrim"></div>
	<div class="hero__scrim-bottom"></div>
	<div class="hero__inner">
		<div class="hero__content">
			<span class="eyebrow">Sejak <?php echo esc_html( $shop['established'] ); ?> &middot; Jakarta Selatan</span>
			<h1>Perhiasan Emas untuk Momen yang Abadi</h1>
			<p class="hero__body">Cincin kawin, kalung, dan gelang emas buatan tangan — serta layanan desain custom untuk perhiasan yang dibuat khusus untuk kisah Anda.</p>
			<div class="hero__actions">
				<a class="btn btn-gold" href="<?php echo esc_url( $wa_hero ); ?>" target="_blank" rel="noopener">
					<?php echo verena_wa_icon( 18 ); // phpcs:ignore -- trusted inline SVG. ?>
					Chat via WhatsApp
				</a>
				<a class="btn btn-outline-light" href="<?php echo esc_url( verena_page_url( 'bullion' ) ); ?>">Lihat Harga Emas Hari Ini</a>
			</div>
		</div>
	</div>
</section>

<!-- Featured collections -->
<section id="collections" class="band band--champagne">
	<div class="band__inner">
		<div class="band__head">
			<span class="eyebrow">Koleksi Kami</span>
			<h2>Perhiasan Emas Pilihan</h2>
			<p>Setiap koleksi tersedia dalam berbagai kadar emas. Harga menyesuaikan kurs emas harian — hubungi kami untuk penawaran terbaru.</p>
		</div>

		<div class="product-grid">
			<?php if ( ! empty( $featured_items ) ) : ?>
				<?php foreach ( $featured_items as $post ) : setup_postdata( $post ); ?>
					<?php get_template_part( 'template-parts/product-card', null, array( 'post_id' => $post->ID ) ); ?>
				<?php endforeach; wp_reset_postdata(); ?>
			<?php else : ?>
				<?php foreach ( $placeholder_products as $p ) : ?>
					<?php
					$wa_card = verena_wa_url( 'Halo, saya ingin tanya tentang ' . $p['name'] . '.' );
					?>
					<a class="card" href="<?php echo esc_url( $wa_card ); ?>" target="_blank" rel="noopener">
						<div class="card__image">
							<span class="card__image-note">product shot: <?php echo esc_html( $p['name'] ); ?></span>
						</div>
						<div class="card__body">
							<span class="card__cat"><?php echo esc_html( $p['category'] ); ?></span>
							<h3 class="card__title"><?php echo esc_html( $p['name'] ); ?></h3>
							<p class="card__meta"><?php echo esc_html( $p['karat'] ); ?> &middot; <?php echo esc_html( $p['weight'] ); ?></p>
							<span class="card__cta"><?php echo verena_wa_icon( 15 ); // phpcs:ignore ?> Inquire via WhatsApp</span>
						</div>
					</a>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>

		<div class="text-center mt-4">
			<a class="btn btn-gold" href="<?php echo esc_url( verena_page_url( 'collection' ) ); ?>">Lihat Semua Koleksi</a>
		</div>
	</div>
</section>

<!-- Testimonials -->
<section class="band band--forest">
	<div class="band__inner" style="max-width:1160px;">
		<div class="band__head">
			<span class="eyebrow">Dipercaya Sejak <?php echo esc_html( $shop['established'] ); ?></span>
			<h2>Kata Pelanggan Kami</h2>
		</div>
		<div class="testi-grid">
			<?php foreach ( verena_testimonials() as $t ) : ?>
				<div class="testi-card">
					<div class="testi-stars">
						<?php for ( $i = 0; $i < 5; $i++ ) : ?>
							<svg width="16" height="16" viewBox="0 0 24 24" fill="#C9A24B" aria-hidden="true"><path d="M12 2l2.9 6.6 7.1.7-5.4 4.7 1.6 7-6.2-3.7-6.2 3.7 1.6-7-5.4-4.7 7.1-.7z"/></svg>
						<?php endfor; ?>
					</div>
					<p class="testi-quote">&ldquo;<?php echo esc_html( $t['quote'] ); ?>&rdquo;</p>
					<div>
						<p class="testi-name"><?php echo esc_html( $t['name'] ); ?></p>
						<p class="testi-loc"><?php echo esc_html( $t['location'] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- Instagram feed -->
<section class="band band--champagne">
	<div class="band__inner">
		<div class="ig-head">
			<div>
				<span class="eyebrow">Ikuti Kami</span>
				<h2>@verenajewellery.id</h2>
			</div>
			<?php if ( $shop['instagram'] ) : ?>
				<a class="ig-more" href="<?php echo esc_url( $shop['instagram'] ); ?>" target="_blank" rel="noopener">Lihat di Instagram &rarr;</a>
			<?php endif; ?>
		</div>
		<div class="ig-grid">
			<?php foreach ( verena_instagram_posts() as $post ) : ?>
				<a class="ig-item" href="<?php echo esc_url( $shop['instagram'] ?: '#' ); ?>" target="_blank" rel="noopener">
					<span class="ig-item__cap"><?php echo esc_html( $post['caption'] ); ?></span>
					<span class="ig-item__likes">
						<svg width="10" height="10" viewBox="0 0 24 24" fill="#F2E9CC" aria-hidden="true"><path d="M12 21s-7.5-4.6-10-9.1C.5 8.6 2.3 5 6 5c2 0 3.4 1 4 2.3C10.6 6 12 5 14 5c3.7 0 5.5 3.6 4 6.9C19.5 16.4 12 21 12 21z"/></svg>
						<?php echo esc_html( $post['likes'] ); ?>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php get_footer(); ?>
