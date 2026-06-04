<?php
/**
 * View mode selector.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

?>
<div class="my-4 flex items-center  justify-center gap-3">
    <span class="font-main text-xs uppercase tracking-widest text-brand-muted">
        <?php esc_html_e('View:', 'dynamic-book-archive'); ?>
    </span>
    <div class="dba-doc-view-seg" role="group" aria-label="<?php esc_attr_e('View mode', 'dynamic-book-archive'); ?>">
        <button
            class="dba-doc-view-btn"
            type="button"
            x-on:click="viewMode = 'image'"
            x-bind:class="{ 'is-active': viewMode === 'image' }"
            x-bind:aria-pressed="viewMode === 'image'"><?php esc_html_e('Image', 'dynamic-book-archive'); ?></button>
        <button
            class="dba-doc-view-btn"
            type="button"
            x-on:click="viewMode = 'text'"
            x-bind:class="{ 'is-active': viewMode === 'text' }"
            x-bind:aria-pressed="viewMode === 'text'"><?php esc_html_e('Text', 'dynamic-book-archive'); ?></button>
        <button
            class="dba-doc-view-btn"
            type="button"
            x-on:click="viewMode = 'both'"
            x-bind:class="{ 'is-active': viewMode === 'both' }"
            x-bind:aria-pressed="viewMode === 'both'"><?php esc_html_e('Both', 'dynamic-book-archive'); ?></button>
    </div>
</div>