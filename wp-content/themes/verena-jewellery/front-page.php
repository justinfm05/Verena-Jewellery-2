<?php
/**
 * Homepage: hero, home delivery highlight, custom showcase, testimonials,
 * Instagram feed.
 *
 * @package Verena_Jewellery
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$shop        = verena_shop_info();

/* Hero split-panel images. Drop the real files in with these exact names
   and each side switches over automatically — no code change needed. */
$hero_texture_file  = 'hero-texture.jpg';
$hero_texture_path  = get_template_directory() . '/assets/img/' . $hero_texture_file;
$hero_placeholder   = get_template_directory_uri() . '/assets/img/hero-placeholder.png';

/* Right-panel slideshow — 5 photos, each shown before sliding to the next
   (see .hero__slide / the inline script at the bottom of this file). Set
   under Verena Jewellery > Hero Slideshow in wp-admin (Media Library
   picker), falls back to the theme's bundled placeholder file per slot. */
$hero_slides = verena_get_hero_slides();

/* Past custom-order process clips (video) or stills (image) — set under
   Verena Jewellery > Custom Showcase in wp-admin (Media Library picker),
   falls back to the theme's bundled placeholder file per slot. */
$showcase_items = verena_get_showcase_media();
?>

<!-- Hero -->
<section class="hero">
	<div class="hero__panel hero__panel--text">
		<img
			class="hero__panel-bg"
			src="<?php echo esc_url( file_exists( $hero_texture_path ) ? verena_asset_url( 'assets/img/' . $hero_texture_file ) : $hero_placeholder ); ?>"
			alt=""
			aria-hidden="true"
		/>
		<div class="hero__panel-overlay"></div>
		<div class="hero__inner">
			<div class="hero__content">
				<span class="eyebrow">Verena Jewellery &nbsp;&middot;&nbsp; Jakarta Selatan</span>
				<h1>Perhiasan Emas untuk Momen yang Abadi</h1>
				<p class="hero__body">Emas dan perhiasan pilihan, dipercaya keluarga Jakarta sejak 1999 — kini hanya sejauh satu pesan dari rumah Anda.</p>
				<div class="hero__actions">
					<div class="hero__actions-row">
						<a class="btn btn-gold" href="<?php echo esc_url( verena_page_url( 'buyback' ) ); ?>">Jual Emas Online</a>
						<a class="btn btn-outline-light" href="<?php echo esc_url( verena_page_url( 'bullion' ) ); ?>">Beli Logam Mulia</a>
					</div>
					<div class="hero__actions-row">
						<a class="btn btn-outline-light" href="<?php echo esc_url( verena_page_url( 'custom' ) ); ?>">Konsultasi Custom Design</a>
						<a class="btn btn-outline-light" href="<?php echo esc_url( verena_page_url( 'repairs' ) ); ?>">Servis/Perbaiki Perhiasan</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="hero__panel hero__panel--image" data-hero-slideshow>
		<div class="hero__slide-track">
			<?php foreach ( $hero_slides as $slide ) : ?>
				<img
					class="hero__slide"
					src="<?php echo esc_url( $slide['url'] ); ?>"
					alt="<?php echo esc_attr( $slide['alt'] ); ?>"
				/>
			<?php endforeach; ?>
		</div>
		<button type="button" class="hero__arrow hero__arrow--prev" data-hero-prev aria-label="Foto sebelumnya">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M15 6l-6 6 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
		</button>
		<button type="button" class="hero__arrow hero__arrow--next" data-hero-next aria-label="Foto berikutnya">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
		</button>
	</div>
</section>

<script>
	document.addEventListener( 'DOMContentLoaded', function () {
		var panel = document.querySelector( '[data-hero-slideshow]' );
		var track = panel ? panel.querySelector( '.hero__slide-track' ) : null;
		if ( ! track ) return;

		var real = Array.prototype.slice.call( track.children );
		if ( real.length < 2 ) return;

		// Clone the last slide onto the front and the first slide onto the end,
		// so stepping past either edge (forward via autoplay/Next, backward via
		// Prev) animates into a duplicate, then snaps invisibly back to the
		// matching real slide once the transition finishes — a bidirectional
		// version of the original forward-only loop trick.
		track.insertBefore( real[ real.length - 1 ].cloneNode( true ), real[ 0 ] );
		track.appendChild( real[ 0 ].cloneNode( true ) );

		var current   = 1; // Slot 0 is the prepended clone; the real first slide is 1.
		var lastReal  = real.length; // Slot of the last real slide.
		var lastSlot  = track.children.length - 1; // The appended clone.

		track.style.transition = 'none';
		track.style.transform  = 'translateX(-' + ( current * 100 ) + '%)';
		void track.offsetWidth; // Force reflow before re-enabling the transition.
		track.style.transition = '';

		function goTo( slot ) {
			current = slot;
			track.style.transform = 'translateX(-' + ( current * 100 ) + '%)';
		}

		track.addEventListener( 'transitionend', function () {
			if ( current === lastSlot ) {
				track.style.transition = 'none';
				current = 1;
				track.style.transform = 'translateX(-100%)';
				void track.offsetWidth;
				track.style.transition = '';
			} else if ( current === 0 ) {
				track.style.transition = 'none';
				current = lastReal;
				track.style.transform = 'translateX(-' + ( lastReal * 100 ) + '%)';
				void track.offsetWidth;
				track.style.transition = '';
			}
		} );

		var AUTO_DELAY   = 2800;
		var RESUME_DELAY = 8000;
		var timer;
		var resumeTimer;

		function next() { goTo( current + 1 ); }
		function prev() { goTo( current - 1 ); }

		function startAuto() {
			clearInterval( timer );
			timer = setInterval( next, AUTO_DELAY );
		}

		// Manual navigation pauses autoplay rather than stopping it for good —
		// it resumes on its own after a pause, so the slideshow never gets
		// stuck on whatever slide a visitor last clicked to.
		function pauseThenResume() {
			clearInterval( timer );
			clearTimeout( resumeTimer );
			resumeTimer = setTimeout( startAuto, RESUME_DELAY );
		}

		startAuto();

		var prevBtn = panel.querySelector( '[data-hero-prev]' );
		var nextBtn = panel.querySelector( '[data-hero-next]' );
		if ( prevBtn ) {
			prevBtn.addEventListener( 'click', function () { prev(); pauseThenResume(); } );
		}
		if ( nextBtn ) {
			nextBtn.addEventListener( 'click', function () { next(); pauseThenResume(); } );
		}

		// Finger/mouse swipe: drag follows the pointer 1:1, then either
		// completes the step or snaps back depending on how far it moved.
		var dragging  = false;
		var startX    = 0;
		var deltaX    = 0;
		var panelWidth = 0;
		var SWIPE_THRESHOLD = 0.15; // Fraction of panel width to commit to a slide change.

		track.addEventListener( 'pointerdown', function ( e ) {
			dragging    = true;
			startX      = e.clientX;
			deltaX      = 0;
			panelWidth  = panel.getBoundingClientRect().width;
			track.setPointerCapture( e.pointerId );
			track.style.transition = 'none';
			clearInterval( timer );
			clearTimeout( resumeTimer );
		} );

		track.addEventListener( 'pointermove', function ( e ) {
			if ( ! dragging ) return;
			deltaX = e.clientX - startX;
			var deltaPercent = ( deltaX / panelWidth ) * 100;
			track.style.transform = 'translateX(calc(-' + ( current * 100 ) + '% + ' + deltaPercent + '%))';
		} );

		function endDrag() {
			if ( ! dragging ) return;
			dragging = false;
			track.style.transition = '';
			if ( deltaX / panelWidth < -SWIPE_THRESHOLD ) {
				next();
			} else if ( deltaX / panelWidth > SWIPE_THRESHOLD ) {
				prev();
			} else {
				goTo( current );
			}
			pauseThenResume();
		}
		track.addEventListener( 'pointerup', endDrag );
		track.addEventListener( 'pointercancel', endDrag );
	} );
</script>

<!-- Home delivery highlight -->
<section class="band band--forest">
	<div class="band__inner" style="max-width:760px;">
		<div class="band__head" style="margin-bottom:0;">
			<div class="delivery-journey" aria-hidden="true">
				<div class="delivery-journey__icon">
					<svg width="34" height="34" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 4H15L20 9L12 20L4 9Z" stroke="#C9A24B" stroke-width="1.5" stroke-linejoin="round"/><path d="M4 9H20M12 20L9 4M12 20L15 4" stroke="#C9A24B" stroke-width="1.2" stroke-linejoin="round"/></svg>
				</div>
				<span class="delivery-journey__line"></span>
				<div class="delivery-journey__icon">
					<svg width="34" height="34" viewBox="0 0 24 24" fill="none"><path d="M2 7h11v9H2z" stroke="#C9A24B" stroke-width="1.5" stroke-linejoin="round"/><path d="M13 10h4l4 3v3h-2" stroke="#C9A24B" stroke-width="1.5" stroke-linejoin="round"/><circle cx="6" cy="18" r="1.6" stroke="#C9A24B" stroke-width="1.5"/><circle cx="17" cy="18" r="1.6" stroke="#C9A24B" stroke-width="1.5"/><path d="M2 16h1M17 16h-4" stroke="#C9A24B" stroke-width="1.5"/></svg>
				</div>
				<span class="delivery-journey__line"></span>
				<div class="delivery-journey__icon">
					<svg width="34" height="34" viewBox="0 0 24 24" fill="none"><path d="M4 11l8-7 8 7" stroke="#C9A24B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M6 10v9h12v-9" stroke="#C9A24B" stroke-width="1.5" stroke-linejoin="round"/><path d="M10 19v-5h4v5" stroke="#C9A24B" stroke-width="1.5" stroke-linejoin="round"/></svg>
				</div>
			</div>
			<h2>Diantar Langsung ke Rumah Anda</h2>
			<p>Verena Jewellery mengirimkan perhiasan dan emas batangan Anda dengan aman, langsung ke rumah Anda.</p>
			<div class="delivery-stats">
				<div class="delivery-stat">
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3z" stroke="#C9A24B" stroke-width="1.5" stroke-linejoin="round"/></svg>
					<strong>Up to 100 Juta</strong>
					<span>Asuransi Pengiriman</span>
				</div>
				<div class="delivery-stat">
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 21s7-6.5 7-12a7 7 0 10-14 0c0 5.5 7 12 7 12z" stroke="#C9A24B" stroke-width="1.5" stroke-linejoin="round"/><circle cx="12" cy="9" r="2.3" stroke="#C9A24B" stroke-width="1.5"/></svg>
					<strong>Real-time</strong>
					<span>Lacak via GPS</span>
				</div>
				<div class="delivery-stat">
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="8.5" stroke="#C9A24B" stroke-width="1.5"/><path d="M12 7.5V12l3 2" stroke="#C9A24B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
					<strong>1&ndash;24 Jam</strong>
					<span>Sampai di Rumah</span>
				</div>
			</div>
			<a class="btn btn-gold" style="margin-top:24px;" href="<?php echo esc_url( verena_page_url( 'bullion' ) ); ?>">
				Beli Logam Mulia Sekarang
			</a>
		</div>
	</div>
</section>

<!-- Custom Showcase -->
<section class="band band--champagne">
	<div class="band__inner">
		<div class="band__head">
			<h2>Custom Showcase</h2>
			<p>Laser Engraving &nbsp;&nbsp;&middot;&nbsp;&nbsp; Certified Designer &nbsp;&nbsp;&middot;&nbsp;&nbsp; High Quality</p>
		</div>

		<div class="showcase-grid">
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
			<p class="text-center" style="margin-top:20px;">
				<a class="ig-more" href="<?php echo esc_url( $shop['instagram'] ); ?>" target="_blank" rel="noopener">Lihat lebih banyak contoh di Instagram &rarr;</a>
			</p>
		<?php endif; ?>

		<p class="text-center showcase-cta-line">Konsultasi Sekarang dan &#10024; Wujudkan Impianmu &#10024;</p>

		<div class="text-center">
			<a class="btn btn-gold" href="<?php echo esc_url( verena_page_url( 'custom' ) ); ?>">Konsultasi Custom Design</a>
		</div>
	</div>
</section>

<!-- Testimonials -->
<section class="band band--forest" style="padding:56px 24px;">
	<div class="band__inner" style="max-width:1160px;">
		<div class="band__head" style="margin-bottom:32px;">
			<span class="eyebrow">Dipercaya Sejak <?php echo esc_html( $shop['established'] ); ?></span>
			<h2>Kata Pelanggan Kami</h2>
		</div>
		<?php if ( shortcode_exists( 'reviews-feed' ) ) : ?>
			<?php echo do_shortcode( '[reviews-feed feed=1]' ); ?>
		<?php else : ?>
			<div class="testi-grid">
				<?php foreach ( verena_testimonials() as $t ) : ?>
					<div class="testi-card">
						<div class="testi-stars">
							<?php for ( $i = 0; $i < 5; $i++ ) : ?>
								<svg width="16" height="16" viewBox="0 0 24 24" fill="#C9A24B" aria-hidden="true"><path d="M12 2l2.9 6.6 7.1.7-5.4 4.7 1.6 7-6.2-3.7-6.2 3.7 1.6-7-5.4-4.7 7.1-.7z"/></svg>
							<?php endfor; ?>
						</div>
						<p class="testi-quote">&ldquo;<?php echo esc_html( $t['quote'] ); ?>&rdquo;</p>
						<div>
							<p class="testi-name"><?php echo esc_html( $t['name'] ); ?></p>
							<p class="testi-loc"><?php echo esc_html( $t['location'] ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<!-- Instagram feed -->
<section class="band band--champagne">
	<div class="band__inner">
		<div class="ig-head">
			<div>
				<span class="eyebrow">Ikuti Kami</span>
				<h2>@verenajewellery.id</h2>
			</div>
			<?php if ( $shop['instagram'] ) : ?>
				<a class="ig-more" href="<?php echo esc_url( $shop['instagram'] ); ?>" target="_blank" rel="noopener">Lihat di Instagram &rarr;</a>
			<?php endif; ?>
		</div>
		<?php if ( shortcode_exists( 'instagram-feed' ) ) : ?>
			<?php echo do_shortcode( '[instagram-feed feed=1]' ); ?>
		<?php else : ?>
			<div class="ig-grid">
				<?php foreach ( verena_instagram_posts() as $post ) : ?>
					<a class="ig-item" href="<?php echo esc_url( $shop['instagram'] ?: '#' ); ?>" target="_blank" rel="noopener">
						<span class="ig-item__cap"><?php echo esc_html( $post['caption'] ); ?></span>
						<span class="ig-item__likes">
							<svg width="10" height="10" viewBox="0 0 24 24" fill="#F2E9CC" aria-hidden="true"><path d="M12 21s-7.5-4.6-10-9.1C.5 8.6 2.3 5 6 5c2 0 3.4 1 4 2.3C10.6 6 12 5 14 5c3.7 0 5.5 3.6 4 6.9C19.5 16.4 12 21 12 21z"/></svg>
							<?php echo esc_html( $post['likes'] ); ?>
						</span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php get_footer(); ?>
