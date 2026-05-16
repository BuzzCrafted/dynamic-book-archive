<?php
/**
 * Renders books-cpt archive REST HTML fragments using the same templates as the main archive.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\Integration;

use DBA\Presenters\Breadcrumb_Items_Presenter;
use DBA\Presenters\Book_Card_Presenter;
use DBA\TemplateTags\Breadcrumb_Renderer;
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
		$items = Breadcrumb_Items_Presenter::for_book_archive( $term );

		$payload['breadcrumb_html']  = Breadcrumb_Renderer::render_markup( $items );
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
		/*
		 * books-cpt archive-filter scopes grid/title/pagination updates under busyRoot.
		 * `.js-book-archive` alone is unsafe: another node could match first and contain no
		 * `.js-book-archive-grid`, so REST grid_html never replaces the visible SSR grid.
		 */
		$data['selectors']['busyRoot'] = 'main[data-book-archive-root]';

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
			foreach ( $query->posts as $p ) {
				$post_id = isset( $p->ID ) ? (int) $p->ID : 0;
				if ( $post_id > 0 ) {
					get_template_part( 'template-parts/book/archive/card', null, Book_Card_Presenter::from_post_id( $post_id ) );
				}
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

		$archive = \dba_get_book_post_type_archive_url();
		if ( ! is_string( $archive ) || '' === $archive ) {
			return '';
		}

		$extra_add_args = array();
		if (
			class_exists( '\DKO\Books\Service\Books_Archive_Filter_Controller' )
			&& class_exists( '\DKO\Books\Service\Books_Archive_Query_Service' )
		) {
			$ctx = \DKO\Books\Service\Books_Archive_Filter_Controller::get_current_archive_filters_for_response();
			if ( is_array( $ctx ) ) {
				$extra_add_args = \DKO\Books\Service\Books_Archive_Query_Service::public_book_archive_url_query_args( $ctx );
			}
		}

		$paginate_args = null;
		if ( $term instanceof WP_Term && \DBA_BOOK_CATEGORY_TAXONOMY === $term->taxonomy ) {
			$tlink = get_term_link( $term );
			if ( ! is_wp_error( $tlink ) && is_string( $tlink ) && '' !== $tlink ) {
				$base          = esc_url_raw( trailingslashit( untrailingslashit( $tlink ) ) ) . '%_%';
				$format        = 'page/%#%/';
				$paginate_args = dba_get_book_archive_paginate_links_args( $total, $current, $base, $format, $extra_add_args );
			}
		}

		if ( null === $paginate_args ) {
			$base          = esc_url_raw( trailingslashit( untrailingslashit( $archive ) ) ) . '%_%';
			$format        = 'page/%#%/';
			$paginate_args = dba_get_book_archive_paginate_links_args( $total, $current, $base, $format, $extra_add_args );
		}

		$clean_pagenum_link = static function ( $result, $pagenum ) use ( $term, $archive ): string {
			unset( $result, $pagenum );
			if ( $term instanceof WP_Term && \DBA_BOOK_CATEGORY_TAXONOMY === $term->taxonomy ) {
				$url = get_term_link( $term );
				if ( ! is_wp_error( $url ) && is_string( $url ) && '' !== $url ) {
					return untrailingslashit( esc_url_raw( $url ) );
				}
			}

			return untrailingslashit( esc_url_raw( (string) $archive ) );
		};
		add_filter( 'get_pagenum_link', $clean_pagenum_link, 1, 2 );
		try {
			$pagination = \DBA\Presenters\Pagination_Presenter::build_from_paginate_links_args(
				$paginate_args
			);
		} finally {
			remove_filter( 'get_pagenum_link', $clean_pagenum_link, 1 );
		}

		if ( ! is_array( $pagination ) ) {
			return '';
		}

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
	 * REST canonical URL: plain `/book/` for all books, otherwise the `book_category` term permalink.
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
