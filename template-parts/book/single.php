<?php
/**
 * Legacy single-book template part.
 *
 * Prefer `template-parts/book/single/page.php` with an explicit view model.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

if ( ! isset( $args ) || ! is_array( $args ) ) {
	return;
}

get_template_part( 'template-parts/book/single/page', null, $args );
