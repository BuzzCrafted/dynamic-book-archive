<?php
/**
 * Template part for displaying page content
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);
?>

<article id="page-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php dba_post_thumbnail(); ?>

	<div class="entry-content prose max-w-none">
		<?php
		the_content();
		?>
	</div>

</article>
