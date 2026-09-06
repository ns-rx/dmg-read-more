<?php
/**
 * WP-CLI command for finding posts that contain the DMG Read More block.
 *
 * @package DMG_Read_More
 */

namespace DMG\ReadMore\CLI;

use WP_CLI;

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
		WP_CLI::log( 'Hello!' );
	}
}
