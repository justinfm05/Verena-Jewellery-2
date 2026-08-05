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

	// Per brand, not a single shared timestamp — each only moves when that
	// brand's own prices actually change (see changed_at in
	// verena_get_bullion_sheet_data()), not on every sheet sync. Always shown
	// in Jakarta time regardless of the site's own configured WordPress
	// timezone setting, since the shop and "WIB" label are fixed.
	$updated_labels = array();
	foreach ( array( 'antam', 'emasku', 'ubs' ) as $key ) {
		$changed_at             = $sheet['changed_at'][ $key ] ?? null;
		$updated_labels[ $key ] = $changed_at
			? wp_date( 'j F Y, H:i', $changed_at, new DateTimeZone( 'Asia/Jakarta' ) ) . ' WIB'
			: null;
	}
	?>
	<main class="container section">
		<div class="text-center section-narrow" style="margin-bottom:var(--space-4);">
			<p class="eyebrow">Emas Batangan</p>
			<h1><?php the_title(); ?></h1>
			<div class="text-muted"><?php the_content(); ?></div>
		</div>

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
			<span>Diantar aman ke rumah Anda via Paxel</span>
		</p>

		<p class="text-muted">Harga kami terjamin paling murah, beli sekarang di Verena Jewellery. Info harga emas akan terkini:</p>

		<h3 class="mt-4">Antam</h3>
		<?php if ( empty( $sheet['antam'] ) ) : ?>
			<p class="text-muted">Harga belum tersedia saat ini.</p>
		<?php else : ?>
			<div class="table-scroll mb-4">
				<table class="price-table">
					<thead>
						<tr>
							<th>Gram</th>
							<th>2026 Jual</th>
							<th>2026 Buyback</th>
							<th>2025 Jual</th>
							<th>2025 Buyback</th>
							<th>2021-2024 Jual</th>
							<th>2021-2024 Buyback</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $sheet['antam'] as $row ) : ?>
							<tr>
								<td><?php echo esc_html( verena_format_grams( $row['gram'] ) ); ?></td>
								<?php foreach ( array( '2026', '2025', '2021-2024' ) as $year ) : ?>
									<td><?php echo null !== $row[ $year ]['sell'] ? esc_html( verena_format_idr( $row[ $year ]['sell'] ) ) : '—'; ?></td>
									<td><?php echo null !== $row[ $year ]['buyback'] ? esc_html( verena_format_idr( $row[ $year ]['buyback'] ) ) : '—'; ?></td>
								<?php endforeach; ?>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php if ( $updated_labels['antam'] ) : ?>
				<p class="text-muted" style="font-size:0.85rem;">Harga terakhir diperbarui: <?php echo esc_html( $updated_labels['antam'] ); ?></p>
			<?php endif; ?>
		<?php endif; ?>

		<?php foreach ( array( 'Emasku' => 'emasku', 'UBS' => 'ubs' ) as $label => $key ) : ?>
			<h3 class="mt-4"><?php echo esc_html( $label ); ?></h3>
			<?php if ( empty( $sheet[ $key ] ) ) : ?>
				<p class="text-muted">Harga belum tersedia saat ini.</p>
			<?php else : ?>
				<div class="table-scroll mb-4">
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
				<?php if ( $updated_labels[ $key ] ) : ?>
					<p class="text-muted" style="font-size:0.85rem;">Harga terakhir diperbarui: <?php echo esc_html( $updated_labels[ $key ] ); ?></p>
				<?php endif; ?>
			<?php endif; ?>
		<?php endforeach; ?>
	</main>
	<?php
endwhile;
get_footer();
