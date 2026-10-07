<?php
/**
 * Title: Pricing section
 * Slug: hive/pricing-section
 * Categories: hive
 * Description: Heading and the plan comparison block.
 *
 * @package Hive
 */

?>
<!-- wp:group {"className":"hive-reveal","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group hive-reveal" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:paragraph {"align":"wide","className":"hive-eyebrow"} -->
	<p class="alignwide hive-eyebrow"><?php esc_html_e( 'Membership', 'hive' ); ?></p>
	<!-- /wp:paragraph -->
	<!-- wp:heading {"align":"wide"} -->
	<h2 class="wp-block-heading alignwide"><?php esc_html_e( 'Plans that grow with you', 'hive' ); ?></h2>
	<!-- /wp:heading -->
	<!-- wp:paragraph {"align":"wide","textColor":"contrast-2","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|50"}}}} -->
	<p class="alignwide has-contrast-2-color has-text-color" style="margin-bottom:var(--wp--preset--spacing--50)"><?php esc_html_e( 'Book by the hour with no plan at all, or become a member for included hours and 24/7 access.', 'hive' ); ?></p>
	<!-- /wp:paragraph -->
	<!-- wp:hive/plan-comparison {"align":"wide"} /-->
</div>
<!-- /wp:group -->
