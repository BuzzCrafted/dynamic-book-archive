<?php
/**
 * Base component controller.
 *
 * A component controller receives raw params (and an optional slot for
 * container components), then exposes a prepared view model via data().
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\Components;

/**
 * Abstract component controller.
 *
 * Concrete controllers live under `inc/components/{type}/` and are resolved by
 * name through {@see Component_Resolver}. Controllers are optional: a component
 * may ship with a view only (Laravel-style anonymous component).
 */
abstract class Component {

	/**
	 * Raw params passed to the component.
	 *
	 * @var array<string, mixed>
	 */
	protected array $params;

	/**
	 * Captured slot HTML for container components, or null for leaf components.
	 */
	protected ?string $slot;

	/**
	 * @param array<string, mixed> $params Raw params from the call site.
	 * @param string|null          $slot   Captured slot HTML (container components only).
	 */
	public function __construct( array $params = array(), ?string $slot = null ) {
		$this->params = $params;
		$this->slot   = $slot;
	}

	/**
	 * Build the view model handed to the component view as `$args`.
	 *
	 * Override to merge defaults, validate, and compute derived values. The
	 * renderer injects the slot under `$args['slot']` after this runs, so views
	 * never need the controller to forward it.
	 *
	 * @return array<string, mixed>
	 */
	public function data(): array {
		return $this->params;
	}

	/**
	 * Read a raw param with a fallback default.
	 *
	 * @param string $key     Param key.
	 * @param mixed  $default Value returned when the key is absent.
	 *
	 * @return mixed
	 */
	protected function param( string $key, $default = null ) {
		return array_key_exists( $key, $this->params ) ? $this->params[ $key ] : $default;
	}

	/**
	 * Read a string param, falling back to the default when missing or non-string.
	 *
	 * @param string $key     Param key.
	 * @param string $default Default string.
	 */
	protected function string_param( string $key, string $default = '' ): string {
		$value = $this->param( $key );

		return is_string( $value ) ? $value : $default;
	}
}
