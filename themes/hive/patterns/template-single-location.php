<?php
/**
 * Title: Single location
 * Slug: hive/template-single-location
 * Inserter: no
 *
 * @package Hive
 */

?>
<!-- wp:group {"tagName":"main","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<main class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:group {"align":"wide","style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"constrained","contentSize":"820px","justifyContent":"left"}} -->
	<div class="wp-block-group alignwide">
		<!-- wp:paragraph {"className":"hive-eyebrow"} -->
		<p class="hive-eyebrow"><?php esc_html_e( 'Location', 'hive' ); ?></p>
		<!-- /wp:paragraph -->
		<!-- wp:post-title {"level":1,"fontSize":"display"} /-->
		<!-- wp:post-excerpt {"fontSize":"large","textColor":"contrast-2"} /-->
		<!-- wp:group {"className":"hive-facts","layout":{"type":"flex","flexWrap":"wrap"}} -->
		<div class="wp-block-group hive-facts">
			<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"hive/fields","args":{"key":"location-address"}}}}} -->
			<p></p>
			<!-- /wp:paragraph -->
			<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"hive/fields","args":{"key":"location-today"}}}}} -->
			<p></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->

	<!-- wp:post-featured-image {"align":"wide","aspectRatio":"21/9","style":{"border":{"radius":"28px"},"spacing":{"margin":{"top":"var:preset|spacing|50"}}}} /-->

	<!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|60"},"margin":{"top":"var:preset|spacing|50"}}}} -->
	<div class="wp-block-columns alignwide" style="margin-top:var(--wp--preset--spacing--50)">
		<!-- wp:column {"width":"60%"} -->
		<div class="wp-block-column" style="flex-basis:60%">
			<!-- wp:post-content {"layout":{"type":"default"}} /-->
		</div>
		<!-- /wp:column -->
		<!-- wp:column {"width":"40%","style":{"spacing":{"blockGap":"var:preset|spacing|50"}}} -->
		<div class="wp-block-column" style="flex-basis:40%">
			<!-- wp:hive/opening-hours /-->
			<!-- wp:group {"layout":{"type":"default"}} -->
			<div class="wp-block-group">
				<!-- wp:heading {"fontSize":"large"} -->
				<h2 class="wp-block-heading has-large-font-size"><?php esc_html_e( 'Events here', 'hive' ); ?></h2>
				<!-- /wp:heading -->
				<!-- wp:hive/upcoming-events {"count":3} /-->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->

	<!-- wp:heading {"align":"wide","style":{"spacing":{"margin":{"top":"var:preset|spacing|60"}}}} -->
	<h2 class="wp-block-heading alignwide" style="margin-top:var(--wp--preset--spacing--60)"><?php esc_html_e( 'Spaces at this location', 'hive' ); ?></h2>
	<!-- /wp:heading -->
	<!-- wp:hive/space-finder {"align":"wide"} /-->
</main>
<!-- /wp:group -->
