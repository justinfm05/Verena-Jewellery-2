<?php
/**
 * Template Name: Bullion Brand
 *
 * Renders the price table for a single bullion brand. Matches the brand by
 * the current page's own slug against the verena_bullion_brand taxonomy —
 * so this template works for any page whose slug is "antam", "ubs", or
 * "emasku" without further configuration.
 *
 * @package Verena_Jewellery
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
while ( have_posts() ) : the_post();

	$brand_slug = get_post_field( 'post_name', get_the_ID() );
	$term = get_term_by( 'slug', $brand_slug, 'verena_bullion_brand' );
	$listings = verena_get_bullion_by_brand( $brand_slug );

	// Antam is priced by mint year — only show the Year column when at
	// least one listing actually has one, so UBS/Emasku tables stay simple.
	$has_years = false;
	foreach ( $listings as $listing ) {
		if ( get_field( 'year', $listing->ID ) ) {
			$has_years = true;
			break;
		}
	}
	?>
	<main class="container section">
		<div class="text-center section-narrow" style="margin-bottom:var(--space-4);">
			<p class="eyebrow">Emas Batangan</p>
			<h1><?php echo esc_html( $term ? $term->name : get_the_title() ); ?></h1>
			<div class="text-muted"><?php the_content(); ?></div>
		</div>

		<?php if ( empty( $listings ) ) : ?>
			<div class="empty-state">
				<p>Stok belum tersedia saat ini. Hubungi kami untuk info terbaru.</p>
				<?php verena_whatsapp_button( verena_wa_message_bullion( $term ? $term->name : $brand_slug, '', 'beli' ), 'Tanya Stok' ); ?>
			</div>
		<?php else : ?>
			<div class="table-scroll">
				<table class="price-table">
					<thead>
						<tr>
							<?php if ( $has_years ) : ?><th>Tahun</th><?php endif; ?>
							<th>Denominasi</th>
							<th>Harga Jual</th>
							<th>Harga Buyback</th>
							<th>Stok</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $listings as $listing ) : ?>
							<?php
							$denomination = get_field( 'denomination_grams', $listing->ID );
							$series = get_field( 'series', $listing->ID );
							$year = get_field( 'year', $listing->ID );
							$sell = get_field( 'sell_price', $listing->ID );
							$buyback = get_field( 'buyback_price', $listing->ID );
							$in_stock = get_field( 'in_stock', $listing->ID );
							$label = verena_format_grams( $denomination ) . ( $series ? ' — ' . $series : '' );
							?>
							<tr>
								<?php if ( $has_years ) : ?><td><?php echo esc_html( $year ? $year : '—' ); ?></td><?php endif; ?>
								<td><?php echo esc_html( $label ); ?></td>
								<td><?php echo esc_html( verena_format_idr( $sell ) ); ?></td>
								<td><?php echo esc_html( verena_format_idr( $buyback ) ); ?></td>
								<td><?php echo $in_stock ? '<span class="status-badge status-available">Tersedia</span>' : '<span class="status-badge status-sold">Habis</span>'; ?></td>
								<td>
									<?php if ( $in_stock ) : ?>
										<a class="btn btn-whatsapp btn-sm" target="_blank" rel="noopener" href="<?php echo esc_url( verena_build_wa_link( verena_wa_message_bullion( $term ? $term->name : $brand_slug, $label, 'beli' ) ) ); ?>">Beli</a>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<div class="service-card mt-4 text-center">
				<h3>Ingin jual emas batangan Anda?</h3>
				<?php verena_whatsapp_button( verena_wa_message_bullion( $term ? $term->name : $brand_slug, '', 'jual' ), 'Jual ke Kami' ); ?>
			</div>
		<?php endif; ?>
	</main>
	<?php
endwhile;
get_footer();
