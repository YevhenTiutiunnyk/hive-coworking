<?php
/**
 * Title: Single space
 * Slug: hive/template-single-space
 * Inserter: no
 *
 * @package Hive
 */

?>
<!-- wp:group {"tagName":"main","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<main class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:group {"align":"wide","style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"default"}} -->
	<div class="wp-block-group alignwide">
		<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"hive/fields","args":{"key":"space-type"}}}},"className":"hive-eyebrow"} -->
		<p class="hive-eyebrow"></p>
		<!-- /wp:paragraph -->
		<!-- wp:post-title {"level":1,"fontSize":"display"} /-->
		<!-- wp:group {"className":"hive-facts","layout":{"type":"flex","flexWrap":"wrap"}} -->
		<div class="wp-block-group hive-facts">
			<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"hive/fields","args":{"key":"space-capacity"}}}}} -->
			<p></p>
			<!-- /wp:paragraph -->
			<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"hive/fields","args":{"key":"space-price"}}}}} -->
			<p></p>
			<!-- /wp:paragraph -->
			<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"hive/fields","args":{"key":"space-location"}}}}} -->
			<p></p>
			<!-- /wp:paragraph -->
			<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"hive/fields","args":{"key":"location-today"}}}}} -->
			<p></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->

	<!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|60"},"margin":{"top":"var:preset|spacing|50"}}}} -->
	<div class="wp-block-columns alignwide" style="margin-top:var(--wp--preset--spacing--50)">
		<!-- wp:column {"width":"58%"} -->
		<div class="wp-block-column" style="flex-basis:58%">
			<!-- wp:post-featured-image {"aspectRatio":"4/3","style":{"border":{"radius":"16px"}}} /-->
			<!-- wp:post-excerpt {"fontSize":"large"} /-->
			<!-- wp:post-content {"layout":{"type":"default"}} /-->
			<!-- wp:heading {"fontSize":"large"} -->
			<h2 class="wp-block-heading has-large-font-size"><?php esc_html_e( 'Amenities', 'hive' ); ?></h2>
			<!-- /wp:heading -->
			<!-- wp:post-terms {"term":"hive_amenity","separator":"","className":"hive-facts"} /-->
		</div>
		<!-- /wp:column -->

		<!-- wp:column {"width":"42%"} -->
		<div class="wp-block-column" style="flex-basis:42%">
			<!-- wp:group {"style":{"position":{"type":"sticky","top":"6rem"},"spacing":{"blockGap":"var:preset|spacing|40"}},"layout":{"type":"default"}} -->
			<div class="wp-block-group">
				<!-- wp:heading {"fontSize":"x-large"} -->
				<h2 class="wp-block-heading has-x-large-font-size"><?php esc_html_e( 'Book this space', 'hive' ); ?></h2>
				<!-- /wp:heading -->
				<!-- wp:hive/booking-widget /-->
				<!-- wp:hive/opening-hours {"fontSize":"small"} /-->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</main>
<!-- /wp:group -->
