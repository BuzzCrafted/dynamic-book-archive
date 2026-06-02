<?php
/**
 * Single historical document — inline document viewer.
 *
 * Builds the REST config directly (the plugin auto-enqueues its Alpine bundle on
 * historical_document singular views via Archive_Assets_Controller::maybe_enqueue_viewer).
 * We bypass the [archive_document_viewer] shortcode so we can own the HTML layout.
 *
 * The `.archive-document-viewer` class is kept on the root element so the
 * plugin's `initDocumentViewerWhenPresent()` can find and register the Alpine
 * component before the DOM is scanned.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

if ( ! isset( $args ) || ! is_array( $args ) ) {
	return;
}

$post_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : 0;
if ( $post_id <= 0 ) {
	return;
}

// Build Alpine bootstrap config — mirrors what the shortcode builds internally.
$config = wp_json_encode(
	array(
		'documentId'    => $post_id,
		'pagesEndpoint' => esc_url_raw( rest_url( 'archive-cpt/v1/documents/' . $post_id . '/pages' ) ),
		'nonce'         => wp_create_nonce( 'wp_rest' ),
	)
);

// Unpack presenter meta for the static "About This Document" panel.
$publication    = isset( $args['publication'] ) ? (string) $args['publication'] : '';
$pub_date       = isset( $args['publication_date'] ) ? (string) $args['publication_date'] : '';
$language       = isset( $args['language'] ) ? (string) $args['language'] : '';
$doc_type       = isset( $args['document_type'] ) && is_array( $args['document_type'] ) ? $args['document_type'] : array();
$collection     = isset( $args['collection'] ) && is_array( $args['collection'] ) ? $args['collection'] : array();
$people         = isset( $args['people'] ) && is_array( $args['people'] ) ? $args['people'] : array();
$pub_date_label = $pub_date && function_exists( 'dba_format_archive_publication_date_label' )
	? dba_format_archive_publication_date_label( $pub_date )
	: $pub_date;
?>
<div
	class="archive-document-viewer"
	x-data="{ activeTab: 'translation' }"
>
<section
	id="dba-doc-viewer-<?php echo (int) $post_id; ?>"
	class="dba-doc-viewer"
	data-document-id="<?php echo (int) $post_id; ?>"
	data-config="<?php echo esc_attr( (string) $config ); ?>"
	x-data="archiveDocumentViewer()"
	x-init="init()"
>
	<!-- Loading / error / empty states -->
	<p class="dba-doc-viewer__status" x-show="loading">
		<?php esc_html_e( 'Loading pages…', 'dynamic-book-archive' ); ?>
	</p>
	<p class="dba-doc-viewer__status dba-doc-viewer__status--error"
	   x-show="!loading && error"
	   x-text="error"></p>
	<p class="dba-doc-viewer__status"
	   x-show="!loading && !error && total === 0">
		<?php esc_html_e( 'No document pages available.', 'dynamic-book-archive' ); ?>
	</p>

	<!-- Main two-column layout (image left, text right) -->
	<div class="dba-doc-viewer__layout" x-show="!loading && total > 0">

		<!-- Left column: scan image with overlaid prev/next navigation -->
		<div class="dba-doc-viewer__image-col">
			<button
				class="dba-doc-viewer__nav dba-doc-viewer__nav--prev"
				type="button"
				x-on:click="prev()"
				x-bind:disabled="index <= 0"
				aria-label="<?php esc_attr_e( 'Previous page', 'dynamic-book-archive' ); ?>"
			>&#8249;</button>
			<figure class="dba-doc-viewer__figure">
				<img
					x-bind:src="current ? current.view_url : ''"
					x-bind:alt="current ? current.title : ''"
					loading="lazy"
				/>
			</figure>
			<button
				class="dba-doc-viewer__nav dba-doc-viewer__nav--next"
				type="button"
				x-on:click="next()"
				x-bind:disabled="index >= total - 1"
				aria-label="<?php esc_attr_e( 'Next page', 'dynamic-book-archive' ); ?>"
			>&#8250;</button>
		</div>

		<!-- Right column: tab bar + panel content -->
		<div class="dba-doc-viewer__text-col">

			<!-- Tab bar + page counter -->
			<div class="dba-doc-viewer__tabs-bar" role="tablist">
				<button
					class="dba-doc-viewer__tab"
					role="tab"
					type="button"
					x-on:click="activeTab = 'translation'"
					x-bind:class="{ 'is-active': activeTab === 'translation' }"
					x-bind:aria-selected="activeTab === 'translation'"
				><?php esc_html_e( 'English Translation', 'dynamic-book-archive' ); ?></button>
				<button
					class="dba-doc-viewer__tab"
					role="tab"
					type="button"
					x-on:click="activeTab = 'about'"
					x-bind:class="{ 'is-active': activeTab === 'about' }"
					x-bind:aria-selected="activeTab === 'about'"
				><?php esc_html_e( 'About This Document', 'dynamic-book-archive' ); ?></button>
				<span class="dba-doc-viewer__counter" x-show="total > 0">
					<span x-text="'Page ' + (current ? current.page_number : 1) + ' of ' + total"></span>
				</span>
			</div>

			<!-- Translation panel -->
			<div class="dba-doc-viewer__panel" role="tabpanel" x-show="activeTab === 'translation'">
				<div class="dba-doc-viewer__prose" x-text="current ? current.translation : ''"></div>
				<a
					class="dba-doc-viewer__next-link"
					href="#"
					x-show="index < total - 1"
					x-on:click.prevent="next()"
				><?php esc_html_e( 'Continue reading on next page', 'dynamic-book-archive' ); ?> &rarr;</a>
			</div>

			<!-- About panel — static metadata from the presenter -->
			<div class="dba-doc-viewer__panel dba-doc-viewer__panel--about" role="tabpanel" x-show="activeTab === 'about'">
				<dl class="dba-doc-viewer__meta">
					<?php if ( ! empty( $publication ) ) : ?>
					<dt><?php esc_html_e( 'Publication', 'dynamic-book-archive' ); ?></dt>
					<dd><?php echo esc_html( $publication ); ?></dd>
					<?php endif; ?>

					<?php if ( ! empty( $pub_date_label ) ) : ?>
					<dt><?php esc_html_e( 'Date', 'dynamic-book-archive' ); ?></dt>
					<dd><?php echo esc_html( $pub_date_label ); ?></dd>
					<?php endif; ?>

					<?php if ( ! empty( $language ) ) : ?>
					<dt><?php esc_html_e( 'Language', 'dynamic-book-archive' ); ?></dt>
					<dd><?php echo esc_html( $language ); ?></dd>
					<?php endif; ?>

					<?php if ( ! empty( $doc_type['name'] ) ) : ?>
					<dt><?php esc_html_e( 'Document type', 'dynamic-book-archive' ); ?></dt>
					<dd><?php echo esc_html( $doc_type['name'] ); ?></dd>
					<?php endif; ?>

					<?php if ( ! empty( $collection['id'] ) && (int) $collection['id'] > 0 ) : ?>
					<dt><?php esc_html_e( 'Collection', 'dynamic-book-archive' ); ?></dt>
					<dd>
						<?php if ( ! empty( $collection['url'] ) ) : ?>
						<a href="<?php echo esc_url( $collection['url'] ); ?>"><?php echo esc_html( $collection['title'] ); ?></a>
						<?php else : ?>
						<?php echo esc_html( $collection['title'] ); ?>
						<?php endif; ?>
					</dd>
					<?php endif; ?>

					<?php if ( ! empty( $people ) ) : ?>
					<dt><?php esc_html_e( 'Related people', 'dynamic-book-archive' ); ?></dt>
					<dd>
						<?php foreach ( $people as $idx => $person ) : ?>
						<?php if ( $idx > 0 ) : ?>, <?php endif; ?>
						<?php if ( ! empty( $person['url'] ) ) : ?>
						<a href="<?php echo esc_url( $person['url'] ); ?>"><?php echo esc_html( $person['title'] ); ?></a>
						<?php else : ?>
						<?php echo esc_html( $person['title'] ); ?>
						<?php endif; ?>
						<?php endforeach; ?>
					</dd>
					<?php endif; ?>
				</dl>
			</div>
		</div>
	</div>

	<!-- Thumbnail strip (only shown when there are multiple pages) -->
	<div class="dba-doc-viewer__thumbs-strip" x-show="!loading && total > 1">
		<span class="dba-doc-viewer__thumbs-label">
			<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
				<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
			</svg>
			<?php esc_html_e( 'Thumbnails', 'dynamic-book-archive' ); ?>
		</span>
		<div class="dba-doc-viewer__thumbs-track">
			<template x-for="(page, i) in pages" x-bind:key="page.id">
				<button
					class="dba-doc-viewer__thumb"
					type="button"
					x-bind:class="{ 'is-active': i === index }"
					x-on:click="goTo(i)"
				>
					<img x-show="page.thumb_url" x-bind:src="page.thumb_url" loading="lazy" alt="" />
					<span x-text="page.page_number"></span>
				</button>
			</template>
		</div>
		<div class="dba-doc-viewer__thumbs-nav">
			<button
				type="button"
				x-on:click="prev()"
				x-bind:disabled="index <= 0"
			>&larr; <?php esc_html_e( 'Previous', 'dynamic-book-archive' ); ?></button>
			<button
				type="button"
				x-on:click="next()"
				x-bind:disabled="index >= total - 1"
			><?php esc_html_e( 'Next', 'dynamic-book-archive' ); ?> &rarr;</button>
		</div>
	</div>
</section>
</div>
