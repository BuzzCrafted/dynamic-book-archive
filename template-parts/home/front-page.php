<?php
/**
 * Front page sections: hero, recent books, then static page or blog loop.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		the_content();
	}
}
