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
	$path = get_theme_file_path( 'assets/css/theme.css' );

	// The file time busts browser caches whenever the stylesheet changes.
	wp_enqueue_style( 'hive-theme', get_theme_file_uri( 'assets/css/theme.css' ), array(), (string) filemtime( $path ) );
}
add_action( 'wp_enqueue_scripts', 'hive_enqueue_styles' );

/**
 * Registers the pattern category used by the theme's patterns.
 */
function hive_register_pattern_category(): void {
	register_block_pattern_category( 'hive', array( 'label' => __( 'Hive Coworking', 'hive' ) ) );
}
add_action( 'init', 'hive_register_pattern_category' );

/**
 * Shows a honeycomb illustration for spaces, locations and events without a photo.
 *
 * @param string               $block_content Rendered block.
 * @param array<string, mixed> $block         Parsed block.
 */
function hive_featured_image_placeholder( string $block_content, array $block ): string {
	if ( '' !== trim( $block_content ) || ! in_array( get_post_type(), array( 'hive_space', 'hive_location', 'hive_event' ), true ) ) {
		return $block_content;
	}

	$classes = array( 'wp-block-post-featured-image', 'hive-placeholder' );
	if ( ! empty( $block['attrs']['align'] ) ) {
		$classes[] = 'align' . $block['attrs']['align'];
	}

	$ratio = $block['attrs']['aspectRatio'] ?? '';
	$style = preg_match( '#^\d+/\d+$#', $ratio ) ? sprintf( ' style="aspect-ratio:%s"', esc_attr( $ratio ) ) : '';

	return sprintf( '<div class="%s"%s aria-hidden="true"></div>', esc_attr( implode( ' ', $classes ) ), $style );
}
add_filter( 'render_block_core/post-featured-image', 'hive_featured_image_placeholder', 10, 2 );
