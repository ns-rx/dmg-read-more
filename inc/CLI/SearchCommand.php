<?php
/**
 * WP-CLI command for finding posts that contain the DMG Read More block.
 *
 * @package DMG_Read_More
 */

namespace DMG\ReadMore\CLI;

use WP_CLI;
use InvalidArgumentException;
use WP_Query;

/**
 * Finds published posts containing the DMG Read More block within a date range.
 */
class SearchCommand {

	/**
	 * Finds posts containing the DMG Read More block.
	 *
	 * Searches published posts within a date range and logs the ID of each
	 * match, one per line, so the output can be piped into another command.
	 *
	 * ## OPTIONS
	 *
	 * [--date-after=<date>]
	 * : Start of the range. Any date string PHP's strtotime() can parse.
	 * Defaults to 30 days ago.
	 *
	 * [--date-before=<date>]
	 * : End of the range. Any date string PHP's strtotime() can parse.
	 * Defaults to now.
	 *
	 * ## EXAMPLES
	 *
	 *     # Search the last 30 days.
	 *     $ wp dmg-read-more search
	 *
	 *     # Search an explicit range.
	 *     $ wp dmg-read-more search --date-after=2026-01-01 --date-before=2026-02-01
	 *
	 * @when after_wp_load
	 *
	 * @param array $args       Positional arguments. Unused.
	 * @param array $assoc_args Associative arguments, keyed by option name.
	 * @return void
	 */
	public function __invoke( $args, $assoc_args ) {
		try {
			$range = DateRange::from_assoc_args( $assoc_args );
		} catch ( InvalidArgumentException $e ) {
			WP_CLI::error( $e->getMessage() );
		}

		$date_after  = wp_date( 'Y-m-d H:i:s', $range['after'] );
		$date_before = wp_date( 'Y-m-d H:i:s', $range['before'] );

		$query_args = array(
			'post_type'              => 'post',
			'post_status'            => 'publish',
			'date_query'             => array(
				'after'     => $date_after,
				'before'    => $date_before,
				'inclusive' => true,
			),
			's'                      => 'wp:dmg/read-more',
			'fields'                 => 'ids',
			// phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page -- Batch size for a CLI command, not a page render: with fields => 'ids' each row is one integer, and this is bounded rather than -1.
			'posts_per_page'         => 500,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'ignore_sticky_posts'    => true,
			'orderby'                => 'ID',
			'order'                  => 'ASC',
		);

		$paged     = 1;
		$found_any = false;

		do {
			$query_args['paged'] = $paged;

			$post_ids   = ( new WP_Query( $query_args ) )->posts;
			$batch_size = count( $post_ids );

			foreach ( $post_ids as $post_id ) {
				WP_CLI::log( $post_id );
				$found_any = true;
			}

			++$paged;
		} while ( $batch_size === $query_args['posts_per_page'] );

		if ( ! $found_any ) {
			WP_CLI::warning( 'No posts found in the given date range.' );
		}
	}
}
