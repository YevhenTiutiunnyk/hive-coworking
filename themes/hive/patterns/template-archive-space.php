<?php
/**
 * Title: Spaces archive
 * Slug: hive/template-archive-space
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
		<p class="hive-eyebrow"><?php esc_html_e( 'Spaces', 'hive' ); ?></p>
		<!-- /wp:paragraph -->
		<!-- wp:heading {"level":1,"fontSize":"display"} -->
		<h1 class="wp-block-heading has-display-font-size"><?php esc_html_e( 'Find your space', 'hive' ); ?></h1>
		<!-- /wp:heading -->
		<!-- wp:paragraph {"fontSize":"large","textColor":"contrast-2"} -->
		<p class="has-contrast-2-color has-text-color has-large-font-size"><?php esc_html_e( 'Meeting rooms, private offices and hot desks across three locations. Filter by what you need, then book by the hour.', 'hive' ); ?></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
	<!-- wp:hive/space-finder {"align":"wide"} /-->
</main>
<!-- /wp:group -->
