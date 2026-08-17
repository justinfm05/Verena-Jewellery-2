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
 * tab as a single Rp-per-gram price (see verena_get_buyback_karat_data()).
 * Logam Mulia items price off the same cached bullion sheet data (and the
 * same buyback figures) as the Antam/Emasku/UBS bullion checkout pages —
 * brand+year selects which rows,
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
// (Kadar)" Google Sheet tab as a single Rp-per-gram price (see
// verena_get_buyback_karat_data() / inc/buyback-karat-sheet-sync.php) — a
// direct per-karat lookup.
$karat_sheet = verena_get_buyback_karat_data();
$karats      = $karat_sheet['karats'];

$karat_options = array();
foreach ( $karats as $label => $price ) {
	if ( null === $price ) {
		continue;
	}
	$karat_options[] = array(
		'label'   => $label,
		'price'   => $price,
		'display' => $label,
	);
}
// Customers who don't know their karat: priced as an indicative ~14K until
// checked for free in-store, same idea as the old "Tidak yakin" option.
if ( isset( $karats['14K'] ) && null !== $karats['14K'] ) {
	$karat_options[] = array(
		'label'   => 'Tidak Yakin (~14K)',
		'price'   => $karats['14K'],
		'display' => 'Tidak Yakin (~14K) — dicek gratis di toko',
	);
}
$default_purity = isset( $karats['17K'] ) ? '17K' : ( $karat_options[0]['label'] ?? '' ); // Most common gold in Indonesia (750/75%).

// Logam Mulia (bars): same cached Google Sheet data (and same buyback
// numbers, straight from the sheet's "Harga Terima" section) as the
// Antam/Emasku/UBS bullion checkout pages — never a separate source. Antam
// carries 7 type tiers per gram row; Emasku has one buyback figure per gram
// row (its own Baru/250+ tiering is already resolved sheet-side); UBS
// carries two — 'buyback' (UBS Baru) and 'buyback_retro' (UBS Retro) — read
// via buybackField below.
$bullion_sheet = verena_get_bullion_sheet_data();
$brand_options  = array(
	array( 'label' => 'Antam 2026', 'key' => 'antam', 'year' => '2026' ),
	array( 'label' => 'Antam 2025', 'key' => 'antam', 'year' => '2025' ),
	array( 'label' => 'Antam 2021-2024', 'key' => 'antam', 'year' => '2021-2024' ),
	array( 'label' => 'Antam Non RM <2020', 'key' => 'antam', 'year' => 'Non RM <2020' ),
	array( 'label' => 'Antam Press Hijau', 'key' => 'antam', 'year' => 'Antam Press Hijau' ),
	array( 'label' => 'Antam Retro Tegak', 'key' => 'antam', 'year' => 'Antam Retro Tegak' ),
	array( 'label' => 'Antam Retro Tidur', 'key' => 'antam', 'year' => 'Antam Retro Tidur' ),
	array( 'label' => 'Emasku', 'key' => 'emasku', 'year' => null ),
	array( 'label' => 'UBS Baru', 'key' => 'ubs', 'year' => null ),
	array( 'label' => 'UBS Retro', 'key' => 'ubs', 'year' => null, 'buybackField' => 'buyback_retro' ),
);

// 1-gram buyback price per brand, for the summary table next to the
// calculator — Antam only shows the current mint year (2026) for now; older
// year tiers aren't shown publicly. Full gram-by-gram pricing lives on the
// Logam Mulia page, linked below that table.
$bullion_1g_rows = array(
	'antam'  => null,
	'emasku' => null,
	'ubs'    => null,
);
foreach ( $bullion_sheet['antam'] ?? array() as $row ) {
	if ( 1 === (int) $row['gram'] ) {
		$bullion_1g_rows['antam'] = $row['2026']['buyback'] ?? null;
		break;
	}
}
foreach ( array( 'emasku', 'ubs' ) as $brand_key ) {
	foreach ( $bullion_sheet[ $brand_key ] ?? array() as $row ) {
		if ( 1 === (int) $row['gram'] ) {
			$bullion_1g_rows[ $brand_key ] = $row['buyback'] ?? null;
			break;
		}
	}
}
$bullion_1g_options = array(
	array( 'label' => 'Antam 2026', 'price' => $bullion_1g_rows['antam'] ),
	array( 'label' => 'Emasku', 'price' => $bullion_1g_rows['emasku'] ),
	array( 'label' => 'UBS', 'price' => $bullion_1g_rows['ubs'] ),
);
$bullion_changed_at = ! empty( $bullion_sheet['changed_at'] ) ? max( $bullion_sheet['changed_at'] ) : null;

// Bullion + Kadar reference tables, captured once and echoed in two spots —
// their normal position inside the calculator's right column (desktop/
// tablet), and again right below the intro text (phone only, hidden the
// other way around per breakpoint via CSS — see .lm-tables-mobile-top /
// .lm-tables-desktop). Captured ahead of the post loop since it only
// depends on the sheet data above, not the_post().
ob_start();
?>
<!-- Bullion 1-gram buyback summary; full gram-by-gram chart lives on the Logam Mulia page -->
<div class="table-scroll mb-2">
	<table class="price-table">
		<thead>
			<tr>
				<th>Logam Mulia (1gr)</th>
				<th>Harga Buyback</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $bullion_1g_options as $option ) : ?>
				<tr>
					<td><?php echo esc_html( $option['label'] ); ?></td>
					<td><?php echo null !== $option['price'] ? esc_html( verena_format_idr( $option['price'] ) ) : '—'; ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
<?php if ( $bullion_changed_at ) : ?>
	<p class="text-muted" style="font-size:0.85rem; margin:0 0 10px; padding-top:10px;">Harga terakhir diperbarui: <?php echo esc_html( wp_date( 'j F Y, H:i', $bullion_changed_at, new DateTimeZone( 'Asia/Jakarta' ) ) . ' WIB' ); ?></p>
<?php endif; ?>
<p style="margin:0 0 16px;">
	<a href="<?php echo esc_url( verena_wa_url( 'Halo Verena Jewellery, saya ingin tahu harga buyback LM' ) ); ?>" target="_blank" rel="noopener" class="btn btn-gold btn-sm">Harga Lengkap Lewat WhatsApp</a>
</p>

<?php if ( empty( $karat_options ) ) : ?>
	<p class="text-muted" style="margin-top:var(--space-3);">Harga belum tersedia saat ini.</p>
<?php else : ?>
	<div class="table-scroll" style="margin-top:var(--space-3); margin-bottom:8px;">
		<table class="price-table">
			<thead>
				<tr>
					<th>Kadar</th>
					<th>Harga Buyback</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $karat_options as $option ) : ?>
					<?php if ( 'Tidak Yakin (~14K)' === $option['label'] ) : continue; endif; ?>
					<tr>
						<td><?php echo esc_html( $option['label'] ); ?></td>
						<td><?php echo esc_html( verena_format_idr( $option['price'] ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
	// "Last changed" across all karats — matches the same karat prices
	// shown in the table above, not just the last time the sheet was
	// polled (see verena_get_buyback_karat_data()'s per-karat changed_at).
	$karat_changed_at = ! empty( $karat_sheet['changed_at'] ) ? max( $karat_sheet['changed_at'] ) : null;
	?>
	<?php if ( $karat_changed_at ) : ?>
		<p class="text-muted" style="font-size:0.85rem;">Harga terakhir diperbarui: <?php echo esc_html( wp_date( 'j F Y, H:i', $karat_changed_at, new DateTimeZone( 'Asia/Jakarta' ) ) . ' WIB' ); ?></p>
	<?php endif; ?>
<?php endif; ?>
<?php
$lm_kadar_tables_html = ob_get_clean();

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
		<div class="section-narrow text-center jual-emas-intro" style="margin-bottom:var(--space-3);">
			<p class="eyebrow">Jual Emas Anda</p>
			<h1><?php the_title(); ?></h1>
			<div class="stack"><?php the_content(); ?></div>
			<div class="jual-emas-why">
				<h3>Mengapa harus jual di Verena?</h3>
				<ol class="jual-emas-why__list">
					<li><strong>Harga terbaik</strong> sesuai harga pasar harian.</li>
					<li><strong>Aman, transparan, dan terpercaya</strong>: menggunakan mesin test kadar modern XRF dan Sigma.</li>
					<li><strong>Langsung dibayar cepat</strong> setelah pengecekan.</li>
				</ol>
			</div>
			<div class="jual-emas-jump-links">
				<a href="#jual-emas-calculator" class="btn btn-outline btn-sm">Hitung Total Nilai Emas Anda</a>
			</div>
		</div>

		<!-- Phone only — tables shown here, right below the intro; hidden on desktop/tablet (they render in their usual spot in the calculator's right column there). -->
		<div class="lm-tables-mobile-top"><?php echo $lm_kadar_tables_html; // phpcs:ignore -- trusted, built from esc_html()'d/esc_url()'d output above. ?></div>

		<?php if ( empty( $karat_options ) ) : ?>
			<div class="empty-state">
				<p>Estimator sedang tidak tersedia. Silakan hubungi kami langsung untuk penawaran.</p>
				<?php verena_whatsapp_button( 'Halo Verena Jewellery, saya ingin jual emas bekas.', 'Tanya via WhatsApp' ); ?>
			</div>
		<?php else : ?>

			<!-- Phone only — section title moved above the "how it works" steps; hidden on desktop/tablet (it renders in its usual spot right above the calculator there). -->
			<div class="calc-title-divider calc-title-divider--mobile" id="jual-emas-calculator">
				<span class="calc-title-divider__line"></span>
				<h3>Hitung Total Nilai Emas Anda</h3>
				<span class="calc-title-divider__line"></span>
			</div>

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

			<!-- Home pickup partnership -->
			<div class="calc-edu pickup-partner" style="background:linear-gradient(135deg, #FFF1E2 0%, #F7C9A2 50%, #EFA97A 100%); border-color:rgba(239,169,122,0.45);">
				<div class="delivery-journey" style="justify-content:center;" aria-hidden="true">
					<div class="delivery-journey__icon">
						<svg width="34" height="34" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 11l8-7 8 7" stroke="#E8B54A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M6 10v9h12v-9" stroke="#E8B54A" stroke-width="1.5" stroke-linejoin="round"/><path d="M10 19v-5h4v5" stroke="#E8B54A" stroke-width="1.5" stroke-linejoin="round"/></svg>
					</div>
					<span class="delivery-journey__line"></span>
					<div class="delivery-journey__icon">
						<svg width="34" height="34" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2 7h11v9H2z" stroke="#E8B54A" stroke-width="1.5" stroke-linejoin="round"/><path d="M13 10h4l4 3v3h-2" stroke="#E8B54A" stroke-width="1.5" stroke-linejoin="round"/><circle cx="6" cy="18" r="1.6" stroke="#E8B54A" stroke-width="1.5"/><circle cx="17" cy="18" r="1.6" stroke="#E8B54A" stroke-width="1.5"/><path d="M2 16h1M17 16h-4" stroke="#E8B54A" stroke-width="1.5"/></svg>
					</div>
					<span class="delivery-journey__line"></span>
					<div class="delivery-journey__icon">
						<?php
						$logo_mark_file = 'verena-logo-mark-black.png';
						$logo_mark_path = get_template_directory() . '/assets/img/' . $logo_mark_file;
						?>
						<?php if ( file_exists( $logo_mark_path ) ) : ?>
							<span
								class="icon-mask verena-logo-icon"
								role="img"
								aria-label="Verena Jewellery"
								style="background-color:#E8B54A; -webkit-mask-image:url(<?php echo esc_url( verena_asset_url( 'assets/img/' . $logo_mark_file ) ); ?>); mask-image:url(<?php echo esc_url( verena_asset_url( 'assets/img/' . $logo_mark_file ) ); ?>);"
							></span>
						<?php else : ?>
							<img src="<?php echo esc_url( verena_asset_url( 'assets/img/verena-logo-stacked.png' ) ); ?>" alt="Verena Jewellery" class="verena-logo-icon" style="object-fit:contain;" />
						<?php endif; ?>
					</div>
				</div>
				<h3 class="text-center" style="margin:0 0 16px; font-style:italic; font-size:clamp(1.5rem, 2.6vw, 2.1rem); font-weight:500; color:var(--ink);">Kami Jemput Langsung dari Rumah Anda</h3>
				<p class="text-center text-muted" style="margin:0 auto; max-width:520px; font-size:15px; font-style:normal;">Aman dan transparan. Emas Anda dapat dijemput dari rumah dengan asuransi sampai 100jt rupiah.</p>
				<p class="text-center text-muted" style="margin:8px auto 0; max-width:520px; font-size:12.5px; font-style:italic;">*hanya berlaku untuk pengiriman dari pulau jawa dan sekelilingnya</p>
			</div>

			<div class="calc-title-divider calc-title-divider--desktop">
				<span class="calc-title-divider__line"></span>
				<h3>Hitung Total Nilai Emas Anda</h3>
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
											<select x-model="item.purity_label" x-init="$nextTick(() => { $el.value = item.purity_label; })">
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
											<select x-model="item.brand" @change="if ( ! lmGrams( item ).some( ( g ) => Number( g ) === Number( item.gram ) ) ) { item.gram = ''; }">
												<option value="">Pilih merek</option>
												<template x-for="option in brandOptions" :key="option.label">
													<option :value="option.label" x-text="option.label"></option>
												</template>
											</select>
										</div>
										<div class="form-field mb-0">
											<label>Berat (gram)</label>
											<select x-model="item.gram" :disabled="! item.brand">
												<option value="" x-text="item.brand ? 'Pilih' : 'Pilih merek dulu'"></option>
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
								<span class="calc-item__value" x-text="formatRp(valueFor(item))"></span>
							</div>
						</div>
					</template>

					<button type="button" class="btn btn-outline calc-add" @click="addItem()" x-show="items.length < maxItems">+ Tambah Emas</button>

					<!-- Total + WhatsApp, below the item list -->
					<div class="calc-summary-card">
						<h3>Estimasi Nilai Emas Anda</h3>
						<p class="calc-sub"><span x-text="validItems.length"></span> item &middot; harga buyback hari ini</p>
						<div class="calc-total-big" x-text="formattedTotal"></div>
						<p class="calc-subtotals">
							Perhiasan: <strong x-text="formatRp(subtotalPerhiasan)"></strong><br>
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

				<!-- RIGHT: bullion + karat price reference tables, formatted like the Logam Mulia tables. Hidden on phone (they show right below the intro instead — see .lm-tables-mobile-top above). -->
				<div class="calc-summary lm-tables-desktop">
					<?php echo $lm_kadar_tables_html; // phpcs:ignore -- trusted, built from esc_html()'d/esc_url()'d output above. ?>
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
						// Which field on a row holds the buyback figure for a given
						// Merek option: a year-tiered sub-object (Antam's 7 types), an
						// alternate top-level field (UBS Retro's buyback_retro), or the
						// plain 'buyback' field (Emasku, UBS Baru).
						buybackFor( option, row ) {
							if ( option.year ) { return row[ option.year ] ? row[ option.year ].buyback : null; }
							if ( option.buybackField ) { return row[ option.buybackField ] ?? null; }
							return row.buyback;
						},
						// Rows for the item's selected brand+type, filtered to ones with
						// a real buyback figure — same rows shown on the Antam/Emasku/UBS
						// bullion checkout pages.
						lmRows( item ) {
							const option = this.brandOptions.find( ( o ) => o.label === item.brand );
							if ( ! option ) { return []; }
							const rows = this.bullion[ option.key ] || [];
							return rows.filter( ( r ) => {
								const buyback = this.buybackFor( option, r );
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
							const perUnit = this.buybackFor( option, row );
							if ( null === perUnit || undefined === perUnit ) { return 0; }
							return Math.round( perUnit * qty );
						},
						gramsFor( item ) {
							if ( item.category === 'lm' ) {
								return ( parseFloat( item.gram ) || 0 ) * ( parseInt( item.qty ) || 0 );
							}
							return parseFloat( item.weight_grams ) || 0;
						},
						// Perhiasan: direct per-karat Rp-per-gram lookup from the "Jual Emas
						// (Kadar)" sheet. Logam Mulia items use lmValue() directly.
						valueFor( item ) {
							if ( item.category === 'lm' ) {
								return this.lmValue( item );
							}
							const grams = this.gramsFor( item );
							const option = this.purityOptions.find( ( o ) => o.label === item.purity_label );
							if ( grams <= 0 || ! option || null === option.price ) {
								return 0;
							}
							return Math.round( grams * option.price );
						},
						get validItems() { return this.items.filter( ( it ) => this.gramsFor( it ) > 0 ); },
						get total() {
							return this.validItems.reduce( ( sum, it ) => sum + this.valueFor( it ), 0 );
						},
						get subtotalPerhiasan() {
							return this.validItems.filter( ( it ) => it.category !== 'lm' ).reduce( ( sum, it ) => sum + this.valueFor( it ), 0 );
						},
						get subtotalLm() { return this.validItems.filter( ( it ) => it.category === 'lm' ).reduce( ( s, it ) => s + this.lmValue( it ), 0 ); },
						formatRp( n ) { return 'Rp' + ( n || 0 ).toLocaleString( 'id-ID' ); },
						get formattedTotal() { return this.formatRp( this.total ); },

						get sellWaLink() {
							const lines = [ 'Halo Verena Jewellery, saya ingin jual emas berikut:' ];
							const maxLines = 10;
							this.validItems.slice( 0, maxLines ).forEach( ( it ) => {
								const cat = it.category === 'lm' ? 'Logam Mulia' : 'Perhiasan';
								const detail = it.category === 'lm'
									? ( ( it.gram || '?' ) + ' gr x' + ( it.qty || 1 ) )
									: ( this.gramsFor( it ) + ' gr, kadar ' + it.purity_label );
								const desc = it.category === 'lm' ? ( it.brand || cat ) : ( it.description || cat );
								lines.push( '- ' + desc + ' (' + cat + '): ' + detail + ' (~' + this.formatRp( this.valueFor( it ) ) + ')' );
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
