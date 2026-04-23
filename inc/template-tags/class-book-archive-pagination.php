<?php
/**
 * Book archive pagination template part loader.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\TemplateTags;

/**
 * Builds paginate_links data for the book archive UI.
 */
final class Book_Archive_Pagination {

	/**
	 * Pagination for the book archive: builds link data and loads {@see template-parts/book/archive/pagination.php}.
	 */
	public static function render(): void {
		if ( ! is_post_type_archive( 'book' ) && ! is_tax( \DBA_BOOK_CATEGORY_TAXONOMY ) ) {
			return;
		}

		global $wp_query;

		$total = (int) $wp_query->max_num_pages;
		if ( $total <= 1 ) {
			return;
		}

		$paged = (int) get_query_var( 'paged' );
		if ( $paged < 1 ) {
			$paged = (int) get_query_var( 'page' );
		}
		$current = max( 1, $paged );

		$links = paginate_links(
			array(
				'total'     => $total,
				'current'   => $current,
				'type'      => 'array',
				'mid_size'  => 2,
				'end_size'  => 1,
				'prev_text' => __( '< Previous', 'dynamic-book-archive' ),
				'next_text' => __( 'Next >', 'dynamic-book-archive' ),
			)
		);

		if ( ! is_array( $links ) ) {
			return;
		}

		$prev_html = '';
		$next_html = '';
		$numbers   = array();

		foreach ( $links as $link ) {
			if ( ! is_string( $link ) ) {
				continue;
			}
			if ( str_contains( $link, 'prev page-numbers' ) ) {
				$prev_html = $link;
				continue;
			}
			if ( str_contains( $link, 'next page-numbers' ) ) {
				$next_html = $link;
				continue;
			}
			$numbers[] = $link;
		}

		$pagination = array(
			'prev_html' => $prev_html,
			'next_html' => $next_html,
			'numbers'   => $numbers,
		);

		set_query_var( 'dba_book_pagination', $pagination );
		get_template_part(
			'template-parts/book/archive/pagination',
			null,
			array(
				'pagination' => $pagination,
			)
		);
	}
}
