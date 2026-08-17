<?php
/**
 * Emasku bullion detail page. Create a WP Page titled "Emasku", slug
 * "emasku", with Parent = "Logam Mulia" (so the URL becomes
 * /logam-mulia/emasku/) — matches this theme's page-{slug}.php template
 * hierarchy convention, same as every other page template.
 *
 * Same layout as page-antam.php, minus the Tahun (year) picker — Emasku is
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

$sheet       = verena_get_bullion_sheet_data();
$emasku_rows = $sheet['emasku'];

while ( have_posts() ) : the_post();
	?>
	<main class="container section">
		<a href="<?php echo esc_url( verena_page_url( 'bullion' ) ); ?>" class="bullion-back-link">
			<svg width="30" height="30" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M15 6l-6 6 6 6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
			<span>Kembali</span>
		</a>
		<div class="text-center section-narrow" style="margin-bottom:var(--space-4);">
			<p class="eyebrow">Emas Batangan</p>
			<h1><?php the_title(); ?></h1>
		</div>

		<?php if ( empty( $emasku_rows ) ) : ?>
			<div class="empty-state">
				<p>Harga sedang tidak tersedia. Silakan hubungi kami langsung untuk info stok.</p>
				<?php verena_whatsapp_button( verena_wa_message_bullion( 'Emasku', '', 'beli' ), 'Tanya via WhatsApp' ); ?>
			</div>
		<?php else : ?>
			<div
				class="bullion-detail"
				x-data="verenaEmaskuDetail(<?php echo esc_attr( wp_json_encode( array( 'rows' => $emasku_rows, 'waNumber' => verena_whatsapp_number() ) ) ); ?>)"
			>
				<div class="bullion-detail__media">
					<div class="brand-badge" style="margin-bottom:var(--space-2);">
						<img src="<?php echo esc_url( verena_asset_url( 'assets/img/emasku-logo.png' ) ); ?>" alt="Emasku" />
					</div>
					<img class="bullion-detail__photo" src="<?php echo esc_url( verena_asset_url( 'assets/img/emasku-bar-photo.png' ) ); ?>" alt="Emasku Fine Gold 999.9 bar" />
				</div>

				<div class="bullion-detail__form">
					<div class="form-field">
						<label for="emasku-gram">Gram</label>
						<select id="emasku-gram" x-model="gram">
							<option value="">Pilih gram</option>
							<template x-for="g in grams" :key="g">
								<option :value="g" x-text="formatGram(g)"></option>
							</template>
						</select>
					</div>

					<div class="form-field">
						<label for="emasku-qty">Jumlah</label>
						<select id="emasku-qty" x-model="quantity">
							<option value="">Pilih jumlah</option>
							<template x-for="q in quantities" :key="q">
								<option :value="q" x-text="q"></option>
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
						<span>Diantar aman ke rumah Anda</span>
					</p>
				</div>
			</div>

			<script>
				function verenaEmaskuDetail( config ) {
					return {
						rows: config.rows,
						waNumber: config.waNumber,
						gram: '',
						quantity: '',
						quantities: [ '1', '2', '3', '4', '5+' ],
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
							if ( null === this.price ) { return '—'; }
							if ( '' === this.quantity ) { return 'Rp' + this.price.toLocaleString( 'id-ID' ) + '/pcs'; }
							const qty   = ( '5+' === this.quantity ) ? 5 : parseInt( this.quantity );
							const total = this.price * qty;
							const suffix = ( '5+' === this.quantity ) ? '+' : '';
							return 'Rp' + total.toLocaleString( 'id-ID' ) + suffix + ' (Rp' + this.price.toLocaleString( 'id-ID' ) + '/pcs)';
						},
						get canInquire() {
							return this.gram !== '' && this.quantity !== '' && this.price !== null;
						},
						get waLink() {
							if ( ! this.canInquire ) {
								return '';
							}
							const lines = [
								'Halo Verena Jewellery, saya ingin bertanya tentang Logam Mulia Emasku.',
								'Gram: ' + this.formatGram( this.gram ),
								'Jumlah: ' + this.quantity,
								'Harga: ' + this.formattedPrice,
							];
							return 'https://wa.me/' + this.waNumber + '?text=' + encodeURIComponent( lines.join( '\n' ) );
						},
					};
				}
			</script>

			<div class="price-history" style="margin-top:var(--space-5);">
				<div class="band__head" style="margin-bottom:24px;">
					<span class="eyebrow">Daftar Harga</span>
					<h2>Tabel Harga Emasku</h2>
				</div>
				<div class="table-scroll" style="margin-bottom:8px;">
					<table class="price-table">
						<thead>
							<tr>
								<th>Gram</th>
								<th>Harga Jual</th>
								<th>Harga Buyback</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $emasku_rows as $row ) : ?>
								<tr>
									<td><?php echo esc_html( verena_format_grams( $row['gram'] ) ); ?></td>
									<td><?php echo null !== $row['sell'] ? esc_html( verena_format_idr( $row['sell'] ) ) : '—'; ?></td>
									<td><?php echo null !== $row['buyback'] ? esc_html( verena_format_idr( $row['buyback'] ) ) : '—'; ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<?php
				$emasku_changed_at = $sheet['changed_at']['emasku'] ?? null;
				if ( $emasku_changed_at ) :
					?>
					<p class="text-muted" style="font-size:0.85rem;">Harga terakhir diperbarui: <?php echo esc_html( wp_date( 'j F Y, H:i', $emasku_changed_at, new DateTimeZone( 'Asia/Jakarta' ) ) . ' WIB' ); ?></p>
				<?php endif; ?>
			</div>

			<div class="text-center" style="margin-top:24px;">
				<a href="<?php echo esc_url( verena_page_url( 'bullion' ) ); ?>" class="btn btn-outline">&larr; Kembali ke Logam Mulia</a>
			</div>
		<?php endif; ?>
	</main>
	<?php
endwhile;
get_footer();
