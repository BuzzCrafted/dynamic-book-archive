<?php
/**
 * Theme supports, textdomain, menus.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\Theme;

/**
 * Runs on after_setup_theme.
 */
final class Theme_Setup {

	public static function register_hooks(): void {
		add_action( 'after_setup_theme', array( self::class, 'setup' ) );
	}

	public static function setup(): void {
		load_theme_textdomain( 'dynamic-book-archive', get_template_directory() . '/languages' );

		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );

		register_nav_menus(
			array(
				'menu-1' => esc_html__( 'Primary', 'dynamic-book-archive' ),
			)
		);

		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
			)
		);

		add_theme_support(
			'custom-logo',
			array(
				'height'      => 250,
				'width'       => 250,
				'flex-width'  => true,
				'flex-height' => true,
			)
		);
	}
}
