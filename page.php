<?php

/**
 * The template for displaying all pages
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

get_header();
?>


<main id="primary" class="site-main flex-1">

	<?php
	while (have_posts()) :
		the_post();
		get_template_part('template-parts/content/page');
	endwhile;
	?>
</main>

<?php
get_footer();
