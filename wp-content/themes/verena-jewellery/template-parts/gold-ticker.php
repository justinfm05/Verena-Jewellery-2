<?php
/**
 * Gold price reference ticker — thin bar above the header on every page.
 * Rates come from verena_gold_rates() (option-backed, staff-editable).
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
				<span class="gold-ticker__rate"><?php echo esc_html( $rate['karat'] ); ?>K <strong>Rp <?php echo esc_html( $rate['price'] ); ?></strong>/gr</span>
			<?php endforeach; ?>
		</div>
		<p class="gold-ticker__note">harga final mengikuti kurs saat transaksi</p>
		<a class="gold-ticker__link" href="<?php echo esc_url( $wa_ticker ); ?>" target="_blank" rel="noopener">Tanya harga &rarr;</a>
	</div>
</div>
