<?php
/**
 * Unit tests for the DateRange class.
 *
 * @package DMG_Read_More
 */

namespace DMG\ReadMore\Tests\CLI;

use DMG\ReadMore\CLI\DateRange;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Covers option parsing, the defaults, and the two failure cases.
 */
class DateRangeTest extends TestCase {

	/**
	 * Relative defaults are resolved at call time, so assertions against them
	 * allow a few seconds of drift between the class and the test.
	 *
	 * @var int
	 */
	private const DELTA = 5;

	/**
	 * Explicit ISO dates are returned as timestamps.
	 *
	 * @return void
	 */
	public function test_parses_explicit_iso_dates() {
		$range = DateRange::from_assoc_args(
			array(
				'date-after'  => '2026-01-01',
				'date-before' => '2026-02-01',
			)
		);

		$this->assertSame( strtotime( '2026-01-01' ), $range['after'] );
		$this->assertSame( strtotime( '2026-02-01' ), $range['before'] );
	}

	/**
	 * Relative strings are accepted too, since strtotime() handles both.
	 *
	 * @return void
	 */
	public function test_parses_relative_dates() {
		$range = DateRange::from_assoc_args(
			array(
				'date-after'  => '7 days ago',
				'date-before' => 'yesterday',
			)
		);

		$this->assertEqualsWithDelta( strtotime( '7 days ago' ), $range['after'], self::DELTA );
		$this->assertEqualsWithDelta( strtotime( 'yesterday' ), $range['before'], self::DELTA );
	}

	/**
	 * With no options at all, the range is the last 30 days.
	 *
	 * @return void
	 */
	public function test_falls_back_to_the_default_range() {
		$range = DateRange::from_assoc_args( array() );

		$this->assertEqualsWithDelta( strtotime( DateRange::DEFAULT_AFTER ), $range['after'], self::DELTA );
		$this->assertEqualsWithDelta( strtotime( DateRange::DEFAULT_BEFORE ), $range['before'], self::DELTA );
	}

	/**
	 * One option can be supplied without the other.
	 *
	 * @return void
	 */
	public function test_falls_back_for_the_missing_option_only() {
		$range = DateRange::from_assoc_args( array( 'date-after' => '2026-01-01' ) );

		$this->assertSame( strtotime( '2026-01-01' ), $range['after'] );
		$this->assertEqualsWithDelta( strtotime( DateRange::DEFAULT_BEFORE ), $range['before'], self::DELTA );
	}

	/**
	 * An unparseable --date-after is rejected rather than silently defaulted.
	 *
	 * @return void
	 */
	public function test_rejects_an_unparseable_after_date() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( '--date-after' );

		DateRange::from_assoc_args( array( 'date-after' => 'not a date' ) );
	}

	/**
	 * The same applies to --date-before.
	 *
	 * @return void
	 */
	public function test_rejects_an_unparseable_before_date() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( '--date-before' );

		DateRange::from_assoc_args( array( 'date-before' => 'not a date' ) );
	}

	/**
	 * An empty string is not a date, and must not fall through to the default.
	 *
	 * @return void
	 */
	public function test_rejects_an_empty_date() {
		$this->expectException( InvalidArgumentException::class );

		DateRange::from_assoc_args( array( 'date-after' => '' ) );
	}

	/**
	 * A range that runs backwards would always return nothing, so it is an error.
	 *
	 * @return void
	 */
	public function test_rejects_a_reversed_range() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'earlier than' );

		DateRange::from_assoc_args(
			array(
				'date-after'  => '2026-02-01',
				'date-before' => '2026-01-01',
			)
		);
	}

	/**
	 * Identical dates are a valid, if empty, range rather than a reversed one.
	 *
	 * @return void
	 */
	public function test_allows_an_after_date_equal_to_the_before_date() {
		$range = DateRange::from_assoc_args(
			array(
				'date-after'  => '2026-01-01',
				'date-before' => '2026-01-01',
			)
		);

		$this->assertSame( $range['after'], $range['before'] );
	}

	/**
	 * Zero is a valid timestamp, and must not be confused with a parse failure.
	 *
	 * @return void
	 */
	public function test_accepts_the_unix_epoch() {
		$range = DateRange::from_assoc_args( array( 'date-after' => '1970-01-01 00:00:00 UTC' ) );

		$this->assertSame( 0, $range['after'] );
	}
}
