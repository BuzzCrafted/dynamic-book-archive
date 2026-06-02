<?php
/**
 * Single historical document page (args-only composition root).
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

?>
<article id="post-<?php echo esc_attr( (string) $post_id ); ?>" <?php post_class( 'js-archive-document' ); ?>>

	<?php get_template_part( 'template-parts/archive/document/back-link', null, $args ); ?>

	<div class="grid gap-8 lg:gap-12">
		<?php get_template_part( 'template-parts/archive/document/header', null, $args ); ?>
		<?php get_template_part( 'template-parts/archive/document/description', null, $args ); ?>
		<?php get_template_part( 'template-parts/archive/document/viewer', null, $args ); ?>
	</div>

</article>
