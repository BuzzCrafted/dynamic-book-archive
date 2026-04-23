/**
 * Book archive toolbar: SlimSelect on sort/year `<select>`s, re-sync after AJAX toolbar updates.
 */
(function () {
	'use strict';

	/**
	 * Refresh SlimSelect UI from the underlying native `<select>` (e.g. after options replaced).
	 *
	 * @param {HTMLSelectElement} selectEl
	 */
	function syncSlimSelectFromNative(selectEl) {
		const slim = selectEl.slim;
		if (
			!slim ||
			!slim.select ||
			!slim.store ||
			!slim.render ||
			typeof slim.select.getData !== 'function'
		) {
			return;
		}
		const data = slim.select.getData();
		slim.store.setData(data);
		slim.render.renderValues();
		slim.render.renderOptions(slim.store.getData());
	}

	function initBookArchiveToolbarSelects() {
		const root = document.querySelector('.js-book-archive');
		if (!root) {
			return;
		}

		const SlimSelect = window.SlimSelect;
		if (typeof SlimSelect !== 'function') {
			return;
		}

		const toolbar = root.querySelector('.js-book-archive-toolbar');
		if (!toolbar) {
			return;
		}

		const sortSelect = root.querySelector('#book-archive-toolbar-sort');
		const yearSelect = root.querySelector('#book-archive-toolbar-year');
		const slimSettings = {
			showSearch: false,
			contentLocation: document.body,
			contentPosition: 'fixed',
			openPosition: 'auto',
		};

		if (sortSelect instanceof HTMLSelectElement) {
			new SlimSelect({ select: sortSelect, settings: slimSettings });
		}
		if (yearSelect instanceof HTMLSelectElement) {
			new SlimSelect({ select: yearSelect, settings: slimSettings });
		}

		root.addEventListener('books-cpt-archive-toolbar-synced', function () {
			if (sortSelect instanceof HTMLSelectElement) {
				syncSlimSelectFromNative(sortSelect);
			}
			if (yearSelect instanceof HTMLSelectElement) {
				syncSlimSelectFromNative(yearSelect);
			}
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initBookArchiveToolbarSelects);
	} else {
		initBookArchiveToolbarSelects();
	}
})();
