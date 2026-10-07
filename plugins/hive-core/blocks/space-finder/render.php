<?php
/**
 * Server render of hive/space-finder.
 *
 * @package Hive\Core
 *
 * @var array<string, mixed> $attributes Block attributes.
 * @var WP_Block             $block      Block instance.
 */

use Hive\Core\Blocks\SpaceFinder;
use Hive\Core\Content\Meta;
use Hive\Core\Content\PostTypes;

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only filters from a GET form.
$hive_query_vars = wp_unslash( $_GET );
// phpcs:enable

$hive_filters     = SpaceFinder::with_context( SpaceFinder::filters_from_request( $hive_query_vars ), $block->context );
$hive_on_location = 'hive_location' === ( $block->context['postType'] ?? '' );
$hive_spaces      = new WP_Query( SpaceFinder::query_args( $hive_filters ) );
$hive_locations   = get_posts(
	array(
		'post_type'   => PostTypes::LOCATION,
		'numberposts' => -1,
		'orderby'     => 'title',
		'order'       => 'ASC',
	)
);
$hive_amenities   = get_terms(
	array(
		'taxonomy'   => PostTypes::AMENITY,
		'hide_empty' => true,
	)
);
$hive_id          = wp_unique_id( 'hive-finder-' );
?>
<div
	<?php echo get_block_wrapper_attributes( array( 'class' => 'hive-finder' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core. ?>
	data-wp-interactive="hive/space-finder"
	data-wp-context='{ "isLoading": false }'
	data-wp-class--is-loading="context.isLoading"
>
	<form class="hive-finder__filters" method="get" role="search" aria-label="<?php esc_attr_e( 'Filter spaces', 'hive-core' ); ?>" data-wp-on--submit="actions.submit">
		<?php
		// Keep parameters such as page_id when permalinks are plain.
		foreach ( $hive_query_vars as $hive_key => $hive_value ) :
			if ( is_string( $hive_value ) && ! str_starts_with( (string) $hive_key, SpaceFinder::PARAM_PREFIX ) ) :
				?>
				<input type="hidden" name="<?php echo esc_attr( (string) $hive_key ); ?>" value="<?php echo esc_attr( $hive_value ); ?>" />
				<?php
			endif;
		endforeach;
		?>

		<?php if ( ! $hive_on_location ) : ?>
		<div class="hive-finder__field">
			<label for="<?php echo esc_attr( $hive_id ); ?>-location"><?php esc_html_e( 'Location', 'hive-core' ); ?></label>
			<select id="<?php echo esc_attr( $hive_id ); ?>-location" name="<?php echo esc_attr( SpaceFinder::PARAM_LOCATION ); ?>" data-wp-on--change="actions.change">
				<option value=""><?php esc_html_e( 'All locations', 'hive-core' ); ?></option>
				<?php foreach ( $hive_locations as $hive_location ) : ?>
					<option value="<?php echo (int) $hive_location->ID; ?>" <?php selected( $hive_filters['location'], $hive_location->ID ); ?>><?php echo esc_html( get_the_title( $hive_location ) ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php endif; ?>

		<div class="hive-finder__field">
			<label for="<?php echo esc_attr( $hive_id ); ?>-type"><?php esc_html_e( 'Type', 'hive-core' ); ?></label>
			<select id="<?php echo esc_attr( $hive_id ); ?>-type" name="<?php echo esc_attr( SpaceFinder::PARAM_TYPE ); ?>" data-wp-on--change="actions.change">
				<option value=""><?php esc_html_e( 'Any type', 'hive-core' ); ?></option>
				<?php foreach ( Meta::SPACE_TYPES as $hive_type ) : ?>
					<option value="<?php echo esc_attr( $hive_type ); ?>" <?php selected( $hive_filters['type'], $hive_type ); ?>><?php echo esc_html( SpaceFinder::type_label( $hive_type ) ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="hive-finder__field">
			<label for="<?php echo esc_attr( $hive_id ); ?>-capacity"><?php esc_html_e( 'People', 'hive-core' ); ?></label>
			<input
				id="<?php echo esc_attr( $hive_id ); ?>-capacity"
				type="number"
				min="0"
				step="1"
				inputmode="numeric"
				name="<?php echo esc_attr( SpaceFinder::PARAM_CAPACITY ); ?>"
				value="<?php echo $hive_filters['capacity'] ? (int) $hive_filters['capacity'] : ''; ?>"
				placeholder="<?php esc_attr_e( 'Any', 'hive-core' ); ?>"
				data-wp-on--change="actions.change"
			/>
		</div>

		<?php if ( is_array( $hive_amenities ) && array() !== $hive_amenities ) : ?>
			<fieldset class="hive-finder__amenities-filter">
				<legend><?php esc_html_e( 'Amenities', 'hive-core' ); ?></legend>
				<?php foreach ( $hive_amenities as $hive_amenity ) : ?>
					<label class="hive-finder__check">
						<input
							type="checkbox"
							name="<?php echo esc_attr( SpaceFinder::PARAM_AMENITY ); ?>[]"
							value="<?php echo esc_attr( $hive_amenity->slug ); ?>"
							<?php checked( in_array( $hive_amenity->slug, $hive_filters['amenities'], true ) ); ?>
							data-wp-on--change="actions.change"
						/>
						<?php echo esc_html( $hive_amenity->name ); ?>
					</label>
				<?php endforeach; ?>
			</fieldset>
		<?php endif; ?>

		<div class="hive-finder__actions">
			<button type="submit" class="wp-element-button"><?php esc_html_e( 'Apply filters', 'hive-core' ); ?></button>
		</div>
	</form>

	<div class="hive-finder__results" data-wp-interactive="hive/space-finder" data-wp-router-region="hive-space-finder">
		<p class="hive-finder__count" role="status">
			<?php
			/* translators: %d: number of spaces. */
			echo esc_html( sprintf( _n( '%d space found', '%d spaces found', $hive_spaces->post_count, 'hive-core' ), $hive_spaces->post_count ) );
			?>
		</p>

		<?php if ( $hive_spaces->have_posts() ) : ?>
			<ul class="hive-finder__grid">
				<?php foreach ( $hive_spaces->posts as $hive_space ) : ?>
					<?php
					$hive_location_id = (int) get_post_meta( $hive_space->ID, Meta::SPACE_LOCATION, true );
					$hive_capacity    = (int) get_post_meta( $hive_space->ID, Meta::SPACE_CAPACITY, true );
					$hive_terms       = get_the_terms( $hive_space, PostTypes::AMENITY );
					?>
					<li class="hive-finder__card">
						<a class="hive-finder__image<?php echo has_post_thumbnail( $hive_space ) ? '' : ' is-empty'; ?>" href="<?php echo esc_url( get_permalink( $hive_space ) ); ?>" tabindex="-1" aria-hidden="true">
							<?php echo get_the_post_thumbnail( $hive_space, 'medium_large' ); ?>
						</a>
						<div class="hive-finder__body">
							<p class="hive-finder__meta">
								<?php echo esc_html( SpaceFinder::type_label( (string) get_post_meta( $hive_space->ID, Meta::SPACE_TYPE, true ) ) ); ?>
								<?php if ( $hive_location_id ) : ?>
									· <?php echo esc_html( get_the_title( $hive_location_id ) ); ?>
								<?php endif; ?>
							</p>
							<h3 class="hive-finder__title">
								<a href="<?php echo esc_url( get_permalink( $hive_space ) ); ?>"><?php echo esc_html( get_the_title( $hive_space ) ); ?></a>
							</h3>
							<p class="hive-finder__facts">
								<?php
								echo esc_html(
									sprintf(
										/* translators: 1: number of people, 2: price such as €40. */
										_n( 'Up to %1$d person · %2$s / hour', 'Up to %1$d people · %2$s / hour', $hive_capacity, 'hive-core' ),
										$hive_capacity,
										SpaceFinder::format_price( (float) get_post_meta( $hive_space->ID, Meta::SPACE_PRICE, true ) )
									)
								);
								?>
							</p>
							<?php if ( is_array( $hive_terms ) ) : ?>
								<ul class="hive-finder__tags" aria-label="<?php esc_attr_e( 'Amenities', 'hive-core' ); ?>">
									<?php foreach ( $hive_terms as $hive_term ) : ?>
										<li><?php echo esc_html( $hive_term->name ); ?></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php else : ?>
			<p class="hive-finder__empty"><?php esc_html_e( 'No spaces match these filters. Try removing some of them.', 'hive-core' ); ?></p>
		<?php endif; ?>
	</div>
</div>
