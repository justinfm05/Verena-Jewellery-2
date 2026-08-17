<?php
/**
 * Antam bullion detail page. Create a WP Page titled "Antam", slug "antam",
 * with Parent = "Logam Mulia" (so the URL becomes /logam-mulia/antam/) —
 * matches this theme's page-{slug}.php template hierarchy convention, same
 * as every other page template.
 *
 * Tipe/Gram dropdowns and the resulting price are driven entirely by the
 * cached Google Sheet data (verena_get_bullion_sheet_data()) — the exact
 * same numbers shown in the main Logam Mulia table, never a separate source.
 * Unlike the Logam Mulia overview table (which only ever shows 2026), this
 * checkout page exposes all 7 Antam types (2026/2025/2021-2024/Non RM
 * <2020/Antam Press Hijau/Antam Retro Tegak/Antam Retro Tidur) behind the
 * Tipe picker — a visitor has to actively choose a type before any of those
 * prices are shown, same "ask via the tool, not passively listed" idea as
 * the Jual Emas calculator.
 *
 * @package Verena_Jewellery
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$sheet      = verena_get_bullion_sheet_data();
$antam_rows = $sheet['antam'];

// Types offered in the Tipe picker, in display order — must match the keys
// verena_jt_bullion_parse_csv() puts on each Antam row (see
// inc/bullion-sheet-sync.php in the plugin).
$antam_types = array( '2026', '2025', '2021-2024', 'Non RM <2020', 'Antam Press Hijau', 'Antam Retro Tegak', 'Antam Retro Tidur' );

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

		<?php if ( empty( $antam_rows ) ) : ?>
			<div class="empty-state">
				<p>Harga sedang tidak tersedia. Silakan hubungi kami langsung untuk info stok.</p>
				<?php verena_whatsapp_button( verena_wa_message_bullion( 'Antam', '', 'beli' ), 'Tanya via WhatsApp' ); ?>
			</div>
		<?php else : ?>
			<div
				class="bullion-detail"
				x-data="verenaAntamDetail(<?php echo esc_attr( wp_json_encode( array( 'rows' => $antam_rows, 'types' => $antam_types, 'waNumber' => verena_whatsapp_number() ) ) ); ?>)"
			>
				<div class="bullion-detail__media">
					<div class="brand-badge" style="margin-bottom:var(--space-2);">
						<img src="<?php echo esc_url( verena_asset_url( 'assets/img/antam-logo.png' ) ); ?>" alt="Antam" />
					</div>
					<img class="bullion-detail__photo" src="<?php echo esc_url( verena_asset_url( 'assets/img/antam-bar-photo.jpg' ) ); ?>" alt="Antam Fine Gold 999.9 bar" />
				</div>

				<div class="bullion-detail__form">
					<div class="form-field">
						<label for="antam-tipe">Tipe</label>
						<select id="antam-tipe" x-model="tipe" @change="gram = ''">
							<option value="">Pilih tipe</option>
							<template x-for="t in types" :key="t">
								<option :value="t" x-text="t"></option>
							</template>
						</select>
					</div>

					<div class="form-field">
						<label for="antam-gram">Gram</label>
						<select id="antam-gram" x-model="gram" :disabled="! tipe">
							<option value="" x-text="tipe ? 'Pilih gram' : 'Pilih tipe dulu'"></option>
							<template x-for="g in grams" :key="g">
								<option :value="g" x-text="formatGram(g)"></option>
							</template>
						</select>
					</div>

					<div class="form-field">
						<label for="antam-qty">Jumlah</label>
						<select id="antam-qty" x-model="quantity">
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
				function verenaAntamDetail( config ) {
					return {
						rows: config.rows,
						types: config.types,
						waNumber: config.waNumber,
						tipe: '',
						gram: '',
						quantity: '',
						quantities: [ '1', '2', '3', '4', '5+' ],
						// Only grams that actually have a price under the chosen Tipe —
						// user picks Tipe first, Gram options depend on that choice.
						get grams() {
							if ( ! this.tipe ) { return []; }
							return this.rows
								.filter( ( r ) => r[ this.tipe ] && null !== r[ this.tipe ].sell )
								.map( ( r ) => r.gram );
						},
						formatGram( g ) {
							return String( g ).replace( '.', ',' ) + ' gr';
						},
						get row() {
							return this.rows.find( ( r ) => Number( r.gram ) === Number( this.gram ) ) || null;
						},
						get price() {
							if ( ! this.tipe || ! this.row || ! this.row[ this.tipe ] ) {
								return null;
							}
							return this.row[ this.tipe ].sell;
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
							return this.tipe !== '' && this.gram !== '' && this.quantity !== '' && this.price !== null;
						},
						get waLink() {
							if ( ! this.canInquire ) {
								return '';
							}
							const lines = [
								'Halo Verena Jewellery, saya ingin bertanya tentang Logam Mulia Antam.',
								'Tipe: ' + this.tipe,
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
					<h2>Tabel Harga Antam 2026</h2>
				</div>
				<div class="table-scroll" style="margin-bottom:8px;">
					<table class="price-table">
						<thead>
							<tr>
								<th>Gram</th>
								<th>2026 Jual</th>
								<th>2026 Buyback</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $antam_rows as $row ) : ?>
								<?php if ( null === $row['2026']['sell'] ) : continue; endif; ?>
								<tr>
									<td><?php echo esc_html( verena_format_grams( $row['gram'] ) ); ?></td>
									<td><?php echo esc_html( verena_format_idr( $row['2026']['sell'] ) ); ?></td>
									<td><?php echo null !== $row['2026']['buyback'] ? esc_html( verena_format_idr( $row['2026']['buyback'] ) ) : '—'; ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<?php
				$antam_changed_at = $sheet['changed_at']['antam'] ?? null;
				if ( $antam_changed_at ) :
					?>
					<p class="text-muted" style="font-size:0.85rem;">Harga terakhir diperbarui: <?php echo esc_html( wp_date( 'j F Y, H:i', $antam_changed_at, new DateTimeZone( 'Asia/Jakarta' ) ) . ' WIB' ); ?></p>
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
