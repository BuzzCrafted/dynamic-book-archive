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
?>

<div class="mx-auto pb-16 flex w-full flex-1 flex-col md:flex-row">
	<main
		id="primary"
		class="js-book-archive group flex-1"
		data-books-cpt-category="<?php echo esc_attr($book_archive_category_attr); ?>"
		data-books-cpt-category-slug="<?php echo esc_attr($book_archive_category_slug); ?>"
		data-books-cpt-page="<?php echo esc_attr((string) $book_archive_paged); ?>"
		data-books-cpt-search=""
		data-books-cpt-orderby="date"
		data-books-cpt-order="desc"
		data-books-cpt-year="0">
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
				<div class="flex justify-between items-center">
					<div class="js-book-archive-toolbar relative z-20 flex w-full max-w-md overflow-visible sm:ml-auto sm:max-w-none sm:items-end md:w-full md:max-w-none md:items-end group-aria-busy:pointer-events-none">
						<div class="flex w-full flex-col gap-2 sm:flex-row sm:flex-nowrap sm:justify-end md:w-auto">
							<label class="sr-only" for="book-archive-toolbar-sort"><?php esc_html_e('Sort books', 'dynamic-book-archive'); ?></label>
							<select
								id="book-archive-toolbar-sort"
								class="js-book-archive-sort w-fit! min-w-34 rounded-md! border-none! bg-filters-background! px-3! py-2! text-sm! text-filters-text! shadow-main focus:outline-none! focus:ring-0! focus-visible:outline-none! focus-visible:ring-0!">
								<option value="author:asc"><?php esc_html_e('Sort by Author', 'dynamic-book-archive'); ?></option>
								<option value="date:desc"><?php esc_html_e('Sort by Date', 'dynamic-book-archive'); ?></option>
								<option value="title:asc"><?php esc_html_e('Sort by Title', 'dynamic-book-archive'); ?></option>
							</select>
							<label class="sr-only" for="book-archive-toolbar-year"><?php esc_html_e('Filter by year', 'dynamic-book-archive'); ?></label>
							<select
								id="book-archive-toolbar-year"
								class="js-book-archive-year w-fit! min-w-34 rounded-md! border-none! bg-filters-background! px-3! py-2! text-sm! text-filters-text! shadow-main focus:outline-none! focus:ring-0! focus-visible:outline-none! focus-visible:ring-0!">
								<option value="0"><?php esc_html_e('All years', 'dynamic-book-archive'); ?></option>
								<?php foreach ($book_archive_years as $pub_year) : ?>
									<option value="<?php echo esc_attr((string) (int) $pub_year); ?>"><?php echo esc_html((string) (int) $pub_year); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>

					<div class="js-book-archive-search-wrap relative w-full max-w-md sm:ml-auto sm:max-w-xs md:max-w-xs md:justify-self-end group-aria-busy:pointer-events-none">
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
			</div>
		</div>

		<?php dba_the_book_archive_category_nav(); ?>

		<div class="js-book-archive-stage js-book-archive-stage-body contain-[layout] backface-hidden">
			<?php if (have_posts()) : ?>
				<div class="js-book-archive-grid grid auto-rows-max grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-x-[3%] gap-y-[4%] w-full pb-12 content-start items-stretch motion-safe:transition-[opacity,filter,transform] motion-safe:duration-200 motion-safe:ease-out group-aria-busy:pointer-events-none motion-safe:group-data-book-archive-dim:opacity-[.86] motion-safe:group-data-book-archive-dim:blur-[2px] motion-safe:group-data-book-archive-dim:scale-[.992] motion-safe:group-data-book-archive-dim:will-change-[opacity,filter,transform]">
					<?php
					while (have_posts()) :
						the_post();
						get_template_part('template-parts/book/archive/card');
					endwhile;
					?>
				</div>

				<div class="js-book-archive-pagination mt-7 mb-10 group-aria-busy:pointer-events-none">
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

