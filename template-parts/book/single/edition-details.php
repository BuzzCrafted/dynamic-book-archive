<?php
/**
 * Single book edition details section.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

if ( ! isset( $args ) || ! is_array( $args ) ) {
	return;
}

$post_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : 0;
$lines   = isset( $args['edition_lines'] ) && is_array( $args['edition_lines'] ) ? $args['edition_lines'] : array();

if ( $post_id <= 0 || empty( $lines ) ) {
	return;
}

$row_class = 'grid grid-cols-[minmax(0,7.5rem)_1fr] gap-x-2 gap-y-1 py-1 text-base sm:grid-cols-[9rem_1fr]';
$dt_class  = 'font-main tracking-wider text-book-secondary';
$dd_class  = 'font-main text-book-primary';

?>
<section aria-labelledby="book-edition-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<h2 id="book-edition-heading-<?php echo esc_attr( (string) $post_id ); ?>" class="font-display text-sm font-semibold uppercase tracking-[0.25em] text-book-secondary"><?php esc_html_e( 'Edition details', 'dynamic-book-archive' ); ?></h2>
	<dl class="mt-2 space-y-0">
		<?php foreach ( $lines as $row ) : ?>
			<?php
			if ( ! is_array( $row ) ) {
				continue;
			}
			$label = isset( $row['label'] ) && is_string( $row['label'] ) ? $row['label'] : '';
			$value = isset( $row['value'] ) && is_string( $row['value'] ) ? $row['value'] : '';
			$value_lines = isset( $row['value_lines'] ) && is_array( $row['value_lines'] ) ? $row['value_lines'] : array();
			$value_lines = array_values(
				array_filter(
					array_map( 'strval', $value_lines ),
					static function ( string $v ): bool {
						$v = html_entity_decode( $v, ENT_QUOTES, 'UTF-8' );
						$v = (string) preg_replace( "/\\x{00A0}/u", ' ', $v );
						return '' !== trim( $v );
					}
				)
			);

			if ( '' === $label || '' === $value ) {
				continue;
			}

			if ( count( $value_lines ) > 0 ) {
				?>
				<div class="<?php echo esc_attr( $row_class ); ?>">
					<dt class="<?php echo esc_attr( $dt_class ); ?>"><?php echo esc_html( $label ); ?></dt>
					<dd class="<?php echo esc_attr( $dd_class ); ?>">
						<ul class="m-0 flex list-none flex-col gap-1 p-0 text-sm leading-relaxed">
							<?php foreach ( $value_lines as $line ) : ?>
								<li><?php echo esc_html( $line ); ?></li>
							<?php endforeach; ?>
						</ul>
					</dd>
				</div>
				<?php
			} else {
				get_template_part(
					'template-parts/ui/dl-row',
					null,
					array(
						'label'     => $label,
						'value'     => $value,
						'row_class' => $row_class,
						'dt_class'  => $dt_class,
						'dd_class'  => $dd_class,
					)
				);
			}
			?>
		<?php endforeach; ?>
	</dl>
	<hr class="mt-4 h-px w-full shrink-0 border-0 bg-linear-to-r from-transparent from-0% via-book-primary/85 via-38% to-transparent to-100% [box-shadow:0_0_12px_color-mix(in_oklch,var(--color-book-primary)_35%,transparent)]" role="presentation" />
</section>

