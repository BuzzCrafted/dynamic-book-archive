/**
 * Fullscreen image lightbox for anchors with class `lightbox` (GLightbox).
 */
import GLightbox from 'glightbox';
import 'glightbox/dist/css/glightbox.min.css';

document.addEventListener('DOMContentLoaded', function () {
	const links = document.querySelectorAll('.lightbox');
	if (links.length === 0) {
		return;
	}

	// GLightbox groups items that share the same `data-gallery`. Without it, every `.lightbox`
	// on the page becomes one gallery. Unique values keep each link a single-image lightbox.
	links.forEach(function (el, index) {
		if (!el.getAttribute('data-gallery')) {
			el.setAttribute('data-gallery', 'dba-lightbox-solo-' + String(index));
		}
	});

	GLightbox({
		selector: '.lightbox',
		loop: false,
		touchNavigation: true,
	});
});
