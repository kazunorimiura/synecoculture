<?php

/**
 * WPF_Template_Tags::get_the_back_link のユニットテスト。
 *
 * @group template_tags
 * @covers WPF_Template_Tags
 * @coversDefaultClass WPF_Template_Tags
 */
class TestGetTheBackLink extends WPF_UnitTestCase {

	public function set_up() {
		parent::set_up();

		$labels       = get_post_type_object( 'post' )->labels;
		$labels->name = 'Posts';
	}

	public function tear_down() {
		$labels       = get_post_type_object( 'post' )->labels;
		$labels->name = 'Posts';

		parent::tear_down();
	}

	/**
	 * @covers ::get_the_back_link
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_back_link_with_single() {
		$post_ids = self::factory()->post->create_many( 10 );

		$this->go_to( get_permalink( $post_ids[0] ) );

		$actual = WPF_Template_Tags::get_the_back_link();

		$this->assertMatchesRegularExpression( '/<a class="button:secondary" href="http:\/\/example.org".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<svg class="icon" width="24" height="24" aria-hidden="true" focusable="false" viewBox="0 0 24 24" xmlns=".*">.*<\/svg>/', $actual );
		$this->assertMatchesRegularExpression( '/<span>Postsに戻る<\/span>/', $actual );
	}

	/**
	 * @covers ::get_the_back_link
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_back_link_with_single_and_change_label() {
		$labels       = get_post_type_object( 'post' )->labels;
		$labels->name = 'ニュース';

		$post_ids = self::factory()->post->create_many( 10 );

		$this->go_to( get_permalink( $post_ids[0] ) );

		$actual = WPF_Template_Tags::get_the_back_link();

		$this->assertMatchesRegularExpression( '/<a class="button:secondary" href="http:\/\/example.org".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<svg class="icon" width="24" height="24" aria-hidden="true" focusable="false" viewBox="0 0 24 24" xmlns=".*">.*<\/svg>/', $actual );
		$this->assertMatchesRegularExpression( '/<span>ニュースに戻る<\/span>/', $actual );
	}

	/**
	 * @covers ::get_the_back_link
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_back_link_with_page_for_posts() {
		$page_for_posts = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Foo',
			)
		);
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $page_for_posts );

		$post_id = self::factory()->post->create();

		$this->go_to( get_permalink( $post_id ) );

		$actual = WPF_Template_Tags::get_the_back_link();

		$this->assertMatchesRegularExpression( '/<a class="button:secondary" href="http:\/\/example.org\/foo\/".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<svg class="icon" width="24" height="24" aria-hidden="true" focusable="false" viewBox="0 0 24 24" xmlns=".*">.*<\/svg>/', $actual );
		$this->assertMatchesRegularExpression( '/<span>Fooに戻る<\/span>/', $actual );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$post_id = self::factory()->post->create();

		$this->go_to( get_permalink( $post_id ) );

		$actual = WPF_Template_Tags::get_the_back_link();

		$this->assertMatchesRegularExpression( '/<a class="button:secondary" href="http:\/\/example.org\/\?page_id=' . $page_for_posts . '".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<svg class="icon" width="24" height="24" aria-hidden="true" focusable="false" viewBox="0 0 24 24" xmlns=".*">.*<\/svg>/', $actual );
		$this->assertMatchesRegularExpression( '/<span>Fooに戻る<\/span>/', $actual );
	}

	/**
	 * @covers ::get_the_back_link
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_back_link_with_attachment_page() {
		$post_id = self::factory()->post->create(
			array(
				'post_title' => 'foo',
				'post_date'  => '2012-12-12',
			)
		);

		$attachment_id = self::factory()->attachment->create_object( 'image.jpg', $post_id, array( 'post_mime_type' => 'image/jpeg' ) );

		$this->go_to( get_attachment_link( $attachment_id ) );

		$actual = WPF_Template_Tags::get_the_back_link();

		$this->assertMatchesRegularExpression( '/<a class="button:secondary" href="http:\/\/example.org\/2012\/12\/12\/foo\/".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<svg class="icon" width="24" height="24" aria-hidden="true" focusable="false" viewBox="0 0 24 24" xmlns=".*">.*<\/svg>/', $actual );
		$this->assertMatchesRegularExpression( '/<span>記事に戻る<\/span>/', $actual );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( get_attachment_link( $attachment_id ) );

		$actual = WPF_Template_Tags::get_the_back_link();

		$this->assertMatchesRegularExpression( '/<a class="button:secondary" href="http:\/\/example.org\/\?p=' . $post_id . '".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<svg class="icon" width="24" height="24" aria-hidden="true" focusable="false" viewBox="0 0 24 24" xmlns=".*">.*<\/svg>/', $actual );
		$this->assertMatchesRegularExpression( '/<span>記事に戻る<\/span>/', $actual );
	}
}
