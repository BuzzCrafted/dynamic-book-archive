<?php
/**
 * Main query tweaks for book archive.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\Theme;

use WP_Query;

/**
 * Book archive posts_per_page.
 */
final class Book_Archive_Query {

	public static function register_hooks(): void {
		add_filter( 'query_vars', array( self::class, 'register_public_query_vars' ) );
		add_action( 'pre_get_posts', array( self::class, 'set_book_archive_posts_per_page' ) );
		add_action( 'pre_get_posts', array( self::class, 'filter_book_archive_by_book_category' ) );
		add_action( 'pre_get_posts', array( self::class, 'filter_book_library_by_author_tag_and_year_range' ) );
	}

	/**
	 * Ensure public query vars are recognized (for progressive enhancement / no-JS filtering).
	 *
	 * @param array<int, string> $vars Public query variables.
	 * @return array<int, string>
	 */
	public static function register_public_query_vars( array $vars ): array {
		$add = array( 'book_cat', 'author', 'tag', 'year_min', 'year_max' );
		foreach ( $add as $v ) {
			if ( ! in_array( $v, $vars, true ) ) {
				$vars[] = $v;
			}
		}

		return $vars;
	}

	/**
	 * Book archive: 4×3 grid (12 books per page).
	 *
	 * @param WP_Query $query Main query.
	 */
	public static function set_book_archive_posts_per_page( WP_Query $query ): void {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}
		if ( $query->is_post_type_archive( 'book' ) ) {
			$query->set( 'posts_per_page', 12 );
		}
	}

	/**
	 * Book archive: constrain main query when `book_cat` is the active taxonomy filter.
	 *
	 * @param WP_Query $query Main query.
	 */
	public static function filter_book_archive_by_book_category( WP_Query $query ): void {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( ! $query->is_post_type_archive( 'book' ) ) {
			return;
		}

		$raw = get_query_var( 'book_cat' );
		if ( ( ! is_string( $raw ) || '' === $raw ) && isset( $_GET['book_cat'] ) && is_string( $_GET['book_cat'] ) ) {
			$raw = sanitize_text_field( wp_unslash( (string) $_GET['book_cat'] ) );
		}
		if ( ! is_string( $raw ) || '' === $raw ) {
			return;
		}

		if ( ! function_exists( 'dba_resolve_book_category_term_from_book_cat_query_var' ) ) {
			return;
		}

		$term = dba_resolve_book_category_term_from_book_cat_query_var( $raw );
		if ( ! $term instanceof \WP_Term ) {
			$query->set( 'post__in', array( 0 ) );
			return;
		}

		$query->set(
			'tax_query',
			array(
				array(
					'taxonomy'         => \DBA_BOOK_CATEGORY_TAXONOMY,
					'field'            => 'term_id',
					'terms'            => array( (int) $term->term_id ),
					'include_children' => true,
				),
			)
		);
	}

	/**
	 * Progressive enhancement: apply author/tag/year range filters to the main query when present in the URL.
	 *
	 * Supports both the `book` post type archive and `book_category` taxonomy archives.
	 */
	public static function filter_book_library_by_author_tag_and_year_range( WP_Query $query ): void {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		$is_library = $query->is_post_type_archive( 'book' );
		if ( ! $is_library && defined( 'DBA_BOOK_CATEGORY_TAXONOMY' ) ) {
			$is_library = $query->is_tax( \DBA_BOOK_CATEGORY_TAXONOMY );
		}
		if ( ! $is_library ) {
			return;
		}

		$author_raw = get_query_var( 'author' );
		if ( ( ! is_string( $author_raw ) || '' === $author_raw ) && isset( $_GET['author'] ) && is_string( $_GET['author'] ) ) {
			$author_raw = sanitize_text_field( wp_unslash( (string) $_GET['author'] ) );
		}
		$author = is_string( $author_raw ) ? trim( $author_raw ) : '';

		$tag_raw = get_query_var( 'tag' );
		if ( ( ! is_string( $tag_raw ) || '' === $tag_raw ) && isset( $_GET['tag'] ) && is_string( $_GET['tag'] ) ) {
			$tag_raw = sanitize_text_field( wp_unslash( (string) $_GET['tag'] ) );
		}
		$tag = is_string( $tag_raw ) ? trim( $tag_raw ) : '';

		$year_min_raw = get_query_var( 'year_min' );
		if ( ( ! is_string( $year_min_raw ) || '' === $year_min_raw ) && isset( $_GET['year_min'] ) && is_string( $_GET['year_min'] ) ) {
			$year_min_raw = sanitize_text_field( wp_unslash( (string) $_GET['year_min'] ) );
		}
		$year_max_raw = get_query_var( 'year_max' );
		if ( ( ! is_string( $year_max_raw ) || '' === $year_max_raw ) && isset( $_GET['year_max'] ) && is_string( $_GET['year_max'] ) ) {
			$year_max_raw = sanitize_text_field( wp_unslash( (string) $_GET['year_max'] ) );
		}

		$year_min = is_string( $year_min_raw ) ? (int) $year_min_raw : 0;
		$year_max = is_string( $year_max_raw ) ? (int) $year_max_raw : 0;

		$year_min = ( $year_min >= 1900 && $year_min <= 2100 ) ? $year_min : 0;
		$year_max = ( $year_max >= 1900 && $year_max <= 2100 ) ? $year_max : 0;

		if ( $year_min > 0 && $year_max > 0 && $year_min > $year_max ) {
			$tmp      = $year_min;
			$year_min = $year_max;
			$year_max = $tmp;
		}

		if ( '' !== $author ) {
			$meta_query   = (array) $query->get( 'meta_query', array() );
			$meta_query[] = array(
				'key'     => 'book_author',
				'value'   => $author,
				'compare' => '=',
			);
			$query->set( 'meta_query', $meta_query );
		}

		if ( $year_min > 0 || $year_max > 0 ) {
			$meta_query = (array) $query->get( 'meta_query', array() );
			if ( $year_min > 0 && $year_max > 0 ) {
				$meta_query[] = array(
					'key'     => 'publication_date',
					'value'   => array( sprintf( '%04d-01-01', $year_min ), sprintf( '%04d-12-31', $year_max ) ),
					'compare' => 'BETWEEN',
					'type'    => 'DATE',
				);
			} elseif ( $year_min > 0 ) {
				$meta_query[] = array(
					'key'     => 'publication_date',
					'value'   => sprintf( '%04d-01-01', $year_min ),
					'compare' => '>=',
					'type'    => 'DATE',
				);
			} else {
				$meta_query[] = array(
					'key'     => 'publication_date',
					'value'   => sprintf( '%04d-12-31', $year_max ),
					'compare' => '<=',
					'type'    => 'DATE',
				);
			}
			$query->set( 'meta_query', $meta_query );
		}

		if ( '' !== $tag && taxonomy_exists( 'post_tag' ) ) {
			$tax_query = (array) $query->get( 'tax_query', array() );
			$tax_query[] = array(
				'taxonomy' => 'post_tag',
				'field'    => ctype_digit( $tag ) ? 'term_id' : 'slug',
				'terms'    => array( ctype_digit( $tag ) ? (int) $tag : $tag ),
			);
			if ( count( $tax_query ) > 1 && ! isset( $tax_query['relation'] ) ) {
				$tax_query['relation'] = 'AND';
			}
			$query->set( 'tax_query', $tax_query );
		}
	}
}
