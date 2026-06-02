<?php
/**
 * Book archive page (args-only composition).
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

if ( ! isset( $args ) || ! is_array( $args ) ) {
	return;
}

$context       = isset( $args['context'] ) && is_array( $args['context'] ) ? $args['context'] : array();
$initial_state = isset( $args['initial_state'] ) && is_array( $args['initial_state'] ) ? $args['initial_state'] : array();
$options       = isset( $args['options'] ) && is_array( $args['options'] ) ? $args['options'] : array();
$results       = isset( $args['results'] ) && is_array( $args['results'] ) ? $args['results'] : array();

$title = isset( $context['title'] ) && is_string( $context['title'] ) ? $context['title'] : __( 'Library', 'dynamic-book-archive' );
$paged = isset( $context['paged'] ) ? max( 1, (int) $context['paged'] ) : 1;

$category = isset( $context['category'] ) && is_array( $context['category'] ) ? $context['category'] : array();
$category_id   = isset( $category['id'] ) ? (int) $category['id'] : 0;
$category_slug = isset( $category['slug'] ) && is_string( $category['slug'] ) ? $category['slug'] : '';

$category_attr = $category_id > 0 ? (string) $category_id : '';

$search  = isset( $initial_state['search'] ) && is_string( $initial_state['search'] ) ? $initial_state['search'] : '';
$orderby = isset( $initial_state['orderby'] ) && is_string( $initial_state['orderby'] ) ? $initial_state['orderby'] : 'date';
$order   = isset( $initial_state['order'] ) && is_string( $initial_state['order'] ) ? $initial_state['order'] : 'desc';
$author  = isset( $initial_state['author'] ) && is_string( $initial_state['author'] ) ? $initial_state['author'] : '';
$tag     = isset( $initial_state['tag'] ) && is_string( $initial_state['tag'] ) ? $initial_state['tag'] : '';
$year    = isset( $initial_state['year'] ) && is_array( $initial_state['year'] ) ? $initial_state['year'] : array();

$year_floor   = isset( $year['floor'] ) ? (int) $year['floor'] : 1900;
$year_ceiling = isset( $year['ceiling'] ) ? (int) $year['ceiling'] : (int) gmdate( 'Y' );
$year_min     = isset( $year['min'] ) ? (int) $year['min'] : 0;
$year_max     = isset( $year['max'] ) ? (int) $year['max'] : 0;

$has_posts = isset( $results['has_posts'] ) ? (bool) $results['has_posts'] : false;
$items     = isset( $results['items'] ) && is_array( $results['items'] ) ? $results['items'] : array();

?>
<div class="mx-auto pb-4 md:pb-8 lg:pb-16 flex w-full flex-1 flex-col md:flex-row">
	<main
		id="primary"
		class="js-book-archive group flex-1"
		data-book-archive-root
		x-data="archiveToolbar()"
		@books-cpt-archive-toolbar-synced="onToolbarSynced()"
		data-books-cpt-category="<?php echo esc_attr( $category_attr ); ?>"
		data-books-cpt-category-slug="<?php echo esc_attr( $category_slug ); ?>"
		data-books-cpt-page="<?php echo esc_attr( (string) $paged ); ?>"
		data-books-cpt-search="<?php echo esc_attr( $search ); ?>"
		data-books-cpt-orderby="<?php echo esc_attr( $orderby ); ?>"
		data-books-cpt-order="<?php echo esc_attr( $order ); ?>"
		data-books-cpt-author="<?php echo esc_attr( $author ); ?>"
		data-books-cpt-tag="<?php echo esc_attr( $tag ); ?>"
		data-books-cpt-year-floor="<?php echo esc_attr( (string) $year_floor ); ?>"
		data-books-cpt-year-ceil="<?php echo esc_attr( (string) $year_ceiling ); ?>"
		data-books-cpt-year-min="<?php echo esc_attr( (string) $year_min ); ?>"
		data-books-cpt-year-max="<?php echo esc_attr( (string) $year_max ); ?>">

		<?php
		get_template_part(
			'template-parts/book/archive/header',
			null,
			array(
				'title'       => $title,
				'options'     => $options,
				'search'      => $search,
				'orderby'     => $orderby,
				'order'       => $order,
				'author'      => $author,
				'tag'         => $tag,
				'year_min'    => $year_min,
				'year_max'    => $year_max,
				'year_floor'  => $year_floor,
				'year_ceiling' => $year_ceiling,
			)
		);
		?>

		<?php dba_the_book_archive_category_nav(); ?>

		<div class="js-book-archive-stage js-book-archive-stage-body contain-[layout] backface-hidden">
			<?php if ( $has_posts ) : ?>
				<?php
				get_template_part(
					'template-parts/book/archive/results-grid',
					null,
					array(
						'items' => $items,
					)
				);
				?>

				<div class="js-book-archive-pagination mt-2 md:mt-4 lg:mt-7 mb-2 md:mb-4 lg:mb-10 group-aria-busy:pointer-events-none">
					<?php dba_the_book_pagination(); ?>
				</div>
			<?php else : ?>
				<div class="js-book-archive-grid">
					<?php get_template_part( 'template-parts/content/none' ); ?>
				</div>
			<?php endif; ?>
		</div>
	</main>
</div>

