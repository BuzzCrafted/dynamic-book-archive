<?php
/**
 * Intro / tagline for the book archive (default or category description).
 *
 * Loaded via {@see Book_Archive_Intro::render()}. Expects prepared HTML in
 * `$args['intro_html']` or query var `dba_book_archive_intro_html`.
 *
 * @package Dynamic_Book_Archive
 *
 * @var array<string, mixed> $args Arguments from get_template_part().
 */

declare(strict_types=1);

$intro_html = '';

if ( isset( $args ) && is_array( $args ) && isset( $args['intro_html'] ) && is_string( $args['intro_html'] ) ) {
	$intro_html = $args['intro_html'];
} else {
	$qv         = get_query_var( 'dba_book_archive_intro_html', '' );
	$intro_html = is_string( $qv ) ? $qv : '';
}

if ( '' === $intro_html ) {
	return;
}

$page_header_embed = isset( $args['page_header_embed'] ) && $args['page_header_embed'];
$intro_wrapper_class = 'col-span-12 text-center text-lg book-archive-intro transition-all transition-discrete';
if ( isset( $args['intro_wrapper_class'] ) && is_string( $args['intro_wrapper_class'] ) && '' !== $args['intro_wrapper_class'] ) {
	$intro_wrapper_class = $args['intro_wrapper_class'];
}

if ( $page_header_embed ) {
	?>
	<div class="<?php echo esc_attr( $intro_wrapper_class ); ?>">
		<?php echo $intro_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in Book_Archive_Intro::render(). ?>
	</div>
	<?php
	return;
}

?>
<div class="grid grid-cols-12 mx-auto md:items-center lg:gap-6">
	<div class="col-span-12 text-center text-lg book-archive-intro">
		<?php echo $intro_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in Book_Archive_Intro::render(). ?>
	</div>
</div>
