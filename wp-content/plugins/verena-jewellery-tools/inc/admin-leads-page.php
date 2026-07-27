<?php
/**
 * Read-only admin screen listing saved gold net-worth calculator lists.
 * Every saved list is a potential lead (visitor added up their gold and
 * cared enough to save a shareable link) — this is the owner's follow-up
 * queue, distinct from the calculator's own "sell via WhatsApp" CTA which
 * doesn't require saving anything.
 *
 * @package Verena_Jewellery_Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function verena_jt_register_leads_menu() {
	add_submenu_page(
		'verena-settings',
		'Gold Calculator Leads',
		'Gold Calculator Leads',
		'manage_options',
		'verena-calculator-leads',
		'verena_jt_render_leads_page'
	);
}
add_action( 'admin_menu', 'verena_jt_register_leads_menu' );

function verena_jt_render_leads_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	global $wpdb;
	$table = $wpdb->prefix . 'verena_calc_lists';

	$per_page = 20;
	$paged = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1;
	$offset = ( $paged - 1 ) * $per_page;

	$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
	$rows = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d OFFSET %d", $per_page, $offset ),
		ARRAY_A
	);
	?>
	<div class="wrap">
		<h1>Gold Calculator Leads</h1>
		<p>Everyone who saved a shareable link from the gold net-worth calculator on the public site. Contact info is only shown when the visitor chose to provide it.</p>

		<?php if ( empty( $rows ) ) : ?>
			<p>No saved calculator lists yet.</p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th>Date</th>
						<th>Name</th>
						<th>WhatsApp</th>
						<th># Items</th>
						<th>Total Est. Value</th>
						<th>Link</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $rows as $row ) : ?>
						<?php
						$items = json_decode( $row['items_json'], true );
						$item_count = is_array( $items ) ? count( $items ) : 0;
						$public_url = home_url( '/gold-calculator/' . rawurlencode( $row['slug'] ) . '/' );
						?>
						<tr>
							<td><?php echo esc_html( mysql2date( 'd M Y, H:i', $row['created_at'] ) ); ?></td>
							<td><?php echo esc_html( $row['contact_name'] ? $row['contact_name'] : '—' ); ?></td>
							<td><?php echo esc_html( $row['contact_whatsapp'] ? $row['contact_whatsapp'] : '—' ); ?></td>
							<td><?php echo esc_html( $item_count ); ?></td>
							<td><?php echo esc_html( verena_format_idr( $row['total_value'] ) ); ?></td>
							<td><a href="<?php echo esc_url( $public_url ); ?>" target="_blank" rel="noopener">View list ↗</a></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php
			$total_pages = (int) ceil( $total / $per_page );
			if ( $total_pages > 1 ) :
				?>
				<p class="tablenav-pages" style="margin-top:1em;">
					<?php
					echo paginate_links(
						array(
							'base'      => add_query_arg( 'paged', '%#%' ),
							'format'    => '',
							'current'   => $paged,
							'total'     => $total_pages,
						)
					);
					?>
				</p>
				<?php
			endif;
			?>
		<?php endif; ?>
	</div>
	<?php
}
