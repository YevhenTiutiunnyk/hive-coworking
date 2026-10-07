<?php
/**
 * Title: Locations grid
 * Slug: hive/locations
 * Categories: hive
 * Description: All locations as cards with today's opening hours.
 *
 * @package Hive
 */

?>
<!-- wp:group {"className":"hive-reveal","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group hive-reveal" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:group {"align":"wide","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"bottom"}} -->
	<div class="wp-block-group alignwide">
		<!-- wp:group {"layout":{"type":"default"}} -->
		<div class="wp-block-group">
			<!-- wp:paragraph {"className":"hive-eyebrow"} -->
			<p class="hive-eyebrow"><?php esc_html_e( 'Locations', 'hive' ); ?></p>
			<!-- /wp:paragraph -->
			<!-- wp:heading -->
			<h2 class="wp-block-heading"><?php esc_html_e( 'Three places to work', 'hive' ); ?></h2>
			<!-- /wp:heading -->
		</div>
		<!-- /wp:group -->
		<!-- wp:paragraph -->
		<p><a href="<?php echo esc_url( home_url( '/locations/' ) ); ?>"><?php esc_html_e( 'All locations →', 'hive' ); ?></a></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->

	<!-- wp:query {"queryId":20,"query":{"perPage":3,"postType":"hive_location","order":"asc","orderBy":"date","inherit":false},"align":"wide"} -->
	<div class="wp-block-query alignwide">
		<!-- wp:post-template {"layout":{"type":"grid","columnCount":3,"minimumColumnWidth":"18rem"}} -->
		<!-- wp:group {"className":"hive-card","style":{"spacing":{"blockGap":"0"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"stretch"}} -->
		<div class="wp-block-group hive-card">
			<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"4/3"} /-->
			<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40","right":"var:preset|spacing|40"},"blockGap":"var:preset|spacing|20"}},"layout":{"type":"flex","orientation":"vertical"}} -->
			<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)">
				<!-- wp:post-title {"level":3,"isLink":true,"fontSize":"x-large"} /-->
				<!-- wp:post-excerpt {"textColor":"contrast-2"} /-->
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
</div>
<!-- /wp:group -->
