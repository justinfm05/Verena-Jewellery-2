<?php
/**
 * Jual Emas (buyback) page. Create a WP Page with slug "buyback-emas".
 *
 * Full multi-item gold net-worth estimator — jewellery (incl. scrap/rusak,
 * any karat 1K-24K) and logam mulia (bullion bars, 24K) — mirroring the
 * calculator on page-gold-calculator.php. Deliberately duplicated here
 * rather than shared, so this page can be edited in isolation from that one.
 *
 * Perhiasan items price directly off the "Jual Emas (Kadar)" Google Sheet
 * tab as a lower/upper Rp-per-gram range (see
 * verena_get_buyback_karat_data()). Logam Mulia items price off the same
 * cached bullion sheet data (and the same buyback figures) as the
 * Antam/Emasku/UBS bullion checkout pages — brand+year selects which rows,
 * gram selects the row, never a separate/typed-in source. The WhatsApp
 * button is the only call to action — no separate "save estimate" /
 * lead-capture step.
 *
 * @package Verena_Jewellery
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

// Perhiasan (jewellery) karat prices come directly from the "Jual Emas
// (Kadar)" Google Sheet tab as a lower/upper Rp-per-gram range (see
// verena_get_buyback_karat_data() / inc/buyback-karat-sheet-sync.php) — a
// direct per-karat lookup.
$karat_sheet = verena_get_buyback_karat_data();
$karats      = $karat_sheet['karats'];

$karat_options = array();
foreach ( $karats as $label => $range ) {
	if ( null === $range['lower'] && null === $range['upper'] ) {
		continue;
	}
	$karat_options[] = array(
		'label'   => $label,
		'lower'   => $range['lower'],
		'upper'   => $range['upper'],
		'display' => $label,
	);
}
// Customers who don't know their karat: priced as an indicative ~14K until
// checked for free in-store, same idea as the old "Tidak yakin" option.
if ( isset( $karats['14K'] ) ) {
	$karat_options[] = array(
		'label'   => 'Tidak Yakin (~14K)',
		'lower'   => $karats['14K']['lower'],
		'upper'   => $karats['14K']['upper'],
		'display' => 'Tidak Yakin (~14K) — dicek gratis di toko',
	);
}
$default_purity = isset( $karats['17K'] ) ? '17K' : ( $karat_options[0]['label'] ?? '' ); // Most common gold in Indonesia (750/75%).

// Logam Mulia (bars): same cached Google Sheet data (and same buyback
// numbers) as the Antam/Emasku/UBS bullion checkout pages — never a
// separate source. Antam carries three year tiers per gram row; Emasku/UBS
// have one buyback figure per gram row.
$bullion_sheet = verena_get_bullion_sheet_data();
$brand_options  = array(
	array(
		'label' => 'Antam 2026',
		'key'   => 'antam',
		'year'  => '2026',
	),
	array(
		'label' => 'Antam 2025',
		'key'   => 'antam',
		'year'  => '2025',
	),
	array(
		'label' => 'Antam 2021-2024',
		'key'   => 'antam',
		'year'  => '2021-2024',
	),
	array(
		'label' => 'Emasku',
		'key'   => 'emasku',
		'year'  => null,
	),
	array(
		'label' => 'UBS',
		'key'   => 'ubs',
		'year'  => null,
	),
);

$initial_items = array(
	array(
		'category'     => 'perhiasan',
		'description'  => '',
		'weight_grams' => '',
		'purity_label' => $default_purity,
		'brand'        => '',
		'gram'         => '',
		'qty'          => 1,
	),
);

$config = array(
	'brandOptions'  => $brand_options,
	'bullion'       => array(
		'antam'  => $bullion_sheet['antam'],
		'emasku' => $bullion_sheet['emasku'],
		'ubs'    => $bullion_sheet['ubs'],
	),
	'purityOptions' => $karat_options,
	'defaultPurity' => $default_purity,
	'weightPresets' => array(
		array( 'label' => 'Cincin', 'grams' => 3 ),
		array( 'label' => 'Kalung', 'grams' => 8 ),
		array( 'label' => 'Gelang', 'grams' => 10 ),
		array( 'label' => 'Anting (sepasang)', 'grams' => 2 ),
		array( 'label' => 'Liontin', 'grams' => 3 ),
	),
	'waNumber'      => verena_whatsapp_number(),
	'initialItems'  => $initial_items,
	'disclaimer'    => verena_estimate_disclaimer(),
	'maxItems'      => VERENA_JT_MAX_ITEMS_PER_LIST,
);

while ( have_posts() ) : the_post();
	?>
	<main class="container section">
		<div class="section-narrow text-center" style="margin-bottom:var(--space-3);">
			<p class="eyebrow">Jual Emas Anda</p>
			<h1><?php the_title(); ?></h1>
			<div class="stack"><?php the_content(); ?></div>
			<p class="text-muted">Tambahkan perhiasan (termasuk emas patah/rongsokan) atau logam mulia Anda untuk melihat estimasi nilainya di kadar berapa pun — lalu chat langsung dengan Verena lewat WhatsApp.</p>
		</div>

		<?php if ( empty( $karat_options ) ) : ?>
			<div class="empty-state">
				<p>Estimator sedang tidak tersedia. Silakan hubungi kami langsung untuk penawaran.</p>
				<?php verena_whatsapp_button( 'Halo Verena Jewellery, saya ingin jual emas bekas.', 'Tanya via WhatsApp' ); ?>
			</div>
		<?php else : ?>

			<!-- How to use (3 simple steps) -->
			<div class="calc-howto">
				<div class="calc-howto__step">
					<span class="calc-howto__num">1</span>
					<p><strong>Tambahkan emas Anda</strong> — perhiasan, emas rusak/rongsokan, atau logam mulia. Bisa lebih dari satu.</p>
				</div>
				<div class="calc-howto__step">
					<span class="calc-howto__num">2</span>
					<p><strong>Isi berat (gram) dan pilih kadar.</strong> Tidak tahu kadarnya? Pilih <em>“Tidak Yakin (~14K)”</em> — kami cek gratis di toko.</p>
				</div>
				<div class="calc-howto__step">
					<span class="calc-howto__num">3</span>
					<p><strong>Lihat estimasi Anda</strong>, lalu chat kami via WhatsApp kapan pun Anda siap melanjutkan.</p>
				</div>
			</div>

			<!-- What is karat? (collapsible so it never clutters the tool) -->
			<details class="calc-edu">
				<summary>
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="#C9A24B" stroke-width="1.6"/><path d="M9.5 9a2.5 2.5 0 0 1 4.9.6c0 1.7-2.4 2-2.4 3.4" stroke="#C9A24B" stroke-width="1.6" stroke-linecap="round"/><circle cx="12" cy="17" r="1" fill="#C9A24B"/></svg>
					Apa itu “kadar / karat”, dan bagaimana cek kadar emas saya?
				</summary>
				<div class="calc-edu__body">
					<p><strong>Karat (K)</strong> menunjukkan seberapa murni emas Anda. Makin tinggi karatnya, makin banyak kandungan emas murninya — sisanya campuran logam lain agar lebih kuat. Contoh: <strong>24K ≈ 99,9% emas</strong>, <strong>22K = 91,6%</strong>, <strong>18K = 75%</strong>.</p>
					<p><strong>Cara cek kadar emas Anda:</strong> lihat cap/tanda kecil di bagian dalam perhiasan (mis. di dalam cincin atau ujung gelang). Di Indonesia biasanya tertera angka seperti ini:</p>
					<table class="karat-ref">
						<thead><tr><th>Cap di emas</th><th>Karat</th><th>Kandungan emas</th></tr></thead>
						<tbody>
							<tr><td>999 / 99.9</td><td>24K</td><td>99,9%</td></tr>
							<tr><td>916 / 91.6</td><td>22K</td><td>91,6%</td></tr>
							<tr><td>875</td><td>21K</td><td>87,5%</td></tr>
							<tr><td>750</td><td>18K &amp; 17K</td><td>75%</td></tr>
							<tr><td>700</td><td>16K</td><td>70%</td></tr>
							<tr><td>375</td><td>9K</td><td>37,5%</td></tr>
						</tbody>
					</table>
					<p>Kalau masih ragu, pilih <strong>“Tidak Yakin (~14K)”</strong> saat menambah emas — kadar dan beratnya akan kami periksa <strong>gratis</strong> di toko sebelum harga final.</p>
				</div>
			</details>

			<!-- Paxel pickup partnership -->
			<div class="calc-edu" style="padding:24px 20px;">
				<div class="delivery-journey" style="justify-content:center;" aria-hidden="true">
					<div class="delivery-journey__icon">
						<svg width="34" height="34" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 11l8-7 8 7" stroke="#C9A24B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M6 10v9h12v-9" stroke="#C9A24B" stroke-width="1.5" stroke-linejoin="round"/><path d="M10 19v-5h4v5" stroke="#C9A24B" stroke-width="1.5" stroke-linejoin="round"/></svg>
					</div>
					<span class="delivery-journey__line"></span>
					<div class="delivery-journey__icon">
						<svg width="34" height="34" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2 7h11v9H2z" stroke="#C9A24B" stroke-width="1.5" stroke-linejoin="round"/><path d="M13 10h4l4 3v3h-2" stroke="#C9A24B" stroke-width="1.5" stroke-linejoin="round"/><circle cx="6" cy="18" r="1.6" stroke="#C9A24B" stroke-width="1.5"/><circle cx="17" cy="18" r="1.6" stroke="#C9A24B" stroke-width="1.5"/><path d="M2 16h1M17 16h-4" stroke="#C9A24B" stroke-width="1.5"/></svg>
					</div>
					<span class="delivery-journey__line"></span>
					<div class="delivery-journey__icon">
						<img src="<?php echo esc_url( verena_asset_url( 'assets/img/verena-logo-stacked.png' ) ); ?>" alt="Verena Jewellery" style="width:36px; height:36px; object-fit:contain;" />
					</div>
				</div>
				<h3 class="text-center" style="margin:0 0 10px;">Kami Jemput Langsung dari Rumah Anda</h3>
				<p class="text-center text-muted" style="margin:0;">Verena Jewellery bekerja sama dengan Paxel untuk menjemput emas Anda dengan aman, langsung dari rumah Anda — tanpa perlu repot datang ke toko.</p>
			</div>

			<div class="calc-title-divider">
				<span class="calc-title-divider__line"></span>
				<h3>Hitung Nilai Total Emas Anda</h3>
				<span class="calc-title-divider__line"></span>
			</div>

			<div x-data="verenaBuybackCalculator(<?php echo esc_attr( wp_json_encode( $config ) ); ?>)" class="calc-layout">

				<!-- LEFT: items -->
				<div class="calc-items">
					<template x-for="(item, index) in items" :key="index">
						<div class="calc-item">
							<div class="calc-item__head">
								<div class="calc-cat">
									<button type="button" :class="{ 'is-active': item.category === 'perhiasan' }" @click="item.category = 'perhiasan'">Perhiasan</button>
									<button type="button" :class="{ 'is-active': item.category === 'lm' }" @click="item.category = 'lm'">Logam Mulia</button>
								</div>
								<button type="button" class="calc-remove" @click="removeItem(index)" x-show="items.length > 1" aria-label="Hapus item">&times;</button>
							</div>

							<!-- Perhiasan (jewellery, incl. scrap): weight + karat -->
							<template x-if="item.category === 'perhiasan'">
								<div>
									<div class="calc-fields calc-fields--triple">
										<div class="form-field mb-0">
											<label>Deskripsi</label>
											<input type="text" x-model="item.description" placeholder="cth. Kalung, cincin patah, emas rongsokan">
										</div>
										<div class="form-field mb-0">
											<label>Berat (gram)</label>
											<input type="number" min="0" step="0.01" x-model.number="item.weight_grams" placeholder="0">
										</div>
										<div class="form-field mb-0">
											<label>Kadar Emas</label>
											<select x-model="item.purity_label">
												<template x-for="option in purityOptions" :key="option.label">
													<option :value="option.label" x-text="option.display"></option>
												</template>
											</select>
										</div>
									</div>
									<p class="calc-hint">
										Tidak tahu beratnya? Timbang dengan timbangan digital, cek nota/sertifikat, atau perkirakan:
										<select @change="applyPreset(item, $event)" style="margin-left:6px;padding:4px 8px;font-size:12px;width:auto;display:inline-block;">
											<option value="">— perkirakan —</option>
											<template x-for="p in weightPresets" :key="p.label">
												<option :value="p.grams" x-text="p.label + ' (~' + p.grams + ' gr)'"></option>
											</template>
										</select>
										<br>Berat &amp; kadar final ditimbang &amp; diuji gratis di toko.
									</p>
								</div>
							</template>

							<!-- Logam Mulia (bars): brand+year -> gram (buyback lookup) x qty -->
							<template x-if="item.category === 'lm'">
								<div>
									<div class="calc-fields calc-fields--triple">
										<div class="form-field mb-0">
											<label>Merek</label>
											<select x-model="item.brand" @change="item.gram = ''">
												<option value="">Pilih merek</option>
												<template x-for="option in brandOptions" :key="option.label">
													<option :value="option.label" x-text="option.label"></option>
												</template>
											</select>
										</div>
										<div class="form-field mb-0">
											<label>Ukuran (gram)</label>
											<select x-model="item.gram">
												<option value="">Pilih</option>
												<template x-for="g in lmGrams(item)" :key="g">
													<option :value="g" x-text="formatGram(g) + ' gr'"></option>
												</template>
											</select>
										</div>
										<div class="form-field mb-0">
											<label>Jumlah</label>
											<input type="number" min="1" step="1" x-model.number="item.qty" placeholder="1">
										</div>
									</div>
									<p class="calc-hint">Harga buyback diambil langsung dari harga resmi hari ini, sama seperti halaman Logam Mulia kami — jadi nilainya pasti.</p>
								</div>
							</template>

							<div class="calc-item__foot">
								<span class="calc-item__foot-label">Perkiraan nilai</span>
								<span class="calc-item__value" x-text="formatRange(rangeFor(item))"></span>
							</div>
						</div>
					</template>

					<button type="button" class="btn btn-outline calc-add" @click="addItem()" x-show="items.length < maxItems">+ Tambah Emas</button>
				</div>

				<!-- RIGHT: sticky total + WhatsApp -->
				<div class="calc-summary">
					<div class="calc-summary-card">
						<h3>Estimasi Nilai Emas Anda</h3>
						<p class="calc-sub"><span x-text="validItems.length"></span> item &middot; harga buyback hari ini</p>
						<div class="calc-total-big" x-text="formattedTotal"></div>
						<p class="calc-subtotals">
							Perhiasan: <strong x-text="formatRange(subtotalPerhiasanRange)"></strong><br>
							Logam Mulia: <strong x-text="formatRp(subtotalLm)"></strong>
						</p>
						<p class="calc-disclaimer" x-text="disclaimer"></p>

						<div class="calc-save">
							<a class="btn btn-gold btn-block" target="_blank" rel="noopener" :href="sellWaLink">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12c0 1.87.5 3.62 1.38 5.13L2 22l5.05-1.32A9.94 9.94 0 0012 22c5.52 0 10-4.48 10-10S17.52 2 12 2z"/></svg>
								Jual Emas via WhatsApp
							</a>
						</div>
					</div>
				</div>
			</div>

			<script>
				function verenaBuybackCalculator( config ) {
					return {
						items: config.initialItems.map( ( item ) => ( {
							category: item.category || 'perhiasan',
							description: item.description || '',
							weight_grams: item.weight_grams || '',
							purity_label: item.purity_label || config.defaultPurity,
							brand: item.brand || '',
							gram: item.gram || '',
							qty: item.qty || 1,
						} ) ),
						purityOptions: config.purityOptions,
						defaultPurity: config.defaultPurity,
						brandOptions: config.brandOptions,
						bullion: config.bullion,
						weightPresets: config.weightPresets,
						waNumber: config.waNumber,
						disclaimer: config.disclaimer,
						maxItems: config.maxItems,

						addItem() {
							if ( this.items.length >= this.maxItems ) return;
							this.items.push( { category: 'perhiasan', description: '', weight_grams: '', purity_label: this.defaultPurity, brand: '', gram: '', qty: 1 } );
						},
						removeItem( index ) { this.items.splice( index, 1 ); },
						applyPreset( item, event ) {
							const v = parseFloat( event.target.value );
							if ( v > 0 ) { item.weight_grams = v; }
							event.target.value = '';
						},
						formatGram( g ) { return String( g ).replace( '.', ',' ); },
						// Rows for the item's selected brand+year (Emasku/UBS have no
						// year dimension), filtered to ones with a real buyback figure —
						// same rows shown on the Antam/Emasku/UBS bullion checkout pages.
						lmRows( item ) {
							const option = this.brandOptions.find( ( o ) => o.label === item.brand );
							if ( ! option ) { return []; }
							const rows = this.bullion[ option.key ] || [];
							return rows.filter( ( r ) => {
								const buyback = option.year ? ( r[ option.year ] ? r[ option.year ].buyback : null ) : r.buyback;
								return null !== buyback && undefined !== buyback;
							} );
						},
						lmGrams( item ) { return this.lmRows( item ).map( ( r ) => r.gram ); },
						lmValue( item ) {
							const option = this.brandOptions.find( ( o ) => o.label === item.brand );
							const qty = parseInt( item.qty ) || 0;
							if ( ! option || '' === item.gram || qty <= 0 ) { return 0; }
							const row = this.lmRows( item ).find( ( r ) => Number( r.gram ) === Number( item.gram ) );
							if ( ! row ) { return 0; }
							const perUnit = option.year ? row[ option.year ].buyback : row.buyback;
							if ( null === perUnit || undefined === perUnit ) { return 0; }
							return Math.round( perUnit * qty / 100 ) * 100;
						},
						gramsFor( item ) {
							if ( item.category === 'lm' ) {
								return ( parseFloat( item.gram ) || 0 ) * ( parseInt( item.qty ) || 0 );
							}
							return parseFloat( item.weight_grams ) || 0;
						},
						// Perhiasan: direct per-karat lower/upper Rp-per-gram lookup from the
						// "Jual Emas (Kadar)" sheet — a real range, not a computed fraction.
						// Logam Mulia items report the same single value as both ends of the
						// range so totals can sum both categories uniformly.
						rangeFor( item ) {
							if ( item.category === 'lm' ) {
								const val = this.lmValue( item );
								return { low: val, high: val };
							}
							const grams = this.gramsFor( item );
							const option = this.purityOptions.find( ( o ) => o.label === item.purity_label );
							if ( grams <= 0 || ! option || null === option.lower || null === option.upper ) {
								return { low: 0, high: 0 };
							}
							const round100 = ( n ) => Math.round( n / 100 ) * 100;
							return {
								low: round100( grams * option.lower ),
								high: round100( grams * option.upper ),
							};
						},
						get validItems() { return this.items.filter( ( it ) => this.gramsFor( it ) > 0 ); },
						get totalRange() {
							return this.validItems.reduce( ( acc, it ) => {
								const r = this.rangeFor( it );
								acc.low += r.low;
								acc.high += r.high;
								return acc;
							}, { low: 0, high: 0 } );
						},
						get subtotalPerhiasanRange() {
							return this.validItems.filter( ( it ) => it.category !== 'lm' ).reduce( ( acc, it ) => {
								const r = this.rangeFor( it );
								acc.low += r.low;
								acc.high += r.high;
								return acc;
							}, { low: 0, high: 0 } );
						},
						get subtotalLm() { return this.validItems.filter( ( it ) => it.category === 'lm' ).reduce( ( s, it ) => s + this.lmValue( it ), 0 ); },
						formatRp( n ) { return 'Rp' + ( n || 0 ).toLocaleString( 'id-ID' ); },
						formatRange( range ) {
							return range.low === range.high ? this.formatRp( range.low ) : this.formatRp( range.low ) + ' – ' + this.formatRp( range.high );
						},
						get formattedTotal() { return this.formatRange( this.totalRange ); },

						get sellWaLink() {
							const lines = [ 'Halo Verena Jewellery, saya ingin jual emas berikut:' ];
							const maxLines = 10;
							this.validItems.slice( 0, maxLines ).forEach( ( it ) => {
								const cat = it.category === 'lm' ? 'Logam Mulia' : 'Perhiasan';
								const detail = it.category === 'lm'
									? ( ( it.gram || '?' ) + ' gr x' + ( it.qty || 1 ) )
									: ( this.gramsFor( it ) + ' gr, kadar ' + it.purity_label );
								const desc = it.category === 'lm' ? ( it.brand || cat ) : ( it.description || cat );
								lines.push( '- ' + desc + ' (' + cat + '): ' + detail + ' (~' + this.formatRange( this.rangeFor( it ) ) + ')' );
							} );
							if ( this.validItems.length > maxLines ) {
								lines.push( '...dan ' + ( this.validItems.length - maxLines ) + ' item lainnya.' );
							}
							lines.push( '' );
							lines.push( 'Estimasi total: ' + this.formattedTotal );
							lines.push( '' );
							lines.push( 'Berikut emas saya — saya akan kirimkan foto beserta berat & kadar yang sudah saya catat.' );
							lines.push( '' );
							lines.push( this.disclaimer );
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
