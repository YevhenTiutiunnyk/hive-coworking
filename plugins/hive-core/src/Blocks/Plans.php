<?php
namespace Hive\Core\Blocks;

use Hive\Core\Content\Meta;
use Hive\Core\Content\PostTypes;

/**
 * Data for the hive/plan-comparison block.
 */
final class Plans {

	/**
	 * Published plans in menu order.
	 *
	 * @return list<array{id: int, title: string, description: string, monthly: float, yearly: float, savings: int, featured: bool, features: list<string>}>
	 */
	public static function all(): array {
		$posts = get_posts(
			array(
				'post_type'   => PostTypes::PLAN,
				'post_status' => 'publish',
				'numberposts' => 20,
				'orderby'     => 'menu_order',
				'order'       => 'ASC',
			)
		);

		return array_map(
			static function ( $post ): array {
				$monthly  = (float) get_post_meta( $post->ID, Meta::PLAN_PRICE_MONTHLY, true );
				$yearly   = (float) get_post_meta( $post->ID, Meta::PLAN_PRICE_YEARLY, true );
				$features = get_post_meta( $post->ID, Meta::PLAN_FEATURES, true );

				return array(
					'id'          => $post->ID,
					'title'       => get_the_title( $post ),
					'description' => trim( wp_strip_all_tags( $post->post_content ) ),
					'monthly'     => $monthly,
					'yearly'      => $yearly,
					'savings'     => $monthly > 0 ? (int) round( 100 * ( 1 - $yearly / ( 12 * $monthly ) ) ) : 0,
					'featured'    => (bool) get_post_meta( $post->ID, Meta::PLAN_FEATURED, true ),
					'features'    => is_array( $features ) ? array_values( array_map( 'strval', $features ) ) : array(),
				);
			},
			$posts
		);
	}
}
