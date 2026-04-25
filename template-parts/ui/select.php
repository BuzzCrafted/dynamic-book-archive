<?php
/**
 * Reusable select control.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

if ( ! isset( $args ) || ! is_array( $args ) ) {
	return;
}

$id    = isset( $args['id'] ) && is_string( $args['id'] ) ? $args['id'] : '';
$class = isset( $args['class'] ) && is_string( $args['class'] ) ? $args['class'] : '';
$name  = isset( $args['name'] ) && is_string( $args['name'] ) ? $args['name'] : '';

$label = isset( $args['label'] ) && is_string( $args['label'] ) ? $args['label'] : '';
$label_sr_only = isset( $args['label_sr_only'] ) ? (bool) $args['label_sr_only'] : true;

$tabindex = isset( $args['tabindex'] ) && is_scalar( $args['tabindex'] ) ? (string) $args['tabindex'] : null;

$options = isset( $args['options'] ) && is_array( $args['options'] ) ? $args['options'] : array();

if ( '' === $id || '' === $class || empty( $options ) ) {
	return;
}

?>
<?php if ( '' !== $label ) : ?>
	<label class="<?php echo esc_attr( $label_sr_only ? 'sr-only' : '' ); ?>" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
<?php endif; ?>
<select
	id="<?php echo esc_attr( $id ); ?>"
	<?php if ( '' !== $name ) : ?>
		name="<?php echo esc_attr( $name ); ?>"
	<?php endif; ?>
	class="<?php echo esc_attr( $class ); ?>"
	<?php if ( null !== $tabindex ) : ?>
		tabindex="<?php echo esc_attr( $tabindex ); ?>"
	<?php endif; ?>
>
	<?php foreach ( $options as $opt ) : ?>
		<?php
		if ( ! is_array( $opt ) ) {
			continue;
		}
		$value    = isset( $opt['value'] ) && is_scalar( $opt['value'] ) ? (string) $opt['value'] : '';
		$opt_label = isset( $opt['label'] ) && is_scalar( $opt['label'] ) ? (string) $opt['label'] : '';
		if ( '' === $value && '' === $opt_label ) {
			continue;
		}
		$selected = isset( $opt['selected'] ) ? (bool) $opt['selected'] : false;
		$disabled = isset( $opt['disabled'] ) ? (bool) $opt['disabled'] : false;
		?>
		<option
			value="<?php echo esc_attr( $value ); ?>"
			<?php selected( $selected ); ?>
			<?php disabled( $disabled ); ?>
		><?php echo esc_html( $opt_label ); ?></option>
	<?php endforeach; ?>
</select>

