<?php
/**
 * Allow SVG / SVGZ in the media library.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\Media;

/**
 * MIME and filetype filters for SVG uploads.
 */
final class Svg_Upload {

	public static function register_hooks(): void {
		add_filter( 'upload_mimes', array( self::class, 'allow_svg_mimes' ), 99999 );
		add_filter( 'wp_check_filetype_and_ext', array( self::class, 'restore_svg_filetype' ), 99999, 5 );
	}

	/**
	 * High priority runs after multisite {@see check_upload_mimes} and after typical plugin filters.
	 *
	 * @param array<string, string> $mimes Mime types keyed by file extension regex.
	 * @return array<string, string>
	 */
	public static function allow_svg_mimes( $mimes ): array {
		if ( ! is_array( $mimes ) ) {
			$mimes = array();
		}
		$mimes['svg']  = 'image/svg+xml';
		$mimes['svgz'] = 'image/svg+xml';
		return $mimes;
	}

	/**
	 * Restore SVG when core clears ext/type (e.g. finfo vs declared MIME) or when upload uses a restrictive $mimes list.
	 *
	 * Extension checks must be case-insensitive: {@see wp_check_filetype()} preserves the case of the
	 * filename suffix (e.g. `logo.SVG` yields ext `SVG`), so a strict `=== 'svg'` check never matched.
	 *
	 * @param array<string, mixed>       $data     File data.
	 * @param string                     $file     Full path to the file.
	 * @param string                     $filename File name.
	 * @param string[]|array<string, string>|null $mimes    Mime types.
	 * @param string|false|null          $real_mime  Real MIME type.
	 * @return array<string, mixed>
	 */
	public static function restore_svg_filetype( $data, $file, $filename, $mimes, $real_mime = null ): array {
		if ( ! is_array( $data ) ) {
			$data = array();
		}
		if ( ! empty( $data['ext'] ) && ! empty( $data['type'] ) ) {
			return $data;
		}
		$filetype = wp_check_filetype( $filename, $mimes );
		$ext_norm = is_string( $filetype['ext'] ) ? strtolower( $filetype['ext'] ) : '';
		if ( '' === $ext_norm || ( 'svg' !== $ext_norm && 'svgz' !== $ext_norm ) ) {
			$filetype = wp_check_filetype( $filename, null );
			$ext_norm = is_string( $filetype['ext'] ) ? strtolower( $filetype['ext'] ) : '';
		}
		if ( 'svg' === $ext_norm || 'svgz' === $ext_norm ) {
			$data['ext']  = $ext_norm;
			$data['type'] = 'image/svg+xml';
		}
		return $data;
	}
}
