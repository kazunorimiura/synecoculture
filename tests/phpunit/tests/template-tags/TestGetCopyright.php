<?php

/**
 * WPF_Template_Tags::get_copyright のユニットテスト。
 *
 * @group template_tags
 * @covers WPF_Template_Tags
 * @coversDefaultClass WPF_Template_Tags
 */
class TestGetCopyright extends WPF_UnitTestCase {

	/**
	 * @covers ::get_copyright
	 * @preserveGlobalState disabled
	 */
	public function test_get_copyright() {
		$post_id = self::factory()->post->create(
			array(
				'post_type' => 'post',
				'post_date' => '2012-12-12',
			)
		);

		$this->assertSame(
			'Copyright &copy;2012 ' . WP_TESTS_TITLE,
			WPF_Template_Tags::get_copyright()
		);
	}

	/**
	 * @covers ::get_copyright
	 * @preserveGlobalState disabled
	 */
	public function test_get_copyright_with_site_owner_option() {
		$post_id = self::factory()->post->create(
			array(
				'post_type' => 'post',
				'post_date' => '2012-12-12',
			)
		);

		update_option( 'site_owner', 'John Doe' );

		$this->assertSame(
			'Copyright &copy;2012 John Doe',
			WPF_Template_Tags::get_copyright()
		);
	}
}
