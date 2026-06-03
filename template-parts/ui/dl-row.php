<?php
/**
 * Definition list row (dt/dd pair).
 *
 * @deprecated Use the `ui.dl-row` component instead: `dba_component( 'ui.dl-row', $args )`.
 *             This partial is a thin shim kept while call sites migrate.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

if ( ! isset( $args ) || ! is_array( $args ) ) {
	return;
}

dba_component( 'ui.dl-row', $args );
