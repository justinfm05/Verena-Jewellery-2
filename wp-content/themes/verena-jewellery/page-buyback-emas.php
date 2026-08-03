<?php
/**
 * Jual Emas (buyback) page. Create a WP Page with slug "buyback-emas".
 *
 * Full multi-item gold net-worth estimator — jewellery (incl. scrap/rusak,
 * any karat 1K-24K) and logam mulia (bullion bars, 24K) — mirroring the
 * calculator on page-gold-calculator.php. Deliberately duplicated here
 * rather than shared, so this page can be edited in isolation from that one.
 *
 * Saving an estimate and messaging WhatsApp are two DECOUPLED actions: every
 * saved estimate becomes a lead in wp-admin > Verena Jewellery > Gold
 * Calculator Leads regardless of whether the customer ever opens WhatsApp,
 * and the WhatsApp button itself is never gated on anything — clicking it
 * always works, and opportunistically saves a lead in the background first
 * if contact details are already filled in.
 *
 * @package Verena_Jewellery
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$buyback_rate  = verena_get_current_buyback_rate();
$rate_per_gram = $buyback_rate ? (int) $buyback_rate['price_per_gram'] : 0;

// Full 1K-24K karat list for the Perhiasan dropdown, same curated set as the
// gold net-worth calculator (page-gold-calculator.php) — labelled by KARAT
// with the gold % as a helper, preferring any purity the shop set in
// wp-admin over the plain N/24 derivation.
$stored_bps = array();
foreach ( verena_get_purity_options() as $o ) {
	$stored_bps[ $o['label'] ] = (int) $o['fraction_bps'];
}

// "91,6" style percent (Indonesian comma, trailing ",0" trimmed).
$pct_fmt = function ( $bps ) {
	return rtrim( rtrim( number_format( floor( $bps / 10 ) / 10, 1, ',', '.' ), '0' ), ',' );
};

$grade_defs = array(
	array( 'value' => '24K' ),
	array( 'value' => '22K' ),
	array( 'value' => '21K' ),
	array( 'value' => '18K' ),
	array( 'value' => '17K' ),
	array( 'value' => '16K' ),
	array( 'value' => '14K' ),
	array( 'value' => '9K' ),
);

$karat_options = array();
foreach ( $grade_defs as $g ) {
	$k   = (int) rtrim( $g['value'], 'K' );
	$bps = $stored_bps[ $g['value'] ] ?? (int) round( $k / 24 * 10000 );
	$karat_options[] = array(
		'label'        => $g['value'],
		'fraction_bps' => $bps,
		'display'      => $g['value'] . ' · ' . $pct_fmt( $bps ) . '% emas',
	);
}
if ( isset( $stored_bps['Tidak yakin / belum dicek'] ) ) {
	$karat_options[] = array(
		'label'        => 'Tidak yakin / belum dicek',
		'fraction_bps' => $stored_bps['Tidak yakin / belum dicek'],
		'display'      => 'Belum tahu kadarnya — dicek gratis di toko',
	);
}

$fraction24     = ( $stored_bps['24K'] ?? 9999 ) / 10000; // Logam Mulia = 24K.
$default_purity = '17K'; // Most common gold in Indonesia (750/75%).

$initial_items = array(
	array(
		'category'     => 'perhiasan',
		'description'  => '',
		'weight_grams' => '',
		'purity_label' => $default_purity,
		'denom'        => '',
		'qty'          => 1,
	),
);

$config = array(
	'ratePerGram'   => $rate_per_gram,
	'fraction24'    => $fraction24,
	'purityOptions' => $karat_options,
	'defaultPurity' => $default_purity,
	'denomOptions'  => array( 0.5, 1, 2, 3, 5, 10, 25, 50, 100 ),
	'weightPresets' => array(
		array( 'label' => 'Cincin', 'grams' => 3 ),
		array( 'label' => 'Kalung', 'grams' => 8 ),
		array( 'label' => 'Gelang', 'grams' => 10 ),
		array( 'label' => 'Anting (sepasang)', 'grams' => 2 ),
		array( 'label' => 'Liontin', 'grams' => 3 ),
	),
	'waNumber'      => verena_whatsapp_number(),
	'restUrl'       => esc_url_raw( rest_url( 'verena/v1/calculator' ) ),
	'nonce'         => wp_create_nonce( 'verena_calculator' ),
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
			<p class="text-muted">Tambahkan perhiasan (termasuk emas patah/rongsokan) atau logam mulia Anda untuk melihat estimasi nilainya di kadar berapa pun — lalu simpan estimasi Anda dan/atau chat langsung dengan Verena lewat WhatsApp.</p>
		</div>

		<?php if ( ! $buyback_rate ) : ?>
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
					<p><strong>Isi berat (gram) dan pilih kadar.</strong> Tidak tahu kadarnya? Pilih <em>“Belum tahu”</em> — kami cek gratis di toko.</p>
				</div>
				<div class="calc-howto__step">
					<span class="calc-howto__num">3</span>
					<p><strong>Simpan estimasi Anda</strong>, lalu chat kami via WhatsApp kapan pun Anda siap melanjutkan.</p>
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
					<p>Kalau masih ragu, pilih <strong>“Belum tahu”</strong> saat menambah emas — kadar dan beratnya akan kami periksa <strong>gratis</strong> di toko sebelum harga final.</p>
				</div>
			</details>

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

							<!-- Logam Mulia (bars): denomination x qty, 24K -->
							<template x-if="item.category === 'lm'">
								<div>
									<div class="calc-fields calc-fields--triple">
										<div class="form-field mb-0">
											<label>Merek / Deskripsi</label>
											<input type="text" x-model="item.description" placeholder="cth. Antam, UBS">
										</div>
										<div class="form-field mb-0">
											<label>Ukuran (gram)</label>
											<select x-model.number="item.denom">
												<option value="">Pilih</option>
												<template x-for="d in denomOptions" :key="d">
													<option :value="d" x-text="d + ' gram'"></option>
												</template>
											</select>
										</div>
										<div class="form-field mb-0">
											<label>Jumlah</label>
											<input type="number" min="1" step="1" x-model.number="item.qty" placeholder="1">
										</div>
									</div>
									<p class="calc-hint">Emas murni 24K (999). Ukuran tertera di keping &amp; sertifikat, jadi nilainya pasti.</p>
								</div>
							</template>

							<div class="calc-item__foot">
								<span class="calc-item__foot-label">Perkiraan nilai</span>
								<span class="calc-item__value" x-text="formatRp(estimateFor(item))"></span>
							</div>
						</div>
					</template>

					<button type="button" class="btn btn-outline calc-add" @click="addItem()" x-show="items.length < maxItems">+ Tambah Emas</button>
				</div>

				<!-- RIGHT: sticky total + lead capture + WhatsApp -->
				<div class="calc-summary">
					<div class="calc-summary-card">
						<h3>Estimasi Nilai Emas Anda</h3>
						<p class="calc-sub"><span x-text="validItems.length"></span> item &middot; harga buyback hari ini</p>
						<div class="calc-total-big" x-text="formattedTotal"></div>
						<p class="calc-subtotals">
							Perhiasan: <strong x-text="formatRp(subtotalPerhiasan)"></strong> &middot;
							Logam Mulia: <strong x-text="formatRp(subtotalLm)"></strong>
						</p>
						<p class="calc-disclaimer" x-text="disclaimer"></p>

						<div class="calc-save">
							<h4>Simpan Estimasi Anda</h4>
							<p style="font-size:12px;color:var(--champagne-dim);margin:0 0 10px;line-height:1.5;">Agar tim kami bisa menghubungi Anda untuk estimasi final.</p>
							<div class="form-field mb-0">
								<input type="text" x-model="contactName" placeholder="Nama Anda">
							</div>
							<div class="form-field mb-0">
								<input type="text" inputmode="tel" x-model="contactWa" placeholder="No. WhatsApp Anda (cth. 08123456789)">
							</div>
							<div class="honeypot-field" aria-hidden="true">
								<input type="text" x-model="website" tabindex="-1" autocomplete="off">
							</div>
							<p x-show="!contactValid" style="font-size:12px;color:var(--champagne-dim);margin:0 0 10px;line-height:1.5;">Isi nama &amp; nomor WhatsApp aktif Anda untuk menyimpan estimasi ini.</p>
							<button type="button" class="btn btn-outline-light btn-block" @click="saveEstimate()" :disabled="saving" x-text="saving ? 'Menyimpan...' : 'Simpan Estimasi Saya'"></button>
							<p x-show="saveError" x-text="saveError" style="color:#ffb3a3;font-size:0.8rem;margin-top:0.5rem;"></p>
							<p x-show="saved && !saveError" style="color:#bfe3c8;font-size:0.8rem;margin-top:0.5rem;">Estimasi tersimpan — tim kami dapat menghubungi Anda.</p>
							<p style="font-size:11px;color:var(--olive-2);margin-top:8px;line-height:1.5;">Data Anda hanya digunakan tim Verena untuk menindaklanjuti estimasi ini.</p>
							<div class="share-link-box mt-4" x-show="shareUrl" x-cloak>
								<code x-text="shareUrl" style="font-size:0.8rem;word-break:break-all;"></code>
								<button type="button" class="btn btn-outline-light btn-sm" @click="copyLink()" x-text="copied ? 'Tersalin!' : 'Salin'"></button>
							</div>
						</div>

						<div class="calc-save">
							<p style="font-size:12px;color:var(--champagne-dim);margin:0 0 10px;line-height:1.5;">Ingin proses lebih cepat? Chat kami langsung dan kirim foto emas Anda yang sudah ditimbang.</p>
							<a class="btn btn-gold btn-block" target="_blank" rel="noopener" :href="sellWaLink" @click="notifyWaClick()">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12c0 1.87.5 3.62 1.38 5.13L2 22l5.05-1.32A9.94 9.94 0 0012 22c5.52 0 10-4.48 10-10S17.52 2 12 2z"/></svg>
								Chat Verena &amp; Kirim Foto Emas Anda
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
							denom: item.denom || '',
							qty: item.qty || 1,
						} ) ),
						purityOptions: config.purityOptions,
						defaultPurity: config.defaultPurity,
						denomOptions: config.denomOptions,
						weightPresets: config.weightPresets,
						ratePerGram: config.ratePerGram,
						fraction24: config.fraction24,
						waNumber: config.waNumber,
						restUrl: config.restUrl,
						nonce: config.nonce,
						disclaimer: config.disclaimer,
						maxItems: config.maxItems,
						slug: '',
						contactName: '',
						contactWa: '',
						website: '',
						saving: false,
						saved: false,
						saveError: '',
						shareUrl: '',
						copied: false,

						addItem() {
							if ( this.items.length >= this.maxItems ) return;
							this.items.push( { category: 'perhiasan', description: '', weight_grams: '', purity_label: this.defaultPurity, denom: '', qty: 1 } );
						},
						removeItem( index ) { this.items.splice( index, 1 ); },
						applyPreset( item, event ) {
							const v = parseFloat( event.target.value );
							if ( v > 0 ) { item.weight_grams = v; }
							event.target.value = '';
						},
						gramsFor( item ) {
							if ( item.category === 'lm' ) {
								return ( parseFloat( item.denom ) || 0 ) * ( parseInt( item.qty ) || 0 );
							}
							return parseFloat( item.weight_grams ) || 0;
						},
						fractionFor( item ) {
							if ( item.category === 'lm' ) { return this.fraction24; }
							const p = this.purityOptions.find( ( o ) => o.label === item.purity_label );
							return p ? p.fraction_bps / 10000 : 0;
						},
						estimateFor( item ) {
							const grams = this.gramsFor( item );
							const fraction = this.fractionFor( item );
							if ( grams <= 0 || fraction <= 0 || ! this.ratePerGram ) { return 0; }
							return Math.round( grams * fraction * this.ratePerGram / 100 ) * 100;
						},
						get validItems() { return this.items.filter( ( it ) => this.gramsFor( it ) > 0 ); },
						get total() { return this.validItems.reduce( ( sum, it ) => sum + this.estimateFor( it ), 0 ); },
						get subtotalPerhiasan() { return this.validItems.filter( ( it ) => it.category !== 'lm' ).reduce( ( s, it ) => s + this.estimateFor( it ), 0 ); },
						get subtotalLm() { return this.validItems.filter( ( it ) => it.category === 'lm' ).reduce( ( s, it ) => s + this.estimateFor( it ), 0 ); },
						formatRp( n ) { return 'Rp' + ( n || 0 ).toLocaleString( 'id-ID' ); },
						get formattedTotal() { return this.formatRp( this.total ); },

						itemToPayload( it ) {
							const grams = this.gramsFor( it );
							const label = it.category === 'lm' ? '24K' : it.purity_label;
							const prefix = it.category === 'lm' ? '[Logam Mulia] ' : '[Perhiasan] ';
							let desc = prefix + ( it.description || ( it.category === 'lm' ? 'Emas batangan' : 'Perhiasan' ) );
							if ( it.category === 'lm' && it.denom ) { desc += ' ' + it.denom + 'gr x' + ( it.qty || 1 ); }
							return { description: desc, weight_grams: grams, purity_label: label };
						},

						get waDigitsOnly() { return ( this.contactWa || '' ).replace( /\D/g, '' ); },
						get contactValid() {
							return this.contactName.trim().length > 0
								&& this.waDigitsOnly.length >= 9 && this.waDigitsOnly.length <= 15;
						},

						get sellWaLink() {
							const lines = [ 'Halo Verena Jewellery, saya ingin jual emas berikut:' ];
							const maxLines = 10;
							this.validItems.slice( 0, maxLines ).forEach( ( it ) => {
								const cat = it.category === 'lm' ? 'Logam Mulia' : 'Perhiasan';
								const detail = it.category === 'lm'
									? ( ( it.denom || '?' ) + ' gr x' + ( it.qty || 1 ) + ', 24K' )
									: ( this.gramsFor( it ) + ' gr, kadar ' + it.purity_label );
								const desc = it.description || cat;
								lines.push( '- ' + desc + ' (' + cat + '): ' + detail + ' (~' + this.formatRp( this.estimateFor( it ) ) + ')' );
							} );
							if ( this.validItems.length > maxLines ) {
								lines.push( '...dan ' + ( this.validItems.length - maxLines ) + ' item lainnya.' );
							}
							lines.push( '' );
							lines.push( 'Estimasi total: ' + this.formattedTotal );
							if ( this.contactName.trim() ) { lines.push( 'Nama: ' + this.contactName.trim() ); }
							if ( this.shareUrl ) { lines.push( 'Daftar lengkap: ' + this.shareUrl ); }
							lines.push( '' );
							lines.push( 'Berikut emas saya — saya akan kirimkan foto beserta berat & kadar yang sudah saya catat.' );
							lines.push( '' );
							lines.push( this.disclaimer );
							return 'https://wa.me/' + this.waNumber + '?text=' + encodeURIComponent( lines.join( '\n' ) );
						},

						async persistEstimate() {
							if ( this.validItems.length === 0 || ! this.contactValid ) {
								return false;
							}
							this.saving = true;
							this.saveError = '';
							const payload = {
								items: this.validItems.map( ( it ) => this.itemToPayload( it ) ),
								contact_name: this.contactName,
								contact_whatsapp: this.contactWa,
								security: this.nonce,
								website: this.website,
							};
							const url = this.slug ? this.restUrl + '/' + this.slug : this.restUrl;
							const method = this.slug ? 'PUT' : 'POST';
							const FRIENDLY_ERRORS = {
								verena_no_items: 'Tambahkan minimal satu barang emas sebelum menyimpan.',
								verena_no_valid_items: 'Periksa kembali berat/kadar barang Anda — ada yang belum valid.',
								verena_rate_limited: 'Terlalu banyak percobaan dari perangkat ini. Coba lagi sebentar lagi, atau hubungi kami langsung via WhatsApp.',
								verena_no_rate: 'Harga buyback sedang tidak tersedia. Coba lagi sebentar lagi.',
								verena_invalid_nonce: 'Halaman ini sudah lama terbuka. Muat ulang halaman lalu coba lagi.',
							};
							try {
								const response = await fetch( url, { method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify( payload ) } );
								const data = await response.json();
								if ( ! response.ok ) {
									throw new Error( FRIENDLY_ERRORS[ data.code ] || 'Terjadi kesalahan saat menyimpan. Silakan coba lagi.' );
								}
								this.slug = data.slug;
								this.shareUrl = data.shareable_url;
								this.saved = true;
								return true;
							} catch ( error ) {
								this.saveError = error instanceof TypeError
									? 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda dan coba lagi.'
									: error.message;
								return false;
							} finally {
								this.saving = false;
							}
						},

						async saveEstimate() {
							this.saveError = '';
							if ( this.validItems.length === 0 ) {
								this.saveError = 'Tambahkan minimal satu barang emas dengan berat yang valid.';
								return;
							}
							if ( ! this.contactValid ) {
								return;
							}
							await this.persistEstimate();
						},

						notifyWaClick() {
							// Best-effort, non-blocking: if contact info is already
							// valid, capture the lead in the background without
							// delaying the WhatsApp navigation this click triggers.
							if ( this.contactValid && this.validItems.length > 0 ) {
								this.persistEstimate();
							}
						},

						copyLink() {
							navigator.clipboard.writeText( this.shareUrl ).then( () => {
								this.copied = true;
								setTimeout( () => { this.copied = false; }, 2000 );
							} );
						},
					};
				}
			</script>
		<?php endif; ?>
	</main>
	<?php
endwhile;
get_footer();
