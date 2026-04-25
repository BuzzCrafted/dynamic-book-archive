<?php

/**
 * Template part for singular book posts (detail layout).
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

$post_id = get_the_ID();

$title_japanese  = (string) get_post_meta($post_id, 'title_japanese', true);
$book_author     = (string) get_post_meta($post_id, 'book_author', true);
$author_japanese = (string) get_post_meta($post_id, 'author_japanese', true);
$publication_raw = (string) get_post_meta($post_id, 'publication_date', true);

$edition_details = (string) get_post_meta($post_id, 'edition_details', true);
$is_signed       = (bool) get_post_meta($post_id, 'is_signed', true);
$has_dust_jacket = (bool) get_post_meta($post_id, 'has_dust_jacket', true);
$has_slipcase    = (bool) get_post_meta($post_id, 'has_slipcase', true);

// Optional fields (may exist from imports even if not registered in books-cpt).
$book_language   = trim((string) get_post_meta($post_id, 'book_language', true)) ?: 'Japanese';
$book_pages      = trim((string) get_post_meta($post_id, 'pages', true)) ?: '1';
$book_dimensions = trim((string) get_post_meta($post_id, 'dimensions', true)) ?: '0 cm';
$book_binding    = trim((string) get_post_meta($post_id, 'binding', true)) ?: '0';
$book_condition  = trim((string) get_post_meta($post_id, 'condition', true)) ?: '0';
$book_publisher  = trim((string) get_post_meta($post_id, 'publisher', true)) ?: '';

$gallery_ids = function_exists('dba_get_book_gallery_image_ids') ? dba_get_book_gallery_image_ids($post_id) : array();
$gallery_ids = array_values(array_filter(array_map('intval', $gallery_ids), 'wp_attachment_is_image'));

$gallery_count   = count($gallery_ids);
$thumb_limit     = 5;
$thumbs_capped   = $gallery_count > $thumb_limit;
$thumb_rail_rtl  = $gallery_count < $thumb_limit;

$publication_raw_trimmed  = trim($publication_raw);
$pub_ts                   = '' !== $publication_raw_trimmed ? strtotime($publication_raw_trimmed) : false;
$is_publication_year_only = (bool) preg_match('/^\d{4}$/', $publication_raw_trimmed);
$publication_label        = '';
if (false !== $pub_ts) {
	$publication_label = $is_publication_year_only
		? date_i18n('Y', (int) $pub_ts)
		: date_i18n((string) get_option('date_format'), (int) $pub_ts);
}

$author_display = $book_author;
if (!empty($author_japanese) && !empty($book_author)) {
	$author_display = sprintf(
		/* translators: 1: author name (Latin), 2: author name (Japanese). */
		__('%1$s (%2$s)', 'dynamic-book-archive'),
		$book_author,
		$author_japanese
	);
} elseif ('' !== $author_japanese) {
	$author_display = $author_japanese;
}

$categories = get_the_terms($post_id, DBA_BOOK_CATEGORY_TAXONOMY);
if (is_wp_error($categories) || ! is_array($categories)) {
	$categories = array();
}

$tags = get_the_terms($post_id, 'post_tag');
if (is_wp_error($tags) || ! is_array($tags)) {
	$tags = array();
}

$category_names = array_map(
	static function (WP_Term $t): string {
		return $t->name;
	},
	$categories
);
$category_label = implode(', ', $category_names);

/**
 * Filters the “collection” line on single book pages (e.g. named collection).
 *
 * @param string $label Plain text.
 * @param int    $post_id Book post ID.
 */
$collection_label = apply_filters(
	'dba_book_single_collection_label',
	__('Robert C. Gruzanski Collection', 'dynamic-book-archive'),
	$post_id
);
$collection_label = is_string($collection_label) ? $collection_label : '';

$archive_url = function_exists('dba_get_book_post_type_archive_url') ? dba_get_book_post_type_archive_url() : get_post_type_archive_link('book');
if (! is_string($archive_url) || '' === $archive_url) {
	$archive_url = home_url('/');
}

$library_back_label = __('Back to Library', 'dynamic-book-archive');
$pto                = get_post_type_object('book');
if ($pto instanceof WP_Post_Type) {
	$menu_label = function_exists('dba_get_breadcrumb_label_from_primary_menu_for_archive')
		? dba_get_breadcrumb_label_from_primary_menu_for_archive($pto)
		: '';
	if ('' !== $menu_label) {
		/* translators: %s: Library section name from the menu (e.g. “Ninjutsu Library”). */
		$library_back_label = sprintf(__('Back to %s', 'dynamic-book-archive'), $menu_label);
	}
}

$quick_items = array();

if (!empty($book_pages)) {
	$quick_items[] = array(
		/* translators: %s: page count. */
		'text' => sprintf(__('%s pages', 'dynamic-book-archive'), $book_pages),
		'icon' => 'pages',
	);
}
if (!empty($book_dimensions)) {
	$quick_items[] = array(
		'text' => $book_dimensions,
		'icon' => 'size',
	);
}
if ($has_slipcase) {
	$quick_items[] = array(
		'text' => __('Slipcase', 'dynamic-book-archive'),
		'icon' => 'slipcase',
	);
}
if (!empty($book_language)) {
	$quick_items[] = array(
		'text' => $book_language,
		'icon' => 'language',
	);
}
if (!empty($book_binding)) {
	$quick_items[] = array(
		'text' => $book_binding,
		'icon' => 'binding',
	);
}
if (!empty($book_condition)) {
	$quick_items[] = array(
		'text' => $book_condition,
		'icon' => 'condition',
	);
}
if (!empty($edition_details)) {
	$quick_items[] = array(
		'text' => $edition_details,
		'icon' => 'note',
	);
}
if ($is_signed) {
	$quick_items[] = array(
		'text' => __('Signed copy', 'dynamic-book-archive'),
		'icon' => 'signed',
	);
}


/**
 * Filters the icon for a quick item on single book pages.
 *
 * @param string $icon Icon key.
 */
$dba_book_single_icon = static function (string $icon): string {
	$icons = array(
		'pages' => 'bx/bx-book-open',
		'size' => 'bx/bx-book',
		'binding' => 'bx/bx-bookmark-alt',
		'language' => 'bx/bx-globe',
		'condition' => 'bx/bx-x-circle',
		'note' => 'bx/bx-note',
		'signed' => 'bx/bx-pen',
		'slipcase' => 'bx/bx-food-menu',
	);

	if (isset($icons[$icon])) {
		return dba_get_inline_icon($icons[$icon], 'h-5 w-5 shrink-0 text-book-secondary');
	}
	return '';
};
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('text-white/90'); ?>>
	<a class="font-main my-2 md:my-4 lg:my-6 inline-flex items-center gap-1 md:gap-2 text-sm font-medium tracking-widest text-book-primary no-underline transition hover:text-book-secondary" href="<?php echo esc_url($archive_url); ?>">
		<span aria-hidden="true">←</span>
		<?php echo esc_html($library_back_label); ?>
	</a>

	<div class="grid gap-1 md:gap-2 lg:gap-6 lg:grid-cols-[3fr_4fr] sm:grid-cols-1">
		<?php if (count($gallery_ids) > 0) : ?>
			<div class="flex flex-col self-start rounded-md shadow-main" data-book-gallery<?php echo $thumbs_capped ? ' data-book-gallery-thumbs-capped="1"' : ''; ?>>
				<span id="book-gallery-status-<?php echo esc_attr((string) $post_id); ?>" class="sr-only" data-book-gallery-status aria-live="polite"></span>
				<div class="relative aspect-2/3 overflow-hidden bg-page/50">
					<?php if (count($gallery_ids) > 1) : ?>
						<button type="button" class="absolute left-1 top-1/2 z-10 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full shadow-main bg-page/50 text-heading backdrop-blur-sm transition hover:bg-page hover:shadow-bronze-glow hover:text-body disabled:pointer-events-none disabled:opacity-30" data-book-gallery-prev aria-controls="book-gallery-slides-<?php echo esc_attr((string) $post_id); ?>">
							<span class="sr-only"><?php esc_html_e('Previous image', 'dynamic-book-archive'); ?></span>
							<?php dba_the_inline_icon('bx/bx-chevron-left', 'block h-8 w-8'); ?>
						</button>
						<button type="button" class="absolute right-1 top-1/2 z-10 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full shadow-main bg-page/50 text-heading backdrop-blur-sm transition hover:bg-page hover:shadow-bronze-glow hover:text-body disabled:pointer-events-none disabled:opacity-30" data-book-gallery-next aria-controls="book-gallery-slides-<?php echo esc_attr((string) $post_id); ?>">
							<span class="sr-only"><?php esc_html_e('Next image', 'dynamic-book-archive'); ?></span>
							<?php dba_the_inline_icon('bx/bx-chevron-right', 'block h-8 w-8'); ?>
						</button>
					<?php endif; ?>
					<div id="book-gallery-slides-<?php echo esc_attr((string) $post_id); ?>" class="contents">
						<?php
						$slide_class = 'absolute inset-0 m-0 z-0 opacity-0 transition-opacity duration-300 pointer-events-none aria-[hidden=false]:pointer-events-auto aria-[hidden=false]:z aria-[hidden=false]:opacity-100';
						foreach ($gallery_ids as $idx => $img_id) {
							$img_html = wp_get_attachment_image(
								$img_id,
								'large',
								false,
								array(
									'class'    => 'h-full w-full bg-main/30 object-contain object-top rounded-md',
									'loading'  => 0 === $idx ? 'eager' : 'lazy',
									'decoding' => 'async',
								)
							);
							if ('' === $img_html) {
								continue;
							}
							printf(
								'<figure class="%s" data-book-gallery-slide="%d" aria-hidden="%s">',
								esc_attr($slide_class),
								(int) $idx,
								0 === $idx ? 'false' : 'true'
							);
							echo $img_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() returns safe HTML.
							echo '</figure>';
						}
						?>
					</div>
				</div>

				<?php if (count($gallery_ids) > 1) : ?>
					<div class="hidden md:flex flex-col gap-2 shadow-main-top p-2">
						<?php
						$tablist_class = 'flex w-full gap-2 [scrollbar-width:thin]';
						if ($thumb_rail_rtl) {
							$tablist_class .= ' flex-row-reverse justify-start';
						} else {
							$tablist_class .= ' justify-end';
						}
						?>
						<div class="<?php echo esc_attr($tablist_class); ?>" role="tablist" aria-label="<?php esc_attr_e('Book images', 'dynamic-book-archive'); ?>">
							<?php
							$thumb_class = 'relative h-30 w-22 shrink-0 hover:z-20 overflow-hidden rounded border-2 border-transparent bg-page/25 opacity-80 transition hover:opacity-100 hover:shadow-bronze-glow hover:border-none duration-200 ease-out hover:scale-120 aria-pressed:border-book-secondary aria-pressed:opacity-100 aria-pressed:ring-1 aria-pressed:ring-heading/40';
							$thumb_count = min($thumb_limit, $gallery_count);
							for ($idx = 0; $idx < $thumb_count; $idx++) {
								$img_id = (int) $gallery_ids[ $idx ];
								$thumb_html = wp_get_attachment_image(
									$img_id,
									'thumbnail',
									false,
									array('class' => 'h-full w-full object-cover')
								);
								if ('' === $thumb_html) {
									continue;
								}
								$show_more_overlay = $thumbs_capped && 4 === $idx;
								if ($show_more_overlay) {
									$aria_thumb = sprintf(
										/* translators: 1: image number (1-based), 2: how many more images exist after the first five. */
										__('Show image %1$d, %2$d more in gallery', 'dynamic-book-archive'),
										$idx + 1,
										$gallery_count - 5
									);
								} else {
									$aria_thumb = sprintf(
										/* translators: %d: image number (1-based). */
										__('Show image %d', 'dynamic-book-archive'),
										$idx + 1
									);
								}
								printf(
									'<button type="button" class="%s" data-book-gallery-thumb="%d" role="tab" aria-pressed="%s" aria-label="%s">',
									esc_attr($thumb_class),
									(int) $idx,
									0 === $idx ? 'true' : 'false',
									esc_attr($aria_thumb)
								);
								echo $thumb_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() returns safe HTML.
								if ($show_more_overlay) {
									$more_n = (int) ($gallery_count - 5);
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
		<?php else : ?>
			<div class="flex w-full items-center justify-center rounded-lg border border-dashed border-library-primary-dark/50 bg-library-secondary/30 p-6 text-center text-sm text-library-primary/60">
				<?php esc_html_e('No cover image available.', 'dynamic-book-archive'); ?>
			</div>
		<?php endif; ?>

		<div class="flex min-w-0 flex-col gap-4 md:gap-6 lg:gap-12">
			<header>
				<?php if (!empty($title_japanese)) : ?>
					<h1 class="font-display text-4xl font-bold leading-tight text-book-primary sm:text-4xl md:text-4xl"><?php echo esc_html($title_japanese); ?></h1>
					<p class="mt-1.5 font-display text-2xl font-semibold leading-tight text-book-secondary sm:text-3xl"><?php the_title(); ?></p>
				<?php else : ?>
					<h1 class="font-display text-4xl font-semibold leading-tight text-book-primary sm:text-4xl md:text-4xl"><?php the_title(); ?></h1>
				<?php endif; ?>
			</header>

			<div class="grid gap-3 lg:grid-cols-[2fr_1fr]">
				<div class="flex min-w-0 flex-col gap-4">
					<dl class="space-y-0">
						<?php if (!empty($author_display)) : ?>
							<div class="grid grid-cols-[minmax(0,7.5rem)_1fr] gap-x-2 gap-y-1 py-1 text-base sm:grid-cols-[9rem_1fr]">
								<dt class="font-main tracking-wider text-book-secondary"><?php esc_html_e('Author:', 'dynamic-book-archive'); ?></dt>
								<dd class="font-main text-book-primary"><?php echo esc_html($author_display); ?></dd>
							</div>
						<?php endif; ?>
						<?php if (!empty($publication_label)) : ?>
							<div class="grid grid-cols-[minmax(0,7.5rem)_1fr] gap-x-2 gap-y-1 py-1 text-base sm:grid-cols-[9rem_1fr]">
								<dt class="font-main tracking-wider text-book-secondary"><?php esc_html_e('Published:', 'dynamic-book-archive'); ?></dt>
								<dd class="font-main text-book-primary"><?php echo esc_html($publication_label); ?></dd>
							</div>
						<?php endif; ?>
						<?php if (!empty($book_language)) : ?>
							<div class="grid grid-cols-[minmax(0,7.5rem)_1fr] gap-x-2 gap-y-1 py-1 text-base sm:grid-cols-[9rem_1fr]">
								<dt class="font-main tracking-wider text-book-secondary"><?php esc_html_e('Language:', 'dynamic-book-archive'); ?></dt>
								<dd class="font-main text-book-primary"><?php echo esc_html($book_language); ?></dd>
							</div>
						<?php endif; ?>
						<?php if (!empty($category_label)) : ?>
							<div class="grid grid-cols-[minmax(0,7.5rem)_1fr] gap-x-2 gap-y-1 py-1 text-base sm:grid-cols-[9rem_1fr]">
								<dt class="font-main tracking-wider text-book-secondary"><?php esc_html_e('Category:', 'dynamic-book-archive'); ?></dt>
								<dd class="font-main text-book-primary"><?php echo esc_html($category_label); ?></dd>
							</div>
						<?php endif; ?>
						<?php if (!empty($collection_label)) : ?>
							<div class="grid grid-cols-[minmax(0,7.5rem)_1fr] gap-x-2 gap-y-1 py-1 text-base sm:grid-cols-[9rem_1fr]">
								<dt class="font-main tracking-wider text-book-secondary"><?php esc_html_e('Collection:', 'dynamic-book-archive'); ?></dt>
								<dd class="font-main text-book-primary"><?php echo esc_html($collection_label); ?></dd>
							</div>
						<?php endif; ?>
					</dl>
					<hr class="h-px w-full shrink-0 border-0 bg-linear-to-r from-transparent from-0% via-book-primary/85 via-38% to-transparent to-100% [box-shadow:0_0_12px_color-mix(in_oklch,var(--color-book-primary-light)_35%,transparent)]" role="presentation" />
				</div>

				<?php if (count($quick_items) > 0) : ?>
					<aside class="rounded-lg mt-4 md:mt-0 border text-book-primary border-book-secondary/50 p-4" aria-label="<?php esc_attr_e('Quick facts', 'dynamic-book-archive'); ?>">
						<h2 class="mb-3 font-display text-sm font-semibold uppercase tracking-[0.2em] text-book-secondary"><?php esc_html_e('Quick info', 'dynamic-book-archive'); ?></h2>
						<ul class="m-0 flex list-none flex-col gap-2 p-0">
							<?php foreach ($quick_items as $item) : ?>
								<li class="flex place-items-center gap-2 text-sm leading-relaxed text-book-primary">
									<?php echo $dba_book_single_icon($item['icon']); ?>
									<span class="text-book-primary"><?php echo ($item['text']); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					</aside>
				<?php endif; ?>
			</div>

			<?php
			$post_obj = get_post($post_id);
			$content  = $post_obj instanceof WP_Post ? (string) $post_obj->post_content : '';
			?>
			<?php if (!empty(trim($content))) : ?>
				<section aria-labelledby="book-desc-heading-<?php echo esc_attr((string) $post_id); ?>">
					<h2 id="book-desc-heading-<?php echo esc_attr((string) $post_id); ?>" class="font-display text-sm font-semibold uppercase tracking-[0.25em] text-book-secondary"><?php esc_html_e('Description', 'dynamic-book-archive'); ?></h2>
					<div class="prose prose-invert prose-headings:font-display prose-headings:text-book-primary prose-p:my-4 lg:prose-p:my-6 mt-2 max-w-none font-main text-base md:text-lg leading-relaxed md:leading-[1.75] text-book-primary">
						<?php
						the_content();
						?>
					</div>
					<hr class="h-px w-full shrink-0 border-0 bg-linear-to-r from-transparent from-0% via-book-primary/85 via-38% to-transparent to-100% [box-shadow:0_0_12px_color-mix(in_oklch,var(--color-book-primary)_35%,transparent)]" role="presentation" />
				</section>
			<?php endif; ?>


			<?php if (count($tags) > 0) : ?>
				<section aria-labelledby="book-tags-heading-<?php echo esc_attr((string) $post_id); ?>">
					<h2 id="book-tags-heading-<?php echo esc_attr((string) $post_id); ?>" class="font-display text-sm font-semibold uppercase tracking-[0.25em] text-book-secondary"><?php esc_html_e('Tags', 'dynamic-book-archive'); ?></h2>
					<div class="mt-4 flex flex-wrap gap-1 md:gap-2 justify-center md:justify-start">
						<?php
						foreach ($tags as $term) {
							if (! $term instanceof WP_Term) {
								continue;
							}
							$link = get_term_link($term);
							if (is_wp_error($link) || ! is_string($link)) {
								continue;
							}
							printf(
								'<a class="inline-flex rounded-md shadow-main px-4 py-1.5 text-sm font-medium text-book-primary no-underline transition hover:bg-book-secondary/10 hover:shadow-bronze-glow duration-200 ease-out" href="%s">%s</a>',
								esc_url($link),
								esc_html($term->name)
							);
						}
						?>
					</div>
				</section>
			<?php endif; ?>
		</div>
	</div>
</article>
