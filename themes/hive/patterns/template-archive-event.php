<?php
/**
 * Title: Events archive
 * Slug: hive/template-archive-event
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
		<p class="hive-eyebrow"><?php esc_html_e( 'Events', 'hive' ); ?></p>
		<!-- /wp:paragraph -->
		<!-- wp:heading {"level":1,"fontSize":"display"} -->
		<h1 class="wp-block-heading has-display-font-size"><?php esc_html_e( 'What\'s on', 'hive' ); ?></h1>
		<!-- /wp:heading -->
		<!-- wp:paragraph {"fontSize":"large","textColor":"contrast-2"} -->
		<p class="has-contrast-2-color has-text-color has-large-font-size"><?php esc_html_e( 'Breakfasts, meetups and evenings with the community. Members and guests welcome.', 'hive' ); ?></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
	<!-- wp:hive/upcoming-events {"count":12} /-->
</main>
<!-- /wp:group -->
