<?php
/**
 * Loads theme subsystems and hook registrations.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\Theme;

use DBA\Customizer\Theme_Customizer;
use DBA\Integration\Books_Cpt_Archive_Rest;
use DBA\Media\Svg_Upload;
use DBA\TemplateTags\Breadcrumb_Presenter;

/**
 * Theme bootstrap.
 */
final class Theme_Bootstrap {

	/**
	 * Register hooks for all theme components.
	 */
	public static function init(): void {
		Theme_Setup::register_hooks();
		Content_Width::register_hooks();
		Theme_Widgets::register_hooks();
		Theme_Assets::register_hooks();
		Book_Archive_Query::register_hooks();
		Book_Archive_Canonical_Redirect::register_hooks();
		Book_Archive_Template_Routing::register_hooks();
		Books_Cpt_Archive_Rest::register_hooks();
		Svg_Upload::register_hooks();
		Theme_Customizer::register_hooks();
		Breadcrumb_Presenter::register_hooks();
	}
}
