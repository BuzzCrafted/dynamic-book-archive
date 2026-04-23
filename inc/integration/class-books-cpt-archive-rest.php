<?php
/**
 * Renders books-cpt archive REST HTML fragments using the same templates as the main archive.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\Integration;

use DBA\TemplateTags\Breadcrumb_Trail;
use \WP_Query;
use \WP_Term;

/**
 * Hooks books-cpt plugin filters for AJAX/REST archive parity.
 */
final class Books_Cpt_Archive_Rest {

	public static function register_hooks(): void {
		add_filter( 'books_cpt_render_archive_title_html', array( self::class, 'filter_title_html' ), 10, 3 );
		add_filter( 'books_cpt_render_archive_intro_html', array( self::class, 'filter_intro_html' ), 10, 3 );
		add_filter( 'books_cpt_render_archive_grid_html', array( self::class, 'filter_grid_html' ), 10, 3 );
		add_filter( 'books_cpt_render_archive_pagination_html', array( self::class, 'filter_pagination_html' ), 10, 3 );
		add_filter( 'books_cpt_archive_canonical_url', array( self::class, 'filter_canonical_url' ), 10, 3 );
		add_filter( 'books_cpt_archive_rest_response', array( self::class, 'filter_rest_response_breadcrumb_html' ), 10, 3 );
		add_filter( 'books_cpt_archive_filter_script_data', array( self::class, 'filter_script_breadcrumb_selector' ) );
	}

	/**
	 * @param array<string, mixed> $payload Response payload.
	 */
	public static function filter_rest_response_breadcrumb_html( array $payload, WP_Query $query, ?WP_Term $term ): array {
		unset( $query );
		$items = Breadcrumb_Trail::get_book_post_type_breadcrumb_items( $term );

		$payload['breadcrumb_html']  = Breadcrumb_Trail::render_breadcrumbs_markup( $items );
		$payload['active_term_slug'] = $term instanceof WP_Term ? (string) $term->slug : '';

		return $payload;
	}

	/**
	 * @param array<string, mixed> $data Localized archive-filter script data.
	 * @return array<string, mixed>
	 */
	public static function filter_script_breadcrumb_selector( array $data ): array {
		if ( ! isset( $data['selectors'] ) || ! is_array( $data['selectors'] ) ) {
			return $data;
		}
		$data['selectors']['breadcrumbs'] = 'nav.breadcrumbs';

		if ( function_exists( 'dba_get_book_archive_distinct_publication_years' ) ) {
			$data['archiveYears'] = dba_get_book_archive_distinct_publication_years();
		}

		return $data;
	}

	/**
	 * @param string       $html  Default empty.
	 * @param \WP_Query    $query Archive query.
	 * @param \WP_Term|null $term Active book_category or null.
	 */
	public static function filter_title_html( string $html, WP_Query $query, ?WP_Term $term ): string {
		unset( $query );
		$label = __( 'Library', 'dynamic-book-archive' );
		if ( $term instanceof WP_Term ) {
			$label = sprintf(
				/* translators: 1: category name, 2: static label "Library". */
				__( '%1$s %2$s', 'dynamic-book-archive' ),
				$term->name,
				$label
			);
		}
		return esc_html( $label );
	}

	/**
	 * @param string        $html  Default empty.
	 * @param \WP_Query     $query Archive query.
	 * @param \WP_Term|null $term  Active book_category or null.
	 */
	public static function filter_intro_html( string $html, WP_Query $query, ?WP_Term $term ): string {
		unset( $query );
		/**
		 * Filters the fallback intro copy when no category is selected or the category has no description.
		 *
		 * @param string $text Plain text (escaped when rendered).
		 */
		$default_text = apply_filters(
			'dba_book_archive_intro_default_text',
			__( 'The collection of rare books about martial arts.', 'dynamic-book-archive' )
		);
		$default_text = is_string( $default_text ) ? $default_text : '';
		$intro_inner  = '';

		if ( $term instanceof WP_Term ) {
			$desc = term_description( $term, $term->taxonomy );
			if ( is_string( $desc ) && '' !== trim( wp_strip_all_tags( $desc ) ) ) {
				$intro_inner = '<div class="book-archive-intro__description">' . wp_kses_post( $desc ) . '</div>';
			}
		}

		if ( '' === $intro_inner ) {
			$intro_inner = '<p class="book-archive-intro__default">' . esc_html( $default_text ) . '</p>';
		}

		$cat_id      = $term instanceof WP_Term ? (int) $term->term_id : 0;
		$intro_inner = apply_filters( 'dba_book_archive_intro_html', $intro_inner, $cat_id );
		if ( ! is_string( $intro_inner ) || '' === $intro_inner ) {
			return '';
		}

		ob_start();
		set_query_var( 'dba_book_archive_intro_html', $intro_inner );
		get_template_part(
			'template-parts/book/archive/intro',
			null,
			array(
				'intro_html' => $intro_inner,
			)
		);
		return (string) ob_get_clean();
	}

	/**
	 * @param string        $html  Default empty.
	 * @param \WP_Query     $query Archive query.
	 * @param \WP_Term|null $term  Active book_category or null.
	 */
	public static function filter_grid_html( string $html, WP_Query $query, ?WP_Term $term ): string {
		unset( $term );
		ob_start();
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				get_template_part( 'template-parts/content', 'archive-book' );
			}
		} else {
			get_template_part( 'template-parts/content/none' );
		}
		wp_reset_postdata();
		return (string) ob_get_clean();
	}

	/**
	 * @param string        $html  Default empty.
	 * @param \WP_Query     $query Archive query.
	 * @param \WP_Term|null $term  Active book_category or null.
	 */
	public static function filter_pagination_html( string $html, WP_Query $query, ?WP_Term $term ): string {
		$total   = (int) $query->max_num_pages;
		$current = max( 1, (int) $query->get( 'paged' ) );
		if ( $total <= 1 ) {
			return '';
		}

		unset( $term );
		$archive = \dba_get_book_post_type_archive_url();
		if ( ! is_string( $archive ) || '' === $archive ) {
			return '';
		}
		$base   = esc_url_raw( trailingslashit( untrailingslashit( $archive ) ) ) . '%_%';
		$format = 'page/%#%/';

		$links = paginate_links(
			array(
				'base'      => $base,
				'format'    => $format,
				'total'     => $total,
				'current'   => $current,
				'type'      => 'array',
				'mid_size'  => 2,
				'end_size'  => 1,
				'prev_text' => __( '< Previous', 'dynamic-book-archive' ),
				'next_text' => __( 'Next >', 'dynamic-book-archive' ),
			)
		);

		if ( ! is_array( $links ) ) {
			return '';
		}

		$prev_html = '';
		$next_html = '';
		$numbers   = array();

		foreach ( $links as $link ) {
			if ( ! is_string( $link ) ) {
				continue;
			}
			if ( str_contains( $link, 'prev page-numbers' ) ) {
				$prev_html = $link;
				continue;
			}
			if ( str_contains( $link, 'next page-numbers' ) ) {
				$next_html = $link;
				continue;
			}
			$numbers[] = $link;
		}

		$pagination = array(
			'prev_html' => $prev_html,
			'next_html' => $next_html,
			'numbers'   => $numbers,
		);

		ob_start();
		set_query_var( 'dba_book_pagination', $pagination );
		get_template_part(
			'template-parts/book/archive/pagination',
			null,
			array(
				'pagination' => $pagination,
			)
		);
		return (string) ob_get_clean();
	}

	/**
	 * Keep REST “canonical” on the book post type archive (pagination only, no `book_cat`), not taxonomy permalinks.
	 *
	 * @param string        $url   Plugin-default URL.
	 * @param \WP_Term|null $term  book_category term or null.
	 * @param int           $paged Page number (>= 1).
	 */
	public static function filter_canonical_url( string $url, ?WP_Term $term, int $paged ): string {
		unset( $url );
		$paged = max( 1, $paged );
		return \dba_get_book_post_type_archive_filter_url( $term, $paged );
	}
}
