<?php

/**
 * WPF_Template_Functions::remove_archive_title_prefix のユニットテスト。
 *
 * @group template_functions
 * @covers WPF_Template_Functions
 * @coversDefaultClass WPF_Template_Functions
 */
class TestRemoveArchiveTitlePrefix extends WPF_UnitTestCase {
	private static $wpf_template_functions;

	public function set_up() {
		parent::set_up();

		self::$wpf_template_functions = new WPF_Template_Functions();

		add_filter( 'get_the_archive_title_prefix', array( self::$wpf_template_functions, 'remove_archive_title_prefix' ) );
	}

	public function tear_down() {
		remove_filter( 'get_the_archive_title_prefix', array( self::$wpf_template_functions, 'remove_archive_title_prefix' ) );

		parent::tear_down();
	}

	/**
	 * @covers ::remove_archive_title_prefix
	 * @preserveGlobalState disabled
	 */
	public function test_remove_archive_title_prefix_with_year_archive() {
		self::factory()->post->create( array( 'post_date' => '2012-12-12' ) );

		$this->go_to( get_year_link( '2012' ) );

		$this->assertSame(
			'2012',
			get_the_archive_title()
		);
	}

	/**
	 * @covers ::remove_archive_title_prefix
	 * @preserveGlobalState disabled
	 */
	public function test_remove_archive_title_prefix_with_category_archive() {
		$cat  = self::factory()->category->create( array( 'name' => 'foo' ) );
		$post = self::factory()->post->create();
		wp_set_post_categories( $post, array( $cat ) );

		$this->go_to( get_category_link( $cat ) );

		$this->assertSame(
			'foo',
			get_the_archive_title()
		);
	}

	/**
	 * @covers ::remove_archive_title_prefix
	 * @preserveGlobalState disabled
	 */
	public function test_remove_archive_title_prefix_with_tag_archive() {
		$tag  = self::factory()->tag->create( array( 'name' => 'foo' ) );
		$post = self::factory()->post->create();
		wp_set_post_tags( $post, array( $tag ) );

		$this->go_to( get_tag_link( $tag ) );

		$this->assertSame(
			'foo',
			get_the_archive_title()
		);
	}
}
