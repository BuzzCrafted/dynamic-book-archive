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

use DBA\Presenters\Book_Archive_Page_Presenter;

get_header();
$vm = Book_Archive_Page_Presenter::build();
get_template_part( 'template-parts/book/archive/page', null, $vm );
get_footer();
