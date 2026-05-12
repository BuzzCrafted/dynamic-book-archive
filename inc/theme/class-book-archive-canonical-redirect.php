<?php
/**
 * Canonicalize legacy `book_cat` query URLs; optional collapse of taxonomy URLs to the plain archive.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\Theme;

/**
 * Valid `book_cat` on `/book/` 301s to the `book_category` term permalink; invalid values 301 to the plain archive.
 * Taxonomy URLs are left as-is unless {@see 'dba_redirect_book_category_taxonomy_to_book_cat'} forces a collapse to `/book/`.
 */
final class Book_Archive_Canonical_Redirect {

	public static function register_hooks(): void {
		add_action( 'template_redirect', array( self::class, 'redirect_strip_book_cat_from_book_archive' ), 0 );
		add_action( 'template_redirect', array( self::class, 'redirect_taxonomy_archive_to_book_cat' ), 1 );
	}

	/**
	 * 301 legacy `/book/?book_cat=…` to the term permalink when valid, or to the plain archive when invalid.
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
		 * Toggle canonicalizing `book_cat` on the book post type archive (e.g. integrations that must keep the query).
		 *
		 * @param bool $strip When true (default), unknown `book_cat` values redirect to the plain archive and known values to the term URL.
		 */
		if ( ! apply_filters( 'dba_strip_book_cat_query_from_book_archive', true ) ) {
			return;
		}

		$resolved = \dba_resolve_book_category_term_from_book_cat_query_var( $raw );
		$paged    = (int) get_query_var( 'paged' );
		if ( $paged < 1 ) {
			$paged = (int) get_query_var( 'page' );
		}
		$paged = max( 1, $paged );

		if ( $resolved instanceof \WP_Term ) {
			$target = \dba_get_book_post_type_archive_filter_url( $resolved, $paged );
			wp_safe_redirect( $target, 301 );
			exit;
		}

		$target = \dba_get_book_post_type_archive_filter_url( null, $paged );

		wp_safe_redirect( $target, 301 );
		exit;
	}

	/**
	 * Optional 301 from `book_category` archives to the plain `/book/` archive (drops category in the URL).
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
		 * When true, send a 301 from taxonomy archives to the plain book post type archive (same page number).
		 *
		 * Default false so filtered Library URLs stay on SEO-friendly term permalinks.
		 *
		 * @param bool $redirect Whether to collapse taxonomy URLs to `/book/` (+ pagination).
		 */
		if ( ! apply_filters( 'dba_redirect_book_category_taxonomy_to_book_cat', false ) ) {
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
