<?php
/**
 * Antam bullion detail page. Create a WP Page titled "Antam", slug "antam",
 * with Parent = "Logam Mulia" (so the URL becomes /logam-mulia/antam/) —
 * matches this theme's page-{slug}.php template hierarchy convention, same
 * as every other page template.
 *
 * Gram/Tahun dropdowns and the resulting price are driven entirely by the
 * cached Google Sheet data (verena_get_bullion_sheet_data()) — the exact
 * same numbers shown in the main Logam Mulia table, never a separate source.
 *
 * @package Verena_Jewellery
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$sheet      = verena_get_bullion_sheet_data();
$antam_rows = $sheet['antam'];

$grams = array();
foreach ( $antam_rows as $row ) {
	$grams[] = $row['gram'];
}

while ( have_posts() ) : the_post();
	?>
	<main class="container section">
		<div class="text-center section-narrow" style="margin-bottom:var(--space-4);">
			<p class="eyebrow">Emas Batangan</p>
			<h1><?php the_title(); ?></h1>
		</div>

		<?php if ( empty( $antam_rows ) ) : ?>
			<div class="empty-state">
				<p>Harga sedang tidak tersedia. Silakan hubungi kami langsung untuk info stok.</p>
				<?php verena_whatsapp_button( verena_wa_message_bullion( 'Antam', '', 'beli' ), 'Tanya via WhatsApp' ); ?>
			</div>
		<?php else : ?>
			<div
				class="bullion-detail"
				x-data="verenaAntamDetail(<?php echo esc_attr( wp_json_encode( array( 'rows' => $antam_rows, 'waNumber' => verena_whatsapp_number() ) ) ); ?>)"
			>
				<div class="bullion-detail__media">
					<div class="brand-badge" style="margin-bottom:var(--space-2);">
						<img src="<?php echo esc_url( verena_asset_url( 'assets/img/antam-logo.png' ) ); ?>" alt="Antam" />
					</div>
					<img class="bullion-detail__photo" src="<?php echo esc_url( verena_asset_url( 'assets/img/antam-bar-photo.jpg' ) ); ?>" alt="Antam Fine Gold 999.9 bar" />
				</div>

				<div class="bullion-detail__form">
					<div class="form-field">
						<label for="antam-gram">Gram</label>
						<select id="antam-gram" x-model="gram">
							<option value="">Pilih gram</option>
							<template x-for="g in grams" :key="g">
								<option :value="g" x-text="formatGram(g)"></option>
							</template>
						</select>
					</div>

					<div class="form-field">
						<label for="antam-tahun">Tahun</label>
						<select id="antam-tahun" x-model="tahun">
							<option value="">Pilih tahun</option>
							<template x-for="y in years" :key="y">
								<option :value="y" x-text="y"></option>
							</template>
						</select>
					</div>

					<div class="form-field">
						<span class="field-label">Harga</span>
						<p class="bullion-detail__price" x-text="formattedPrice"></p>
					</div>

					<button
						type="button"
						class="btn btn-whatsapp btn-block"
						:disabled="!canInquire"
						@click="canInquire && window.open( waLink, '_blank' )"
					>
						Inquire via WhatsApp
					</button>

					<p class="form-note" style="margin-top:16px;">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2 7h11v9H2z" stroke="#A08B4A" stroke-width="1.5" stroke-linejoin="round"/><path d="M13 10h4l4 3v3h-2" stroke="#A08B4A" stroke-width="1.5" stroke-linejoin="round"/><circle cx="6" cy="18" r="1.6" stroke="#A08B4A" stroke-width="1.5"/><circle cx="17" cy="18" r="1.6" stroke="#A08B4A" stroke-width="1.5"/><path d="M2 16h1M17 16h-4" stroke="#A08B4A" stroke-width="1.5"/></svg>
						<span>Diantar aman ke rumah Anda via Paxel</span>
					</p>
				</div>
			</div>

			<script>
				function verenaAntamDetail( config ) {
					return {
						rows: config.rows,
						waNumber: config.waNumber,
						gram: '',
						tahun: '',
						years: [ '2026', '2025', '2021-2024' ],
						get grams() {
							return this.rows.map( ( r ) => r.gram );
						},
						formatGram( g ) {
							return String( g ).replace( '.', ',' ) + ' gr';
						},
						get row() {
							return this.rows.find( ( r ) => Number( r.gram ) === Number( this.gram ) ) || null;
						},
						get price() {
							if ( ! this.row || ! this.tahun || ! this.row[ this.tahun ] ) {
								return null;
							}
							return this.row[ this.tahun ].sell;
						},
						get formattedPrice() {
							return this.price ? 'Rp' + this.price.toLocaleString( 'id-ID' ) : '—';
						},
						get canInquire() {
							return this.gram !== '' && this.tahun !== '' && this.price !== null;
						},
						get waLink() {
							if ( ! this.canInquire ) {
								return '';
							}
							const lines = [
								'Halo Verena Jewellery, saya ingin bertanya tentang Logam Mulia Antam.',
								'Gram: ' + this.formatGram( this.gram ),
								'Tahun: ' + this.tahun,
								'Harga: ' + this.formattedPrice,
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
