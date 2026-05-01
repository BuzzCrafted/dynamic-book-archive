<?php
/**
 * Book archive header + toolbar (args-only).
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

if ( ! isset( $args ) || ! is_array( $args ) ) {
	return;
}

$title = isset( $args['title'] ) && is_string( $args['title'] ) ? $args['title'] : __( 'Library', 'dynamic-book-archive' );

$options = isset( $args['options'] ) && is_array( $args['options'] ) ? $args['options'] : array();
$authors = isset( $options['authors'] ) && is_array( $options['authors'] ) ? $options['authors'] : array();
$tags    = isset( $options['tags'] ) && is_array( $options['tags'] ) ? $options['tags'] : array();
$years   = isset( $options['years'] ) && is_array( $options['years'] ) ? $options['years'] : array();

$year_floor   = isset( $years['floor'] ) ? (int) $years['floor'] : 1900;
$year_ceiling = isset( $years['ceiling'] ) ? (int) $years['ceiling'] : (int) gmdate( 'Y' );

?>
<div class="js-book-archive-stage js-book-archive-stage-head contain-[layout] backface-hidden">
	<div class="mx-auto flex flex-col">
		<header class="flex min-h-0 flex-col justify-center transition-all transition-discrete">
			<h1 class="js-book-archive-title py-4 text-center text-3xl font-semibold uppercase tracking-tight text-heading group-aria-busy:pointer-events-none">
				<span class="js-book-archive-title-value inline-block min-w-0"><?php echo esc_html( $title ); ?></span>
			</h1>
			<?php
			dba_the_book_archive_intro(
				array(
					'page_header_embed'   => true,
					'intro_wrapper_class' => 'js-book-archive-intro flex min-h-0 min-w-0 items-center pb-4 justify-center text-center text-lg group-aria-busy:pointer-events-none',
				)
			);
			?>
		</header>

		<?php
		get_template_part(
			'template-parts/book/archive/toolbar',
			null,
			array(
				'authors'       => $authors,
				'tags'          => $tags,
				'year_floor'    => $year_floor,
				'year_ceiling'  => $year_ceiling,
			)
		);
		?>
	</div>
</div>

