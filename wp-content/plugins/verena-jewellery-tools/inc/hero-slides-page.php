<?php
/**
 * "Hero Slideshow" media manager — lets any wp-admin user (not just a
 * developer) swap the 5 photos in the homepage hero's sliding carousel via
 * the normal WordPress Media Library, instead of a developer replacing
 * files in the theme and redeploying. Stores 5 {attachment_id, alt} pairs
 * in the `verena_hero_slides` option; the theme reads them via
 * verena_get_hero_slides() (see functions.php), falling back to the
 * theme's bundled placeholder file for any slot left unset.
 *
 * @package Verena_Jewellery_Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle the save. Each slot posts an attachment ID (0 = cleared, falls
 * back to the theme default) and an optional alt text override.
 */
function verena_jt_handle_hero_slides_save() {
	if ( ! isset( $_POST['verena_hero_slides_nonce'] ) || ! wp_verify_nonce( $_POST['verena_hero_slides_nonce'], 'verena_save_hero_slides' ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$posted_ids  = isset( $_POST['hero_slide_id'] ) && is_array( $_POST['hero_slide_id'] ) ? wp_unslash( $_POST['hero_slide_id'] ) : array();
	$posted_alts = isset( $_POST['hero_slide_alt'] ) && is_array( $_POST['hero_slide_alt'] ) ? wp_unslash( $_POST['hero_slide_alt'] ) : array();

	$slides = array();
	for ( $i = 0; $i < 5; $i++ ) {
		$slides[ $i ] = array(
			'attachment_id' => isset( $posted_ids[ $i ] ) ? (int) $posted_ids[ $i ] : 0,
			'alt'           => isset( $posted_alts[ $i ] ) ? sanitize_text_field( $posted_alts[ $i ] ) : '',
		);
	}

	update_option( 'verena_hero_slides', $slides );
	add_settings_error( 'verena_hero_slides', 'saved', 'Hero Slideshow tersimpan. Perubahan langsung tampil di beranda.', 'success' );
}
add_action( 'admin_init', 'verena_jt_handle_hero_slides_save' );

/**
 * Render the media manager: 5 slots, each with a Media Library picker
 * (images only), a live preview, and an alt-text field.
 */
function verena_jt_render_hero_slides_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	wp_enqueue_media();
	settings_errors( 'verena_hero_slides' );

	$slides = get_option( 'verena_hero_slides', array() );
	$labels = array( 'Slide 1', 'Slide 2', 'Slide 3', 'Slide 4', 'Slide 5' );
	?>
	<style>
		.verena-hero-slides { max-width: 1100px; }
		.verena-hero-slides__intro { font-size: 15px; line-height: 1.6; background: #fff; border-left: 4px solid #C9A24B; padding: 12px 16px; margin: 12px 0 20px; }
		.verena-hero-slides__grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; margin-bottom: 24px; }
		.verena-hero-slides__slot { background: #fff; border: 1px solid #dcdcde; border-radius: 8px; padding: 16px; text-align: center; }
		.verena-hero-slides__slot h3 { margin: 0 0 12px; font-size: 14px; }
		.verena-hero-slides__preview { width: 100%; aspect-ratio: 3/4; background: #f0f0f1; border-radius: 6px; overflow: hidden; display: flex; align-items: center; justify-content: center; margin-bottom: 10px; }
		.verena-hero-slides__preview img { width: 100%; height: 100%; object-fit: cover; }
		.verena-hero-slides__preview span { font-size: 12px; color: #888; padding: 8px; }
		.verena-hero-slides__actions { display: flex; gap: 6px; justify-content: center; margin-bottom: 10px; }
		.verena-hero-slides__alt { width: 100%; box-sizing: border-box; }
		.verena-hero-slides__alt-label { display: block; font-size: 11px; color: #666; margin-bottom: 4px; text-align: left; }
	</style>

	<div class="wrap verena-hero-slides">
		<h1>Hero Slideshow (Beranda)</h1>
		<p class="verena-hero-slides__intro">
			Ganti 5 foto yang bergantian tampil di panel kanan hero beranda langsung dari sini — tidak perlu developer.
			Klik <strong>Pilih Foto</strong>, ambil dari Media Library (atau upload baru), isi teks alternatif singkat untuk foto tersebut, lalu <strong>Simpan</strong>.
			Slot yang belum diisi akan memakai file bawaan tema.
		</p>

		<form method="post">
			<?php wp_nonce_field( 'verena_save_hero_slides', 'verena_hero_slides_nonce' ); ?>

			<div class="verena-hero-slides__grid">
				<?php foreach ( $labels as $i => $label ) : ?>
					<?php
					$slide         = $slides[ $i ] ?? array();
					$attachment_id = (int) ( $slide['attachment_id'] ?? 0 );
					$alt           = (string) ( $slide['alt'] ?? '' );
					$is_set        = $attachment_id && 'attachment' === get_post_type( $attachment_id );
					$preview_url   = $is_set ? wp_get_attachment_image_url( $attachment_id, 'medium' ) : '';
					?>
					<div class="verena-hero-slides__slot" data-slot="<?php echo esc_attr( $i ); ?>">
						<h3><?php echo esc_html( $label ); ?></h3>
						<div class="verena-hero-slides__preview">
							<?php if ( $is_set ) : ?>
								<img src="<?php echo esc_url( $preview_url ); ?>" alt="" />
							<?php else : ?>
								<span>Belum diatur — memakai file bawaan tema</span>
							<?php endif; ?>
						</div>
						<input type="hidden" name="hero_slide_id[<?php echo esc_attr( $i ); ?>]" class="verena-hero-slides__input" value="<?php echo esc_attr( $attachment_id ); ?>" />
						<div class="verena-hero-slides__actions">
							<button type="button" class="button verena-hero-slides__pick">Pilih Foto</button>
							<button type="button" class="button verena-hero-slides__clear" <?php echo $is_set ? '' : 'style="display:none;"'; ?>>Hapus</button>
						</div>
						<label class="verena-hero-slides__alt-label" for="hero_slide_alt_<?php echo esc_attr( $i ); ?>">Teks alternatif (deskripsi foto)</label>
						<input type="text" id="hero_slide_alt_<?php echo esc_attr( $i ); ?>" name="hero_slide_alt[<?php echo esc_attr( $i ); ?>]" class="verena-hero-slides__alt" value="<?php echo esc_attr( $alt ); ?>" placeholder="cth. Cincin emas rose gold model peniti" />
					</div>
				<?php endforeach; ?>
			</div>

			<?php submit_button( 'Simpan Hero Slideshow' ); ?>
		</form>
	</div>

	<script>
	( function() {
		document.querySelectorAll( '.verena-hero-slides__slot' ).forEach( function( slot ) {
			var pickBtn  = slot.querySelector( '.verena-hero-slides__pick' );
			var clearBtn = slot.querySelector( '.verena-hero-slides__clear' );
			var input    = slot.querySelector( '.verena-hero-slides__input' );
			var preview  = slot.querySelector( '.verena-hero-slides__preview' );
			var frame;

			pickBtn.addEventListener( 'click', function( e ) {
				e.preventDefault();
				if ( frame ) { frame.open(); return; }
				frame = wp.media( {
					title: 'Pilih Foto',
					library: { type: 'image' },
					multiple: false,
					button: { text: 'Gunakan Foto Ini' },
				} );
				frame.on( 'select', function() {
					var attachment = frame.state().get( 'selection' ).first().toJSON();
					input.value = attachment.id;
					var url = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
					preview.innerHTML = '<img src="' + url + '" alt="" />';
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
