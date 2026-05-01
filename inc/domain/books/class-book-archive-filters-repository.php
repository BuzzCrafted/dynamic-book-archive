<?php
/**
 * Book archive filter datasets (authors, tags, years).
 *
 * @package Dynamic_Book_Archive
 */

declare(strict_types=1);

namespace DBA\Domain\Books;

/**
 * Data access layer for distinct filter values used by the book archive UI.
 */
final class Book_Archive_Filters_Repository {

	private const TRANSIENT_PUB_YEARS = 'dba_book_archive_pub_years_v' . DBA_VERSION;
	private const TRANSIENT_AUTHORS   = 'dba_book_archive_authors_v' . DBA_VERSION;
	private const TRANSIENT_TAGS      = 'dba_book_archive_tags_v' . DBA_VERSION;

	/**
	 * Clear cached filter datasets when book content changes.
	 */
	public static function register_hooks(): void {
		add_action( 'save_post_book', array( self::class, 'invalidate_caches_on_book_save' ), 10, 3 );
		add_action( 'before_delete_post', array( self::class, 'invalidate_caches_on_book_delete' ), 10, 2 );
	}

	/**
	 * Delete all book-archive filter transients.
	 */
	public static function invalidate_caches(): void {
		delete_transient( self::TRANSIENT_PUB_YEARS );
		delete_transient( self::TRANSIENT_AUTHORS );
		delete_transient( self::TRANSIENT_TAGS );
	}

	/**
	 * Invalidate after book insert/update (not revisions/autosaves).
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 * @param bool     $update  Whether this is an existing post being updated.
	 */
	public static function invalidate_caches_on_book_save( int $post_id, $post, bool $update ): void {
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}
		self::invalidate_caches();
	}

	/**
	 * Invalidate when any post is about to be deleted; no-op unless type is `book`.
	 *
	 * @param int           $post_id Post ID.
	 * @param \WP_Post|null $post    Post object.
	 */
	public static function invalidate_caches_on_book_delete( int $post_id, $post = null ): void {
		if ( 'book' !== get_post_type( $post_id ) ) {
			return;
		}
		self::invalidate_caches();
	}

	/**
	 * Distinct publication years from published books (publication_date meta), newest first.
	 *
	 * Cached 1 hour; cleared on book save/update/delete.
	 *
	 * @return array<int, int>
	 */
	public static function get_distinct_publication_years(): array {
		$key    = self::TRANSIENT_PUB_YEARS;
		$cached = get_transient( $key );
		if ( false !== $cached && is_array( $cached ) ) {
			$out = array();
			foreach ( $cached as $v ) {
				$y = (int) $v;
				if ( $y <= 2100 ) {
					$out[] = $y;
				}
			}
			return array_values( array_unique( $out ) );
		}

		global $wpdb;

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- identifiers are $wpdb table names.
		$sql = "
			SELECT DISTINCT YEAR( CAST( {$wpdb->postmeta}.meta_value AS DATE ) ) AS y
			FROM {$wpdb->postmeta}
			INNER JOIN {$wpdb->posts} ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id
			WHERE {$wpdb->postmeta}.meta_key = %s
			AND {$wpdb->posts}.post_type = %s
			AND {$wpdb->posts}.post_status = 'publish'
			AND {$wpdb->postmeta}.meta_value != ''
			HAVING y IS NOT NULL AND y
			ORDER BY y DESC
		";
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql uses placeholders for values.
		$col = $wpdb->get_col( $wpdb->prepare( $sql, 'publication_date', 'book' ) );

		if ( ! is_array( $col ) ) {
			$col = array();
		}

		$years = array();
		foreach ( $col as $v ) {
			$y = (int) $v;
			if ( $y <= 2100 ) {
				$years[] = $y;
			}
		}

		$years = array_values( array_unique( $years ) );
		rsort( $years, SORT_NUMERIC );

		set_transient( $key, $years, 1 * HOUR_IN_SECONDS );

		return $years;
	}

	/**
	 * Distinct `book_author` meta values from published books, A→Z.
	 *
	 * Cached 12 hours; cleared on book save/update/delete.
	 *
	 * @return array<int, string>
	 */
	public static function get_distinct_authors(): array {
		$key    = self::TRANSIENT_AUTHORS;
		$cached = get_transient( $key );
		if ( false !== $cached && is_array( $cached ) ) {
			$out = array();
			foreach ( $cached as $v ) {
				if ( is_string( $v ) ) {
					$s = trim( wp_strip_all_tags( $v ) );
					if ( '' !== $s && strlen( $s ) <= 180 ) {
						$out[] = $s;
					}
				}
			}
			return array_values( array_unique( $out ) );
		}

		global $wpdb;

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- identifiers are $wpdb table names.
		$sql = "
			SELECT DISTINCT books_cpt_a.meta_value
			FROM {$wpdb->postmeta} AS books_cpt_a
			INNER JOIN {$wpdb->posts} AS books_cpt_p ON books_cpt_p.ID = books_cpt_a.post_id
			WHERE books_cpt_a.meta_key = %s
			AND books_cpt_p.post_type = %s
			AND books_cpt_p.post_status = 'publish'
			AND books_cpt_a.meta_value != ''
			ORDER BY books_cpt_a.meta_value ASC
		";
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql uses placeholders for values.
		$col = $wpdb->get_col( $wpdb->prepare( $sql, 'book_author', 'book' ) );
		if ( ! is_array( $col ) ) {
			$col = array();
		}

		$authors = array();
		foreach ( $col as $v ) {
			if ( ! is_string( $v ) ) {
				continue;
			}
			$s = trim( wp_strip_all_tags( $v ) );
			if ( '' === $s || strlen( $s ) > 180 ) {
				continue;
			}
			$authors[] = $s;
		}

		$authors = array_values( array_unique( $authors ) );
		sort( $authors, SORT_NATURAL | SORT_FLAG_CASE );

		set_transient( $key, $authors, 12 * HOUR_IN_SECONDS );

		return $authors;
	}

	/**
	 * Distinct `post_tag` terms used by published `book` posts.
	 *
	 * Cached 12 hours; cleared on book save/update/delete.
	 *
	 * @return array<int, array{term_id: int, slug: string, name: string}>
	 */
	public static function get_distinct_tags(): array {
		$key    = self::TRANSIENT_TAGS;
		$cached = get_transient( $key );
		if ( false !== $cached && is_array( $cached ) ) {
			$out = array();
			foreach ( $cached as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				$term_id = isset( $row['term_id'] ) ? (int) $row['term_id'] : 0;
				$slug    = isset( $row['slug'] ) && is_string( $row['slug'] ) ? $row['slug'] : '';
				$name    = isset( $row['name'] ) && is_string( $row['name'] ) ? $row['name'] : '';
				if ( $term_id > 0 && '' !== $slug && '' !== $name ) {
					$out[] = array(
						'term_id' => $term_id,
						'slug'    => $slug,
						'name'    => $name,
					);
				}
			}
			return $out;
		}

		global $wpdb;

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- identifiers are $wpdb table names.
		$sql = "
			SELECT DISTINCT t.term_id, t.slug, t.name
			FROM {$wpdb->terms} AS t
			INNER JOIN {$wpdb->term_taxonomy} AS tt ON tt.term_id = t.term_id AND tt.taxonomy = 'post_tag'
			INNER JOIN {$wpdb->term_relationships} AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
			INNER JOIN {$wpdb->posts} AS p ON p.ID = tr.object_id
			WHERE p.post_type = %s
			AND p.post_status = 'publish'
			ORDER BY t.name ASC
		";
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql uses placeholder for post_type.
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, 'book' ), ARRAY_A );
		if ( ! is_array( $rows ) ) {
			$rows = array();
		}

		$tags = array();
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$term_id = isset( $row['term_id'] ) ? (int) $row['term_id'] : 0;
			$slug    = isset( $row['slug'] ) && is_string( $row['slug'] ) ? $row['slug'] : '';
			$name    = isset( $row['name'] ) && is_string( $row['name'] ) ? $row['name'] : '';
			if ( $term_id <= 0 || '' === $slug || '' === $name ) {
				continue;
			}
			$tags[] = array(
				'term_id' => $term_id,
				'slug'    => $slug,
				'name'    => $name,
			);
		}

		set_transient( $key, $tags, 12 * HOUR_IN_SECONDS );

		return $tags;
	}
}

