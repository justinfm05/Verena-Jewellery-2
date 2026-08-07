<?php
/**
 * Site header: gold price ticker + sticky navigation.
 *
 * @package Verena_Jewellery
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$shop     = verena_shop_info();
$logo_url = get_template_directory_uri() . '/assets/img/verena-logo-stacked.png';
$wa_nav   = verena_wa_url( 'Halo Verena Jewellery, saya ingin bertanya tentang perhiasan.' );

// Every nav link below is gated by verena_page_is_published() — a page that's
// still Draft (or hasn't been created yet) simply doesn't appear in the nav,
// rather than linking out to a dead/404 page. Flip a page to Published in
// wp-admin whenever it's ready and it'll show up here automatically.
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php get_template_part( 'template-parts/gold-ticker' ); ?>

<header class="site-header" data-nav>
	<div class="site-header__inner">
<?php if ( has_custom_logo() ) : ?>
			<?php the_custom_logo(); ?>
		<?php else : ?>
			<span class="site-logo">
				<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $shop['name'] ); ?> logo" />
			</span>
		<?php endif; ?>

		<nav class="primary-nav" aria-label="Primary">
			<?php if ( verena_page_is_published( 'collection' ) ) : ?>
				<a class="navlink" href="<?php echo esc_url( verena_page_url( 'collection' ) ); ?>">Koleksi</a>
			<?php endif; ?>
			<a class="navlink" href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
			<?php if ( verena_page_is_published( 'bullion' ) ) : ?>
				<a class="navlink" href="<?php echo esc_url( verena_page_url( 'bullion' ) ); ?>">Logam Mulia</a>
			<?php endif; ?>
			<?php if ( verena_page_is_published( 'collection' ) ) : ?>
				<a class="navlink" href="<?php echo esc_url( verena_page_url( 'collection', '?category=cincin-kawin' ) ); ?>">Cincin Kawin</a>
			<?php endif; ?>
			<?php if ( verena_page_is_published( 'buyback' ) ) : ?>
				<a class="navlink" href="<?php echo esc_url( verena_page_url( 'buyback' ) ); ?>">Jual Emas</a>
			<?php endif; ?>
			<?php if ( verena_page_is_published( 'custom' ) ) : ?>
				<a class="navlink" href="<?php echo esc_url( verena_page_url( 'custom' ) ); ?>">Custom Design</a>
			<?php endif; ?>
			<?php if ( verena_page_is_published( 'repairs' ) ) : ?>
				<a class="navlink" href="<?php echo esc_url( verena_page_url( 'repairs' ) ); ?>">Servis &amp; Perbaikan</a>
			<?php endif; ?>
			<?php if ( verena_page_is_published( 'about' ) ) : ?>
				<a class="navlink" href="<?php echo esc_url( verena_page_url( 'about' ) ); ?>">Tentang Kami</a>
			<?php endif; ?>
		</nav>

		<div class="header-actions">
			<a class="header-wa" href="<?php echo esc_url( $wa_nav ); ?>" target="_blank" rel="noopener">
				<?php echo verena_wa_icon( 16 ); // phpcs:ignore -- trusted inline SVG. ?>
				<span>Chat via WhatsApp</span>
			</a>
			<button class="nav-toggle" type="button" aria-label="Buka menu" aria-expanded="false" data-menu-toggle>
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18" stroke="#F2E9CC" stroke-width="2" stroke-linecap="round"/></svg>
			</button>
		</div>
	</div>

	<nav class="mobile-nav" aria-label="Mobile" data-mobile-nav>
		<?php if ( verena_page_is_published( 'collection' ) ) : ?>
			<a href="<?php echo esc_url( verena_page_url( 'collection' ) ); ?>">Koleksi</a>
		<?php endif; ?>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
		<?php if ( verena_page_is_published( 'bullion' ) ) : ?>
			<a href="<?php echo esc_url( verena_page_url( 'bullion' ) ); ?>">Logam Mulia</a>
		<?php endif; ?>
		<?php if ( verena_page_is_published( 'collection' ) ) : ?>
			<a href="<?php echo esc_url( verena_page_url( 'collection', '?category=cincin-kawin' ) ); ?>">Cincin Kawin</a>
		<?php endif; ?>
		<?php if ( verena_page_is_published( 'buyback' ) ) : ?>
			<a href="<?php echo esc_url( verena_page_url( 'buyback' ) ); ?>">Jual Emas</a>
		<?php endif; ?>
		<?php if ( verena_page_is_published( 'custom' ) ) : ?>
			<a href="<?php echo esc_url( verena_page_url( 'custom' ) ); ?>">Custom Design</a>
		<?php endif; ?>
		<?php if ( verena_page_is_published( 'repairs' ) ) : ?>
			<a href="<?php echo esc_url( verena_page_url( 'repairs' ) ); ?>">Servis &amp; Perbaikan</a>
		<?php endif; ?>
		<?php if ( verena_page_is_published( 'about' ) ) : ?>
			<a href="<?php echo esc_url( verena_page_url( 'about' ) ); ?>">Tentang Kami</a>
		<?php endif; ?>
		<?php if ( verena_page_is_published( 'contact' ) ) : ?>
			<a href="<?php echo esc_url( verena_page_url( 'contact' ) ); ?>">Kontak</a>
		<?php endif; ?>
	</nav>
</header>
