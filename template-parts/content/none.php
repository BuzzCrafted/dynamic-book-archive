<?php
/**
 * Template part for displaying a message when posts cannot be found
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);
?>

<section class="no-results not-found">
	<header class="page-header mb-6">
		<h1 class="page-title text-3xl font-semibold tracking-tight text-library-primary"><?php esc_html_e('Nothing here', 'dynamic-book-archive'); ?></h1>
	</header>

	<div class="page-content max-w-xl">
		<?php
		if (is_home() && current_user_can('publish_posts')) {
			?>
			<p class="text-library-primary/70">
				<?php
				printf(
					wp_kses(
						/* translators: %s: URL to create a new post */
						__('Ready to publish your first post? <a href="%s">Get started here</a>.', 'dynamic-book-archive'),
						array(
							'a' => array(
								'href' => array(),
							),
						)
					),
					esc_url(admin_url('post-new.php'))
				);
				?>
			</p>
			<?php
		} elseif (is_search()) {
			?>
			<p class="text-library-primary/70"><?php esc_html_e('Sorry, but nothing matched your search terms. Please try again with different keywords.', 'dynamic-book-archive'); ?></p>
			<div class="mt-6">
				<?php get_search_form(); ?>
			</div>
			<?php
		} else {
			?>
			<p class="text-library-primary/70"><?php esc_html_e('It seems we cannot find what you are looking for. Perhaps searching can help.', 'dynamic-book-archive'); ?></p>
			<div class="mt-6">
				<?php get_search_form(); ?>
			</div>
			<?php
		}
		?>
	</div>
</section>
