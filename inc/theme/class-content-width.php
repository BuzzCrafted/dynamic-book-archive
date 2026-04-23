<?php
/**
 * $content_width for embeds and media.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\Theme;

/**
 * Sets content width on after_setup_theme priority 0.
 */
final class Content_Width {

	public static function register_hooks(): void {
		add_action( 'after_setup_theme', array( self::class, 'set_content_width' ), 0 );
	}

	public static function set_content_width(): void {
		$GLOBALS['content_width'] = apply_filters( 'dba_content_width', 720 );
	}
}
