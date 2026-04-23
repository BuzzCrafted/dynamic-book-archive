<?php
/**
 * The template for displaying search results pages
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

get_header();
?>

<div class="site-content mx-auto flex w-full max-w-6xl flex-1 flex-col md:flex-row">
	<main id="primary" class="site-main flex-1">
		<div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">

			<?php if (have_posts()) : ?>

				<header class="page-header mb-10">
					<h1 class="page-title text-3xl font-semibold tracking-tight text-library-primary">
						<?php esc_html_e('Search results for:', 'dynamic-book-archive'); ?>
						<span class="ml-2 text-library-primary/85"><?php echo esc_html(get_search_query()); ?></span>
					</h1>
				</header>

				<?php
				while (have_posts()) :
					the_post();
					get_template_part('template-parts/content/search');
				endwhile;

				the_posts_navigation(
					array(
						'prev_text' => __('Older posts', 'dynamic-book-archive'),
						'next_text' => __('Newer posts', 'dynamic-book-archive'),
					)
				);

			else :

				get_template_part('template-parts/content/none');

			endif;
			?>

		</div>
	</main>

	<?php get_sidebar(); ?>
</div>

<?php
get_footer();
