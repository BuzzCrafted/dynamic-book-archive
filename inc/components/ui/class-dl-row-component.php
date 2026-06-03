<?php
/**
 * Definition list row component controller.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\Components\Ui;

use DBA\Components\Component;

/**
 * Prepares the view model for the `ui.dl-row` component.
 */
final class Dl_Row_Component extends Component {

	/**
	 * @return array{label:string, value:string, row_class:string, dt_class:string, dd_class:string}
	 */
	public function data(): array {
		return array(
			'label'     => $this->string_param( 'label' ),
			'value'     => $this->string_param( 'value' ),
			'row_class' => $this->string_param( 'row_class' ),
			'dt_class'  => $this->string_param( 'dt_class' ),
			'dd_class'  => $this->string_param( 'dd_class' ),
		);
	}
}
