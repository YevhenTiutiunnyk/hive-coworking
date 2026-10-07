<?php
/**
 * Title: Call to action
 * Slug: hive/cta
 * Categories: hive, call-to-action
 * Description: A dark band with a headline and a button.
 *
 * @package Hive
 */

?>
<!-- wp:group {"className":"hive-band","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"backgroundColor":"accent","textColor":"accent-contrast","layout":{"type":"constrained"}} -->
<div class="wp-block-group hive-band has-accent-contrast-color has-accent-background-color has-text-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:group {"align":"wide","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"center"}} -->
	<div class="wp-block-group alignwide">
		<!-- wp:heading {"textColor":"accent-contrast","fontSize":"xx-large"} -->
		<h2 class="wp-block-heading has-accent-contrast-color has-text-color has-xx-large-font-size"><?php esc_html_e( 'Your desk is waiting.', 'hive' ); ?></h2>
		<!-- /wp:heading -->
		<!-- wp:buttons -->
		<div class="wp-block-buttons">
			<!-- wp:button {"backgroundColor":"contrast","textColor":"base"} -->
			<div class="wp-block-button"><a class="wp-block-button__link has-base-color has-contrast-background-color has-text-color has-background wp-element-button" href="<?php echo esc_url( home_url( '/spaces/' ) ); ?>"><?php esc_html_e( 'Book a space', 'hive' ); ?></a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
