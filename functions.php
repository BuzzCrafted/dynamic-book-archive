<?php
/**
 * Dynamic Book Archive functions and definitions
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

use DBA\Theme\Theme_Bootstrap;

if ( ! defined( 'DBA_VERSION' ) ) {
	define( 'DBA_VERSION', '1.1.0-alpha-009' );
}

$dba_autoload = get_template_directory() . '/vendor/autoload.php';
if ( is_readable( $dba_autoload ) ) {
	require_once $dba_autoload;
	Theme_Bootstrap::init();
} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
	trigger_error(
		'Dynamic Book Archive: run `composer install` in the theme directory (vendor/autoload.php missing).',
		E_USER_WARNING
	);
}
