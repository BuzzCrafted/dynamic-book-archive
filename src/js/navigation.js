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

	const menu = nav.getElementsByTagName('ul')[0];
	if (!menu) {
		toggleButton.style.display = 'none';
		return;
	}

	if (!menu.classList.contains('menu')) {
		menu.classList.add('menu');
	}

	toggleButton.addEventListener('click', function () {
		nav.classList.toggle('toggled');
		const isOpen = nav.classList.contains('toggled');
		toggleButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
	});

	document.addEventListener('click', function (event) {
		if (!nav.classList.contains('toggled')) {
			return;
		}
		const target = event.target;
		if (target instanceof Node && !nav.contains(target)) {
			nav.classList.remove('toggled');
			toggleButton.setAttribute('aria-expanded', 'false');
		}
	});
})();
