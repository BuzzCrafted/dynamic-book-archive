<?php
/**
 * Single historical document presenter.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\Presenters;

use WP_Post;
use WP_Term;

/**
 * Builds an args-only view model for `template-parts/archive/document/page.php`.
 */
final class Historical_Document_Single_Presenter {

	/**
	 * Build the view model for a single historical document.
	 *
	 * @param int $post_id The historical_document post ID.
	 * @return array<string, mixed>
	 */
	public static function build_from_post_id( int $post_id ): array {
		$post = get_post( $post_id );
		if ( ! $post instanceof WP_Post ) {
			return array();
		}

		$publication      = (string) get_post_meta( $post_id, '_archive_publication', true );
		$publication_date = (string) get_post_meta( $post_id, '_archive_publication_date', true );
		$language         = (string) get_post_meta( $post_id, '_archive_language', true );
		$collection_id    = (int) get_post_meta( $post_id, '_archive_collection_id', true );

		// Multi-value meta for related people.
		$person_ids_raw = get_post_meta( $post_id, '_archive_person_ids', false );
		$person_ids     = array_values( array_filter( array_map( 'absint', is_array( $person_ids_raw ) ? $person_ids_raw : array() ) ) );

		// Document type — plugin enforces 1:1; take the first term.
		$doc_type_terms = get_the_terms( $post_id, 'document_type' );
		$document_type  = array();
		if ( is_array( $doc_type_terms ) && ! empty( $doc_type_terms ) ) {
			$term = reset( $doc_type_terms );
			if ( $term instanceof WP_Term ) {
				$document_type = array(
					'name' => $term->name,
					'slug' => $term->slug,
				);
			}
		}

		// Parent collection.
		$collection = self::resolve_collection( $collection_id );

		// Related people.
		$people = self::resolve_people( $person_ids );

		// Combined "Publication / Year" header line (e.g. "Asahi Roots / 1960").
		$publication_line = function_exists( 'dba_format_archive_publication_line' )
			? dba_format_archive_publication_line( $publication, $publication_date )
			: '';

		return array(
			'post_id'          => $post_id,
			'title'            => (string) get_the_title( $post ),
			'content'          => $post->post_content,
			'back_link'        => self::build_back_link( $collection ),
			'publication'      => $publication,
			'publication_date' => $publication_date,
			'publication_line' => $publication_line,
			'language'         => $language,
			'document_type'    => $document_type,
			'collection'       => $collection,
			'people'           => $people,
			'breadcrumbs'      => self::build_breadcrumbs( $post_id, $collection ),
			'cover_image'      => self::resolve_cover_image( $post_id ),
		);
	}

	/**
	 * Resolve the cover image from the post's featured image.
	 *
	 * @param int $post_id Historical document post ID.
	 * @return array{url: string, alt: string}
	 */
	private static function resolve_cover_image( int $post_id ): array {
		$empty = array( 'url' => '', 'alt' => '' );

		$url = get_the_post_thumbnail_url( $post_id, 'large' );
		if ( ! is_string( $url ) || '' === $url ) {
			return $empty;
		}

		$thumb_id = (int) get_post_thumbnail_id( $post_id );
		$alt      = '';
		if ( $thumb_id > 0 ) {
			$alt = (string) get_post_meta( $thumb_id, '_wp_attachment_image_alt', true );
			if ( '' === $alt ) {
				$alt = (string) get_the_title( $thumb_id );
			}
		}

		return array( 'url' => $url, 'alt' => $alt );
	}

	/**
	 * Resolve the parent collection into a display array.
	 *
	 * @param int $collection_id Collection post ID (0 when not set).
	 * @return array{id: int, title: string, url: string}
	 */
	private static function resolve_collection( int $collection_id ): array {
		$empty = array( 'id' => 0, 'title' => '', 'url' => '' );
		if ( $collection_id <= 0 ) {
			return $empty;
		}
		$post = get_post( $collection_id );
		if ( ! $post instanceof WP_Post || 'collection' !== $post->post_type ) {
			return $empty;
		}
		$url = get_permalink( $collection_id );
		return array(
			'id'    => $collection_id,
			'title' => (string) get_the_title( $post ),
			'url'   => is_string( $url ) && '' !== $url ? $url : '',
		);
	}

	/**
	 * Resolve person IDs to display arrays.
	 *
	 * @param array<int, int> $person_ids Person post IDs.
	 * @return array<int, array{id: int, title: string, url: string}>
	 */
	private static function resolve_people( array $person_ids ): array {
		$people = array();
		foreach ( $person_ids as $pid ) {
			$person = get_post( $pid );
			if ( ! $person instanceof WP_Post || 'person' !== $person->post_type ) {
				continue;
			}
			$url      = get_permalink( $pid );
			$people[] = array(
				'id'    => $pid,
				'title' => (string) get_the_title( $person ),
				'url'   => is_string( $url ) && '' !== $url ? $url : '',
			);
		}
		return $people;
	}

	/**
	 * Build back-link: to parent collection when present, otherwise to collections archive.
	 *
	 * @param array{id: int, title: string, url: string} $collection
	 * @return array{url: string, label: string}
	 */
	private static function build_back_link( array $collection ): array {
		if ( $collection['id'] > 0 && '' !== $collection['url'] ) {
			return array(
				'url'   => $collection['url'],
				/* translators: %s: Collection title. */
				'label' => sprintf( __( 'Back to %s', 'dynamic-book-archive' ), $collection['title'] ),
			);
		}
		$archive_url = get_post_type_archive_link( 'collection' );
		return array(
			'url'   => is_string( $archive_url ) && '' !== $archive_url ? $archive_url : home_url( '/' ),
			'label' => __( 'Back to Collections', 'dynamic-book-archive' ),
		);
	}

	/**
	 * Build breadcrumb items: Home > Collections > [Collection] > Document.
	 *
	 * The last item has no URL — breadcrumbs partial marks it as `aria-current="page"`.
	 *
	 * @param int                                  $post_id    Current document ID.
	 * @param array{id: int, title: string, url: string} $collection Parent collection.
	 * @return array<int, array{label: string, url: string}>
	 */
	private static function build_breadcrumbs( int $post_id, array $collection ): array {
		$items   = array();
		$items[] = array(
			'label' => __( 'Home', 'dynamic-book-archive' ),
			'url'   => home_url( '/' ),
		);

		$collections_url = get_post_type_archive_link( 'collection' );
		if ( is_string( $collections_url ) && '' !== $collections_url ) {
			$items[] = array(
				'label' => __( 'Collections', 'dynamic-book-archive' ),
				'url'   => $collections_url,
			);
		}

		if ( $collection['id'] > 0 && '' !== $collection['url'] && '' !== $collection['title'] ) {
			$items[] = array(
				'label' => $collection['title'],
				'url'   => $collection['url'],
			);
		}

		// Current page — no URL.
		$items[] = array(
			'label' => (string) get_the_title( $post_id ),
			'url'   => '',
		);

		return $items;
	}
}
