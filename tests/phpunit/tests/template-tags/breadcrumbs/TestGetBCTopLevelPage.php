<?php

/**
 * WPF_Template_Tags::get_the_breadcrumbs のユニットテスト。
 *
 * @group template_tags
 * @covers WPF_Template_Tags
 * @coversDefaultClass WPF_Template_Tags
 */
class TestGetBCTopLevelPage extends WPF_UnitTestCase {

	/**
	 * ホームページの表示: 最新の投稿
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_latest_posts() {
		update_option( 'show_on_front', 'post' );
		$post_ids = self::factory()->post->create_many( 10, array( 'post_type' => 'post' ) );

		$this->go_to( home_url() );

		$expected = null;

		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);

		$this->go_to( next_posts( 0, false ) );

		// 2ページ目以降も変わらないことを確認
		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);

		// ダーティパーマリンクの場合
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( home_url() );

		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);

		$this->go_to( next_posts( 0, false ) );

		// 2ページ目以降も変わらないことを確認
		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);
	}

	/**
	 * ホームページの表示: 固定ページ
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_page_on_front() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Foo',
			)
		);
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $page_id );

		$post_ids = self::factory()->post->create_many( 10, array( 'post_type' => 'post' ) );

		$this->go_to( home_url() );

		$expected = null;

		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);

		$this->go_to( next_posts( 0, false ) );

		// 2ページ目以降も変わらないことを確認
		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);

		// ダーティパーマリンクの場合
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( home_url() );

		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);

		$this->go_to( next_posts( 0, false ) );

		// 2ページ目以降も変わらないことを確認。
		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);
	}

	/**
	 * 投稿用ページ
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_page_for_posts() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Foo',
			)
		);
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $page_id );

		$post_ids = self::factory()->post->create_many( 10, array( 'post_type' => 'post' ) );

		$this->go_to( get_permalink( get_option( 'page_for_posts' ) ) );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Foo',
				'link'  => home_url() . '/foo/',
				'layer' => 'post_type',
			),
		);

		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);

		$this->go_to( next_posts( 0, false ) );

		// 2ページ目以降も変わらないことを確認
		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);

		// ダーティパーマリンクの場合
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( get_permalink( get_option( 'page_for_posts' ) ) );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Foo',
				'link'  => home_url() . '/?page_id=' . $page_id,
				'layer' => 'post_type',
			),
		);

		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);

		$this->go_to( next_posts( 0, false ) );

		// 2ページ目以降も変わらないことを確認
		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);
	}

	/**
	 * CPT投稿アーカイブ
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_cpt_post_archive() {
		register_post_type(
			self::$post_type,
			array(
				'labels'      => array(
					'name' => 'Foo',
				),
				'public'      => true,
				'has_archive' => true,
			)
		);

		$post_ids = self::factory()->post->create_many( 10, array( 'post_type' => self::$post_type ) );

		$this->go_to( get_post_type_archive_link( self::$post_type ) );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Foo',
				'link'  => home_url() . '/' . self::$post_type . '/',
				'layer' => 'post_type',
			),
		);

		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);

		$this->go_to( next_posts( 0, false ) );

		// 2ページ目以降も変わらないことを確認
		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);

		// ダーティパーマリンクの場合
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( get_post_type_archive_link( self::$post_type ) );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Foo',
				'link'  => home_url() . '/?post_type=' . self::$post_type,
				'layer' => 'post_type',
			),
		);

		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);

		$this->go_to( next_posts( 0, false ) );

		// 2ページ目以降も変わらないことを確認
		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);
	}

	/**
	 * CPT投稿用ページ
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_page_for_cpt_posts() {
		register_post_type(
			self::$post_type,
			array(
				'labels'      => array(
					'name' => 'Foo',
				),
				'public'      => true,
				'has_archive' => true,
			)
		);

		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_name'  => self::$post_type,
				'post_title' => 'Bar',
			)
		);

		$post_ids = self::factory()->post->create_many( 10, array( 'post_type' => self::$post_type ) );

		$this->go_to( get_post_type_archive_link( self::$post_type ) );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Bar',
				'link'  => home_url() . '/' . self::$post_type . '/',
				'layer' => 'post_type',
			),
		);

		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);

		$this->go_to( next_posts( 0, false ) );

		// 2ページ目以降も変わらないことを確認
		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);

		// ダーティパーマリンクの場合
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( get_post_type_archive_link( self::$post_type ) );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Bar',
				'link'  => home_url() . '/?page_id=' . $page_id,
				'layer' => 'post_type',
			),
		);

		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);

		$this->go_to( next_posts( 0, false ) );

		// 2ページ目以降も変わらないことを確認。
		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);
	}
}
