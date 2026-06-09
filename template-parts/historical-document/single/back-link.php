<?php
/**
 * Single historical document back link.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

if ( ! isset( $args ) || ! is_array( $args ) ) {
	return;
}

$back  = isset( $args['back_link'] ) && is_array( $args['back_link'] ) ? $args['back_link'] : array();
$url   = isset( $back['url'] ) && is_string( $back['url'] ) ? $back['url'] : '';
$label = isset( $back['label'] ) && is_string( $back['label'] ) ? $back['label'] : '';

if ( '' === $url || '' === $label ) {
	return;
}

?>
<a class="font-main my-2 md:my-4 lg:my-6 inline-flex items-center gap-1 md:gap-2 text-sm font-medium tracking-widest text-brand no-underline transition hover:text-brand-muted" href="<?php echo esc_url( $url ); ?>">
	<span aria-hidden="true">←</span>
	<?php echo esc_html( $label ); ?>
</a>
