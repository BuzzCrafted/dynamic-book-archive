<?php
/**
 * Book media repository.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\Domain\Books;

/**
 * Data access helpers for book-related media and attachments.
 */
final class Book_Media_Repository {
	/**
	 * Image attachment IDs for a book: ordered `book_gallery` meta (books-cpt 1.3.0+);
	 * falls back to the featured image when the meta is empty.
	 *
	 * @return array<int, int>
	 */
	public static function get_gallery_image_ids( int $post_id ): array {
		$ids = array();

		$raw = get_post_meta( $post_id, 'book_gallery', true );
		if ( is_array( $raw ) ) {
			foreach ( $raw as $v ) {
				$id = (int) $v;
				if ( $id > 0 && ! in_array( $id, $ids, true ) && wp_attachment_is_image( $id ) ) {
					$ids[] = $id;
				}
			}
		}

		if ( empty( $ids ) ) {
			$thumb = (int) get_post_thumbnail_id( $post_id );
			if ( $thumb > 0 && wp_attachment_is_image( $thumb ) ) {
				$ids[] = $thumb;
			}
		}

		return $ids;
	}
}

