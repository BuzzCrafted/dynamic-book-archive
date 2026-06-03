<?php
/**
 * Slot capture stack for container components.
 *
 * Enables template-friendly slot syntax: open a container, emit inner markup,
 * then close it. Inner output is buffered and handed to the component as its
 * slot. A stack supports safe nesting of containers.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\Components;

/**
 * Manages nested open/close slot capture for container components.
 */
final class Component_Slot_Stack {

	/**
	 * Open frames, each holding the component name and its params.
	 *
	 * @var array<int, array{name:string, params:array<string, mixed>}>
	 */
	private static array $frames = array();

	/**
	 * Begin a container component and start capturing its slot markup.
	 *
	 * @param string               $name   Container component name, e.g. `container.card`.
	 * @param array<string, mixed> $params Raw params for the controller/view.
	 */
	public static function open( string $name, array $params = array() ): void {
		self::$frames[] = array(
			'name'   => $name,
			'params' => $params,
		);
		ob_start();
	}

	/**
	 * Close the most recently opened container and render it with its captured slot.
	 */
	public static function close(): void {
		$frame = array_pop( self::$frames );

		if ( null === $frame ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				trigger_error(
					esc_html( 'Dynamic Book Archive component: dba_component_close() called without a matching open.' ),
					E_USER_WARNING
				);
			}

			return;
		}

		$slot = (string) ob_get_clean();

		Component_Renderer::render( $frame['name'], $frame['params'], $slot );
	}
}
