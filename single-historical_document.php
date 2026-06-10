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

		$document_type = isset( $vm['document_type'] ) && is_array( $vm['document_type'] ) ? $vm['document_type'] : array();
		$type_slug     = isset( $document_type['slug'] ) && is_string( $document_type['slug'] ) ? $document_type['slug'] : '';

		get_template_part( 'template-parts/historical-document/single/page', $type_slug, $vm );
	endwhile;
	?>
</main>

<?php
get_footer();
