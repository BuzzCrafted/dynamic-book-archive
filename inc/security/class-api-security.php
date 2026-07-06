<?php
/**
 * REST and XML-RPC hardening.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\Security;

/**
 * Blocks public user enumeration via REST and disables XML-RPC.
 */
final class Api_Security {

	public static function register_hooks(): void {
		add_filter( 'rest_endpoints', array( self::class, 'restrict_public_user_endpoints' ) );
		add_filter( 'xmlrpc_enabled', '__return_false' );
		add_filter( 'wp_headers', array( self::class, 'remove_pingback_header' ) );
		remove_action( 'wp_head', 'rsd_link' );
		add_action( 'init', array( self::class, 'block_xmlrpc_requests' ), 1 );
		add_filter( 'wp_xmlrpc_server_class', array( self::class, 'abort_xmlrpc_server_bootstrap' ) );
	}

	/**
	 * Reject all requests to xmlrpc.php before any method is served.
	 */
	public static function block_xmlrpc_requests(): void {
		if ( ! defined( 'XMLRPC_REQUEST' ) || ! XMLRPC_REQUEST ) {
			return;
		}

		self::send_xmlrpc_disabled_response();
	}

	/**
	 * Last-chance block when xmlrpc.php bootstraps the XML-RPC server class.
	 *
	 * @param string $class XML-RPC server class name.
	 * @return string
	 */
	public static function abort_xmlrpc_server_bootstrap( string $class ): string {
		self::send_xmlrpc_disabled_response();

		return $class;
	}

	private static function send_xmlrpc_disabled_response(): void {
		status_header( 403 );
		nocache_headers();
		header( 'Content-Type: text/plain; charset=UTF-8' );
		echo esc_html__( 'XML-RPC services are disabled on this site.', 'dynamic-book-archive' );
		exit;
	}

	/**
	 * @param array<string, string> $headers Response headers.
	 * @return array<string, string>
	 */
	public static function remove_pingback_header( array $headers ): array {
		unset( $headers['X-Pingback'] );

		return $headers;
	}

	/**
	 * Remove user list and user-by-id REST routes for anonymous visitors.
	 *
	 * @param array<string, mixed> $endpoints Registered REST routes.
	 * @return array<string, mixed>
	 */
	public static function restrict_public_user_endpoints( array $endpoints ): array {
		if ( is_user_logged_in() ) {
			return $endpoints;
		}

		unset( $endpoints['/wp/v2/users'] );
		unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );

		return $endpoints;
	}
}
