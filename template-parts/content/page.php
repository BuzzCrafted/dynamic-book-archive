<?php
/**
 * Template part for displaying page content
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header mb-6">
		<?php the_title('<h1 class="entry-title text-3xl font-semibold tracking-tight text-library-primary">', '</h1>'); ?>
	</header>

	<?php dba_post_thumbnail(); ?>

	<div class="entry-content prose max-w-none">
		<?php
		the_content();

		wp_link_pages(
			array(
				'before' => '<div class="page-links mt-6 text-sm">' . esc_html__('Pages:', 'dynamic-book-archive'),
				'after'  => '</div>',
			)
		);
		?>
	</div>

	<?php if (get_edit_post_link()) : ?>
		<footer class="entry-footer mt-8">
			<?php
			edit_post_link(
				sprintf(
					wp_kses(
						/* translators: %s: post title */
						__('Edit <span class="screen-reader-text">%s</span>', 'dynamic-book-archive'),
						array(
							'span' => array(
								'class' => array(),
							),
						)
					),
					wp_kses_post(get_the_title())
				),
				'<span class="edit-link text-sm">',
				'</span>'
			);
			?>
		</footer>
	<?php endif; ?>
</article>
