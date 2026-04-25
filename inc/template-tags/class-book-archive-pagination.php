<?php
/**
 * Book archive pagination template part loader.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\TemplateTags;

use DBA\Presenters\Pagination_Presenter;

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

		$pagination = Pagination_Presenter::build_from_paginate_links_args(
			dba_get_book_archive_paginate_links_args( $total, $current )
		);
		if ( ! is_array( $pagination ) ) {
			return;
		}

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
