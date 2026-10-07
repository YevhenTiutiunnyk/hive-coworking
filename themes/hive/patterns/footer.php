<?php
/**
 * Title: Footer
 * Slug: hive/footer
 * Categories: footer
 * Block Types: core/template-part/footer
 * Inserter: no
 *
 * @package Hive
 */

?>
<!-- wp:group {"className":"hive-footer","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|40"},"margin":{"top":"0"}}},"backgroundColor":"contrast","textColor":"base","layout":{"type":"constrained"}} -->
<div class="wp-block-group hive-footer has-base-color has-contrast-background-color has-text-color has-background" style="margin-top:0;padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--40)">
	<!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|50"}}}} -->
	<div class="wp-block-columns alignwide">
		<!-- wp:column {"width":"40%"} -->
		<div class="wp-block-column" style="flex-basis:40%">
			<!-- wp:site-title {"level":0} /-->
			<!-- wp:paragraph {"style":{"color":{"text":"color-mix(in srgb, currentcolor 75%, transparent)"}}} -->
			<p class="has-text-color" style="color:color-mix(in srgb, currentcolor 75%, transparent)"><?php esc_html_e( 'Three calm, well-run coworking spaces with rooms you can book by the hour. Fast Wi-Fi, good coffee and people who like their work.', 'hive' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:heading {"level":2,"fontSize":"medium","fontFamily":"body"} -->
			<h2 class="wp-block-heading has-body-font-family has-medium-font-size"><?php esc_html_e( 'Locations', 'hive' ); ?></h2>
			<!-- /wp:heading -->
			<!-- wp:query {"queryId":90,"query":{"perPage":6,"postType":"hive_location","order":"asc","orderBy":"title","inherit":false}} -->
			<div class="wp-block-query">
				<!-- wp:post-template {"style":{"spacing":{"blockGap":"0.4rem"}}} -->
				<!-- wp:post-title {"level":0,"isLink":true,"fontSize":"small","fontFamily":"body"} /-->
				<!-- /wp:post-template -->
			</div>
			<!-- /wp:query -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:heading {"level":2,"fontSize":"medium","fontFamily":"body"} -->
			<h2 class="wp-block-heading has-body-font-family has-medium-font-size"><?php esc_html_e( 'Explore', 'hive' ); ?></h2>
			<!-- /wp:heading -->
			<!-- wp:list {"className":"is-style-default","style":{"spacing":{"padding":{"left":"0"}}},"fontSize":"small"} -->
			<ul style="padding-left:0;list-style:none" class="wp-block-list is-style-default has-small-font-size">
				<!-- wp:list-item -->
				<li><a href="<?php echo esc_url( home_url( '/spaces/' ) ); ?>"><?php esc_html_e( 'Find a space', 'hive' ); ?></a></li>
				<!-- /wp:list-item -->
				<!-- wp:list-item -->
				<li><a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>"><?php esc_html_e( 'Membership plans', 'hive' ); ?></a></li>
				<!-- /wp:list-item -->
				<!-- wp:list-item -->
				<li><a href="<?php echo esc_url( home_url( '/events/' ) ); ?>"><?php esc_html_e( 'Events', 'hive' ); ?></a></li>
				<!-- /wp:list-item -->
				<!-- wp:list-item -->
				<li><a href="<?php echo esc_url( home_url( '/account/' ) ); ?>"><?php esc_html_e( 'My bookings', 'hive' ); ?></a></li>
				<!-- /wp:list-item -->
			</ul>
			<!-- /wp:list -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->

	<!-- wp:separator {"align":"wide","style":{"color":{"background":"color-mix(in srgb, currentcolor 20%, transparent)"},"spacing":{"margin":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|30"}}},"className":"is-style-wide"} -->
	<hr class="wp-block-separator alignwide has-text-color has-alpha-channel-opacity has-background is-style-wide" style="margin-top:var(--wp--preset--spacing--50);margin-bottom:var(--wp--preset--spacing--30);background-color:color-mix(in srgb, currentcolor 20%, transparent);color:color-mix(in srgb, currentcolor 20%, transparent)"/>
	<!-- /wp:separator -->

	<!-- wp:paragraph {"align":"wide","fontSize":"small","style":{"color":{"text":"color-mix(in srgb, currentcolor 65%, transparent)"}}} -->
	<p class="alignwide has-text-color has-small-font-size" style="color:color-mix(in srgb, currentcolor 65%, transparent)">
		<?php
		printf(
			/* translators: %s: link to the source code. */
			esc_html__( 'Hive Coworking is a fictional company. This site is a WordPress portfolio project — %s.', 'hive' ),
			'<a href="https://github.com/YevhenTiutiunnyk/hive-coworking">' . esc_html__( 'view the source on GitHub', 'hive' ) . '</a>'
		);
		?>
	</p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
