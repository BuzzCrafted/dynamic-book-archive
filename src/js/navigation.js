/**
 * Mobile / drawer toggle for `#site-navigation` (underscores-style menu markup).
 */
(function () {
	const nav = document.getElementById('site-navigation');
	if (!nav) {
		return;
	}

	const toggleButton = nav.getElementsByTagName('button')[0];
	if (!toggleButton) {
		return;
	}

	const menu = nav.getElementsByClassName('js-menu')[0];
	if (!menu) {
		toggleButton.classList.add('hidden');
		return;
	}

	toggleButton.addEventListener('click', function (event) {
		event.stopPropagation();
		menu.classList.toggle('hidden');
		const isOpen = !menu.classList.contains('hidden');
		toggleButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
	});

	document.addEventListener('keydown', function (event) {
		if (menu.classList.contains('hidden')) {
			return;
		}
		if (event.key !== 'Escape') {
			return;
		}
		event.preventDefault();
		menu.classList.add('hidden');
		toggleButton.setAttribute('aria-expanded', 'false');
		toggleButton.focus();
	});

	document.addEventListener('click', function (event) {
		if (menu.classList.contains('hidden')) {
			return;
		}
		const target = event.target;
		if (target instanceof Node && !nav.contains(target)) {
			menu.classList.add('hidden');
			toggleButton.setAttribute('aria-expanded', 'false');
		}
	});
})();
