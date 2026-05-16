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
$search  = isset( $args['search'] ) && is_string( $args['search'] ) ? $args['search'] : '';
$authors = isset( $options['authors'] ) && is_array( $options['authors'] ) ? $options['authors'] : array();
$tags    = isset( $options['tags'] ) && is_array( $options['tags'] ) ? $options['tags'] : array();
$years   = isset( $options['years'] ) && is_array( $options['years'] ) ? $options['years'] : array();

$year_floor   = isset( $args['year_floor'] ) ? (int) $args['year_floor'] : ( isset( $years['floor'] ) ? (int) $years['floor'] : 1900 );
$year_ceiling = isset( $args['year_ceiling'] ) ? (int) $args['year_ceiling'] : ( isset( $years['ceiling'] ) ? (int) $years['ceiling'] : (int) gmdate( 'Y' ) );

$orderby = isset( $args['orderby'] ) && is_string( $args['orderby'] ) ? strtolower( $args['orderby'] ) : 'date';
$order   = isset( $args['order'] ) && is_string( $args['order'] ) ? strtolower( trim( $args['order'] ) ) : 'desc';
if ( 'asc' !== $order && 'desc' !== $order ) {
	$order = in_array( $orderby, array( 'title', 'author' ), true ) ? 'asc' : 'desc';
}
if ( ! in_array( $orderby, array( 'author', 'date', 'title', 'pubdate' ), true ) ) {
	$orderby = 'date';
	$order   = 'desc';
}
$author_filter = isset( $args['author'] ) && is_string( $args['author'] ) ? $args['author'] : '';
$tag_filter    = isset( $args['tag'] ) && is_string( $args['tag'] ) ? $args['tag'] : '';
$year_min_arg  = isset( $args['year_min'] ) ? (int) $args['year_min'] : 0;
$year_max_arg  = isset( $args['year_max'] ) ? (int) $args['year_max'] : 0;

$sort_options = array(
	array( 'value' => 'author:asc', 'label' => __( 'Author', 'dynamic-book-archive' ) ),
	array( 'value' => 'date:desc', 'label' => __( 'Date', 'dynamic-book-archive' ) ),
	array( 'value' => 'title:asc', 'label' => __( 'Title', 'dynamic-book-archive' ) ),
);
$current_sort = $orderby . ':' . $order;
$sort_values  = array_map(
	static function ( array $o ): string {
		return isset( $o['value'] ) && is_string( $o['value'] ) ? $o['value'] : '';
	},
	$sort_options
);
if ( ! in_array( $current_sort, $sort_values, true ) && in_array( $orderby, array( 'author', 'date', 'title', 'pubdate' ), true ) ) {
	$extra_label = 'pubdate' === $orderby
		? __( 'Publication date', 'dynamic-book-archive' )
		: __( 'Date', 'dynamic-book-archive' );
	$sort_options[] = array(
		'value' => $current_sort,
		'label' => $extra_label,
	);
}
foreach ( $sort_options as $k => $row ) {
	if ( ! is_array( $row ) ) {
		continue;
	}
	$v = isset( $row['value'] ) && is_string( $row['value'] ) ? $row['value'] : '';
	$sort_options[ $k ]['selected'] = ( $v === $current_sort );
}

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
				'authors'          => $authors,
				'tags'             => $tags,
				'year_floor'       => $year_floor,
				'year_ceiling'     => $year_ceiling,
				'year_value_min'   => $year_min_arg,
				'year_value_max'   => $year_max_arg,
				'search'           => $search,
				'sort_options'     => $sort_options,
				'selected_author'  => $author_filter,
				'selected_tag'     => $tag_filter,
			)
		);
		?>
	</div>
</div>

<?php
get_template_part( 'template-parts/book/archive/dialog-sort', null, array( 'sort_options' => $sort_options ) );
get_template_part(
	'template-parts/book/archive/dialog-filter',
	null,
	array(
		'year_floor'       => $year_floor,
		'year_ceiling'     => $year_ceiling,
		'year_value_min'   => $year_min_arg,
		'year_value_max'   => $year_max_arg,
	)
);
?>

