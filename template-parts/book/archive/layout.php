<?php

/**
 * Shared layout: book post type archive and `book_category` taxonomy archive.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

$book_archive_title = __('Library', 'dynamic-book-archive');
$filter_term        = dba_get_book_archive_filtered_category();
if ($filter_term instanceof WP_Term) {
	$book_archive_title = sprintf(
		/* translators: 1: category name, 2: static label "Library". */
		__('%1$s %2$s', 'dynamic-book-archive'),
		$filter_term->name,
		$book_archive_title
	);
}

$book_archive_paged = (int) get_query_var('paged');
if ($book_archive_paged < 1) {
	$book_archive_paged = (int) get_query_var('page');
}
$book_archive_paged = max(1, $book_archive_paged);

$book_archive_category_id   = dba_get_book_archive_filtered_category_id();
$book_archive_category_attr = $book_archive_category_id > 0 ? (string) $book_archive_category_id : '';
$book_archive_category_slug = ($filter_term instanceof WP_Term) ? (string) $filter_term->slug : '';

$book_archive_years = function_exists('dba_get_book_archive_distinct_publication_years')
	? dba_get_book_archive_distinct_publication_years()
	: array();

$book_archive_year_ceiling = (int) ( $book_archive_years[0] ?? 0 );
$book_archive_year_floor   = (int) ( $book_archive_years[ count( $book_archive_years ) - 1 ] ?? 0 );
if ( $book_archive_year_floor <= 0 ) {
	$book_archive_year_floor = 1900;
}
if ( $book_archive_year_ceiling <= 0 ) {
	$book_archive_year_ceiling = (int) gmdate( 'Y' );
}
if ( $book_archive_year_floor > $book_archive_year_ceiling ) {
	$tmp                       = $book_archive_year_floor;
	$book_archive_year_floor   = $book_archive_year_ceiling;
	$book_archive_year_ceiling = $tmp;
}

$book_archive_authors = function_exists( 'dba_get_book_archive_distinct_authors' )
	? dba_get_book_archive_distinct_authors()
	: array();

$book_archive_tags = function_exists( 'dba_get_book_archive_distinct_tags' )
	? dba_get_book_archive_distinct_tags()
	: array();
?>

<div class="mx-auto pb-4 md:pb-8 lg:pb-16 flex w-full flex-1 flex-col md:flex-row">
	<main
		id="primary"
		class="js-book-archive group flex-1"
		data-books-cpt-category="<?php echo esc_attr($book_archive_category_attr); ?>"
		data-books-cpt-category-slug="<?php echo esc_attr($book_archive_category_slug); ?>"
		data-books-cpt-page="<?php echo esc_attr((string) $book_archive_paged); ?>"
		data-books-cpt-search=""
		data-books-cpt-orderby="date"
		data-books-cpt-order="desc"
		data-books-cpt-author=""
		data-books-cpt-tag=""
		data-books-cpt-year-floor="<?php echo esc_attr( (string) $book_archive_year_floor ); ?>"
		data-books-cpt-year-ceil="<?php echo esc_attr( (string) $book_archive_year_ceiling ); ?>"
		data-books-cpt-year-min="0"
		data-books-cpt-year-max="0">
		<div class="js-book-archive-stage js-book-archive-stage-head contain-[layout] backface-hidden">
			<div class="mx-auto flex flex-col">
				<header class="flex min-h-0 flex-col justify-center transition-all transition-discrete">
					<h1 class="js-book-archive-title py-4 text-center text-3xl font-semibold uppercase tracking-tight text-library-primary group-aria-busy:pointer-events-none"><span class="js-book-archive-title-value inline-block min-w-0"><?php echo esc_html($book_archive_title); ?></span></h1>
					<?php
					dba_the_book_archive_intro(
						array(
							'page_header_embed'   => true,
							'intro_wrapper_class' => 'js-book-archive-intro flex min-h-0 min-w-0 items-center pb-4 justify-center text-center text-lg group-aria-busy:pointer-events-none',
						)
					);
					?>
				</header>
				<div class="js-book-archive-toolbar relative z-20 w-full group-aria-busy:pointer-events-none">
					<div class="flex w-full flex-wrap items-center justify-end gap-2">
					<button
						type="button"
						class="js-book-archive-open-sort inline-flex min-h-10 min-w-30 shrink-0 cursor-pointer items-center gap-2.5 rounded-xl bg-primary bg-filters-background py-2 pl-3 pr-2.5 font-display text-sm text-filters-text shadow-main transition-shadow hover:shadow-main-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-filters-link/60 [&[aria-expanded='true']>span:last-child]:motion-safe:rotate-180"
						aria-haspopup="dialog"
						aria-expanded="false"
						aria-controls="book-archive-dialog-sort">
						<span class="text-heading [&>i]:flex [&>i]:size-4 [&>i]:items-center [&>i]:justify-center" aria-hidden="true"><?php dba_the_inline_icon( 'bx/bx-sort', 'text-current' ); ?></span>
						<span class="min-w-0 flex-1 text-left leading-none"><?php esc_html_e( 'Sort', 'dynamic-book-archive' ); ?></span>
						<span class="shrink-0 text-heading motion-safe:transition-transform [&>i]:flex [&>i]:size-4 [&>i]:items-center [&>i]:justify-center" aria-hidden="true"><?php dba_the_inline_icon( 'bx/bx-chevron-down', 'text-current' ); ?></span>
					</button>
					<button
						type="button"
						class="js-book-archive-open-filter inline-flex min-h-10 min-w-30 shrink-0 cursor-pointer items-center gap-2.5 rounded-xl bg-primary bg-filters-background py-2 pl-3 pr-2.5 font-display text-sm text-filters-text shadow-main transition-shadow hover:shadow-main-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-filters-link/60 [&[aria-expanded='true']>span:last-child]:motion-safe:rotate-180"
						aria-haspopup="dialog"
						aria-expanded="false"
						aria-controls="book-archive-dialog-filter">
						<span class="text-heading [&>i]:flex [&>i]:size-4 [&>i]:items-center [&>i]:justify-center" aria-hidden="true"><?php dba_the_inline_icon( 'bx/bx-slider', 'text-current' ); ?></span>
						<span class="min-w-0 flex-1 text-left leading-none"><?php esc_html_e( 'Filter', 'dynamic-book-archive' ); ?></span>
						<span class="shrink-0 text-heading motion-safe:transition-transform [&>i]:flex [&>i]:size-4 [&>i]:items-center [&>i]:justify-center" aria-hidden="true"><?php dba_the_inline_icon( 'bx/bx-chevron-down', 'text-current' ); ?></span>
					</button>

					<div class="js-book-archive-search-wrap relative w-full min-w-48 max-w-md sm:w-auto sm:max-w-xs md:max-w-xs group-aria-busy:pointer-events-none">
						<label class="sr-only" for="book-archive-toolbar-search"><?php esc_html_e('Search books', 'dynamic-book-archive'); ?></label>
						<input
							type="search"
							id="book-archive-toolbar-search"
							class="js-book-archive-search search-cancel-themed w-full rounded-full shadow-main bg-filters-background py-2 pl-3 pr-10 text-sm text-filters-text placeholder:text-filters-text/50 focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-filters-link"
							placeholder="<?php echo esc_attr(__('Search books…', 'dynamic-book-archive')); ?>"
							autocomplete="off" />
						<span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-heading" aria-hidden="true">
							<?php dba_the_inline_icon('search-icon', 'h-4 w-4'); ?>
						</span>
					</div>
					</div>

					<div class="js-book-archive-real-controls pointer-events-none fixed left-0 top-0 -z-50 h-px w-px overflow-hidden opacity-0" aria-hidden="true">
						<label class="sr-only" for="book-archive-toolbar-sort"><?php esc_html_e( 'Sort books', 'dynamic-book-archive' ); ?></label>
						<select
							id="book-archive-toolbar-sort"
							class="js-book-archive-sort"
							tabindex="-1">
							<option value="author:asc"><?php esc_html_e( 'Author', 'dynamic-book-archive' ); ?></option>
							<option value="date:desc"><?php esc_html_e( 'Date', 'dynamic-book-archive' ); ?></option>
							<option value="title:asc"><?php esc_html_e( 'Title', 'dynamic-book-archive' ); ?></option>
						</select>

						<label class="sr-only" for="book-archive-toolbar-author"><?php esc_html_e( 'Filter by author', 'dynamic-book-archive' ); ?></label>
						<select id="book-archive-toolbar-author" class="js-book-archive-author" tabindex="-1">
							<option value=""><?php esc_html_e( 'All authors', 'dynamic-book-archive' ); ?></option>
							<?php foreach ( $book_archive_authors as $author ) : ?>
								<?php if ( ! is_string( $author ) || '' === trim( $author ) ) : ?>
									<?php continue; ?>
								<?php endif; ?>
								<option value="<?php echo esc_attr( $author ); ?>"><?php echo esc_html( $author ); ?></option>
							<?php endforeach; ?>
						</select>

						<label class="sr-only" for="book-archive-toolbar-tag"><?php esc_html_e( 'Filter by tag', 'dynamic-book-archive' ); ?></label>
						<select id="book-archive-toolbar-tag" class="js-book-archive-tag" tabindex="-1">
							<option value=""><?php esc_html_e( 'All tags', 'dynamic-book-archive' ); ?></option>
							<?php foreach ( $book_archive_tags as $tag ) : ?>
								<?php
								$tag_slug = is_array( $tag ) && isset( $tag['slug'] ) && is_string( $tag['slug'] ) ? $tag['slug'] : '';
								$tag_name = is_array( $tag ) && isset( $tag['name'] ) && is_string( $tag['name'] ) ? $tag['name'] : '';
								?>
								<?php if ( '' === $tag_slug || '' === $tag_name ) : ?>
									<?php continue; ?>
								<?php endif; ?>
								<option value="<?php echo esc_attr( $tag_slug ); ?>"><?php echo esc_html( $tag_name ); ?></option>
							<?php endforeach; ?>
						</select>

						<div
							class="dba-year-range w-full"
							data-year-floor="<?php echo esc_attr( (string) $book_archive_year_floor ); ?>"
							data-year-ceil="<?php echo esc_attr( (string) $book_archive_year_ceiling ); ?>"
							data-label-any="<?php echo esc_attr__( 'Any', 'dynamic-book-archive' ); ?>">
							<span class="js-book-archive-year-min-label sr-only"></span>
							<span class="js-book-archive-year-max-label sr-only"></span>
							<div class="dba-year-range__sliders relative h-5">
								<label class="sr-only" for="book-archive-toolbar-year-min"><?php esc_html_e( 'Filter by published year (from)', 'dynamic-book-archive' ); ?></label>
								<input
									id="book-archive-toolbar-year-min"
									type="range"
									class="js-book-archive-year-min dba-year-range__input"
									tabindex="-1"
									min="<?php echo esc_attr( (string) $book_archive_year_floor ); ?>"
									max="<?php echo esc_attr( (string) $book_archive_year_ceiling ); ?>"
									value="<?php echo esc_attr( (string) $book_archive_year_floor ); ?>"
									step="1"
									aria-label="<?php echo esc_attr__( 'From year', 'dynamic-book-archive' ); ?>" />
								<label class="sr-only" for="book-archive-toolbar-year-max"><?php esc_html_e( 'Filter by published year (to)', 'dynamic-book-archive' ); ?></label>
								<input
									id="book-archive-toolbar-year-max"
									type="range"
									class="js-book-archive-year-max dba-year-range__input"
									tabindex="-1"
									min="<?php echo esc_attr( (string) $book_archive_year_floor ); ?>"
									max="<?php echo esc_attr( (string) $book_archive_year_ceiling ); ?>"
									value="<?php echo esc_attr( (string) $book_archive_year_ceiling ); ?>"
									step="1"
									aria-label="<?php echo esc_attr__( 'To year', 'dynamic-book-archive' ); ?>" />
								<div class="dba-year-range__track" aria-hidden="true"></div>
							</div>
						</div>
					</div>

					<dialog id="book-archive-dialog-sort" class="relative z-50 hidden w-[min(100vw-2rem,24rem)] max-h-[min(90dvh,36rem)] gap-5 overflow-visible rounded-2xl border-0 bg-surface p-6 text-body shadow-main backdrop:bg-page/80 sm:p-7 open:m-0 open:flex open:max-h-[min(90dvh,36rem)] open:flex-col open:fixed open:inset-auto" aria-labelledby="book-archive-dialog-sort-title">
						<div class="shrink-0">
							<div class="flex items-start justify-between gap-3 border-b border-border-main/80 pb-4">
								<h2 id="book-archive-dialog-sort-title" class="font-display text-lg font-semibold tracking-tight text-heading"><?php esc_html_e( 'Sort books', 'dynamic-book-archive' ); ?></h2>
								<form method="dialog">
									<button type="submit" class="cursor-pointer rounded-md p-1 text-filters-text/80 hover:bg-filters-background hover:text-filters-text" aria-label="<?php esc_attr_e( 'Close', 'dynamic-book-archive' ); ?>"><?php dba_the_inline_icon( 'bx/bx-x', 'block size-6' ); ?></button>
								</form>
							</div>
						</div>
						<div class="min-h-0 flex-1 overflow-y-auto overflow-x-clip overscroll-contain">
							<div class="flex flex-col gap-5">
								<div class="flex flex-col">
									<label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-filters-text/80" for="book-archive-toolbar-sort-staging"><?php esc_html_e( 'Sort by', 'dynamic-book-archive' ); ?></label>
									<select id="book-archive-toolbar-sort-staging" class="mt-0 w-full appearance-auto rounded-lg border border-border-main bg-filters-background px-3 py-2.5 text-sm text-filters-text shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-filters-link/50">
										<option value="author:asc"><?php esc_html_e( 'Author', 'dynamic-book-archive' ); ?></option>
										<option value="date:desc"><?php esc_html_e( 'Date', 'dynamic-book-archive' ); ?></option>
										<option value="title:asc"><?php esc_html_e( 'Title', 'dynamic-book-archive' ); ?></option>
									</select>
								</div>
							</div>
						</div>
						<div class="shrink-0 border-t border-border-main/80 pt-5">
							<div class="flex justify-end">
								<button type="button" class="js-book-archive-sort-apply cursor-pointer rounded-lg bg-filters-link-background px-6 py-2.5 font-display text-sm font-semibold text-filters-link shadow-sm hover:opacity-90"><?php esc_html_e( 'Apply', 'dynamic-book-archive' ); ?></button>
							</div>
						</div>
					</dialog>

					<dialog id="book-archive-dialog-filter" class="relative z-50 hidden w-[min(100vw-2rem,26rem)] max-h-[min(92dvh,40rem)] gap-5 overflow-visible rounded-2xl border-0 bg-surface p-6 text-body shadow-main backdrop:bg-page/80 sm:p-7 open:m-0 open:flex open:max-h-[min(92dvh,40rem)] open:flex-col open:fixed open:inset-auto" aria-labelledby="book-archive-dialog-filter-title">
						<div class="shrink-0">
							<div class="flex items-start justify-between gap-3 border-b border-border-main/80 pb-4">
								<h2 id="book-archive-dialog-filter-title" class="font-display text-lg font-semibold tracking-tight text-heading"><?php esc_html_e( 'Filter books', 'dynamic-book-archive' ); ?></h2>
								<form method="dialog">
									<button type="submit" class="cursor-pointer rounded-md p-1 text-filters-text/80 hover:bg-filters-background hover:text-filters-text" aria-label="<?php esc_attr_e( 'Close', 'dynamic-book-archive' ); ?>"><?php dba_the_inline_icon( 'bx/bx-x', 'block size-6' ); ?></button>
								</form>
							</div>
						</div>
						<div class="min-h-0 flex-1 overflow-y-auto overflow-x-clip overscroll-contain">
							<div class="flex flex-col gap-5">
								<div class="flex flex-col">
									<label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-filters-text/80" for="book-archive-toolbar-author-staging"><?php esc_html_e( 'Author', 'dynamic-book-archive' ); ?></label>
									<select id="book-archive-toolbar-author-staging" class="mt-0 w-full appearance-auto rounded-lg border border-border-main bg-filters-background px-3 py-2.5 text-sm text-filters-text shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-filters-link/50"></select>
								</div>
								<div class="flex flex-col">
									<label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-filters-text/80" for="book-archive-toolbar-tag-staging"><?php esc_html_e( 'Tags', 'dynamic-book-archive' ); ?></label>
									<select id="book-archive-toolbar-tag-staging" class="mt-0 w-full appearance-auto rounded-lg border border-border-main bg-filters-background px-3 py-2.5 text-sm text-filters-text shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-filters-link/50"></select>
								</div>
								<div class="flex flex-col">
									<div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
										<span class="mb-0 block text-xs font-semibold uppercase tracking-wider text-filters-text/80"><?php esc_html_e( 'Publication years', 'dynamic-book-archive' ); ?></span>
										<span class="text-sm font-medium tabular-nums text-filters-text/90">
											<span class="js-staging-year-min-label"><?php esc_html_e( 'Any', 'dynamic-book-archive' ); ?></span>
											<span class="px-1 text-filters-text/45" aria-hidden="true">–</span>
											<span class="js-staging-year-max-label"><?php esc_html_e( 'Any', 'dynamic-book-archive' ); ?></span>
										</span>
									</div>
									<div
										class="dba-year-range mt-3 w-full rounded-lg border border-border-main/50 bg-filters-background px-3 py-4 shadow-sm"
										data-year-floor="<?php echo esc_attr( (string) $book_archive_year_floor ); ?>"
										data-year-ceil="<?php echo esc_attr( (string) $book_archive_year_ceiling ); ?>"
										data-label-any="<?php echo esc_attr__( 'Any', 'dynamic-book-archive' ); ?>">
										<div class="dba-year-range__sliders relative h-5">
											<label class="sr-only" for="book-archive-toolbar-year-min-staging"><?php esc_html_e( 'Filter by published year (from)', 'dynamic-book-archive' ); ?></label>
											<input
												id="book-archive-toolbar-year-min-staging"
												type="range"
												class="dba-year-range__input js-staging-year-min"
												min="<?php echo esc_attr( (string) $book_archive_year_floor ); ?>"
												max="<?php echo esc_attr( (string) $book_archive_year_ceiling ); ?>"
												value="<?php echo esc_attr( (string) $book_archive_year_floor ); ?>"
												step="1"
												aria-label="<?php echo esc_attr__( 'From year', 'dynamic-book-archive' ); ?>" />
											<label class="sr-only" for="book-archive-toolbar-year-max-staging"><?php esc_html_e( 'Filter by published year (to)', 'dynamic-book-archive' ); ?></label>
											<input
												id="book-archive-toolbar-year-max-staging"
												type="range"
												class="dba-year-range__input js-staging-year-max"
												min="<?php echo esc_attr( (string) $book_archive_year_floor ); ?>"
												max="<?php echo esc_attr( (string) $book_archive_year_ceiling ); ?>"
												value="<?php echo esc_attr( (string) $book_archive_year_ceiling ); ?>"
												step="1"
												aria-label="<?php echo esc_attr__( 'To year', 'dynamic-book-archive' ); ?>" />
											<div class="dba-year-range__track" aria-hidden="true"></div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="shrink-0 border-t border-border-main/80 pt-5">
							<div class="flex flex-wrap items-center justify-between gap-3">
								<button type="button" class="js-book-archive-filter-reset cursor-pointer bg-transparent px-0 py-2 font-display text-xs font-semibold uppercase tracking-wider text-filters-text/65 hover:text-heading hover:underline"><?php esc_html_e( 'Reset filters', 'dynamic-book-archive' ); ?></button>
								<button type="button" class="js-book-archive-filter-apply cursor-pointer rounded-lg bg-filters-link-background px-6 py-2.5 font-display text-sm font-semibold text-filters-link shadow-sm hover:opacity-90"><?php esc_html_e( 'Apply', 'dynamic-book-archive' ); ?></button>
							</div>
						</div>
					</dialog>
				</div>
			</div>
		</div>

		<?php dba_the_book_archive_category_nav(); ?>

		<div class="js-book-archive-stage js-book-archive-stage-body contain-[layout] backface-hidden">
			<?php if (have_posts()) : ?>
				<div class="js-book-archive-grid grid auto-rows-max grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-1 md:gap-x-2 lg:gap-x-[3%] gap-y-1 md:gap-y-2 lg:gap-y-[4%] w-full pb-2 md:pb-4 lg:pb-12 content-start items-stretch motion-safe:transition-[opacity,filter,transform] motion-safe:duration-200 motion-safe:ease-out group-aria-busy:pointer-events-none motion-safe:group-data-book-archive-dim:opacity-[.86] motion-safe:group-data-book-archive-dim:blur-[2px] motion-safe:group-data-book-archive-dim:scale-[.992] motion-safe:group-data-book-archive-dim:will-change-[opacity,filter,transform]">
					<?php
					while (have_posts()) :
						the_post();
						get_template_part('template-parts/book/archive/card');
					endwhile;
					?>
				</div>

				<div class="js-book-archive-pagination mt-2 md:mt-4 lg:mt-7 mb-2 md:mb-4 lg:mb-10 group-aria-busy:pointer-events-none">
					<?php dba_the_book_pagination(); ?>
				</div>
			<?php else : ?>
				<div class="js-book-archive-grid">
					<?php get_template_part('template-parts/content/none'); ?>
				</div>
			<?php endif; ?>
		</div>
	</main>
</div>

