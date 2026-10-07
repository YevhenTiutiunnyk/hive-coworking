<?php
/**
 * Title: Events section
 * Slug: hive/events-section
 * Categories: hive
 * Description: Heading, the next three events and a link to all events.
 *
 * @package Hive
 */

?>
<!-- wp:group {"className":"hive-reveal","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group hive-reveal" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|60"}}}} -->
	<div class="wp-block-columns alignwide">
		<!-- wp:column {"width":"38%"} -->
		<div class="wp-block-column" style="flex-basis:38%">
			<!-- wp:paragraph {"className":"hive-eyebrow"} -->
			<p class="hive-eyebrow"><?php esc_html_e( 'Community', 'hive' ); ?></p>
			<!-- /wp:paragraph -->
			<!-- wp:heading -->
			<h2 class="wp-block-heading"><?php esc_html_e( 'Coming up', 'hive' ); ?></h2>
			<!-- /wp:heading -->
			<!-- wp:paragraph {"textColor":"contrast-2"} -->
			<p class="has-contrast-2-color has-text-color"><?php esc_html_e( 'Breakfasts, meetups and evenings with people from all three locations. Add any event to your calendar in one click.', 'hive' ); ?></p>
			<!-- /wp:paragraph -->
			<!-- wp:paragraph -->
			<p><a href="<?php echo esc_url( home_url( '/events/' ) ); ?>"><?php esc_html_e( 'All events →', 'hive' ); ?></a></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->
		<!-- wp:column {"width":"62%"} -->
		<div class="wp-block-column" style="flex-basis:62%">
			<!-- wp:hive/upcoming-events {"count":3} /-->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
