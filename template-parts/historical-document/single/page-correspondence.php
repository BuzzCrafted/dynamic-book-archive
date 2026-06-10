<?php
/**
 * Single historical document page — Correspondence document type.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

if ( ! isset( $args ) || ! is_array( $args ) ) {
	return;
}

$post_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : 0;
if ( $post_id <= 0 ) {
	return;
}

// Defer to Elementor Theme Builder when a Single location template is assigned.
// Class lives in archive-cpt plugin (optional external dependency — string-based call).
$elementor_class = 'DKO\\Archive\\Controller\\Elementor_Integration_Controller';
if ( class_exists( $elementor_class ) && (bool) call_user_func( array( $elementor_class, 'did_location' ), 'single' ) ) {
	return;
}

// Build Alpine bootstrap config here so both header (view buttons) and viewer
// (panels, image) share one Alpine scope rooted at the grid wrapper.
$viewer_config = wp_json_encode(
	array(
		'documentId'    => $post_id,
		'pagesEndpoint' => esc_url_raw( rest_url( 'archive-cpt/v1/documents/' . $post_id . '/pages' ) ),
	)
);

?>
<article id="post-<?php echo esc_attr( (string) $post_id ); ?>" <?php post_class( 'js-archive-document' ); ?>>

	<?php get_template_part( 'template-parts/historical-document/single/back-link', null, $args ); ?>

	<div
		class="archive-document-viewer grid gap-8 lg:gap-12"
		data-document-id="<?php echo (int) $post_id; ?>"
		data-config="<?php echo esc_attr( (string) $viewer_config ); ?>"
		x-data="Object.assign( archiveDocumentViewer(), { activeTab: 'translation', viewMode: 'both' } )"
		x-init="init()"
	>
		<?php get_template_part( 'template-parts/historical-document/single/header', 'correspondence', $args ); ?>
		<?php get_template_part( 'template-parts/historical-document/single/view-selector', null, $args ); ?>
		<?php get_template_part( 'template-parts/historical-document/single/description', null, $args ); ?>
		<?php get_template_part( 'template-parts/historical-document/single/viewer', null, $args ); ?>
	</div>

</article>
