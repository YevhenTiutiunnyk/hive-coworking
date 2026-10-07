<?php
/**
 * Server render of hive/upcoming-events.
 *
 * @package Hive\Core
 *
 * @var array<string, mixed> $attributes Block attributes.
 * @var WP_Block             $block      Block instance.
 */

use Hive\Core\Events\EventRepository;

$hive_location = (int) ( $attributes['locationId'] ?? 0 );

// On a location page, show that location's events unless one was chosen.
if ( 0 === $hive_location && 'hive_location' === ( $block->context['postType'] ?? '' ) ) {
	$hive_location = (int) ( $block->context['postId'] ?? 0 );
}

$hive_events = ( new EventRepository() )->upcoming( current_datetime(), max( 1, (int) ( $attributes['count'] ?? 3 ) ), $hive_location );
$hive_time   = get_option( 'time_format' );
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'hive-events' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core. ?>>
	<?php if ( array() === $hive_events ) : ?>
		<p class="hive-events__empty"><?php esc_html_e( 'No upcoming events. Check back soon.', 'hive-core' ); ?></p>
	<?php else : ?>
		<ul class="hive-events__list">
			<?php foreach ( $hive_events as $hive_event ) : ?>
				<li class="hive-events__item">
					<time class="hive-events__date" datetime="<?php echo esc_attr( $hive_event->start->format( DATE_ATOM ) ); ?>">
						<span class="hive-events__day"><?php echo esc_html( wp_date( 'j', $hive_event->start->getTimestamp() ) ); ?></span>
						<span class="hive-events__month"><?php echo esc_html( wp_date( 'M', $hive_event->start->getTimestamp() ) ); ?></span>
					</time>
					<div class="hive-events__body">
						<h3 class="hive-events__title"><a href="<?php echo esc_url( $hive_event->url ); ?>"><?php echo esc_html( $hive_event->title ); ?></a></h3>
						<p class="hive-events__meta">
							<?php
							echo esc_html(
								sprintf(
									/* translators: 1: weekday, 2: start time, 3: end time. */
									__( '%1$s, %2$s–%3$s', 'hive-core' ),
									wp_date( 'l', $hive_event->start->getTimestamp() ),
									wp_date( $hive_time, $hive_event->start->getTimestamp() ),
									wp_date( $hive_time, $hive_event->end->getTimestamp() )
								)
							);
							if ( $hive_event->location_id ) {
								echo ' · ' . esc_html( get_the_title( $hive_event->location_id ) );
							}
							?>
						</p>
						<?php if ( '' !== $hive_event->excerpt ) : ?>
							<p class="hive-events__excerpt"><?php echo esc_html( $hive_event->excerpt ); ?></p>
						<?php endif; ?>
						<a class="hive-events__ics" href="<?php echo esc_url( rest_url( sprintf( 'hive/v1/events/%d/ics', $hive_event->id ) ) ); ?>" download>
							<?php esc_html_e( 'Add to calendar', 'hive-core' ); ?>
							<span class="screen-reader-text">
								<?php
								/* translators: %s: event title. */
								echo esc_html( sprintf( __( '(%s)', 'hive-core' ), $hive_event->title ) );
								?>
							</span>
						</a>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
