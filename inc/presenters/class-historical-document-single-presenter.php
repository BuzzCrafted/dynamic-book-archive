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

		// Collections — multi-row since archive-cpt plugin update.
		$collection_ids_raw = get_post_meta( $post_id, '_archive_collection_id', false );
		$collection_ids     = array_values( array_filter( array_map( 'intval', is_array( $collection_ids_raw ) ? $collection_ids_raw : array() ) ) );
		$collections        = self::resolve_collections( $collection_ids );

		// Multi-value meta for related people (person CPT links).
		$person_ids_raw = get_post_meta( $post_id, '_archive_person_ids', false );
		$person_ids     = array_values( array_filter( array_map( 'absint', is_array( $person_ids_raw ) ? $person_ids_raw : array() ) ) );

		// String-based people fields from archive-cpt plugin.
		$authors_raw = get_post_meta( $post_id, '_archive_authors', false );
		$authors     = array_values( array_filter( array_map( 'strval', is_array( $authors_raw ) ? $authors_raw : array() ) ) );

		$translators_raw = get_post_meta( $post_id, '_archive_translators', false );
		$translators     = array_values( array_filter( array_map( 'strval', is_array( $translators_raw ) ? $translators_raw : array() ) ) );

		$editor    = trim( (string) get_post_meta( $post_id, '_archive_editor', true ) );
		$publisher = trim( (string) get_post_meta( $post_id, '_archive_publisher', true ) );

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

		// Related people (person CPT).
		$people = self::resolve_people( $person_ids );

		// Combined "Publication / Year" header line (e.g. "Asahi Roots / 1960").
		$publication_line = function_exists( 'dba_format_archive_publication_line' )
			? dba_format_archive_publication_line( $publication, $publication_date )
			: '';

		// Back link targets the first collection when any are set; falls back to collections archive.
		$back_link = self::build_back_link( $collections );

		return array(
			'post_id'          => $post_id,
			'title'            => (string) get_the_title( $post ),
			'content'          => $post->post_content,
			'back_link'        => $back_link,
			'publication'      => $publication,
			'publication_date' => $publication_date,
			'publication_line' => $publication_line,
			'language'         => $language,
			'document_type'    => $document_type,
			'collections'      => $collections,
			'authors'          => $authors,
			'translators'      => $translators,
			'editor'           => $editor,
			'publisher'        => $publisher,
			'people'           => $people,
			'cover_image'      => self::resolve_cover_image( $post_id ),
		);
	}

	/**
	 * Resolve the cover image from the post's featured image.
	 *
	 * @param int $post_id Historical document post ID.
	 * @return array{url: string, full_url: string, alt: string}
	 */
	private static function resolve_cover_image( int $post_id ): array {
		$empty = array( 'url' => '', 'full_url' => '', 'alt' => '' );

		$url = get_the_post_thumbnail_url( $post_id, 'large' );
		if ( ! is_string( $url ) || '' === $url ) {
			return $empty;
		}

		$thumb_id = (int) get_post_thumbnail_id( $post_id );
		$alt      = '';
		$full_url = '';
		if ( $thumb_id > 0 ) {
			$alt = (string) get_post_meta( $thumb_id, '_wp_attachment_image_alt', true );
			if ( '' === $alt ) {
				$alt = (string) get_the_title( $thumb_id );
			}
			$full_url_raw = wp_get_attachment_image_url( $thumb_id, 'full' );
			$full_url     = is_string( $full_url_raw ) ? $full_url_raw : '';
		}

		return array( 'url' => $url, 'full_url' => $full_url, 'alt' => $alt );
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
	 * Resolve multiple collection IDs to display arrays, skipping invalid posts.
	 *
	 * @param array<int, int> $collection_ids Collection post IDs.
	 * @return array<int, array{id: int, title: string, url: string}>
	 */
	private static function resolve_collections( array $collection_ids ): array {
		$collections = array();
		foreach ( $collection_ids as $cid ) {
			$resolved = self::resolve_collection( $cid );
			if ( $resolved['id'] > 0 ) {
				$collections[] = $resolved;
			}
		}
		return $collections;
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
	 * Build back-link: to the first collection when any are set, otherwise to the collections archive.
	 *
	 * For documents belonging to multiple collections the first ID (in meta storage order) is used
	 * for navigation consistency; all collections are shown in the header.
	 *
	 * @param array<int, array{id: int, title: string, url: string}> $collections Resolved collections.
	 * @return array{url: string, label: string}
	 */
	private static function build_back_link( array $collections ): array {
		$first = ! empty( $collections ) ? $collections[0] : null;
		if ( null !== $first && $first['id'] > 0 && '' !== $first['url'] ) {
			return array(
				'url'   => $first['url'],
				/* translators: %s: Collection title. */
				'label' => sprintf( __( 'Back to %s', 'dynamic-book-archive' ), $first['title'] ),
			);
		}
		$archive_url = get_post_type_archive_link( 'collection' );
		return array(
			'url'   => is_string( $archive_url ) && '' !== $archive_url ? $archive_url : home_url( '/' ),
			'label' => __( 'Back to Collections', 'dynamic-book-archive' ),
		);
	}
}
