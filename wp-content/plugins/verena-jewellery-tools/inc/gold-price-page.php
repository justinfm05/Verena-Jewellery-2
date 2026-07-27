<?php
/**
 * "Harga Emas Hari Ini" — the dead-simple daily price screen.
 *
 * This is the landing page under the Verena Jewellery admin menu: a handful
 * of large number fields (per-karat sell price + buyback) that a non-technical
 * staff member fills in once a day and saves. Saving instantly drives:
 *   - the gold price ticker at the top of every page,
 *   - every jewellery product's computed price,
 *   - the buyback estimator, and the Kalkulator Emas.
 *
 * Rates are appended to the same log tables the full Settings page uses
 * (verena_record_gold_rate / verena_record_buyback_rate), so history is kept.
 * The store's own Excel price list is intentionally NOT connected — these few
 * numbers are re-typed here, which is the reliable, non-fragile approach.
 *
 * @package Verena_Jewellery_Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The karats shown on the daily screen (sell rates). Kept short on purpose;
 * the full Settings page still exposes every purity option.
 *
 * @return string[]
 */
function verena_jt_daily_karats() {
	return array( '24K', '22K', '18K', '17K', '16K' );
}

/**
 * Handle the daily-price form submit. Own nonce so it never collides with the
 * full Settings page save. Empty fields are skipped (never zero out a rate).
 */
function verena_jt_handle_gold_price_save() {
	if ( ! isset( $_POST['verena_gold_price_nonce'] ) || ! wp_verify_nonce( $_POST['verena_gold_price_nonce'], 'verena_save_gold_price' ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$saved = 0;

	if ( isset( $_POST['gold_rate'] ) && is_array( $_POST['gold_rate'] ) ) {
		foreach ( wp_unslash( $_POST['gold_rate'] ) as $purity_label => $value ) {
			$value = trim( str_replace( array( '.', ',', ' ', 'Rp', 'rp' ), '', (string) $value ) );
			if ( '' === $value ) {
				continue;
			}
			verena_record_gold_rate( (int) $value, sanitize_text_field( $purity_label ) );
			$saved++;
		}
	}

	if ( isset( $_POST['buyback_rate_per_gram'] ) ) {
		$buyback = trim( str_replace( array( '.', ',', ' ', 'Rp', 'rp' ), '', (string) wp_unslash( $_POST['buyback_rate_per_gram'] ) ) );
		if ( '' !== $buyback ) {
			verena_record_buyback_rate( (int) $buyback );
			$saved++;
		}
	}

	if ( $saved > 0 ) {
		add_settings_error( 'verena_gold_price', 'saved', 'Harga tersimpan. Website, harga perhiasan, dan Kalkulator Emas sudah diperbarui.', 'success' );
	} else {
		add_settings_error( 'verena_gold_price', 'nochange', 'Tidak ada harga yang diisi, jadi tidak ada yang berubah.', 'warning' );
	}
}
add_action( 'admin_init', 'verena_jt_handle_gold_price_save' );

/**
 * Render the daily price screen.
 */
function verena_jt_render_gold_price_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	settings_errors( 'verena_gold_price' );

	$current_gold_rates   = verena_get_all_current_gold_rates();
	$current_buyback_rate = verena_get_current_buyback_rate();
	$karats               = verena_jt_daily_karats();

	// Most recent update timestamp across all shown rates, for the header line.
	$latest = 0;
	foreach ( $current_gold_rates as $rate ) {
		$latest = max( $latest, strtotime( $rate['created_at'] ) );
	}
	if ( $current_buyback_rate ) {
		$latest = max( $latest, strtotime( $current_buyback_rate['created_at'] ) );
	}

	global $wpdb;
	$gold_history = $wpdb->get_results(
		"SELECT * FROM {$wpdb->prefix}verena_gold_rate_log ORDER BY created_at DESC, id DESC LIMIT 10",
		ARRAY_A
	);
	?>
	<style>
		.verena-price { max-width: 620px; }
		.verena-price__intro { font-size: 15px; line-height: 1.6; background: #fff; border-left: 4px solid #C9A24B; padding: 12px 16px; margin: 12px 0 20px; }
		.verena-price__updated { color: #555; margin: 0 0 18px; }
		.verena-price__card { background: #fff; border: 1px solid #dcdcde; border-radius: 8px; padding: 8px 20px 20px; }
		.verena-price__row { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 8px 16px; padding: 16px 0; border-bottom: 1px solid #f0f0f1; }
		.verena-price__row:last-of-type { border-bottom: 0; }
		.verena-price__label { font-size: 17px; font-weight: 600; }
		.verena-price__label small { display: block; font-weight: 400; color: #666; font-size: 13px; }
		.verena-price__now { font-size: 13px; color: #666; }
		.verena-price__now strong { color: #1E2A1E; font-size: 15px; }
		.verena-price input[type=text] { font-size: 20px; padding: 10px 12px; width: 190px; max-width: 48vw; text-align: right; border-radius: 6px; }
		.verena-price__buyback { background: #fbf6e9; margin: 0 -20px; padding: 16px 20px; border-radius: 0 0 8px 8px; }
		.verena-price__save { margin-top: 20px; }
		.verena-price__save .button-primary { font-size: 17px; height: auto; padding: 12px 28px; border-radius: 6px; }
		@media (max-width: 600px) {
			.verena-price__row { flex-direction: column; align-items: stretch; }
			.verena-price input[type=text] { width: 100%; max-width: none; }
		}
	</style>

	<div class="wrap verena-price">
		<h1>Harga Emas Hari Ini</h1>

		<p class="verena-price__intro">
			Isi harga hari ini lalu tekan <strong>Simpan Harga</strong>. Website (ticker harga emas di atas),
			harga perhiasan, dan <strong>Kalkulator Emas</strong> langsung ikut diperbarui.
			Kolom yang dikosongkan tidak akan mengubah harga sebelumnya.
		</p>

		<?php if ( $latest ) : ?>
			<p class="verena-price__updated">Terakhir diperbarui: <strong><?php echo esc_html( wp_date( 'd F Y, H:i', $latest ) ); ?></strong> WIB</p>
		<?php endif; ?>

		<form method="post">
			<?php wp_nonce_field( 'verena_save_gold_price', 'verena_gold_price_nonce' ); ?>

			<div class="verena-price__card">
				<?php foreach ( $karats as $label ) : ?>
					<?php $current = $current_gold_rates[ $label ] ?? null; ?>
					<div class="verena-price__row">
						<div class="verena-price__label">
							Harga Jual <?php echo esc_html( $label ); ?>
							<small>Rupiah per gram</small>
						</div>
						<div style="text-align:right;">
							<div class="verena-price__now">
								<?php if ( $current ) : ?>
									Sekarang: <strong><?php echo esc_html( verena_format_idr( $current['sell_price_per_gram'] ) ); ?></strong>
								<?php else : ?>
									Belum ada harga
								<?php endif; ?>
							</div>
							<input type="text" inputmode="numeric" name="gold_rate[<?php echo esc_attr( $label ); ?>]" placeholder="cth. <?php echo esc_attr( $current ? number_format( (int) $current['sell_price_per_gram'], 0, ',', '.' ) : '1.850.000' ); ?>" />
						</div>
					</div>
				<?php endforeach; ?>

				<div class="verena-price__row verena-price__buyback">
					<div class="verena-price__label">
						Harga Buyback
						<small>Rupiah per gram (emas murni / 24K) &middot; dipakai Kalkulator Emas</small>
					</div>
					<div style="text-align:right;">
						<div class="verena-price__now">
							<?php if ( $current_buyback_rate ) : ?>
								Sekarang: <strong><?php echo esc_html( verena_format_idr( $current_buyback_rate['price_per_gram'] ) ); ?></strong>
							<?php else : ?>
								Belum ada harga
							<?php endif; ?>
						</div>
						<input type="text" inputmode="numeric" name="buyback_rate_per_gram" placeholder="cth. <?php echo esc_attr( $current_buyback_rate ? number_format( (int) $current_buyback_rate['price_per_gram'], 0, ',', '.' ) : '2.292.000' ); ?>" />
					</div>
				</div>
			</div>

			<p class="verena-price__save">
				<button type="submit" class="button button-primary">Simpan Harga</button>
			</p>
		</form>

		<h2 style="margin-top:32px;">Riwayat Perubahan Terakhir</h2>
		<?php verena_jt_render_rate_history_table( $gold_history, 'sell_price_per_gram', 'purity_label' ); ?>
	</div>
	<?php
}
