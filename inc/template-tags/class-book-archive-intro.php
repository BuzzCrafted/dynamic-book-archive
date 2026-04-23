<?php
/**
 * Book archive intro: default tagline or selected category description.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\TemplateTags;

use WP_Term;

/**
 * Prints the intro block for the book archive (standalone grid or embedded in the page header).
 */
final class Book_Archive_Intro {

	/**
	 * Loads {@see template-parts/book/archive/intro.php}.
	 *
	 * @param array<string, mixed> $template_part_args Optional args for the template (e.g. `page_header_embed`, `intro_wrapper_class`).
	 */
	public static function render( array $template_part_args = [] ): void {
		if ( ! is_post_type_archive( 'book' ) && ! is_tax( \DBA_BOOK_CATEGORY_TAXONOMY ) ) {
			return;
		}

		/**
		 * Filters the fallback intro copy when no category is selected or the category has no description.
		 *
		 * @param string $text Plain text (escaped when rendered).
		 */
		$default_text = apply_filters(
			'dba_book_archive_intro_default_text',
			__( 'The collection of rare books about martial arts.', 'dynamic-book-archive' )
		);
		$default_text = is_string( $default_text ) ? $default_text : '';

		$term   = \dba_get_book_archive_filtered_category();
		$cat_id = $term instanceof WP_Term ? (int) $term->term_id : 0;

		$intro_html = '';

		if ( $term instanceof WP_Term ) {
			$desc = term_description( $term, $term->taxonomy );
			if ( is_string( $desc ) && '' !== trim( wp_strip_all_tags( $desc ) ) ) {
				$intro_html = '<div class="book-archive-intro__description">' . wp_kses_post( $desc ) . '</div>';
			}
		}

		if ( '' === $intro_html ) {
			$intro_html = '<p class="book-archive-intro__default">' . esc_html( $default_text ) . '</p>';
		}

		/**
		 * Filters the full intro HTML for the book archive.
		 *
		 * @param string $intro_html Safe HTML for the intro region.
		 * @param int    $cat_id     Category term ID from the query, or 0 for “All”.
		 */
		$intro_html = apply_filters( 'dba_book_archive_intro_html', $intro_html, $cat_id );
		if ( ! is_string( $intro_html ) || '' === $intro_html ) {
			return;
		}

		set_query_var( 'dba_book_archive_intro_html', $intro_html );
		get_template_part(
			'template-parts/book/archive/intro',
			null,
			array_merge(
				array(
					'intro_html' => $intro_html,
				),
				$template_part_args
			)
		);
	}
}
