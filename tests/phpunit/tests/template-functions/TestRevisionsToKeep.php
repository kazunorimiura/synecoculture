<?php

/**
 * WPF_Template_Functions::revisions_to_keep のユニットテスト。
 *
 * @group template_functions
 * @covers WPF_Template_Functions
 * @coversDefaultClass WPF_Template_Functions
 */
class TestRevisionsToKeep extends WPF_UnitTestCase {
	public static $wpf_template_functions;

	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		parent::wpSetUpBeforeClass( $factory );

		self::$wpf_template_functions = new WPF_Template_Functions();

		add_filter( 'wp_revisions_to_keep', array( self::$wpf_template_functions, 'revisions_to_keep' ), 10, 2 );
	}

	public static function wpTearDownAfterClass() {
		remove_filter( 'wp_revisions_to_keep', array( self::$wpf_template_functions, 'revisions_to_keep' ), 10, 2 );
	}

	/**
	 * @covers ::revisions_to_keep
	 * @preserveGlobalState disabled
	 */
	public function test_revisions_to_keep_with_max_number() {
		$post_id = self::factory()->post->create(
			array(
				'post_type' => 'post',
			)
		);
		wp_update_post(
			array(
				'post_content' => 'This content is much better',
				'ID'           => $post_id,
			)
		);
		wp_update_post(
			array(
				'post_content' => 'This content is even better',
				'ID'           => $post_id,
			)
		);
		wp_update_post(
			array(
				'post_content' => 'This content is more better',
				'ID'           => $post_id,
			)
		);
		wp_update_post(
			array(
				'post_content' => 'This content is more more better',
				'ID'           => $post_id,
			)
		);

		// リビジョンは ::revisions_to_keep によって3つに抑えられる。
		$this->assertCount( 3, wp_get_post_revisions( $post_id ) );
	}
}
