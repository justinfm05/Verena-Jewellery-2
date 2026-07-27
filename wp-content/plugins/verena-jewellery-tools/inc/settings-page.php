<?php
/**
 * "Verena Jewellery" top-level admin menu + the main Settings screen:
 * shop info, WhatsApp number, and today's gold / buyback rates.
 *
 * Rate changes are appended to the log tables (see db/schema.php) rather
 * than overwriting a single option, so every change is kept as history and
 * "current rate" is simply the latest row.
 *
 * @package Verena_Jewellery_Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function verena_jt_register_admin_menu() {
	// Top-level lands on the dead-simple daily price screen (the thing staff
	// use every day). Full settings live one click deeper.
	add_menu_page(
		'Verena Jewellery',
		'Verena Jewellery',
		'manage_options',
		'verena-gold-price',
		'verena_jt_render_gold_price_page',
		'dashicons-money-alt',
		56
	);

	add_submenu_page(
		'verena-gold-price',
		'Harga Emas Hari Ini',
		'Harga Emas Hari Ini',
		'manage_options',
		'verena-gold-price',
		'verena_jt_render_gold_price_page'
	);

	add_submenu_page(
		'verena-gold-price',
		'Settings & Gold Rate',
		'Settings',
		'manage_options',
		'verena-settings',
		'verena_jt_render_settings_page'
	);
}
add_action( 'admin_menu', 'verena_jt_register_admin_menu' );

function verena_jt_handle_settings_save() {
	if ( ! isset( $_POST['verena_settings_nonce'] ) || ! wp_verify_nonce( $_POST['verena_settings_nonce'], 'verena_save_settings' ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Shop info.
	update_option( 'verena_shop_name', sanitize_text_field( wp_unslash( $_POST['shop_name'] ?? 'Verena Jewellery' ) ) );
	update_option( 'verena_whatsapp_number', sanitize_text_field( wp_unslash( $_POST['whatsapp_number'] ?? '' ) ) );
	update_option( 'verena_shop_phone', sanitize_text_field( wp_unslash( $_POST['shop_phone'] ?? '' ) ) );
	update_option( 'verena_shop_address', sanitize_text_field( wp_unslash( $_POST['shop_address'] ?? '' ) ) );
	update_option( 'verena_shop_hours', sanitize_text_field( wp_unslash( $_POST['shop_hours'] ?? '' ) ) );
	update_option( 'verena_shop_established', sanitize_text_field( wp_unslash( $_POST['shop_established'] ?? '' ) ) );
	update_option( 'verena_instagram_url', esc_url_raw( wp_unslash( $_POST['instagram_url'] ?? '' ) ) );
	update_option( 'verena_price_rounding', max( 1, (int) ( $_POST['price_rounding'] ?? 100 ) ) );

	// Gold rate: one field per karat the shop stocks (see Purity Options).
	// Only record a new row for karats that were actually filled in — an
	// empty field should never zero out that karat's current rate.
	if ( isset( $_POST['gold_rate'] ) && is_array( $_POST['gold_rate'] ) ) {
		foreach ( wp_unslash( $_POST['gold_rate'] ) as $purity_label => $value ) {
			if ( '' === trim( $value ) ) {
				continue;
			}
			verena_record_gold_rate( (int) $value, sanitize_text_field( $purity_label ) );
		}
	}

	if ( isset( $_POST['buyback_rate_per_gram'] ) && '' !== trim( $_POST['buyback_rate_per_gram'] ) ) {
		verena_record_buyback_rate( (int) $_POST['buyback_rate_per_gram'] );
	}

	add_settings_error( 'verena_settings', 'saved', 'Settings saved.', 'success' );
}
add_action( 'admin_init', 'verena_jt_handle_settings_save' );

function verena_jt_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	settings_errors( 'verena_settings' );

	$current_gold_rates = verena_get_all_current_gold_rates();
	$current_buyback_rate = verena_get_current_buyback_rate();
	$purity_options = verena_get_purity_options();

	global $wpdb;
	$gold_history = $wpdb->get_results(
		"SELECT * FROM {$wpdb->prefix}verena_gold_rate_log ORDER BY created_at DESC, id DESC LIMIT 15",
		ARRAY_A
	);
	$buyback_history = $wpdb->get_results(
		"SELECT * FROM {$wpdb->prefix}verena_buyback_rate_log ORDER BY created_at DESC, id DESC LIMIT 10",
		ARRAY_A
	);
	?>
	<div class="wrap">
		<h1>Verena Jewellery — Settings &amp; Gold Rate</h1>

		<form method="post">
			<?php wp_nonce_field( 'verena_save_settings', 'verena_settings_nonce' ); ?>

			<h2>Today's Gold Rates, Per Karat</h2>
			<p class="description">Each fashion piece is priced using its own karat's rate (set on the piece under Jewellery Pieces). Only fill in the karats you're updating today — everything else keeps its last saved rate.</p>
			<table class="form-table">
				<?php foreach ( $purity_options as $option ) : ?>
					<?php
					if ( 'Tidak yakin / belum dicek' === $option['label'] ) {
						continue;
					}
					$label = $option['label'];
					$current = $current_gold_rates[ $label ] ?? null;
					$field_id = 'gold_rate_' . sanitize_key( $label );
					?>
					<tr>
						<th><label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $label ); ?> Sell Rate (IDR / gram)</label></th>
						<td>
							<input type="number" min="0" step="1" name="gold_rate[<?php echo esc_attr( $label ); ?>]" id="<?php echo esc_attr( $field_id ); ?>" class="regular-text" placeholder="<?php echo esc_attr( $current ? $current['sell_price_per_gram'] : '' ); ?>" />
							<p class="description">
								<?php if ( $current ) : ?>
									Current: <strong><?php echo esc_html( verena_format_idr( $current['sell_price_per_gram'] ) ); ?></strong> per gram, set <?php echo esc_html( human_time_diff( strtotime( $current['created_at'] ) ) ); ?> ago.
								<?php else : ?>
									No rate set yet for <?php echo esc_html( $label ); ?> — pieces at this karat won't show a price until one is entered.
								<?php endif; ?>
							</p>
						</td>
					</tr>
				<?php endforeach; ?>
				<tr>
					<th><label for="buyback_rate_per_gram">Buyback Rate (IDR / gram, fine-gold-equivalent)</label></th>
					<td>
						<input type="number" min="0" step="1" name="buyback_rate_per_gram" id="buyback_rate_per_gram" class="regular-text" placeholder="<?php echo esc_attr( $current_buyback_rate ? $current_buyback_rate['price_per_gram'] : '' ); ?>" />
						<p class="description">
							<?php if ( $current_buyback_rate ) : ?>
								Current: <strong><?php echo esc_html( verena_format_idr( $current_buyback_rate['price_per_gram'] ) ); ?></strong> per gram, set <?php echo esc_html( human_time_diff( strtotime( $current_buyback_rate['created_at'] ) ) ); ?> ago. Leave blank to keep it unchanged.
							<?php else : ?>
								No rate set yet — the buyback estimator and gold calculator won't work until one is entered.
							<?php endif; ?>
						</p>
					</td>
				</tr>
				<tr>
					<th><label for="price_rounding">Price Rounding (nearest Rp)</label></th>
					<td>
						<input type="number" min="1" step="1" name="price_rounding" id="price_rounding" value="<?php echo esc_attr( get_option( 'verena_price_rounding', 100 ) ); ?>" class="small-text" />
						<p class="description">All computed prices round to the nearest multiple of this amount. Default 100.</p>
					</td>
				</tr>
			</table>

			<h2>Shop Info</h2>
			<table class="form-table">
				<tr>
					<th><label for="shop_name">Shop Name</label></th>
					<td><input type="text" name="shop_name" id="shop_name" value="<?php echo esc_attr( get_option( 'verena_shop_name', 'Verena Jewellery' ) ); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th><label for="whatsapp_number">WhatsApp Number</label></th>
					<td>
						<input type="text" name="whatsapp_number" id="whatsapp_number" value="<?php echo esc_attr( get_option( 'verena_whatsapp_number', '628111099399' ) ); ?>" class="regular-text" />
						<p class="description">Digits only, with country code, no leading + (e.g. 628111099399). Every "inquire / sell" button on the site links here.</p>
					</td>
				</tr>
				<tr>
					<th><label for="shop_phone">Landline Phone</label></th>
					<td><input type="text" name="shop_phone" id="shop_phone" value="<?php echo esc_attr( get_option( 'verena_shop_phone', '(+62) 21 72793226' ) ); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th><label for="shop_address">Address</label></th>
					<td><input type="text" name="shop_address" id="shop_address" value="<?php echo esc_attr( get_option( 'verena_shop_address', 'ITC Fatmawati, Lt. 1 No. 175-177, Jl. RS. Fatmawati, Jakarta Selatan' ) ); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th><label for="shop_hours">Operating Hours</label></th>
					<td><input type="text" name="shop_hours" id="shop_hours" value="<?php echo esc_attr( get_option( 'verena_shop_hours', 'Senin – Sabtu, 11.30 – 19.00 WIB (Minggu tutup)' ) ); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th><label for="shop_established">Established Year</label></th>
					<td><input type="text" name="shop_established" id="shop_established" value="<?php echo esc_attr( get_option( 'verena_shop_established', '1999' ) ); ?>" class="small-text" /></td>
				</tr>
				<tr>
					<th><label for="instagram_url">Instagram URL</label></th>
					<td><input type="url" name="instagram_url" id="instagram_url" value="<?php echo esc_attr( get_option( 'verena_instagram_url', 'https://www.instagram.com/verenajewellery.id/' ) ); ?>" class="regular-text" /></td>
				</tr>
			</table>

			<?php submit_button( 'Save Settings' ); ?>
		</form>

		<h2>Gold Rate History</h2>
		<?php verena_jt_render_rate_history_table( $gold_history, 'sell_price_per_gram', 'purity_label' ); ?>

		<h2>Buyback Rate History</h2>
		<?php verena_jt_render_rate_history_table( $buyback_history, 'price_per_gram' ); ?>
	</div>
	<?php
}

function verena_jt_render_rate_history_table( $rows, $price_field, $label_field = null ) {
	if ( empty( $rows ) ) {
		echo '<p>No history yet.</p>';
		return;
	}
	?>
	<table class="widefat striped" style="max-width:500px;">
		<thead><tr><?php if ( $label_field ) : ?><th>Karat</th><?php endif; ?><th>Date</th><th>Rate</th></tr></thead>
		<tbody>
		<?php foreach ( $rows as $row ) : ?>
			<tr>
				<?php if ( $label_field ) : ?><td><?php echo esc_html( $row[ $label_field ] ); ?></td><?php endif; ?>
				<td><?php echo esc_html( mysql2date( 'd M Y, H:i', $row['created_at'] ) ); ?></td>
				<td><?php echo esc_html( verena_format_idr( $row[ $price_field ] ) ); ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php
}
