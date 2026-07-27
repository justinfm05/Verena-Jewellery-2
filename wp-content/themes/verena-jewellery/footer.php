<?php
/**
 * Site footer: brand + socials, store info, embedded map, bottom bar.
 *
 * @package Verena_Jewellery
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$shop      = verena_shop_info();
$logo_url  = get_template_directory_uri() . '/assets/img/verena-logo-stacked.png';
$wa_footer = verena_wa_url( 'Halo Verena Jewellery, saya ingin bertanya.' );
$map_query = get_option( 'verena_map_query', 'ITC Fatmawati Jakarta Selatan' );
?>
	<footer class="site-footer">
		<div class="site-footer__inner">
			<div class="footer-grid">
				<div class="footer-col">
					<img class="footer-logo" src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $shop['name'] ); ?>" />
					<p class="footer-blurb">Perhiasan emas dan desain custom terpercaya sejak <?php echo esc_html( $shop['established'] ); ?>. Melayani cincin kawin, kalung, gelang, dan pesanan khusus untuk keluarga Anda.</p>
					<div class="footer-socials">
						<?php if ( $shop['instagram'] ) : ?>
							<a class="footer-social" href="<?php echo esc_url( $shop['instagram'] ); ?>" target="_blank" rel="noopener" aria-label="Instagram">
								<svg width="17" height="17" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="4.2" stroke="currentColor" stroke-width="1.6"/><circle cx="17.3" cy="6.7" r="1.1" fill="currentColor"/></svg>
							</a>
						<?php endif; ?>
						<a class="footer-social" href="<?php echo esc_url( $wa_footer ); ?>" target="_blank" rel="noopener" aria-label="WhatsApp">
							<svg width="17" height="17" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12c0 1.87.5 3.62 1.38 5.13L2 22l5.05-1.32A9.94 9.94 0 0012 22c5.52 0 10-4.48 10-10S17.52 2 12 2z" fill="currentColor"/></svg>
						</a>
					</div>
				</div>

				<div class="footer-col">
					<h3>Kunjungi Toko</h3>
					<?php if ( $shop['address'] ) : ?>
						<p><?php echo nl2br( esc_html( $shop['address'] ) ); ?></p>
					<?php endif; ?>
					<?php if ( $shop['hours'] ) : ?>
						<p><?php echo nl2br( esc_html( $shop['hours'] ) ); ?></p>
					<?php endif; ?>
					<a class="footer-link" href="<?php echo esc_url( $wa_footer ); ?>" target="_blank" rel="noopener">+<?php echo esc_html( $shop['whatsapp'] ); ?></a>
				</div>

				<div class="footer-col">
					<h3>Lokasi</h3>
					<div class="footer-map">
						<iframe title="Peta lokasi <?php echo esc_attr( $shop['name'] ); ?>" src="<?php echo esc_url( 'https://www.google.com/maps?q=' . rawurlencode( $map_query ) . '&output=embed' ); ?>" loading="lazy"></iframe>
					</div>
				</div>
			</div>

			<div class="footer-bottom">
				<p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( $shop['name'] ); ?>. Sejak <?php echo esc_html( $shop['established'] ); ?>, Jakarta Selatan.</p>
				<div class="footer-bottom__links">
					<a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>">Kebijakan Privasi</a>
					<a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>">Syarat &amp; Ketentuan</a>
				</div>
			</div>
		</div>
	</footer>

<?php wp_footer(); ?>
</body>
</html>
