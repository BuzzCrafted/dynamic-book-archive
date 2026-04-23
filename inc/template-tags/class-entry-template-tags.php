<?php
/**
 * Entry meta and post thumbnail markup.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\TemplateTags;

/**
 * Template tags for single/archive entry display.
 */
final class Entry_Template_Tags {

	public static function posted_on(): void {
		$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
		if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
			$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated hidden" datetime="%3$s">%4$s</time>';
		}

		$time_string = sprintf(
			$time_string,
			esc_attr( get_the_date( DATE_W3C ) ),
			esc_html( get_the_date() ),
			esc_attr( get_the_modified_date( DATE_W3C ) ),
			esc_html( get_the_modified_date() )
		);

		printf(
			'<span class="posted-on text-sm text-library-primary/70"><span class="screen-reader-text">%1$s </span>%2$s</span>',
			esc_html_x( 'Posted on', 'post date', 'dynamic-book-archive' ),
			$time_string // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}

	public static function posted_by(): void {
		printf(
			'<span class="byline text-sm text-library-primary/70"><span class="screen-reader-text">%1$s</span><span class="author vcard"><a class="url fn n font-medium text-library-primary/90 no-underline hover:underline" href="%2$s">%3$s</a></span></span>',
			esc_html_x( 'Posted by', 'post author', 'dynamic-book-archive' ),
			esc_url( get_author_posts_url( (int) get_the_author_meta( 'ID' ) ) ),
			esc_html( get_the_author() )
		);
	}

	public static function entry_footer(): void {
		if ( 'post' === get_post_type() ) {
			$categories_list = get_the_category_list( esc_html__( ', ', 'dynamic-book-archive' ) );
			if ( $categories_list ) {
				printf(
					'<span class="cat-links block text-sm text-library-primary/70">%1$s %2$s</span>',
					esc_html_x( 'Posted in', 'list of categories', 'dynamic-book-archive' ),
					$categories_list // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				);
			}

			$tags_list = get_the_tag_list( '', esc_html_x( ', ', 'list item separator', 'dynamic-book-archive' ) );
			if ( $tags_list ) {
				printf(
					'<span class="tags-links mt-2 block text-sm text-library-primary/70">%1$s %2$s</span>',
					esc_html_x( 'Tagged', 'list of tags', 'dynamic-book-archive' ),
					$tags_list // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				);
			}
		}

		if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
			echo '<span class="comments-link mt-2 block text-sm">';
			comments_popup_link(
				sprintf(
					wp_kses(
						/* translators: %s: post title */
						__( 'Leave a comment<span class="screen-reader-text"> on %s</span>', 'dynamic-book-archive' ),
						array(
							'span' => array(
								'class' => array(),
							),
						)
					),
					wp_kses_post( get_the_title() )
				)
			);
			echo '</span>';
		}

		edit_post_link(
			sprintf(
				wp_kses(
					/* translators: %s: post title */
					__( 'Edit <span class="screen-reader-text">%s</span>', 'dynamic-book-archive' ),
					array(
						'span' => array(
							'class' => array(),
						),
					)
				),
				wp_kses_post( get_the_title() )
			),
			'<span class="edit-link mt-3 block text-sm">',
			'</span>'
		);
	}

	public static function post_thumbnail(): void {
		if ( post_password_required() || is_attachment() || ! has_post_thumbnail() ) {
			return;
		}

		if ( is_singular() ) :
			?>

			<div class="post-thumbnail mb-8 overflow-hidden rounded-lg border border-library-primary-dark/35">
				<?php the_post_thumbnail( 'large', array( 'class' => 'h-auto w-full object-cover' ) ); ?>
			</div>

			<?php
		else :
			?>

			<a class="post-thumbnail mb-6 block overflow-hidden rounded-lg border border-library-primary-dark/35 no-underline" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
				<?php
				the_post_thumbnail(
					'large',
					array(
						'alt'   => the_title_attribute(
							array(
								'echo' => false,
							)
						),
						'class' => 'h-auto w-full object-cover transition hover:opacity-95',
					)
				);
				?>
			</a>

			<?php
		endif;
	}
}
