<?php
/**
 * Canonicalize legacy `book_cat` query URLs; optional collapse of taxonomy URLs to the plain archive.
 * Redirects `/tag/{slug}/` to the book archive tag filter when the tag belongs to books.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\Theme;

/**
 * Valid `book_cat` on `/book/` 301s to the `book_category` term permalink; invalid values 301 to the plain archive.
 * Taxonomy URLs are left as-is unless {@see 'dba_redirect_book_category_taxonomy_to_book_cat'} forces a collapse to `/book/`.
 * Tag archives for book-only tags are redirected to the book archive tag filter.
 */
final class Book_Archive_Canonical_Redirect {

	public static function register_hooks(): void {
		add_action( 'template_redirect', array( self::class, 'redirect_strip_book_cat_from_book_archive' ), 0 );
		add_action( 'template_redirect', array( self::class, 'redirect_taxonomy_archive_to_book_cat' ), 1 );
		add_action( 'template_redirect', array( self::class, 'redirect_post_tag_archive_to_book_archive' ), 2 );
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

	/**
	 * 301 `/tag/{slug}/` to the book archive tag filter when the tag belongs exclusively to books.
	 *
	 * Skips the redirect when regular posts also use the tag so existing blog tag archives are preserved.
	 */
	public static function redirect_post_tag_archive_to_book_archive(): void {
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) ) {
			return;
		}

		if ( is_feed() || is_embed() || is_trackback() ) {
			return;
		}

		if ( is_customize_preview() ) {
			return;
		}

		if ( ! is_tag() ) {
			return;
		}

		// Already on the book post-type archive (e.g. /book/?tag=…) — never redirect.
		if ( is_post_type_archive( 'book' ) ) {
			return;
		}

		/**
		 * Set to false to disable redirecting `/tag/{slug}/` to the book archive tag filter.
		 *
		 * @param bool $redirect Whether to redirect tag archives used by books to the Library (default true).
		 */
		if ( ! apply_filters( 'dba_redirect_post_tag_to_book_archive', true ) ) {
			return;
		}

		$term = get_queried_object();
		if ( ! $term instanceof \WP_Term || 'post_tag' !== $term->taxonomy ) {
			return;
		}

		global $wpdb;

		// Skip redirect if any published regular posts use this tag — preserve the standard tag archive.
		$has_post = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->term_relationships} tr
				INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
				INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id
				WHERE tt.term_id = %d
				AND tt.taxonomy = 'post_tag'
				AND p.post_type = 'post'
				AND p.post_status = 'publish'
				LIMIT 1",
				$term->term_id
			)
		);

		if ( $has_post > 0 ) {
			return;
		}

		// Redirect only when at least one published book uses this tag.
		$has_book = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->term_relationships} tr
				INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
				INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id
				WHERE tt.term_id = %d
				AND tt.taxonomy = 'post_tag'
				AND p.post_type = 'book'
				AND p.post_status = 'publish'
				LIMIT 1",
				$term->term_id
			)
		);

		if ( $has_book < 1 ) {
			return;
		}

		if ( ! function_exists( 'dba_get_book_archive_tag_filter_url' ) ) {
			return;
		}

		$paged = (int) get_query_var( 'paged' );
		if ( $paged < 1 ) {
			$paged = (int) get_query_var( 'page' );
		}
		$paged  = max( 1, $paged );
		$target = \dba_get_book_archive_tag_filter_url( $term, $paged );

		wp_safe_redirect( $target, 301 );
		exit;
	}
}
