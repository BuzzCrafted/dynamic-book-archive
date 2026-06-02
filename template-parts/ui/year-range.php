<?php
/**
 * Dual-handle publication year range.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

if ( ! isset( $args ) || ! is_array( $args ) ) {
	return;
}

$variant = isset( $args['variant'] ) && is_string( $args['variant'] ) ? $args['variant'] : '';
if ( '' === $variant || ( 'real' !== $variant && 'staging' !== $variant ) ) {
	return;
}

$year_floor   = isset( $args['year_floor'] ) ? (int) $args['year_floor'] : 0;
$year_ceiling = isset( $args['year_ceiling'] ) ? (int) $args['year_ceiling'] : 0;
if ( $year_floor <= 0 || $year_ceiling <= 0 || $year_floor > $year_ceiling ) {
	return;
}

$label_any = isset( $args['label_any'] ) && is_string( $args['label_any'] ) ? $args['label_any'] : __( 'Any', 'dynamic-book-archive' );

$input_year_min_id = isset( $args['input_year_min_id'] ) && is_string( $args['input_year_min_id'] ) ? $args['input_year_min_id'] : '';
$input_year_max_id = isset( $args['input_year_max_id'] ) && is_string( $args['input_year_max_id'] ) ? $args['input_year_max_id'] : '';
$input_min_class   = isset( $args['input_min_class'] ) && is_string( $args['input_min_class'] ) ? $args['input_min_class'] : '';
$input_max_class   = isset( $args['input_max_class'] ) && is_string( $args['input_max_class'] ) ? $args['input_max_class'] : '';

if ( '' === $input_year_min_id || '' === $input_year_max_id || '' === $input_min_class || '' === $input_max_class ) {
	return;
}

$floor_s = (string) $year_floor;
$ceil_s  = (string) $year_ceiling;

$scale_ticks = array();
$span        = $year_ceiling - $year_floor;
$step_count  = 4;
for ( $i = 0; $i <= $step_count; $i++ ) {
	$t             = $step_count > 0 ? ( $i / $step_count ) : 0.0;
	$scale_ticks[] = (int) round( $year_floor + $t * $span );
}
$scale_ticks = array_values( array_unique( $scale_ticks, SORT_NUMERIC ) );

$is_staging = 'staging' === $variant;

$wrap_class = 'dba-year-range w-full js-year-range-root';
if ( $is_staging ) {
	$wrap_class .= ' js-staging-year-range mt-3';
}

$alpine_config = wp_json_encode( array(
	'floor'    => $year_floor,
	'ceil'     => $year_ceiling,
	'labelAny' => $label_any,
) );
?>
<div
	class="<?php echo esc_attr( $wrap_class ); ?>"
	data-year-floor="<?php echo esc_attr( $floor_s ); ?>"
	data-year-ceil="<?php echo esc_attr( $ceil_s ); ?>"
	data-label-any="<?php echo esc_attr( $label_any ); ?>"
	x-data="yearRange(<?php echo esc_attr( $alpine_config ); ?>)">
	<?php if ( $is_staging ) : ?>
		<div class="mb-3">
			<span class="block text-xs font-semibold uppercase tracking-wider text-filters-text/80"><?php esc_html_e( 'Years', 'dynamic-book-archive' ); ?></span>
		</div>
		<div class="dba-year-range__sliders relative px-1 pb-2 sm:px-2">
			<div class="relative z-10 h-5">
				<label class="sr-only" for="<?php echo esc_attr( $input_year_min_id ); ?>"><?php esc_html_e( 'Filter by published year (from)', 'dynamic-book-archive' ); ?></label>
				<input
					id="<?php echo esc_attr( $input_year_min_id ); ?>"
					type="range"
					class="<?php echo esc_attr( trim( $input_min_class . ' dba-year-range__input' ) ); ?>"
					min="<?php echo esc_attr( $floor_s ); ?>"
					max="<?php echo esc_attr( $ceil_s ); ?>"
					value="<?php echo esc_attr( $floor_s ); ?>"
					step="1"
					x-model.number="vMin"
					aria-label="<?php echo esc_attr__( 'From year', 'dynamic-book-archive' ); ?>" />
				<label class="sr-only" for="<?php echo esc_attr( $input_year_max_id ); ?>"><?php esc_html_e( 'Filter by published year (to)', 'dynamic-book-archive' ); ?></label>
				<input
					id="<?php echo esc_attr( $input_year_max_id ); ?>"
					type="range"
					class="<?php echo esc_attr( trim( $input_max_class . ' dba-year-range__input' ) ); ?>"
					min="<?php echo esc_attr( $floor_s ); ?>"
					max="<?php echo esc_attr( $ceil_s ); ?>"
					value="<?php echo esc_attr( $ceil_s ); ?>"
					step="1"
					x-model.number="vMax"
					aria-label="<?php echo esc_attr__( 'To year', 'dynamic-book-archive' ); ?>" />
				<div class="dba-year-range__track" x-ref="track" aria-hidden="true"></div>
			</div>
			<div class="js-year-range-scale mt-0.5 flex w-full items-center text-xs tabular-nums text-filters-text/60" aria-hidden="true">
				<?php foreach ( $scale_ticks as $i => $tick ) : ?>
					<?php if ( $i > 0 ) : ?>
				<span class="flex min-h-3 min-w-0 flex-1 items-center justify-center">
					<span class="size-1 shrink-0 rounded-full bg-filters-text/45"></span>
				</span>
					<?php endif; ?>
				<span class="shrink-0 leading-none"><?php echo esc_html( (string) $tick ); ?></span>
				<?php endforeach; ?>
			</div>
			<div class="pointer-events-none relative mt-2 min-h-[2.125rem]">
				<span class="js-year-range-tooltip-min dba-year-range__tooltip-min absolute left-0 top-0 z-10 min-w-[2.25rem] rounded-md bg-heading px-2 py-1 text-center text-xs font-bold tabular-nums text-surface shadow-sm" aria-hidden="true"
					x-text="vMin"
					:style="tooltipMinStyle"></span>
				<span class="js-year-range-tooltip-max dba-year-range__tooltip-max pointer-events-none absolute left-0 top-0 z-10 min-w-[2.25rem] rounded-md bg-heading px-2 py-1 text-center text-xs font-bold tabular-nums text-surface shadow-sm" aria-hidden="true"
					x-text="vMax"
					:style="tooltipMaxStyle"></span>
			</div>
		</div>
	<?php else : ?>
		<span class="js-book-archive-year-min-label sr-only" x-text="labelMin"></span>
		<span class="js-book-archive-year-max-label sr-only" x-text="labelMax"></span>
		<div class="dba-year-range__sliders relative h-5">
			<label class="sr-only" for="<?php echo esc_attr( $input_year_min_id ); ?>"><?php esc_html_e( 'Filter by published year (from)', 'dynamic-book-archive' ); ?></label>
			<input
				id="<?php echo esc_attr( $input_year_min_id ); ?>"
				type="range"
				class="<?php echo esc_attr( trim( $input_min_class . ' dba-year-range__input' ) ); ?>"
				tabindex="-1"
				min="<?php echo esc_attr( $floor_s ); ?>"
				max="<?php echo esc_attr( $ceil_s ); ?>"
				value="<?php echo esc_attr( $floor_s ); ?>"
				step="1"
				x-model.number="vMin"
				aria-label="<?php echo esc_attr__( 'From year', 'dynamic-book-archive' ); ?>" />
			<label class="sr-only" for="<?php echo esc_attr( $input_year_max_id ); ?>"><?php esc_html_e( 'Filter by published year (to)', 'dynamic-book-archive' ); ?></label>
			<input
				id="<?php echo esc_attr( $input_year_max_id ); ?>"
				type="range"
				class="<?php echo esc_attr( trim( $input_max_class . ' dba-year-range__input' ) ); ?>"
				tabindex="-1"
				min="<?php echo esc_attr( $floor_s ); ?>"
				max="<?php echo esc_attr( $ceil_s ); ?>"
				value="<?php echo esc_attr( $ceil_s ); ?>"
				step="1"
				x-model.number="vMax"
				aria-label="<?php echo esc_attr__( 'To year', 'dynamic-book-archive' ); ?>" />
			<div class="dba-year-range__track" x-ref="track" aria-hidden="true"></div>
		</div>
	<?php endif; ?>
</div>
