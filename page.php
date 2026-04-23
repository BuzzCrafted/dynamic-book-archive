<?php
/**
 * The template for displaying all pages
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

get_header();
?>

<div class="site-content mx-auto flex w-full max-w-6xl flex-1 flex-col md:flex-row">
	<main id="primary" class="site-main flex-1">
		<div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">

			<?php
			while (have_posts()) :
				the_post();
				get_template_part('template-parts/content/page');

				if (comments_open() || get_comments_number()) :
					comments_template();
				endif;

			endwhile;
			?>

		</div>
	</main>

	<?php get_sidebar(); ?>
</div>

<?php
get_footer();
