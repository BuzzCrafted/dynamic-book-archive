<?php
/**
 * Card container component controller.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\Components\Container;

use DBA\Components\Component;

/**
 * Prepares the view model for the `container.card` component.
 *
 * A container component wraps slot markup. The slot is injected by the renderer
 * as `$args['slot']`; this controller only prepares the chrome around it.
 */
final class Card_Component extends Component {

	/**
	 * @return array{title:string, title_class:string, class:string, body_class:string}
	 */
	public function data(): array {
		$class = trim( 'rounded-sm bg-(--color-surface) p-5 shadow-main ' . $this->string_param( 'class' ) );

		return array(
			'title'       => $this->string_param( 'title' ),
			'title_class' => $this->string_param(
				'title_class',
				'mb-3 font-display text-lg text-(--color-heading)'
			),
			'class'       => $class,
			'body_class'  => $this->string_param( 'body_class' ),
		);
	}
}
