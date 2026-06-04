<?php
/**
 * Breadcrumb trail markup.
 *
 * Loaded via {@see dba_breadcrumbs()}. Items are passed in {@see get_template_part()} `$args['items']`
 * (WordPress does not extract `$args` into separate variables on all versions).
 *
 * @package Dynamic_Book_Archive
 *
 * @var array<string, mixed> $args Arguments from get_template_part() (includes `items`).
 */

declare(strict_types=1);

$breadcrumb_items = array();
if ( isset( $args ) && is_array( $args ) && isset( $args['items'] ) && is_array( $args['items'] ) ) {
	$breadcrumb_items = $args['items'];
} elseif ( isset( $items ) && is_array( $items ) ) {
	$breadcrumb_items = $items;
} else {
	$qv = get_query_var( 'dba_breadcrumb_items', array() );
	$breadcrumb_items = is_array( $qv ) ? $qv : array();
}

if ( count( $breadcrumb_items ) < 2 ) {
	return;
}

?>

		<nav class="py-3 px-2 md:px-4 lg:px-0" aria-label="<?php esc_attr_e( 'Breadcrumb', 'dynamic-book-archive' ); ?>">
			<ol class="m-0 flex list-none flex-wrap items-center gap-y-1 p-0 text-sm text-brand">
				<?php
				$last_index = count( $breadcrumb_items ) - 1;
				foreach ( $breadcrumb_items as $i => $item ) {
					$is_last = ( $i === $last_index );
					$label   = wp_strip_all_tags( $item['label'] );
					?>
					<li class="inline-flex flex-wrap items-center">
						<?php if ( $is_last ) : ?>
							<span class="font-medium " aria-current="page"><?php echo esc_html( $label ); ?></span>
						<?php else : ?>
							<a class="text-brand-light no-underline hover:text-brand hover:underline" href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $label ); ?></a>
							<span class="mx-2" aria-hidden="true">></span>
						<?php endif; ?>
					</li>
					<?php 
				}
				?>
			</ol>
		</nav>

