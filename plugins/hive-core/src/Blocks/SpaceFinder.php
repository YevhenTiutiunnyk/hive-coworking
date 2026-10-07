<?php
namespace Hive\Core\Blocks;

use Hive\Core\Content\Meta;
use Hive\Core\Content\PostTypes;

/**
 * Filtering for the hive/space-finder block.
 *
 * Filters live in the query string, so results can be shared, bookmarked and crawled,
 * and the form works without JavaScript.
 */
final class SpaceFinder {

	// Not "hive_location" or "hive_amenity": those are the query vars of the post type and
	// taxonomy, and WordPress would treat them as a request for a location or amenity archive.
	public const PARAM_PREFIX   = 'space_';
	public const PARAM_LOCATION = 'space_location';
	public const PARAM_TYPE     = 'space_type';
	public const PARAM_CAPACITY = 'space_capacity';
	public const PARAM_AMENITY  = 'space_amenity';

	private const MAX_RESULTS = 50;

	/**
	 * Filters that match every space.
	 *
	 * @return array{location: int, type: string, capacity: int, amenities: list<string>}
	 */
	public static function empty_filters(): array {
		return array(
			'location'  => 0,
			'type'      => '',
			'capacity'  => 0,
			'amenities' => array(),
		);
	}

	/**
	 * Reads filters from query-string values, dropping anything invalid.
	 *
	 * @param array<string, mixed> $query Raw query values.
	 * @return array{location: int, type: string, capacity: int, amenities: list<string>}
	 */
	public static function filters_from_request( array $query ): array {
		$location = $query[ self::PARAM_LOCATION ] ?? '';
		$type     = $query[ self::PARAM_TYPE ] ?? '';
		$capacity = $query[ self::PARAM_CAPACITY ] ?? '';

		return array(
			'location'  => is_numeric( $location ) ? max( 0, (int) $location ) : 0,
			'type'      => is_string( $type ) && in_array( $type, Meta::SPACE_TYPES, true ) ? $type : '',
			'capacity'  => is_numeric( $capacity ) ? max( 0, (int) $capacity ) : 0,
			'amenities' => array_values( array_filter( array_map( 'sanitize_key', (array) ( $query[ self::PARAM_AMENITY ] ?? array() ) ) ) ),
		);
	}

	/**
	 * Locks the location filter on a location page, where the block lists that location's spaces.
	 *
	 * @param array{location: int, type: string, capacity: int, amenities: list<string>} $filters Filters.
	 * @param array<string, mixed>                                                       $context Block context.
	 * @return array{location: int, type: string, capacity: int, amenities: list<string>}
	 */
	public static function with_context( array $filters, array $context ): array {
		if ( PostTypes::LOCATION === ( $context['postType'] ?? '' ) ) {
			$filters['location'] = (int) ( $context['postId'] ?? 0 );
		}

		return $filters;
	}

	/**
	 * WP_Query arguments for the filters.
	 *
	 * @param array{location: int, type: string, capacity: int, amenities: list<string>} $filters Filters.
	 * @return array<string, mixed>
	 */
	public static function query_args( array $filters ): array {
		$meta_query = array();

		if ( $filters['location'] > 0 ) {
			$meta_query[] = array(
				'key'   => Meta::SPACE_LOCATION,
				'value' => $filters['location'],
				'type'  => 'NUMERIC',
			);
		}

		if ( '' !== $filters['type'] ) {
			$meta_query[] = array(
				'key'   => Meta::SPACE_TYPE,
				'value' => $filters['type'],
			);
		}

		if ( $filters['capacity'] > 0 ) {
			$meta_query[] = array(
				'key'     => Meta::SPACE_CAPACITY,
				'value'   => $filters['capacity'],
				'compare' => '>=',
				'type'    => 'NUMERIC',
			);
		}

		$args = array(
			'post_type'      => PostTypes::SPACE,
			'post_status'    => 'publish',
			'posts_per_page' => self::MAX_RESULTS,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		);

		if ( array() !== $meta_query ) {
			$args['meta_query'] = $meta_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Filtering is the point of this query; results are capped.
		}

		if ( array() !== $filters['amenities'] ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- See above.
			$args['tax_query'] = array(
				array(
					'taxonomy' => PostTypes::AMENITY,
					'field'    => 'slug',
					'terms'    => $filters['amenities'],
					'operator' => 'AND',
				),
			);
		}

		return $args;
	}

	/**
	 * Display label of a space type.
	 *
	 * @param string $type One of Meta::SPACE_TYPES.
	 */
	public static function type_label( string $type ): string {
		return match ( $type ) {
			'meeting_room'   => __( 'Meeting room', 'hive-core' ),
			'private_office' => __( 'Private office', 'hive-core' ),
			'hot_desk'       => __( 'Hot desk', 'hive-core' ),
			default          => $type,
		};
	}

	/**
	 * A price with the site currency, e.g. "€40".
	 *
	 * @param float $amount Amount.
	 */
	public static function format_price( float $amount ): string {
		/**
		 * Filters the currency symbol shown with prices.
		 *
		 * @param string $symbol Currency symbol.
		 */
		$symbol = (string) apply_filters( 'hive_currency_symbol', '€' );

		return $symbol . number_format_i18n( $amount, floor( $amount ) === $amount ? 0 : 2 );
	}
}
