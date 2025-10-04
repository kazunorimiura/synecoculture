<?php

/**
 * WPF_Template_Tags::get_the_breadcrumbs のユニットテスト。
 *
 * @group template_tags
 * @covers WPF_Template_Tags
 * @coversDefaultClass WPF_Template_Tags
 */
class TestGetBCPage extends WPF_UnitTestCase {

	/**
	 * 固定ページ
	 *
	 * 条件:
	 * - 親ページなし => nullが返されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_page() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Foo',
			)
		);

		$post_link = get_permalink( $page_id );
		$this->go_to( $post_link );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Foo',
				'link'  => home_url( user_trailingslashit( '/foo' ) ),
				'layer' => 'current_page',
			),
		);

		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);

		$next_page_link = user_trailingslashit( rtrim( $post_link, '/' ) . '/page/2' );
		$this->go_to( $next_page_link );

		// 2ページ目以降も変わらないことを確認。
		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$post_link = get_permalink( $page_id );
		$this->go_to( $post_link );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Foo',
				'link'  => home_url( '/?page_id=' . $page_id ),
				'layer' => 'current_page',
			),
		);

		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);

		$next_page_link = user_trailingslashit( rtrim( $post_link, '/' ) . '/page/2' );
		$this->go_to( $next_page_link );

		// 2ページ目以降も変わらないことを確認。
		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);
	}

	/**
	 * 固定ページ
	 *
	 * 条件:
	 * - 親ページあり => 親ページがパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_page_has_parent() {
		$parent_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Foo',
			)
		);
		$page_id   = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_title'  => 'Bar',
				'post_parent' => $parent_id,
			)
		);

		$post_link = get_permalink( $page_id );
		$this->go_to( $post_link );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Foo',
				'link'  => home_url( user_trailingslashit( '/foo' ) ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Bar',
				'link'  => home_url( user_trailingslashit( '/foo/bar' ) ),
				'layer' => 'current_page',
			),
		);

		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);

		$next_page_link = user_trailingslashit( rtrim( $post_link, '/' ) . '/page/2' );
		$this->go_to( $next_page_link );

		// 2ページ目以降も変わらないことを確認。
		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$post_link = get_permalink( $page_id );
		$this->go_to( $post_link );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Foo',
				'link'  => home_url( '/?page_id=' . $parent_id ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Bar',
				'link'  => home_url( '/?page_id=' . $page_id ),
				'layer' => 'current_page',
			),
		);

		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);

		$next_page_link = user_trailingslashit( rtrim( $post_link, '/' ) . '/page/2' );
		$this->go_to( $next_page_link );

		// 2ページ目以降も変わらないことを確認。
		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);
	}
}
