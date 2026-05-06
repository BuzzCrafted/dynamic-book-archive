<?php
/**
 * Theme Customizer bindings.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\Customizer;

use WP_Customize_Manager;
use WP_Customize_Media_Control;

/**
 * Site title / tagline postMessage, alternative logo, and preview script.
 */
final class Theme_Customizer {

	public const THEME_MOD_ALTERNATIVE_LOGO = 'dba_alternative_logo';

	public static function register_hooks(): void {
		add_action( 'customize_register', array( self::class, 'register' ) );
		add_action( 'customize_preview_init', array( self::class, 'enqueue_preview_scripts' ) );
	}

	/**
	 * Customizer registration.
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	public static function register( WP_Customize_Manager $wp_customize ): void {
		$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
		$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';

		$wp_customize->add_setting(
			self::THEME_MOD_ALTERNATIVE_LOGO,
			array(
				'default'           => 0,
				'sanitize_callback' => array( self::class, 'sanitize_attachment_id_image' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Media_Control(
				$wp_customize,
				self::THEME_MOD_ALTERNATIVE_LOGO,
				array(
					'label'       => esc_html__( 'Alternative logo', 'dynamic-book-archive' ),
					'description' => esc_html__( 'Optional. Shown in the site footer; uses the main logo if not set.', 'dynamic-book-archive' ),
					'section'     => 'title_tagline',
					'mime_type'   => 'image',
					'priority'    => 9,
				)
			)
		);

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
			$wp_customize->selective_refresh->add_partial(
				'dba_footer_logo',
				array(
					'settings'            => array( self::THEME_MOD_ALTERNATIVE_LOGO, 'custom_logo' ),
					'selector'            => '.site-footer-brand-logo',
					'container_inclusive' => true,
					'render_callback'     => array( self::class, 'partial_footer_logo' ),
				)
			);
		}
	}

	/**
	 * Echo footer logo block for selective refresh (matches footer markup).
	 */
	public static function partial_footer_logo(): void {
		echo '<div class="site-footer-brand-logo w-16 md:w-18 shrink-0">';
		dba_the_site_logo( 'w-full h-auto object-contain brightness-90 contrast-95 sepia-22 saturate-40', true );
		echo '</div>';
	}

	/**
	 * Sanitize image attachment ID for theme mods.
	 *
	 * @param mixed $value Raw setting value.
	 * @return int Attachment ID or 0.
	 */
	public static function sanitize_attachment_id_image( $value ): int {
		$id = absint( $value );
		if ( $id <= 0 ) {
			return 0;
		}

		return wp_attachment_is_image( $id ) ? $id : 0;
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
