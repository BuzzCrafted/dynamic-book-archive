<?php
/**
 * Single historical document — hero header.
 *
 * Two-column layout: featured image (left) + title / publication line /
 * key metadata (right). Degrades to single-column when no featured image
 * is set. Styled entirely with Tailwind v4 utility classes.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

if ( ! isset( $args ) || ! is_array( $args ) ) {
	return;
}

$post_id          = isset( $args['post_id'] ) ? (int) $args['post_id'] : 0;
$title            = isset( $args['title'] ) && is_string( $args['title'] ) ? $args['title'] : '';
$publication_line = isset( $args['publication_line'] ) && is_string( $args['publication_line'] ) ? $args['publication_line'] : '';
$publication      = isset( $args['publication'] ) && is_string( $args['publication'] ) ? $args['publication'] : '';
$pub_date         = isset( $args['publication_date'] ) && is_string( $args['publication_date'] ) ? $args['publication_date'] : '';
$language         = isset( $args['language'] ) && is_string( $args['language'] ) ? $args['language'] : '';
$doc_type         = isset( $args['document_type'] ) && is_array( $args['document_type'] ) ? $args['document_type'] : array();
$collections      = isset( $args['collections'] ) && is_array( $args['collections'] ) ? $args['collections'] : array();
$authors          = isset( $args['authors'] ) && is_array( $args['authors'] ) ? $args['authors'] : array();
$translators      = isset( $args['translators'] ) && is_array( $args['translators'] ) ? $args['translators'] : array();
$editor                  = isset( $args['editor'] ) && is_string( $args['editor'] ) ? $args['editor'] : '';
$publisher               = isset( $args['publisher'] ) && is_string( $args['publisher'] ) ? $args['publisher'] : '';
$original_japanese_title = isset( $args['original_japanese_title'] ) && is_string( $args['original_japanese_title'] ) ? $args['original_japanese_title'] : '';
$notes                   = isset( $args['notes'] ) && is_string( $args['notes'] ) ? $args['notes'] : '';
$people                  = isset( $args['people'] ) && is_array( $args['people'] ) ? $args['people'] : array();
$cover_image      = isset( $args['cover_image'] ) && is_array( $args['cover_image'] ) ? $args['cover_image'] : array();

$has_cover      = ! empty( $cover_image['url'] );
$cover_full_url = isset( $cover_image['full_url'] ) && is_string( $cover_image['full_url'] ) ? $cover_image['full_url'] : '';

$pub_date_label = $pub_date && function_exists( 'dba_format_archive_publication_date_label' )
	? dba_format_archive_publication_date_label( $pub_date )
	: $pub_date;

if ( '' === $title ) {
	return;
}

$meta_rows = array_filter(
	array(
		__( 'Publication', 'dynamic-book-archive' ) => $publication,
		__( 'Type', 'dynamic-book-archive' )         => $doc_type['name'] ?? '',
		__( 'Language', 'dynamic-book-archive' )     => $language,
		__( 'Editor', 'dynamic-book-archive' )       => $editor,
		__( 'Publisher', 'dynamic-book-archive' )    => $publisher,
	)
);

$has_meta = ! empty( $meta_rows ) || '' !== $original_japanese_title || ! empty( $collections ) || ! empty( $authors ) || ! empty( $translators ) || ! empty( $people );
?>
<header class="overflow-hidden rounded-lg border border-stroke-muted bg-surface
	<?php echo $has_cover ? 'grid sm:grid-cols-[auto_1fr]' : ''; ?>">

	<?php if ( $has_cover ) : ?>
	<div class="flex items-stretch border-b border-stroke-muted bg-surface sm:border-b-0 sm:border-r">
		<figure class="m-0 flex items-center justify-center">
			<img
				class="block h-auto w-[clamp(8rem,20vw,13rem)] object-cover object-top<?php echo '' !== $cover_full_url ? ' js-doc-zoom-trigger' : ''; ?>"
				src="<?php echo esc_url( $cover_image['url'] ); ?>"
				alt="<?php echo esc_attr( $cover_image['alt'] ); ?>"
				loading="eager"
				decoding="async"
				<?php if ( '' !== $cover_full_url ) : ?>
				data-full-url="<?php echo esc_url( $cover_full_url ); ?>"
				role="button"
				tabindex="0"
				aria-label="<?php esc_attr_e( 'Open image zoom', 'dynamic-book-archive' ); ?>"
				x-on:click="openZoom($event.currentTarget.dataset.fullUrl)"
				x-on:keydown="($event.key === 'Enter' || $event.key === ' ') && (openZoom($event.currentTarget.dataset.fullUrl), $event.preventDefault())"
				<?php endif; ?>
			/>
		</figure>
	</div>
	<?php endif; ?>

	<div class="flex flex-col gap-3 p-8">

		<h1 class="m-0 font-display text-[clamp(1.5rem,4vw,2.25rem)] font-normal leading-tight text-content">
			<?php echo esc_html( $title ); ?>
		</h1>

		<?php if ( '' !== $publication_line ) : ?>
		<p class="m-0 font-main text-sm text-brand-muted">
			<?php echo esc_html( $pub_date_label ); ?>
		</p>
		<?php endif; ?>

		<?php if ( $has_meta ) : ?>
		<dl class="mt-2 grid grid-cols-[auto_1fr] items-baseline gap-x-6 gap-y-1.5">

			<?php if ( '' !== $original_japanese_title ) : ?>
			<dt class="whitespace-nowrap font-main text-xs uppercase tracking-wide text-brand-muted after:content-[':']">
				<?php esc_html_e( 'Japanese title', 'dynamic-book-archive' ); ?>
			</dt>
			<dd class="m-0 flex items-baseline gap-1.5 font-main text-sm text-content">
				<?php echo esc_html( $original_japanese_title ); ?>
			</dd>
			<?php endif; ?>

			<?php foreach ( $meta_rows as $label => $value ) : ?>
			<dt class="whitespace-nowrap font-main text-xs uppercase tracking-wide text-brand-muted after:content-[':']">
				<?php echo esc_html( $label ); ?>
			</dt>
			<dd class="m-0 flex items-baseline gap-1.5 font-main text-sm text-content">
				<?php echo esc_html( $value ); ?>
			</dd>
			<?php endforeach; ?>

			<?php if ( ! empty( $collections ) ) : ?>
			<dt class="whitespace-nowrap font-main text-xs uppercase tracking-wide text-brand-muted after:content-[':']">
				<?php esc_html_e( 'Collection', 'dynamic-book-archive' ); ?>
			</dt>
			<dd class="m-0 flex flex-wrap items-baseline gap-1.5 font-main text-sm text-content">
				<?php foreach ( $collections as $idx => $col ) : ?>
				<?php if ( $idx > 0 ) : ?><span class="text-brand-muted">,</span><?php endif; ?>
				<?php if ( ! empty( $col['url'] ) ) : ?>
				<a class="text-link no-underline transition-colors hover:text-link-hover" href="<?php echo esc_url( $col['url'] ); ?>"><?php echo esc_html( $col['title'] ); ?></a>
				<?php else : ?>
				<?php echo esc_html( $col['title'] ); ?>
				<?php endif; ?>
				<?php endforeach; ?>
			</dd>
			<?php endif; ?>

			<?php if ( ! empty( $authors ) ) : ?>
			<dt class="whitespace-nowrap font-main text-xs uppercase tracking-wide text-brand-muted after:content-[':']">
				<?php esc_html_e( 'Authors', 'dynamic-book-archive' ); ?>
			</dt>
			<dd class="m-0 flex flex-wrap items-baseline gap-1.5 font-main text-sm text-content">
				<?php foreach ( $authors as $idx => $author ) : ?>
				<?php if ( $idx > 0 ) : ?><span class="text-brand-muted">,</span><?php endif; ?>
				<?php echo esc_html( $author ); ?>
				<?php endforeach; ?>
			</dd>
			<?php endif; ?>

			<?php if ( ! empty( $translators ) ) : ?>
			<dt class="whitespace-nowrap font-main text-xs uppercase tracking-wide text-brand-muted after:content-[':']">
				<?php esc_html_e( 'Translators', 'dynamic-book-archive' ); ?>
			</dt>
			<dd class="m-0 flex flex-wrap items-baseline gap-1.5 font-main text-sm text-content">
				<?php foreach ( $translators as $idx => $translator ) : ?>
				<?php if ( $idx > 0 ) : ?><span class="text-brand-muted">,</span><?php endif; ?>
				<?php echo esc_html( $translator ); ?>
				<?php endforeach; ?>
			</dd>
			<?php endif; ?>

			<?php if ( ! empty( $people ) ) : ?>
			<dt class="whitespace-nowrap font-main text-xs uppercase tracking-wide text-brand-muted after:content-[':']">
				<?php esc_html_e( 'People', 'dynamic-book-archive' ); ?>
			</dt>
			<dd class="m-0 flex flex-wrap items-baseline gap-1.5 font-main text-sm text-content">
				<?php foreach ( $people as $idx => $person ) : ?>
				<?php if ( $idx > 0 ) : ?><span class="text-brand-muted">,</span><?php endif; ?>
				<?php if ( ! empty( $person['url'] ) ) : ?>
				<a class="text-link no-underline transition-colors hover:text-link-hover" href="<?php echo esc_url( $person['url'] ); ?>"><?php echo esc_html( $person['title'] ); ?></a>
				<?php else : ?>
				<?php echo esc_html( $person['title'] ); ?>
				<?php endif; ?>
				<?php endforeach; ?>
			</dd>
			<?php endif; ?>

		</dl>
		<?php endif; ?>

		<?php if ( '' !== $notes ) : ?>
		<div class="border-t border-stroke-muted pt-4">
			<p class="m-0 mb-1.5 font-main text-xs uppercase tracking-wide text-brand-muted">
				<?php esc_html_e( 'Notes', 'dynamic-book-archive' ); ?>
			</p>
			<p class="m-0 whitespace-pre-wrap font-main text-sm italic text-content"><?php echo esc_html( $notes ); ?></p>
		</div>
		<?php endif; ?>

	</div>

</header>
