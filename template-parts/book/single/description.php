<?php
/**
 * Single book description section.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

if ( ! isset( $args ) || ! is_array( $args ) ) {
	return;
}

$post_id  = isset( $args['post_id'] ) ? (int) $args['post_id'] : 0;
$content  = isset( $args['content'] ) && is_string( $args['content'] ) ? $args['content'] : '';

if ( $post_id <= 0 ) {
	return;
}

if ( '' === trim( $content ) ) {
	return;
}

?>
<section aria-labelledby="book-desc-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<h2 id="book-desc-heading-<?php echo esc_attr( (string) $post_id ); ?>" class="font-display text-sm font-semibold uppercase tracking-[0.25em] text-book-secondary"><?php esc_html_e( 'Description', 'dynamic-book-archive' ); ?></h2>
	<div class="prose prose-invert prose-headings:font-display prose-headings:text-book-primary prose-p:my-2 lg:prose-p:my-3 prose-strong:text-book-primary prose-em:text-book-primary prose-li:text-book-primary prose-blockquote:text-book-primary prose-a:text-book-secondary mt-1 max-w-none font-main text-base leading-relaxed md:leading-[1.75] text-book-primary">
		<?php the_content(); ?>
	</div>
	<hr class="h-px w-full shrink-0 border-0 bg-linear-to-r from-transparent from-0% via-book-primary/85 via-38% to-transparent to-100% [box-shadow:0_0_12px_color-mix(in_oklch,var(--color-book-primary)_35%,transparent)]" role="presentation" />
</section>

