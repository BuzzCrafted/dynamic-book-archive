<?php
/**
 * Single book gallery.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

use DBA\Domain\Books\Book_Media_Repository;

if ( ! isset( $args ) || ! is_array( $args ) ) {
	return;
}

$post_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : 0;
$gallery = isset( $args['gallery'] ) && is_array( $args['gallery'] ) ? $args['gallery'] : array();

$ids            = isset( $gallery['ids'] ) && is_array( $gallery['ids'] ) ? $gallery['ids'] : array();
$count          = isset( $gallery['count'] ) ? (int) $gallery['count'] : count( $ids );
$thumb_limit    = isset( $gallery['thumb_limit'] ) ? (int) $gallery['thumb_limit'] : 5;
$thumbs_capped  = isset( $gallery['thumbs_capped'] ) ? (bool) $gallery['thumbs_capped'] : ( $count > $thumb_limit );

$ids = array_values( array_filter( array_map( 'intval', $ids ), 'wp_attachment_is_image' ) );

if ( $post_id <= 0 ) {
	return;
}

if ( count( $ids ) <= 0 ) :
	?>
	<div class="flex w-full items-center justify-center rounded-lg border border-dashed border-library-primary-dark/50 bg-library-secondary/30 p-6 text-center text-sm text-library-primary/60">
		<?php esc_html_e( 'No cover image available.', 'dynamic-book-archive' ); ?>
	</div>
	<?php
	return;
endif;

$thumb_limit   = max( 1, $thumb_limit );
$gallery_count = count( $ids );
$lightbox_gallery_id = $gallery_count > 1 ? 'book-gallery-' . $post_id : '';
?>
<div class="flex flex-col self-start rounded-md shadow-main" data-book-gallery<?php echo $thumbs_capped ? ' data-book-gallery-thumbs-capped="1"' : ''; ?>>
	<span id="book-gallery-status-<?php echo esc_attr( (string) $post_id ); ?>" class="sr-only" data-book-gallery-status aria-live="polite"></span>
	<div class="relative h-(--book-single-gallery-stage-height) w-full overflow-hidden rounded-md bg-page/50">
		<?php if ( $gallery_count > 1 ) : ?>
			<button type="button" class="absolute left-1 top-1/2 z-10 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full shadow-main bg-page/50 text-heading backdrop-blur-sm transition hover:bg-page hover:shadow-bronze-glow hover:text-body disabled:pointer-events-none disabled:opacity-30" data-book-gallery-prev aria-controls="book-gallery-slides-<?php echo esc_attr( (string) $post_id ); ?>">
				<span class="sr-only"><?php esc_html_e( 'Previous image', 'dynamic-book-archive' ); ?></span>
				<?php dba_the_inline_icon( 'bx/bx-chevron-left', 'block h-8 w-8' ); ?>
			</button>
			<button type="button" class="absolute right-1 top-1/2 z-10 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full shadow-main bg-page/50 text-heading backdrop-blur-sm transition hover:bg-page hover:shadow-bronze-glow hover:text-body disabled:pointer-events-none disabled:opacity-30" data-book-gallery-next aria-controls="book-gallery-slides-<?php echo esc_attr( (string) $post_id ); ?>">
				<span class="sr-only"><?php esc_html_e( 'Next image', 'dynamic-book-archive' ); ?></span>
				<?php dba_the_inline_icon( 'bx/bx-chevron-right', 'block h-8 w-8' ); ?>
			</button>
		<?php endif; ?>
		<div id="book-gallery-slides-<?php echo esc_attr( (string) $post_id ); ?>" class="contents">
			<?php
			$slide_class = 'absolute inset-0 m-1! z-0 flex items-center justify-center bg-main/30 opacity-0 transition-opacity duration-300 pointer-events-none aria-[hidden=false]:pointer-events-auto aria-[hidden=false]:z aria-[hidden=false]:opacity-100';
			foreach ( $ids as $idx => $img_id ) {
				$aria_label = 1 === $gallery_count
					? __( 'View cover image', 'dynamic-book-archive' )
					: sprintf(
						/* translators: 1: image number (1-based), 2: total image count. */
						__( 'View image %1$d of %2$d', 'dynamic-book-archive' ),
						$idx + 1,
						$gallery_count
					);
				$img_html = Book_Media_Repository::render_lightbox_image(
					(int) $img_id,
					'full',
					'max-h-full max-w-full h-auto w-auto object-contain object-center',
					$aria_label,
					array(
						'loading'  => 0 === $idx ? 'eager' : 'lazy',
						'decoding' => 'async',
					),
					$lightbox_gallery_id
				);
				if ( '' === $img_html ) {
					continue;
				}
				printf(
					'<figure class="%s" data-book-gallery-slide="%d" aria-hidden="%s">',
					esc_attr( $slide_class ),
					(int) $idx,
					0 === $idx ? 'false' : 'true'
				);
				echo $img_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() returns safe HTML.
				echo '</figure>';
			}
			?>
		</div>
	</div>

	<?php if ( $gallery_count > 1 ) : ?>
		<div class="hidden md:flex flex-col gap-2 shadow-main-top p-2">
			<?php
			$tablist_class = 'flex w-full gap-2 [scrollbar-width:thin] justify-end';
			?>
			<div class="<?php echo esc_attr( $tablist_class ); ?>" role="tablist" aria-label="<?php esc_attr_e( 'Book images', 'dynamic-book-archive' ); ?>">
				<?php
				$thumb_class = 'relative h-30 w-22 m-1 shrink-0 hover:z-20 overflow-hidden rounded border-2 border-transparent bg-page/25 opacity-80 transition hover:opacity-100 hover:shadow-bronze-glow hover:border-none duration-200 ease-out hover:scale-120 aria-pressed:border-book-secondary aria-pressed:opacity-100 aria-pressed:ring-1 aria-pressed:ring-heading/40';
				$thumb_count = min( $thumb_limit, $gallery_count );
				for ( $idx = 0; $idx < $thumb_count; $idx++ ) {
					$img_id = (int) $ids[ $idx ];
					$thumb_meta = wp_get_attachment_metadata( $img_id );
					$thumb_w    = is_array( $thumb_meta ) && isset( $thumb_meta['width'] ) ? (int) $thumb_meta['width'] : 0;
					$thumb_h    = is_array( $thumb_meta ) && isset( $thumb_meta['height'] ) ? (int) $thumb_meta['height'] : 0;
					$is_portrait = $thumb_w > 0 && $thumb_h > $thumb_w;
					$thumb_img_class = $is_portrait
						? 'h-auto w-full object-contain'
						: 'h-full w-full object-center object-cover';
					$thumb_html = wp_get_attachment_image(
						$img_id,
						'full',
						false,
						array( 'class' => $thumb_img_class )
					);
					if ( '' === $thumb_html ) {
						continue;
					}
					$show_more_overlay = $thumbs_capped && 4 === $idx;
					if ( $show_more_overlay ) {
						$aria_thumb = sprintf(
							/* translators: 1: image number (1-based), 2: how many more images exist after the first five. */
							__( 'Show image %1$d, %2$d more in gallery', 'dynamic-book-archive' ),
							$idx + 1,
							$gallery_count - 5
						);
					} else {
						$aria_thumb = sprintf(
							/* translators: %d: image number (1-based). */
							__( 'Show image %d', 'dynamic-book-archive' ),
							$idx + 1
						);
					}
					printf(
						'<button type="button" class="%s" data-book-gallery-thumb="%d" role="tab" aria-pressed="%s" aria-label="%s">',
						esc_attr( $thumb_class ),
						(int) $idx,
						0 === $idx ? 'true' : 'false',
						esc_attr( $aria_thumb )
					);
					echo $thumb_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() returns safe HTML.
					if ( $show_more_overlay ) {
						$more_n = (int) ( $gallery_count - 5 );
						printf(
							'<span class="absolute inset-0 z-1 flex items-center justify-center bg-black/50 text-sm font-semibold text-white" aria-hidden="true">+%d</span>',
							$more_n
						);
					}
					echo '</button>';
				}
				?>
			</div>
		</div>
	<?php endif; ?>
</div>

