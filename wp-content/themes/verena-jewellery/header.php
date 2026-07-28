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

$layanan = array(
	array( 'label' => 'Servis & Perbaikan',  'url' => verena_page_url( 'repairs' ) ),
	array( 'label' => 'Buyback Emas',        'url' => verena_page_url( 'buyback' ) ),
	array( 'label' => 'Kalkulator Emas',     'url' => verena_page_url( 'calculator' ) ),
);
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
			<a class="site-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( $shop['name'] ); ?> — Beranda">
				<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $shop['name'] ); ?> logo" />
			</a>
		<?php endif; ?>

		<nav class="primary-nav" aria-label="Primary">
			<a class="navlink" href="<?php echo esc_url( verena_page_url( 'collection' ) ); ?>">Koleksi</a>
			<a class="navlink" href="<?php echo esc_url( verena_page_url( 'bullion' ) ); ?>">Logam Mulia</a>
			<a class="navlink" href="<?php echo esc_url( verena_page_url( 'collection', '?category=cincin-kawin' ) ); ?>">Cincin Kawin</a>
			<a class="navlink" href="<?php echo esc_url( verena_page_url( 'custom' ) ); ?>">Custom Design</a>
			<div class="has-dropdown" data-dropdown>
				<button class="navlink" type="button" aria-haspopup="true" aria-expanded="false" data-dropdown-toggle>
					Layanan
					<svg width="10" height="10" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</button>
				<div class="nav-dropdown" role="menu">
					<?php foreach ( $layanan as $item ) : ?>
						<a href="<?php echo esc_url( $item['url'] ); ?>" role="menuitem"><?php echo esc_html( $item['label'] ); ?></a>
					<?php endforeach; ?>
				</div>
			</div>
			<a class="navlink" href="<?php echo esc_url( verena_page_url( 'about' ) ); ?>">Tentang Kami</a>
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
		<a href="<?php echo esc_url( verena_page_url( 'collection' ) ); ?>">Koleksi</a>
		<a href="<?php echo esc_url( verena_page_url( 'bullion' ) ); ?>">Logam Mulia</a>
		<a href="<?php echo esc_url( verena_page_url( 'collection', '?category=cincin-kawin' ) ); ?>">Cincin Kawin</a>
		<a href="<?php echo esc_url( verena_page_url( 'custom' ) ); ?>">Custom Design</a>
		<p class="mobile-nav__label">Layanan</p>
		<?php foreach ( $layanan as $item ) : ?>
			<a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a>
		<?php endforeach; ?>
		<p class="mobile-nav__label">Lainnya</p>
		<a href="<?php echo esc_url( verena_page_url( 'about' ) ); ?>">Tentang Kami</a>
		<a href="<?php echo esc_url( verena_page_url( 'contact' ) ); ?>">Kontak</a>
	</nav>
</header>
