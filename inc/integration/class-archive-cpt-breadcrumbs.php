<?php
/**
 * Breadcrumb integration for archive-cpt post types.
 *
 * Hooks into `dba_breadcrumb_items` to replace the default post-type-archive
 * trail with a collection-aware trail for `historical_document` singles.
 * This avoids a second manual breadcrumb render in the template while producing
 * the correct "Home > Collections > Collection Name > Document" path.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\Integration;

use WP_Post;

/**
 * Filters breadcrumb items for archive-cpt post types.
 */
final class Archive_Cpt_Breadcrumbs {

	public static function register_hooks(): void {
		add_filter( 'dba_breadcrumb_items', array( self::class, 'replace_for_historical_document' ) );
	}

	/**
	 * When on a single `historical_document`, build a collection-aware breadcrumb
	 * trail: Home > Collections > [Collection] > Document.
	 *
	 * The generic fallback in Breadcrumb_Trail::get_items() would produce
	 * "Home > Historical Documents > Title" — correct structurally but not useful
	 * for navigation when the document belongs to a specific collection.
	 *
	 * @param array<int, array{label: string, url: string}> $items Auto-generated items.
	 * @return array<int, array{label: string, url: string}>
	 */
	public static function replace_for_historical_document( array $items ): array {
		if ( ! is_singular( 'historical_document' ) ) {
			return $items;
		}

		global $post;
		if ( ! $post instanceof WP_Post ) {
			return $items;
		}

		$new_items   = array();
		$new_items[] = array(
			'label' => __( 'Home', 'dynamic-book-archive' ),
			'url'   => home_url( '/' ),
		);

		// Collections archive segment.
		$collections_url = get_post_type_archive_link( 'collection' );
		if ( is_string( $collections_url ) && '' !== $collections_url ) {
			$new_items[] = array(
				'label' => __( 'Collections', 'dynamic-book-archive' ),
				'url'   => $collections_url,
			);
		}

		// Parent collection segment (when set).
		$collection_id = (int) get_post_meta( $post->ID, '_archive_collection_id', true );
		if ( $collection_id > 0 ) {
			$collection = get_post( $collection_id );
			if ( $collection instanceof WP_Post && 'collection' === $collection->post_type ) {
				$collection_url = get_permalink( $collection_id );
				$new_items[]    = array(
					'label' => (string) get_the_title( $collection ),
					'url'   => is_string( $collection_url ) && '' !== $collection_url ? $collection_url : '',
				);
			}
		}

		// Current document — last crumb has no URL (rendered as aria-current="page").
		$new_items[] = array(
			'label' => (string) get_the_title( $post ),
			'url'   => '',
		);

		return $new_items;
	}
}
