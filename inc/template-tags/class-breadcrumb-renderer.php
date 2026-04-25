<?php
/**
 * Breadcrumb rendering helper.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\TemplateTags;

/**
 * Renders breadcrumb markup for a pre-built item list.
 */
final class Breadcrumb_Renderer {
	/**
	 * @param array<int, array{label: string, url: string}> $items Breadcrumb items.
	 */
	public static function render_markup( array $items ): string {
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
}

