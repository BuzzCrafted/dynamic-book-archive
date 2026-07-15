<?php
/**
 * Global template tag wrappers for templates and child themes.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

if ( ! defined( 'DBA_BOOK_CATEGORY_TAXONOMY' ) ) {
	define( 'DBA_BOOK_CATEGORY_TAXONOMY', 'book_category' );
}

use DBA\Components\Component_Renderer;
use DBA\Components\Component_Slot_Stack;
use DBA\Domain\Books\Book_Archive_Filters_Repository;
use DBA\Domain\Books\Book_Media_Repository;
use DBA\TemplateTags\Book_Archive_Category_Nav;
use DBA\TemplateTags\Book_Archive_Intro;
use DBA\TemplateTags\Book_Archive_Pagination;
use DBA\TemplateTags\Breadcrumb_Presenter;
use DBA\TemplateTags\Breadcrumb_Trail;
use DBA\TemplateTags\Entry_Template_Tags;

if ( ! function_exists( 'dba_posted_on' ) ) :
	/**
	 * Prints HTML with meta information for the current post-date/time.
	 */
	function dba_posted_on(): void {
		Entry_Template_Tags::posted_on();
	}
endif;

if ( ! function_exists( 'dba_posted_by' ) ) :
	/**
	 * Prints HTML with meta information for the current author.
	 */
	function dba_posted_by(): void {
		Entry_Template_Tags::posted_by();
	}
endif;

if ( ! function_exists( 'dba_entry_footer' ) ) :
	/**
	 * Prints HTML with meta information for categories, tags and comments.
	 */
	function dba_entry_footer(): void {
		Entry_Template_Tags::entry_footer();
	}
endif;

if ( ! function_exists( 'dba_post_thumbnail' ) ) :
	/**
	 * Displays an optional post thumbnail.
	 */
	function dba_post_thumbnail(): void {
		Entry_Template_Tags::post_thumbnail();
	}
endif;

if ( ! function_exists( 'dba_finalize_breadcrumb_items' ) ) :
	/**
	 * Applies pagination to the last crumb URL and exposes items for filtering.
	 *
	 * @param array<int, array{label: string, url: string}> $items Breadcrumb items.
	 * @return array<int, array{label: string, url: string}>
	 */
	function dba_finalize_breadcrumb_items( array $items ): array {
		return Breadcrumb_Trail::finalize_items( $items );
	}
endif;

if ( ! function_exists( 'dba_get_breadcrumb_label_from_primary_menu_for_archive' ) ) :
	/**
	 * Breadcrumb label for a post type archive: use the same title as the active primary nav item.
	 *
	 * @param WP_Post_Type $pto Post type object for the current archive.
	 */
	function dba_get_breadcrumb_label_from_primary_menu_for_archive( WP_Post_Type $pto ): string {
		return Breadcrumb_Trail::label_from_primary_menu_for_archive( $pto );
	}
endif;

if ( ! function_exists( 'dba_get_breadcrumb_items' ) ) :
	/**
	 * Builds the breadcrumb trail (label + URL for each segment; last segment is the current page).
	 *
	 * @return array<int, array{label: string, url: string}>
	 */
	function dba_get_breadcrumb_items(): array {
		return Breadcrumb_Trail::get_items();
	}
endif;

if ( ! function_exists( 'dba_breadcrumbs' ) ) :
	/**
	 * Prints an accessible breadcrumb trail (skipped on the non-paged front page).
	 *
	 * Hooks: `dba_show_breadcrumbs`, `dba_breadcrumb_items` (see dba_get_breadcrumb_items()).
	 */
	function dba_breadcrumbs(): void {
		Breadcrumb_Presenter::render();
	}
endif;

if ( ! function_exists( 'dba_print_breadcrumb_schema' ) ) :
	/**
	 * Outputs BreadcrumbList JSON-LD for supported views (not 404).
	 */
	function dba_print_breadcrumb_schema(): void {
		Breadcrumb_Presenter::print_schema();
	}
endif;

if ( ! function_exists( 'dba_resolve_book_category_term_from_book_cat_query_var' ) ) :
	/**
	 * Resolves the `book_cat` public query var to a `book_category` term (slug or hierarchical path).
	 *
	 * @param string $raw Raw value from {@see get_query_var()} `book_cat`.
	 */
	function dba_resolve_book_category_term_from_book_cat_query_var( string $raw ): ?WP_Term {
		if ( function_exists( 'books_cpt_resolve_book_category_term_from_book_cat_path' ) ) {
			$term = call_user_func( 'books_cpt_resolve_book_category_term_from_book_cat_path', $raw );
			return $term instanceof WP_Term ? $term : null;
		}

		$raw = trim( rawurldecode( $raw ), '/' );
		if ( '' === $raw ) {
			return null;
		}

		$parts     = array_values( array_filter( explode( '/', $raw ) ) );
		$leaf_slug = (string) end( $parts );
		if ( '' === $leaf_slug ) {
			return null;
		}

		if ( ! taxonomy_exists( DBA_BOOK_CATEGORY_TAXONOMY ) ) {
			return null;
		}

		$term = get_term_by( 'slug', $leaf_slug, DBA_BOOK_CATEGORY_TAXONOMY );
		return $term instanceof WP_Term ? $term : null;
	}
endif;

if ( ! function_exists( 'dba_get_book_archive_filtered_category' ) ) :
	/**
	 * Active book category on the Library: taxonomy archive ({@see get_term_link()}) or transient `book_cat` before redirect to the plain archive.
	 *
	 * @return WP_Term|null
	 */
	function dba_get_book_archive_filtered_category(): ?WP_Term {
		if ( is_tax( DBA_BOOK_CATEGORY_TAXONOMY ) ) {
			$obj = get_queried_object();
			return $obj instanceof WP_Term ? $obj : null;
		}

		if ( ! is_post_type_archive( 'book' ) ) {
			return null;
		}

		$raw = get_query_var( 'book_cat' );
		if ( ( ! is_string( $raw ) || '' === $raw ) && isset( $_GET['book_cat'] ) && is_string( $_GET['book_cat'] ) ) {
			$raw = sanitize_text_field( wp_unslash( (string) $_GET['book_cat'] ) );
		}

		if ( ! is_string( $raw ) || '' === $raw ) {
			return null;
		}

		return dba_resolve_book_category_term_from_book_cat_query_var( $raw );
	}
endif;

if ( ! function_exists( 'dba_get_book_post_type_archive_url' ) ) :
	/**
	 * Post type archive URL for `book` without permalink rewrite placeholders (CPT uses `book/%book_category%` slug).
	 */
	function dba_get_book_post_type_archive_url(): string {
		$raw = get_post_type_archive_link( 'book' );
		if ( ! is_string( $raw ) || '' === $raw ) {
			return home_url( '/' );
		}

		$normalized = str_replace( '%book_category%', '', $raw );
		$normalized = (string) preg_replace( '#([^:])//+#', '$1/', $normalized );
		$normalized = user_trailingslashit( untrailingslashit( $normalized ) );

		return $normalized;
	}
endif;

if ( ! function_exists( 'dba_get_book_category_book_cat_path' ) ) :
	/**
	 * Slug path for the `book_cat` query var (ancestor slugs + term slug).
	 */
	function dba_get_book_category_book_cat_path( WP_Term $term ): string {
		$slugs = array( $term->slug );
		$t     = $term;
		while ( $t->parent > 0 ) {
			$parent = get_term( $t->parent, $term->taxonomy );
			if ( ! $parent instanceof WP_Term || is_wp_error( $parent ) ) {
				break;
			}
			array_unshift( $slugs, $parent->slug );
			$t = $parent;
		}

		return implode( '/', $slugs );
	}
endif;

if ( ! function_exists( 'dba_get_book_post_type_archive_filter_url' ) ) :
	/**
	 * Public URL for the Library with an optional category filter.
	 *
	 * “All books” uses the `book` post type archive (`/book/` with pretty permalinks). A category uses
	 * the taxonomy term permalink ({@see get_term_link()}) so the shape follows the site’s permalink
	 * structure (e.g. `/book-category/martial-art/`).
	 *
	 * @param WP_Term|null $term  Active `book_category` term, or null for “All”.
	 * @param int          $paged Page number (min 1).
	 */
	function dba_get_book_post_type_archive_filter_url( ?WP_Term $term, int $paged = 1 ): string {
		$paged = max( 1, $paged );

		if ( $term instanceof WP_Term && isset( $term->taxonomy ) && DBA_BOOK_CATEGORY_TAXONOMY === $term->taxonomy ) {
			return dba_get_book_archive_category_filter_url( $term, $paged );
		}

		$base = dba_get_book_post_type_archive_url();
		if ( ! is_string( $base ) || '' === $base ) {
			return home_url( '/' );
		}

		$archive_root = user_trailingslashit( untrailingslashit( $base ) );

		if ( $paged > 1 ) {
			return $archive_root . user_trailingslashit( 'page/' . (string) $paged );
		}

		return $archive_root;
	}
endif;

if ( ! function_exists( 'dba_get_book_archive_filtered_category_id' ) ) :
	/**
	 * Term ID for the active book-archive category filter, or 0 for “All”.
	 */
	function dba_get_book_archive_filtered_category_id(): int {
		$term = dba_get_book_archive_filtered_category();
		return $term instanceof WP_Term ? (int) $term->term_id : 0;
	}
endif;

if ( ! function_exists( 'dba_get_book_archive_category_filter_url' ) ) :
	/**
	 * Canonical book category archive URL ({@see get_term_link()}).
	 */
	function dba_get_book_archive_category_filter_url( WP_Term $term, int $paged = 1 ): string {
		$url = get_term_link( $term );
		if ( is_wp_error( $url ) || ! is_string( $url ) ) {
			return home_url( '/' );
		}

		$paged = max( 1, $paged );
		if ( $paged > 1 ) {
			$url = add_query_arg( 'paged', $paged, $url );
		}

		return $url;
	}
endif;

if ( ! function_exists( 'dba_get_book_archive_distinct_publication_years' ) ) :
	/**
	 * Distinct publication years from published books (publication_date meta), newest first.
	 *
	 * Cached 12 hours.
	 *
	 * @return array<int, int>
	 */
	function dba_get_book_archive_distinct_publication_years(): array {
		return Book_Archive_Filters_Repository::get_distinct_publication_years();
	}
endif;

if ( ! function_exists( 'dba_get_book_archive_distinct_authors' ) ) :
	/**
	 * Distinct `book_author` meta values from published books, A→Z.
	 *
	 * Cached 12 hours.
	 *
	 * @return array<int, string>
	 */
	function dba_get_book_archive_distinct_authors(): array {
		return Book_Archive_Filters_Repository::get_distinct_authors();
	}
endif;

if ( ! function_exists( 'dba_get_book_archive_distinct_tags' ) ) :
	/**
	 * Distinct `post_tag` terms used by published `book` posts.
	 *
	 * Cached 12 hours.
	 *
	 * @return array<int, array{term_id: int, slug: string, name: string}>
	 */
	function dba_get_book_archive_distinct_tags(): array {
		return Book_Archive_Filters_Repository::get_distinct_tags();
	}
endif;

if ( ! function_exists( 'dba_get_book_archive_tag_filter_url' ) ) :
	/**
	 * Public URL for the Library filtered by a `post_tag` term.
	 *
	 * Produces `/book/?tag={slug}` (or `/book/page/N/?tag={slug}` for paged views).
	 *
	 * @param WP_Term $term  The `post_tag` term to filter by.
	 * @param int     $paged Page number (min 1).
	 */
	function dba_get_book_archive_tag_filter_url( WP_Term $term, int $paged = 1 ): string {
		$paged = max( 1, $paged );
		$base  = dba_get_book_post_type_archive_url();
		if ( ! is_string( $base ) || '' === $base ) {
			return home_url( '/' );
		}

		$archive_root = user_trailingslashit( untrailingslashit( $base ) );

		if ( $paged > 1 ) {
			$url = $archive_root . user_trailingslashit( 'page/' . (string) $paged );
		} else {
			$url = $archive_root;
		}

		return add_query_arg( 'tag', $term->slug, $url );
	}
endif;

if ( ! function_exists( 'dba_the_book_archive_intro' ) ) :
	/**
	 * Book archive intro (default tagline or category description): {@see template-parts/book/archive/intro.php}.
	 *
	 * @param array<string, mixed> $template_part_args Optional args (e.g. `page_header_embed`, `intro_wrapper_class`).
	 */
	function dba_the_book_archive_intro( array $template_part_args = [] ): void {
		Book_Archive_Intro::render( $template_part_args );
	}
endif;

if ( ! function_exists( 'dba_the_book_archive_category_nav' ) ) :
	/**
	 * Category filter links for the book archive: loads {@see template-parts/book/archive/category-nav.php}.
	 */
	function dba_the_book_archive_category_nav(): void {
		Book_Archive_Category_Nav::render();
	}
endif;

if ( ! function_exists( 'dba_get_book_gallery_image_ids' ) ) :
	/**
	 * Image attachment IDs for a book: ordered `book_gallery` meta (books-cpt 1.3.0+); falls back to the featured image when the meta is empty.
	 *
	 * @return array<int, int>
	 */
	function dba_get_book_gallery_image_ids( int $post_id ): array {
		return Book_Media_Repository::get_gallery_image_ids( $post_id );
	}
endif;

if ( ! function_exists( 'dba_get_book_archive_paginate_links_args' ) ) :
	/**
	 * Arguments for {@see paginate_links()} on the book archive (SSR or REST-shaped URLs).
	 *
	 * When there are more than five pages, uses a tighter numeric window (more ellipsis).
	 *
	 * @param int         $total   Total page count (>= 1).
	 * @param int         $current Current page (>= 1).
	 * @param string|null $base    Optional `base` for paginate_links (REST archive URL + %_%).
	 * @param string|null $format  Optional `format` (e.g. page/%#%/) when `base` is set.
	 * @param array<string, string>|null $add_args Optional query args appended to each page link (e.g. book_search).
	 * @return array<string, mixed>
	 */
	function dba_get_book_archive_paginate_links_args( int $total, int $current, ?string $base = null, ?string $format = null, ?array $add_args = null ): array {
		$total   = max( 1, $total );
		$current = max( 1, $current );
		$compact = $total > 5;

		$args = array(
			'total'     => $total,
			'current'   => $current,
			'type'      => 'array',
			'mid_size'  => $compact ? 1 : 2,
			'end_size'  => 1,
			'prev_text' => __( '< Previous', 'dynamic-book-archive' ),
			'next_text' => __( 'Next >', 'dynamic-book-archive' ),
		);

		if ( is_string( $base ) && '' !== $base && is_string( $format ) && '' !== $format ) {
			$args['base']   = $base;
			$args['format'] = $format;
		}

		if ( is_array( $add_args ) && array() !== $add_args ) {
			$args['add_args'] = $add_args;
		}

		return $args;
	}
endif;

if ( ! function_exists( 'dba_the_book_pagination' ) ) :
	/**
	 * Pagination for the book archive: builds link data and loads {@see template-parts/book/archive/pagination.php}.
	 */
	function dba_the_book_pagination(): void {
		Book_Archive_Pagination::render();
	}
endif;

if ( ! function_exists( 'dba_get_inline_icon' ) ) :
	/**
	 * Returns an inline SVG icon from `/assets/images/icons/` wrapped in an `<i>` element.
	 *
	 * Supports icons in subfolders via a `/`-separated path (e.g. `bx/bx-book`).
	 * Returns an empty string if the icon is missing or the path is invalid.
	 *
	 * @param string $name  Icon path (without `.svg`) relative to `/assets/images/icons/`, e.g. `search-icon` or `bx/bx-book`.
	 * @param string $class CSS classes applied to the wrapping `<i>`.
	 */
	function dba_get_inline_icon( string $name, string $class = '' ): string {
		$segments = array();
		foreach ( explode( '/', trim( $name, '/' ) ) as $segment ) {
			if ( '' === $segment || '.' === $segment || '..' === $segment ) {
				return '';
			}
			$clean = sanitize_file_name( $segment );
			if ( '' === $clean ) {
				return '';
			}
			$segments[] = $clean;
		}

		if ( empty( $segments ) ) {
			return '';
		}

		$base = trailingslashit( get_template_directory() ) . 'assets/images/icons/';
		$path = $base . implode( '/', $segments ) . '.svg';

		$real_path = realpath( $path );
		$real_base = realpath( $base );
		if ( false === $real_path || false === $real_base || 0 !== strpos( $real_path, $real_base . DIRECTORY_SEPARATOR ) ) {
			return '';
		}

		if ( ! is_readable( $real_path ) ) {
			return '';
		}

		$svg = file_get_contents( $real_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme asset.
		if ( false === $svg || '' === $svg ) {
			return '';
		}

		return sprintf( '<i class="%s">%s</i>', esc_attr( $class ), $svg );
	}
endif;

if ( ! function_exists( 'dba_get_icon_svg_raw' ) ) :
	/**
	 * Raw SVG markup from `/assets/images/icons/` (no wrapper element).
	 *
	 * @param string $name Icon path (without `.svg`) relative to `/assets/images/icons/`.
	 */
	function dba_get_icon_svg_raw( string $name ): string {
		$wrapped = dba_get_inline_icon( $name );
		if ( '' === $wrapped ) {
			return '';
		}

		if ( preg_match( '/<svg\b[^>]*>.*?<\/svg>/is', $wrapped, $matches ) ) {
			return $matches[0];
		}

		return '';
	}
endif;

if ( ! function_exists( 'dba_the_inline_icon' ) ) :
	/**
	 * Outputs an inline SVG icon from `/assets/images/icons/` wrapped in an `<i>` element.
	 *
	 * Thin `the_`-style wrapper around {@see dba_get_inline_icon()}.
	 *
	 * @param string $name  Icon path (without `.svg`) relative to `/assets/images/icons/`, e.g. `search-icon` or `bx/bx-book`.
	 * @param string $class CSS classes applied to the wrapping `<i>`.
	 */
	function dba_the_inline_icon( string $name, string $class = '' ): void {
		echo dba_get_inline_icon( $name, $class ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted theme-bundled SVG markup; class is escaped in the getter.
	}
endif;

if ( ! function_exists( 'dba_format_publication_date_label' ) ) :
	/**
	 * Formats a calendar publication date for display (no timezone shift).
	 *
	 * Returns only the year for YYYY-only strings; otherwise uses the site date format.
	 * Returns an empty string when the input is empty or unparseable.
	 *
	 * @param string $raw ISO date string (e.g. `1960` or `1960-03-15`).
	 */
	function dba_format_publication_date_label( string $raw ): string {
		$raw = trim( $raw );
		if ( '' === $raw ) {
			return '';
		}
		if ( (bool) preg_match( '/^\d{4}$/', $raw ) ) {
			return $raw;
		}
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $raw ) ) {
			return '';
		}
		$dt = \DateTimeImmutable::createFromFormat( '!Y-m-d', $raw, new \DateTimeZone( 'UTC' ) );
		if ( false === $dt ) {
			return '';
		}
		return wp_date( (string) get_option( 'date_format' ), $dt->getTimestamp(), new \DateTimeZone( 'UTC' ) );
	}
endif;

if ( ! function_exists( 'dba_format_archive_publication_date_label' ) ) :
	/**
	 * Formats an ISO publication date for display.
	 *
	 * Returns only the year for YYYY-only strings; otherwise uses the site date format.
	 * Returns an empty string when the input is empty or unparseable.
	 *
	 * @param string $iso_date ISO date string (e.g. `1960` or `1960-03-15`).
	 */
	function dba_format_archive_publication_date_label( string $iso_date ): string {
		return dba_format_publication_date_label( $iso_date );
	}
endif;

if ( ! function_exists( 'dba_format_archive_publication_line' ) ) :
	/**
	 * Combined "Publication / Year" label for document header display.
	 *
	 * Examples: "Asahi Roots / 1960", "1960" (when publication name is empty), "Asahi Roots" (when date is empty).
	 *
	 * @param string $publication Free-text publication name.
	 * @param string $iso_date    ISO date string passed to {@see dba_format_archive_publication_date_label()}.
	 */
	function dba_format_archive_publication_line( string $publication, string $iso_date ): string {
		$publication = trim( $publication );
		$date_label  = dba_format_archive_publication_date_label( $iso_date );
		if ( '' === $publication && '' === $date_label ) {
			return '';
		}
		if ( '' === $publication ) {
			return $date_label;
		}
		if ( '' === $date_label ) {
			return $publication;
		}
		return $publication . ' / ' . $date_label;
	}
endif;

if ( ! function_exists( 'dba_get_carousel_nav_button_classes' ) ) :
	/**
	 * Returns the full Tailwind class string for a carousel nav button.
	 *
	 * Centralising defaults here means every caller inherits changes automatically.
	 *
	 * @param string $position_class Side offset class, e.g. 'left-1' or 'right-1'.
	 * @param string $size_class     Hit-target classes, e.g. 'h-11 w-11'.
	 * @param string $extra_class    Optional additional classes merged onto the defaults.
	 */
	/**
	 * @param string $position_class Side offset class (used in 'overlay' variant only), e.g. 'left-1'.
	 * @param string $size_class     Hit-target classes, e.g. 'h-11 w-11'.
	 * @param string $extra_class    Optional extra classes merged onto the result.
	 * @param string $variant        'overlay' (default) — absolute positioned over the container;
	 *                               'inline'  — normal flow flex/grid sibling, no absolute positioning.
	 */
	function dba_get_carousel_nav_button_classes( string $position_class, string $size_class, string $extra_class = '', string $variant = 'overlay' ): string {
		$visual = 'flex items-center justify-center rounded-full shadow-main bg-canvas/50 text-content backdrop-blur-sm transition hover:bg-canvas hover:shadow-bronze-glow hover:text-brand-muted disabled:pointer-events-none disabled:opacity-30';

		if ( 'inline' === $variant ) {
			$parts = array( $visual, 'flex-shrink-0', $size_class, $extra_class );
		} else {
			$parts = array( $visual, 'absolute top-1/2 z-10 -translate-y-1/2', $position_class, $size_class, $extra_class );
		}

		return implode( ' ', array_filter( $parts ) );
	}
endif;

if ( ! function_exists( 'dba_get_carousel_nav_button' ) ) :
	/**
	 * Returns the HTML for a single carousel nav button.
	 *
	 * Buffers {@see template-parts/ui/carousel-nav-button.php}.
	 * See that file for the full `$args` reference.
	 *
	 * @param array<string, mixed> $args Template args.
	 */
	function dba_get_carousel_nav_button( array $args = [] ): string {
		ob_start();
		get_template_part( 'template-parts/ui/carousel-nav-button', null, $args );
		return (string) ob_get_clean();
	}
endif;

if ( ! function_exists( 'dba_the_carousel_nav_button' ) ) :
	/**
	 * Outputs a single carousel nav button.
	 *
	 * Thin `the_`-style wrapper around {@see template-parts/ui/carousel-nav-button.php}.
	 * See that file for the full `$args` reference.
	 *
	 * @param array<string, mixed> $args Template args.
	 */
	function dba_the_carousel_nav_button( array $args = [] ): void {
		get_template_part( 'template-parts/ui/carousel-nav-button', null, $args );
	}
endif;

if ( ! function_exists( 'dba_the_carousel_nav_pair' ) ) :
	/**
	 * Outputs a prev + next carousel nav button pair.
	 *
	 * Callers pass shared config once; per-direction overrides go in
	 * `alpine_prev` / `alpine_next`, `aria_label_prev` / `aria_label_next`,
	 * and `data_attr_prev` / `data_attr_next`. Any key valid for
	 * {@see dba_the_carousel_nav_button()} and prefixed or shared is accepted.
	 *
	 * @param array<string, mixed> $args {
	 *   @type string $aria_controls      Shared aria-controls id for both buttons.
	 *   @type string $aria_label_prev    SR label for the prev button.
	 *   @type string $aria_label_next    SR label for the next button.
	 *   @type array  $alpine_prev        Alpine/x-bind attrs for the prev button.
	 *   @type array  $alpine_next        Alpine/x-bind attrs for the next button.
	 *   @type string $data_attr_prev     Boolean data attribute name for prev.
	 *   @type string $data_attr_next     Boolean data attribute name for next.
	 *   @type string $class              Extra classes applied to both buttons.
	 *   @type string $position_class_prev Override left position class for prev.
	 *   @type string $position_class_next Override right position class for next.
	 *   @type string $size_class         Hit-target override applied to both.
	 *   @type string $icon               Icon name override applied to both.
	 *   @type string $icon_class         Icon wrapper classes applied to both.
	 *   @type array  $attrs              Extra static HTML attributes for both.
	 * }
	 */
	function dba_the_carousel_nav_pair( array $args = [] ): void {
		$aria_controls       = isset( $args['aria_controls'] ) && is_string( $args['aria_controls'] ) ? $args['aria_controls'] : '';
		$aria_label_prev     = isset( $args['aria_label_prev'] ) && is_string( $args['aria_label_prev'] ) ? $args['aria_label_prev'] : '';
		$aria_label_next     = isset( $args['aria_label_next'] ) && is_string( $args['aria_label_next'] ) ? $args['aria_label_next'] : '';
		$alpine_prev         = isset( $args['alpine_prev'] ) && is_array( $args['alpine_prev'] ) ? $args['alpine_prev'] : array();
		$alpine_next         = isset( $args['alpine_next'] ) && is_array( $args['alpine_next'] ) ? $args['alpine_next'] : array();
		$data_attr_prev      = isset( $args['data_attr_prev'] ) && is_string( $args['data_attr_prev'] ) ? $args['data_attr_prev'] : '';
		$data_attr_next      = isset( $args['data_attr_next'] ) && is_string( $args['data_attr_next'] ) ? $args['data_attr_next'] : '';
		$position_class_prev = isset( $args['position_class_prev'] ) && is_string( $args['position_class_prev'] ) ? $args['position_class_prev'] : '';
		$position_class_next = isset( $args['position_class_next'] ) && is_string( $args['position_class_next'] ) ? $args['position_class_next'] : '';

		$class_prev = isset( $args['class_prev'] ) && is_string( $args['class_prev'] ) ? $args['class_prev'] : '';
		$class_next = isset( $args['class_next'] ) && is_string( $args['class_next'] ) ? $args['class_next'] : '';

		// Shared keys forwarded verbatim to both buttons.
		$shared_keys = array( 'class', 'variant', 'size_class', 'icon', 'icon_class', 'attrs' );
		$shared      = array();
		foreach ( $shared_keys as $key ) {
			if ( isset( $args[ $key ] ) ) {
				$shared[ $key ] = $args[ $key ];
			}
		}

		if ( '' !== $aria_controls ) {
			$shared['aria_controls'] = $aria_controls;
		}

		$prev_args = array_merge( $shared, array( 'direction' => 'prev' ) );
		if ( '' !== $aria_label_prev )     { $prev_args['aria_label']     = $aria_label_prev; }
		if ( '' !== $data_attr_prev )      { $prev_args['data_attr']      = $data_attr_prev; }
		if ( ! empty( $alpine_prev ) )     { $prev_args['alpine']         = $alpine_prev; }
		if ( '' !== $position_class_prev ) { $prev_args['position_class'] = $position_class_prev; }
		if ( '' !== $class_prev ) {
			$prev_args['class'] = trim( ( isset( $shared['class'] ) ? $shared['class'] . ' ' : '' ) . $class_prev );
		}

		$next_args = array_merge( $shared, array( 'direction' => 'next' ) );
		if ( '' !== $aria_label_next )     { $next_args['aria_label']     = $aria_label_next; }
		if ( '' !== $data_attr_next )      { $next_args['data_attr']      = $data_attr_next; }
		if ( ! empty( $alpine_next ) )     { $next_args['alpine']         = $alpine_next; }
		if ( '' !== $position_class_next ) { $next_args['position_class'] = $position_class_next; }
		if ( '' !== $class_next ) {
			$next_args['class'] = trim( ( isset( $shared['class'] ) ? $shared['class'] . ' ' : '' ) . $class_next );
		}

		dba_the_carousel_nav_button( $prev_args );
		dba_the_carousel_nav_button( $next_args );
	}
endif;

if ( ! function_exists( 'dba_the_carousel_image_stage' ) ) :
	/**
	 * Outputs a shrink-wrapped image container with optional overlaid nav buttons.
	 *
	 * Thin wrapper around {@see template-parts/ui/carousel-image-stage.php}.
	 * See that file for the full `$args` reference.
	 *
	 * @param array<string, mixed> $args Template args.
	 */
	function dba_the_carousel_image_stage( array $args = [] ): void {
		get_template_part( 'template-parts/ui/carousel-image-stage', null, $args );
	}
endif;

if ( ! function_exists( 'dba_the_site_logo' ) ) :
	/**
	 * Prints the custom logo: home link wrapping the logo image.
	 *
	 * @param string $img_class              CSS classes for the `<img>` element.
	 * @param bool   $use_alternative_logo   When true, uses the Customizer "Alternative logo" (footer); falls back to the main logo if unset.
	 */
	function dba_the_site_logo( string $img_class = 'w-full h-auto object-contain', bool $use_alternative_logo = false ): void {
		$logo_id = 0;

		if ( $use_alternative_logo ) {
			$alt = get_theme_mod( 'dba_alternative_logo', 0 );
			if ( is_numeric( $alt ) && (int) $alt > 0 ) {
				$logo_id = (int) $alt;
			}
			if ( $logo_id <= 0 ) {
				$primary = get_theme_mod( 'custom_logo', 0 );
				$logo_id = ( is_numeric( $primary ) && (int) $primary > 0 ) ? (int) $primary : 0;
			}
		} else {
			$primary = get_theme_mod( 'custom_logo' );
			$logo_id = ( is_numeric( $primary ) && (int) $primary > 0 ) ? (int) $primary : 0;
		}

		if ( $logo_id <= 0 ) {
			return;
		}

		$image_url = wp_get_attachment_image_url( $logo_id, 'full' );
		if ( false === $image_url || '' === $image_url ) {
			return;
		}

		printf(
			'<a class="no-underline text-inherit hover:no-underline" href="%1$s" rel="home"><img src="%2$s" alt="%3$s" class="%4$s"></a>',
			esc_url( home_url( '/' ) ),
			esc_url( $image_url ),
			esc_attr( get_bloginfo( 'name', 'display' ) ),
			esc_attr( $img_class )
		);
	}
endif;

if ( ! function_exists( 'dba_component' ) ) :
	/**
	 * Renders a component by name (Laravel-style).
	 *
	 * The name is `{type}.{name}` (or `{type}/{name}`), where type is `ui` or
	 * `container`. The matching controller under `DBA\Components\{Type}` prepares
	 * the view model; the view lives at `template-parts/components/{type}/{name}.php`.
	 * Controllers are optional: a view-only component renders with raw params.
	 *
	 * @param string               $name   Component name, e.g. `ui.dl-row`.
	 * @param array<string, mixed> $params Params handed to the controller/view.
	 * @param string|null          $slot   Captured slot HTML (container components only).
	 */
	function dba_component( string $name, array $params = array(), ?string $slot = null ): void {
		Component_Renderer::render( $name, $params, $slot );
	}
endif;

if ( ! function_exists( 'dba_component_buffered' ) ) :
	/**
	 * Returns a rendered component as a string.
	 *
	 * @param string               $name   Component name, e.g. `ui.dl-row`.
	 * @param array<string, mixed> $params Params handed to the controller/view.
	 * @param string|null          $slot   Captured slot HTML (container components only).
	 */
	function dba_component_buffered( string $name, array $params = array(), ?string $slot = null ): string {
		return Component_Renderer::render_buffered( $name, $params, $slot );
	}
endif;

if ( ! function_exists( 'dba_component_open' ) ) :
	/**
	 * Opens a container component and begins capturing its slot markup.
	 *
	 * Pair with {@see dba_component_close()}. Markup echoed between the two calls
	 * becomes the component's slot. Container calls may be nested.
	 *
	 * @param string               $name   Container component name, e.g. `container.card`.
	 * @param array<string, mixed> $params Params handed to the controller/view.
	 */
	function dba_component_open( string $name, array $params = array() ): void {
		Component_Slot_Stack::open( $name, $params );
	}
endif;

if ( ! function_exists( 'dba_component_close' ) ) :
	/**
	 * Closes the most recently opened container component and renders it.
	 */
	function dba_component_close(): void {
		Component_Slot_Stack::close();
	}
endif;
