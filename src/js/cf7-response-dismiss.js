/**
 * Contact Form 7: auto-dismiss success messages after a short delay.
 */
(function () {
	'use strict';

	const DISMISS_DELAY_MS = 5000;
	const HIDE_TRANSITION_MS = 600;
	const timers = new WeakMap();

	/**
	 * CF7 6+ dispatches custom events on the form; older docs reference the
	 * `.wpcf7` wrapper. Resolve both shapes, with unitTag as a fallback.
	 *
	 * @param {Event} event
	 * @returns {{ form: HTMLFormElement, output: Element, root: Element | null } | null}
	 */
	function getFormContext(event) {
		let form = null;
		let root = null;

		const target = event.target;
		if (target instanceof HTMLFormElement && target.classList.contains('wpcf7-form')) {
			form = target;
			root = target.closest('.wpcf7');
		} else if (target instanceof Element && target.classList.contains('wpcf7')) {
			root = target;
			form = target.querySelector('form');
		}

		const detail = event.detail;
		if (!(form instanceof HTMLFormElement) && detail && typeof detail.unitTag === 'string') {
			root = document.getElementById(detail.unitTag);
			if (root instanceof Element) {
				form = root.querySelector('form');
			}
		}

		if (!(form instanceof HTMLFormElement)) {
			return null;
		}

		const output = form.querySelector('.wpcf7-response-output');
		if (!output) {
			return null;
		}

		return { form, output, root };
	}

	/**
	 * @param {HTMLFormElement} form
	 */
	function clearTimer(form) {
		const timerId = timers.get(form);
		if (timerId !== undefined) {
			window.clearTimeout(timerId);
			timers.delete(form);
		}
	}

	/**
	 * @param {HTMLFormElement} form
	 * @param {() => void} callback
	 * @param {number} delay
	 */
	function setTimer(form, callback, delay) {
		clearTimer(form);
		timers.set(
			form,
			window.setTimeout(callback, delay)
		);
	}

	/**
	 * @param {HTMLFormElement} form
	 * @param {Element} output
	 * @param {Element | null} root
	 */
	function finishHide(form, output, root) {
		output.classList.remove('is-hiding');
		form.classList.remove('sent');
		form.setAttribute('data-status', 'init');
		output.style.display = 'none';
		output.setAttribute('aria-hidden', 'true');

		const wrapper = root instanceof Element ? root : form.closest('.wpcf7');
		const srStatus = wrapper?.querySelector('.screen-reader-response [role="status"]');
		if (srStatus) {
			srStatus.textContent = '';
		}
	}

	/**
	 * @param {HTMLFormElement} form
	 * @param {Element} output
	 * @param {Element | null} root
	 */
	function hideOutput(form, output, root) {
		output.classList.add('is-hiding');
		output.setAttribute('aria-hidden', 'false');

		let finished = false;
		function complete() {
			if (finished) {
				return;
			}
			finished = true;
			finishHide(form, output, root);
		}

		output.addEventListener(
			'transitionend',
			function onEnd(e) {
				if (e.propertyName === 'opacity') {
					complete();
				}
			},
			{ once: true }
		);

		window.setTimeout(complete, HIDE_TRANSITION_MS);
	}

	document.addEventListener('wpcf7mailsent', function (event) {
		const context = getFormContext(event);
		if (!context) {
			return;
		}

		const { form, output, root } = context;

		clearTimer(form);
		setTimer(form, function () {
			hideOutput(form, output, root);
		}, DISMISS_DELAY_MS);
	});

	// Reset only when the user starts a new submission — not on wpcf7submit,
	// which CF7 fires after wpcf7mailsent and would cancel the dismiss timer.
	document.addEventListener(
		'submit',
		function (event) {
			const form = event.target;
			if (!(form instanceof HTMLFormElement) || !form.classList.contains('wpcf7-form')) {
				return;
			}

			clearTimer(form);

			const output = form.querySelector('.wpcf7-response-output');
			if (output) {
				output.classList.remove('is-hiding');
				output.style.display = '';
				output.removeAttribute('aria-hidden');
			}
		},
		true
	);
})();
