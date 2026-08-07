<?php
/**
 * "Custom Showcase" media manager — lets any wp-admin user (not just a
 * developer) swap the 4 photo/video slots shown in the homepage and Custom
 * Design page's Custom Showcase grid, via the normal WordPress Media
 * Library, instead of a developer replacing files in the theme and
 * redeploying. Stores 4 attachment IDs in the `verena_showcase_media`
 * option; the theme reads them via verena_get_showcase_media() (see
 * functions.php), falling back to the theme's bundled placeholder file for
 * any slot left unset.
 *
 * @package Verena_Jewellery_Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle the save. Each slot posts an attachment ID (0 = cleared, falls
 * back to the theme default).
 */
function verena_jt_handle_showcase_media_save() {
	if ( ! isset( $_POST['verena_showcase_media_nonce'] ) || ! wp_verify_nonce( $_POST['verena_showcase_media_nonce'], 'verena_save_showcase_media' ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$posted = isset( $_POST['showcase_media'] ) && is_array( $_POST['showcase_media'] ) ? wp_unslash( $_POST['showcase_media'] ) : array();
	$ids    = array();
	for ( $i = 0; $i < 4; $i++ ) {
		$ids[ $i ] = isset( $posted[ $i ] ) ? (int) $posted[ $i ] : 0;
	}

	update_option( 'verena_showcase_media', $ids );
	add_settings_error( 'verena_showcase_media', 'saved', 'Custom Showcase tersimpan. Perubahan langsung tampil di beranda & halaman Custom Design.', 'success' );
}
add_action( 'admin_init', 'verena_jt_handle_showcase_media_save' );

/**
 * Render the media manager: 4 slots, each with a Media Library picker
 * (image or video) and a live preview.
 */
function verena_jt_render_showcase_media_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	wp_enqueue_media();
	settings_errors( 'verena_showcase_media' );

	$ids   = get_option( 'verena_showcase_media', array() );
	$slots = array(
		0 => 'Slot 1',
		1 => 'Slot 2',
		2 => 'Slot 3',
		3 => 'Slot 4',
	);
	?>
	<style>
		.verena-showcase { max-width: 900px; }
		.verena-showcase__intro { font-size: 15px; line-height: 1.6; background: #fff; border-left: 4px solid #C9A24B; padding: 12px 16px; margin: 12px 0 20px; }
		.verena-showcase__grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 24px; }
		.verena-showcase__slot { background: #fff; border: 1px solid #dcdcde; border-radius: 8px; padding: 16px; text-align: center; }
		.verena-showcase__slot h3 { margin: 0 0 12px; font-size: 14px; }
		.verena-showcase__preview { width: 100%; aspect-ratio: 4/5; background: #f0f0f1; border-radius: 6px; overflow: hidden; display: flex; align-items: center; justify-content: center; margin-bottom: 10px; }
		.verena-showcase__preview img, .verena-showcase__preview video { width: 100%; height: 100%; object-fit: cover; }
		.verena-showcase__preview span { font-size: 12px; color: #888; padding: 8px; }
		.verena-showcase__actions { display: flex; gap: 6px; justify-content: center; }
	</style>

	<div class="wrap verena-showcase">
		<h1>Custom Showcase (Beranda)</h1>
		<p class="verena-showcase__intro">
			Ganti foto/video di 4 kotak "Custom Showcase" pada beranda dan halaman Custom Design langsung dari sini —
			tidak perlu developer. Klik <strong>Pilih Media</strong>, ambil dari Media Library (atau upload baru), lalu <strong>Simpan</strong>.
			Slot yang belum diisi akan memakai file bawaan tema.
		</p>

		<form method="post">
			<?php wp_nonce_field( 'verena_save_showcase_media', 'verena_showcase_media_nonce' ); ?>

			<div class="verena-showcase__grid">
				<?php foreach ( $slots as $i => $label ) : ?>
					<?php
					$attachment_id = (int) ( $ids[ $i ] ?? 0 );
					$is_set        = $attachment_id && 'attachment' === get_post_type( $attachment_id );
					$mime          = $is_set ? get_post_mime_type( $attachment_id ) : '';
					$is_video      = $mime && 0 === strpos( $mime, 'video/' );
					$preview_url   = $is_set ? ( $is_video ? wp_get_attachment_url( $attachment_id ) : wp_get_attachment_image_url( $attachment_id, 'medium' ) ) : '';
					?>
					<div class="verena-showcase__slot" data-slot="<?php echo esc_attr( $i ); ?>">
						<h3><?php echo esc_html( $label ); ?></h3>
						<div class="verena-showcase__preview">
							<?php if ( $is_set && $is_video ) : ?>
								<video src="<?php echo esc_url( $preview_url ); ?>" muted></video>
							<?php elseif ( $is_set ) : ?>
								<img src="<?php echo esc_url( $preview_url ); ?>" alt="" />
							<?php else : ?>
								<span>Belum diatur — memakai file bawaan tema</span>
							<?php endif; ?>
						</div>
						<input type="hidden" name="showcase_media[<?php echo esc_attr( $i ); ?>]" class="verena-showcase__input" value="<?php echo esc_attr( $attachment_id ); ?>" />
						<div class="verena-showcase__actions">
							<button type="button" class="button verena-showcase__pick">Pilih Media</button>
							<button type="button" class="button verena-showcase__clear" <?php echo $is_set ? '' : 'style="display:none;"'; ?>>Hapus</button>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<?php submit_button( 'Simpan Custom Showcase' ); ?>
		</form>
	</div>

	<script>
	( function() {
		document.querySelectorAll( '.verena-showcase__slot' ).forEach( function( slot ) {
			var pickBtn  = slot.querySelector( '.verena-showcase__pick' );
			var clearBtn = slot.querySelector( '.verena-showcase__clear' );
			var input    = slot.querySelector( '.verena-showcase__input' );
			var preview  = slot.querySelector( '.verena-showcase__preview' );
			var frame;

			pickBtn.addEventListener( 'click', function( e ) {
				e.preventDefault();
				if ( frame ) { frame.open(); return; }
				frame = wp.media( {
					title: 'Pilih Foto atau Video',
					library: { type: [ 'image', 'video' ] },
					multiple: false,
					button: { text: 'Gunakan Media Ini' },
				} );
				frame.on( 'select', function() {
					var attachment = frame.state().get( 'selection' ).first().toJSON();
					input.value = attachment.id;
					var isVideo = attachment.type === 'video';
					preview.innerHTML = isVideo
						? '<video src="' + attachment.url + '" muted></video>'
						: '<img src="' + ( attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url ) + '" alt="" />';
					clearBtn.style.display = '';
				} );
				frame.open();
			} );

			clearBtn.addEventListener( 'click', function( e ) {
				e.preventDefault();
				input.value = '0';
				preview.innerHTML = '<span>Belum diatur — memakai file bawaan tema</span>';
				clearBtn.style.display = 'none';
			} );
		} );
	} )();
	</script>
	<?php
}
