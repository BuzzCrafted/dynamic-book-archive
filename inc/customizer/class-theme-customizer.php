<?php
/**
 * Theme Customizer bindings.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\Customizer;

use WP_Customize_Manager;

/**
 * Site title / tagline postMessage and preview script.
 */
final class Theme_Customizer {

	public static function register_hooks(): void {
		add_action( 'customize_register', array( self::class, 'register' ) );
		add_action( 'customize_preview_init', array( self::class, 'enqueue_preview_scripts' ) );
	}

	/**
	 * Add postMessage support for site title and description for the Theme Customizer.
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	public static function register( WP_Customize_Manager $wp_customize ): void {
		$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
		$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';

		if ( isset( $wp_customize->selective_refresh ) ) {
			$wp_customize->selective_refresh->add_partial(
				'blogname',
				array(
					'selector'        => '.site-title a',
					'render_callback' => array( self::class, 'partial_blogname' ),
				)
			);
			$wp_customize->selective_refresh->add_partial(
				'blogdescription',
				array(
					'selector'        => '.site-description',
					'render_callback' => array( self::class, 'partial_blogdescription' ),
				)
			);
		}
	}

	/**
	 * Render the site title for the selective refresh partial.
	 */
	public static function partial_blogname(): void {
		bloginfo( 'name' );
	}

	/**
	 * Render the site tagline for the selective refresh partial.
	 */
	public static function partial_blogdescription(): void {
		bloginfo( 'description' );
	}

	/**
	 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
	 */
	public static function enqueue_preview_scripts(): void {
		$customizer_js_path = get_template_directory() . '/assets/js/customizer.min.js';
		wp_enqueue_script(
			'dba_customizer',
			get_template_directory_uri() . '/assets/js/customizer.min.js',
			array( 'customize-preview', 'jquery' ),
			file_exists( $customizer_js_path ) ? (string) filemtime( $customizer_js_path ) : ( defined( 'DBA_VERSION' ) ? DBA_VERSION : '1.0.0' ),
			true
		);
	}
}
