<?php
/**
 * Breadcrumb item building.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\TemplateTags;

use WP_Post;
use WP_Post_Type;
use WP_Term;
use WP_User;

/**
 * Builds label/URL crumb arrays.
 */
final class Breadcrumb_Trail {

	/**
	 * Applies pagination to the last crumb URL and exposes items for filtering.
	 *
	 * @param array<int, array{label: string, url: string}> $items Breadcrumb items.
	 * @return array<int, array{label: string, url: string}>
	 */
	/**
	 * Full trail for the book post type archive (optional active category for labels; URLs stay on the plain archive).
	 *
	 * @param \WP_Term|null $filter_term Active `book_category` term, or null for all books.
	 * @return array<int, array{label: string, url: string}>
	 */
	public static function get_book_post_type_breadcrumb_items( ?WP_Term $filter_term ): array {
		$items   = array();
		$items[] = array(
			'label' => __( 'Home', 'dynamic-book-archive' ),
			'url'   => home_url( '/' ),
		);

		$pto = get_post_type_object( 'book' );
		if ( ! $pto instanceof WP_Post_Type || ! $pto->has_archive ) {
			return self::finalize_items( $items );
		}

		$lib_url = \dba_get_book_post_type_archive_url();
		if ( ! is_string( $lib_url ) || '' === $lib_url ) {
			return self::finalize_items( $items );
		}

		$items[] = array(
			'label' => self::label_from_primary_menu_for_archive( $pto ),
			'url'   => $lib_url,
		);

		if ( $filter_term instanceof WP_Term ) {
			if ( $filter_term->parent > 0 ) {
				$parents = array_reverse(
					array_map(
						'intval',
						get_ancestors( $filter_term->term_id, \DBA_BOOK_CATEGORY_TAXONOMY )
					)
				);
				foreach ( $parents as $parent_id ) {
					$p = get_term( $parent_id, \DBA_BOOK_CATEGORY_TAXONOMY );
					if ( ! $p instanceof WP_Term ) {
						continue;
					}
					$items[] = array(
						'label' => $p->name,
						'url'   => \dba_get_book_post_type_archive_filter_url( $p ),
					);
				}
			}
			$items[] = array(
				'label' => $filter_term->name,
				'url'   => \dba_get_book_post_type_archive_filter_url( $filter_term ),
			);
		}

		return self::finalize_items( $items );
	}

	/**
	 * Renders {@see template-parts/ui/breadcrumbs.php} for a pre-built item list.
	 *
	 * @param array<int, array{label: string, url: string}> $items Breadcrumb items.
	 */
	public static function render_breadcrumbs_markup( array $items ): string {
		if ( count( $items ) < 2 ) {
			return '';
		}
		ob_start();
		get_template_part(
			'template-parts/ui/breadcrumbs',
			null,
			array(
				'items' => $items,
			)
		);

		return (string) ob_get_clean();
	}

	public static function finalize_items( array $items ): array {
		if ( array() !== $items ) {
			$paged = (int) get_query_var( 'paged' );
			if ( $paged > 1 && ! is_singular() ) {
				$last      = count( $items ) - 1;
				$paged_url = get_pagenum_link( $paged );
				if ( is_string( $paged_url ) && '' !== $paged_url ) {
					$items[ $last ]['url'] = $paged_url;
				}
			}
		}

		/**
		 * Filters the breadcrumb items before they are rendered.
		 *
		 * @param array<int, array{label: string, url: string}> $items Breadcrumb items.
		 */
		return apply_filters( 'dba_breadcrumb_items', $items );
	}

	/**
	 * Breadcrumb label for a post type archive: use the same title as the active primary nav item.
	 *
	 * Uses {@see _wp_menu_item_classes_by_context()} so the result matches the underlined menu item
	 * (post type archive, custom URL, scheme, etc.), instead of duplicating URL rules in the theme.
	 *
	 * @param WP_Post_Type $pto Post type object for the current archive.
	 */
	public static function label_from_primary_menu_for_archive( WP_Post_Type $pto ): string {
		$default = post_type_archive_title( '', false );
		if ( ! is_string( $default ) || '' === $default ) {
			$default = isset( $pto->labels->name ) ? (string) $pto->labels->name : '';
		}

		/**
		 * Theme location used to resolve the menu label for post type archive breadcrumbs.
		 *
		 * @param string $location Registered location slug (default `menu-1`, Primary).
		 */
		$location = apply_filters( 'dba_breadcrumb_menu_location_for_post_type_archive', 'menu-1' );

		$locations = get_nav_menu_locations();
		if ( ! is_string( $location ) || '' === $location || ! isset( $locations[ $location ] ) ) {
			return $default;
		}

		$menu_id = (int) $locations[ $location ];
		if ( $menu_id <= 0 ) {
			return $default;
		}

		$menu_items = wp_get_nav_menu_items( $menu_id );
		if ( ! is_array( $menu_items ) ) {
			return $default;
		}

		if ( function_exists( '_wp_menu_item_classes_by_context' ) ) {
			_wp_menu_item_classes_by_context( $menu_items );
			foreach ( $menu_items as $item ) {
				if ( ! $item instanceof WP_Post || empty( $item->current ) ) {
					continue;
				}
				$title = trim( (string) $item->title );
				if ( '' !== $title ) {
					return $title;
				}
			}
		}

		foreach ( $menu_items as $item ) {
			if ( ! $item instanceof WP_Post ) {
				continue;
			}
			if ( 'post_type_archive' === $item->type && $pto->name === $item->object ) {
				$title = trim( (string) $item->title );
				return '' !== $title ? $title : $default;
			}
		}

		$archive_url = 'book' === $pto->name ? \dba_get_book_post_type_archive_url() : get_post_type_archive_link( $pto->name );
		if ( is_string( $archive_url ) && '' !== $archive_url ) {
			$norm_archive = untrailingslashit( set_url_scheme( esc_url_raw( $archive_url ), 'http' ) );
			foreach ( $menu_items as $item ) {
				if ( ! $item instanceof WP_Post || empty( $item->url ) ) {
					continue;
				}
				$hash_pos = strpos( (string) $item->url, '#' );
				$raw      = $hash_pos ? substr( (string) $item->url, 0, $hash_pos ) : (string) $item->url;
				$norm_item = untrailingslashit( set_url_scheme( esc_url_raw( $raw ), 'http' ) );
				if ( $norm_item === $norm_archive ) {
					$title = trim( (string) $item->title );
					return '' !== $title ? $title : $default;
				}
			}
		}

		return $default;
	}

	/**
	 * Builds the breadcrumb trail (label + URL for each segment; last segment is the current page).
	 *
	 * @return array<int, array{label: string, url: string}>
	 */
	public static function get_items(): array {
		if ( is_feed() ) {
			return array();
		}

		if ( is_front_page() && ! is_paged() ) {
			return array();
		}

		$items   = array();
		$items[] = array(
			'label' => __( 'Home', 'dynamic-book-archive' ),
			'url'   => home_url( '/' ),
		);

		if ( is_home() && ! is_front_page() ) {
			$posts_page_id = (int) get_option( 'page_for_posts' );
			$title         = $posts_page_id ? get_the_title( $posts_page_id ) : __( 'Blog', 'dynamic-book-archive' );
			if ( ! is_string( $title ) || '' === $title ) {
				$title = __( 'Blog', 'dynamic-book-archive' );
			}
			$url = $posts_page_id ? get_permalink( $posts_page_id ) : get_post_type_archive_link( 'post' );
			if ( ! is_string( $url ) || '' === $url ) {
				$url = home_url( '/' );
			}
			$items[] = array(
				'label' => $title,
				'url'   => $url,
			);

			return self::finalize_items( $items );
		}

		if ( is_front_page() && is_paged() ) {
			$items[] = array(
				'label' => sprintf(
					/* translators: %d: page number. */
					__( 'Page %d', 'dynamic-book-archive' ),
					max( 1, (int) get_query_var( 'paged' ) )
				),
				'url'   => get_pagenum_link( max( 1, (int) get_query_var( 'paged' ) ) ),
			);

			return self::finalize_items( $items );
		}

		if ( is_singular() ) {
			global $post;
			if ( ! $post instanceof WP_Post ) {
				return array();
			}

			if ( is_page() ) {
				$ancestors = array_reverse( array_map( 'intval', get_post_ancestors( $post ) ) );
				foreach ( $ancestors as $ancestor_id ) {
					$items[] = array(
						'label' => get_the_title( $ancestor_id ),
						'url'   => get_permalink( $ancestor_id ),
					);
				}
				$items[] = array(
					'label' => get_the_title( $post ),
					'url'   => get_permalink( $post ),
				);

				return self::finalize_items( $items );
			}

			if ( 'attachment' === $post->post_type ) {
				if ( $post->post_parent ) {
					$parent = get_post( $post->post_parent );
					if ( $parent instanceof WP_Post ) {
						$items[] = array(
							'label' => get_the_title( $parent ),
							'url'   => get_permalink( $parent ),
						);
					}
				}
				$items[] = array(
					'label' => get_the_title( $post ),
					'url'   => get_permalink( $post ),
				);

				return self::finalize_items( $items );
			}

			if ( 'post' === $post->post_type ) {
				$categories = get_the_category( $post->ID );
				if ( ! empty( $categories ) ) {
					$cat   = $categories[0];
					$clink = get_category_link( $cat->term_id );
					$items[] = array(
						'label' => $cat->name,
						'url'   => is_string( $clink ) ? $clink : home_url( '/' ),
					);
				}
				$items[] = array(
					'label' => get_the_title( $post ),
					'url'   => get_permalink( $post ),
				);

				return self::finalize_items( $items );
			}

			$pto = get_post_type_object( $post->post_type );
			if ( $pto && $pto->has_archive ) {
				$arch = 'book' === $post->post_type ? \dba_get_book_post_type_archive_url() : get_post_type_archive_link( $post->post_type );
				if ( is_string( $arch ) && '' !== $arch ) {
					$items[] = array(
						'label' => $pto->labels->name,
						'url'   => $arch,
					);
				}
			}
			$items[] = array(
				'label' => get_the_title( $post ),
				'url'   => get_permalink( $post ),
			);

			return self::finalize_items( $items );
		}

		if ( is_tax( \DBA_BOOK_CATEGORY_TAXONOMY ) ) {
			$term = get_queried_object();
			if ( $term instanceof WP_Term ) {
				$pto = get_post_type_object( 'book' );
				if ( $pto instanceof WP_Post_Type && $pto->has_archive ) {
					$lib_url = \dba_get_book_post_type_archive_url();
					if ( is_string( $lib_url ) && '' !== $lib_url ) {
						$items[] = array(
							'label' => self::label_from_primary_menu_for_archive( $pto ),
							'url'   => $lib_url,
						);
					}
				}
				if ( $term->parent > 0 ) {
					$parents = array_reverse(
						array_map(
							'intval',
							get_ancestors( $term->term_id, \DBA_BOOK_CATEGORY_TAXONOMY )
						)
					);
					foreach ( $parents as $parent_id ) {
						$p = get_term( $parent_id, \DBA_BOOK_CATEGORY_TAXONOMY );
						if ( ! $p instanceof WP_Term ) {
							continue;
						}
						$items[] = array(
							'label' => $p->name,
							'url'   => \dba_get_book_post_type_archive_filter_url( $p ),
						);
					}
				}
				$items[] = array(
					'label' => $term->name,
					'url'   => \dba_get_book_post_type_archive_filter_url( $term ),
				);
			}

			return self::finalize_items( $items );
		}

		// Book archive + optional category (from query before redirect): Home > Library > term.
		if ( ( is_category() || is_tag() || is_tax() ) && ! is_post_type_archive( 'book' ) && ! is_tax( \DBA_BOOK_CATEGORY_TAXONOMY ) ) {
			$term = get_queried_object();
			if ( $term instanceof WP_Term ) {
				if ( is_category() && $term->parent > 0 ) {
					$parents = array_reverse(
						array_map(
							'intval',
							get_ancestors( $term->term_id, 'category' )
						)
					);
					foreach ( $parents as $parent_id ) {
						$p = get_term( $parent_id, 'category' );
						if ( ! $p instanceof WP_Term ) {
							continue;
						}
						$plink = get_term_link( $p );
						if ( is_wp_error( $plink ) ) {
							continue;
						}
						$items[] = array(
							'label' => $p->name,
							'url'   => $plink,
						);
					}
				}
				$tlink   = get_term_link( $term );
				$items[] = array(
					'label' => $term->name,
					'url'   => is_string( $tlink ) && ! is_wp_error( $tlink ) ? $tlink : home_url( '/' ),
				);
			}

			return self::finalize_items( $items );
		}

		if ( is_post_type_archive() ) {
			$pto = get_queried_object();
			if ( ! $pto instanceof WP_Post_Type ) {
				$qv = get_query_var( 'post_type' );
				if ( is_array( $qv ) && isset( $qv[0] ) && is_string( $qv[0] ) ) {
					$qv = $qv[0];
				}
				if ( is_string( $qv ) && '' !== $qv ) {
					$maybe_pto = get_post_type_object( $qv );
					if ( $maybe_pto instanceof WP_Post_Type ) {
						$pto = $maybe_pto;
					}
				}
			}
			if ( $pto instanceof WP_Post_Type && 'book' === $pto->name ) {
				$filter_term = \dba_get_book_archive_filtered_category();

				return self::get_book_post_type_breadcrumb_items( $filter_term );
			}
			if ( $pto instanceof WP_Post_Type ) {
				$url = get_post_type_archive_link( $pto->name );
				if ( ! is_string( $url ) || '' === $url ) {
					$url = home_url( '/' );
				}
				$items[] = array(
					'label' => self::label_from_primary_menu_for_archive( $pto ),
					'url'   => $url,
				);
			}

			return self::finalize_items( $items );
		}

		if ( is_author() ) {
			$author = get_queried_object();
			if ( $author instanceof WP_User ) {
				$items[] = array(
					'label' => $author->display_name,
					'url'   => get_author_posts_url( $author->ID ),
				);
			}

			return self::finalize_items( $items );
		}

		if ( is_year() ) {
			$year    = (int) get_query_var( 'year' );
			$items[] = array(
				'label' => (string) $year,
				'url'   => get_year_link( $year ),
			);

			return self::finalize_items( $items );
		}

		if ( is_month() ) {
			$year    = (int) get_query_var( 'year' );
			$month   = (int) get_query_var( 'monthnum' );
			$items[] = array(
				'label' => mysql2date( 'F Y', sprintf( '%04d-%02d-01', $year, $month ) ),
				'url'   => get_month_link( $year, $month ),
			);

			return self::finalize_items( $items );
		}

		if ( is_day() ) {
			$year    = (int) get_query_var( 'year' );
			$month   = (int) get_query_var( 'monthnum' );
			$day     = (int) get_query_var( 'day' );
			$items[] = array(
				'label' => mysql2date( (string) get_option( 'date_format' ), sprintf( '%04d-%02d-%02d', $year, $month, $day ) ),
				'url'   => get_day_link( $year, $month, $day ),
			);

			return self::finalize_items( $items );
		}

		if ( is_search() ) {
			$items[] = array(
				'label' => sprintf(
					/* translators: %s: search query. */
					__( 'Search results for "%s"', 'dynamic-book-archive' ),
					get_search_query()
				),
				'url'   => get_search_link(),
			);

			return self::finalize_items( $items );
		}

		if ( is_404() ) {
			$items[] = array(
				'label' => __( 'Page not found', 'dynamic-book-archive' ),
				'url'   => home_url( '/' ),
			);

			return self::finalize_items( $items );
		}

		if ( is_archive() ) {
			$items[] = array(
				'label' => wp_strip_all_tags( get_the_archive_title() ),
				'url'   => home_url( add_query_arg( array() ) ),
			);

			return self::finalize_items( $items );
		}

		return self::finalize_items( $items );
	}
}
