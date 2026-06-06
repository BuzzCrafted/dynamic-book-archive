<?php
/**
 * Book archive toolbar and hidden native controls (dialogs are output in header.php).
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

if ( ! isset( $args ) || ! is_array( $args ) ) {
	return;
}

$authors = isset( $args['authors'] ) && is_array( $args['authors'] ) ? $args['authors'] : array();
$tags    = isset( $args['tags'] ) && is_array( $args['tags'] ) ? $args['tags'] : array();

$year_floor   = isset( $args['year_floor'] ) ? (int) $args['year_floor'] : 1900;
$year_ceiling = isset( $args['year_ceiling'] ) ? (int) $args['year_ceiling'] : (int) gmdate( 'Y' );

$sort_options = isset( $args['sort_options'] ) && is_array( $args['sort_options'] ) ? $args['sort_options'] : array();
if ( empty( $sort_options ) ) {
	$sort_options = array(
		array( 'value' => 'author:asc', 'label' => __( 'Author', 'dynamic-book-archive' ) ),
		array( 'value' => 'date:desc', 'label' => __( 'Date', 'dynamic-book-archive' ) ),
		array( 'value' => 'title:asc', 'label' => __( 'Title', 'dynamic-book-archive' ) ),
	);
}

$selected_author = isset( $args['selected_author'] ) && is_string( $args['selected_author'] ) ? trim( $args['selected_author'] ) : '';
$selected_tag    = isset( $args['selected_tag'] ) && is_string( $args['selected_tag'] ) ? trim( $args['selected_tag'] ) : '';

$author_options = array(
	array(
		'value'    => '',
		'label'    => __( 'All authors', 'dynamic-book-archive' ),
		'selected' => '' === $selected_author,
	),
);
foreach ( $authors as $author ) {
	if ( ! is_string( $author ) || '' === trim( $author ) ) {
		continue;
	}
	$author_trim = trim( $author );
	$author_options[] = array(
		'value'    => $author_trim,
		'label'    => $author_trim,
		'selected' => $author_trim === $selected_author,
	);
}

$tag_options = array(
	array(
		'value'    => '',
		'label'    => __( 'All tags', 'dynamic-book-archive' ),
		'selected' => '' === $selected_tag,
	),
);
foreach ( $tags as $tag ) {
	$tag_slug = is_array( $tag ) && isset( $tag['slug'] ) && is_string( $tag['slug'] ) ? $tag['slug'] : '';
	$tag_name = is_array( $tag ) && isset( $tag['name'] ) && is_string( $tag['name'] ) ? $tag['name'] : '';
	if ( '' === $tag_slug || '' === $tag_name ) {
		continue;
	}
	$tag_options[] = array(
		'value'    => $tag_slug,
		'label'    => $tag_name,
		'selected' => $tag_slug === $selected_tag,
	);
}

?>
<div class="js-book-archive-toolbar relative z-20 w-full group-aria-busy:pointer-events-none">
	<div class="flex w-full items-center justify-end gap-2">
		<button
			type="button"
			class="js-book-archive-open-sort inline-flex md:min-h-10 md:min-w-30 shrink-0 cursor-pointer items-center gap-2.5 rounded-xl bg-primary bg-filters-background py-2 pl-3 pr-2.5 font-display text-sm text-brand shadow-main transition hover:bg-book-secondary/10 hover:shadow-bronze-glow duration-200 ease-out focus:outline-none focus-visible:ring-2 focus-visible:ring-filters-link/60 [&[aria-expanded='true']>span:last-child]:motion-safe:rotate-180"
			x-ref="btnSort"
			aria-haspopup="dialog"
			:aria-expanded="sortOpen ? 'true' : 'false'"
			aria-controls="book-archive-dialog-sort"
			@click="openSort()">
			<span class="text-content [&>i]:flex [&>i]:size-4 [&>i]:items-center [&>i]:justify-center" aria-hidden="true"><?php dba_the_inline_icon( 'bx/bx-sort' ); ?></span>
			<span class="hidden md:inline-block min-w-0 flex-1 text-left leading-none"><?php esc_html_e( 'Sort', 'dynamic-book-archive' ); ?></span>
			<span class="hidden md:block shrink-0 text-content motion-safe:transition-transform [&>i]:flex [&>i]:size-4 [&>i]:items-center [&>i]:justify-center" aria-hidden="true"><?php dba_the_inline_icon( 'bx/bx-chevron-down', 'text-current' ); ?></span>
		</button>
		<button
			type="button"
			class="js-book-archive-open-filter inline-flex md:min-h-10 md:min-w-30 shrink-0 cursor-pointer items-center gap-2.5 rounded-xl bg-primary bg-filters-background py-2 pl-3 pr-2.5 font-display text-sm text-brand shadow-main transition hover:bg-book-secondary/10 hover:shadow-bronze-glow duration-200 ease-out focus:outline-none focus-visible:ring-2 focus-visible:ring-filters-link/60 [&[aria-expanded='true']>span:last-child]:motion-safe:rotate-180"
			x-ref="btnFilter"
			aria-haspopup="dialog"
			:aria-expanded="filterOpen ? 'true' : 'false'"
			aria-controls="book-archive-dialog-filter"
			@click="openFilter()">
			<span class="text-content [&>i]:flex [&>i]:size-4 [&>i]:items-center [&>i]:justify-center" aria-hidden="true"><?php dba_the_inline_icon( 'bx/bx-slider', 'text-current' ); ?></span>
			<span class="hidden md:inline-block min-w-0 flex-1 text-left leading-none"><?php esc_html_e( 'Filter', 'dynamic-book-archive' ); ?></span>
			<span class="hidden md:block shrink-0 text-content motion-safe:transition-transform [&>i]:flex [&>i]:size-4 [&>i]:items-center [&>i]:justify-center" aria-hidden="true"><?php dba_the_inline_icon( 'bx/bx-chevron-down', 'text-current' ); ?></span>
		</button>

		<div class="relative w-full min-w-48 max-w-md sm:w-auto sm:max-w-xs md:max-w-xs group-aria-busy:pointer-events-none">
			<label class="sr-only" for="book-archive-toolbar-search"><?php esc_html_e( 'Search books', 'dynamic-book-archive' ); ?></label>
			<input
				type="text"
				inputmode="search"
				enterkeyhint="search"
				id="book-archive-toolbar-search"
				x-ref="searchInput"
				x-model="searchQuery"
				@keydown.enter.prevent="submitSearch()"
				class="js-book-archive-search search-cancel-themed w-full rounded-full shadow-main bg-filters-background py-2 pl-3 pr-10 text-sm text-brand placeholder:text-brand/50 focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-filters-link"
				placeholder="<?php echo esc_attr( __( 'Search books…', 'dynamic-book-archive' ) ); ?>"
				autocomplete="off" />
			<button
				type="button"
				class="pointer-events-auto absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer items-center justify-center border-0 bg-transparent p-0 text-content focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-filters-link"
				aria-label="<?php echo esc_attr( __( 'Search books', 'dynamic-book-archive' ) ); ?>"
				@click="submitSearch()">
				<?php dba_the_inline_icon( 'search-icon', 'h-4 w-4' ); ?>
			</button>
		</div>
	</div>

	<div class="js-book-archive-real-controls pointer-events-none fixed left-0 top-0 -z-50 h-px w-px overflow-hidden opacity-0" aria-hidden="true">
		<?php
		get_template_part(
			'template-parts/ui/select',
			null,
			array(
				'id'            => 'book-archive-toolbar-sort',
				'class'         => 'js-book-archive-sort',
				'label'         => __( 'Sort books', 'dynamic-book-archive' ),
				'label_sr_only' => true,
				'tabindex'      => '-1',
				'options'       => $sort_options,
				'x_ref'         => 'realSort',
			)
		);

		get_template_part(
			'template-parts/ui/select',
			null,
			array(
				'id'            => 'book-archive-toolbar-author',
				'class'         => 'js-book-archive-author',
				'label'         => __( 'Filter by author', 'dynamic-book-archive' ),
				'label_sr_only' => true,
				'tabindex'      => '-1',
				'options'       => $author_options,
				'x_ref'         => 'realAuthor',
			)
		);

		get_template_part(
			'template-parts/ui/select',
			null,
			array(
				'id'            => 'book-archive-toolbar-tag',
				'class'         => 'js-book-archive-tag',
				'label'         => __( 'Filter by tag', 'dynamic-book-archive' ),
				'label_sr_only' => true,
				'tabindex'      => '-1',
				'options'       => $tag_options,
				'x_ref'         => 'realTag',
			)
		);

		get_template_part(
			'template-parts/ui/year-range',
			null,
			array(
				'variant'           => 'real',
				'year_floor'        => $year_floor,
				'year_ceiling'      => $year_ceiling,
				'year_value_min'    => isset( $args['year_value_min'] ) ? (int) $args['year_value_min'] : 0,
				'year_value_max'    => isset( $args['year_value_max'] ) ? (int) $args['year_value_max'] : 0,
				'label_any'         => __( 'Any', 'dynamic-book-archive' ),
				'input_year_min_id' => 'book-archive-toolbar-year-min',
				'input_year_max_id' => 'book-archive-toolbar-year-max',
				'input_min_class'   => 'js-book-archive-year-min',
				'input_max_class'   => 'js-book-archive-year-max',
			)
		);
		?>
	</div>
</div>

