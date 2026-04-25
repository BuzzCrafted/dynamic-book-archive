<?php
/**
 * Breadcrumb items presenter.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\Presenters;

use DBA\TemplateTags\Breadcrumb_Trail;
use WP_Term;

/**
 * Thin adapter around the existing breadcrumb builder.
 *
 * This is the stable dependency for views/integrations; the underlying breadcrumb implementation
 * can be refactored later without touching call sites.
 */
final class Breadcrumb_Items_Presenter {
	/**
	 * @return array<int, array{label:string,url:string}>
	 */
	public static function for_current_request(): array {
		return Breadcrumb_Trail::get_items();
	}

	/**
	 * @return array<int, array{label:string,url:string}>
	 */
	public static function for_book_archive( ?WP_Term $filter_term ): array {
		return Breadcrumb_Trail::get_book_post_type_breadcrumb_items( $filter_term );
	}
}

