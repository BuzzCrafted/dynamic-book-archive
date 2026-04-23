<?php
/**
 * Redirect `book_category` term permalinks and legacy `book_cat` query URLs to the plain book archive.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\Theme;

/**
 * Uses `/book/` (+ optional `/page/N/`) only; category state is client-side (`data-*` + REST).
 */
final class Book_Archive_Canonical_Redirect {

	public static function register_hooks(): void {
		add_action( 'template_redirect', array( self::class, 'redirect_strip_book_cat_from_book_archive' ), 0 );
		add_action( 'template_redirect', array( self::class, 'redirect_taxonomy_archive_to_book_cat' ), 1 );
	}

	/**
	 * 301 away from `?book_cat=` on the book post type archive (keeps pagination path only).
	 */
	public static function redirect_strip_book_cat_from_book_archive(): void {
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) ) {
			return;
		}

		if ( is_feed() || is_embed() || is_trackback() ) {
			return;
		}

		if ( is_customize_preview() ) {
			return;
		}

		if ( ! is_post_type_archive( 'book' ) ) {
			return;
		}

		$raw = get_query_var( 'book_cat' );
		if ( ( ! is_string( $raw ) || '' === trim( rawurldecode( (string) $raw ) ) ) && isset( $_GET['book_cat'] ) && is_string( $_GET['book_cat'] ) ) {
			$raw = sanitize_text_field( wp_unslash( $_GET['book_cat'] ) );
		}

		if ( ! is_string( $raw ) || '' === trim( rawurldecode( $raw ) ) ) {
			return;
		}

		/**
		 * Toggle stripping `book_cat` from the book archive URL (e.g. legacy integrations).
		 *
		 * @param bool $strip Whether to redirect to the same archive without `book_cat`.
		 */
		if ( ! apply_filters( 'dba_strip_book_cat_query_from_book_archive', true ) ) {
			return;
		}

		$paged = (int) get_query_var( 'paged' );
		if ( $paged < 1 ) {
			$paged = (int) get_query_var( 'page' );
		}
		$paged = max( 1, $paged );

		$target = \dba_get_book_post_type_archive_filter_url( null, $paged );

		wp_safe_redirect( $target, 301 );
		exit;
	}

	/**
	 * 301 to {@see dba_get_book_post_type_archive_filter_url()} (plain archive + pagination; no category query).
	 */
	public static function redirect_taxonomy_archive_to_book_cat(): void {
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) ) {
			return;
		}

		if ( is_feed() || is_embed() || is_trackback() ) {
			return;
		}

		if ( is_customize_preview() ) {
			return;
		}

		if ( ! is_tax( \DBA_BOOK_CATEGORY_TAXONOMY ) ) {
			return;
		}

		/**
		 * Toggle the redirect (e.g. debugging).
		 *
		 * @param bool $redirect Whether to send a 301 to the plain book post type archive URL.
		 */
		if ( ! apply_filters( 'dba_redirect_book_category_taxonomy_to_book_cat', true ) ) {
			return;
		}

		$term = get_queried_object();
		if ( ! $term instanceof \WP_Term ) {
			return;
		}

		unset( $term );

		$paged = (int) get_query_var( 'paged' );
		if ( $paged < 1 ) {
			$paged = (int) get_query_var( 'page' );
		}
		$paged = max( 1, $paged );

		$target = \dba_get_book_post_type_archive_filter_url( null, $paged );

		wp_safe_redirect( $target, 301 );
		exit;
	}
}
