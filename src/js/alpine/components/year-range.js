/**
 * Alpine component: yearRange
 * Replaces initYearRange() from book-archive-toolbar-selects.js.
 * Dual-handle publication year range with tooltip positioning and CSS track fill.
 *
 * @param {{ floor: number, ceil: number, labelAny: string }} config
 * @returns {import('alpinejs').AlpineComponent}
 */
export function yearRange({ floor, ceil, labelAny }) {
	const yearFloor = Number.isFinite(floor) ? floor : 1900;
	const yearCeil = Number.isFinite(ceil) ? ceil : yearFloor;

	function clamp(v) {
		if (!Number.isFinite(v)) return yearFloor;
		return Math.min(yearCeil, Math.max(yearFloor, Math.floor(v)));
	}

	/** Keep tooltip centres inside the track to avoid edge clipping. */
	function clampTooltipPct(pct) {
		const edge = 7;
		return Math.min(100 - edge, Math.max(edge, pct));
	}

	return {
		vMin: yearFloor,
		vMax: yearCeil,
		floor: yearFloor,
		ceil: yearCeil,
		labelAny,

		get labelMin() {
			return this.vMin <= this.floor ? this.labelAny : String(this.vMin);
		},

		get labelMax() {
			return this.vMax >= this.ceil ? this.labelAny : String(this.vMax);
		},

		get pctMin() {
			const range = Math.max(1, this.ceil - this.floor);
			return ((this.vMin - this.floor) / range) * 100;
		},

		get pctMax() {
			const range = Math.max(1, this.ceil - this.floor);
			return ((this.vMax - this.floor) / range) * 100;
		},

		get tooltipMinStyle() {
			return `left:${clampTooltipPct(this.pctMin)}%;transform:translateX(-50%)`;
		},

		get tooltipMaxStyle() {
			return `left:${clampTooltipPct(this.pctMax)}%;transform:translateX(-50%)`;
		},

		update() {
			let vMin = clamp(this.vMin);
			let vMax = clamp(this.vMax);
			if (vMin > vMax) {
				[vMin, vMax] = [vMax, vMin];
			}
			this.vMin = vMin;
			this.vMax = vMax;

			// $refs.track: x-ref="track" on .dba-year-range__track in the template.
			const range = Math.max(1, this.ceil - this.floor);
			this.$refs.track?.style.setProperty('--dba-min', String(((vMin - this.floor) / range) * 100));
			this.$refs.track?.style.setProperty('--dba-max', String(((vMax - this.floor) / range) * 100));
		},

		init() {
			this.$watch('vMin', () => this.update());
			this.$watch('vMax', () => this.update());
			this.update();
		},
	};
}
