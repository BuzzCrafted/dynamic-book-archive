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
$collection       = isset( $args['collection'] ) && is_array( $args['collection'] ) ? $args['collection'] : array();
$people           = isset( $args['people'] ) && is_array( $args['people'] ) ? $args['people'] : array();
$cover_image      = isset( $args['cover_image'] ) && is_array( $args['cover_image'] ) ? $args['cover_image'] : array();

$has_cover = ! empty( $cover_image['url'] );

$pub_date_label = $pub_date && function_exists( 'dba_format_archive_publication_date_label' )
	? dba_format_archive_publication_date_label( $pub_date )
	: $pub_date;

if ( '' === $title ) {
	return;
}

$meta_rows = array_filter(
	array(
		__( 'Publication', 'dynamic-book-archive' ) => $publication,
		__( 'Date', 'dynamic-book-archive' )        => $pub_date_label,
		__( 'Type', 'dynamic-book-archive' )         => $doc_type['name'] ?? '',
		__( 'Language', 'dynamic-book-archive' )     => $language,
	)
);
?>
<header class="overflow-hidden rounded-lg border border-border-soft bg-surface
	<?php echo $has_cover ? 'grid sm:grid-cols-[auto_1fr]' : ''; ?>">

	<?php if ( $has_cover ) : ?>
	<div class="flex items-stretch border-b border-border-soft bg-[--c-ink-950] sm:border-b-0 sm:border-r">
		<figure class="m-0 flex items-center justify-center">
			<img
				class="block h-auto w-[clamp(8rem,20vw,13rem)] object-cover object-top"
				src="<?php echo esc_url( $cover_image['url'] ); ?>"
				alt="<?php echo esc_attr( $cover_image['alt'] ); ?>"
				loading="eager"
				decoding="async"
			/>
		</figure>
	</div>
	<?php endif; ?>

	<div class="flex flex-col gap-3 p-8">

		<h1 class="m-0 font-display text-[clamp(1.5rem,4vw,2.25rem)] font-normal leading-tight text-heading">
			<?php echo esc_html( $title ); ?>
		</h1>

		<?php if ( '' !== $publication_line ) : ?>
		<p class="m-0 font-main text-[0.9375rem] text-body">
			<?php echo esc_html( $publication_line ); ?>
		</p>
		<?php endif; ?>

		<?php if ( ! empty( $meta_rows ) || ! empty( $collection['id'] ) || ! empty( $people ) ) : ?>
		<dl class="mt-2 grid grid-cols-[auto_1fr] items-baseline gap-x-6 gap-y-1.5">

			<?php foreach ( $meta_rows as $label => $value ) : ?>
			<dt class="whitespace-nowrap font-main text-xs uppercase tracking-wide text-body after:content-[':']">
				<?php echo esc_html( $label ); ?>
			</dt>
			<dd class="m-0 flex items-baseline gap-1.5 font-main text-sm text-heading before:shrink-0 before:text-body before:content-['·']">
				<?php echo esc_html( $value ); ?>
			</dd>
			<?php endforeach; ?>

			<?php if ( ! empty( $collection['id'] ) && (int) $collection['id'] > 0 ) : ?>
			<dt class="whitespace-nowrap font-main text-xs uppercase tracking-wide text-body after:content-[':']">
				<?php esc_html_e( 'Collection', 'dynamic-book-archive' ); ?>
			</dt>
			<dd class="m-0 flex items-baseline gap-1.5 font-main text-sm text-heading before:shrink-0 before:text-body before:content-['·']">
				<?php if ( ! empty( $collection['url'] ) ) : ?>
				<a class="text-link no-underline transition-colors hover:text-link-hover" href="<?php echo esc_url( $collection['url'] ); ?>"><?php echo esc_html( $collection['title'] ); ?></a>
				<?php else : ?>
				<?php echo esc_html( $collection['title'] ); ?>
				<?php endif; ?>
			</dd>
			<?php endif; ?>

			<?php if ( ! empty( $people ) ) : ?>
			<dt class="whitespace-nowrap font-main text-xs uppercase tracking-wide text-body after:content-[':']">
				<?php esc_html_e( 'People', 'dynamic-book-archive' ); ?>
			</dt>
			<dd class="m-0 flex flex-wrap items-baseline gap-1.5 font-main text-sm text-heading before:shrink-0 before:text-body before:content-['·']">
				<?php foreach ( $people as $idx => $person ) : ?>
				<?php if ( $idx > 0 ) : ?><span class="text-body">,</span><?php endif; ?>
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

	</div>

</header>
