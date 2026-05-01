<?php
/**
 * Book card presenter for the archive grid.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\Presenters;

use WP_Post;

/**
 * Builds args for `template-parts/book/archive/card.php`.
 */
final class Book_Card_Presenter {
	/**
	 * @return array{
	 *   post_id:int,
	 *   permalink:string,
	 *   title:string,
	 *   title_japanese:string,
	 *   publication_label:string,
	 *   thumbnail_html:string,
	 *   placeholder_url:string
	 * }
	 */
	public static function from_post_id( int $post_id ): array {
		$post = get_post( $post_id );
		if ( ! $post instanceof WP_Post ) {
			return self::empty_model( $post_id );
		}

		$title          = get_the_title( $post );
		$title          = is_string( $title ) ? $title : '';
		$title_japanese = (string) get_post_meta( $post->ID, 'title_japanese', true );

		$publication_label = self::format_publication_date_label(
			(string) get_post_meta( $post->ID, 'publication_date', true )
		);

		$permalink = get_permalink( $post );
		$permalink = is_string( $permalink ) && '' !== $permalink ? $permalink : home_url( '/' );

		$thumb_html = '';
		if ( has_post_thumbnail( $post ) ) {
			$thumb_html = get_the_post_thumbnail(
				$post,
				'full',
				array( 'class' => 'size-full max-h-none object-cover object-center' )
			);
			$thumb_html = is_string( $thumb_html ) ? $thumb_html : '';
		}

		return array(
			'post_id'         => (int) $post->ID,
			'permalink'       => $permalink,
			'title'           => $title,
			'title_japanese'  => $title_japanese,
			'publication_label' => $publication_label,
			'thumbnail_html'  => $thumb_html,
			'placeholder_url' => trailingslashit( get_template_directory_uri() ) . 'assets/images/placeholders/no-cover.webp',
		);
	}

	/**
	 * Default model for empty book card.
	 *
	 * @param int $post_id The post ID.
	 * @return array{
	 *   post_id:int,
	 *   permalink:string,
	 *   title:string,
	 *   title_japanese:string,
	 *   publication_label:string,
	 *   thumbnail_html:string,
	 *   placeholder_url:string
	 * }
	 */
	private static function empty_model( int $post_id ): array {
		return array(
			'post_id'         => $post_id,
			'permalink'       => home_url( '/' ),
			'title'           => '',
			'title_japanese'  => '',
			'publication_label' => '',
			'thumbnail_html'  => '',
			'placeholder_url' => trailingslashit( get_template_directory_uri() ) . 'assets/images/placeholders/no-cover.webp',
		);
	}

	/**
	 * Format the publication date label.
	 *
	 * @param string $raw The raw publication date.
	 * @return string The formatted publication date label.
	 */
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
}

