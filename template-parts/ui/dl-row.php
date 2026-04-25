<?php
/**
 * Definition list row (dt/dd pair).
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

if ( ! isset( $args ) || ! is_array( $args ) ) {
	return;
}

$label = isset( $args['label'] ) && is_string( $args['label'] ) ? $args['label'] : '';
$value = isset( $args['value'] ) && is_string( $args['value'] ) ? $args['value'] : '';

$row_class = isset( $args['row_class'] ) && is_string( $args['row_class'] ) ? $args['row_class'] : '';
$dt_class  = isset( $args['dt_class'] ) && is_string( $args['dt_class'] ) ? $args['dt_class'] : '';
$dd_class  = isset( $args['dd_class'] ) && is_string( $args['dd_class'] ) ? $args['dd_class'] : '';

if ( '' === $label || '' === $value ) {
	return;
}

?>
<div class="<?php echo esc_attr( $row_class ); ?>">
	<dt class="<?php echo esc_attr( $dt_class ); ?>"><?php echo esc_html( $label ); ?></dt>
	<dd class="<?php echo esc_attr( $dd_class ); ?>"><?php echo esc_html( $value ); ?></dd>
</div>

