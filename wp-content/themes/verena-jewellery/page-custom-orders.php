<?php
/**
 * Custom design inquiry. Create a WP Page with slug "custom-orders".
 * Any page content (edited in wp-admin) renders above the interactive form,
 * which builds a pre-filled WhatsApp message from the visitor's selections.
 *
 * @package Verena_Jewellery
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$shop      = verena_shop_info();
$wa_number = preg_replace( '/\D+/', '', (string) $shop['whatsapp'] );
$occasions = array( 'Cincin Kawin', 'Lamaran', 'Ulang Tahun', 'Hadiah Keluarga', 'Koleksi Pribadi', 'Lainnya' );

/* Same Custom Showcase media as the homepage — set under Verena Jewellery >
   Custom Showcase in wp-admin (Media Library picker), see
   verena_get_showcase_media() in functions.php. */
$showcase_items = verena_get_showcase_media();

while ( have_posts() ) :
	the_post();
	if ( trim( get_the_content() ) ) :
		?>
		<main class="container section section-narrow">
			<div class="stack"><?php the_content(); ?></div>
		</main>
		<?php
	endif;
endwhile;
?>

<section class="band" x-data="verenaCustomForm(<?php echo esc_attr( wp_json_encode( $wa_number ) ); ?>)">
	<div class="band__inner" style="max-width:720px;">
		<div class="band__head">
			<span class="eyebrow">Custom Design</span>
			<h2>Wujudkan Perhiasan Impian Anda</h2>
			<p>Ceritakan kebutuhan Anda — tim kami akan menghubungi via WhatsApp untuk konsultasi desain dan estimasi harga.</p>
		</div>
	</div>

	<div class="band__inner">
		<div class="showcase-grid" style="margin-bottom:20px;">
			<?php foreach ( $showcase_items as $item ) : ?>
				<div class="showcase-video">
					<?php if ( $item['url'] ) : ?>
						<?php if ( 'video' === $item['type'] ) : ?>
							<video src="<?php echo esc_url( $item['url'] ); ?>" controls playsinline muted autoplay loop></video>
						<?php else : ?>
							<img src="<?php echo esc_url( $item['url'] ); ?>" alt="Contoh hasil custom design" />
						<?php endif; ?>
					<?php else : ?>
						<span class="showcase-video__placeholder">Placeholder Video of Custom Product</span>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<?php if ( $shop['instagram'] ) : ?>
			<p class="text-center" style="margin-bottom:32px;">
				<a class="ig-more" href="<?php echo esc_url( $shop['instagram'] ); ?>" target="_blank" rel="noopener">Lihat lebih banyak contoh di Instagram &rarr;</a>
			</p>
		<?php endif; ?>
	</div>

	<div class="band__inner" style="max-width:720px;">
		<div class="form-card">
			<div>
				<label class="field-label">Untuk acara apa? <span class="required-mark">*</span></label>
				<div class="chip-row">
					<?php foreach ( $occasions as $occ ) : ?>
						<button type="button" class="chip" :class="{ 'is-active': occasion === <?php echo esc_attr( wp_json_encode( $occ ) ); ?> }" @click="occasion = <?php echo esc_attr( wp_json_encode( $occ ) ); ?>"><?php echo esc_html( $occ ); ?></button>
					<?php endforeach; ?>
				</div>
			</div>

			<div>
				<label class="field-label" for="vj-budget">Kisaran Budget <span class="required-mark">*</span></label>
				<select id="vj-budget" class="vj-field" x-model="budget">
					<option value="">Pilih kisaran budget</option>
					<option value="&lt; Rp 5 juta">&lt; Rp 5 juta</option>
					<option value="Rp 5–15 juta">Rp 5–15 juta</option>
					<option value="Rp 15–30 juta">Rp 15–30 juta</option>
					<option value="&gt; Rp 30 juta">&gt; Rp 30 juta</option>
				</select>
			</div>

			<div>
				<label class="field-label" for="vj-name">Nama Anda <span class="required-mark">*</span></label>
				<input id="vj-name" type="text" class="vj-field" placeholder="cth. Dewi Anggraini" x-model="name" />
			</div>

			<div>
				<label class="field-label" for="vj-desc">Deskripsi Perhiasan yang Diinginkan <span class="field-optional">(opsional)</span></label>
				<textarea id="vj-desc" class="vj-field" rows="4" placeholder="cth. Cincin kawin emas 18K dengan ukiran nama, model minimalis..." x-model="desc" style="resize:vertical;"></textarea>
			</div>

			<div class="form-note" style="border-left:none; padding-left:0;">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 16l4.6-4.6a2 2 0 012.8 0L16 16M14 14l1.6-1.6a2 2 0 012.8 0L20 14M4 8h16M4 4h16M4 20h16" stroke="#A08B4A" stroke-width="1.5" stroke-linecap="round"/></svg>
				<span><span class="form-note__label">Note:</span> Punya foto referensi? Lampirkan langsung saat chat WhatsApp</span>
			</div>

			<a class="btn btn-gold btn-block" :class="{ 'is-disabled': ! isValid }" :href="isValid ? waLink : '#'" @click="if ( ! isValid ) { $event.preventDefault(); touched = true; }" target="_blank" rel="noopener">
				<?php echo verena_wa_icon( 18 ); // phpcs:ignore -- trusted inline SVG. ?>
				Kirim ke WhatsApp
			</a>

			<p class="form-note" style="margin-top:8px;">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2 7h11v9H2z" stroke="#A08B4A" stroke-width="1.5" stroke-linejoin="round"/><path d="M13 10h4l4 3v3h-2" stroke="#A08B4A" stroke-width="1.5" stroke-linejoin="round"/><circle cx="6" cy="18" r="1.6" stroke="#A08B4A" stroke-width="1.5"/><circle cx="17" cy="18" r="1.6" stroke="#A08B4A" stroke-width="1.5"/><path d="M2 16h1M17 16h-4" stroke="#A08B4A" stroke-width="1.5"/></svg>
				<span>Diantar aman ke rumah Anda via Paxel</span>
			</p>

			<p class="form-hint"><span class="required-mark">*</span> Wajib diisi</p>
			<p class="form-error" x-cloak x-show="touched && ! isValid">Lengkapi dulu field bertanda <span class="required-mark">*</span> di atas.</p>
		</div>
	</div>
</section>

<script>
	function verenaCustomForm( waNumber ) {
		return {
			number: waNumber,
			occasion: '',
			budget: '',
			name: '',
			desc: '',
			touched: false,
			get isValid() {
				return !! ( this.occasion && this.budget && this.name.trim() );
			},
			get waLink() {
				let msg = 'Halo Verena Jewellery, saya ingin konsultasi custom design.\n';
				if ( this.occasion ) msg += 'Acara: ' + this.occasion + '\n';
				if ( this.budget ) msg += 'Budget: ' + this.budget + '\n';
				if ( this.name ) msg += 'Nama: ' + this.name + '\n';
				if ( this.desc ) msg += 'Deskripsi: ' + this.desc;
				return 'https://wa.me/' + this.number + '?text=' + encodeURIComponent( msg );
			}
		};
	}
</script>

<?php
get_footer();
