<?php
/**
 * The template for displaying 404 pages
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

get_header();
?>

	<main id="primary" class="site-main flex-1">
		<div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
			<section class="error-404 not-found text-center">
				<header class="page-header mb-6">
					<h1 class="page-title text-3xl font-semibold tracking-tight text-library-primary"><?php esc_html_e('That page cannot be found.', 'dynamic-book-archive'); ?></h1>
				</header>
				<div class="page-content max-w-xl mx-auto">
					<p class="text-library-primary/70"><?php esc_html_e('It looks like nothing was found at this location. Maybe try a search?', 'dynamic-book-archive'); ?></p>
					<div class="mt-8">
						<?php get_search_form(); ?>
					</div>
				</div>
			</section>
		</div>
	</main>

<?php
get_footer();
