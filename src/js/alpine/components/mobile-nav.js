/**
 * Alpine component: mobileNav
 * Replaces navigation.js — mobile overlay toggle for #site-navigation.
 *
 * Refs expected in template:
 *   x-ref="menuToggle" on the hamburger button
 *   x-ref="menuClose"  on the X button inside the panel
 *
 * @returns {import('alpinejs').AlpineComponent}
 */
export function mobileNav() {
	return {
		open: false,

		init() {
			this.$watch('open', (isOpen) => {
				document.body.classList.toggle('mobile-menu-open', isOpen);
			});

			// Close on outside click.
			document.addEventListener('click', (event) => {
				if (!this.open) return;
				if (event.target instanceof Node && !this.$el.contains(event.target)) {
					this.close();
				}
			});
		},

		toggle() {
			if (this.open) {
				this.close();
			} else {
				this.openMenu();
			}
		},

		openMenu() {
			this.open = true;
			this.$nextTick(() => this.$refs.menuClose?.focus());
		},

		close() {
			if (!this.open) return;
			this.open = false;
			this.$nextTick(() => this.$refs.menuToggle?.focus());
		},
	};
}
