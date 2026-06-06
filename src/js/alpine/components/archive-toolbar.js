/**
 * Alpine component: archiveToolbar
 * Replaces book-archive-toolbar-selects.js — sort/filter dialogs, staging
 * controls sync, SlimSelect initialization, and books-cpt integration.
 *
 * Refs expected in templates (all directly in archiveToolbar scope):
 *   x-ref="sortDialog"    on <dialog id="book-archive-dialog-sort">
 *   x-ref="filterDialog"  on <dialog id="book-archive-dialog-filter">
 *   x-ref="btnSort"       on the sort trigger button
 *   x-ref="btnFilter"     on the filter trigger button
 *   x-ref="realSort"      on <select id="book-archive-toolbar-sort">
 *   x-ref="realAuthor"    on <select id="book-archive-toolbar-author">
 *   x-ref="realTag"       on <select id="book-archive-toolbar-tag">
 *   x-ref="stagingSort"   on <select id="book-archive-toolbar-sort-staging">
 *   x-ref="stagingAuthor" on <select id="book-archive-toolbar-author-staging">
 *   x-ref="stagingTag"    on <select id="book-archive-toolbar-tag-staging">
 *   x-ref="searchInput"   on <input id="book-archive-toolbar-search">
 *
 * Year inputs are inside nested yearRange x-data scopes so $refs cannot reach
 * them. They are accessed by ID via document.getElementById instead.
 *
 * @returns {import('alpinejs').AlpineComponent}
 */
export function archiveToolbar() {
	let repositionRaf = 0;
	let slimInitialized = false;

	// ---------------------------------------------------------------------------
	// DOM helpers (imperative — no Alpine equivalent)
	// ---------------------------------------------------------------------------

	/**
	 * Refresh SlimSelect UI after the underlying native <select> options change.
	 * @param {HTMLSelectElement} selectEl
	 */
	function syncSlimSelectFromNative(selectEl) {
		const slim = selectEl.slim;
		if (!slim?.select?.getData || !slim.store || !slim.render) return;
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
	 * Place dialog just below the trigger; clamp to viewport, flip above if needed.
	 * Pure DOM measurement — no declarative Alpine equivalent.
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

	/** @param {HTMLDialogElement} dialog @param {HTMLButtonElement} trigger */
	function openAnchoredDialog(dialog, trigger) {
		dialog.showModal();
		requestAnimationFrame(() => {
			positionDialogBelowTrigger(dialog, trigger);
			requestAnimationFrame(() => positionDialogBelowTrigger(dialog, trigger));
		});
	}

	/**
	 * Year inputs live inside nested yearRange x-data scopes; $refs cannot
	 * cross x-data boundaries, so we use getElementById.
	 * @param {string} id
	 * @returns {HTMLInputElement | null}
	 */
	function yearInput(id) {
		const el = document.getElementById(id);
		return el instanceof HTMLInputElement ? el : null;
	}

	// ---------------------------------------------------------------------------
	// Component
	// ---------------------------------------------------------------------------

	return {
		sortOpen: false,
		filterOpen: false,
		searchQuery: '',

		init() {
			const initialSearch = this.$el.getAttribute('data-books-cpt-search');
			this.searchQuery =
				typeof initialSearch === 'string' ? initialSearch : '';

			// Initialize staging values from real controls on mount.
			this._syncSortStagingFromReal();
			this._syncFilterStagingFromReal(false);

			// Reposition anchored dialogs on resize/scroll.
			const repositionOpenDialogs = () => {
				if (!this.$refs.sortDialog?.open && !this.$refs.filterDialog?.open) return;
				if (repositionRaf) return;
				repositionRaf = window.requestAnimationFrame(() => {
					repositionRaf = 0;
					if (this.$refs.sortDialog?.open && this.$refs.btnSort) {
						positionDialogBelowTrigger(this.$refs.sortDialog, this.$refs.btnSort);
					}
					if (this.$refs.filterDialog?.open && this.$refs.btnFilter) {
						positionDialogBelowTrigger(this.$refs.filterDialog, this.$refs.btnFilter);
					}
				});
			};
			window.addEventListener('resize', repositionOpenDialogs, { passive: true });
			window.addEventListener('scroll', repositionOpenDialogs, { passive: true });
		},

		// Called by @close on the sort dialog in the template.
		onSortClose() {
			this.sortOpen = false;
		},

		// Called by @close on the filter dialog in the template.
		onFilterClose() {
			this.filterOpen = false;
		},

		// Called by @books-cpt-archive-toolbar-synced on the root in the template.
		onToolbarSynced() {
			if (this.$refs.filterDialog?.open) this._syncFilterStagingFromReal();
		},

		// -------------------------------------------------------------------------
		// Sort dialog
		// -------------------------------------------------------------------------

		openSort() {
			if (!this.$refs.sortDialog || !this.$refs.btnSort) return;
			if (this.$refs.filterDialog?.open) {
				this.$refs.filterDialog.close();
				this.filterOpen = false;
			}
			this._syncSortStagingFromReal();
			openAnchoredDialog(this.$refs.sortDialog, this.$refs.btnSort);
			this.sortOpen = true;
		},

		applySort() {
			const { realSort, stagingSort } = this.$refs;
			if (
				!(realSort instanceof HTMLSelectElement) ||
				!(stagingSort instanceof HTMLSelectElement)
			) return;
			realSort.value = stagingSort.value;
			realSort.dispatchEvent(new Event('change', { bubbles: true }));
			this.$refs.sortDialog?.close();
		},

		// -------------------------------------------------------------------------
		// Filter dialog
		// -------------------------------------------------------------------------

		openFilter() {
			if (!this.$refs.filterDialog || !this.$refs.btnFilter) return;
			if (this.$refs.sortDialog?.open) {
				this.$refs.sortDialog.close();
				this.sortOpen = false;
			}
			this._syncFilterStagingFromReal();
			openAnchoredDialog(this.$refs.filterDialog, this.$refs.btnFilter);
			this.filterOpen = true;

			if (!slimInitialized) {
				this.$nextTick(() => this._initSlimSelect());
			}
		},

		applyFilter() {
			const { realAuthor, realTag, stagingAuthor, stagingTag } = this.$refs;
			if (
				!(realAuthor instanceof HTMLSelectElement) ||
				!(realTag instanceof HTMLSelectElement) ||
				!(stagingAuthor instanceof HTMLSelectElement) ||
				!(stagingTag instanceof HTMLSelectElement)
			) return;

			const realYearMin = yearInput('book-archive-toolbar-year-min');
			const realYearMax = yearInput('book-archive-toolbar-year-max');
			const stagingYearMin = yearInput('book-archive-toolbar-year-min-staging');
			const stagingYearMax = yearInput('book-archive-toolbar-year-max-staging');
			if (!realYearMin || !realYearMax || !stagingYearMin || !stagingYearMax) return;

			realAuthor.value = stagingAuthor.value;
			realTag.value = stagingTag.value;

			let vMin = parseInt(stagingYearMin.value, 10);
			let vMax = parseInt(stagingYearMax.value, 10);
			if (Number.isFinite(vMin) && Number.isFinite(vMax) && vMin > vMax) {
				[vMin, vMax] = [vMax, vMin];
			}
			realYearMin.value = String(vMin);
			realYearMax.value = String(vMax);

			realAuthor.dispatchEvent(new Event('change', { bubbles: true }));
			this.$refs.filterDialog?.close();
		},

		submitSearch() {
			const { realSort, searchInput } = this.$refs;
			if (searchInput instanceof HTMLInputElement) {
				const query = String(searchInput.value).trim();
				this.searchQuery = query;
				searchInput.value = query;
			}
			if (realSort instanceof HTMLSelectElement) {
				realSort.dispatchEvent(new Event('change', { bubbles: true }));
			}
		},

		resetFilter() {
			const { stagingAuthor, stagingTag } = this.$refs;
			if (stagingAuthor instanceof HTMLSelectElement) {
				stagingAuthor.value = '';
				syncSlimSelectFromNative(stagingAuthor);
			}
			if (stagingTag instanceof HTMLSelectElement) {
				stagingTag.value = '';
				syncSlimSelectFromNative(stagingTag);
			}

			const stagingYearMin = yearInput('book-archive-toolbar-year-min-staging');
			const stagingYearMax = yearInput('book-archive-toolbar-year-max-staging');

			// The input min/max attributes hold the floor/ceil values set by PHP.
			const yf = parseInt(stagingYearMin?.min ?? '', 10) || 1900;
			const yc = parseInt(stagingYearMax?.max ?? '', 10) || yf;

			if (stagingYearMin) {
				stagingYearMin.value = String(yf);
				stagingYearMin.dispatchEvent(new Event('input', { bubbles: true }));
			}
			if (stagingYearMax) {
				stagingYearMax.value = String(yc);
				stagingYearMax.dispatchEvent(new Event('input', { bubbles: true }));
			}
		},

		// -------------------------------------------------------------------------
		// Private sync helpers
		// -------------------------------------------------------------------------

		_syncSortStagingFromReal() {
			const { realSort, stagingSort } = this.$refs;
			if (
				!(realSort instanceof HTMLSelectElement) ||
				!(stagingSort instanceof HTMLSelectElement)
			) return;
			stagingSort.value = realSort.value;
			syncSlimSelectFromNative(stagingSort);
		},

		_syncFilterStagingFromReal(syncSlim = true) {
			const { realAuthor, realTag, stagingAuthor, stagingTag } = this.$refs;
			if (
				!(realAuthor instanceof HTMLSelectElement) ||
				!(realTag instanceof HTMLSelectElement) ||
				!(stagingAuthor instanceof HTMLSelectElement) ||
				!(stagingTag instanceof HTMLSelectElement)
			) return;

			const realYearMin = yearInput('book-archive-toolbar-year-min');
			const realYearMax = yearInput('book-archive-toolbar-year-max');
			const stagingYearMin = yearInput('book-archive-toolbar-year-min-staging');
			const stagingYearMax = yearInput('book-archive-toolbar-year-max-staging');
			if (!realYearMin || !realYearMax || !stagingYearMin || !stagingYearMax) return;

			cloneSelectOptions(realAuthor, stagingAuthor);
			cloneSelectOptions(realTag, stagingTag);

			if (syncSlim) {
				syncSlimSelectFromNative(stagingAuthor);
				syncSlimSelectFromNative(stagingTag);
			}

			stagingYearMin.value = realYearMin.value;
			stagingYearMax.value = realYearMax.value;
			stagingYearMin.dispatchEvent(new Event('input', { bubbles: true }));
			stagingYearMax.dispatchEvent(new Event('input', { bubbles: true }));
		},

		_initSlimSelect() {
			const SlimSelect = window.SlimSelect;
			if (typeof SlimSelect !== 'function' || slimInitialized) return;
			slimInitialized = true;

			const { stagingSort, stagingAuthor, stagingTag, sortDialog, filterDialog } = this.$refs;
			if (!sortDialog || !filterDialog) return;

			function slimSettings(contentRoot) {
				return { showSearch: false, contentLocation: contentRoot, contentPosition: 'fixed', openPosition: 'auto' };
			}

			if (stagingSort instanceof HTMLSelectElement) {
				new SlimSelect({ select: stagingSort, settings: slimSettings(sortDialog) });
			}
			if (stagingAuthor instanceof HTMLSelectElement) {
				new SlimSelect({ select: stagingAuthor, settings: slimSettings(filterDialog) });
			}
			if (stagingTag instanceof HTMLSelectElement) {
				new SlimSelect({ select: stagingTag, settings: slimSettings(filterDialog) });
			}
		},
	};
}
