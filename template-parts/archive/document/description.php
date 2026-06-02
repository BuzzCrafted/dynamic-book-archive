<?php
/**
 * Single historical document description (post_content).
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

if ( ! isset( $args ) || ! is_array( $args ) ) {
	return;
}

$post_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : 0;
$content = isset( $args['content'] ) && is_string( $args['content'] ) ? $args['content'] : '';

if ( $post_id <= 0 || '' === trim( $content ) ) {
	return;
}

?>
<section aria-labelledby="doc-desc-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<h2 id="doc-desc-heading-<?php echo esc_attr( (string) $post_id ); ?>" class="font-display text-sm font-semibold uppercase tracking-[0.25em] text-book-secondary mb-2">
		<?php esc_html_e( 'Description', 'dynamic-book-archive' ); ?>
	</h2>
	<div class="prose prose-invert prose-headings:font-display prose-headings:text-book-primary prose-p:my-2 lg:prose-p:my-3 prose-strong:text-book-primary prose-em:text-book-primary prose-li:text-book-primary prose-blockquote:text-book-primary prose-a:text-book-secondary mt-1 max-w-none font-main text-base leading-relaxed md:leading-[1.75] text-book-primary">
		<?php the_content(); ?>
	</div>
</section>
