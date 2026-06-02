<?php
/**
 * Template for a single `historical_document` post.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

use DBA\Presenters\Historical_Document_Single_Presenter;

get_header();
?>

<main id="primary" class="flex-1 w-full min-w-0 pb-16">
	<?php
	while ( have_posts() ) :
		the_post();
		$post_id = get_the_ID();
		$vm      = Historical_Document_Single_Presenter::build_from_post_id( (int) $post_id );
		get_template_part( 'template-parts/archive/document/page', null, $vm );
	endwhile;
	?>
</main>

<?php
get_footer();
