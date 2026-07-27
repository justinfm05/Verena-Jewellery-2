<?php
/**
 * Adds a Status column + Quick Edit support to the Jewellery Pieces admin
 * list, so the owner can mark a one-off piece Sold in one click without
 * opening the full edit screen — important since two customers can
 * plausibly message about the same physical item before it's marked sold.
 *
 * @package Verena_Jewellery_Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter(
	'manage_verena_product_posts_columns',
	function ( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['verena_sku'] = 'SKU';
				$new['verena_karat'] = 'Karat';
				$new['verena_status'] = 'Status';
			}
		}
		return $new;
	}
);

add_action(
	'manage_verena_product_posts_custom_column',
	function ( $column, $post_id ) {
		if ( 'verena_sku' === $column ) {
			echo esc_html( get_field( 'sku', $post_id ) );
		}
		if ( 'verena_karat' === $column ) {
			echo esc_html( get_field( 'purity_label', $post_id ) ?: '17K' );
		}
		if ( 'verena_status' === $column ) {
			$status = get_field( 'status', $post_id );
			$statuses = verena_jt_product_statuses();
			$label = $statuses[ $status ] ?? 'Available';
			printf(
				'<span class="verena-status-badge verena-status-%s" data-status="%s">%s</span>',
				esc_attr( $status ),
				esc_attr( $status ),
				esc_html( $label )
			);
		}
	},
	10,
	2
);

add_action(
	'quick_edit_custom_box',
	function ( $column_name, $post_type ) {
		if ( 'verena_product' !== $post_type || 'verena_status' !== $column_name ) {
			return;
		}
		?>
		<fieldset class="inline-edit-col-right">
			<div class="inline-edit-col">
				<label>
					<span class="title">Status</span>
					<select name="verena_status" class="verena-quick-edit-status">
						<?php foreach ( verena_jt_product_statuses() as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
			</div>
		</fieldset>
		<?php
	},
	10,
	2
);

add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		if ( 'edit.php' !== $hook ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen || 'verena_product' !== $screen->post_type ) {
			return;
		}
		wp_enqueue_script(
			'verena-quick-edit',
			VERENA_JT_URL . 'assets/js/quick-edit.js',
			array( 'jquery', 'inline-edit-post' ),
			VERENA_JT_VERSION,
			true
		);
	}
);

add_action(
	'save_post_verena_product',
	function ( $post_id ) {
		if ( ! isset( $_POST['verena_status'] ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$statuses = verena_jt_product_statuses();
		$value = sanitize_key( wp_unslash( $_POST['verena_status'] ) );
		if ( isset( $statuses[ $value ] ) ) {
			update_field( 'status', $value, $post_id );
		}
	}
);
