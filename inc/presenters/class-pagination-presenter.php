<?php
/**
 * Pagination presenter.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\Presenters;

/**
 * Builds a stable view model for archive pagination.
 */
final class Pagination_Presenter {
	/**
	 * @param array<string, mixed> $paginate_links_args Args for paginate_links().
	 * @return array{prev_html:string,next_html:string,numbers:array<int,string>}|null
	 */
	public static function build_from_paginate_links_args( array $paginate_links_args ): ?array {
		$links = paginate_links( $paginate_links_args );
		if ( ! is_array( $links ) ) {
			return null;
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

		return array(
			'prev_html' => $prev_html,
			'next_html' => $next_html,
			'numbers'   => $numbers,
		);
	}
}

