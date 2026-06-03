<?php
/**
 * Carousel image stage — shrink-wrapped image container with optional overlaid nav buttons.
 *
 * Renders a `<span>` that shrinks to the natural size of its content image,
 * establishing the `position: relative` context so nav buttons position themselves
 * at the image edges rather than the full column/stage boundary.
 *
 * @package Dynamic_Book_Archive
 *
 * @param array $args {
 *   @type string $content       Required. Trusted HTML string (e.g. from wp_get_attachment_image()).
 *   @type string $wrapper_class Optional. Classes on the outer <span>. Default 'relative inline-block max-w-full'.
 *   @type bool   $nav           Optional. When true, renders a nav button pair over the image. Default false.
 *   @type array  $nav_args      Optional. Passed through to {@see dba_the_carousel_nav_pair()}.
 *   @type array  $alpine_nav    Optional. Shorthand: sets both alpine_prev and alpine_next in nav_args
 *                               when those keys are not already present.
 * }
 */

declare(strict_types=1);

if ( ! isset( $args ) || ! is_array( $args ) ) {
	return;
}

$content = isset( $args['content'] ) && is_string( $args['content'] ) ? $args['content'] : '';
if ( '' === $content ) {
	return;
}

$wrapper_class = isset( $args['wrapper_class'] ) && is_string( $args['wrapper_class'] ) ? $args['wrapper_class'] : 'relative inline-block max-w-full';
$show_nav      = isset( $args['nav'] ) ? (bool) $args['nav'] : false;
$nav_args      = isset( $args['nav_args'] ) && is_array( $args['nav_args'] ) ? $args['nav_args'] : array();
$alpine_nav    = isset( $args['alpine_nav'] ) && is_array( $args['alpine_nav'] ) ? $args['alpine_nav'] : array();

// Shorthand: propagate alpine_nav to each direction when not already set.
if ( $show_nav && ! empty( $alpine_nav ) ) {
	if ( empty( $nav_args['alpine_prev'] ) ) {
		$nav_args['alpine_prev'] = $alpine_nav;
	}
	if ( empty( $nav_args['alpine_next'] ) ) {
		$nav_args['alpine_next'] = $alpine_nav;
	}
}

?>
<span class="<?php echo esc_attr( $wrapper_class ); ?>">
	<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- callers must provide trusted HTML (wp_get_attachment_image, lightbox helper, hardcoded markup). ?>
	<?php if ( $show_nav ) : ?>
		<?php dba_the_carousel_nav_pair( $nav_args ); ?>
	<?php endif; ?>
</span>
