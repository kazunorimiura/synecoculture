<?php

/**
 * WPF_Template_Functions::set_posts_per_page_on_mobile のユニットテスト。
 *
 * @group template_tags
 * @covers WPF_Template_Functions
 * @coversDefaultClass WPF_Template_Functions
 */
class TestSetPostsPerPageOnMobile extends WPF_UnitTestCase {

	/**
	 * @covers ::set_posts_per_page_on_mobile
	 * @preserveGlobalState disabled
	 */
	public function test_set_posts_per_page_on_mobile() {
		$post_id = self::factory()->post->create_many( 10 );

		$this->go_to( get_post_type_archive_link( 'post' ) );

		global $wp_query;

		$_SERVER['HTTP_USER_AGENT'] = 'Mobile';

		update_option( 'posts_per_page_on_mobile', 3 );

		WPF_Template_Functions::set_posts_per_page_on_mobile( $wp_query );

		$this->assertSame(
			3,
			$wp_query->query_vars['posts_per_page']
		);
	}

	/**
	 * @covers ::set_posts_per_page_on_mobile
	 * @preserveGlobalState disabled
	 */
	public function test_set_posts_per_page_on_mobile_with_unset_option() {
		$post_id = self::factory()->post->create_many( 10 );

		$this->go_to( get_post_type_archive_link( 'post' ) );

		global $wp_query;

		$_SERVER['HTTP_USER_AGENT'] = 'Mobile';

		update_option( 'posts_per_page_on_mobile', false );

		WPF_Template_Functions::set_posts_per_page_on_mobile( $wp_query );

		$this->assertSame(
			5,
			$wp_query->query_vars['posts_per_page']
		);
	}

	/**
	 * @covers ::set_posts_per_page_on_mobile
	 * @preserveGlobalState disabled
	 */
	public function test_set_posts_per_page_on_mobile_with_desktop() {
		$post_id = self::factory()->post->create_many( 10 );

		$this->go_to( get_post_type_archive_link( 'post' ) );

		global $wp_query;

		unset( $_SERVER['HTTP_USER_AGENT'] );

		update_option( 'posts_per_page_on_mobile', 3 );

		WPF_Template_Functions::set_posts_per_page_on_mobile( $wp_query );

		$this->assertSame(
			5,
			$wp_query->query_vars['posts_per_page']
		);
	}

	/**
	 * @covers ::set_posts_per_page_on_mobile
	 * @preserveGlobalState disabled
	 */
	public function test_set_posts_per_page_on_mobile_with_no_main_query() {
		$query = new WP_Query();

		$this->assertSame(
			null,
			WPF_Template_Functions::set_posts_per_page_on_mobile( $query )
		);
	}

	/**
	 * @covers ::set_posts_per_page_on_mobile
	 * @preserveGlobalState disabled
	 */
	public function test_set_posts_per_page_on_mobile_with_admin() {
		global $wp_query;

		set_current_screen( 'dashboard' );

		$this->assertSame(
			null,
			WPF_Template_Functions::set_posts_per_page_on_mobile( $wp_query )
		);
	}
}
