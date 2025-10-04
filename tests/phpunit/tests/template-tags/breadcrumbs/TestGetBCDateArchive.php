<?php

/**
 * WPF_Template_Tags::get_the_breadcrumbs のユニットテスト。
 *
 * @group template_tags
 * @covers WPF_Template_Tags
 * @coversDefaultClass WPF_Template_Tags
 */
class TestGetBCDateArchive extends WPF_UnitTestCase {

	/**
	 * 年アーカイブ
	 *
	 * 条件:
	 * - デフォルト投稿タイプ => `Posts` がパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_year_archive() {
		$post_ids = self::factory()->post->create_many(
			10,
			array(
				'post_type' => 'post',
				'post_date' => '2012-12-12',
			)
		);

		$this->go_to( home_url( user_trailingslashit( '/2012' ) ) );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Posts',
				'link'  => home_url(),
				'layer' => 'post_type',
			),
			array(
				'text'  => '2012年',
				'link'  => home_url( user_trailingslashit( '/2012' ) ),
				'layer' => 'current_date',
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

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( home_url( '/?m=2012' ) );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Posts',
				'link'  => home_url(),
				'layer' => 'post_type',
			),
			array(
				'text'  => '2012年',
				'link'  => home_url( '/?m=2012' ),
				'layer' => 'current_date',
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

	/**
	 * CPT年アーカイブ
	 *
	 * 条件:
	 * - CPT => CPT名がパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_cpt_year_archive() {
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

		$post_ids = self::factory()->post->create_many(
			10,
			array(
				'post_type' => self::$post_type,
				'post_date' => '2012-12-12',
			)
		);

		$this->go_to( home_url( user_trailingslashit( '/' . self::$post_type . '/date/2012' ) ) );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Foo',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type ) ),
				'layer' => 'post_type',
			),
			array(
				'text'  => '2012年',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type . '/date/2012' ) ),
				'layer' => 'current_date',
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

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( home_url( '/?m=2012&post_type=' . self::$post_type ) );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Foo',
				'link'  => home_url( '/?post_type=' . self::$post_type ),
				'layer' => 'post_type',
			),
			array(
				'text'  => '2012年',
				'link'  => home_url( '?m=2012&post_type=' . self::$post_type ),
				'layer' => 'current_date',
			),
		);

		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);

		$this->go_to( home_url( '/?m=2012&post_type=' . self::$post_type . '&paged=2' ) );

		// 2ページ目以降も変わらないことを確認。
		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);
	}

	/**
	 * 年月アーカイブ
	 *
	 * 条件:
	 * - デフォルト投稿タイプ => `Posts` がパンくずに追加されるはず。
	 * - 年を親に持っている => 年パーマリンクがパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_month_archive() {
		$post_ids = self::factory()->post->create_many(
			10,
			array(
				'post_type' => 'post',
				'post_date' => '2012-12-12',
			)
		);

		$this->go_to( home_url( user_trailingslashit( '/2012/12' ) ) );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Posts',
				'link'  => home_url(),
				'layer' => 'post_type',
			),
			array(
				'text'  => '2012年',
				'link'  => home_url( user_trailingslashit( '/2012' ) ),
				'layer' => 'date',
			),
			array(
				'text'  => '2012年12月',
				'link'  => home_url( user_trailingslashit( '/2012/12' ) ),
				'layer' => 'current_date',
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

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( home_url( '/?m=201212' ) );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Posts',
				'link'  => home_url(),
				'layer' => 'post_type',
			),
			array(
				'text'  => '2012年',
				'link'  => home_url( '/?m=2012' ),
				'layer' => 'date',
			),
			array(
				'text'  => '2012年12月',
				'link'  => home_url( '/?m=201212' ),
				'layer' => 'current_date',
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

	/**
	 * CPT年月アーカイブ
	 *
	 * 条件:
	 * - CPT => CPT名がパンくずに追加されるはず。
	 * - 年を親に持っている => 年パーマリンクがパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_cpt_month_archive() {
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

		$post_ids = self::factory()->post->create_many(
			10,
			array(
				'post_type' => self::$post_type,
				'post_date' => '2012-12-12',
			)
		);

		$this->go_to( home_url( user_trailingslashit( '/' . self::$post_type . '/date/2012/12' ) ) );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Foo',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type ) ),
				'layer' => 'post_type',
			),
			array(
				'text'  => '2012年',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type . '/date/2012' ) ),
				'layer' => 'date',
			),
			array(
				'text'  => '2012年12月',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type . '/date/2012/12' ) ),
				'layer' => 'current_date',
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

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( home_url( '/?m=201212&post_type=' . self::$post_type ) );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Foo',
				'link'  => home_url( '/?post_type=' . self::$post_type ),
				'layer' => 'post_type',
			),
			array(
				'text'  => '2012年',
				'link'  => home_url( '/?m=2012&post_type=' . self::$post_type ),
				'layer' => 'date',
			),
			array(
				'text'  => '2012年12月',
				'link'  => home_url( '/?m=201212&post_type=' . self::$post_type ),
				'layer' => 'current_date',
			),
		);

		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);

		$this->go_to( home_url( '/?m=201212&post_type=' . self::$post_type . '&paged=2' ) );

		// 2ページ目以降も変わらないことを確認。
		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);
	}

	/**
	 * 年月日アーカイブ
	 *
	 * 条件:
	 * - デフォルト投稿タイプ => `Posts` がパンくずに追加されるはず。
	 * - 年月を親に持っている => 年月パーマリンクがパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_day_archive() {
		$post_ids = self::factory()->post->create_many(
			10,
			array(
				'post_type' => 'post',
				'post_date' => '2012-02-12',
			)
		);

		$this->go_to( home_url( user_trailingslashit( '/2012/02/12' ) ) );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Posts',
				'link'  => home_url(),
				'layer' => 'post_type',
			),
			array(
				'text'  => '2012年',
				'link'  => home_url( user_trailingslashit( '/2012' ) ),
				'layer' => 'date',
			),
			array(
				'text'  => '2012年2月',
				'link'  => home_url( user_trailingslashit( '/2012/02' ) ),
				'layer' => 'date',
			),
			array(
				'text'  => '2012年2月12日',
				'link'  => home_url( user_trailingslashit( '/2012/02/12' ) ),
				'layer' => 'current_date',
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

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( home_url( '/?m=20120212' ) );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Posts',
				'link'  => home_url(),
				'layer' => 'post_type',
			),
			array(
				'text'  => '2012年',
				'link'  => home_url( '/?m=2012' ),
				'layer' => 'date',
			),
			array(
				'text'  => '2012年2月',
				'link'  => home_url( '/?m=201202' ),
				'layer' => 'date',
			),
			array(
				'text'  => '2012年2月12日',
				'link'  => home_url( '/?m=20120212' ),
				'layer' => 'current_date',
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

	/**
	 * CPT年月日アーカイブ
	 *
	 * 条件:
	 * - CPT => CPT名がパンくずに追加されるはず。
	 * - 年月を親に持っている => 年月パーマリンクがパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_cpt_day_archive() {
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

		$post_ids = self::factory()->post->create_many(
			10,
			array(
				'post_type' => self::$post_type,
				'post_date' => '2012-12-12',
			)
		);

		$this->go_to( home_url( user_trailingslashit( '/' . self::$post_type . '/date/2012/12/12' ) ) );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Foo',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type ) ),
				'layer' => 'post_type',
			),
			array(
				'text'  => '2012年',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type . '/date/2012' ) ),
				'layer' => 'date',
			),
			array(
				'text'  => '2012年12月',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type . '/date/2012/12' ) ),
				'layer' => 'date',
			),
			array(
				'text'  => '2012年12月12日',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type . '/date/2012/12/12' ) ),
				'layer' => 'current_date',
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

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( home_url( '/?m=20121212&post_type=' . self::$post_type ) );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Foo',
				'link'  => home_url( '/?post_type=' . self::$post_type ),
				'layer' => 'post_type',
			),
			array(
				'text'  => '2012年',
				'link'  => home_url( '/?m=2012&post_type=' . self::$post_type ),
				'layer' => 'date',
			),
			array(
				'text'  => '2012年12月',
				'link'  => home_url( '/?m=201212&post_type=' . self::$post_type ),
				'layer' => 'date',
			),
			array(
				'text'  => '2012年12月12日',
				'link'  => home_url( '/?m=20121212&post_type=' . self::$post_type ),
				'layer' => 'current_date',
			),
		);

		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);

		$this->go_to( home_url( '/?m=20121212&post_type=' . self::$post_type . '&paged=2' ) );

		// 2ページ目以降も変わらないことを確認。
		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);
	}
}
