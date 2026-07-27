<?php
/**
 * Single-item buyback estimator. Create a WP Page with slug "buyback-emas".
 *
 * @package Verena_Jewellery
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$buyback_rate = verena_get_current_buyback_rate();
$rate_per_gram = $buyback_rate ? (int) $buyback_rate['price_per_gram'] : 0;
$purity_options = verena_get_purity_options();

while ( have_posts() ) : the_post();
	?>
	<main class="container section section-narrow">
		<p class="eyebrow">Jual Emas Anda</p>
		<h1><?php the_title(); ?></h1>
		<div class="stack"><?php the_content(); ?></div>

		<?php if ( ! $buyback_rate ) : ?>
			<div class="empty-state">
				<p>Estimator sedang tidak tersedia. Silakan hubungi kami langsung untuk penawaran.</p>
				<?php verena_whatsapp_button( 'Halo Verena Jewellery, saya ingin jual emas bekas.', 'Tanya via WhatsApp' ); ?>
			</div>
		<?php else : ?>
			<div
				x-data="verenaBuyback(<?php echo esc_attr( wp_json_encode( array( 'ratePerGram' => $rate_per_gram, 'purityOptions' => $purity_options, 'waNumber' => verena_whatsapp_number() ) ) ); ?>)"
				class="mt-4"
			>
				<div class="calc-item-row" style="grid-template-columns: 1fr 1fr;">
					<div class="form-field mb-0">
						<label for="bb-weight">Berat (gram)</label>
						<input id="bb-weight" type="number" min="0" step="0.001" x-model.number="weight" placeholder="cth. 5">
					</div>
					<div class="form-field mb-0">
						<label for="bb-purity">Kadar</label>
						<select id="bb-purity" x-model="purityLabel" x-init="$nextTick(() => { $el.value = purityLabel })">
							<template x-for="option in purityOptions" :key="option.label">
								<option :value="option.label" x-text="option.label"></option>
							</template>
						</select>
					</div>
				</div>

				<div class="calc-total-bar">
					<span>Estimasi Nilai</span>
					<span class="calc-total-bar__value" x-text="formattedEstimate"></span>
				</div>

				<p class="disclaimer"><?php echo esc_html( verena_estimate_disclaimer() ); ?></p>

				<a class="btn btn-whatsapp btn-block" target="_blank" rel="noopener" :href="waLink">Jual ke Verena via WhatsApp</a>
			</div>

			<script>
				function verenaBuyback( config ) {
					return {
						weight: 0,
						purityLabel: config.purityOptions.length ? config.purityOptions[0].label : '',
						purityOptions: config.purityOptions,
						ratePerGram: config.ratePerGram,
						waNumber: config.waNumber,
						get estimate() {
							const purity = this.purityOptions.find( ( option ) => option.label === this.purityLabel );
							if ( ! purity || ! this.weight || this.weight <= 0 ) {
								return 0;
							}
							const fraction = purity.fraction_bps / 10000;
							const raw = this.weight * fraction * this.ratePerGram;
							return Math.round( raw / 100 ) * 100;
						},
						get formattedEstimate() {
							return 'Rp' + this.estimate.toLocaleString( 'id-ID' );
						},
						get waLink() {
							const lines = [
								'Halo Verena Jewellery, saya ingin jual emas bekas.',
								'Berat: ' + this.weight + ' gr',
								'Kadar: ' + this.purityLabel,
								'Estimasi awal: ' + this.formattedEstimate,
								'',
								'<?php echo esc_js( verena_estimate_disclaimer() ); ?>',
							];
							return 'https://wa.me/' + this.waNumber + '?text=' + encodeURIComponent( lines.join( '\n' ) );
						},
					};
				}
			</script>
		<?php endif; ?>
	</main>
	<?php
endwhile;
get_footer();
