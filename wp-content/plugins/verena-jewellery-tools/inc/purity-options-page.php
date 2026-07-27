<?php
/**
 * Admin screen for editing the karat -> purity-fraction table used by the
 * buyback estimator and the gold net-worth calculator.
 *
 * @package Verena_Jewellery_Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function verena_jt_register_purity_options_menu() {
	add_submenu_page(
		'verena-settings',
		'Purity Options',
		'Purity Options',
		'manage_options',
		'verena-purity-options',
		'verena_jt_render_purity_options_page'
	);
}
add_action( 'admin_menu', 'verena_jt_register_purity_options_menu' );

function verena_jt_handle_purity_options_save() {
	if ( ! isset( $_POST['verena_purity_nonce'] ) || ! wp_verify_nonce( $_POST['verena_purity_nonce'], 'verena_save_purity_options' ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$labels = isset( $_POST['label'] ) ? (array) wp_unslash( $_POST['label'] ) : array();
	$percents = isset( $_POST['percent'] ) ? (array) $_POST['percent'] : array();

	$options = array();
	$sort_order = 10;

	foreach ( $labels as $index => $label ) {
		$label = sanitize_text_field( $label );
		if ( '' === $label ) {
			continue; // Skip blank rows (used to remove an entry).
		}
		$percent = isset( $percents[ $index ] ) ? (float) $percents[ $index ] : 0;
		$percent = max( 0, min( 100, $percent ) );

		$options[] = array(
			'label'        => $label,
			'fraction_bps' => (int) round( $percent * 100 ),
			'sort_order'   => $sort_order,
		);
		$sort_order += 10;
	}

	update_option( 'verena_purity_options', $options );
	add_settings_error( 'verena_purity_options', 'saved', 'Purity options saved.', 'success' );
}
add_action( 'admin_init', 'verena_jt_handle_purity_options_save' );

function verena_jt_render_purity_options_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	settings_errors( 'verena_purity_options' );

	$options = verena_get_purity_options();
	// Always show a couple of blank trailing rows to add new entries.
	$blank_rows = 3;
	?>
	<div class="wrap">
		<h1>Purity Options</h1>
		<p>Used to convert a customer's declared karat into a fraction of pure gold when estimating buyback value. Confirm the <strong>17K</strong> row matches your actual SNI-labelled purity before relying on it.</p>

		<form method="post">
			<?php wp_nonce_field( 'verena_save_purity_options', 'verena_purity_nonce' ); ?>
			<table class="widefat striped" style="max-width:600px;">
				<thead>
					<tr>
						<th>Label</th>
						<th>Purity (%)</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $options as $option ) : ?>
						<tr>
							<td><input type="text" name="label[]" value="<?php echo esc_attr( $option['label'] ); ?>" class="regular-text" /></td>
							<td><input type="number" name="percent[]" value="<?php echo esc_attr( $option['fraction_bps'] / 100 ); ?>" min="0" max="100" step="0.01" class="small-text" /></td>
						</tr>
					<?php endforeach; ?>
					<?php for ( $i = 0; $i < $blank_rows; $i++ ) : ?>
						<tr>
							<td><input type="text" name="label[]" value="" class="regular-text" placeholder="e.g. 21K" /></td>
							<td><input type="number" name="percent[]" value="" min="0" max="100" step="0.01" class="small-text" /></td>
						</tr>
					<?php endfor; ?>
				</tbody>
			</table>
			<p class="description">Leave a label blank to remove that row on save.</p>
			<?php submit_button( 'Save Purity Options' ); ?>
		</form>
	</div>
	<?php
}
