<?php
/**
 * Single book page presenter.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\Presenters;

use DBA\Domain\Books\Book_Media_Repository;
use WP_Post;
use WP_Post_Type;
use WP_Term;

/**
 * Builds an args-only view model for `template-parts/book/single.php` (and its extracted sections).
 */
final class Book_Single_Presenter {
	/**
	 * Build the view model for the single book page.
	 *
	 * @param int $post_id The post ID.
	 * @return array<string, mixed>
	 */
	public static function build_from_post_id( int $post_id ): array {
		$post = get_post( $post_id );
		if ( ! $post instanceof WP_Post ) {
			return array();
		}

		$title_japanese  = (string) get_post_meta( $post_id, 'title_japanese', true );
		$book_author     = (string) get_post_meta( $post_id, 'book_author', true );
		$author_japanese = (string) get_post_meta( $post_id, 'author_japanese', true );
		$publication_raw = (string) get_post_meta( $post_id, 'publication_date', true );

		$edition_details = (string) get_post_meta( $post_id, 'edition_details', true );
		$is_signed       = (bool) get_post_meta( $post_id, 'is_signed', true );
		$has_dust_jacket = (bool) get_post_meta( $post_id, 'has_dust_jacket', true );
		$has_slipcase    = (bool) get_post_meta( $post_id, 'has_slipcase', true );

		$book_language   = trim( (string) get_post_meta( $post_id, 'book_language', true ) ) ?: 'Japanese';
		$book_pages      = trim( (string) get_post_meta( $post_id, 'pages', true ) ) ?: '1';
		$book_dimensions = trim( (string) get_post_meta( $post_id, 'dimensions', true ) ) ?: '0 cm';
		$book_binding    = trim( (string) get_post_meta( $post_id, 'binding', true ) ) ?: '0';
		$book_condition  = trim( (string) get_post_meta( $post_id, 'condition', true ) ) ?: '0';
		$book_publisher  = trim( (string) get_post_meta( $post_id, 'publisher', true ) ) ?: '';
		$book_price      = trim( (string) get_post_meta( $post_id, 'price', true ) );

		if ( empty( $book_price ) ) {
			$book_price = trim( (string) get_post_meta( $post_id, 'book_price', true ) );
		}

		$gallery_ids = Book_Media_Repository::get_gallery_image_ids( $post_id );
		$gallery_ids = array_values( array_filter( array_map( 'intval', $gallery_ids ), 'wp_attachment_is_image' ) );

		$gallery_count  = count( $gallery_ids );
		$thumb_limit    = 5;
		$thumbs_capped  = $gallery_count > $thumb_limit;
		$thumb_rail_rtl = $gallery_count < $thumb_limit;

		$publication_label = self::format_publication_date_label( $publication_raw );

		$author_display = $book_author;
		if ( '' !== $author_japanese && '' !== $book_author ) {
			$author_display = sprintf(
				/* translators: 1: author name (Latin), 2: author name (Japanese). */
				__( '%1$s (%2$s)', 'dynamic-book-archive' ),
				$book_author,
				$author_japanese
			);
		} elseif ( '' !== $author_japanese ) {
			$author_display = $author_japanese;
		}

		$categories = get_the_terms( $post_id, \DBA_BOOK_CATEGORY_TAXONOMY );
		if ( is_wp_error( $categories ) || ! is_array( $categories ) ) {
			$categories = array();
		}
		$category_names = array();
		foreach ( $categories as $t ) {
			if ( $t instanceof WP_Term ) {
				$category_names[] = $t->name;
			}
		}
		$category_label = implode( ', ', $category_names );

		$tags = get_the_terms( $post_id, 'post_tag' );
		if ( is_wp_error( $tags ) || ! is_array( $tags ) ) {
			$tags = array();
		}
		$tag_links = array();
		foreach ( $tags as $term ) {
			if ( ! $term instanceof WP_Term ) {
				continue;
			}
			$link = function_exists( 'dba_get_book_archive_tag_filter_url' )
				? dba_get_book_archive_tag_filter_url( $term )
				: get_term_link( $term );
			if ( is_wp_error( $link ) || ! is_string( $link ) ) {
				continue;
			}
			$tag_links[] = array(
				'name' => $term->name,
				'url'  => $link,
			);
		}

		$collection_label = apply_filters(
			'dba_book_single_collection_label',
			__( 'Robert C. Gruzanski Collection', 'dynamic-book-archive' ),
			$post_id
		);
		$collection_label = is_string( $collection_label ) ? $collection_label : '';

		$archive_url = function_exists( 'dba_get_book_post_type_archive_url' ) ? dba_get_book_post_type_archive_url() : get_post_type_archive_link( 'book' );
		if ( ! is_string( $archive_url ) || '' === $archive_url ) {
			$archive_url = home_url( '/' );
		}

		$library_back_label = __( 'Back to Library', 'dynamic-book-archive' );
		$pto                = get_post_type_object( 'book' );
		if ( $pto instanceof WP_Post_Type ) {
			$menu_label = function_exists( 'dba_get_breadcrumb_label_from_primary_menu_for_archive' )
				? dba_get_breadcrumb_label_from_primary_menu_for_archive( $pto )
				: '';
			if ( '' !== $menu_label ) {
				/* translators: %s: Library section name from the menu (e.g. “Ninjutsu Library”). */
				$library_back_label = sprintf( __( 'Back to %s', 'dynamic-book-archive' ), $menu_label );
			}
		}

		$quick_items = array();
		if ( '' !== $book_pages ) {
			$quick_items[] = array(
				/* translators: %s: page count. */
				'text' => sprintf( __( '%s pages', 'dynamic-book-archive' ), $book_pages ),
				'icon' => 'pages',
			);
		}
		$book_dimensions_display = trim( $book_dimensions );
		if ( '' !== $book_dimensions_display && '0 cm' !== strtolower( $book_dimensions_display ) && '0' !== $book_dimensions_display ) {
			$quick_items[] = array(
				'text' => $book_dimensions_display,
				'icon' => 'size',
			);
		}
		if ( '' !== $book_language ) {
			$quick_items[] = array(
				'text' => $book_language,
				'icon' => 'language',
			);
		}
		$book_binding_display = trim( $book_binding );
		if ( '' !== $book_binding_display && '0' !== $book_binding_display ) {
			$quick_items[] = array(
				'text' => $book_binding_display,
				'icon' => 'binding',
			);
		}

		$edition_lines           = array();
		$edition_details_trimmed = trim( $edition_details );
		$book_condition_display  = trim( $book_condition );

		if ( '' !== $publication_label ) {
			$edition_lines[] = array(
				'label' => __( 'Published:', 'dynamic-book-archive' ),
				'value' => $publication_label,
			);
		}
		if ( '' !== $book_publisher ) {
			$edition_lines[] = array(
				'label' => __( 'Publisher:', 'dynamic-book-archive' ),
				'value' => $book_publisher,
			);
		}
		if ( '' !== $edition_details_trimmed ) {
			$edition_parts = self::split_edition_details_lines( $edition_details_trimmed );
			$edition_lines[] = array(
				'label' => __( 'Edition:', 'dynamic-book-archive' ),
				'value' => $edition_details_trimmed,
				'value_lines' => count( $edition_parts ) > 1 ? $edition_parts : array(),
			);
		}
		if ( '' !== $book_condition_display && '0' !== $book_condition_display ) {
			$edition_lines[] = array(
				'label' => __( 'Condition:', 'dynamic-book-archive' ),
				'value' => $book_condition_display,
			);
		}
		if ( $is_signed ) {
			$edition_lines[] = array(
				'label' => __( 'Signed:', 'dynamic-book-archive' ),
				'value' => __( 'Signed copy', 'dynamic-book-archive' ),
			);
		}
		if ( $has_slipcase ) {
			$edition_lines[] = array(
				'label' => __( 'Slipcase:', 'dynamic-book-archive' ),
				'value' => __( 'Yes', 'dynamic-book-archive' ),
			);
		}
		if ( $has_dust_jacket ) {
			$edition_lines[] = array(
				'label' => __( 'Dust jacket:', 'dynamic-book-archive' ),
				'value' => __( 'Yes', 'dynamic-book-archive' ),
			);
		}
		if ( '' !== $book_price ) {
			$edition_lines[] = array(
				'label' => __( 'Price:', 'dynamic-book-archive' ),
				'value' => $book_price,
			);
		}

		$content = (string) $post->post_content;

		return array(
			'post_id' => (int) $post_id,
			'back_link' => array(
				'url'   => $archive_url,
				'label' => $library_back_label,
			),
			'titles' => array(
				'title'          => get_the_title( $post ),
				'title_japanese' => $title_japanese,
			),
			'meta' => array(
				'author_display'   => $author_display,
				'category_label'   => $category_label,
				'collection_label' => $collection_label,
			),
			'gallery' => array(
				'ids'            => $gallery_ids,
				'count'          => $gallery_count,
				'thumb_limit'    => $thumb_limit,
				'thumbs_capped'  => $thumbs_capped,
				'thumb_rail_rtl' => $thumb_rail_rtl,
			),
			'quick_items'   => $quick_items,
			'content'       => $content,
			'edition_lines' => $edition_lines,
			'tags'          => $tag_links,
		);
	}

	private static function format_publication_date_label( string $raw ): string {
		$raw = trim( $raw );
		if ( '' === $raw ) {
			return '';
		}

		$ts = strtotime( $raw );
		if ( false === $ts ) {
			return '';
		}

		$is_year_only = (bool) preg_match( '/^\\d{4}$/', $raw );
		return $is_year_only
			? date_i18n( 'Y', (int) $ts )
			: date_i18n( (string) get_option( 'date_format' ), (int) $ts );
	}

	/**
	 * @return array<int, string>
	 */
	private static function split_edition_details_lines( string $raw ): array {
		$raw = trim( wp_strip_all_tags( $raw ) );
		if ( '' === $raw ) {
			return array();
		}

		$raw = html_entity_decode( $raw, ENT_QUOTES, 'UTF-8' );
		$raw = (string) preg_replace( "/\\x{00A0}/u", ' ', $raw ); // NBSP → regular space.

		$normalize = static function ( string $v ): string {
			$v = html_entity_decode( $v, ENT_QUOTES, 'UTF-8' );
			$v = (string) preg_replace( "/\\x{00A0}/u", ' ', $v );
			$v = trim( $v );
			return $v;
		};

		$parts = preg_split( "/\\R+/", $raw ) ?: array();
		$parts = array_values( array_filter( array_map( $normalize, $parts ), static fn( string $v ): bool => '' !== $v ) );

		if ( count( $parts ) <= 1 ) {
			$parts = preg_split( '/\\s*;\\s*/', $raw ) ?: array();
			$parts = array_values( array_filter( array_map( $normalize, $parts ), static fn( string $v ): bool => '' !== $v ) );
		}

		return $parts;
	}
}

