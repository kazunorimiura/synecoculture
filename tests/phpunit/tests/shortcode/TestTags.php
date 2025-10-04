<?php

/**
 * WPF_Shortcode::tags のユニットテスト。
 *
 * @group template_tags
 * @covers WPF_Shortcode
 * @coversDefaultClass WPF_Shortcode
 */
class TestTags extends WPF_UnitTestCase {

	/**
	 * @covers ::tags
	 * @preserveGlobalState disabled
	 */
	public function test_tags() {
		$terms = self::factory()->term->create_many( 15, array( 'taxonomy' => 'category' ) );
		$post  = self::factory()->post->create();
		wp_set_post_terms( $post, $terms, 'category' );

		$atts   = array( 'taxonomy' => 'category' );
		$actual = WPF_Shortcode::tags( $atts );

		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/category\/term-.+\/">/', $actual );
	}
}
