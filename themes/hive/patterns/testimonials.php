<?php
/**
 * Title: Testimonials
 * Slug: hive/testimonials
 * Categories: hive, testimonials
 * Description: Three member quotes on a honeycomb band.
 *
 * @package Hive
 */

?>
<!-- wp:group {"className":"hive-honeycomb hive-reveal","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"backgroundColor":"base-2","layout":{"type":"constrained"}} -->
<div class="wp-block-group hive-honeycomb hive-reveal has-base-2-background-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:heading {"align":"wide"} -->
	<h2 class="wp-block-heading alignwide"><?php esc_html_e( 'What members say', 'hive' ); ?></h2>
	<!-- /wp:heading -->
	<!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|40"},"margin":{"top":"var:preset|spacing|50"}}}} -->
	<div class="wp-block-columns alignwide" style="margin-top:var(--wp--preset--spacing--50)">
		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:group {"tagName":"figure","className":"hive-quote","layout":{"type":"default"}} -->
			<figure class="wp-block-group hive-quote">
				<!-- wp:paragraph {"fontFamily":"display","fontSize":"large"} -->
				<p class="has-display-font-family has-large-font-size"><?php esc_html_e( 'I book the Lighthouse Room for client workshops. It takes thirty seconds and the screen always works.', 'hive' ); ?></p>
				<!-- /wp:paragraph -->
				<!-- wp:paragraph {"fontSize":"small","textColor":"contrast-2"} -->
				<p class="has-contrast-2-color has-text-color has-small-font-size"><?php esc_html_e( 'Maya, product designer', 'hive' ); ?></p>
				<!-- /wp:paragraph -->
			</figure>
			<!-- /wp:group -->
		</div>
		<!-- /wp:column -->
		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:group {"tagName":"figure","className":"hive-quote","layout":{"type":"default"}} -->
			<figure class="wp-block-group hive-quote">
				<!-- wp:paragraph {"fontFamily":"display","fontSize":"large"} -->
				<p class="has-display-font-family has-large-font-size"><?php esc_html_e( 'The Old Town reading room is the only place I can write for four hours straight.', 'hive' ); ?></p>
				<!-- /wp:paragraph -->
				<!-- wp:paragraph {"fontSize":"small","textColor":"contrast-2"} -->
				<p class="has-contrast-2-color has-text-color has-small-font-size"><?php esc_html_e( 'Tomás, freelance journalist', 'hive' ); ?></p>
				<!-- /wp:paragraph -->
			</figure>
			<!-- /wp:group -->
		</div>
		<!-- /wp:column -->
		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:group {"tagName":"figure","className":"hive-quote","layout":{"type":"default"}} -->
			<figure class="wp-block-group hive-quote">
				<!-- wp:paragraph {"fontFamily":"display","fontSize":"large"} -->
				<p class="has-display-font-family has-large-font-size"><?php esc_html_e( 'We moved our team of three into the Terrace Office. Hourly booking let us try it before committing.', 'hive' ); ?></p>
				<!-- /wp:paragraph -->
				<!-- wp:paragraph {"fontSize":"small","textColor":"contrast-2"} -->
				<p class="has-contrast-2-color has-text-color has-small-font-size"><?php esc_html_e( 'Ines, startup founder', 'hive' ); ?></p>
				<!-- /wp:paragraph -->
			</figure>
			<!-- /wp:group -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
