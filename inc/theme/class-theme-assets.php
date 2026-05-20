<?php
/**
 * Front-end scripts and styles.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\Theme;

/**
 * Enqueues theme assets.
 */
final class Theme_Assets {

	private const THEME_SLIM_SELECT_HANDLE     = 'dba-slim-select';
	private const BOOKS_CPT_SLIM_SELECT_HANDLE = 'books-cpt-slim-select';

	public static function register_hooks(): void {
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue' ) );
		add_action( 'wpcf7_enqueue_scripts', array( self::class, 'enqueue_cf7_slim_select_assets' ), 20 );
	}

	/**
	 * Book post type archive or book_category taxonomy (Library).
	 */
	private static function is_book_library_archive(): bool {
		return is_post_type_archive( 'book' ) || is_tax( \DBA_BOOK_CATEGORY_TAXONOMY );
	}

	public static function enqueue(): void {
		wp_enqueue_style(
			'dba-style',
			get_stylesheet_uri(),
			array(),
			defined( 'DBA_VERSION' ) ? DBA_VERSION : '1.0.0'
		);

		// Cinzel loads here: the Tailwind build does not pass through @import url() from input.css, so compiled app.min.css had no @font-face.
		wp_enqueue_style(
			'dba-font-cinzel',
			'https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700;800;900&family=Lora:wght@400;500;600;700&display=swap',
			array(),
			null
		);

		$app_css = get_template_directory() . '/assets/css/app.min.css';
		wp_enqueue_style(
			'dba-tailwind',
			get_template_directory_uri() . '/assets/css/app.min.css',
			array( 'dba-style', 'dba-font-cinzel' ),
			file_exists( $app_css ) ? (string) filemtime( $app_css ) : ( defined( 'DBA_VERSION' ) ? DBA_VERSION : '1.0.0' )
		);

		$nav_js_path = get_template_directory() . '/assets/js/navigation.min.js';
		wp_enqueue_script(
			'dba-navigation',
			get_template_directory_uri() . '/assets/js/navigation.min.js',
			array(),
			file_exists( $nav_js_path ) ? (string) filemtime( $nav_js_path ) : ( defined( 'DBA_VERSION' ) ? DBA_VERSION : '1.0.0' ),
			true
		);

		self::maybe_enqueue_book_archive_select_assets();
		self::maybe_enqueue_book_single_gallery();
		self::maybe_enqueue_lightbox();

		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}

	/**
	 * GLightbox for elements with class `lightbox` (typically `<a class="lightbox" href="…">`).
	 */
	private static function maybe_enqueue_lightbox(): void {
		$dir = get_template_directory();
		$uri = get_template_directory_uri();

		$css_path = $dir . '/assets/js/lightbox.min.css';
		$js_path  = $dir . '/assets/js/lightbox.min.js';

		if ( ! is_readable( $css_path ) || ! is_readable( $js_path ) ) {
			return;
		}

		wp_enqueue_style(
			'dba-lightbox',
			$uri . '/assets/js/lightbox.min.css',
			array( 'dba-tailwind' ),
			(string) filemtime( $css_path )
		);

		wp_enqueue_script(
			'dba-lightbox',
			$uri . '/assets/js/lightbox.min.js',
			array(),
			(string) filemtime( $js_path ),
			true
		);
	}

	/**
	 * Gallery prev/next for singular books.
	 */
	private static function maybe_enqueue_book_single_gallery(): void {
		if ( ! is_singular( 'book' ) ) {
			return;
		}

		$path = get_template_directory() . '/assets/js/book-single-gallery.min.js';
		$uri  = get_template_directory_uri() . '/assets/js/book-single-gallery.min.js';
		if ( ! is_readable( $path ) ) {
			return;
		}

		wp_enqueue_script(
			'dba-book-single-gallery',
			$uri,
			array(),
			(string) filemtime( $path ),
			true
		);

		wp_localize_script(
			'dba-book-single-gallery',
			'dbaBookSingleGallery',
			array(
				'imageStatus' => __( 'Image %1$s of %2$s', 'dynamic-book-archive' ),
			)
		);
	}

	private static function maybe_enqueue_book_archive_select_assets(): void {
		if ( ! self::is_book_library_archive() ) {
			return;
		}

		$dir = get_template_directory();
		$uri = get_template_directory_uri();

		$init_js = $dir . '/assets/js/book-archive-toolbar-selects.min.js';
		if ( ! is_readable( $init_js ) ) {
			return;
		}

		$slim_handle = self::ensure_slim_select_handle();
		if ( null === $slim_handle ) {
			return;
		}

		$script_deps = array( $slim_handle );
		if ( wp_script_is( 'books-cpt-archive-filter', 'registered' ) ) {
			$script_deps[] = 'books-cpt-archive-filter';
		}

		wp_enqueue_script(
			'dba-book-archive-selects',
			$uri . '/assets/js/book-archive-toolbar-selects.min.js',
			$script_deps,
			(string) filemtime( $init_js ),
			true
		);
	}

	/**
	 * Slim Select assets for any page that renders a Contact Form 7 form.
	 *
	 * Triggered by CF7 on the `wpcf7_enqueue_scripts` action, which fires
	 * only on requests that output a form. Reuses the theme's Slim Select
	 * vendor (already loaded on book archives) when both contexts run on
	 * the same request.
	 */
	public static function enqueue_cf7_slim_select_assets(): void {
		$dir = get_template_directory();
		$uri = get_template_directory_uri();

		$init_js = $dir . '/assets/js/cf7-slim-select.min.js';
		if ( ! is_readable( $init_js ) ) {
			return;
		}

		$slim_handle = self::ensure_slim_select_handle();
		if ( null === $slim_handle ) {
			return;
		}

		wp_enqueue_script(
			'dba-cf7-slim-select',
			$uri . '/assets/js/cf7-slim-select.min.js',
			array( $slim_handle ),
			(string) filemtime( $init_js ),
			true
		);
	}

	/**
	 * Idempotent: enqueue Slim Select vendor at most once per request, or
	 * reuse an already-enqueued handle.
	 *
	 * Order matters:
	 *  1. Theme vendor already enqueued → return its handle.
	 *  2. books-cpt vendor already enqueued (forward-compat) → return that.
	 *  3. Otherwise enqueue theme vendor.
	 *
	 * @return string|null Handle to depend on, or null when vendor files are missing.
	 */
	private static function ensure_slim_select_handle(): ?string {
		if ( self::is_script_active( self::THEME_SLIM_SELECT_HANDLE ) ) {
			return self::THEME_SLIM_SELECT_HANDLE;
		}
		if ( self::is_script_active( self::BOOKS_CPT_SLIM_SELECT_HANDLE ) ) {
			return self::BOOKS_CPT_SLIM_SELECT_HANDLE;
		}

		return self::enqueue_theme_slim_select_vendor() ? self::THEME_SLIM_SELECT_HANDLE : null;
	}

	private static function is_script_active( string $handle ): bool {
		return wp_script_is( $handle, 'enqueued' ) || wp_script_is( $handle, 'done' );
	}

	private static function enqueue_theme_slim_select_vendor(): bool {
		$dir = get_template_directory();
		$uri = get_template_directory_uri();

		$slim_css = $dir . '/assets/vendor/slim-select/slimselect.css';
		$slim_js  = $dir . '/assets/vendor/slim-select/slimselect.min.js';

		if ( ! is_readable( $slim_css ) || ! is_readable( $slim_js ) ) {
			return false;
		}

		wp_enqueue_style(
			self::THEME_SLIM_SELECT_HANDLE,
			$uri . '/assets/vendor/slim-select/slimselect.css',
			array( 'dba-tailwind' ),
			(string) filemtime( $slim_css )
		);

		wp_enqueue_script(
			self::THEME_SLIM_SELECT_HANDLE,
			$uri . '/assets/vendor/slim-select/slimselect.min.js',
			array(),
			(string) filemtime( $slim_js ),
			true
		);

		return true;
	}
}
