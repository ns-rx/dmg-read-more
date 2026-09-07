<?php
/**
 * Date range parsing for the search command.
 *
 * No WordPress or WP-CLI calls here, so this can be unit tested on its own.
 *
 * @package DMG_Read_More
 */

namespace DMG\ReadMore\CLI;

use InvalidArgumentException;

/**
 * Validates the --date-after and --date-before options.
 */
class DateRange {

	/**
	 * Used when --date-after is omitted.
	 *
	 * @var string
	 */
	public const DEFAULT_AFTER = '30 days ago';

	/**
	 * Used when --date-before is omitted.
	 *
	 * @var string
	 */
	public const DEFAULT_BEFORE = 'now';

	/**
	 * Builds a validated date range, applying defaults for anything omitted.
	 *
	 * Values go through strtotime(), which disambiguates by separator: slashes
	 * are read as American m/d/y, dashes and dots as European d-m-y. ISO 8601
	 * (Y-m-d) avoids the ambiguity.
	 *
	 * @param array $assoc_args Associative arguments as passed to the command.
	 * @return array The validated range, keyed 'after' and 'before'.
	 * @throws InvalidArgumentException If either date is unparseable, or the
	 *                                  range runs backwards.
	 */
	public static function from_assoc_args( array $assoc_args ): array {

		$date_after  = strtotime( $assoc_args['date-after'] ?? self::DEFAULT_AFTER );
		$date_before = strtotime( $assoc_args['date-before'] ?? self::DEFAULT_BEFORE );

		if ( false === $date_after ) {
			throw new InvalidArgumentException( 'Could not parse --date-after. Use a date strtotime() understands, such as 2026-01-01.' );
		}

		if ( false === $date_before ) {
			throw new InvalidArgumentException( 'Could not parse --date-before. Use a date strtotime() understands, such as 2026-02-01.' );
		}

		if ( $date_after > $date_before ) {
			throw new InvalidArgumentException( '--date-after must be earlier than --date-before.' );
		}

		return array(
			'after'  => $date_after,
			'before' => $date_before,
		);
	}
}
