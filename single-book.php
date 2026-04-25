<?php
/**
 * Single template for the `book` post type (full-width library detail layout).
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

use DBA\Presenters\Book_Single_Presenter;

get_header();
?>

<div class="site-content mx-auto flex w-full max-w-[1440px] flex-1 flex-col px-4 pb-16 sm:px-6 lg:px-8 xl:px-12">
	<main id="primary" class="site-main flex-1 w-full min-w-0">
		<?php
		while (have_posts()) :
			the_post();
			$post_id = get_the_ID();
			$vm      = Book_Single_Presenter::build_from_post_id( (int) $post_id );
			get_template_part( 'template-parts/book/single/page', null, $vm );
		endwhile;
		?>
	</main>
</div>

<?php
get_footer();
