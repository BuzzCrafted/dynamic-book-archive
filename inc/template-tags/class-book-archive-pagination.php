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

		$paginate_args = null;
		if ( is_tax( \DBA_BOOK_CATEGORY_TAXONOMY ) ) {
			$filter_term = function_exists( 'dba_get_book_archive_filtered_category' )
				? \dba_get_book_archive_filtered_category()
				: null;
			if ( $filter_term instanceof \WP_Term && \DBA_BOOK_CATEGORY_TAXONOMY === $filter_term->taxonomy ) {
				$tlink = get_term_link( $filter_term );
				if ( ! is_wp_error( $tlink ) && is_string( $tlink ) && '' !== $tlink ) {
					$base          = esc_url_raw( trailingslashit( untrailingslashit( $tlink ) ) ) . '%_%';
					$format        = 'page/%#%/';
					$paginate_args = dba_get_book_archive_paginate_links_args( $total, $current, $base, $format );
				}
			}
		}

		if ( null === $paginate_args ) {
			$archive = \dba_get_book_post_type_archive_url();
			if ( ! is_string( $archive ) || '' === $archive ) {
				return;
			}
			$base          = esc_url_raw( trailingslashit( untrailingslashit( $archive ) ) ) . '%_%';
			$format        = 'page/%#%/';
			$paginate_args = dba_get_book_archive_paginate_links_args( $total, $current, $base, $format );
		}

		$pagination = Pagination_Presenter::build_from_paginate_links_args(
			$paginate_args
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
