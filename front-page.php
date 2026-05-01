<?php

/**
 * Template for the site front page (static page or latest posts).
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

get_header();
?>


<main id="primary" class="site-main min-w-0 flex-1">
	<?php get_template_part('template-parts/home/front-page'); ?>
</main>


<?php
get_footer();
