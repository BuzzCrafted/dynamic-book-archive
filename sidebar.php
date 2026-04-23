<?php
/**
 * The sidebar containing the main widget area
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

if (! is_active_sidebar('sidebar-1')) {
	return;
}
?>

<aside id="secondary" class="widget-area w-full shrink-0 border-t border-library-primary-dark/35 bg-library-primary/10 md:w-72 md:border-l md:border-t-0 lg:w-80">
	<div class="p-6 md:sticky md:top-0 md:py-10">
		<?php dynamic_sidebar('sidebar-1'); ?>
	</div>
</aside>
