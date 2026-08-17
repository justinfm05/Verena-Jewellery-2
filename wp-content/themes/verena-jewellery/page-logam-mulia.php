<?php
/**
 * Bullion (Logam Mulia) overview page. Create a WP Page with slug "logam-mulia"
 * — this template shows a logo strip for the three brands plus one price
 * table per brand, all inline on this single page.
 *
 * Prices come from the shop's shared Google Sheet, synced every 5 minutes by
 * the Verena Jewellery Tools plugin (see inc/bullion-sheet-sync.php in the
 * plugin) — this template only ever reads the cached result, never fetches
 * the sheet live itself.
 *
 * @package Verena_Jewellery
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
while ( have_posts() ) : the_post();

	$brands = array(
		'Antam'  => 'antam-logo.png',
		'Emasku' => 'emasku-logo.png',
		'UBS'    => 'ubs-logo.png',
	);

	$sheet = verena_get_bullion_sheet_data();

	// Price-history graph — 1-gram sell price per brand, logged once a day by
	// verena_jt_record_bullion_price_history() each time the sheet syncs (see
	// bullion-sheet-sync.php in the plugin). Brands don't always share the
	// exact same set of recorded dates (a brand's price row can occasionally
	// be missing from a given sync), so build one shared date axis first and
	// let each brand's series fill in null for any day it's missing.
	$price_history = verena_get_bullion_price_history( 90 );
	$history_dates = array();
	foreach ( $price_history as $series ) {
		foreach ( $series as $point ) {
			$history_dates[ $point['date'] ] = true;
		}
	}
	$history_dates = array_keys( $history_dates );
	sort( $history_dates );

	$history_series_meta = array(
		'antam_2026' => array( 'label' => 'Antam 2026', 'color' => '#C9A24B' ),
		'emasku'     => array( 'label' => 'Emasku', 'color' => '#1E2A1E' ),
		'ubs'        => array( 'label' => 'UBS', 'color' => '#A08B4A' ),
	);
	$history_chart_data = array(
		'labels'   => array_map( function ( $date ) { return wp_date( 'd/m', strtotime( $date ) ); }, $history_dates ),
		'datasets' => array(),
	);
	foreach ( $history_series_meta as $key => $meta ) {
		$by_date = array();
		foreach ( $price_history[ $key ] as $point ) {
			$by_date[ $point['date'] ] = $point['price'];
		}
		$history_chart_data['datasets'][] = array(
			'label' => $meta['label'],
			'color' => $meta['color'],
			'data'  => array_map( function ( $date ) use ( $by_date ) { return $by_date[ $date ] ?? null; }, $history_dates ),
		);
	}
	$has_price_history = count( $history_dates ) >= 2;
	?>
	<main class="container section">
		<div class="text-center section-narrow lm-intro" style="margin-bottom:var(--space-4);">
			<p class="eyebrow">Emas Batangan</p>
			<h1><?php the_title(); ?></h1>
			<div class="text-muted"><?php the_content(); ?></div>
		</div>

		<div class="price-history">
			<div class="band__head" style="margin-bottom:24px;">
				<span class="eyebrow">Tren Harga</span>
				<h2>Histori Harga Emas Batangan</h2>
				<p>Pergerakan harga jual per gram (denominasi 1 gram) 90 hari terakhir.</p>
			</div>
			<?php if ( $has_price_history ) : ?>
				<div class="price-history__canvas-wrap">
					<canvas id="bullion-price-history" data-history="<?php echo esc_attr( wp_json_encode( $history_chart_data ) ); ?>"></canvas>
				</div>
			<?php else : ?>
				<p class="text-muted text-center">Grafik histori harga akan mulai terisi setelah beberapa hari data harga tercatat.</p>
			<?php endif; ?>
		</div>

		<?php if ( $has_price_history ) : ?>
			<script>
				document.addEventListener( 'DOMContentLoaded', function () {
					var canvas = document.getElementById( 'bullion-price-history' );
					if ( ! canvas || typeof Chart === 'undefined' ) return;
					var chartData = JSON.parse( canvas.dataset.history );
					new Chart( canvas.getContext( '2d' ), {
						type: 'line',
						data: {
							labels: chartData.labels,
							datasets: chartData.datasets.map( function ( ds ) {
								return {
									label: ds.label,
									data: ds.data,
									borderColor: ds.color,
									backgroundColor: ds.color,
									tension: 0.3,
									spanGaps: true,
									pointRadius: 2,
								};
							} ),
						},
						options: {
							responsive: true,
							maintainAspectRatio: false,
							plugins: { legend: { position: 'bottom' } },
							scales: {
								y: {
									ticks: {
										callback: function ( value ) { return 'Rp ' + Number( value ).toLocaleString( 'id-ID' ); }
									}
								}
							}
						}
					} );
				} );
			</script>
		<?php endif; ?>

		<?php
		$bullion_slug = verena_page_slug( 'bullion' );
		$brand_urls   = array();
		foreach ( array(
			'Antam'  => 'antam',
			'Emasku' => 'emasku',
			'UBS'    => 'ubs',
		) as $brand_name => $brand_slug ) {
			$brand_page               = get_page_by_path( $bullion_slug . '/' . $brand_slug );
			$brand_urls[ $brand_name ] = $brand_page ? get_permalink( $brand_page ) : verena_page_url( 'bullion', $brand_slug . '/' );
		}
		?>
		<p><strong>Beli Sekarang:</strong></p>
		<div class="brand-strip">
			<?php foreach ( $brands as $name => $logo_file ) : ?>
				<?php if ( isset( $brand_urls[ $name ] ) ) : ?>
					<a class="brand-badge" href="<?php echo esc_url( $brand_urls[ $name ] ); ?>">
						<img src="<?php echo esc_url( verena_asset_url( 'assets/img/' . $logo_file ) ); ?>" alt="<?php echo esc_attr( $name ); ?>" />
					</a>
				<?php else : ?>
					<div class="brand-badge">
						<img src="<?php echo esc_url( verena_asset_url( 'assets/img/' . $logo_file ) ); ?>" alt="<?php echo esc_attr( $name ); ?>" />
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>

		<p class="form-note" style="margin-bottom:var(--space-3);">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2 7h11v9H2z" stroke="#A08B4A" stroke-width="1.5" stroke-linejoin="round"/><path d="M13 10h4l4 3v3h-2" stroke="#A08B4A" stroke-width="1.5" stroke-linejoin="round"/><circle cx="6" cy="18" r="1.6" stroke="#A08B4A" stroke-width="1.5"/><circle cx="17" cy="18" r="1.6" stroke="#A08B4A" stroke-width="1.5"/><path d="M2 16h1M17 16h-4" stroke="#A08B4A" stroke-width="1.5"/></svg>
			<span>Diantar aman ke rumah Anda</span>
		</p>

		<p class="text-muted">Harga kami terjamin kompetitif, beli sekarang di Verena Jewellery. Info harga emas akan terkini:</p>

		<?php
		// One gram-indexed table across all three brands. Grams line up
		// across brands except Emasku's 250/500/1000 tier, which Antam/UBS
		// don't carry (confirmed against the sheet — those rows are simply
		// absent from $sheet['antam']/$sheet['ubs'], not present with a null
		// price) — union every gram that appears in ANY brand's rows, then
		// look each brand up by that gram; a brand with no row at a given
		// gram shows "-", never 0 or a blank cell that could be misread as
		// free. Antam only shows the 2026 type here, same as before — older
		// year/type tiers are still on the Antam checkout page.
		// Keyed by (string) gram, not (float) gram — PHP silently truncates
		// float array keys to int (0.5 would collide with a hypothetical 0.x
		// row at key 0), so every lookup below uses the string form to stay
		// exact regardless of what gram tiers the sheet ever adds.
		$combined_grams = array();
		foreach ( array( 'antam', 'emasku', 'ubs' ) as $key ) {
			foreach ( $sheet[ $key ] as $row ) {
				$combined_grams[ (string) $row['gram'] ] = (float) $row['gram'];
			}
		}
		$combined_grams = array_values( $combined_grams );
		sort( $combined_grams, SORT_NUMERIC );

		$antam_by_gram = array();
		foreach ( $sheet['antam'] as $row ) {
			$antam_by_gram[ (string) $row['gram'] ] = $row;
		}
		$emasku_by_gram = array();
		foreach ( $sheet['emasku'] as $row ) {
			$emasku_by_gram[ (string) $row['gram'] ] = $row;
		}
		$ubs_by_gram = array();
		foreach ( $sheet['ubs'] as $row ) {
			$ubs_by_gram[ (string) $row['gram'] ] = $row;
		}

		$combined_last_updated = null;
		foreach ( array( 'antam', 'emasku', 'ubs' ) as $key ) {
			$brand_changed_at = $sheet['changed_at'][ $key ] ?? null;
			if ( $brand_changed_at ) {
				$combined_last_updated = max( $combined_last_updated ?? 0, $brand_changed_at );
			}
		}
		?>
		<?php if ( empty( $combined_grams ) ) : ?>
			<p class="text-muted">Harga belum tersedia saat ini.</p>
		<?php else : ?>
			<div class="table-scroll" style="margin-bottom:8px;">
				<table class="price-table price-table--combined">
					<thead>
						<tr>
							<th rowspan="2">Gram</th>
							<th colspan="2" class="price-table--group-start">Antam 2026</th>
							<th colspan="2" class="price-table--group-start">Emasku</th>
							<th colspan="2" class="price-table--group-start">UBS</th>
						</tr>
						<tr>
							<th class="price-table--group-start">Jual</th>
							<th>Buyback</th>
							<th class="price-table--group-start">Jual</th>
							<th>Buyback</th>
							<th class="price-table--group-start">Jual</th>
							<th>Buyback</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $combined_grams as $gram ) : ?>
							<?php
							$antam_row      = $antam_by_gram[ (string) $gram ] ?? null;
							$emasku_row     = $emasku_by_gram[ (string) $gram ] ?? null;
							$ubs_row        = $ubs_by_gram[ (string) $gram ] ?? null;
							$antam_sell     = $antam_row['2026']['sell'] ?? null;
							$antam_buyback  = $antam_row['2026']['buyback'] ?? null;
							$emasku_sell    = $emasku_row['sell'] ?? null;
							$emasku_buyback = $emasku_row['buyback'] ?? null;
							$ubs_sell       = $ubs_row['sell'] ?? null;
							$ubs_buyback    = $ubs_row['buyback'] ?? null;
							?>
							<tr>
								<td><?php echo esc_html( verena_format_grams( $gram ) ); ?></td>
								<td class="price-table--group-start"><?php echo null !== $antam_sell ? esc_html( verena_format_idr( $antam_sell ) ) : '-'; ?></td>
								<td><?php echo null !== $antam_buyback ? esc_html( verena_format_idr( $antam_buyback ) ) : '-'; ?></td>
								<td class="price-table--group-start"><?php echo null !== $emasku_sell ? esc_html( verena_format_idr( $emasku_sell ) ) : '-'; ?></td>
								<td><?php echo null !== $emasku_buyback ? esc_html( verena_format_idr( $emasku_buyback ) ) : '-'; ?></td>
								<td class="price-table--group-start"><?php echo null !== $ubs_sell ? esc_html( verena_format_idr( $ubs_sell ) ) : '-'; ?></td>
								<td><?php echo null !== $ubs_buyback ? esc_html( verena_format_idr( $ubs_buyback ) ) : '-'; ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php if ( $combined_last_updated ) : ?>
				<p class="text-muted" style="font-size:0.85rem;">Harga terakhir diperbarui: <?php echo esc_html( wp_date( 'j F Y, H:i', $combined_last_updated, new DateTimeZone( 'Asia/Jakarta' ) ) . ' WIB' ); ?></p>
			<?php endif; ?>
			<p class="text-muted" style="font-size:0.85rem;">Antam 2026 di atas hanya menampilkan tipe termuda &mdash; tahun/tipe lain (2025, 2021-2024, Non RM, Press Hijau, Retro) tersedia di halaman <a href="<?php echo esc_url( $brand_urls['Antam'] ?? verena_page_url( 'bullion', 'antam/' ) ); ?>">Antam</a>.</p>
		<?php endif; ?>

		<div class="price-table-head mt-4">
			<h3 style="margin:0;">Antam</h3>
			<div style="display:flex; gap:8px; flex-wrap:wrap;">
				<?php if ( isset( $brand_urls['Antam'] ) ) : ?>
					<a href="<?php echo esc_url( $brand_urls['Antam'] ); ?>" class="btn btn-gold btn-sm">Beli LM Antam</a>
				<?php endif; ?>
				<?php if ( isset( $brand_urls['Antam'] ) ) : ?>
					<a href="<?php echo esc_url( $brand_urls['Antam'] ); ?>" class="btn btn-outline btn-sm">Beli LM Antam Tahun Lama</a>
				<?php endif; ?>
			</div>
		</div>
		<?php if ( empty( $sheet['antam'] ) ) : ?>
			<p class="text-muted">Harga belum tersedia saat ini.</p>
		<?php else : ?>
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
						<?php foreach ( $sheet['antam'] as $row ) : ?>
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
		<?php endif; ?>

		<?php foreach ( array( 'Emasku' => 'emasku', 'UBS' => 'ubs' ) as $label => $key ) : ?>
			<div class="price-table-head mt-4">
				<h3 style="margin:0;"><?php echo esc_html( $label ); ?></h3>
				<div style="display:flex; gap:8px; flex-wrap:wrap;">
					<?php if ( isset( $brand_urls[ $label ] ) ) : ?>
						<a href="<?php echo esc_url( $brand_urls[ $label ] ); ?>" class="btn btn-gold btn-sm">Beli LM <?php echo esc_html( $label ); ?></a>
					<?php endif; ?>
				</div>
			</div>
			<?php if ( empty( $sheet[ $key ] ) ) : ?>
				<p class="text-muted">Harga belum tersedia saat ini.</p>
			<?php else : ?>
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
							<?php foreach ( $sheet[ $key ] as $row ) : ?>
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
				$brand_changed_at_individual = $sheet['changed_at'][ $key ] ?? null;
				if ( $brand_changed_at_individual ) :
					?>
					<p class="text-muted" style="font-size:0.85rem;">Harga terakhir diperbarui: <?php echo esc_html( wp_date( 'j F Y, H:i', $brand_changed_at_individual, new DateTimeZone( 'Asia/Jakarta' ) ) . ' WIB' ); ?></p>
				<?php endif; ?>
			<?php endif; ?>
		<?php endforeach; ?>
	</main>
	<?php
endwhile;
get_footer();
