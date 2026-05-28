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

	/**
	 * Attachment img markup using the DKO Smart Lightbox Image DOM contract.
	 *
	 * @param int               $attachment_id Attachment post ID.
	 * @param string            $display_size  Registered WP image size for the inline img.
	 * @param string            $image_class   CSS classes for the img (js-dko-lightbox-image appended when missing).
	 * @param string            $aria_label    Accessible label for the lightbox trigger.
	 * @param array<string,mixed> $extra_attrs Extra attributes passed to wp_get_attachment_image().
	 * @param string            $gallery_id    Optional `data-gallery` id for multi-image lightbox navigation.
	 */
	public static function render_lightbox_image(
		int $attachment_id,
		string $display_size,
		string $image_class,
		string $aria_label,
		array $extra_attrs = array(),
		string $gallery_id = ''
	): string {
		if ( function_exists( 'dko_elementor_widgets_render_lightbox_image' ) ) {
			return dko_elementor_widgets_render_lightbox_image(
				$attachment_id,
				$display_size,
				'full',
				$image_class,
				$aria_label,
				$extra_attrs,
				$gallery_id
			);
		}

		if ( $attachment_id <= 0 ) {
			return '';
		}

		$html = wp_get_attachment_image(
			$attachment_id,
			$display_size,
			false,
			array_merge(
				array( 'class' => trim( $image_class ) ),
				$extra_attrs
			)
		);

		return is_string( $html ) ? $html : '';
	}
}

