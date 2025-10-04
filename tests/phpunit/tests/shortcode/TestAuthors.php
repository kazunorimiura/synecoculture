<?php

/**
 * WPF_Shortcode::authors のユニットテスト。
 *
 * @group template_tags
 * @covers WPF_Shortcode
 * @coversDefaultClass WPF_Shortcode
 */
class TestAuthors extends WPF_UnitTestCase {

	/**
	 * @covers ::authors
	 * @preserveGlobalState disabled
	 */
	public function test_authors() {
		$users = self::factory()->user->create_many( 15, array( 'role' => 'editor' ) );
		foreach ( $users as $user ) {
			$post = self::factory()->post->create( array( 'post_author' => $user ) );
		}

		$atts   = array();
		$actual = WPF_Shortcode::authors( $atts );

		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org\/author\/user-.+\/"[^>][^>]*tabindex="-1"[^>]*>/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org\/author\/user-.+\/"[^>]*>/', $actual );
	}
}
