<?php
namespace Hive\Core\Demo;

use DateTimeImmutable;
use Hive\Core\Bookings\BookingError;
use Hive\Core\Bookings\BookingRepository;
use Hive\Core\Bookings\BookingService;
use Hive\Core\Bookings\SpaceCatalog;
use Hive\Core\Content\Meta;
use Hive\Core\Content\PostTypes;
use Hive\Core\Roles;

/**
 * Fills the site with demo content. Safe to run repeatedly: existing items are kept.
 */
final class Seeder {

	public const DEMO_USER = 'demo';

	/**
	 * Post IDs by slug, filled while seeding.
	 *
	 * @var array<string, int>
	 */
	private array $ids = array();

	/**
	 * Creates whatever is missing and returns how many items of each kind were created.
	 *
	 * @param DateTimeImmutable $now           Current time; events and bookings are placed relative to it.
	 * @param string|null       $demo_password Password for the demo member; random when null.
	 * @return array{amenities: int, locations: int, spaces: int, plans: int, events: int, members: int, bookings: int}
	 */
	public function run( DateTimeImmutable $now, ?string $demo_password = null ): array {
		return array(
			'amenities' => $this->seed_amenities(),
			'locations' => $this->seed_locations(),
			'spaces'    => $this->seed_spaces(),
			'plans'     => $this->seed_plans(),
			'events'    => $this->seed_events( $now ),
			'members'   => $this->seed_member( $demo_password ),
			'bookings'  => $this->seed_bookings( $now ),
		);
	}

	/**
	 * Creates the amenity terms.
	 */
	private function seed_amenities(): int {
		$created = 0;

		foreach ( DemoContent::amenities() as $slug => $name ) {
			if ( ! term_exists( $slug, PostTypes::AMENITY ) ) {
				wp_insert_term( $name, PostTypes::AMENITY, array( 'slug' => $slug ) );
				++$created;
			}
		}

		return $created;
	}

	/**
	 * Creates the locations.
	 */
	private function seed_locations(): int {
		$created = 0;

		foreach ( DemoContent::locations() as $slug => $location ) {
			$created += $this->ensure_post(
				PostTypes::LOCATION,
				$slug,
				array(
					'post_title'   => $location['title'],
					'post_excerpt' => $location['excerpt'],
					'post_content' => $location['content'],
					'meta_input'   => array(
						Meta::LOCATION_ADDRESS   => $location['address'],
						Meta::LOCATION_LATITUDE  => $location['lat'],
						Meta::LOCATION_LONGITUDE => $location['lng'],
						Meta::LOCATION_HOURS     => $location['hours'],
					),
				)
			);
		}

		return $created;
	}

	/**
	 * Creates the spaces and assigns their amenities.
	 */
	private function seed_spaces(): int {
		$created = 0;

		foreach ( DemoContent::spaces() as $slug => $space ) {
			$is_new   = $this->ensure_post(
				PostTypes::SPACE,
				$slug,
				array(
					'post_title'   => $space['title'],
					'post_excerpt' => $space['excerpt'],
					'meta_input'   => array(
						Meta::SPACE_LOCATION => $this->ids[ $space['location'] ],
						Meta::SPACE_TYPE     => $space['type'],
						Meta::SPACE_CAPACITY => $space['capacity'],
						Meta::SPACE_PRICE    => $space['price'],
					),
				)
			);
			$created += $is_new;

			if ( $is_new ) {
				wp_set_object_terms( $this->ids[ $slug ], $space['amenities'], PostTypes::AMENITY );
			}
		}

		return $created;
	}

	/**
	 * Creates the membership plans.
	 */
	private function seed_plans(): int {
		$created = 0;
		$order   = 0;

		foreach ( DemoContent::plans() as $slug => $plan ) {
			$created += $this->ensure_post(
				PostTypes::PLAN,
				$slug,
				array(
					'post_title'   => $plan['title'],
					'post_content' => $plan['content'],
					'menu_order'   => ++$order,
					'meta_input'   => array(
						Meta::PLAN_PRICE_MONTHLY => $plan['monthly'],
						Meta::PLAN_PRICE_YEARLY  => $plan['yearly'],
						Meta::PLAN_FEATURED      => $plan['featured'],
						Meta::PLAN_FEATURES      => $plan['features'],
					),
				)
			);
		}

		return $created;
	}

	/**
	 * Creates upcoming events.
	 *
	 * @param DateTimeImmutable $now Current time.
	 */
	private function seed_events( DateTimeImmutable $now ): int {
		$created = 0;
		$today   = $now->setTimezone( wp_timezone() )->setTime( 0, 0 );

		foreach ( DemoContent::events() as $slug => $event ) {
			$day = $today->modify( sprintf( '+%d days', $event['days'] ) );

			$created += $this->ensure_post(
				PostTypes::EVENT,
				$slug,
				array(
					'post_title'   => $event['title'],
					'post_excerpt' => $event['excerpt'],
					'meta_input'   => array(
						Meta::EVENT_STARTS   => $day->modify( $event['start'] )->format( DATE_ATOM ),
						Meta::EVENT_ENDS     => $day->modify( $event['end'] )->format( DATE_ATOM ),
						Meta::EVENT_LOCATION => $this->ids[ $event['location'] ],
						Meta::EVENT_CAPACITY => $event['capacity'],
					),
				)
			);
		}

		return $created;
	}

	/**
	 * Creates the demo member, or updates the password of an existing one.
	 *
	 * @param string|null $password Password; random when null.
	 */
	private function seed_member( ?string $password ): int {
		$user = get_user_by( 'login', self::DEMO_USER );

		if ( false !== $user ) {
			if ( null !== $password ) {
				wp_set_password( $password, $user->ID );
			}
			return 0;
		}

		wp_insert_user(
			array(
				'user_login'   => self::DEMO_USER,
				'user_pass'    => $password ?? wp_generate_password( 24 ),
				'user_email'   => 'demo@example.com',
				'display_name' => 'Demo Member',
				'first_name'   => 'Demo',
				'role'         => Roles::MEMBER,
			)
		);

		return 1;
	}

	/**
	 * Gives the demo member three upcoming and two past bookings, unless they already have bookings.
	 *
	 * @param DateTimeImmutable $now Current time.
	 */
	private function seed_bookings( DateTimeImmutable $now ): int {
		$user = get_user_by( 'login', self::DEMO_USER );
		if ( false === $user ) {
			return 0;
		}

		$repository = new BookingRepository();
		if ( array() !== $repository->for_user( $user->ID, $now, true, 1 ) || array() !== $repository->for_user( $user->ID, $now, false, 1 ) ) {
			return 0;
		}

		$service = BookingService::create_default();
		$created = 0;

		// Upcoming bookings go through the service, so they obey the booking rules.
		$upcoming = array(
			array( 'lighthouse-room', 1, '10:00', '12:00' ),
			array( 'orchid-room', 3, '14:00', '15:00' ),
			array( 'clocktower-room', 6, '09:00', '11:00' ),
		);
		foreach ( $upcoming as list( $slug, $days, $start, $end ) ) {
			$slot = $this->next_open_slot( $this->ids[ $slug ], $now, $days, $start, $end );
			if ( null === $slot ) {
				continue;
			}

			try {
				$service->book( $user->ID, $this->ids[ $slug ], $slot[0], $slot[1], $now );
				++$created;
			} catch ( BookingError $error ) {
				// The slot is not bookable with the current settings; skip it.
				continue;
			}
		}

		// Past bookings cannot be made through the rules, so they are written directly.
		$today = $now->setTimezone( wp_timezone() )->setTime( 0, 0 );
		$past  = array(
			array( 'fern-boardroom', 7, '10:00', '12:00' ),
			array( 'library-nook', 14, '15:00', '16:00' ),
		);
		foreach ( $past as list( $slug, $days, $start, $end ) ) {
			$day = $today->modify( sprintf( '-%d days', $days ) );
			$repository->insert( $this->ids[ $slug ], $user->ID, $day->modify( $start ), $day->modify( $end ) );
			++$created;
		}

		return $created;
	}

	/**
	 * The first day, at least $days after now, when the space is open for the whole slot.
	 *
	 * @param int               $space_id Space ID.
	 * @param DateTimeImmutable $now      Current time.
	 * @param int               $days     Minimum days ahead.
	 * @param string            $start    Start time, HH:MM.
	 * @param string            $end      End time, HH:MM.
	 * @return array{0: DateTimeImmutable, 1: DateTimeImmutable}|null
	 */
	private function next_open_slot( int $space_id, DateTimeImmutable $now, int $days, string $start, string $end ): ?array {
		$space = ( new SpaceCatalog() )->find( $space_id );
		if ( null === $space ) {
			return null;
		}

		$today = $now->setTimezone( wp_timezone() )->setTime( 0, 0 );
		for ( $offset = $days; $offset < $days + 14; $offset++ ) {
			$day    = $today->modify( sprintf( '+%d days', $offset ) );
			$window = $space->hours->for_date( $day );
			$from   = $day->modify( $start );
			$to     = $day->modify( $end );

			if ( null !== $window && $from >= $window[0] && $to <= $window[1] ) {
				return array( $from, $to );
			}
		}

		return null;
	}

	/**
	 * Creates a published post unless one with the slug exists. Returns 1 when created.
	 *
	 * @param string               $post_type Post type.
	 * @param string               $slug      Post slug.
	 * @param array<string, mixed> $args      wp_insert_post() arguments.
	 */
	private function ensure_post( string $post_type, string $slug, array $args ): int {
		$existing = get_page_by_path( $slug, OBJECT, $post_type );
		if ( $existing instanceof \WP_Post ) {
			$this->ids[ $slug ] = $existing->ID;
			return 0;
		}

		$this->ids[ $slug ] = (int) wp_insert_post(
			array_merge(
				array(
					'post_type'   => $post_type,
					'post_name'   => $slug,
					'post_status' => 'publish',
				),
				$args
			)
		);

		return 1;
	}
}
