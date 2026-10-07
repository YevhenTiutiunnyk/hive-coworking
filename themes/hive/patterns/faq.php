<?php
/**
 * Title: Frequently asked questions
 * Slug: hive/faq
 * Categories: hive
 * Description: Questions and answers in expandable rows.
 *
 * @package Hive
 */

?>
<!-- wp:group {"className":"hive-reveal","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group hive-reveal" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:heading -->
	<h2 class="wp-block-heading"><?php esc_html_e( 'Questions, answered', 'hive' ); ?></h2>
	<!-- /wp:heading -->
	<!-- wp:details -->
	<details class="wp-block-details"><summary><?php esc_html_e( 'Do I need a membership to book a room?', 'hive' ); ?></summary>
		<!-- wp:paragraph {"textColor":"contrast-2"} -->
		<p class="has-contrast-2-color has-text-color"><?php esc_html_e( 'No. Anyone with an account can book by the hour. Members get included hours and a lower rate.', 'hive' ); ?></p>
		<!-- /wp:paragraph -->
	</details>
	<!-- /wp:details -->
	<!-- wp:details -->
	<details class="wp-block-details"><summary><?php esc_html_e( 'How far ahead can I book?', 'hive' ); ?></summary>
		<!-- wp:paragraph {"textColor":"contrast-2"} -->
		<p class="has-contrast-2-color has-text-color"><?php esc_html_e( 'Up to sixty days ahead, from one hour before the start time.', 'hive' ); ?></p>
		<!-- /wp:paragraph -->
	</details>
	<!-- /wp:details -->
	<!-- wp:details -->
	<details class="wp-block-details"><summary><?php esc_html_e( 'Can I cancel a booking?', 'hive' ); ?></summary>
		<!-- wp:paragraph {"textColor":"contrast-2"} -->
		<p class="has-contrast-2-color has-text-color"><?php esc_html_e( 'Yes, from My bookings, up to two hours before it starts. After that, contact the front desk.', 'hive' ); ?></p>
		<!-- /wp:paragraph -->
	</details>
	<!-- /wp:details -->
	<!-- wp:details -->
	<details class="wp-block-details"><summary><?php esc_html_e( 'Is there coffee?', 'hive' ); ?></summary>
		<!-- wp:paragraph {"textColor":"contrast-2"} -->
		<p class="has-contrast-2-color has-text-color"><?php esc_html_e( 'Always. Free coffee and tea at every location, and a kitchen you can use.', 'hive' ); ?></p>
		<!-- /wp:paragraph -->
	</details>
	<!-- /wp:details -->
</div>
<!-- /wp:group -->
