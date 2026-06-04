<?php
/**
 * Book archive pagination markup (Previous | page numbers | Next).
 *
 * Loaded via {@see dba_the_book_pagination()}. Data is passed in {@see get_template_part()}
 * `$args['pagination']` (WordPress does not extract `$args` into separate variables on all versions).
 *
 * @package Dynamic_Book_Archive
 *
 * @var array<string, mixed> $args Arguments from get_template_part() (includes `pagination`).
 */

declare(strict_types=1);

$pagination_data = array();

if ( isset( $args ) && is_array( $args ) && isset( $args['pagination'] ) && is_array( $args['pagination'] ) ) {
	$pagination_data = $args['pagination'];
} elseif ( isset( $pagination ) && is_array( $pagination ) ) {
	// Populated `$pagination` from the caller scope when args are extracted (some WordPress versions).
	$pagination_data = $pagination;
} else {
	$qv              = get_query_var( 'dba_book_pagination', array() );
	$pagination_data = is_array( $qv ) ? $qv : array();
}

$prev_html = isset( $pagination_data['prev_html'] ) && is_string( $pagination_data['prev_html'] ) ? $pagination_data['prev_html'] : '';
$next_html = isset( $pagination_data['next_html'] ) && is_string( $pagination_data['next_html'] ) ? $pagination_data['next_html'] : '';
$numbers   = isset( $pagination_data['numbers'] ) && is_array( $pagination_data['numbers'] ) ? $pagination_data['numbers'] : array();

if ( '' === $prev_html && '' === $next_html && array() === $numbers ) {
	return;
}

$edge_link_tw = 'inline-flex min-h-10 items-center justify-center rounded-lg shadow-main px-4 py-2 text-sm font-medium text-brand-muted no-underline transition-colors hover:border-brand-light/45 hover:text-brand-light';

$number_link_tw    = 'min-w-9 px-2 py-1.5 text-center text-sm text-brand-muted no-underline transition-all duration-200 hover:rounded-md hover:font-medium hover:text-brand-light hover:bg-surface hover:shadow-page-current focus-visible:ring-0 focus-visible:ring-offset-0 focus-visible:outline-0';
$number_current_tw = 'min-w-9 px-2 py-1.5 text-center text-sm rounded-md font-medium text-brand-light bg-surface shadow-page-current';
$number_dots_tw    = 'px-1 text-brand-muted';

if ( '' !== $prev_html ) {
	$prev_html = str_replace( 'class="prev page-numbers"', 'class="prev page-numbers ' . $edge_link_tw . '"', $prev_html );
}
if ( '' !== $next_html ) {
	$next_html = str_replace( 'class="next page-numbers"', 'class="next page-numbers ' . $edge_link_tw . '"', $next_html );
}

$numbers = array_map(
	static function ( $item ) use ( $number_link_tw, $number_current_tw, $number_dots_tw ) {
		if ( ! is_string( $item ) ) {
			return $item;
		}
		$item = str_replace( 'class="page-numbers dots"', 'class="page-numbers dots ' . $number_dots_tw . '"', $item );
		$item = str_replace( 'class="page-numbers current"', 'class="page-numbers current ' . $number_current_tw . '"', $item );
		$item = str_replace( 'class="page-numbers"', 'class="page-numbers ' . $number_link_tw . '"', $item );
		return $item;
	},
	$numbers
);

$edge_disabled_tw = 'inline-flex min-h-10 items-center justify-center rounded-lg shadow-main px-4 py-2 text-sm font-medium text-content';
$page_item_tw     = 'inline-flex items-center justify-center';
?>

<nav class="mx-auto w-full max-w-4xl px-4 font-sans text-sm" aria-label="<?php esc_attr_e( 'Posts navigation', 'dynamic-book-archive' ); ?>">
	<h2 class="screen-reader-text"><?php esc_html_e( 'Posts navigation', 'dynamic-book-archive' ); ?></h2>
	<div class="flex flex-wrap items-center justify-between gap-x-6 gap-y-4">
		<div class="shrink-0">
			<?php if ( $prev_html ) : ?>
				<?php echo $prev_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php else : ?>
				<span class="<?php echo esc_attr( $edge_disabled_tw ); ?>" aria-disabled="true"><?php esc_html_e( '< Previous', 'dynamic-book-archive' ); ?></span>
			<?php endif; ?>
		</div>

		<div class="flex min-w-0 flex-1 flex-wrap items-center justify-center gap-x-1 gap-y-2 sm:gap-x-2" role="list">
			<?php foreach ( $numbers as $item ) : ?>
				<?php if ( ! is_string( $item ) ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<span class="<?php echo esc_attr( $page_item_tw ); ?>" role="listitem"><?php echo $item; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<?php endforeach; ?>
		</div>

		<div class="shrink-0">
			<?php if ( $next_html ) : ?>
				<?php echo $next_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php else : ?>
				<span class="<?php echo esc_attr( $edge_disabled_tw ); ?>" aria-disabled="true"><?php esc_html_e( 'Next >', 'dynamic-book-archive' ); ?></span>
			<?php endif; ?>
		</div>
	</div>
</nav>
