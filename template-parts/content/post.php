<?php
/**
 * Template part for displaying posts
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('mb-12 border-b border-library-primary-dark/35 pb-12 last:mb-0 last:border-b-0 last:pb-0'); ?>>
	<header class="entry-header mb-4">
		<?php
		if (is_singular()) :
			the_title('<h1 class="entry-title text-3xl font-semibold tracking-tight text-library-primary">', '</h1>');
		else :
			the_title('<h2 class="entry-title text-2xl font-semibold tracking-tight text-library-primary"><a class="no-underline text-inherit hover:underline" href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h2>');
		endif;

		if ('post' === get_post_type()) :
			?>
			<div class="entry-meta mt-2 flex flex-wrap gap-x-3 gap-y-1">
				<?php dba_posted_on(); ?>
				<?php dba_posted_by(); ?>
			</div>
		<?php endif; ?>
	</header>

	<?php dba_post_thumbnail(); ?>

	<div class="entry-content prose mt-4 max-w-none">
		<?php
		the_content(
			sprintf(
				wp_kses(
					/* translators: %s: post title */
					__('Continue reading<span class="screen-reader-text"> "%s"</span>', 'dynamic-book-archive'),
					array(
						'span' => array(
							'class' => array(),
						),
					)
				),
				wp_kses_post(get_the_title())
			)
		);

		wp_link_pages(
			array(
				'before' => '<div class="page-links mt-6 text-sm">' . esc_html__('Pages:', 'dynamic-book-archive'),
				'after'  => '</div>',
			)
		);
		?>
	</div>

	<footer class="entry-footer mt-6">
		<?php dba_entry_footer(); ?>
	</footer>
</article>
