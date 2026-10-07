<?php
/**
 * Title: Locations archive
 * Slug: hive/template-archive-location
 * Inserter: no
 *
 * @package Hive
 */

?>
<!-- wp:group {"tagName":"main","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<main class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:group {"align":"wide","style":{"spacing":{"blockGap":"var:preset|spacing|30","margin":{"bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained","contentSize":"760px","justifyContent":"left"}} -->
	<div class="wp-block-group alignwide" style="margin-bottom:var(--wp--preset--spacing--50)">
		<!-- wp:paragraph {"className":"hive-eyebrow"} -->
		<p class="hive-eyebrow"><?php esc_html_e( 'Locations', 'hive' ); ?></p>
		<!-- /wp:paragraph -->
		<!-- wp:heading {"level":1,"fontSize":"display"} -->
		<h1 class="wp-block-heading has-display-font-size"><?php esc_html_e( 'Three places to work', 'hive' ); ?></h1>
		<!-- /wp:heading -->
		<!-- wp:paragraph {"fontSize":"large","textColor":"contrast-2"} -->
		<p class="has-contrast-2-color has-text-color has-large-font-size"><?php esc_html_e( 'A converted warehouse on the water, a quiet library above a clockmaker\'s shop and a greenhouse full of plants.', 'hive' ); ?></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
	<!-- wp:query {"queryId":2,"query":{"inherit":true},"align":"wide"} -->
	<div class="wp-block-query alignwide">
		<!-- wp:post-template {"layout":{"type":"grid","columnCount":3,"minimumColumnWidth":"18rem"}} -->
		<!-- wp:group {"className":"hive-card","style":{"spacing":{"blockGap":"0"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"stretch"}} -->
		<div class="wp-block-group hive-card">
			<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"4/3"} /-->
			<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40","right":"var:preset|spacing|40"},"blockGap":"var:preset|spacing|20"}},"layout":{"type":"flex","orientation":"vertical"}} -->
			<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)">
				<!-- wp:post-title {"level":2,"isLink":true,"fontSize":"x-large"} /-->
				<!-- wp:post-excerpt {"textColor":"contrast-2"} /-->
				<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"hive/fields","args":{"key":"location-address"}}}},"fontSize":"small"} -->
				<p class="has-small-font-size"></p>
				<!-- /wp:paragraph -->
				<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"hive/fields","args":{"key":"location-today"}}}},"fontSize":"small","textColor":"accent-2"} -->
				<p class="has-accent-2-color has-text-color has-small-font-size"></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->
		<!-- /wp:post-template -->
	</div>
	<!-- /wp:query -->
</main>
<!-- /wp:group -->
