<?php
/**
 * Book archive sort dialog (args-only).
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

if ( ! isset( $args ) || ! is_array( $args ) ) {
	return;
}

$sort_options = isset( $args['sort_options'] ) && is_array( $args['sort_options'] ) ? $args['sort_options'] : array();
if ( empty( $sort_options ) ) {
	return;
}

?>
<dialog id="book-archive-dialog-sort" class="relative z-50 hidden w-[min(100vw-2rem,24rem)] max-h-[min(90dvh,36rem)] gap-5 overflow-visible rounded-2xl border-0 bg-surface p-6 text-brand-muted shadow-main backdrop:bg-canvas/80 sm:p-7 open:m-0 open:flex open:max-h-[min(90dvh,36rem)] open:flex-col open:fixed open:inset-auto focus:outline-none focus-visible:outline-0"
	x-ref="sortDialog"
	@close="onSortClose()"
	aria-labelledby="book-archive-dialog-sort-title">
	<div class="shrink-0">
		<div class="flex items-start justify-between gap-3 pb-4">
			<h2 id="book-archive-dialog-sort-title" class="font-display text-lg font-semibold tracking-tight text-content"><?php esc_html_e( 'Sort books', 'dynamic-book-archive' ); ?></h2>
			<form method="dialog">
				<button type="submit" class="cursor-pointer rounded-md p-1 text-brand/80 hover:bg-filters-background hover:text-brand" aria-label="<?php esc_attr_e( 'Close', 'dynamic-book-archive' ); ?>"><?php dba_the_inline_icon( 'bx/bx-x', 'block size-6' ); ?></button>
			</form>
		</div>
	</div>
	<div class="min-h-0 flex-1 overflow-y-auto overflow-x-clip overscroll-contain">
		<div class="flex flex-col gap-5">
			<div class="flex flex-col">
				<label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-brand/80" for="book-archive-toolbar-sort-staging"><?php esc_html_e( 'Sort by', 'dynamic-book-archive' ); ?></label>
				<?php
				get_template_part(
					'template-parts/ui/select',
					null,
					array(
						'id'            => 'book-archive-toolbar-sort-staging',
						'class'         => 'mt-0 w-full appearance-auto rounded-lg border border-stroke bg-filters-background px-3 py-2.5! text-sm text-brand focus:outline-none focus-visible:ring-0 focus-visible:ring-filters-link/50',
						'label'         => '',
						'label_sr_only' => true,
						'options'       => $sort_options,
						'x_ref'         => 'stagingSort',
					)
				);
				?>
			</div>
		</div>
	</div>
	<div class="shrink-0 pt-5">
		<div class="flex justify-end">
			<button type="button" class="js-book-archive-sort-apply cursor-pointer rounded-lg bg-filters-link-background px-6 py-2.5 font-display text-sm font-semibold text-filters-link shadow-sm transition hover:bg-filters-background hover:text-brand hover:shadow-bronze-glow duration-200 ease-out" @click="applySort()"><?php esc_html_e( 'Apply', 'dynamic-book-archive' ); ?></button>
		</div>
	</div>
</dialog>

