<?php

/**
 * The header for the theme
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);
?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="profile" href="https://gmpg.org/xfn/11" />
	<?php wp_head(); ?>
</head>

<body <?php body_class('min-h-screen'); ?>>
	<?php wp_body_open(); ?>

	<div id="page" class="site flex min-h-screen flex-col mx-auto">
		<a class="skip-link screen-reader-text" href="#primary">
			<?php esc_html_e('Skip to content', 'dynamic-book-archive'); ?>
		</a>

		<header id="masthead" class="site-header shadow-[0_0_1px_1px_rgba(214,201,163,0.30)] py-4">
			<div class="max-w-[1440px] lg:px-30 grid grid-cols-12 mx-auto md:items-center lg:gap-6 text-heading">
				<div class="col-span-12 flex items-center gap-3 md:col-start-2 md:col-span-3">
					<div class="w-10 shrink-0">
						<a class="no-underline text-inherit hover:no-underline" href="<?php echo esc_url(home_url('/')); ?>" rel="home">
							<?php the_custom_logo(); ?>
						</a>
					</div>
					<div class="flex flex-col gap-y-2">
						<h1 class="site-title font-display text-lg not-italic font-bold leading-[normal] tracking-[0.4px] uppercase">
							Robert C. Gruzanski
						</h1>
						<sup class="site-description inline-flex items-center justify-between text-[12px] not-italic font-bold leading-[normal] tracking-[0.24px] uppercase">
							<span>Student</span>
							<span>&bull;</span>
							<span>Historian</span>
							<span>&bull;</span>
							<span>Collector</span>
						</sup>
					</div>
				</div>

				<nav id="site-navigation" class="col-span-12 flex flex-wrap items-center justify-end gap-x-10 gap-y-2 text-[11px] tracking-[0.2em] md:col-start-5 md:col-span-8">
					<button class="menu-toggle inline-flex items-center rounded-md border border-library-primary-dark/50 bg-library-primary-light px-3 py-2 text-sm font-medium text-library-secondary md:hidden" aria-controls="primary-menu" aria-expanded="false">
						<?php esc_html_e('Menu', 'dynamic-book-archive'); ?>
					</button>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'menu-1',
							'menu_id'        => 'primary-menu',
							'container'      => false,
							'menu_class'     => 'menu flex flex-col gap-2 text-sm md:flex-row md:items-center md:gap-6',
							'fallback_cb'    => false,
						)
					);
					?>
				</nav>
			</div>

		</header>
        <div class="container max-w-[1440px] px-2 md:px-4 lg:px-30 mx-auto">
		     <?php dba_breadcrumbs(); ?>
  