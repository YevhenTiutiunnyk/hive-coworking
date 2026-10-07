<?php
/**
 * Title: Header
 * Slug: hive/header
 * Categories: header
 * Block Types: core/template-part/header
 * Inserter: no
 *
 * @package Hive
 */

?>
<!-- wp:group {"className":"hive-header","style":{"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group hive-header" style="padding-top:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--30)">
	<!-- wp:group {"align":"wide","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
	<div class="wp-block-group alignwide">
		<!-- wp:site-title {"level":0} /-->

		<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|40"}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
		<div class="wp-block-group">
			<!-- wp:navigation {"overlayMenu":"mobile","layout":{"type":"flex","justifyContent":"right"}} -->
			<!-- wp:navigation-link {"label":"<?php echo esc_attr__( 'Spaces', 'hive' ); ?>","url":"<?php echo esc_url( home_url( '/spaces/' ) ); ?>","kind":"custom","isTopLevelLink":true} /-->
			<!-- wp:navigation-link {"label":"<?php echo esc_attr__( 'Locations', 'hive' ); ?>","url":"<?php echo esc_url( home_url( '/locations/' ) ); ?>","kind":"custom","isTopLevelLink":true} /-->
			<!-- wp:navigation-link {"label":"<?php echo esc_attr__( 'Pricing', 'hive' ); ?>","url":"<?php echo esc_url( home_url( '/pricing/' ) ); ?>","kind":"custom","isTopLevelLink":true} /-->
			<!-- wp:navigation-link {"label":"<?php echo esc_attr__( 'Events', 'hive' ); ?>","url":"<?php echo esc_url( home_url( '/events/' ) ); ?>","kind":"custom","isTopLevelLink":true} /-->
			<!-- /wp:navigation -->

			<!-- wp:buttons -->
			<div class="wp-block-buttons">
				<!-- wp:button {"fontSize":"small"} -->
				<div class="wp-block-button has-custom-font-size has-small-font-size"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/account/' ) ); ?>"><?php esc_html_e( 'My bookings', 'hive' ); ?></a></div>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
