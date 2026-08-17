<?php
/**
 * Gold price reference ticker — thin bar sticking directly below the nav on
 * every page (see .gold-ticker in style.css + assets/js/main.js). Rates come
 * from verena_gold_rates() (Antam 2026 / Emasku / UBS — each brand's
 * 100-gram bar price divided by 100, not the 1-gram bar's own price, from
 * the shared bullion Google Sheet).
 *
 * @package Verena_Jewellery
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ticker   = verena_gold_rates();
$wa_ticker = verena_wa_url( 'Halo Verena Jewellery, saya ingin tanya harga emas hari ini.' );
?>
<div class="gold-ticker">
	<div class="gold-ticker__inner">
		<span class="gold-ticker__label">Harga Emas Referensi &middot; <?php echo esc_html( $ticker['date'] ); ?></span>
		<div class="gold-ticker__rates">
			<?php foreach ( $ticker['rates'] as $rate ) : ?>
				<span class="gold-ticker__rate"><?php echo esc_html( $rate['label'] ); ?> <strong>Rp <?php echo esc_html( $rate['price'] ); ?></strong>/gr</span>
			<?php endforeach; ?>
		</div>
		<p class="gold-ticker__note">harga final mengikuti kurs saat transaksi</p>
		<a class="gold-ticker__link" href="<?php echo esc_url( $wa_ticker ); ?>" target="_blank" rel="noopener">Tanya harga &rarr;</a>
	</div>
</div>
