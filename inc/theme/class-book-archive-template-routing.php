<?php
/**
 * Loads the book archive template for book_category taxonomy URLs (single entry with archive-book.php).
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\Theme;

/**
 * Removes the need for a duplicate taxonomy-book_category.php file.
 */
final class Book_Archive_Template_Routing {

	public static function register_hooks(): void {
		add_filter( 'template_include', array( self::class, 'filter_template_include' ), 99 );
	}

	/**
	 * Use archive-book.php for book_category term archives when present in the theme.
	 *
	 * @param string $template Path to the selected template.
	 */
	public static function filter_template_include( string $template ): string {
		if ( ! is_tax( \DBA_BOOK_CATEGORY_TAXONOMY ) ) {
			return $template;
		}

		$located = locate_template( array( 'archive-book.php' ) );
		if ( is_string( $located ) && '' !== $located && is_readable( $located ) ) {
			return $located;
		}

		return $template;
	}
}
