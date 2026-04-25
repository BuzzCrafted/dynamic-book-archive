<?php
/**
 * Book archive results grid.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

if ( ! isset( $args ) || ! is_array( $args ) ) {
	return;
}

$items = isset( $args['items'] ) && is_array( $args['items'] ) ? $args['items'] : array();
if ( empty( $items ) ) {
	return;
}

?>
<div class="js-book-archive-grid grid auto-rows-max grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-1 md:gap-x-2 lg:gap-x-[3%] gap-y-1 md:gap-y-2 lg:gap-y-[4%] w-full pb-2 md:pb-4 lg:pb-12 content-start items-stretch motion-safe:transition-[opacity,filter,transform] motion-safe:duration-200 motion-safe:ease-out group-aria-busy:pointer-events-none motion-safe:group-data-book-archive-dim:opacity-[.86] motion-safe:group-data-book-archive-dim:blur-[2px] motion-safe:group-data-book-archive-dim:scale-[.992] motion-safe:group-data-book-archive-dim:will-change-[opacity,filter,transform]">
	<?php foreach ( $items as $item ) : ?>
		<?php
		if ( ! is_array( $item ) ) {
			continue;
		}
		get_template_part( 'template-parts/book/archive/card', null, $item );
		?>
	<?php endforeach; ?>
</div>

