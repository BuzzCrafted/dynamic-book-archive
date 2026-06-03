<?php
/**
 * Carousel navigation button (single direction).
 *
 * @package Dynamic_Book_Archive
 *
 * @param array $args {
 *   @type string $direction      Required. 'prev' or 'next'. Drives side position, icon and default SR label.
 *   @type string $aria_controls  Optional. ID of the element this button controls.
 *   @type string $aria_label     Optional. Screen-reader label. Defaults to "Previous image" / "Next image" (i18n).
 *   @type string $icon           Optional. Icon path for {@see dba_the_inline_icon()}. Defaults to chevron left/right.
 *   @type string $icon_class     Optional. Classes on the icon <i>. Default 'block h-8 w-8'.
 *   @type string $variant        Optional. 'overlay' (default) positions the button absolutely over
 *                                its container. 'inline' renders it as a normal flow flex/grid sibling.
 *   @type string $class          Optional. Extra Tailwind classes merged onto the defaults.
 *   @type string $position_class Optional. Side offset override (overlay variant). Default 'left-1' / 'right-1'.
 *   @type string $size_class     Optional. Hit-target override. Default 'h-11 w-11'.
 *   @type string $data_attr      Optional. Boolean data attribute name, e.g. 'data-book-gallery-prev'.
 *   @type array  $attrs          Optional. Map of extra static HTML attributes (name => value).
 *   @type array  $alpine         Optional. Map of Alpine/x-bind attrs, e.g. ['@click' => 'prev()', ':disabled' => 'total < 2'].
 * }
 */

declare(strict_types=1);

if ( ! isset( $args ) || ! is_array( $args ) ) {
	return;
}

$direction = isset( $args['direction'] ) && is_string( $args['direction'] ) ? $args['direction'] : '';
if ( 'prev' !== $direction && 'next' !== $direction ) {
	return;
}

$is_prev = 'prev' === $direction;

// Screen-reader label.
$aria_label = isset( $args['aria_label'] ) && is_string( $args['aria_label'] ) ? $args['aria_label'] : '';
if ( '' === $aria_label ) {
	$aria_label = $is_prev
		? __( 'Previous image', 'dynamic-book-archive' )
		: __( 'Next image', 'dynamic-book-archive' );
}

// Icon.
$default_icon = $is_prev ? 'bx/bx-chevron-left' : 'bx/bx-chevron-right';
$icon         = isset( $args['icon'] ) && is_string( $args['icon'] ) ? $args['icon'] : $default_icon;
$icon_class   = isset( $args['icon_class'] ) && is_string( $args['icon_class'] ) ? $args['icon_class'] : 'block h-8 w-8';

// Classes.
$variant        = isset( $args['variant'] ) && is_string( $args['variant'] ) ? $args['variant'] : 'overlay';
$extra_class    = isset( $args['class'] ) && is_string( $args['class'] ) ? $args['class'] : '';
$position_class = isset( $args['position_class'] ) && is_string( $args['position_class'] ) ? $args['position_class'] : ( $is_prev ? 'left-1' : 'right-1' );
$size_class     = isset( $args['size_class'] ) && is_string( $args['size_class'] ) ? $args['size_class'] : 'h-11 w-11';
$btn_class      = dba_get_carousel_nav_button_classes( $position_class, $size_class, $extra_class, $variant );

// Attributes.
$aria_controls = isset( $args['aria_controls'] ) && is_string( $args['aria_controls'] ) ? $args['aria_controls'] : '';
$data_attr     = isset( $args['data_attr'] ) && is_string( $args['data_attr'] ) ? $args['data_attr'] : '';
$attrs         = isset( $args['attrs'] ) && is_array( $args['attrs'] ) ? $args['attrs'] : array();
$alpine        = isset( $args['alpine'] ) && is_array( $args['alpine'] ) ? $args['alpine'] : array();

?>
<button type="button"
	class="<?php echo esc_attr( $btn_class ); ?>"
	<?php if ( '' !== $data_attr ) : ?>
		<?php echo esc_attr( $data_attr ); ?>
	<?php endif; ?>
	<?php if ( '' !== $aria_controls ) : ?>
		aria-controls="<?php echo esc_attr( $aria_controls ); ?>"
	<?php endif; ?>
	<?php foreach ( $attrs as $attr_name => $attr_val ) : ?>
		<?php echo esc_attr( (string) $attr_name ); ?>="<?php echo esc_attr( (string) $attr_val ); ?>"
	<?php endforeach; ?>
	<?php foreach ( $alpine as $binding => $expression ) : ?>
		<?php echo esc_attr( (string) $binding ); ?>="<?php echo esc_attr( (string) $expression ); ?>"
	<?php endforeach; ?>
>
	<span class="sr-only"><?php echo esc_html( $aria_label ); ?></span>
	<?php dba_the_inline_icon( $icon, $icon_class ); ?>
</button>
