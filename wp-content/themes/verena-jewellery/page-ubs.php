<?php
/**
 * UBS bullion detail page. Create a WP Page titled "UBS", slug "ubs", with
 * Parent = "Logam Mulia" (so the URL becomes /logam-mulia/ubs/) — matches
 * this theme's page-{slug}.php template hierarchy convention, same as every
 * other page template.
 *
 * Same layout as page-antam.php, minus the Tahun (year) picker — UBS is
 * priced by gram only, with no year variants. The Gram dropdown and price
 * are driven entirely by the cached Google Sheet data
 * (verena_get_bullion_sheet_data()) — the exact same numbers shown in the
 * main Logam Mulia table, never a separate source.
 *
 * @package Verena_Jewellery
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$sheet    = verena_get_bullion_sheet_data();
$ubs_rows = $sheet['ubs'];

while ( have_posts() ) : the_post();
	?>
	<main class="container section">
		<div class="text-center section-narrow" style="margin-bottom:var(--space-4);">
			<p class="eyebrow">Emas Batangan</p>
			<h1><?php the_title(); ?></h1>
		</div>

		<?php if ( empty( $ubs_rows ) ) : ?>
			<div class="empty-state">
				<p>Harga sedang tidak tersedia. Silakan hubungi kami langsung untuk info stok.</p>
				<?php verena_whatsapp_button( verena_wa_message_bullion( 'UBS', '', 'beli' ), 'Tanya via WhatsApp' ); ?>
			</div>
		<?php else : ?>
			<div
				class="bullion-detail"
				x-data="verenaUbsDetail(<?php echo esc_attr( wp_json_encode( array( 'rows' => $ubs_rows, 'waNumber' => verena_whatsapp_number() ) ) ); ?>)"
			>
				<div class="bullion-detail__media">
					<div class="brand-badge" style="margin-bottom:var(--space-2);">
						<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/ubs-logo.png' ); ?>" alt="UBS" />
					</div>
					<img class="bullion-detail__photo" src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/ubs-bar-photo.png' ); ?>" alt="UBS Gold Bar" />
				</div>

				<div class="bullion-detail__form">
					<div class="form-field">
						<label for="ubs-gram">Gram</label>
						<select id="ubs-gram" x-model="gram">
							<option value="">Pilih gram</option>
							<template x-for="g in grams" :key="g">
								<option :value="g" x-text="formatGram(g)"></option>
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
				</div>
			</div>

			<script>
				function verenaUbsDetail( config ) {
					return {
						rows: config.rows,
						waNumber: config.waNumber,
						gram: '',
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
							return this.row ? this.row.sell : null;
						},
						get formattedPrice() {
							return this.price ? 'Rp' + this.price.toLocaleString( 'id-ID' ) : '—';
						},
						get canInquire() {
							return this.gram !== '' && this.price !== null;
						},
						get waLink() {
							if ( ! this.canInquire ) {
								return '';
							}
							const lines = [
								'Halo Verena Jewellery, saya ingin bertanya tentang Logam Mulia UBS.',
								'Gram: ' + this.formatGram( this.gram ),
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
