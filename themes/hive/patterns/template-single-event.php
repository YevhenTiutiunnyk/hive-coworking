<?php
/**
 * Title: Single event
 * Slug: hive/template-single-event
 * Inserter: no
 *
 * @package Hive
 */

?>
<!-- wp:group {"tagName":"main","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<main class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:paragraph {"className":"hive-eyebrow"} -->
	<p class="hive-eyebrow"><?php esc_html_e( 'Event', 'hive' ); ?></p>
	<!-- /wp:paragraph -->
	<!-- wp:post-title {"level":1,"fontSize":"xx-large"} /-->
	<!-- wp:group {"className":"hive-facts","layout":{"type":"flex","flexWrap":"wrap"}} -->
	<div class="wp-block-group hive-facts">
		<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"hive/fields","args":{"key":"event-when"}}}}} -->
		<p></p>
		<!-- /wp:paragraph -->
		<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"hive/fields","args":{"key":"event-location"}}}}} -->
		<p></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
	<!-- wp:buttons -->
	<div class="wp-block-buttons">
		<!-- wp:button {"metadata":{"bindings":{"url":{"source":"hive/fields","args":{"key":"event-ics-url"}}}}} -->
		<div class="wp-block-button"><a class="wp-block-button__link wp-element-button"><?php esc_html_e( 'Add to calendar', 'hive' ); ?></a></div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->
	<!-- wp:post-featured-image {"aspectRatio":"16/9","style":{"border":{"radius":"16px"}}} /-->
	<!-- wp:post-excerpt {"fontSize":"large"} /-->
	<!-- wp:post-content {"layout":{"type":"default"}} /-->
	<!-- wp:paragraph -->
	<p><a href="<?php echo esc_url( home_url( '/events/' ) ); ?>"><?php esc_html_e( '← All events', 'hive' ); ?></a></p>
	<!-- /wp:paragraph -->
</main>
<!-- /wp:group -->
