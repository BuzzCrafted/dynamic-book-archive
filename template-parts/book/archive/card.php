<?php
/**
 * Markup for each book in the post-type archive grid (4×3 cell).
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

$dba_card_post_id        = get_the_ID();
$dba_card_title_japanese = (string) get_post_meta( $dba_card_post_id, 'title_japanese', true );
$dba_card_publication        = (string) get_post_meta( $dba_card_post_id, 'publication_date', true );
$dba_card_publication_trim   = trim( $dba_card_publication );
$dba_card_pub_ts             = '' !== $dba_card_publication_trim ? strtotime( $dba_card_publication_trim ) : false;
$dba_card_is_year_only       = (bool) preg_match( '/^\d{4}$/', $dba_card_publication_trim );
$dba_card_publication_label  = '';
if ( false !== $dba_card_pub_ts ) {
	$dba_card_publication_label = $dba_card_is_year_only
		? date_i18n( 'Y', (int) $dba_card_pub_ts )
		: date_i18n( (string) get_option( 'date_format' ), (int) $dba_card_pub_ts );
}
?>

<article id="post-<?php the_ID(); ?>" class="group relative min-h-0 w-full p-2 rounded-lg bg-surface transition-shadow duration-300 shadow-main hover:shadow-main-hover before:pointer-events-none before:absolute before:inset-0 before:rounded-[inherit] before:p-px before:content-[''] before:opacity-0 before:transition-opacity before:duration-300 hover:before:opacity-100 before:bg-(image:--image-card-highlight) before:mask-[linear-gradient(#000,#000),linear-gradient(#000,#000)] before:[-webkit-mask-image:linear-gradient(#000,#000),linear-gradient(#000,#000)] before:[mask-clip:content-box,border-box] before:[-webkit-mask-clip:content-box,border-box] before:mask-exclude">
	<a href="<?php the_permalink(); ?>" class="flex w-full min-h-0 items-stretch gap-2 text-card-text no-underline">
		<div class="relative min-w-0 flex-1 self-center overflow-hidden aspect-2/3">
			<?php
			if ( has_post_thumbnail() ) {
				the_post_thumbnail( 'large', array( 'class' => '!h-full !w-full object-cover object-left' ) );
			} else {
				printf(
					'<img src="%1$s" alt="%2$s" class="%3$s" width="1301" height="1209" loading="lazy" decoding="async" />',
					esc_url( get_template_directory_uri() . '/assets/images/placeholders/no-cover.webp' ),
					esc_attr__( 'No cover image', 'dynamic-book-archive' ),
					esc_attr( 'h-full w-full object-cover object-left' )
				);
			}
			?>
		</div>
		<div class="flex min-w-0 flex-1 flex-col justify-center gap-2 overflow-hidden">
			<?php if ( '' !== $dba_card_title_japanese ) : ?>
				<h2 class="font-display text-xl uppercase leading-tight tracking-[0.4px]"><?php echo esc_html( $dba_card_title_japanese ); ?></h2>
			<?php endif; ?>
			<p class="font-main text-sm tracking-[0.24px]"><?php the_title(); ?></p>
			<?php if ( '' !== $dba_card_publication_label ) : ?>
				<p class="font-main text-xs tracking-[0.2px]"><?php echo esc_html( $dba_card_publication_label ); ?></p>
			<?php endif; ?>
		</div>
	</a>
</article>
