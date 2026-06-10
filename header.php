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

		<header id="masthead" class="site-header shadow-[0_0_1px_1px_rgba(214,201,163,0.30)] py-4 px-2 md:px-4 lg:px-0">
			<div class="max-w-[1440px] lg:px-30 flex justify-between mx-auto items-center text-content">
				<div class="flex items-center gap-4 px-1 md:px-0">
					<div class="w-16 md:w-20 shrink-0">
						<?php dba_the_site_logo(); ?>
					</div>
					<div class="flex flex-col gap-y-3">
						<h1 class="site-title font-display text-base md:text-lg not-italic font-bold leading-[normal] tracking-[0.28px] md:tracking-[1.1px] uppercase">
							Robert C. Gruzanski
						</h1>
						<sup class="site-description inline-flex items-center justify-between text-xs md:text-sm not-italic font-bold leading-[normal] tracking-[0.1px] md:tracking-[0.24px]">
							<span>Curator of the Gruzanski Archives</span>
						</sup>
					</div>
				</div>

				<nav id="site-navigation" class="flex flex-wrap items-center justify-end gap-x-10 gap-y-2 text-[11px] tracking-[0.2em]"
					x-data="mobileNav()"
					@keydown.escape.window="close()">
					<button type="button" class="inline-flex items-center rounded-md border bg-primary px-3 py-2 font-medium text-brand-muted md:hidden"
						x-ref="menuToggle"
						aria-controls="primary-menu-panel"
						:aria-expanded="open ? 'true' : 'false'"
						@click.stop="toggle()">
						<span class="sr-only"><?php esc_html_e('Menu', 'dynamic-book-archive'); ?></span>
						<?php dba_the_inline_icon('bx/bx-menu', 'size-6 shrink-0'); ?>
					</button>
					<div id="primary-menu-panel" class="primary-menu-panel hidden md:flex md:flex-row md:items-center md:gap-6"
						:class="{ 'hidden': !open }">
						<button type="button" class="md:hidden inline-flex cursor-pointer items-center justify-center rounded-md border-0 bg-transparent p-2 text-content transition-opacity hover:opacity-80 max-md:fixed max-md:top-[max(0.5rem,env(safe-area-inset-top,0px))] max-md:end-[max(0.5rem,env(safe-area-inset-right,0px))] max-md:z-[60] max-md:min-h-11 max-md:min-w-11" aria-label="<?php esc_attr_e('Close menu', 'dynamic-book-archive'); ?>"
							x-ref="menuClose"
							@click.stop="close()">
							<?php dba_the_inline_icon('bx/bx-x', 'flex size-10 shrink-0 items-center justify-center text-current [&>svg]:block [&>svg]:size-full'); ?>
						</button>
						<div class="primary-menu-panel__mark md:hidden text-site-navigation-secondary" aria-hidden="true">
							<?php dba_the_inline_icon('bx/bx-globe', 'size-14 shrink-0 opacity-50'); ?>
						</div>
						<?php
						wp_nav_menu(
							array(
								'theme_location' => 'menu-1',
								'menu_id'        => 'primary-menu',
								'container'      => false,
								'menu_class'     => 'primary-menu-panel__list flex flex-col gap-6 md:flex-row md:items-center md:gap-6 md:text-sm md:px-4 [&_a]:no-underline',
								'fallback_cb'    => false,
							)
						);
						?>
					</div>
				</nav>
			</div>

		</header>
		<div class="container max-w-[1440px] px-2 md:px-4 lg:px-30 mx-auto">
			<?php dba_breadcrumbs(); ?>