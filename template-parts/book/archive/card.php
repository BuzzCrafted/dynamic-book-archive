<?php
/**
 * Markup for each book in the post-type archive grid (4×3 cell).
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

if ( ! isset( $args ) || ! is_array( $args ) ) {
	return;
}

$post_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : 0;
if ( $post_id <= 0 ) {
	return;
}

$permalink = isset( $args['permalink'] ) && is_string( $args['permalink'] ) ? $args['permalink'] : '';
$title     = isset( $args['title'] ) && is_string( $args['title'] ) ? $args['title'] : '';
$title_japanese     = isset( $args['title_japanese'] ) && is_string( $args['title_japanese'] ) ? $args['title_japanese'] : '';
$publication_label  = isset( $args['publication_label'] ) && is_string( $args['publication_label'] ) ? $args['publication_label'] : '';
$thumbnail_html     = isset( $args['thumbnail_html'] ) && is_string( $args['thumbnail_html'] ) ? $args['thumbnail_html'] : '';
$placeholder_url    = isset( $args['placeholder_url'] ) && is_string( $args['placeholder_url'] ) ? $args['placeholder_url'] : '';

if ( '' === $permalink ) {
	$permalink = get_permalink( $post_id );
	$permalink = is_string( $permalink ) && '' !== $permalink ? $permalink : home_url( '/' );
}
?>

<article id="post-<?php echo esc_attr( (string) $post_id ); ?>" class="group relative min-h-0 w-full p-2 rounded-lg bg-surface transition-shadow duration-300 shadow-main hover:shadow-main-hover before:pointer-events-none before:absolute before:inset-0 before:rounded-[inherit] before:p-px before:content-[''] before:opacity-0 before:transition-opacity before:duration-300 hover:before:opacity-100 before:bg-(image:--image-card-highlight) before:mask-[linear-gradient(#000,#000),linear-gradient(#000,#000)] before:[-webkit-mask-image:linear-gradient(#000,#000),linear-gradient(#000,#000)] before:[mask-clip:content-box,border-box] before:[-webkit-mask-clip:content-box,border-box] before:mask-exclude">
	<a href="<?php echo esc_url( $permalink ); ?>" class="flex w-full min-h-0 items-stretch gap-2 text-card-text no-underline">
		<div class="relative min-w-0 flex-1 self-center overflow-hidden aspect-2/3">
			<?php
			if ( '' !== $thumbnail_html ) {
				echo $thumbnail_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized by WP thumbnail renderer.
			} else {
				printf(
					'<img src="%1$s" alt="%2$s" class="%3$s" width="1301" height="1209" loading="lazy" decoding="async" />',
					esc_url( $placeholder_url ),
					esc_attr__( 'No cover image', 'dynamic-book-archive' ),
					esc_attr( 'h-full w-full object-cover object-left' )
				);
			}
			?>
		</div>
		<div class="flex min-w-0 flex-1 flex-col justify-center gap-2 overflow-hidden">
			<?php if ( '' !== $title_japanese ) : ?>
				<h2 class="font-display text-xl uppercase leading-tight tracking-[0.4px]"><?php echo esc_html( $title_japanese ); ?></h2>
			<?php endif; ?>
			<?php if ( '' !== $title ) : ?>
				<p class="font-main text-sm tracking-[0.24px]"><?php echo esc_html( $title ); ?></p>
			<?php endif; ?>
			<?php if ( '' !== $publication_label ) : ?>
				<p class="font-main text-xs tracking-[0.2px]"><?php echo esc_html( $publication_label ); ?></p>
			<?php endif; ?>
		</div>
	</a>
</article>
