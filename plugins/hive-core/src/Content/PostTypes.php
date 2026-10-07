<?php
namespace Hive\Core\Content;

/**
 * Registers the post types and taxonomy that make up the coworking catalogue.
 */
final class PostTypes {

	public const LOCATION = 'hive_location';
	public const SPACE    = 'hive_space';
	public const PLAN     = 'hive_plan';
	public const EVENT    = 'hive_event';
	public const AMENITY  = 'hive_amenity';

	/**
	 * Hooks registration into `init`.
	 */
	public static function register_hooks(): void {
		add_action( 'init', array( self::class, 'register_all' ) );
	}

	/**
	 * Registers every post type and the amenity taxonomy.
	 */
	public static function register_all(): void {
		register_post_type(
			self::LOCATION,
			array(
				'labels'       => array(
					'name'          => __( 'Locations', 'hive-core' ),
					'singular_name' => __( 'Location', 'hive-core' ),
					'add_new_item'  => __( 'Add New Location', 'hive-core' ),
					'edit_item'     => __( 'Edit Location', 'hive-core' ),
					'view_item'     => __( 'View Location', 'hive-core' ),
					'all_items'     => __( 'All Locations', 'hive-core' ),
					'search_items'  => __( 'Search Locations', 'hive-core' ),
					'not_found'     => __( 'No locations found.', 'hive-core' ),
				),
				'public'       => true,
				'has_archive'  => 'locations',
				'rewrite'      => array( 'slug' => 'locations' ),
				'menu_icon'    => 'dashicons-location',
				'show_in_rest' => true,
				'rest_base'    => 'hive-locations',
				'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'revisions' ),
			)
		);

		register_post_type(
			self::SPACE,
			array(
				'labels'       => array(
					'name'          => __( 'Spaces', 'hive-core' ),
					'singular_name' => __( 'Space', 'hive-core' ),
					'add_new_item'  => __( 'Add New Space', 'hive-core' ),
					'edit_item'     => __( 'Edit Space', 'hive-core' ),
					'view_item'     => __( 'View Space', 'hive-core' ),
					'all_items'     => __( 'All Spaces', 'hive-core' ),
					'search_items'  => __( 'Search Spaces', 'hive-core' ),
					'not_found'     => __( 'No spaces found.', 'hive-core' ),
				),
				'public'       => true,
				'has_archive'  => 'spaces',
				'rewrite'      => array( 'slug' => 'spaces' ),
				'menu_icon'    => 'dashicons-building',
				'show_in_rest' => true,
				'rest_base'    => 'hive-spaces',
				'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'revisions' ),
			)
		);

		register_post_type(
			self::PLAN,
			array(
				'labels'             => array(
					'name'          => __( 'Plans', 'hive-core' ),
					'singular_name' => __( 'Plan', 'hive-core' ),
					'add_new_item'  => __( 'Add New Plan', 'hive-core' ),
					'edit_item'     => __( 'Edit Plan', 'hive-core' ),
					'all_items'     => __( 'All Plans', 'hive-core' ),
					'search_items'  => __( 'Search Plans', 'hive-core' ),
					'not_found'     => __( 'No plans found.', 'hive-core' ),
				),
				// Plans are shown in the pricing block, not on their own pages.
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => true,
				'menu_icon'          => 'dashicons-tickets-alt',
				'show_in_rest'       => true,
				'rest_base'          => 'hive-plans',
				'supports'           => array( 'title', 'editor', 'custom-fields', 'page-attributes' ),
			)
		);

		register_post_type(
			self::EVENT,
			array(
				'labels'       => array(
					'name'          => __( 'Events', 'hive-core' ),
					'singular_name' => __( 'Event', 'hive-core' ),
					'add_new_item'  => __( 'Add New Event', 'hive-core' ),
					'edit_item'     => __( 'Edit Event', 'hive-core' ),
					'view_item'     => __( 'View Event', 'hive-core' ),
					'all_items'     => __( 'All Events', 'hive-core' ),
					'search_items'  => __( 'Search Events', 'hive-core' ),
					'not_found'     => __( 'No events found.', 'hive-core' ),
				),
				'public'       => true,
				'has_archive'  => 'events',
				'rewrite'      => array( 'slug' => 'events' ),
				'menu_icon'    => 'dashicons-calendar-alt',
				'show_in_rest' => true,
				'rest_base'    => 'hive-events',
				'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields' ),
			)
		);

		register_taxonomy(
			self::AMENITY,
			array( self::SPACE ),
			array(
				'labels'            => array(
					'name'          => __( 'Amenities', 'hive-core' ),
					'singular_name' => __( 'Amenity', 'hive-core' ),
					'add_new_item'  => __( 'Add New Amenity', 'hive-core' ),
					'edit_item'     => __( 'Edit Amenity', 'hive-core' ),
					'all_items'     => __( 'All Amenities', 'hive-core' ),
					'search_items'  => __( 'Search Amenities', 'hive-core' ),
					'not_found'     => __( 'No amenities found.', 'hive-core' ),
				),
				'public'            => true,
				'hierarchical'      => false,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rest_base'         => 'hive-amenities',
				'rewrite'           => array( 'slug' => 'amenity' ),
			)
		);
	}
}
