/**
 * Alpine bootstrap: bundle Alpine into this script so components are always
 * registered before Alpine.start() scans the DOM.
 *
 * Handles the third-party Alpine reuse case: if another plugin has already
 * placed Alpine on window and started it, we use their instance and call
 * initTree() on theme roots instead of start().
 *
 * @param {import('alpinejs').Alpine} bundledAlpine - Alpine imported from our bundle
 * @param {(alpine: import('alpinejs').Alpine) => void} register
 */
export function bootAlpine(bundledAlpine, register) {
	const thirdPartyAlreadyActive = Boolean(window.Alpine);

	if (!window.Alpine) {
		window.Alpine = bundledAlpine;
	}

	const alpine = window.Alpine;

	if (!window.__dbaAlpineRegistered) {
		window.__dbaAlpineRegistered = true;
		register(alpine);
	}

	if (thirdPartyAlreadyActive) {
		// Third-party Alpine has already scanned the DOM; activate any theme
		// roots that were parsed but not yet initialized.
		document
			.querySelectorAll('[data-book-archive-root],[data-book-gallery],#site-navigation')
			.forEach((el) => alpine.initTree(el));
		return;
	}

	if (!window.__dbaAlpineStarted) {
		window.__dbaAlpineStarted = true;
		alpine.start();
	}
}
