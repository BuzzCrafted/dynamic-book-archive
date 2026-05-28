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
