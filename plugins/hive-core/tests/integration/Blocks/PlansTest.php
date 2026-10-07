<?php
namespace Hive\Core\Tests\Integration\Blocks;

use Hive\Core\Blocks\Plans;
use Hive\Core\Content\Meta;
use Hive\Core\Content\PostTypes;
use Hive\Core\Tests\Integration\TestCase;

final class PlansTest extends TestCase {

	public function test_lists_published_plans_in_menu_order(): void {
		$this->plan( 'Office', 2, 899, 8990 );
		$this->plan( 'Flex', 1, 149, 1490, true, array( '10 days', 'Coffee' ) );
		$this->plan( 'Draft', 0, 1, 1, false, array(), 'draft' );

		$plans = Plans::all();

		$this->assertSame( array( 'Flex', 'Office' ), array_column( $plans, 'title' ) );
		$this->assertSame( 149.0, $plans[0]['monthly'] );
		$this->assertSame( 1490.0, $plans[0]['yearly'] );
		$this->assertTrue( $plans[0]['featured'] );
		$this->assertSame( array( '10 days', 'Coffee' ), $plans[0]['features'] );
	}

	public function test_calculates_yearly_savings(): void {
		$this->plan( 'Flex', 1, 149, 1490 );

		// 12 × 149 = 1788; 1490 is 16.7% less.
		$this->assertSame( 17, Plans::all()[0]['savings'] );
	}

	public function test_savings_are_zero_without_a_monthly_price(): void {
		$this->plan( 'Free', 1, 0, 0 );

		$this->assertSame( 0, Plans::all()[0]['savings'] );
	}

	/**
	 * @param list<string> $features
	 */
	private function plan( string $title, int $order, float $monthly, float $yearly, bool $featured = false, array $features = array(), string $status = 'publish' ): int {
		return self::factory()->post->create(
			array(
				'post_type'   => PostTypes::PLAN,
				'post_title'  => $title,
				'post_status' => $status,
				'menu_order'  => $order,
				'meta_input'  => array(
					Meta::PLAN_PRICE_MONTHLY => $monthly,
					Meta::PLAN_PRICE_YEARLY  => $yearly,
					Meta::PLAN_FEATURED      => $featured,
					Meta::PLAN_FEATURES      => $features,
				),
			)
		);
	}
}
