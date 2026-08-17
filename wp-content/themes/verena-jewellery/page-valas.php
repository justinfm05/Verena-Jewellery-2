<?php
/**
 * Valas (currency exchange) page. Create a WP Page titled "Valas", slug
 * "valas" — matches this theme's page-{slug}.php convention. Deliberately
 * NOT linked from the nav (see header.php) and meant to be kept as a Draft
 * in wp-admin — verena_page_is_published( 'valas' ) then returns false and
 * the page simply doesn't get linked from anywhere else on the site either,
 * without needing any extra code. It stays reachable directly by URL for
 * whoever needs to preview it ahead of launch.
 *
 * Rates come from verena_get_valas_data() (see valas-sheet-sync.php in the
 * plugin) — a Google Sheet sync that isn't wired up to a real sheet yet (see
 * that file's docblock). Until it is, every rate below shows as "Hubungi
 * kami" and customers are pointed to WhatsApp, exactly as the final version
 * is meant to work for currencies the sheet doesn't cover.
 *
 * @package Verena_Jewellery
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$currencies = array(
	'USD' => 'Dolar Amerika Serikat',
	'AUD' => 'Dolar Australia',
	'SGD' => 'Dolar Singapura',
	'EUR' => 'Euro',
);

$valas = verena_get_valas_data();

while ( have_posts() ) : the_post();
	?>
	<main class="container section">
		<div class="text-center section-narrow" style="margin-bottom:var(--space-4);">
			<p class="eyebrow">Valas</p>
			<h1><?php the_title(); ?></h1>
			<p class="text-muted">Kurs jual beli mata uang asing hari ini. Hubungi kami langsung via WhatsApp untuk kurs terkini dan transaksi.</p>
		</div>

		<div class="gold-ref-grid">
			<?php foreach ( $currencies as $code => $label ) : ?>
				<?php
				$rate       = $valas['rates'][ $code ] ?? null;
				$rate_label = null !== $rate ? 'Rp ' . number_format( (int) $rate, 0, ',', '.' ) : 'Hubungi kami';
				$wa_message = sprintf(
					'Halo Verena Jewellery, saya ingin tanya kurs %s/IDR hari ini.',
					$code
				);
				?>
				<a class="gold-ref-card" href="<?php echo esc_url( verena_wa_url( $wa_message ) ); ?>" target="_blank" rel="noopener">
					<span class="gold-ref-card__label"><?php echo esc_html( $code ); ?>/IDR</span>
					<p class="gold-ref-card__price"><?php echo esc_html( $rate_label ); ?></p>
					<span class="gold-ref-card__unit"><?php echo esc_html( $label ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>

		<p class="text-muted text-center" style="margin-top:14px; font-size:12.5px; font-style:italic;">kurs final mengikuti kesepakatan saat transaksi</p>
		<div class="text-center" style="margin-top:16px;">
			<?php verena_whatsapp_button( 'Halo Verena Jewellery, saya ingin bertanya tentang Valas.', 'Tanya Kurs via WhatsApp' ); ?>
		</div>
	</main>
	<?php
endwhile;
get_footer();
