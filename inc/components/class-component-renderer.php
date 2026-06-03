<?php
/**
 * Component renderer.
 *
 * Resolves a component name, runs its controller (if any), and renders the
 * matching view via get_template_part().
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\Components;

/**
 * Renders components by name.
 */
final class Component_Renderer {

	/**
	 * Render a component to output.
	 *
	 * @param string               $name   Component name, e.g. `ui.dl-row`.
	 * @param array<string, mixed> $params Raw params for the controller/view.
	 * @param string|null          $slot   Captured slot HTML (container components only).
	 */
	public static function render( string $name, array $params = array(), ?string $slot = null ): void {
		$resolved = Component_Resolver::resolve( $name );

		if ( null === $resolved ) {
			self::warn( sprintf( 'Invalid component name "%s".', $name ) );

			return;
		}

		$args = $params;

		if ( class_exists( $resolved['class'] ) ) {
			$controller = new $resolved['class']( $params, $slot );
			$args       = $controller->data();
		}

		if ( null !== $slot ) {
			$args['slot'] = $slot;
		}

		/**
		 * Filter the view model passed to a component view.
		 *
		 * @param array<string, mixed> $args View model handed to the view as `$args`.
		 * @param string               $name Component name being rendered.
		 */
		$args = apply_filters( 'dba_component_data', $args, $name );

		get_template_part( $resolved['view'], null, $args );
	}

	/**
	 * Render a component and return its output as a string.
	 *
	 * @param string               $name   Component name.
	 * @param array<string, mixed> $params Raw params.
	 * @param string|null          $slot   Captured slot HTML.
	 */
	public static function render_buffered( string $name, array $params = array(), ?string $slot = null ): string {
		ob_start();
		self::render( $name, $params, $slot );

		return (string) ob_get_clean();
	}

	/**
	 * Emit a developer warning when WP_DEBUG is enabled.
	 *
	 * @param string $message Warning message.
	 */
	private static function warn( string $message ): void {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			trigger_error( esc_html( 'Dynamic Book Archive component: ' . $message ), E_USER_WARNING );
		}
	}
}
