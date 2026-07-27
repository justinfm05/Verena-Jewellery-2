<?php
/**
 * Contact. Create a WP Page with slug "contact-us".
 *
 * @package Verena_Jewellery
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$shop      = verena_shop_info();
$wa_link   = verena_wa_url( 'Halo Verena Jewellery, saya ingin bertanya.' );
$map_query = get_option( 'verena_map_query', 'ITC Fatmawati Jakarta Selatan' );
?>

<section class="page-header">
	<div class="container section-narrow">
		<p class="eyebrow">Hubungi Kami</p>
		<h1>Kunjungi Toko Kami di ITC Fatmawati</h1>
		<p>Datang langsung ke toko kami, atau chat lewat WhatsApp untuk pertanyaan, harga, dan konsultasi.</p>
		<div class="mt-4">
			<a class="btn btn-gold" href="<?php echo esc_url( $wa_link ); ?>" target="_blank" rel="noopener">
				<?php echo verena_wa_icon( 18 ); // phpcs:ignore -- trusted inline SVG. ?>
				Chat via WhatsApp
			</a>
		</div>
	</div>
</section>

<section class="band band--champagne">
	<div class="band__inner" style="max-width:1000px;">
		<div class="footer-grid" style="align-items:start;">
			<div class="footer-col">
				<h3 style="color:var(--gold-ink);">Alamat</h3>
				<?php if ( $shop['address'] ) : ?>
					<p style="color:var(--ink-muted);"><?php echo nl2br( esc_html( $shop['address'] ) ); ?></p>
				<?php endif; ?>
				<?php if ( $shop['phone'] ) : ?>
					<p style="color:var(--ink-muted);">Telp: <?php echo esc_html( $shop['phone'] ); ?></p>
				<?php endif; ?>
			</div>
			<div class="footer-col">
				<h3 style="color:var(--gold-ink);">Jam Buka</h3>
				<?php if ( $shop['hours'] ) : ?>
					<p style="color:var(--ink-muted);"><?php echo nl2br( esc_html( $shop['hours'] ) ); ?></p>
				<?php endif; ?>
				<?php if ( $shop['instagram'] ) : ?>
					<p><a class="btn btn-outline btn-sm" href="<?php echo esc_url( $shop['instagram'] ); ?>" target="_blank" rel="noopener">Instagram</a></p>
				<?php endif; ?>
			</div>
			<div class="footer-col">
				<h3 style="color:var(--gold-ink);">Lokasi</h3>
				<div class="footer-map" style="border-color:var(--line-light);">
					<iframe title="Peta lokasi <?php echo esc_attr( $shop['name'] ); ?>" src="<?php echo esc_url( 'https://www.google.com/maps?q=' . rawurlencode( $map_query ) . '&output=embed' ); ?>" loading="lazy"></iframe>
				</div>
			</div>
		</div>

		<?php
		// Any content typed into the WP Page editor renders below the store info.
		while ( have_posts() ) :
			the_post();
			if ( trim( get_the_content() ) ) :
				?>
				<div class="stack" style="max-width:720px;margin:48px auto 0;"><?php the_content(); ?></div>
				<?php
			endif;
		endwhile;
		?>
	</div>
</section>

<?php
get_footer();
