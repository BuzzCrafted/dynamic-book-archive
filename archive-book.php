<?php

/**
 * Template for the book custom post type archive.
 *
 * Expects the CPT slug `book` (WordPress resolves this file as archive-{post_type}.php).
 * Registration and rewrites are handled by your plugin.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

get_header();
get_template_part( 'template-parts/library', 'books-archive' );
get_template_part( 'template-parts/book/archive/layout' );
get_footer();
