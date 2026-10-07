<?php
namespace Hive\Core\Demo;

/**
 * The fictional Hive Coworking network used for the demo site.
 */
final class DemoContent {

	/**
	 * Amenity terms: slug => name.
	 *
	 * @return array<string, string>
	 */
	public static function amenities(): array {
		return array(
			'wifi'               => 'Fast Wi-Fi',
			'projector'          => 'Projector',
			'whiteboard'         => 'Whiteboard',
			'video-conferencing' => 'Video conferencing',
			'standing-desk'      => 'Standing desk',
			'natural-light'      => 'Natural light',
			'coffee'             => 'Free coffee',
			'step-free'          => 'Step-free access',
		);
	}

	/**
	 * Locations keyed by slug.
	 *
	 * @return array<string, array{title: string, excerpt: string, content: string, address: string, lat: float, lng: float, hours: array<string, array{open: string, close: string}|null>}>
	 */
	public static function locations(): array {
		$weekday = static fn( string $open, string $close ): array => array(
			'open'  => $open,
			'close' => $close,
		);

		return array(
			'hive-harbour'    => array(
				'title'   => 'Hive Harbour',
				'excerpt' => 'Our flagship space in a converted warehouse, with long opening hours and views over the water.',
				'content' => self::paragraphs(
					'Hive Harbour fills two floors of a restored brick warehouse on the quay. Big windows, long tables and quiet corners make it easy to find the right spot for the day.',
					'It is open seven days a week, so it suits early starters, late finishers and anyone with a weekend deadline.'
				),
				'address' => '12 Quay Street, Harbour District',
				'lat'     => 52.3791,
				'lng'     => 4.9003,
				'hours'   => array(
					'mon' => $weekday( '07:00', '21:00' ),
					'tue' => $weekday( '07:00', '21:00' ),
					'wed' => $weekday( '07:00', '21:00' ),
					'thu' => $weekday( '07:00', '21:00' ),
					'fri' => $weekday( '07:00', '21:00' ),
					'sat' => $weekday( '09:00', '17:00' ),
					'sun' => $weekday( '10:00', '16:00' ),
				),
			),
			'hive-old-town'   => array(
				'title'   => 'Hive Old Town',
				'excerpt' => 'A calm, book-lined space above a former clockmaker\'s shop in the historic centre.',
				'content' => self::paragraphs(
					'Hive Old Town is the quiet one. Creaky floorboards, a reading room and the best meeting room in the network make it popular with writers, consultants and small teams.',
					'Cafés, the train station and the market are all within a five-minute walk.'
				),
				'address' => '4 Clockmaker Lane, Old Town',
				'lat'     => 52.3731,
				'lng'     => 4.8922,
				'hours'   => array(
					'mon' => $weekday( '08:00', '20:00' ),
					'tue' => $weekday( '08:00', '20:00' ),
					'wed' => $weekday( '08:00', '20:00' ),
					'thu' => $weekday( '08:00', '20:00' ),
					'fri' => $weekday( '08:00', '20:00' ),
					'sat' => $weekday( '10:00', '16:00' ),
					'sun' => null,
				),
			),
			'hive-greenhouse' => array(
				'title'   => 'Hive Greenhouse',
				'excerpt' => 'A glass-roofed space full of plants and daylight, next to the botanic gardens.',
				'content' => self::paragraphs(
					'Hive Greenhouse sits under a glass roof with more plants than people. It is bright, warm and surprisingly quiet.',
					'The large boardroom and the terrace make it the favourite for workshops and offsites.'
				),
				'address' => '88 Orchard Avenue, Botanic Quarter',
				'lat'     => 52.3667,
				'lng'     => 4.9080,
				'hours'   => array(
					'mon' => $weekday( '08:00', '19:00' ),
					'tue' => $weekday( '08:00', '19:00' ),
					'wed' => $weekday( '08:00', '19:00' ),
					'thu' => $weekday( '08:00', '19:00' ),
					'fri' => $weekday( '08:00', '19:00' ),
					'sat' => null,
					'sun' => null,
				),
			),
		);
	}

	/**
	 * Spaces keyed by slug.
	 *
	 * @return array<string, array{title: string, location: string, type: string, capacity: int, price: float, amenities: list<string>, excerpt: string}>
	 */
	public static function spaces(): array {
		return array(
			'lighthouse-room'     => array(
				'title'     => 'Lighthouse Room',
				'location'  => 'hive-harbour',
				'type'      => 'meeting_room',
				'capacity'  => 8,
				'price'     => 40.0,
				'amenities' => array( 'wifi', 'video-conferencing', 'whiteboard', 'natural-light' ),
				'excerpt'   => 'A corner meeting room with a harbour view and a large screen for hybrid calls.',
			),
			'dockside-booth'      => array(
				'title'     => 'Dockside Booth',
				'location'  => 'hive-harbour',
				'type'      => 'meeting_room',
				'capacity'  => 2,
				'price'     => 15.0,
				'amenities' => array( 'wifi', 'video-conferencing' ),
				'excerpt'   => 'A soundproof booth for interviews and one-to-one calls.',
			),
			'captains-office'     => array(
				'title'     => 'Captain\'s Office',
				'location'  => 'hive-harbour',
				'type'      => 'private_office',
				'capacity'  => 4,
				'price'     => 30.0,
				'amenities' => array( 'wifi', 'standing-desk', 'natural-light', 'coffee' ),
				'excerpt'   => 'A lockable office for a small team, bookable by the hour.',
			),
			'harbour-hot-desk'    => array(
				'title'     => 'Harbour Hot Desk',
				'location'  => 'hive-harbour',
				'type'      => 'hot_desk',
				'capacity'  => 1,
				'price'     => 8.0,
				'amenities' => array( 'wifi', 'coffee', 'step-free' ),
				'excerpt'   => 'A desk at the long window table, with coffee on tap.',
			),
			'clocktower-room'     => array(
				'title'     => 'Clocktower Room',
				'location'  => 'hive-old-town',
				'type'      => 'meeting_room',
				'capacity'  => 12,
				'price'     => 55.0,
				'amenities' => array( 'wifi', 'projector', 'whiteboard', 'video-conferencing' ),
				'excerpt'   => 'Our largest room in the old town, under the original clock mechanism.',
			),
			'cobblestone-studio'  => array(
				'title'     => 'Cobblestone Studio',
				'location'  => 'hive-old-town',
				'type'      => 'private_office',
				'capacity'  => 6,
				'price'     => 35.0,
				'amenities' => array( 'wifi', 'whiteboard', 'coffee' ),
				'excerpt'   => 'A private studio with its own entrance from the courtyard.',
			),
			'library-nook'        => array(
				'title'     => 'Library Nook',
				'location'  => 'hive-old-town',
				'type'      => 'meeting_room',
				'capacity'  => 4,
				'price'     => 25.0,
				'amenities' => array( 'wifi', 'natural-light' ),
				'excerpt'   => 'A small, quiet room surrounded by bookshelves.',
			),
			'old-town-hot-desk'   => array(
				'title'     => 'Old Town Hot Desk',
				'location'  => 'hive-old-town',
				'type'      => 'hot_desk',
				'capacity'  => 1,
				'price'     => 7.0,
				'amenities' => array( 'wifi', 'coffee' ),
				'excerpt'   => 'A desk in the reading room, where phone calls are not allowed.',
			),
			'fern-boardroom'      => array(
				'title'     => 'Fern Boardroom',
				'location'  => 'hive-greenhouse',
				'type'      => 'meeting_room',
				'capacity'  => 16,
				'price'     => 70.0,
				'amenities' => array( 'wifi', 'projector', 'video-conferencing', 'natural-light', 'step-free' ),
				'excerpt'   => 'A boardroom under the glass roof, made for workshops and offsites.',
			),
			'orchid-room'         => array(
				'title'     => 'Orchid Room',
				'location'  => 'hive-greenhouse',
				'type'      => 'meeting_room',
				'capacity'  => 6,
				'price'     => 30.0,
				'amenities' => array( 'wifi', 'whiteboard', 'natural-light' ),
				'excerpt'   => 'A bright room for brainstorming, with floor-to-ceiling whiteboards.',
			),
			'terrace-office'      => array(
				'title'     => 'Terrace Office',
				'location'  => 'hive-greenhouse',
				'type'      => 'private_office',
				'capacity'  => 3,
				'price'     => 28.0,
				'amenities' => array( 'wifi', 'standing-desk', 'natural-light' ),
				'excerpt'   => 'A small office that opens onto the terrace.',
			),
			'greenhouse-hot-desk' => array(
				'title'     => 'Greenhouse Hot Desk',
				'location'  => 'hive-greenhouse',
				'type'      => 'hot_desk',
				'capacity'  => 1,
				'price'     => 8.0,
				'amenities' => array( 'wifi', 'natural-light', 'step-free' ),
				'excerpt'   => 'A desk among the plants, in the brightest spot of the building.',
			),
		);
	}

	/**
	 * Membership plans keyed by slug.
	 *
	 * @return array<string, array{title: string, content: string, monthly: float, yearly: float, featured: bool, features: list<string>}>
	 */
	public static function plans(): array {
		return array(
			'flex'           => array(
				'title'    => 'Flex',
				'content'  => self::paragraphs( 'For freelancers who come in a few days a week.' ),
				'monthly'  => 149.0,
				'yearly'   => 1490.0,
				'featured' => false,
				'features' => array( '10 days per month', 'Hot desk at any location', '2 hours of meeting rooms', 'Free coffee' ),
			),
			'dedicated-desk' => array(
				'title'    => 'Dedicated Desk',
				'content'  => self::paragraphs( 'Your own desk, set up the way you like it.' ),
				'monthly'  => 299.0,
				'yearly'   => 2990.0,
				'featured' => true,
				'features' => array( 'Your own desk and locker', '24/7 access', '8 hours of meeting rooms', 'Mail handling' ),
			),
			'private-office' => array(
				'title'    => 'Private Office',
				'content'  => self::paragraphs( 'A lockable office for teams of up to four.' ),
				'monthly'  => 899.0,
				'yearly'   => 8990.0,
				'featured' => false,
				'features' => array( 'Lockable office for 4', '24/7 access', '20 hours of meeting rooms', 'Company name at reception' ),
			),
		);
	}

	/**
	 * Events keyed by slug. Times are relative to the seeding day, in the site timezone.
	 *
	 * @return array<string, array{title: string, excerpt: string, location: string, days: int, start: string, end: string, capacity: int}>
	 */
	public static function events(): array {
		return array(
			'founders-breakfast'    => array(
				'title'    => 'Founders\' Breakfast',
				'excerpt'  => 'Coffee, pastries and honest conversations about building a company.',
				'location' => 'hive-harbour',
				'days'     => 3,
				'start'    => '08:30',
				'end'      => '10:00',
				'capacity' => 30,
			),
			'design-systems-meetup' => array(
				'title'    => 'Design Systems Meetup',
				'excerpt'  => 'Two short talks on keeping design and code in sync, followed by drinks.',
				'location' => 'hive-old-town',
				'days'     => 8,
				'start'    => '18:30',
				'end'      => '21:00',
				'capacity' => 60,
			),
			'pitch-practice-night'  => array(
				'title'    => 'Pitch Practice Night',
				'excerpt'  => 'Practise your pitch in front of friendly members and get feedback.',
				'location' => 'hive-greenhouse',
				'days'     => 15,
				'start'    => '18:00',
				'end'      => '20:00',
				'capacity' => 25,
			),
			'community-social'      => array(
				'title'    => 'Community Social',
				'excerpt'  => 'Meet the members from all three locations. Food and music provided.',
				'location' => 'hive-harbour',
				'days'     => 22,
				'start'    => '17:00',
				'end'      => '20:00',
				'capacity' => 80,
			),
		);
	}

	/**
	 * Pages keyed by slug. Content uses the plugin's blocks and, when the Hive theme is active, its patterns.
	 *
	 * @return array<string, array{title: string, content: string}>
	 */
	public static function pages(): array {
		return array(
			'pricing' => array(
				'title'   => 'Pricing',
				'content' => self::paragraphs( 'Book any space by the hour without a plan, or become a member for included hours, 24/7 access and a lower hourly rate.' )
					. "\n\n<!-- wp:hive/plan-comparison {\"align\":\"wide\"} /-->\n\n<!-- wp:pattern {\"slug\":\"hive/faq\"} /-->",
			),
			'account' => array(
				'title'   => 'My bookings',
				'content' => '<!-- wp:hive/my-bookings /-->',
			),
		);
	}

	/**
	 * Block markup for a few paragraphs.
	 *
	 * @param string ...$paragraphs Paragraph texts.
	 */
	private static function paragraphs( string ...$paragraphs ): string {
		return implode(
			"\n\n",
			array_map(
				static fn( string $text ): string => "<!-- wp:paragraph -->\n<p>" . esc_html( $text ) . "</p>\n<!-- /wp:paragraph -->",
				$paragraphs
			)
		);
	}
}
