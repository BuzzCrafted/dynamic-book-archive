<?php
/**
 * Breadcrumb markup and JSON-LD.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\TemplateTags;

/**
 * Renders breadcrumbs and schema.org output.
 */
final class Breadcrumb_Presenter {

	public static function register_hooks(): void {
		add_action( 'wp_head', array( self::class, 'print_schema' ), 20 );
		add_filter( 'dba_show_breadcrumbs', array( self::class, 'filter_hide_on_singular_book' ), 10, 1 );
	}

	/**
	 * Single book pages use the template “Back to Library” link instead of the trail.
	 *
	 * @param bool $show Whether breadcrumbs should render (before other filters).
	 */
	public static function filter_hide_on_singular_book( bool $show ): bool {
		if ( is_singular( 'book' ) ) {
			return false;
		}

		return $show;
	}

	/**
	 * Prints an accessible breadcrumb trail (skipped on the non-paged front page).
	 *
	 * Hooks: `dba_show_breadcrumbs`, `dba_breadcrumb_items` (see Breadcrumb_Trail::get_items()).
	 */
	public static function render(): void {
		$default_show = ( ! is_front_page() || is_paged() );

		if ( ! apply_filters( 'dba_show_breadcrumbs', $default_show ) ) {
			return;
		}

		$items = Breadcrumb_Trail::get_items();
		if ( count( $items ) < 2 ) {
			return;
		}

		set_query_var( 'dba_breadcrumb_items', $items );
		get_template_part(
			'template-parts/ui/breadcrumbs',
			null,
			array(
				'items' => $items,
			)
		);
	}

	/**
	 * Outputs BreadcrumbList JSON-LD for supported views (not 404).
	 */
	public static function print_schema(): void {
		if ( is_feed() || is_404() ) {
			return;
		}

		$default_show = ( ! is_front_page() || is_paged() );
		if ( ! apply_filters( 'dba_show_breadcrumbs', $default_show ) ) {
			return;
		}

		$items = Breadcrumb_Trail::get_items();
		if ( count( $items ) < 2 ) {
			return;
		}

		$list = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => array(),
		);

		foreach ( $items as $i => $item ) {
			$list['itemListElement'][] = array(
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'name'     => wp_strip_all_tags( $item['label'] ),
				'item'     => $item['url'],
			);
		}

		printf(
			'<script type="application/ld+json">%s</script>' . "\n",
			wp_json_encode( $list, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT )
		);
	}
}
