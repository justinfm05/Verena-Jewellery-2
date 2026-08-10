<?php
/**
 * Theme bootstrap.
 *
 * @package Verena_Jewellery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'VERENA_THEME_VERSION', '2.0.5' );

function verena_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'custom-logo', array(
		'height'      => 80,
		'width'       => 240,
		'flex-height' => true,
		'flex-width'  => true,
	) );

	register_nav_menus(
		array(
			'primary' => 'Primary Navigation',
			'footer'  => 'Footer Navigation',
		)
	);
}
add_action( 'after_setup_theme', 'verena_theme_setup' );

/**
 * All styling lives in a single hand-authored style.css (design tokens +
 * components) — no build step, so it can be edited and deployed directly
 * on SiteGround without a Node toolchain. Alpine.js is loaded from a CDN
 * for the calculator's interactivity (add/remove rows, live totals); it's
 * the only third-party runtime dependency in the theme.
 */
/**
 * filemtime-based version for a theme-root-relative asset, so editing
 * style.css or main.js busts the browser's cached copy immediately instead
 * of waiting for a manual VERENA_THEME_VERSION bump (see verena_asset_url()
 * for the same pattern applied to hand-written <img>/<video> tags).
 *
 * @param string $relative_path Path under the theme root, e.g. 'style.css'.
 * @return string
 */
function verena_theme_asset_version( $relative_path ) {
	$file_path = get_template_directory() . '/' . ltrim( $relative_path, '/' );
	return file_exists( $file_path ) ? (string) filemtime( $file_path ) : VERENA_THEME_VERSION;
}

function verena_enqueue_assets() {
	// Google Fonts — Cormorant Garamond (display) + Manrope (UI/body).
	wp_enqueue_style(
		'verena-fonts',
		'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,500;1,600&family=Manrope:wght@400;500;600;700&display=swap',
		array(),
		null
	);
	wp_enqueue_style( 'verena-style', get_stylesheet_uri(), array( 'verena-fonts' ), verena_theme_asset_version( 'style.css' ) );

	wp_enqueue_script(
		'alpinejs',
		'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js',
		array(),
		'3',
		array( 'in_footer' => true, 'strategy' => 'defer' )
	);

	wp_enqueue_script(
		'verena-main',
		get_template_directory_uri() . '/assets/js/main.js',
		array(),
		verena_theme_asset_version( 'assets/js/main.js' ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'verena_enqueue_assets' );

/**
 * Shop-wide values (WhatsApp number, shop name, etc) set in
 * wp-admin > Verena Jewellery > Settings, made available to templates.
 *
 * @return array
 */
function verena_shop_info() {
	return array(
		'name'        => get_option( 'verena_shop_name', 'Verena Jewellery' ),
		'whatsapp'    => get_option( 'verena_whatsapp_number', '628111099399' ),
		'phone'       => get_option( 'verena_shop_phone', '(+62) 21 72793226' ),
		'address'     => get_option( 'verena_shop_address', 'ITC Fatmawati, Lt. 1 No. 175-177, Jl. RS. Fatmawati, Jakarta Selatan' ),
		'hours'       => get_option( 'verena_shop_hours', 'Senin – Sabtu, 11.30 – 19.00 WIB (Minggu tutup)' ),
		'established' => get_option( 'verena_shop_established', '1999' ),
		'instagram'   => get_option( 'verena_instagram_url', 'https://www.instagram.com/verenajewellery.id/' ),
	);
}

/**
 * Keep /admin and wp-admin out of search engines' good graces beyond WP's
 * own defaults, and make sure the calculator's saved-list pages aren't
 * indexed individually (they're personal, shareable-by-link content, not
 * public catalog pages).
 */
function verena_robots_txt( $output, $public ) {
	if ( '1' !== $public ) {
		return $output;
	}
	$output .= "Disallow: /wp-admin/\n";
	$output .= "Disallow: /gold-calculator/*/\n";
	$output .= "Allow: /wp-admin/admin-ajax.php\n";
	return $output;
}
add_filter( 'robots_txt', 'verena_robots_txt', 10, 2 );

/**
 * Gold price ticker data for the bar above the header on every page.
 *
 * Reads the 1-gram sell price for Antam 2026, Emasku, and UBS from the same
 * Google Sheet the Logam Mulia page uses (verena_get_bullion_sheet_data(), in
 * the plugin — synced every 5 minutes). Any brand missing a 1g row — or the
 * whole thing if the plugin isn't active yet — falls back to the demo
 * numbers below, so the ticker never renders empty. The reporting date shown
 * is the most recent of the three displayed brands' own `changed_at` (the
 * last time that brand's price actually changed, not just the last sync
 * attempt) — same source of truth as "Harga terakhir diperbarui" on
 * page-logam-mulia.php, so the two never disagree. Converted to Jakarta time
 * regardless of the site's own configured WordPress timezone setting.
 *
 * @return array{date:string, rates:array<int,array{label:string,price:string}>}
 */
function verena_gold_rates() {
	// Brands shown in the ticker (display order) + fallback prices used only
	// until the sheet has a 1-gram row for that brand.
	$display = array(
		'Antam 2026' => '1.850.000',
		'Emasku'     => '1.800.000',
		'UBS'        => '1.780.000',
	);

	$sheet = function_exists( 'verena_get_bullion_sheet_data' ) ? verena_get_bullion_sheet_data() : array();
	$rates = array();
	$latest_changed_at = null;

	foreach ( $display as $label => $fallback ) {
		$one_gram_sell = verena_bullion_one_gram_sell( $sheet, $label );
		$price         = null !== $one_gram_sell ? number_format( (int) $one_gram_sell, 0, ',', '.' ) : $fallback;
		$rates[]       = array( 'label' => $label, 'price' => $price );

		$brand_key   = ( 'Antam 2026' === $label ) ? 'antam' : strtolower( $label );
		$changed_at  = $sheet['changed_at'][ $brand_key ] ?? null;
		if ( $changed_at && ( null === $latest_changed_at || $changed_at > $latest_changed_at ) ) {
			$latest_changed_at = $changed_at;
		}
	}

	$date_label = $latest_changed_at
		? wp_date( 'j F Y', $latest_changed_at, new DateTimeZone( 'Asia/Jakarta' ) )
		: wp_date( 'j F Y' );

	return array(
		'date'  => $date_label,
		'rates' => $rates,
	);
}

/**
 * Find the 1-gram sell price for one ticker brand within the parsed bullion
 * sheet data (see verena_get_bullion_sheet_data()).
 *
 * @param array  $sheet {antam,emasku,ubs} arrays of gram-keyed rows.
 * @param string $label One of the $display keys in verena_gold_rates(), e.g. "Antam 2026".
 * @return int|float|null
 */
function verena_bullion_one_gram_sell( $sheet, $label ) {
	$brand_key = ( 'Antam 2026' === $label ) ? 'antam' : strtolower( $label );
	$rows      = $sheet[ $brand_key ] ?? array();

	foreach ( $rows as $row ) {
		if ( ! isset( $row['gram'] ) || 1.0 !== (float) $row['gram'] ) {
			continue;
		}
		return 'Antam 2026' === $label ? ( $row['2026']['sell'] ?? null ) : ( $row['sell'] ?? null );
	}

	return null;
}

/**
 * Homepage testimonials placeholders. front-page.php only uses these when no
 * `[reviews-feed]` shortcode is registered — install and connect a Google
 * Business Profile with a reviews feed plugin (e.g. Smash Balloon Reviews
 * Feed) and the homepage switches to real Google reviews automatically, no
 * code change here needed.
 *
 * @return array<int,array{quote:string,name:string,location:string}>
 */
function verena_testimonials() {
	return array(
		array(
			'quote'    => 'Sudah 3 generasi keluarga kami beli emas di Verena. Selalu jujur soal kadar dan harga.',
			'name'     => 'Ibu Sulistyawati',
			'location' => 'Cilandak, Jakarta Selatan',
		),
		array(
			'quote'    => 'Cincin kawin custom kami jadi persis seperti yang kami bayangkan. Prosesnya cepat dan ramah lewat WhatsApp.',
			'name'     => 'Andra & Nadia',
			'location' => 'Kebayoran Baru',
		),
		array(
			'quote'    => 'Pelayanan seperti keluarga sendiri. Toko langganan sejak saya kecil, sekarang saya bawa anak saya juga.',
			'name'     => 'Bapak Hartono',
			'location' => 'Pondok Indah',
		),
	);
}

/**
 * Instagram feed placeholders for the homepage grid. front-page.php only uses
 * these when no `[instagram-feed]` shortcode is registered — install and
 * connect an account with an Instagram feed plugin (e.g. Smash Balloon) and
 * the homepage switches to the real feed automatically, no code change here
 * needed.
 *
 * @return array<int,array{caption:string,likes:string}>
 */
function verena_instagram_posts() {
	return array(
		array( 'caption' => 'post: cincin kawin', 'likes' => '128' ),
		array( 'caption' => 'post: kalung baru', 'likes' => '94' ),
		array( 'caption' => 'post: proses custom', 'likes' => '211' ),
		array( 'caption' => 'post: testimoni klien', 'likes' => '76' ),
		array( 'caption' => 'post: gelang emas', 'likes' => '153' ),
		array( 'caption' => 'post: di balik layar', 'likes' => '88' ),
	);
}

/**
 * Build a wa.me deep link with a pre-filled message, using the shop's central
 * WhatsApp number. Mirrors the plugin's verena_build_wa_link() but is
 * theme-safe (works even if the plugin's helper name changes).
 *
 * @param string $message
 * @return string
 */
function verena_wa_url( $message ) {
	if ( function_exists( 'verena_build_wa_link' ) ) {
		return verena_build_wa_link( $message );
	}
	$shop   = verena_shop_info();
	$number = preg_replace( '/\D+/', '', (string) $shop['whatsapp'] );
	return 'https://wa.me/' . $number . '?text=' . rawurlencode( $message );
}

/**
 * Inline WhatsApp glyph used in nav/hero/cards. Fill is set via CSS (svg path
 * inherits currentColor context through the .btn-* / .card__cta rules).
 *
 * @param int $size
 * @return string
 */
function verena_wa_icon( $size = 16 ) {
	return sprintf(
		'<svg width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12c0 1.87.5 3.62 1.38 5.13L2 22l5.05-1.32A9.94 9.94 0 0012 22c5.52 0 10-4.48 10-10S17.52 2 12 2z"/></svg>',
		(int) $size
	);
}

/**
 * Central map of the site's page slugs. Every internal nav/footer/CTA link
 * routes through here, so renaming a slug is a ONE-LINE change (and the
 * matching page-{slug}.php template file must be renamed to match).
 *
 * @param string $key  Logical page key.
 * @return string      The page slug (no slashes).
 */
function verena_page_slug( $key ) {
	$slugs = array(
		'collection' => 'collection',      // Koleksi Perhiasan  → page-collection.php
		'custom'     => 'custom-orders',   // Perhiasan Custom   → page-custom-orders.php
		'bullion'    => 'logam-mulia',     // Logam Mulia        → page-logam-mulia.php
		'repairs'    => 'repairs',         // Servis & Perbaikan → page-repairs.php
		'buyback'    => 'buyback-emas',    // Buyback Emas       → page-buyback-emas.php
		'calculator' => 'gold-calculator', // Kalkulator Emas    → page-gold-calculator.php
		'about'      => 'about-us',        // Tentang Kami       → page-about-us.php
		'contact'    => 'contact-us',      // Kontak Kami        → page-contact-us.php
	);
	/**
	 * Allow the slug map to be overridden (e.g. per-language) without editing
	 * the theme. Filter: verena_page_slugs.
	 */
	$slugs = apply_filters( 'verena_page_slugs', $slugs );
	return $slugs[ $key ] ?? '';
}

/**
 * Home-relative URL for a mapped page, with optional trailing path/query.
 *
 * @param string $key   Logical page key (see verena_page_slug()).
 * @param string $extra Appended after the trailing slash (e.g. "?category=x" or "antam/").
 * @return string
 */
function verena_page_url( $key, $extra = '' ) {
	return home_url( '/' . verena_page_slug( $key ) . '/' . $extra );
}

/**
 * Whether a mapped page actually exists and is published — lets nav links
 * automatically hide pages that are still Draft or not yet created, instead
 * of linking to a dead/404 page. Toggle a page to Draft/Published in
 * wp-admin to hide/show it from nav; no code changes needed either way.
 *
 * @param string $key See verena_page_slug().
 * @return bool
 */
function verena_page_is_published( $key ) {
	$slug = verena_page_slug( $key );
	if ( '' === $slug ) {
		return false;
	}
	$page = get_page_by_path( $slug );
	return $page instanceof WP_Post && 'publish' === $page->post_status;
}

/**
 * Theme asset URL with a filemtime-based cache-buster. Logo/photo <img> tags
 * are hand-written with a plain path (not wp_enqueue'd), so replacing a file
 * on disk (same filename, new bytes — e.g. swapping in a sharper logo) would
 * otherwise keep serving the old cached bytes from browsers and the host's
 * page cache indefinitely, since the URL never changes. Appending the file's
 * own mtime makes the URL change automatically whenever the file does.
 *
 * @param string $relative_path Path under the theme root, e.g. 'assets/img/antam-logo.png'.
 * @return string
 */
function verena_asset_url( $relative_path ) {
	$relative_path = ltrim( $relative_path, '/' );
	$file_path     = get_template_directory() . '/' . $relative_path;
	$version       = file_exists( $file_path ) ? filemtime( $file_path ) : VERENA_THEME_VERSION;
	return get_template_directory_uri() . '/' . $relative_path . '?ver=' . $version;
}

/**
 * The 4 "Custom Showcase" slots shown on the homepage and the Custom Design
 * page (front-page.php / page-custom-orders.php). Each slot pulls from the
 * WordPress Media Library — set by staff under Verena Jewellery > Custom
 * Showcase in wp-admin (see verena_jt_render_showcase_media_page() in the
 * plugin) — so swapping a photo/video never needs a developer or a theme
 * deploy. Falls back to the theme's bundled placeholder file for any slot
 * that hasn't been set yet.
 *
 * @return array<int, array{type: string, url: string}>
 */
function verena_get_showcase_media() {
	$defaults = array(
		array( 'type' => 'video', 'file' => 'assets/video/custom-showcase-1.mp4' ),
		array( 'type' => 'video', 'file' => 'assets/video/custom-showcase-2.mp4' ),
		array( 'type' => 'image', 'file' => 'assets/img/custom-showcase-3.jpg' ),
		array( 'type' => 'video', 'file' => 'assets/video/custom-showcase-4.mp4' ),
	);
	$attachment_ids = get_option( 'verena_showcase_media', array() );

	$items = array();
	foreach ( $defaults as $i => $default ) {
		$attachment_id = (int) ( $attachment_ids[ $i ] ?? 0 );
		if ( $attachment_id && 'attachment' === get_post_type( $attachment_id ) ) {
			$mime      = get_post_mime_type( $attachment_id );
			$items[]   = array(
				'type' => ( $mime && 0 === strpos( $mime, 'video/' ) ) ? 'video' : 'image',
				'url'  => wp_get_attachment_url( $attachment_id ),
			);
			continue;
		}
		$abs_path = get_template_directory() . '/' . $default['file'];
		$items[]  = array(
			'type' => $default['type'],
			'url'  => file_exists( $abs_path ) ? verena_asset_url( $default['file'] ) : '',
		);
	}
	return $items;
}

/**
 * The 5 hero slideshow photos on the homepage (front-page.php). Each slot
 * pulls from the WordPress Media Library — set by staff under Verena
 * Jewellery > Hero Slideshow in wp-admin (see
 * verena_jt_render_hero_slides_page() in the plugin) — so swapping a photo
 * never needs a developer or a theme deploy. Falls back to the theme's
 * bundled placeholder file for any slot that hasn't been set yet.
 *
 * @return array<int, array{url: string, alt: string}>
 */
function verena_get_hero_slides() {
	$defaults = array(
		array( 'file' => 'assets/img/hero-slide-1.jpg', 'alt' => 'Model mengenakan kalung liontin hati emas' ),
		array( 'file' => 'assets/img/hero-slide-2.jpg', 'alt' => 'Anting emas dengan baguette diamond' ),
		array( 'file' => 'assets/img/hero-slide-3.jpg', 'alt' => 'Cincin dan anting emas rose gold motif anyaman' ),
		array( 'file' => 'assets/img/hero-slide-4.jpg', 'alt' => 'Kalung dan cincin emas rose gold model peniti' ),
		array( 'file' => 'assets/img/hero-slide-5.jpg', 'alt' => 'Koleksi cincin emas Verena Jewellery' ),
	);
	$slides = get_option( 'verena_hero_slides', array() );

	$items = array();
	foreach ( $defaults as $i => $default ) {
		$slide         = $slides[ $i ] ?? array();
		$attachment_id = (int) ( $slide['attachment_id'] ?? 0 );
		if ( $attachment_id && 'attachment' === get_post_type( $attachment_id ) ) {
			$alt = trim( (string) ( $slide['alt'] ?? '' ) );
			if ( '' === $alt ) {
				$alt = trim( (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) );
			}
			$items[] = array(
				'url' => wp_get_attachment_url( $attachment_id ),
				'alt' => '' !== $alt ? $alt : 'Perhiasan Verena Jewellery',
			);
			continue;
		}
		$abs_path = get_template_directory() . '/' . $default['file'];
		$items[]  = array(
			'url' => file_exists( $abs_path ) ? verena_asset_url( $default['file'] ) : get_template_directory_uri() . '/assets/img/hero-placeholder.png',
			'alt' => $default['alt'],
		);
	}
	return $items;
}

require_once get_template_directory() . '/inc/template-helpers.php';
