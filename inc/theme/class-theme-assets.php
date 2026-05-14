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

	public static function register_hooks(): void {
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue' ) );
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

		$slim_css = $dir . '/assets/vendor/slim-select/slimselect.css';
		$slim_js  = $dir . '/assets/vendor/slim-select/slimselect.min.js';
		$init_js  = $dir . '/assets/js/book-archive-toolbar-selects.min.js';

		if ( ! is_readable( $slim_css ) || ! is_readable( $slim_js ) || ! is_readable( $init_js ) ) {
			return;
		}

		wp_enqueue_style(
			'dba-slim-select',
			$uri . '/assets/vendor/slim-select/slimselect.css',
			array( 'dba-tailwind' ),
			(string) filemtime( $slim_css )
		);

		wp_enqueue_script(
			'dba-slim-select',
			$uri . '/assets/vendor/slim-select/slimselect.min.js',
			array(),
			(string) filemtime( $slim_js ),
			true
		);

		$script_deps = array( 'dba-slim-select' );
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
}
