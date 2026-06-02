/**
 * Alpine component: bookGallery
 * Replaces book-single-gallery.js — single book image gallery with
 * main slide, prev/next, thumb strip, and screen-reader status.
 *
 * @param {{ total: number, capped: boolean }} config
 * @returns {import('alpinejs').AlpineComponent}
 */
export function bookGallery({ total, capped }) {
	return {
		activeIndex: 0,
		total,
		capped,

		get statusText() {
			const template =
				(window.dbaBookSingleGallery && window.dbaBookSingleGallery.imageStatus) ||
				'Image %1$s of %2$s';
			return template
				.replace('%1$s', String(this.activeIndex + 1))
				.replace('%2$s', String(this.total));
		},

		/**
		 * Which thumb index should be shown as "pressed" when the rail is capped
		 * at 5 (+more). Thumb 4 (the "+N" button) represents all slides beyond index 4.
		 */
		pressedThumbIndex(slideIndex) {
			if (!this.capped) return slideIndex;
			if (slideIndex < 4) return slideIndex;
			return 4;
		},

		goToSlide(index) {
			if (this.total <= 0) return;
			this.activeIndex = ((index % this.total) + this.total) % this.total;
		},

		prev() {
			this.goToSlide(this.activeIndex - 1);
		},

		next() {
			this.goToSlide(this.activeIndex + 1);
		},
	};
}
