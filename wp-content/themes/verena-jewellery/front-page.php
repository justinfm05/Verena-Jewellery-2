<?php
/**
 * Homepage: hero, Paxel delivery highlight, custom showcase, testimonials,
 * Instagram feed.
 *
 * @package Verena_Jewellery
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$shop        = verena_shop_info();
$wa_hero     = verena_wa_url( 'Halo Verena Jewellery, saya ingin bertanya tentang koleksi perhiasan.' );

/* Hero split-panel images. Drop the real files in with these exact names
   and each side switches over automatically — no code change needed. */
$hero_texture_file  = 'hero-texture.jpg';
$hero_necklace_file = 'hero-necklace.jpg';
$hero_texture_path  = get_template_directory() . '/assets/img/' . $hero_texture_file;
$hero_necklace_path = get_template_directory() . '/assets/img/' . $hero_necklace_file;
$hero_placeholder   = get_template_directory_uri() . '/assets/img/hero-placeholder.png';

/* Past custom-order process clips (video) or stills (image). Drop a
   matching file into assets/video/ or assets/img/ and each slot switches
   from the placeholder to the real media automatically — no code change
   needed. */
$showcase_items = array(
	array( 'type' => 'video', 'file' => 'custom-showcase-1.mp4' ),
	array( 'type' => 'video', 'file' => 'custom-showcase-2.mp4' ),
	array( 'type' => 'image', 'file' => 'custom-showcase-3.jpg' ),
	array( 'type' => 'video', 'file' => 'custom-showcase-4.mp4' ),
);
?>

<!-- Hero -->
<section class="hero">
	<div class="hero__panel hero__panel--text">
		<img
			class="hero__panel-bg"
			src="<?php echo esc_url( file_exists( $hero_texture_path ) ? verena_asset_url( 'assets/img/' . $hero_texture_file ) : $hero_placeholder ); ?>"
			alt=""
			aria-hidden="true"
		/>
		<div class="hero__panel-overlay"></div>
		<div class="hero__inner">
			<div class="hero__content">
				<span class="eyebrow">Verena Jewellery &nbsp;&middot;&nbsp; Jakarta Selatan</span>
				<h1>Perhiasan Emas untuk Momen yang Abadi</h1>
				<p class="hero__body">Emas dan perhiasan pilihan, dipercaya keluarga Jakarta sejak 1999 — kini hanya sejauh satu pesan dari rumah Anda.</p>
				<div class="hero__actions">
					<a class="btn btn-gold" href="<?php echo esc_url( $wa_hero ); ?>" target="_blank" rel="noopener">
						<?php echo verena_wa_icon( 18 ); // phpcs:ignore -- trusted inline SVG. ?>
						Chat via WhatsApp
					</a>
					<a class="btn btn-outline-light" href="<?php echo esc_url( verena_page_url( 'bullion' ) ); ?>">Lihat Harga Emas Hari Ini</a>
				</div>
			</div>
		</div>
	</div>
	<div class="hero__panel hero__panel--image">
		<img
			class="hero__panel-bg"
			src="<?php echo esc_url( file_exists( $hero_necklace_path ) ? verena_asset_url( 'assets/img/' . $hero_necklace_file ) : $hero_placeholder ); ?>"
			alt="Model mengenakan kalung liontin hati emas"
		/>
	</div>
</section>

<!-- Delivery partnership (Paxel) -->
<section class="band band--forest">
	<div class="band__inner" style="max-width:760px;">
		<div class="band__head" style="margin-bottom:0;">
			<div class="delivery-journey" aria-hidden="true">
				<div class="delivery-journey__icon">
					<svg width="34" height="34" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 4H15L20 9L12 20L4 9Z" stroke="#C9A24B" stroke-width="1.5" stroke-linejoin="round"/><path d="M4 9H20M12 20L9 4M12 20L15 4" stroke="#C9A24B" stroke-width="1.2" stroke-linejoin="round"/></svg>
				</div>
				<span class="delivery-journey__line"></span>
				<div class="delivery-journey__icon">
					<svg width="34" height="34" viewBox="0 0 24 24" fill="none"><path d="M2 7h11v9H2z" stroke="#C9A24B" stroke-width="1.5" stroke-linejoin="round"/><path d="M13 10h4l4 3v3h-2" stroke="#C9A24B" stroke-width="1.5" stroke-linejoin="round"/><circle cx="6" cy="18" r="1.6" stroke="#C9A24B" stroke-width="1.5"/><circle cx="17" cy="18" r="1.6" stroke="#C9A24B" stroke-width="1.5"/><path d="M2 16h1M17 16h-4" stroke="#C9A24B" stroke-width="1.5"/></svg>
				</div>
				<span class="delivery-journey__line"></span>
				<div class="delivery-journey__icon">
					<svg width="34" height="34" viewBox="0 0 24 24" fill="none"><path d="M4 11l8-7 8 7" stroke="#C9A24B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M6 10v9h12v-9" stroke="#C9A24B" stroke-width="1.5" stroke-linejoin="round"/><path d="M10 19v-5h4v5" stroke="#C9A24B" stroke-width="1.5" stroke-linejoin="round"/></svg>
				</div>
			</div>
			<h2>Diantar Langsung ke Rumah Anda</h2>
			<p>Verena Jewellery bekerja sama dengan <strong>Paxel</strong> untuk mengirimkan perhiasan dan emas batangan Anda dengan aman, langsung ke rumah Anda.</p>
			<div class="delivery-stats">
				<div class="delivery-stat">
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3z" stroke="#C9A24B" stroke-width="1.5" stroke-linejoin="round"/></svg>
					<strong>Up to 100 Juta</strong>
					<span>Asuransi Pengiriman</span>
				</div>
				<div class="delivery-stat">
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 21s7-6.5 7-12a7 7 0 10-14 0c0 5.5 7 12 7 12z" stroke="#C9A24B" stroke-width="1.5" stroke-linejoin="round"/><circle cx="12" cy="9" r="2.3" stroke="#C9A24B" stroke-width="1.5"/></svg>
					<strong>Real-time</strong>
					<span>Lacak via GPS</span>
				</div>
				<div class="delivery-stat">
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="8.5" stroke="#C9A24B" stroke-width="1.5"/><path d="M12 7.5V12l3 2" stroke="#C9A24B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
					<strong>1&ndash;24 Jam</strong>
					<span>Sampai di Rumah</span>
				</div>
			</div>
			<a class="btn btn-gold" style="margin-top:24px;" href="<?php echo esc_url( verena_page_url( 'bullion' ) ); ?>">
				Beli Logam Mulia Sekarang
			</a>
		</div>
	</div>
</section>

<!-- Custom Showcase -->
<section class="band band--champagne">
	<div class="band__inner">
		<div class="band__head">
			<h2>Custom Showcase</h2>
			<p>Laser Engraving &nbsp;&nbsp;&middot;&nbsp;&nbsp; Certified Designer &nbsp;&nbsp;&middot;&nbsp;&nbsp; High Quality</p>
		</div>

		<div class="showcase-grid">
			<?php foreach ( $showcase_items as $item ) : ?>
				<?php
				$is_video = 'video' === $item['type'];
				$subdir   = $is_video ? 'assets/video/' : 'assets/img/';
				$rel_path = $subdir . $item['file'];
				$abs_path = get_template_directory() . '/' . $rel_path;
				?>
				<div class="showcase-video">
					<?php if ( file_exists( $abs_path ) ) : ?>
						<?php if ( $is_video ) : ?>
							<video src="<?php echo esc_url( verena_asset_url( $rel_path ) ); ?>" controls playsinline muted autoplay loop></video>
						<?php else : ?>
							<img src="<?php echo esc_url( verena_asset_url( $rel_path ) ); ?>" alt="Contoh hasil custom design" />
						<?php endif; ?>
					<?php else : ?>
						<span class="showcase-video__placeholder">Placeholder Video of Custom Product</span>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<?php if ( $shop['instagram'] ) : ?>
			<p class="text-center" style="margin-top:20px;">
				<a class="ig-more" href="<?php echo esc_url( $shop['instagram'] ); ?>" target="_blank" rel="noopener">Lihat lebih banyak contoh di Instagram &rarr;</a>
			</p>
		<?php endif; ?>

		<p class="text-center showcase-cta-line">Konsultasi Sekarang dan &#10024; Wujudkan Impianmu &#10024;</p>

		<div class="text-center">
			<a class="btn btn-gold" href="<?php echo esc_url( verena_page_url( 'custom' ) ); ?>">Konsultasi Custom Design</a>
		</div>
	</div>
</section>

<!-- Testimonials -->
<section class="band band--forest" style="padding:56px 24px;">
	<div class="band__inner" style="max-width:1160px;">
		<div class="band__head" style="margin-bottom:32px;">
			<span class="eyebrow">Dipercaya Sejak <?php echo esc_html( $shop['established'] ); ?></span>
			<h2>Kata Pelanggan Kami</h2>
		</div>
		<?php if ( shortcode_exists( 'reviews-feed' ) ) : ?>
			<?php echo do_shortcode( '[reviews-feed feed=1]' ); ?>
		<?php else : ?>
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
		<?php endif; ?>
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
		<?php if ( shortcode_exists( 'instagram-feed' ) ) : ?>
			<?php echo do_shortcode( '[instagram-feed feed=1]' ); ?>
		<?php else : ?>
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
		<?php endif; ?>
	</div>
</section>

<?php get_footer(); ?>
