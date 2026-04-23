<?php
/**
 * The template for displaying comments
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

if (post_password_required()) {
	return;
}
?>

<div id="comments" class="comments-area mt-12 border-t border-library-primary-dark/35 pt-10">

	<?php if (have_comments()) : ?>
		<h2 class="comments-title text-xl font-semibold text-library-primary">
			<?php
			$comment_count = get_comments_number();
			if ('1' === (string) $comment_count) {
				printf(
					/* translators: 1: title */
					esc_html__('One thought on &ldquo;%1$s&rdquo;', 'dynamic-book-archive'),
					'<span>' . wp_kses_post(get_the_title()) . '</span>'
				);
			} else {
				printf(
					/* translators: 1: comment count, 2: post title */
					esc_html(_nx('%1$s thought on &ldquo;%2$s&rdquo;', '%1$s thoughts on &ldquo;%2$s&rdquo;', $comment_count, 'comments title', 'dynamic-book-archive')),
					number_format_i18n($comment_count),
					'<span>' . wp_kses_post(get_the_title()) . '</span>'
				);
			}
			?>
		</h2>

		<ol class="comment-list mt-6 list-none space-y-6 p-0">
			<?php
			wp_list_comments(
				array(
					'style'      => 'ol',
					'short_ping' => true,
				)
			);
			?>
		</ol>

		<?php
		the_comments_navigation(
			array(
				'prev_text' => __('Older comments', 'dynamic-book-archive'),
				'next_text' => __('Newer comments', 'dynamic-book-archive'),
			)
		);

	endif;

	comment_form(
		array(
			'title_reply_before' => '<h3 id="reply-title" class="comment-reply-title mt-10 text-lg font-semibold text-library-primary">',
			'title_reply_after'  => '</h3>',
		)
	);
	?>

</div>
