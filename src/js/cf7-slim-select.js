/**
 * Contact Form 7: initialize SlimSelect on every `<select>` rendered by CF7.
 *
 * CF7 keeps the native `<select>` in the DOM, so existing CF7 validation
 * and submission continue to work unchanged. SlimSelect mirrors the
 * native value via the underlying element.
 */
(function () {
	'use strict';

	function initSelect(selectEl) {
		if (!(selectEl instanceof HTMLSelectElement)) {
			return;
		}
		if (selectEl.slim) {
			return;
		}
		const SlimSelect = window.SlimSelect;
		if (typeof SlimSelect !== 'function') {
			return;
		}
		new SlimSelect({
			select: selectEl,
			settings: { showSearch: false },
		});
	}

	/**
	 * @param {ParentNode} root
	 */
	function initForm(root) {
		if (!root || typeof root.querySelectorAll !== 'function') {
			return;
		}
		const selects = root.querySelectorAll('select.wpcf7-form-control');
		for (let i = 0; i < selects.length; i++) {
			initSelect(selects[i]);
		}
	}

	function initAll() {
		const forms = document.querySelectorAll('.wpcf7');
		for (let i = 0; i < forms.length; i++) {
			initForm(forms[i]);
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initAll);
	} else {
		initAll();
	}

	document.addEventListener('wpcf7init', function (event) {
		const target = event.target;
		if (target instanceof Element) {
			initForm(target);
		} else {
			initAll();
		}
	});
})();
