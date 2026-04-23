<?php
/**
 * Book archive category navigation (`book_category` taxonomy from books-cpt).
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\TemplateTags;

use WP_Term;

/**
 * Renders category pills for the book post type archive.
 */
final class Book_Archive_Category_Nav {

	/**
	 * Loads {@see template-parts/book/archive/category-nav.php} when on the book archive.
	 */
	public static function render(): void {
		if ( ! is_post_type_archive( 'book' ) && ! is_tax( \DBA_BOOK_CATEGORY_TAXONOMY ) ) {
			return;
		}

		if ( ! apply_filters( 'dba_show_book_archive_category_nav', true ) ) {
			return;
		}

		$archive_url = \dba_get_book_post_type_archive_url();
		if ( ! is_string( $archive_url ) || '' === $archive_url ) {
			return;
		}

		$exclude_uncat = array();
		$uncat_term    = get_term_by( 'slug', 'uncategorized', \DBA_BOOK_CATEGORY_TAXONOMY );
		if ( $uncat_term instanceof WP_Term ) {
			$exclude_uncat = array( (int) $uncat_term->term_id );
		}

		$defaults = array(
			'taxonomy'   => \DBA_BOOK_CATEGORY_TAXONOMY,
			'hide_empty' => true,
			'orderby'    => 'name',
			'order'      => 'ASC',
			'number'     => 0,
			'parent'     => 0,
			'exclude'    => $exclude_uncat,
		);

		/**
		 * Filters arguments passed to {@see get_terms()} for the book archive category nav.
		 *
		 * @param array<string, mixed> $defaults Term query arguments.
		 */
		$term_args = apply_filters( 'dba_book_archive_category_nav_terms_args', $defaults );

		$terms = get_terms( $term_args );

		if ( is_wp_error( $terms ) ) {
			return;
		}

		/** @var array<int, WP_Term> $terms */
		$terms = array_values( array_filter( (array) $terms, 'is_object' ) );

		$current_cat = \dba_get_book_archive_filtered_category_id();

		$items = array(
			array(
				'id'      => 0,
				'slug'    => '',
				'label'   => __( 'All', 'dynamic-book-archive' ),
				'url'     => \dba_get_book_post_type_archive_filter_url( null ),
				'current' => 0 === $current_cat,
			),
		);

		foreach ( $terms as $term ) {
			if ( ! $term instanceof WP_Term ) {
				continue;
			}
			$items[] = array(
				'id'      => (int) $term->term_id,
				'slug'    => (string) $term->slug,
				'label'   => $term->name,
				'url'     => \dba_get_book_post_type_archive_filter_url( $term ),
				'current' => $current_cat === (int) $term->term_id,
			);
		}

		/**
		 * Filters the list of category nav items (including "All") before markup is loaded.
		 *
		 * @param array<int, array{id: int, slug: string, label: string, url: string, current: bool}> $items Nav items.
		 * @param array<int, WP_Term>                                                                 $terms Category terms (empty if get_terms returned none).
		 */
		$items = apply_filters( 'dba_book_archive_category_nav_items', $items, $terms );

		if ( ! is_array( $items ) || array() === $items ) {
			return;
		}

		set_query_var( 'dba_book_category_nav_items', $items );
		get_template_part(
			'template-parts/book/archive/category-nav',
			null,
			array(
				'items' => $items,
			)
		);
	}
}
