<?php
/**
 * Server render of hive/opening-hours.
 *
 * @package Hive\Core
 *
 * @var array<string, mixed> $attributes Block attributes.
 * @var WP_Block             $block      Block instance.
 */

use Hive\Core\Blocks\OpeningHoursTable;

$hive_location = (int) ( $attributes['locationId'] ?? 0 );
if ( 0 === $hive_location ) {
	$hive_location = OpeningHoursTable::location_for( (int) ( $block->context['postId'] ?? get_the_ID() ) );
}

if ( 0 === $hive_location ) {
	return;
}
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'hive-hours' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core. ?>>
	<table class="hive-hours__table">
		<caption><?php esc_html_e( 'Opening hours', 'hive-core' ); ?></caption>
		<tbody>
			<?php foreach ( OpeningHoursTable::rows( $hive_location, current_datetime() ) as $hive_row ) : ?>
				<tr<?php echo $hive_row['today'] ? ' class="is-today" aria-current="date"' : ''; ?>>
					<th scope="row"><?php echo esc_html( $hive_row['day'] ); ?></th>
					<td><?php echo esc_html( $hive_row['hours'] ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
