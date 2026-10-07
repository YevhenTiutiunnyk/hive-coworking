<?php
/**
 * Title: Page not found
 * Slug: hive/404
 * Inserter: no
 *
 * @package Hive
 */

?>
<!-- wp:group {"className":"hive-honeycomb","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70"}}},"layout":{"type":"constrained","contentSize":"640px"}} -->
<div class="wp-block-group hive-honeycomb" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--70)">
	<!-- wp:paragraph {"className":"hive-eyebrow"} -->
	<p class="hive-eyebrow"><?php esc_html_e( 'Error 404', 'hive' ); ?></p>
	<!-- /wp:paragraph -->
	<!-- wp:heading {"level":1,"fontSize":"display"} -->
	<h1 class="wp-block-heading has-display-font-size"><?php esc_html_e( 'This room is empty.', 'hive' ); ?></h1>
	<!-- /wp:heading -->
	<!-- wp:paragraph {"fontSize":"large"} -->
	<p class="has-large-font-size"><?php esc_html_e( 'The page you were looking for has moved or never existed. Plenty of other rooms are free, though.', 'hive' ); ?></p>
	<!-- /wp:paragraph -->
	<!-- wp:buttons -->
	<div class="wp-block-buttons">
		<!-- wp:button -->
		<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/spaces/' ) ); ?>"><?php esc_html_e( 'Find a space', 'hive' ); ?></a></div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
