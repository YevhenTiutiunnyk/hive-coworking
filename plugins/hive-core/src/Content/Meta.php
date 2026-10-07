<?php
namespace Hive\Core\Content;

/**
 * Registers post meta for the catalogue post types.
 *
 * Every key is exposed in the REST API with a JSON schema, so the block editor can edit it
 * and invalid values are rejected before they reach the database.
 */
final class Meta {

	public const LOCATION_ADDRESS   = 'hive_address';
	public const LOCATION_LATITUDE  = 'hive_latitude';
	public const LOCATION_LONGITUDE = 'hive_longitude';
	public const LOCATION_HOURS     = 'hive_opening_hours';
	public const LOCATION_GALLERY   = 'hive_gallery';

	public const SPACE_LOCATION = 'hive_location_id';
	public const SPACE_TYPE     = 'hive_space_type';
	public const SPACE_CAPACITY = 'hive_capacity';
	public const SPACE_PRICE    = 'hive_hourly_price';

	public const PLAN_PRICE_MONTHLY = 'hive_price_monthly';
	public const PLAN_PRICE_YEARLY  = 'hive_price_yearly';
	public const PLAN_FEATURES      = 'hive_features';
	public const PLAN_FEATURED      = 'hive_is_featured';

	public const EVENT_STARTS   = 'hive_starts_at';
	public const EVENT_ENDS     = 'hive_ends_at';
	public const EVENT_LOCATION = 'hive_location_id';
	public const EVENT_CAPACITY = 'hive_capacity';

	public const SPACE_TYPES = array( 'meeting_room', 'private_office', 'hot_desk' );

	public const WEEKDAYS = array( 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun' );

	/**
	 * Opening hours used when a location has none set. `null` means closed.
	 */
	public const DEFAULT_HOURS = array(
		'mon' => array(
			'open'  => '08:00',
			'close' => '20:00',
		),
		'tue' => array(
			'open'  => '08:00',
			'close' => '20:00',
		),
		'wed' => array(
			'open'  => '08:00',
			'close' => '20:00',
		),
		'thu' => array(
			'open'  => '08:00',
			'close' => '20:00',
		),
		'fri' => array(
			'open'  => '08:00',
			'close' => '20:00',
		),
		'sat' => array(
			'open'  => '10:00',
			'close' => '16:00',
		),
		'sun' => null,
	);

	/**
	 * Hooks registration into `init`.
	 */
	public static function register_hooks(): void {
		add_action( 'init', array( self::class, 'register_all' ) );
	}

	/**
	 * Registers every meta key.
	 */
	public static function register_all(): void {
		self::register( PostTypes::LOCATION, self::LOCATION_ADDRESS, 'string', '' );
		self::register( PostTypes::LOCATION, self::LOCATION_LATITUDE, 'number', 0 );
		self::register( PostTypes::LOCATION, self::LOCATION_LONGITUDE, 'number', 0 );
		self::register( PostTypes::LOCATION, self::LOCATION_HOURS, 'object', self::DEFAULT_HOURS, self::opening_hours_schema() );
		self::register( PostTypes::LOCATION, self::LOCATION_GALLERY, 'array', array(), array( 'items' => array( 'type' => 'integer' ) ) );

		self::register( PostTypes::SPACE, self::SPACE_LOCATION, 'integer', 0 );
		self::register( PostTypes::SPACE, self::SPACE_TYPE, 'string', 'meeting_room', array( 'enum' => self::SPACE_TYPES ) );
		self::register( PostTypes::SPACE, self::SPACE_CAPACITY, 'integer', 1, array( 'minimum' => 1 ) );
		self::register( PostTypes::SPACE, self::SPACE_PRICE, 'number', 0, array( 'minimum' => 0 ) );

		self::register( PostTypes::PLAN, self::PLAN_PRICE_MONTHLY, 'number', 0, array( 'minimum' => 0 ) );
		self::register( PostTypes::PLAN, self::PLAN_PRICE_YEARLY, 'number', 0, array( 'minimum' => 0 ) );
		self::register( PostTypes::PLAN, self::PLAN_FEATURES, 'array', array(), array( 'items' => array( 'type' => 'string' ) ) );
		self::register( PostTypes::PLAN, self::PLAN_FEATURED, 'boolean', false );

		// Event dates have no default: an empty string is not a valid date-time.
		self::register( PostTypes::EVENT, self::EVENT_STARTS, 'string', null, array( 'format' => 'date-time' ) );
		self::register( PostTypes::EVENT, self::EVENT_ENDS, 'string', null, array( 'format' => 'date-time' ) );
		self::register( PostTypes::EVENT, self::EVENT_LOCATION, 'integer', 0 );
		self::register( PostTypes::EVENT, self::EVENT_CAPACITY, 'integer', 0, array( 'minimum' => 0 ) );
	}

	/**
	 * Registers one single-value meta key that is visible in the REST API.
	 *
	 * @param string               $post_type     Post type the key belongs to.
	 * @param string               $key           Meta key.
	 * @param string               $type          JSON schema type.
	 * @param mixed                $default_value Value returned when the key is not set; null for none.
	 * @param array<string, mixed> $schema        Extra JSON schema merged into the REST schema.
	 */
	private static function register( string $post_type, string $key, string $type, mixed $default_value, array $schema = array() ): void {
		$args = array(
			'type'          => $type,
			'single'        => true,
			'show_in_rest'  => array( 'schema' => array_merge( array( 'type' => $type ), $schema ) ),
			'auth_callback' => array( self::class, 'can_edit' ),
		);
		if ( null !== $default_value ) {
			$args['default'] = $default_value;
		}

		register_post_meta( $post_type, $key, $args );
	}

	/**
	 * Meta auth callback: only users who can edit the post may change its meta.
	 *
	 * @param bool   $allowed  Whether the user can edit the meta, as decided so far.
	 * @param string $meta_key Meta key being checked.
	 * @param int    $post_id  Post the meta belongs to.
	 */
	public static function can_edit( bool $allowed, string $meta_key, int $post_id ): bool {
		return current_user_can( 'edit_post', $post_id );
	}

	/**
	 * JSON schema for the weekly opening hours object.
	 *
	 * @return array<string, mixed>
	 */
	private static function opening_hours_schema(): array {
		$time = array(
			'type'     => 'string',
			'pattern'  => '^([01][0-9]|2[0-3]):[0-5][0-9]$',
			'required' => true,
		);
		$day  = array(
			'type'                 => array( 'object', 'null' ),
			'properties'           => array(
				'open'  => $time,
				'close' => $time,
			),
			'additionalProperties' => false,
		);

		return array(
			'properties'           => array_fill_keys( self::WEEKDAYS, $day ),
			'additionalProperties' => false,
		);
	}
}
