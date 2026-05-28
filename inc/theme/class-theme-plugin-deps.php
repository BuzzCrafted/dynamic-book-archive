<?php
/**
 * Required plugin checks and admin notices.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\Theme;

/**
 * Surfaces missing theme dependencies in wp-admin.
 */
final class Theme_Plugin_Deps {

	public static function register_hooks(): void {
		add_action( 'admin_notices', array( self::class, 'maybe_notice_dko_lightbox_plugin' ) );
	}

	public static function is_dko_lightbox_available(): bool {
		if ( function_exists( 'dko_elementor_widgets_enqueue_lightbox_assets' ) ) {
			return true;
		}

		return class_exists( \DKO_Elementor_Widgets_Plugin::class )
			&& class_exists( \DKO_Elementor_Widgets_Asset_Manager::class );
	}

	public static function maybe_notice_dko_lightbox_plugin(): void {
		if ( ! current_user_can( 'activate_plugins' ) || self::is_dko_lightbox_available() ) {
			return;
		}

		$message = sprintf(
			/* translators: %s: bold plugin name. */
			esc_html__(
				'%s requires the DKO Elementor Widgets plugin for the single book gallery lightbox. Install and activate the plugin, then run `npm run build` inside the plugin folder so lightbox assets are present.',
				'dynamic-book-archive'
			),
			'<strong>' . esc_html__( 'Dynamic Book Archive', 'dynamic-book-archive' ) . '</strong>'
		);

		printf( '<div class="notice notice-warning"><p>%s</p></div>', wp_kses_post( $message ) );
	}
}
