/**
 * Book archive toolbar: modals with staging controls, SlimSelect on staging `<select>`s, Apply → books-cpt.
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
		if (typeof slim.render.renderSingleValue === 'function') {
			slim.render.renderSingleValue();
		}
	}

	/**
	 * @param {HTMLSelectElement} from
	 * @param {HTMLSelectElement} to
	 */
	function cloneSelectOptions(from, to) {
		to.innerHTML = '';
		for (let i = 0; i < from.options.length; i++) {
			to.appendChild(from.options[i].cloneNode(true));
		}
		to.value = from.value;
	}

	/**
	 * @param {HTMLElement} wrap
	 */
	function initYearRange(wrap) {
		const minInput = wrap.querySelector(
			'.js-book-archive-year-min, .js-staging-year-min'
		);
		const maxInput = wrap.querySelector(
			'.js-book-archive-year-max, .js-staging-year-max'
		);
		const minLabel =
			wrap.querySelector('.js-book-archive-year-min-label') ||
			wrap.querySelector('.js-staging-year-min-label');
		const maxLabel =
			wrap.querySelector('.js-book-archive-year-max-label') ||
			wrap.querySelector('.js-staging-year-max-label');
		const track = wrap.querySelector('.dba-year-range__track');
		const tooltipMin = wrap.querySelector('.js-year-range-tooltip-min');
		const tooltipMax = wrap.querySelector('.js-year-range-tooltip-max');

		if (
			!(minInput instanceof HTMLInputElement) ||
			!(maxInput instanceof HTMLInputElement) ||
			!(track instanceof HTMLElement)
		) {
			return;
		}

		const floor = parseInt(String(wrap.dataset.yearFloor || ''), 10);
		const ceil = parseInt(String(wrap.dataset.yearCeil || ''), 10);
		const yearFloor = Number.isFinite(floor) ? floor : 1900;
		const yearCeil = Number.isFinite(ceil) ? ceil : yearFloor;
		const anyLabel = wrap.dataset.labelAny || 'Any';

		function clamp(v) {
			if (!Number.isFinite(v)) return yearFloor;
			return Math.min(yearCeil, Math.max(yearFloor, Math.floor(v)));
		}

		/** Keep tooltip centers inside the track so -translateX(50%) does not clip at modal edges. */
		function clampTooltipPct(pct) {
			const edge = 7;
			return Math.min(100 - edge, Math.max(edge, pct));
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

			if (minLabel instanceof HTMLElement) {
				minLabel.textContent = vMin <= yearFloor ? anyLabel : String(vMin);
			}
			if (maxLabel instanceof HTMLElement) {
				maxLabel.textContent = vMax >= yearCeil ? anyLabel : String(vMax);
			}

			const range = Math.max(1, yearCeil - yearFloor);
			const pctMin = ((vMin - yearFloor) / range) * 100;
			const pctMax = ((vMax - yearFloor) / range) * 100;
			track.style.setProperty('--dba-min', String(pctMin));
			track.style.setProperty('--dba-max', String(pctMax));

			if (tooltipMin instanceof HTMLElement) {
				tooltipMin.textContent = String(vMin);
				tooltipMin.style.left = `${clampTooltipPct(pctMin)}%`;
				tooltipMin.style.transform = 'translateX(-50%)';
			}
			if (tooltipMax instanceof HTMLElement) {
				tooltipMax.textContent = String(vMax);
				tooltipMax.style.left = `${clampTooltipPct(pctMax)}%`;
				tooltipMax.style.transform = 'translateX(-50%)';
			}
		}

		minInput.addEventListener('input', update, { passive: true });
		maxInput.addEventListener('input', update, { passive: true });
		update();
	}

	/**
	 * @param {HTMLDialogElement} dialog
	 * @param {HTMLButtonElement | null} trigger
	 * @param {boolean} open
	 */
	function setTriggerExpanded(dialog, trigger, open) {
		if (trigger) {
			trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
		}
	}

	/**
	 * Place modal dialog just under the trigger; clamp to viewport. Flips above if needed.
	 *
	 * @param {HTMLDialogElement} dialog
	 * @param {HTMLButtonElement} trigger
	 */
	function positionDialogBelowTrigger(dialog, trigger) {
		const gap = 8;
		const pad = 8;
		const br = trigger.getBoundingClientRect();
		dialog.style.margin = '0';
		dialog.style.position = 'fixed';
		dialog.style.inset = 'auto';

		const dw = dialog.offsetWidth || dialog.getBoundingClientRect().width;
		const dh = dialog.offsetHeight || 280;

		let left = br.left;
		if (br.left + br.width / 2 > window.innerWidth / 2) {
			left = br.right - dw;
		}
		left = Math.max(pad, Math.min(left, window.innerWidth - dw - pad));

		let top = br.bottom + gap;
		const roomBelow = window.innerHeight - br.bottom - gap - pad;
		const roomAbove = br.top - gap - pad;
		if (dh > roomBelow && roomAbove >= roomBelow) {
			top = Math.max(pad, br.top - dh - gap);
		}
		top = Math.max(pad, Math.min(top, window.innerHeight - dh - pad));

		dialog.style.left = `${Math.round(left)}px`;
		dialog.style.top = `${Math.round(top)}px`;
		dialog.style.right = 'auto';
		dialog.style.bottom = 'auto';
	}

	/**
	 * @param {HTMLDialogElement} dialog
	 * @param {HTMLButtonElement} trigger
	 */
	function openAnchoredDialog(dialog, trigger) {
		dialog.showModal();
		requestAnimationFrame(function () {
			positionDialogBelowTrigger(dialog, trigger);
			requestAnimationFrame(function () {
				positionDialogBelowTrigger(dialog, trigger);
			});
		});
	}

	function initBookArchiveToolbar() {
		const root = document.querySelector('.js-book-archive');
		if (!root) {
			return;
		}

		const ranges = root.querySelectorAll('.dba-year-range');
		for (let i = 0; i < ranges.length; i++) {
			const w = ranges[i];
			if (w instanceof HTMLElement) {
				initYearRange(w);
			}
		}

		const sortDialog = root.querySelector('#book-archive-dialog-sort');
		const filterDialog = root.querySelector('#book-archive-dialog-filter');
		const btnSort = root.querySelector('.js-book-archive-open-sort');
		const btnFilter = root.querySelector('.js-book-archive-open-filter');
		const btnSortApply = root.querySelector('.js-book-archive-sort-apply');
		const btnFilterApply = root.querySelector('.js-book-archive-filter-apply');
		const btnFilterReset = root.querySelector('.js-book-archive-filter-reset');

		const realSort = root.querySelector('#book-archive-toolbar-sort');
		const realAuthor = root.querySelector('#book-archive-toolbar-author');
		const realTag = root.querySelector('#book-archive-toolbar-tag');
		const realYearMin = root.querySelector('#book-archive-toolbar-year-min');
		const realYearMax = root.querySelector('#book-archive-toolbar-year-max');

		const stagingSort = root.querySelector('#book-archive-toolbar-sort-staging');
		const stagingAuthor = root.querySelector('#book-archive-toolbar-author-staging');
		const stagingTag = root.querySelector('#book-archive-toolbar-tag-staging');
		const stagingYearMin = root.querySelector('#book-archive-toolbar-year-min-staging');
		const stagingYearMax = root.querySelector('#book-archive-toolbar-year-max-staging');
		const stagingYearWrap = filterDialog
			? filterDialog.querySelector('.js-staging-year-range')
			: null;

		if (!(sortDialog instanceof HTMLDialogElement)) {
			return;
		}
		if (!(filterDialog instanceof HTMLDialogElement)) {
			return;
		}
		if (!(btnSort instanceof HTMLButtonElement) || !(btnFilter instanceof HTMLButtonElement)) {
			return;
		}
		if (
			!(realSort instanceof HTMLSelectElement) ||
			!(realAuthor instanceof HTMLSelectElement) ||
			!(realTag instanceof HTMLSelectElement) ||
			!(realYearMin instanceof HTMLInputElement) ||
			!(realYearMax instanceof HTMLInputElement) ||
			!(stagingSort instanceof HTMLSelectElement) ||
			!(stagingAuthor instanceof HTMLSelectElement) ||
			!(stagingTag instanceof HTMLSelectElement) ||
			!(stagingYearMin instanceof HTMLInputElement) ||
			!(stagingYearMax instanceof HTMLInputElement)
		) {
			return;
		}

		function syncSortStagingFromReal() {
			stagingSort.value = realSort.value;
			syncSlimSelectFromNative(stagingSort);
		}

		function syncFilterStagingFromReal() {
			cloneSelectOptions(realAuthor, stagingAuthor);
			cloneSelectOptions(realTag, stagingTag);
			syncSlimSelectFromNative(stagingAuthor);
			syncSlimSelectFromNative(stagingTag);
			stagingYearMin.value = realYearMin.value;
			stagingYearMax.value = realYearMax.value;
			stagingYearMin.dispatchEvent(new Event('input', { bubbles: true }));
			stagingYearMax.dispatchEvent(new Event('input', { bubbles: true }));
		}

		function resetFilterStaging() {
			stagingAuthor.value = '';
			stagingTag.value = '';
			syncSlimSelectFromNative(stagingAuthor);
			syncSlimSelectFromNative(stagingTag);
			const floor = parseInt(String(stagingYearWrap?.dataset.yearFloor || ''), 10);
			const ceil = parseInt(String(stagingYearWrap?.dataset.yearCeil || ''), 10);
			const yf = Number.isFinite(floor) ? floor : 1900;
			const yc = Number.isFinite(ceil) ? ceil : yf;
			stagingYearMin.value = String(yf);
			stagingYearMax.value = String(yc);
			stagingYearMin.dispatchEvent(new Event('input', { bubbles: true }));
			stagingYearMax.dispatchEvent(new Event('input', { bubbles: true }));
		}

		function applySort() {
			realSort.value = stagingSort.value;
			realSort.dispatchEvent(new Event('change', { bubbles: true }));
			sortDialog.close();
		}

		function applyFilter() {
			realAuthor.value = stagingAuthor.value;
			realTag.value = stagingTag.value;
			realYearMin.value = stagingYearMin.value;
			realYearMax.value = stagingYearMax.value;

			let vMin = parseInt(String(realYearMin.value), 10);
			let vMax = parseInt(String(realYearMax.value), 10);
			const floor = parseInt(String(realYearMin.min), 10);
			const ceil = parseInt(String(realYearMax.max), 10);
			const yf = Number.isFinite(floor) ? floor : 1900;
			const yc = Number.isFinite(ceil) ? ceil : yf;
			if (Number.isFinite(vMin) && Number.isFinite(vMax) && vMin > vMax) {
				const t = vMin;
				vMin = vMax;
				vMax = t;
				realYearMin.value = String(vMin);
				realYearMax.value = String(vMax);
			}

			realAuthor.dispatchEvent(new Event('change', { bubbles: true }));
			filterDialog.close();
		}

		let repositionRaf = 0;
		function repositionOpenDialogs() {
			if (!sortDialog.open && !filterDialog.open) {
				return;
			}
			if (repositionRaf) {
				return;
			}
			repositionRaf = window.requestAnimationFrame(function () {
				repositionRaf = 0;
				if (sortDialog.open) {
					positionDialogBelowTrigger(sortDialog, btnSort);
				}
				if (filterDialog.open) {
					positionDialogBelowTrigger(filterDialog, btnFilter);
				}
			});
		}

		btnSort.addEventListener('click', function () {
			if (filterDialog.open) {
				filterDialog.close();
			}
			syncSortStagingFromReal();
			openAnchoredDialog(sortDialog, btnSort);
			setTriggerExpanded(sortDialog, btnSort, true);
		});

		btnFilter.addEventListener('click', function () {
			if (sortDialog.open) {
				sortDialog.close();
			}
			syncFilterStagingFromReal();
			openAnchoredDialog(filterDialog, btnFilter);
			setTriggerExpanded(filterDialog, btnFilter, true);
		});

		window.addEventListener('resize', repositionOpenDialogs, { passive: true });
		window.addEventListener('scroll', repositionOpenDialogs, { passive: true });

		if (btnSortApply instanceof HTMLButtonElement) {
			btnSortApply.addEventListener('click', applySort);
		}
		if (btnFilterApply instanceof HTMLButtonElement) {
			btnFilterApply.addEventListener('click', applyFilter);
		}
		if (btnFilterReset instanceof HTMLButtonElement) {
			btnFilterReset.addEventListener('click', resetFilterStaging);
		}

		sortDialog.addEventListener('close', function () {
			setTriggerExpanded(sortDialog, btnSort, false);
		});
		filterDialog.addEventListener('close', function () {
			setTriggerExpanded(filterDialog, btnFilter, false);
		});

		root.addEventListener('books-cpt-archive-toolbar-synced', function () {
			if (filterDialog.open) {
				syncFilterStagingFromReal();
			}
		});

		stagingSort.value = realSort.value;
		cloneSelectOptions(realAuthor, stagingAuthor);
		cloneSelectOptions(realTag, stagingTag);

		const SlimSelect = window.SlimSelect;
		if (typeof SlimSelect === 'function') {
			/**
			 * @param {HTMLElement} contentRoot
			 */
			function slimSettings(contentRoot) {
				return {
					showSearch: false,
					contentLocation: contentRoot,
					contentPosition: 'fixed',
					openPosition: 'auto',
				};
			}

			new SlimSelect({
				select: stagingSort,
				settings: slimSettings(sortDialog),
			});
			new SlimSelect({
				select: stagingAuthor,
				settings: slimSettings(filterDialog),
			});
			new SlimSelect({
				select: stagingTag,
				settings: slimSettings(filterDialog),
			});
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initBookArchiveToolbar);
	} else {
		initBookArchiveToolbar();
	}
})();
