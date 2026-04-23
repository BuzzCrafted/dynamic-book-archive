<?php
/**
 * Template part for displaying results in search pages
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('mb-10 border-b border-library-primary-dark/35 pb-10 last:mb-0 last:border-b-0 last:pb-0'); ?>>
	<header class="entry-header mb-3">
		<?php the_title(sprintf('<h2 class="entry-title text-xl font-semibold text-library-primary"><a class="no-underline text-inherit hover:underline" href="%s" rel="bookmark">', esc_url(get_permalink())), '</a></h2>'); ?>

		<?php if ('post' === get_post_type()) : ?>
			<div class="entry-meta mt-2 flex flex-wrap gap-x-3 gap-y-1 text-sm text-library-primary/70">
				<?php dba_posted_on(); ?>
				<?php dba_posted_by(); ?>
			</div>
		<?php endif; ?>
	</header>

	<?php dba_post_thumbnail(); ?>

	<div class="entry-summary mt-3 text-library-primary/85">
		<?php the_excerpt(); ?>
	</div>

	<footer class="entry-footer mt-4 text-sm">
		<?php dba_entry_footer(); ?>
	</footer>
</article>
