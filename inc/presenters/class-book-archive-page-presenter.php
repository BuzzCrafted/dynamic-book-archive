<?php
/**
 * Book archive page presenter.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\Presenters;

use DBA\Domain\Books\Book_Archive_Filters_Repository;
use WP_Query;
use WP_Term;

/**
 * Builds an args-only view model for the book archive template.
 */
final class Book_Archive_Page_Presenter {
	/**
	 * @param WP_Query|null $query Defaults to global main query.
	 * @return array<string, mixed>
	 */
	public static function build( ?WP_Query $query = null ): array {
		if ( null === $query ) {
			global $wp_query;
			$query = $wp_query instanceof WP_Query ? $wp_query : null;
		}

		$filter_term = function_exists( 'dba_get_book_archive_filtered_category' )
			? dba_get_book_archive_filtered_category()
			: null;

		$title = __( 'Library', 'dynamic-book-archive' );
		if ( $filter_term instanceof WP_Term ) {
			$title = sprintf(
				/* translators: 1: category name, 2: static label "Library". */
				__( '%1$s %2$s', 'dynamic-book-archive' ),
				$filter_term->name,
				$title
			);
		}

		$paged = (int) get_query_var( 'paged' );
		if ( $paged < 1 ) {
			$paged = (int) get_query_var( 'page' );
		}
		$paged = max( 1, $paged );

		$category_id   = function_exists( 'dba_get_book_archive_filtered_category_id' ) ? dba_get_book_archive_filtered_category_id() : 0;
		$category_slug = $filter_term instanceof WP_Term ? (string) $filter_term->slug : '';

		$years = Book_Archive_Filters_Repository::get_distinct_publication_years();
		$year_ceiling = (int) ( $years[0] ?? 0 );
		$year_floor   = (int) ( $years[ count( $years ) - 1 ] ?? 0 );
		if ( $year_floor <= 0 ) {
			$year_floor = 1900;
		}
		if ( $year_ceiling <= 0 ) {
			$year_ceiling = (int) gmdate( 'Y' );
		}
		if ( $year_floor > $year_ceiling ) {
			$tmp          = $year_floor;
			$year_floor   = $year_ceiling;
			$year_ceiling = $tmp;
		}

		$authors = Book_Archive_Filters_Repository::get_distinct_authors();
		$tags    = Book_Archive_Filters_Repository::get_distinct_tags();

		$items = array();
		if ( $query instanceof WP_Query && $query->have_posts() ) {
			foreach ( $query->posts as $p ) {
				$post_id = isset( $p->ID ) ? (int) $p->ID : 0;
				if ( $post_id > 0 ) {
					$items[] = Book_Card_Presenter::from_post_id( $post_id );
				}
			}
		}

		return array(
			'context' => array(
				'title'    => $title,
				'category' => array(
					'id'   => (int) $category_id,
					'slug' => $category_slug,
					'term' => $filter_term instanceof WP_Term ? $filter_term : null,
				),
				'paged' => (int) $paged,
			),
			'initial_state' => array(
				'search'  => '',
				'orderby' => 'date',
				'order'   => 'desc',
				'author'  => '',
				'tag'     => '',
				'year'    => array(
					'floor'   => (int) $year_floor,
					'ceiling' => (int) $year_ceiling,
					'min'     => 0,
					'max'     => 0,
				),
			),
			'options' => array(
				'sort' => array(
					array( 'value' => 'author:asc', 'label' => __( 'Author', 'dynamic-book-archive' ) ),
					array( 'value' => 'date:desc', 'label' => __( 'Date', 'dynamic-book-archive' ) ),
					array( 'value' => 'title:asc', 'label' => __( 'Title', 'dynamic-book-archive' ) ),
				),
				'authors' => $authors,
				'tags'    => $tags,
				'years'   => array(
					'floor'   => (int) $year_floor,
					'ceiling' => (int) $year_ceiling,
				),
			),
			'results' => array(
				'has_posts' => $query instanceof WP_Query ? (bool) $query->have_posts() : false,
				'items'     => $items,
			),
		);
	}
}

