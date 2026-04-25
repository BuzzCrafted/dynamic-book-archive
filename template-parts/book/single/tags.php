<?php
/**
 * Single book tags section.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

if ( ! isset( $args ) || ! is_array( $args ) ) {
	return;
}

$post_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : 0;
$tags    = isset( $args['tags'] ) && is_array( $args['tags'] ) ? $args['tags'] : array();

if ( $post_id <= 0 || empty( $tags ) ) {
	return;
}

?>
<section aria-labelledby="book-tags-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<h2 id="book-tags-heading-<?php echo esc_attr( (string) $post_id ); ?>" class="font-display text-sm font-semibold uppercase tracking-[0.25em] text-book-secondary"><?php esc_html_e( 'Tags', 'dynamic-book-archive' ); ?></h2>
	<div class="mt-4 flex flex-wrap gap-1 md:gap-2 justify-center md:justify-start">
		<?php foreach ( $tags as $tag ) : ?>
			<?php
			if ( ! is_array( $tag ) ) {
				continue;
			}
			$name = isset( $tag['name'] ) && is_string( $tag['name'] ) ? $tag['name'] : '';
			$url  = isset( $tag['url'] ) && is_string( $tag['url'] ) ? $tag['url'] : '';
			if ( '' === $name || '' === $url ) {
				continue;
			}
			?>
			<a class="inline-flex rounded-md shadow-main px-4 py-1.5 text-sm font-medium text-book-primary no-underline transition hover:bg-book-secondary/10 hover:shadow-bronze-glow duration-200 ease-out" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $name ); ?></a>
		<?php endforeach; ?>
	</div>
</section>

