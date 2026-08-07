<?php
/**
 * Repairs. Create a WP Page with slug "repairs".
 *
 * @package Verena_Jewellery
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$repair_options = array(
	'Krum/Ganti Warna',
	'Cuci Perhiasan',
	'Poles',
	'Gelang Putus',
	'Cincin/Bangle Patah',
	'Cincin/Bangle Resize',
	'Berlian/Batu Permata Hilang',
	'Perbarui Perhiasaan',
	'Lainnya',
);

while ( have_posts() ) : the_post();
	?>
	<main class="container section section-narrow">
		<div class="text-center repairs-intro">
			<p class="eyebrow">Servis &amp; Perbaikan</p>
			<h1><?php the_title(); ?></h1>
		</div>
		<div class="stack"><?php the_content(); ?></div>

		<div
			class="service-card repairs-form text-center"
			x-data="verenaRepairChips(<?php echo esc_attr( wp_json_encode( array( 'waNumber' => verena_whatsapp_number(), 'baseMessage' => verena_wa_message_repair() ) ) ); ?>)"
		>
			<h3>Bagaimana Kami Bisa Membantu?</h3>
			<p class="text-muted" style="margin-bottom:4px;">(Bisa pilih lebih dari satu)</p>
			<div class="chip-row" style="justify-content:center;">
				<?php foreach ( $repair_options as $opt ) : ?>
					<button type="button" class="chip" :class="{ 'is-active': selected.includes( <?php echo esc_attr( wp_json_encode( $opt ) ); ?> ) }" @click="toggle( <?php echo esc_attr( wp_json_encode( $opt ) ); ?> )"><?php echo esc_html( $opt ); ?></button>
				<?php endforeach; ?>
			</div>

			<p class="text-muted">Mulai chat di WhatsApp, lalu kirimkan foto kondisi perhiasan Anda langsung di sana — link ini tidak bisa membawa lampiran foto.</p>
			<a class="btn btn-whatsapp" :href="waLink" target="_blank" rel="noopener">Tanya Servis via WhatsApp</a>
		</div>
	</main>

	<script>
		function verenaRepairChips( config ) {
			return {
				selected: [],
				waNumber: config.waNumber,
				baseMessage: config.baseMessage,
				toggle( option ) {
					this.selected = this.selected.includes( option )
						? this.selected.filter( ( o ) => o !== option )
						: [ ...this.selected, option ];
				},
				get waLink() {
					let msg = this.baseMessage;
					if ( this.selected.length ) {
						msg += '\nKebutuhan: ' + this.selected.join( ', ' );
					}
					return 'https://wa.me/' + this.waNumber + '?text=' + encodeURIComponent( msg );
				},
			};
		}
	</script>
	<?php
endwhile;
get_footer();
