<?php
/**
 * Hive theme setup.
 *
 * @package Hive
 */

defined( 'ABSPATH' ) || exit;

/**
 * Adds editor styles and translations.
 */
function hive_setup(): void {
	add_editor_style( 'assets/css/theme.css' );
	load_theme_textdomain( 'hive', get_template_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'hive_setup' );

/**
 * Loads the theme's extra styles: textures, motion and details theme.json cannot express.
 */
function hive_enqueue_styles(): void {
	$theme = wp_get_theme();

	wp_enqueue_style( 'hive-theme', get_theme_file_uri( 'assets/css/theme.css' ), array(), $theme->get( 'Version' ) );
}
add_action( 'wp_enqueue_scripts', 'hive_enqueue_styles' );

/**
 * Registers the pattern category used by the theme's patterns.
 */
function hive_register_pattern_category(): void {
	register_block_pattern_category( 'hive', array( 'label' => __( 'Hive Coworking', 'hive' ) ) );
}
add_action( 'init', 'hive_register_pattern_category' );
