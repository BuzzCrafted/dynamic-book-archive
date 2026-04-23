<?php
/**
 * Widget areas.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\Theme;

/**
 * Registers sidebars.
 */
final class Theme_Widgets {

	public static function register_hooks(): void {
		add_action( 'widgets_init', array( self::class, 'register_sidebars' ) );
	}

	public static function register_sidebars(): void {
		register_sidebar(
			array(
				'name'          => esc_html__( 'Sidebar', 'dynamic-book-archive' ),
				'id'            => 'sidebar-1',
				'description'   => esc_html__( 'Add widgets here.', 'dynamic-book-archive' ),
				'before_widget' => '<section id="%1$s" class="widget %2$s mb-8">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="widget-title text-sm font-semibold uppercase tracking-wide text-library-primary/70">',
				'after_title'   => '</h2>',
			)
		);
	}
}
