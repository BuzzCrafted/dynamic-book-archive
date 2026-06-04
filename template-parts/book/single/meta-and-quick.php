<?php
/**
 * Single book meta (dl) + quick info sidebar.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

if ( ! isset( $args ) || ! is_array( $args ) ) {
	return;
}

$post_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : 0;
$meta    = isset( $args['meta'] ) && is_array( $args['meta'] ) ? $args['meta'] : array();
$quick_items = isset( $args['quick_items'] ) && is_array( $args['quick_items'] ) ? $args['quick_items'] : array();

$author_display   = isset( $meta['author_display'] ) && is_string( $meta['author_display'] ) ? $meta['author_display'] : '';
$category_label   = isset( $meta['category_label'] ) && is_string( $meta['category_label'] ) ? $meta['category_label'] : '';
$collection_label = isset( $meta['collection_label'] ) && is_string( $meta['collection_label'] ) ? $meta['collection_label'] : '';

$row_class = 'grid grid-cols-[minmax(0,7.5rem)_1fr] gap-x-2 gap-y-1 py-1 text-base sm:grid-cols-[9rem_1fr]';
$dt_class  = 'font-main tracking-wider text-brand-muted';
$dd_class  = 'font-main text-brand';

/**
 * Filters the icon for a quick item on single book pages.
 *
 * @param string $icon Icon key.
 */
$icon_html = static function ( string $icon ): string {
	$icons = array(
		'pages'    => 'bx/bx-book-open',
		'size'     => 'bx/bx-book',
		'binding'  => 'bx/bx-bookmark-alt',
		'language' => 'bx/bx-globe',
	);

	if ( isset( $icons[ $icon ] ) ) {
		return dba_get_inline_icon( $icons[ $icon ], 'h-5 w-5 shrink-0 text-brand-muted' );
	}
	return '';
};

?>
<div class="grid grid-cols-1 gap-3 items-start lg:grid-cols-[2fr_1fr]">
	<div class="flex min-w-0 flex-col gap-4">
		<dl class="space-y-0">
			<?php
			if ( '' !== $author_display ) {
				get_template_part(
					'template-parts/ui/dl-row',
					null,
					array(
						'label'     => __( 'Author:', 'dynamic-book-archive' ),
						'value'     => $author_display,
						'row_class' => $row_class,
						'dt_class'  => $dt_class,
						'dd_class'  => $dd_class,
					)
				);
			}

			if ( '' !== $category_label ) {
				get_template_part(
					'template-parts/ui/dl-row',
					null,
					array(
						'label'     => __( 'Category:', 'dynamic-book-archive' ),
						'value'     => $category_label,
						'row_class' => $row_class,
						'dt_class'  => $dt_class,
						'dd_class'  => $dd_class,
					)
				);
			}

			if ( '' !== $collection_label ) {
				get_template_part(
					'template-parts/ui/dl-row',
					null,
					array(
						'label'     => __( 'Collection:', 'dynamic-book-archive' ),
						'value'     => $collection_label,
						'row_class' => $row_class,
						'dt_class'  => $dt_class,
						'dd_class'  => $dd_class,
					)
				);
			}
			?>
		</dl>
		<hr class="h-px w-full shrink-0 border-0 bg-linear-to-r from-transparent from-0% via-brand/85 via-38% to-transparent to-100% [box-shadow:0_0_12px_color-mix(in_oklch,var(--color-brand)_35%,transparent)]" role="presentation" />
	</div>

	<?php if ( count( $quick_items ) > 0 ) : ?>
		<aside class="mt-4 self-start rounded-lg border border-brand-muted/50 p-4 text-brand md:mt-0" aria-label="<?php esc_attr_e( 'Quick facts', 'dynamic-book-archive' ); ?>">
			<h2 class="mb-3 font-display text-sm font-semibold uppercase tracking-[0.2em] text-book-secondary"><?php esc_html_e( 'Quick info', 'dynamic-book-archive' ); ?></h2>
			<ul class="m-0 flex list-none flex-col gap-2 p-0">
				<?php foreach ( $quick_items as $item ) : ?>
					<?php
					if ( ! is_array( $item ) ) {
						continue;
					}
					$text = isset( $item['text'] ) && is_string( $item['text'] ) ? $item['text'] : '';
					$icon = isset( $item['icon'] ) && is_string( $item['icon'] ) ? $item['icon'] : '';
					if ( '' === $text ) {
						continue;
					}
					?>
					<li class="flex place-items-center gap-2 text-sm leading-relaxed text-brand">
						<?php echo $icon_html( $icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wrapper returns safe HTML. ?>
						<span class="text-brand"><?php echo esc_html( $text ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</aside>
	<?php endif; ?>
</div>

