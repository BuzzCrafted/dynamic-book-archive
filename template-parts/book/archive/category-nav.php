<?php
/**
 * Category selector for the book archive.
 *
 * Loaded via {@see Book_Archive_Category_Nav::render()}. Data is passed in
 * {@see get_template_part()} `$args['items']` (WordPress does not extract `$args`
 * into separate variables on all versions).
 *
 * @package Dynamic_Book_Archive
 *
 * @var array<string, mixed> $args Arguments from get_template_part() (includes `items`).
 */

declare(strict_types=1);

$nav_items = array();

if ( isset( $args ) && is_array( $args ) && isset( $args['items'] ) && is_array( $args['items'] ) ) {
	$nav_items = $args['items'];
} elseif ( isset( $items ) && is_array( $items ) ) {
	$nav_items = $items;
} else {
	$qv        = get_query_var( 'dba_book_category_nav_items', array() );
	$nav_items = is_array( $qv ) ? $qv : array();
}

if ( array() === $nav_items ) {
	return;
}

?>
<nav class="js-book-archive-category-nav mx-auto w-full max-w-4xl px-4 pt-4 pb-7" aria-label="<?php esc_attr_e( 'Book categories', 'dynamic-book-archive' ); ?>">
	<ul class="m-0 flex list-none flex-wrap items-center justify-center gap-3 p-0" role="list">
		<?php foreach ( $nav_items as $item ) : ?>
			<?php
			if ( ! is_array( $item ) ) {
				continue;
			}
			$label   = isset( $item['label'] ) ? (string) $item['label'] : '';
			$url     = isset( $item['url'] ) ? (string) $item['url'] : '';
			$current = ! empty( $item['current'] );
			if ( '' === $label || '' === $url ) {
				continue;
			}
			$link_class = 'inline-flex min-h-10 items-center justify-center rounded-md shadow-main border border-transparent px-4 py-2 text-center text-base tracking-wide text-brand-muted no-underline transition-[color,background-color,border-color,box-shadow] duration-200 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand hover:text-content-inverse hover:border-book-archive-category-nav-current-border hover:bg-(image:--image-book-archive-category-nav-current) hover:shadow-book-archive-category-nav-current aria-[current=page]:text-content-inverse aria-[current=page]:border-book-archive-category-nav-current-border aria-[current=page]:bg-(image:--image-book-archive-category-nav-current) aria-[current=page]:shadow-book-archive-category-nav-current aria-[current=page]:hover:bg-(image:--image-book-archive-category-nav-current-hover) aria-[current=page]:hover:shadow-book-archive-category-nav-current-hover';
			if ( $current ) {
				$link_class .= ' js-brand-current';
			}
			$books_nav_cat  = '';
			$books_nav_slug = isset( $item['slug'] ) ? (string) $item['slug'] : '';
			if ( isset( $item['id'] ) && (int) $item['id'] > 0 ) {
				$books_nav_cat = (string) (int) $item['id'];
			}
			?>
			<li class="m-0 p-0" role="listitem">
				<a
					class="<?php echo esc_attr( $link_class ); ?>"
					href="<?php echo esc_url( $url ); ?>"
					data-books-cpt-category="<?php echo esc_attr( $books_nav_cat ); ?>"
					data-books-cpt-category-slug="<?php echo esc_attr( $books_nav_slug ); ?>"
					<?php echo $current ? ' aria-current="page"' : ''; ?>
				>
					<?php echo esc_html( $label ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
