<?php
/**
 * Book archive filter dialog (args-only).
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

if ( ! isset( $args ) || ! is_array( $args ) ) {
	return;
}

$year_floor   = isset( $args['year_floor'] ) ? (int) $args['year_floor'] : 1900;
$year_ceiling = isset( $args['year_ceiling'] ) ? (int) $args['year_ceiling'] : (int) gmdate( 'Y' );

?>
<dialog id="book-archive-dialog-filter" class="relative z-50 hidden w-[min(100vw-2rem,26rem)] max-h-[min(92dvh,40rem)] gap-5 overflow-visible rounded-2xl border-0 bg-surface p-6 text-brand-muted shadow-main backdrop:bg-canvas/80 sm:p-7 open:m-0 open:flex open:max-h-[min(92dvh,40rem)] open:flex-col open:fixed open:inset-auto"
	x-ref="filterDialog"
	@close="onFilterClose()"
	aria-labelledby="book-archive-dialog-filter-title">
	<div class="shrink-0">
		<div class="flex items-start justify-between gap-3 pb-4">
			<h2 id="book-archive-dialog-filter-title" class="font-display text-lg font-semibold tracking-tight text-content"><?php esc_html_e( 'Filter books', 'dynamic-book-archive' ); ?></h2>
			<form method="dialog">
				<button type="submit" class="cursor-pointer rounded-md p-1 text-brand/80 hover:bg-filters-background hover:text-brand" aria-label="<?php esc_attr_e( 'Close', 'dynamic-book-archive' ); ?>"><?php dba_the_inline_icon( 'bx/bx-x', 'block size-6' ); ?></button>
			</form>
		</div>
	</div>
	<div class="min-h-0 flex-1 overflow-y-auto overscroll-contain">
		<div class="flex flex-col gap-5">
			<div class="flex flex-col">
				<label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-brand/80" for="book-archive-toolbar-author-staging"><?php esc_html_e( 'Author', 'dynamic-book-archive' ); ?></label>
				<select id="book-archive-toolbar-author-staging" x-ref="stagingAuthor" class="mt-0 w-full appearance-auto rounded-lg bg-filters-background px-3 py-2.5! text-sm text-brand shadow-main! border-none! focus:outline-none focus-visible:ring-0 focus-visible:ring-filters-link/50"></select>
			</div>
			<div class="flex flex-col">
				<label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-brand/80" for="book-archive-toolbar-tag-staging"><?php esc_html_e( 'Tags', 'dynamic-book-archive' ); ?></label>
				<select id="book-archive-toolbar-tag-staging" x-ref="stagingTag" class="mt-0 w-full appearance-auto rounded-lg bg-filters-background px-3 py-2.5! text-sm text-brand shadow-main! border-none! focus:outline-none focus-visible:ring-0 focus-visible:ring-filters-link/50"></select>
			</div>
			<div class="flex flex-col">
				<?php
				get_template_part(
					'template-parts/ui/year-range',
					null,
					array(
						'variant'           => 'staging',
						'year_floor'        => $year_floor,
						'year_ceiling'      => $year_ceiling,
						'label_any'         => __( 'Any', 'dynamic-book-archive' ),
						'input_year_min_id' => 'book-archive-toolbar-year-min-staging',
						'input_year_max_id' => 'book-archive-toolbar-year-max-staging',
						'input_min_class'   => 'js-staging-year-min',
						'input_max_class'   => 'js-staging-year-max',
					)
				);
				?>
			</div>
		</div>
	</div>
	<div class="shrink-0 pt-5">
		<div class="flex flex-wrap items-center justify-between gap-3">
			<button type="button" class="js-book-archive-filter-reset cursor-pointer bg-transparent px-0 py-2 font-display text-xs font-semibold uppercase tracking-wider text-brand/65 hover:text-filters-link-hover hover:[text-shadow:0_0_6px_rgba(230,215,194,0.6)]" @click="resetFilter()"><?php esc_html_e( 'Reset filters', 'dynamic-book-archive' ); ?></button>
			<button type="button" class="js-book-archive-filter-apply cursor-pointer rounded-lg bg-filters-link-background px-6 py-2.5 font-display text-sm font-semibold text-filters-link shadow-sm transition hover:bg-book-secondary/10 hover:text-filters-link-hover hover:shadow-bronze-glow duration-200 ease-out" @click="applyFilter()"><?php esc_html_e( 'Apply', 'dynamic-book-archive' ); ?></button>
		</div>
	</div>
</dialog>

