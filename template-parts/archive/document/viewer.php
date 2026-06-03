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
 * Alpine scope: inherited from the grid wrapper in page.php, which owns
 * archiveDocumentViewer(), activeTab, and viewMode. This template only renders
 * the viewer shell; all Alpine directives bind to the parent scope.
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

?>
<section
	id="dba-doc-viewer-<?php echo (int) $post_id; ?>"
	class="dba-doc-viewer"
	data-document-id="<?php echo (int) $post_id; ?>"
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
	<div
		class="dba-doc-viewer__layout"
		x-show="!loading && total > 0"
		x-bind:class="{ 'dba-doc-viewer__layout--single': viewMode !== 'both' }"
	>

		<!-- Left column: scan image with prev/next as grid siblings (outside the image, flush against it) -->
		<div class="dba-doc-viewer__image-col" x-show="viewMode !== 'text'">
			<figure class="dba-doc-viewer__figure">
				<?php
				dba_the_carousel_nav_button( array(
					'direction'  => 'prev',
					'variant'    => 'inline',
					'class'      => 'justify-self-end',
					'aria_label' => __( 'Previous page', 'dynamic-book-archive' ),
					'alpine'     => array( 'x-on:click' => 'prev()', 'x-bind:disabled' => 'index <= 0' ),
				) );
				?>
			<img
				class="js-doc-zoom-trigger"
				x-bind:src="current ? current.view_url : ''"
				x-bind:alt="current ? current.title : ''"
				loading="lazy"
				role="button"
				tabindex="0"
				aria-label="<?php esc_attr_e( 'Open image zoom', 'dynamic-book-archive' ); ?>"
				x-on:click="current && current.full_url && openZoom()"
				x-on:keydown="($event.key === 'Enter' || $event.key === ' ') && current && current.full_url && (openZoom(), $event.preventDefault())"
			/>
				<?php
				dba_the_carousel_nav_button( array(
					'direction'  => 'next',
					'variant'    => 'inline',
					'class'      => 'justify-self-start',
					'aria_label' => __( 'Next page', 'dynamic-book-archive' ),
					'alpine'     => array( 'x-on:click' => 'next()', 'x-bind:disabled' => 'index >= total - 1' ),
				) );
				?>
			</figure>
		</div>

		<!-- Right column: tab bar + panel content -->
		<div class="dba-doc-viewer__text-col" x-show="viewMode !== 'image'">

			<!-- Tab bar + page counter -->
			<div class="dba-doc-viewer__tabs-bar" role="tablist">
				<button
					class="dba-doc-viewer__tab"
					role="tab"
					type="button"
					x-on:click="activeTab = 'original'"
					x-bind:class="{ 'is-active': activeTab === 'original' }"
					x-bind:aria-selected="activeTab === 'original'"
				><?php esc_html_e( 'Original', 'dynamic-book-archive' ); ?></button>
				<button
					class="dba-doc-viewer__tab"
					role="tab"
					type="button"
					x-on:click="activeTab = 'translation'"
					x-bind:class="{ 'is-active': activeTab === 'translation' }"
					x-bind:aria-selected="activeTab === 'translation'"
				><?php esc_html_e( 'Translation', 'dynamic-book-archive' ); ?></button>
				<button
					class="dba-doc-viewer__tab"
					role="tab"
					type="button"
					x-on:click="activeTab = 'notes'"
					x-bind:class="{ 'is-active': activeTab === 'notes' }"
					x-bind:aria-selected="activeTab === 'notes'"
				><?php esc_html_e( 'Notes', 'dynamic-book-archive' ); ?></button>
				<span class="dba-doc-viewer__counter" x-show="total > 0">
					<span x-text="'Page ' + (index + 1) + ' of ' + total"></span>
				</span>
			</div>

			<!-- Original (transcription) panel -->
			<div class="dba-doc-viewer__panel" role="tabpanel" x-show="activeTab === 'original'">
				<div class="dba-doc-viewer__prose" x-text="current ? current.transcription : ''"></div>
			</div>

			<!-- Translation panel -->
			<div class="dba-doc-viewer__panel" role="tabpanel" x-show="activeTab === 'translation'">
				<div class="dba-doc-viewer__prose" x-text="current ? current.translation : ''"></div>
			</div>

			<!-- Notes (editorial notes) panel -->
			<div class="dba-doc-viewer__panel" role="tabpanel" x-show="activeTab === 'notes'">
				<div class="dba-doc-viewer__prose" x-text="current ? current.editorial_notes : ''"></div>
			</div>
		</div>
	</div>

	<!-- Fullscreen OpenSeadragon zoom overlay -->
	<div
		class="dba-doc-viewer__zoom"
		x-show="mode === 'zoom'"
		x-cloak
		x-on:keydown.escape.window="mode === 'zoom' && closeZoom()"
		role="dialog"
		aria-modal="true"
		aria-label="<?php esc_attr_e( 'Image zoom', 'dynamic-book-archive' ); ?>"
	>
		<div class="dba-doc-viewer__zoom-controls">

			<button
				id="dba-osd-zoom-in-<?php echo (int) $post_id; ?>"
				class="dba-doc-viewer__zoom-btn"
				type="button"
				aria-label="<?php esc_attr_e( 'Zoom in', 'dynamic-book-archive' ); ?>"
			><?php dba_the_inline_icon( 'bx/bx-zoom-in', 'block h-5 w-5' ); ?></button>

			<button
				id="dba-osd-zoom-out-<?php echo (int) $post_id; ?>"
				class="dba-doc-viewer__zoom-btn"
				type="button"
				aria-label="<?php esc_attr_e( 'Zoom out', 'dynamic-book-archive' ); ?>"
			><?php dba_the_inline_icon( 'bx/bx-zoom-out', 'block h-5 w-5' ); ?></button>

			<button
				id="dba-osd-fullpage-<?php echo (int) $post_id; ?>"
				class="dba-doc-viewer__zoom-btn"
				type="button"
				aria-label="<?php esc_attr_e( 'Full screen', 'dynamic-book-archive' ); ?>"
			><?php dba_the_inline_icon( 'bx/bx-fullscreen', 'block h-5 w-5' ); ?></button>

			<button
				class="dba-doc-viewer__zoom-btn"
				type="button"
				x-on:click="closeZoom()"
				aria-label="<?php esc_attr_e( 'Close zoom', 'dynamic-book-archive' ); ?>"
			><?php dba_the_inline_icon( 'bx/bx-x', 'block h-5 w-5' ); ?></button>

		</div>

		<div class="dba-doc-viewer__zoom-stage" x-ref="osd"></div>
	</div>

	<!-- Thumbnail strip (only shown when there are multiple pages) -->
	<div class="dba-doc-viewer__thumbs-strip" x-show="!loading && total > 1">
		<div class="dba-doc-viewer__thumbs-track">
			<template x-for="(page, i) in pages" x-bind:key="page.id">
				<button
					class="dba-doc-viewer__thumb"
					type="button"
					x-bind:class="{ 'is-active': i === index }"
					x-on:click="goTo(i)"
				>
					<img x-show="page.thumb_url" x-bind:src="page.thumb_url" loading="lazy" alt="" />
					<span x-text="i + 1"></span>
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
