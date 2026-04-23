/**
 * Single book image gallery: main slide, prev/next, thumb strip, optional "+N" cap, status text.
 */
(function () {
	'use strict';

	document.querySelectorAll('[data-book-gallery]').forEach(function (root) {
		const slides = [].slice.call(root.querySelectorAll('[data-book-gallery-slide]'));
		const thumbs = [].slice.call(root.querySelectorAll('[data-book-gallery-thumb]'));
		const prevBtn = root.querySelector('[data-book-gallery-prev]');
		const nextBtn = root.querySelector('[data-book-gallery-next]');
		const statusEl = root.querySelector('[data-book-gallery-status]');
		const thumbsCapped = root.getAttribute('data-book-gallery-thumbs-capped') === '1';

		if (slides.length === 0) {
			return;
		}

		let activeIndex = 0;

		/**
		 * Which thumb should show as pressed when the capped rail shows only the first five (+more).
		 */
		function thumbIndexForSlide(slideIndex) {
			if (!thumbsCapped || thumbs.length === 0 || slideIndex < 4) {
				return slideIndex;
			}
			return Math.min(4, thumbs.length - 1);
		}

		function updateStatus() {
			if (!statusEl) {
				return;
			}
			const template =
				(window.dbaBookSingleGallery && window.dbaBookSingleGallery.imageStatus) ||
				'Image %1$s of %2$s';
			statusEl.textContent = template
				.replace('%1$s', String(activeIndex + 1))
				.replace('%2$s', String(slides.length));
		}

		function goToSlide(targetIndex) {
			const total = slides.length;
			activeIndex = ((targetIndex % total) + total) % total;

			slides.forEach(function (slide, index) {
				slide.setAttribute('aria-hidden', index === activeIndex ? 'false' : 'true');
			});

			const pressedThumbIndex = thumbIndexForSlide(activeIndex);
			thumbs.forEach(function (thumb, index) {
				const pressed = index === pressedThumbIndex;
				thumb.setAttribute('aria-pressed', pressed ? 'true' : 'false');
			});

			if (prevBtn) {
				prevBtn.disabled = total < 2;
			}
			if (nextBtn) {
				nextBtn.disabled = total < 2;
			}

			updateStatus();
		}

		if (prevBtn) {
			prevBtn.addEventListener('click', function () {
				goToSlide(activeIndex - 1);
			});
		}
		if (nextBtn) {
			nextBtn.addEventListener('click', function () {
				goToSlide(activeIndex + 1);
			});
		}

		thumbs.forEach(function (thumb) {
			thumb.addEventListener('click', function () {
				const idx = parseInt(thumb.getAttribute('data-book-gallery-thumb'), 10);
				if (!isNaN(idx)) {
					goToSlide(idx);
				}
			});
		});

		goToSlide(0);
	});
})();
