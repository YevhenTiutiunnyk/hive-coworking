<?php
namespace Hive\Core\Blocks;

use DateTimeImmutable;
use Hive\Core\Bookings\OpeningHours;
use Hive\Core\Content\Meta;
use Hive\Core\Content\PostTypes;
use Hive\Core\Events\EventRepository;
use WP_Block;

/**
 * Block Bindings source "hive/fields".
 *
 * Lets core blocks in templates show formatted plugin data, for example a paragraph bound to
 * `{"source":"hive/fields","args":{"key":"space-price"}}` renders "€40 / hour".
 */
final class Bindings {

	public const SOURCE = 'hive/fields';

	/**
	 * Hooks registration into `init`.
	 */
	public static function register_hooks(): void {
		add_action( 'init', array( self::class, 'register' ) );
	}

	/**
	 * Registers the bindings source.
	 */
	public static function register(): void {
		register_block_bindings_source(
			self::SOURCE,
			array(
				'label'              => __( 'Hive Coworking', 'hive-core' ),
				'get_value_callback' => array( self::class, 'get_value' ),
				'uses_context'       => array( 'postId' ),
			)
		);
	}

	/**
	 * Bindings callback.
	 *
	 * @param array<string, mixed> $source_args Binding arguments; `key` selects the field.
	 * @param WP_Block             $block       Block being rendered.
	 */
	public static function get_value( array $source_args, WP_Block $block ): ?string {
		$post_id = (int) ( $block->context['postId'] ?? get_the_ID() );

		return self::value( (string) ( $source_args['key'] ?? '' ), $post_id, current_datetime() );
	}

	/**
	 * A formatted field of a space, location or event, or null when it does not apply.
	 *
	 * @param string            $key     Field key.
	 * @param int               $post_id Post being rendered.
	 * @param DateTimeImmutable $now     Current time.
	 */
	public static function value( string $key, int $post_id, DateTimeImmutable $now ): ?string {
		$post_type = get_post_type( $post_id );

		if ( str_starts_with( $key, 'space-' ) ) {
			return PostTypes::SPACE === $post_type ? self::space_field( $key, $post_id ) : null;
		}

		if ( str_starts_with( $key, 'location-' ) ) {
			$location = PostTypes::SPACE === $post_type ? (int) get_post_meta( $post_id, Meta::SPACE_LOCATION, true ) : $post_id;
			return PostTypes::LOCATION === get_post_type( $location ) ? self::location_field( $key, $location, $now ) : null;
		}

		if ( str_starts_with( $key, 'event-' ) ) {
			return PostTypes::EVENT === $post_type ? self::event_field( $key, $post_id ) : null;
		}

		return null;
	}

	/**
	 * Space fields.
	 *
	 * @param string $key     Field key.
	 * @param int    $post_id Space ID.
	 */
	private static function space_field( string $key, int $post_id ): ?string {
		switch ( $key ) {
			case 'space-type':
				return SpaceFinder::type_label( (string) get_post_meta( $post_id, Meta::SPACE_TYPE, true ) );

			case 'space-capacity':
				$capacity = (int) get_post_meta( $post_id, Meta::SPACE_CAPACITY, true );
				/* translators: %d: number of people. */
				return sprintf( _n( 'Up to %d person', 'Up to %d people', $capacity, 'hive-core' ), $capacity );

			case 'space-price':
				/* translators: %s: price such as €40. */
				return sprintf( __( '%s / hour', 'hive-core' ), SpaceFinder::format_price( (float) get_post_meta( $post_id, Meta::SPACE_PRICE, true ) ) );

			case 'space-location':
				$location = (int) get_post_meta( $post_id, Meta::SPACE_LOCATION, true );
				return $location ? get_the_title( $location ) : '';

			case 'space-amenities':
				$terms = get_the_terms( $post_id, PostTypes::AMENITY );
				return is_array( $terms ) ? implode( ' · ', wp_list_pluck( $terms, 'name' ) ) : '';
		}

		return null;
	}

	/**
	 * Location fields.
	 *
	 * @param string            $key         Field key.
	 * @param int               $location_id Location ID.
	 * @param DateTimeImmutable $now         Current time.
	 */
	private static function location_field( string $key, int $location_id, DateTimeImmutable $now ): ?string {
		switch ( $key ) {
			case 'location-address':
				return (string) get_post_meta( $location_id, Meta::LOCATION_ADDRESS, true );

			case 'location-today':
				$window = OpeningHours::from_meta( get_post_meta( $location_id, Meta::LOCATION_HOURS, true ), wp_timezone() )->for_date( $now );
				if ( null === $window ) {
					return __( 'Closed today', 'hive-core' );
				}
				/* translators: 1: opening time, 2: closing time. */
				return sprintf( __( 'Open today %1$s–%2$s', 'hive-core' ), $window[0]->format( 'H:i' ), $window[1]->format( 'H:i' ) );
		}

		return null;
	}

	/**
	 * Event fields.
	 *
	 * @param string $key      Field key.
	 * @param int    $event_id Event ID.
	 */
	private static function event_field( string $key, int $event_id ): ?string {
		if ( 'event-ics-url' === $key ) {
			return rest_url( sprintf( 'hive/v1/events/%d/ics', $event_id ) );
		}

		$event = ( new EventRepository() )->find( $event_id );
		if ( null === $event ) {
			return '';
		}

		switch ( $key ) {
			case 'event-when':
				return sprintf(
					/* translators: 1: date, 2: start time, 3: end time. */
					__( '%1$s, %2$s–%3$s', 'hive-core' ),
					wp_date( _x( 'l j F', 'event date', 'hive-core' ), $event->start->getTimestamp() ),
					wp_date( 'H:i', $event->start->getTimestamp() ),
					wp_date( 'H:i', $event->end->getTimestamp() )
				);

			case 'event-location':
				return $event->location_id ? get_the_title( $event->location_id ) : '';
		}

		return null;
	}
}
