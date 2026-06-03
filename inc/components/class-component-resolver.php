<?php
/**
 * Component name resolution.
 *
 * Maps a dot/slash component name (e.g. `ui.dl-row`) to its controller class
 * and view slug using the theme naming convention.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\Components;

/**
 * Resolves component names to controller classes and view template slugs.
 */
final class Component_Resolver {

	/**
	 * Allowed component types, mapped to their PascalCase namespace segment.
	 *
	 * @var array<string, string>
	 */
	private const TYPES = array(
		'ui'        => 'Ui',
		'container' => 'Container',
	);

	/**
	 * Resolve a component name into its parts.
	 *
	 * @param string $name Component name, e.g. `ui.dl-row` or `container/card`.
	 *
	 * @return array{type:string, kebab_name:string, class:string, view:string}|null
	 *         Resolved parts, or null when the name is invalid.
	 */
	public static function resolve( string $name ): ?array {
		$normalized = strtolower( trim( str_replace( '/', '.', $name ) ) );
		$segments   = explode( '.', $normalized );

		if ( 2 !== count( $segments ) ) {
			return null;
		}

		list( $type, $kebab_name ) = $segments;

		if ( '' === $kebab_name || ! isset( self::TYPES[ $type ] ) ) {
			return null;
		}

		$class = sprintf(
			'DBA\\Components\\%s\\%s_Component',
			self::TYPES[ $type ],
			self::pascal_snake( $kebab_name )
		);

		return array(
			'type'       => $type,
			'kebab_name' => $kebab_name,
			'class'      => $class,
			'view'       => sprintf( 'template-parts/components/%s/%s', $type, $kebab_name ),
		);
	}

	/**
	 * Convert a kebab-case name into Pascal_Snake_Case (e.g. `dl-row` => `Dl_Row`).
	 *
	 * @param string $kebab_name Kebab-case component name.
	 */
	private static function pascal_snake( string $kebab_name ): string {
		$parts = array_filter( explode( '-', $kebab_name ), 'strlen' );
		$parts = array_map( 'ucfirst', $parts );

		return implode( '_', $parts );
	}
}
