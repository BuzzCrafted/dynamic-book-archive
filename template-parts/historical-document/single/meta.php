<?php
/**
 * Single historical document metadata: publication, date, language, document type, people.
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

if ( ! isset( $args ) || ! is_array( $args ) ) {
	return;
}

$publication      = isset( $args['publication'] ) && is_string( $args['publication'] ) ? trim( $args['publication'] ) : '';
$publication_date = isset( $args['publication_date'] ) && is_string( $args['publication_date'] ) ? trim( $args['publication_date'] ) : '';
$language         = isset( $args['language'] ) && is_string( $args['language'] ) ? trim( $args['language'] ) : '';
$document_type    = isset( $args['document_type'] ) && is_array( $args['document_type'] ) ? $args['document_type'] : array();
$people           = isset( $args['people'] ) && is_array( $args['people'] ) ? $args['people'] : array();

$doc_type_name = isset( $document_type['name'] ) && is_string( $document_type['name'] ) ? $document_type['name'] : '';
$date_label    = '' !== $publication_date && function_exists( 'dba_format_archive_publication_date_label' )
	? dba_format_archive_publication_date_label( $publication_date )
	: '';

// Nothing to display.
if ( '' === $publication && '' === $date_label && '' === $language && '' === $doc_type_name && empty( $people ) ) {
	return;
}

$row_class = 'grid grid-cols-[minmax(0,7.5rem)_1fr] gap-x-2 gap-y-1 py-1 text-base sm:grid-cols-[9rem_1fr]';
$dt_class  = 'font-main tracking-wider text-brand-muted';
$dd_class  = 'font-main text-brand';

?>
<section aria-label="<?php esc_attr_e( 'Document details', 'dynamic-book-archive' ); ?>">
	<dl class="space-y-0">
		<?php
		if ( '' !== $publication ) {
			dba_component(
				'ui.dl-row',
				array(
					'label'     => __( 'Publication:', 'dynamic-book-archive' ),
					'value'     => $publication,
					'row_class' => $row_class,
					'dt_class'  => $dt_class,
					'dd_class'  => $dd_class,
				)
			);
		}

		if ( '' !== $date_label ) {
			dba_component(
				'ui.dl-row',
				array(
					'label'     => __( 'Date:', 'dynamic-book-archive' ),
					'value'     => $date_label,
					'row_class' => $row_class,
					'dt_class'  => $dt_class,
					'dd_class'  => $dd_class,
				)
			);
		}

		if ( '' !== $language ) {
			dba_component(
				'ui.dl-row',
				array(
					'label'     => __( 'Language:', 'dynamic-book-archive' ),
					'value'     => $language,
					'row_class' => $row_class,
					'dt_class'  => $dt_class,
					'dd_class'  => $dd_class,
				)
			);
		}

		if ( '' !== $doc_type_name ) {
			dba_component(
				'ui.dl-row',
				array(
					'label'     => __( 'Type:', 'dynamic-book-archive' ),
					'value'     => $doc_type_name,
					'row_class' => $row_class,
					'dt_class'  => $dt_class,
					'dd_class'  => $dd_class,
				)
			);
		}
		?>

		<?php if ( ! empty( $people ) ) : ?>
			<div class="<?php echo esc_attr( $row_class ); ?>">
				<dt class="<?php echo esc_attr( $dt_class ); ?>"><?php esc_html_e( 'People:', 'dynamic-book-archive' ); ?></dt>
				<dd class="<?php echo esc_attr( $dd_class ); ?>">
					<?php foreach ( $people as $i => $person ) : ?>
						<?php
						if ( ! is_array( $person ) ) {
							continue;
						}
						$person_title  = isset( $person['title'] ) && is_string( $person['title'] ) ? $person['title'] : '';
						$person_url    = isset( $person['url'] ) && is_string( $person['url'] ) ? $person['url'] : '';
						$person_status = isset( $person['post_status'] ) && is_string( $person['post_status'] ) ? $person['post_status'] : '';
						if ( '' === $person_title ) {
							continue;
						}
						if ( $i > 0 ) {
							echo ', ';
						}
						if ( '' !== $person_url && 'publish' === $person_status ) {
							printf(
								'<a class="text-brand underline underline-offset-2 hover:text-brand-muted" href="%1$s">%2$s</a>',
								esc_url( $person_url ),
								esc_html( $person_title )
							);
						} else {
							echo esc_html( $person_title );
						}
						?>
					<?php endforeach; ?>
				</dd>
			</div>
		<?php endif; ?>
	</dl>
</section>
