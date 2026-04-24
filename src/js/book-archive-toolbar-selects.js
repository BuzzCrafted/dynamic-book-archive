/**
 * Book archive toolbar: SlimSelect on toolbar `<select>`s, re-sync after AJAX updates.
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

		/**
		 * Dual range slider for publication years.
		 */
		(function initYearRange() {
			const wrap = root.querySelector('.dba-year-range');
			if (!(wrap instanceof HTMLElement)) {
				return;
			}

			const minInput = wrap.querySelector('#book-archive-toolbar-year-min');
			const maxInput = wrap.querySelector('#book-archive-toolbar-year-max');
			const minLabel = wrap.querySelector('.js-book-archive-year-min-label');
			const maxLabel = wrap.querySelector('.js-book-archive-year-max-label');
			const track = wrap.querySelector('.dba-year-range__track');

			if (
				!(minInput instanceof HTMLInputElement) ||
				!(maxInput instanceof HTMLInputElement) ||
				!(minLabel instanceof HTMLElement) ||
				!(maxLabel instanceof HTMLElement) ||
				!(track instanceof HTMLElement)
			) {
				return;
			}

			const floor = parseInt(String(wrap.dataset.yearFloor || ''), 10);
			const ceil = parseInt(String(wrap.dataset.yearCeil || ''), 10);
			const yearFloor = Number.isFinite(floor) ? floor : 1900;
			const yearCeil = Number.isFinite(ceil) ? ceil : yearFloor;

			function clamp(v) {
				if (!Number.isFinite(v)) return yearFloor;
				return Math.min(yearCeil, Math.max(yearFloor, Math.floor(v)));
			}

			function update() {
				let vMin = clamp(parseInt(String(minInput.value), 10));
				let vMax = clamp(parseInt(String(maxInput.value), 10));
				if (vMin > vMax) {
					const tmp = vMin;
					vMin = vMax;
					vMax = tmp;
				}
				minInput.value = String(vMin);
				maxInput.value = String(vMax);

				minLabel.textContent = vMin <= yearFloor ? 'Any' : String(vMin);
				maxLabel.textContent = vMax >= yearCeil ? 'Any' : String(vMax);

				const range = Math.max(1, yearCeil - yearFloor);
				const pctMin = ((vMin - yearFloor) / range) * 100;
				const pctMax = ((vMax - yearFloor) / range) * 100;
				track.style.setProperty('--dba-min', String(pctMin));
				track.style.setProperty('--dba-max', String(pctMax));
			}

			minInput.addEventListener('input', update, { passive: true });
			maxInput.addEventListener('input', update, { passive: true });
			update();
		})();

		const sortSelect = root.querySelector('#book-archive-toolbar-sort');
		const authorSelect = root.querySelector('#book-archive-toolbar-author');
		const tagSelect = root.querySelector('#book-archive-toolbar-tag');
		const slimSettings = {
			showSearch: false,
			contentLocation: document.body,
			contentPosition: 'fixed',
			openPosition: 'auto',
		};

		if (sortSelect instanceof HTMLSelectElement) {
			new SlimSelect({ select: sortSelect, settings: slimSettings });
		}
		if (authorSelect instanceof HTMLSelectElement) {
			new SlimSelect({ select: authorSelect, settings: slimSettings });
		}
		if (tagSelect instanceof HTMLSelectElement) {
			new SlimSelect({ select: tagSelect, settings: slimSettings });
		}

		root.addEventListener('books-cpt-archive-toolbar-synced', function () {
			if (sortSelect instanceof HTMLSelectElement) {
				syncSlimSelectFromNative(sortSelect);
			}
			if (authorSelect instanceof HTMLSelectElement) {
				syncSlimSelectFromNative(authorSelect);
			}
			if (tagSelect instanceof HTMLSelectElement) {
				syncSlimSelectFromNative(tagSelect);
			}
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initBookArchiveToolbarSelects);
	} else {
		initBookArchiveToolbarSelects();
	}
})();
