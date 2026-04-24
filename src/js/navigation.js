/**
 * Mobile overlay toggle for `#site-navigation` (panel `#primary-menu-panel` with `.js-menu`).
 */
(function () {
	const nav = document.getElementById('site-navigation');
	if (!nav) {
		return;
	}

	const toggleButton = nav.querySelector('.js-menu-toggle');
	if (!toggleButton || !(toggleButton instanceof HTMLButtonElement)) {
		return;
	}

	const menu = nav.getElementsByClassName('js-menu')[0];
	if (!menu) {
		toggleButton.classList.add('hidden');
		return;
	}

	const closeButton = menu.querySelector('.js-menu-close');
	const bodyOpenClass = 'mobile-menu-open';

	function setOpen(isOpen) {
		menu.classList.toggle('hidden', !isOpen);
		toggleButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
		document.body.classList.toggle(bodyOpenClass, isOpen);
		if (isOpen) {
			if (closeButton instanceof HTMLElement) {
				closeButton.focus();
			}
		} else {
			toggleButton.focus();
		}
	}

	toggleButton.addEventListener('click', function (event) {
		event.stopPropagation();
		const willOpen = menu.classList.contains('hidden');
		setOpen(willOpen);
	});

	if (closeButton instanceof HTMLButtonElement) {
		closeButton.addEventListener('click', function (event) {
			event.stopPropagation();
			setOpen(false);
		});
	}

	document.addEventListener('keydown', function (event) {
		if (menu.classList.contains('hidden')) {
			return;
		}
		if (event.key !== 'Escape') {
			return;
		}
		event.preventDefault();
		setOpen(false);
	});

	document.addEventListener('click', function (event) {
		if (menu.classList.contains('hidden')) {
			return;
		}
		const target = event.target;
		if (target instanceof Node && !nav.contains(target)) {
			setOpen(false);
		}
	});
})();
