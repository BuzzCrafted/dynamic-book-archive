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
		add_filter( 'query_vars', array( self::class, 'register_book_cat_public_query_var' ) );
		add_action( 'pre_get_posts', array( self::class, 'set_book_archive_posts_per_page' ) );
		add_action( 'pre_get_posts', array( self::class, 'filter_book_archive_by_book_category' ) );
	}

	/**
	 * Ensure `book_cat` is recognized on the book post type archive (`?book_cat=…`).
	 *
	 * @param array<int, string> $vars Public query variables.
	 * @return array<int, string>
	 */
	public static function register_book_cat_public_query_var( array $vars ): array {
		if ( ! in_array( 'book_cat', $vars, true ) ) {
			$vars[] = 'book_cat';
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
}
