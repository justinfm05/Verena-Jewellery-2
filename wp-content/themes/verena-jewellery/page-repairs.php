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
while ( have_posts() ) : the_post();
	?>
	<main class="container section section-narrow">
		<p class="eyebrow">Layanan</p>
		<h1><?php the_title(); ?></h1>
		<div class="stack"><?php the_content(); ?></div>

		<div class="service-card mt-4 text-center">
			<h3>Butuh perbaikan perhiasan?</h3>
			<p class="text-muted">Mulai chat di WhatsApp, lalu kirimkan foto kondisi perhiasan Anda langsung di sana — link ini tidak bisa membawa lampiran foto.</p>
			<?php verena_whatsapp_button( verena_wa_message_repair(), 'Tanya Servis via WhatsApp' ); ?>
		</div>
	</main>
	<?php
endwhile;
get_footer();
