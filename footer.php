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

$book_categories_list = array_map(
	static function ( $book_category ) {
		$link = '';
		if ( $book_category instanceof \WP_Term && function_exists( 'dba_get_book_post_type_archive_filter_url' ) ) {
			$link = dba_get_book_post_type_archive_filter_url( $book_category );
		} elseif ( $book_category instanceof \WP_Term ) {
			$tlink = get_term_link( $book_category );
			$link  = is_string( $tlink ) && ! is_wp_error( $tlink ) ? $tlink : '';
		}

		return array(
			'link' => $link,
			'name' => $book_category instanceof \WP_Term ? $book_category->name : '',
		);
	},
	is_wp_error( $book_categories ) ? array() : (array) $book_categories
);

?>
<footer class="site-footer py-2 px-2 md:px-4 lg:px-0 text-content bg-surface">
	<div class="max-w-[1440px] lg:px-2 mx-auto pb-4">
		<div class="flex flex-col md:flex-row md:justify-start items-stretch gap-x-16">
			<div class="flex justify-center md:justify-start items-center gap-2">
				<div class="site-footer-brand-logo w-16 md:w-20 shrink-0">
					<?php dba_the_site_logo('w-full h-auto object-contain brightness-150', true); ?>
				</div>
				<div class="flex flex-col gap-y-2">
					<h1 class="site-title font-display text-base not-italic font-bold leading-[normal] tracking-[0.7px] uppercase">
						Robert C. Gruzanski
					</h1>
					<sup class="site-description inline-flex items-center justify-between text-xs not-italic font-bold leading-[normal] tracking-[0.24px]">
						<span>Curator of the Gruzanski Archives</span>
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
						'menu_class'     => 'footer-menu flex flex-row gap-6 text-base tracking-[0.2em] uppercase items-center [&_a]:no-underline',
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
			<span class="inline-flex text-xs md:text-sm justify-center md:justify-start not-italic leading-[normal] tracking-[0.24px]"> &copy; 2002–<?php echo date('Y'); ?> Robert C. Gruzanski. All rights reserved.</span>
			<span class="inline-flex text-xxs justify-center md:justify-start text-content/65 not-italic leading-[normal] tracking-[0.24px]">Content may not be reproduced or used without permission.</span>
		</div>
	</div>
</footer>
</div><!-- .container -->
</div><!-- #page -->
<?php wp_footer(); ?>
</body>

</html>