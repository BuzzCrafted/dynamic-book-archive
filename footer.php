<?php

/**
 * The footer for the theme
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

$book_categories = get_terms(array(
	'taxonomy' => 'book_category',
	'hide_empty' => false,
));

$book_categories_list = array_map(function ($book_category) {
	return array(
		'link' => get_term_link($book_category->term_id),
		'name' => $book_category->name,
	);
}, $book_categories)

?>
<footer class="site-footer py-2 px-2 md:px-4 lg:px-0 text-heading bg-surface">
	<div class="max-w-[1440px] lg:px-2 mx-auto pb-2">
		<div class="flex flex-col md:flex-row items-stretch gap-x-16">
			<div class="flex flex-col items-center gap-2">
				<div class="site-footer-brand-logo w-16 md:w-20 shrink-0">
					<?php dba_the_site_logo('w-full h-auto object-contain brightness-110', true); ?>
				</div>
				<div class="flex flex-col gap-y-2">
					<h1 class="site-title font-display text-base not-italic font-bold leading-[normal] tracking-[0.4px] uppercase">
						Robert C. Gruzanski
					</h1>
					<sup class="site-description inline-flex items-center justify-between text-[10px] not-italic font-bold leading-[normal] tracking-[0.24px] uppercase">
						<span>Curator of the Gruzanski Archive</span>
					</sup>
				</div>
			</div>
			<div class="hidden md:flex gap-1 md:gap-5">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'menu-1',
						'menu_id'        => 'footer-menu',
						'container'      => false,
						'menu_class'     => 'footer-menu flex flex-row gap-6 text-base tracking-[0.2em] uppercase',
						'fallback_cb'    => false,
					)
				);
				?>
			</div>
		</div>
	</div>
	<hr class="hr-separator">
	<div class="max-w-[1440px] lg:px-2 mx-auto pt-2">
		<div class="flex flex-col gap-1">
			<span class="inline-flex text-sm text-center md:text-left not-italic leading-[normal] tracking-[0.24px]"> &copy; 2002–<?php echo date('Y'); ?> Robert C. Gruzanski. All rights reserved.</span>
			<span class="inline-flex text-xs text-center md:text-left text-heading/70 not-italic leading-[normal] tracking-[0.24px]">Content may not be reproduced or used without permission. Please contact for usage requests.</span>
		</div>
	</div>
</footer>
</div><!-- .container -->
</div><!-- #page -->
<?php wp_footer(); ?>
</body>

</html>