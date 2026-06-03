<?php
/**
 * Card container with an optional title and a content slot.
 *
 * Component: `container.card`. View model from {@see DBA\Components\Container\Card_Component}.
 * Open/close the container to supply the slot:
 *
 *   dba_component_open( 'container.card', array( 'title' => 'Details' ) );
 *   // ...inner markup...
 *   dba_component_close();
 *
 * The slot HTML is already-rendered template output, so it is echoed verbatim.
 *
 * @package Dynamic_Book_Archive
 *
 * @param array $args {
 *   @type string $title       Optional. Heading shown above the slot.
 *   @type string $title_class Optional. Classes on the heading.
 *   @type string $class       Optional. Classes on the wrapping <section>.
 *   @type string $body_class  Optional. Classes on the slot wrapper.
 *   @type string $slot        Slot HTML injected by the renderer.
 * }
 */

declare(strict_types=1);

if ( ! isset( $args ) || ! is_array( $args ) ) {
	return;
}

$title       = isset( $args['title'] ) && is_string( $args['title'] ) ? $args['title'] : '';
$title_class = isset( $args['title_class'] ) && is_string( $args['title_class'] ) ? $args['title_class'] : '';
$class       = isset( $args['class'] ) && is_string( $args['class'] ) ? $args['class'] : '';
$body_class  = isset( $args['body_class'] ) && is_string( $args['body_class'] ) ? $args['body_class'] : '';
$slot        = isset( $args['slot'] ) && is_string( $args['slot'] ) ? $args['slot'] : '';

?>
<section class="<?php echo esc_attr( $class ); ?>">
	<?php if ( '' !== $title ) : ?>
		<h2 class="<?php echo esc_attr( $title_class ); ?>"><?php echo esc_html( $title ); ?></h2>
	<?php endif; ?>
	<div class="<?php echo esc_attr( $body_class ); ?>">
		<?php echo $slot; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Slot is pre-rendered template markup. ?>
	</div>
</section>
