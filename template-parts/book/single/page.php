<?php
/**
 * Single book page (args-only composition).
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

?>
<article id="post-<?php echo esc_attr( (string) $post_id ); ?>" <?php post_class( 'text-white/90' ); ?>>
	<?php get_template_part( 'template-parts/book/single/back-link', null, $args ); ?>
	<div class="grid gap-1 md:gap-2 lg:gap-6 lg:grid-cols-[3fr_4fr] sm:grid-cols-1">
		<?php get_template_part( 'template-parts/book/single/gallery', null, $args ); ?>

		<div class="flex min-w-0 flex-col gap-4 md:gap-6 lg:gap-12">
			<?php get_template_part( 'template-parts/book/single/titles', null, $args ); ?>
			<?php get_template_part( 'template-parts/book/single/meta-and-quick', null, $args ); ?>
			<?php get_template_part( 'template-parts/book/single/description', null, $args ); ?>
			<?php get_template_part( 'template-parts/book/single/edition-details', null, $args ); ?>
			<?php get_template_part( 'template-parts/book/single/tags', null, $args ); ?>
		</div>
	</div>
</article>

