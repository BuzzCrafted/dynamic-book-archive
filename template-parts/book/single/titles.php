<?php
/**
 * Single book titles.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

if ( ! isset( $args ) || ! is_array( $args ) ) {
	return;
}

$titles = isset( $args['titles'] ) && is_array( $args['titles'] ) ? $args['titles'] : array();
$title  = isset( $titles['title'] ) && is_string( $titles['title'] ) ? $titles['title'] : '';
$title_japanese = isset( $titles['title_japanese'] ) && is_string( $titles['title_japanese'] ) ? $titles['title_japanese'] : '';

if ( '' === $title && '' === $title_japanese ) {
	return;
}

?>
<header>
	<?php if ( '' !== $title_japanese ) : ?>
		<h1 class="font-display text-4xl font-bold leading-tight text-brand sm:text-4xl md:text-4xl"><?php echo esc_html( $title_japanese ); ?></h1>
		<?php if ( '' !== $title ) : ?>
			<p class="mt-1.5 font-display text-2xl font-semibold leading-tight text-brand-muted sm:text-3xl"><?php echo esc_html( $title ); ?></p>
		<?php endif; ?>
	<?php else : ?>
		<h1 class="font-display text-4xl font-semibold leading-tight text-brand sm:text-4xl md:text-4xl"><?php echo esc_html( $title ); ?></h1>
	<?php endif; ?>
</header>

