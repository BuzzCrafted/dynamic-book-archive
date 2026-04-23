<?php
/**
 * The main template file
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

get_header();
?>

<div class="site-content max-w-[1440px] lg:px-30 mx-auto flex w-full flex-1 flex-col md:flex-row">
	<main id="primary" class="site-main flex-1">
		<div class="grid grid-cols-12 mx-auto md:items-center lg:gap-6">

			<?php
			if (have_posts()) :

				if (is_home() && ! is_front_page()) :
					?>
					<header class="mb-10">
						<h1 class="page-title text-3xl font-semibold tracking-tight text-zinc-900"><?php single_post_title(); ?></h1>
					</header>
					<?php
				endif;

				while (have_posts()) :
					the_post();
					get_template_part('template-parts/content/' . get_post_type());
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
