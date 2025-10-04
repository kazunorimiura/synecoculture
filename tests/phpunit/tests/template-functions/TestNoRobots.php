<?php

/**
 * WPF_Template_Functions::no_robots のユニットテスト。
 *
 * @group template_functions
 * @covers WPF_Template_Functions
 * @coversDefaultClass WPF_Template_Functions
 */
class TestNoRobots extends WPF_UnitTestCase {

	/**
	 * 検索結果ページ
	 *
	 * @covers ::no_robots
	 * @preserveGlobalState disabled
	 */
	public function test_no_robots_with_search_result() {
		$post_id = self::factory()->post->create(
			array(
				'post_type'  => 'post',
				'post_title' => 'Foo',
			)
		);

		$this->go_to( home_url( '/?s=Foo' ) );

		$this->assertSame(
			array(
				'noindex' => true,
				'follow'  => true,
			),
			WPF_Template_Functions::no_robots( array() )
		);
	}

	/**
	 * 投稿アーカイブ
	 *
	 * @covers ::no_robots
	 * @preserveGlobalState disabled
	 */
	public function test_no_robots_with_post_archive() {
		self::factory()->post->create( array( 'post_type' => 'post' ) );

		$this->go_to( get_post_type_archive_link( 'post' ) );

		$this->assertSame(
			array(),
			WPF_Template_Functions::no_robots( array() )
		);
	}

	/**
	 * CPT投稿アーカイブ
	 *
	 * @covers ::no_robots
	 * @preserveGlobalState disabled
	 */
	public function test_no_robots_with_cpt_post_archive() {
		register_post_type(
			self::$post_type,
			array(
				'public'      => true,
				'has_archive' => true,
			)
		);

		self::factory()->post->create( array( 'post_type' => self::$post_type ) );

		$this->go_to( get_post_type_archive_link( self::$post_type ) );

		$post_type_object = get_post_type_object( self::$post_type );

		$this->assertSame(
			array(
				'noindex' => true,
				'follow'  => true,
			),
			WPF_Template_Functions::no_robots( array() )
		);
	}

	/**
	 * 日付アーカイブ
	 *
	 * @covers ::no_robots
	 * @preserveGlobalState disabled
	 */
	public function test_no_robots_with_date_archive() {
		self::factory()->post->create(
			array(
				'post_type' => 'post',
				'post_date' => '2012-12-12',
			)
		);

		$this->go_to( home_url( user_trailingslashit( '/2012/12/12' ) ) );

		$this->assertSame(
			array(
				'noindex' => true,
				'follow'  => true,
			),
			WPF_Template_Functions::no_robots( array() )
		);
	}

	/**
	 * 404エラー
	 *
	 * @covers ::no_robots
	 * @preserveGlobalState disabled
	 */
	public function test_no_robots_with_404() {
		$this->go_to( home_url( user_trailingslashit( '/blahblahblah' ) ) );

		$this->assertSame(
			array(
				'noindex' => true,
				'follow'  => true,
			),
			WPF_Template_Functions::no_robots( array() )
		);
	}

	/**
	 * フロントページ
	 *
	 * @covers ::no_robots
	 * @preserveGlobalState disabled
	 */
	public function test_no_robots_with_front_page() {
		self::factory()->post->create( array( 'post_type' => 'post' ) );

		$this->go_to( home_url() );

		$this->assertSame(
			array(),
			WPF_Template_Functions::no_robots( array() )
		);
	}

	/**
	 * 個別投稿ページ
	 *
	 * @covers ::no_robots
	 * @preserveGlobalState disabled
	 */
	public function test_no_robots_with_post() {
		$post_id = self::factory()->post->create(
			array(
				'post_type' => 'post',
			)
		);

		$this->go_to( get_permalink( $post_id ) );

		$this->assertSame(
			array(),
			WPF_Template_Functions::no_robots( array() )
		);
	}

	/**
	 * CPT個別投稿ページ
	 *
	 * @covers ::no_robots
	 * @preserveGlobalState disabled
	 */
	public function test_no_robots_with_cpt_post() {
		register_post_type( self::$post_type, array( 'public' => true ) );

		$post_id = self::factory()->post->create(
			array(
				'post_type' => self::$post_type,
			)
		);

		$this->go_to( get_permalink( $post_id ) );

		$this->assertSame(
			array(),
			WPF_Template_Functions::no_robots( array() )
		);
	}

	/**
	 * 固定ページ
	 *
	 * @covers ::no_robots
	 * @preserveGlobalState disabled
	 */
	public function test_no_robots_with_page() {
		$post_id = self::factory()->post->create(
			array(
				'post_type' => 'page',
			)
		);

		$this->go_to( get_permalink( $post_id ) );

		$this->assertSame(
			array(),
			WPF_Template_Functions::no_robots( array() )
		);
	}

	/**
	 * カテゴリアーカイブ
	 *
	 * @covers ::no_robots
	 * @preserveGlobalState disabled
	 */
	public function test_no_robots_with_category_archive() {
		$cat_id  = self::factory()->category->create();
		$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );
		wp_set_post_categories( $post_id, array( $cat_id ) );

		$this->go_to( get_category_link( $cat_id ) );

		$this->assertSame(
			array(),
			WPF_Template_Functions::no_robots( array() )
		);
	}

	/**
	 * タグアーカイブ
	 *
	 * @covers ::no_robots
	 * @preserveGlobalState disabled
	 */
	public function test_no_robots_with_tag_archive() {
		$tag_id  = self::factory()->tag->create();
		$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );
		wp_set_post_tags( $post_id, array( $tag_id ) );

		$this->go_to( get_tag_link( $tag_id ) );

		$this->assertSame(
			array(),
			WPF_Template_Functions::no_robots( array() )
		);
	}

	/**
	 * ctaxアーカイブ
	 *
	 * @covers ::no_robots
	 * @preserveGlobalState disabled
	 */
	public function test_no_robots_with_ctax_archive() {
		register_taxonomy( self::$taxonomy, self::$post_type );

		$term_id = self::factory()->term->create( array( 'taxonomy' => self::$taxonomy ) );
		$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );
		wp_set_post_terms( $post_id, array( $term_id ), self::$taxonomy );

		$this->go_to( get_term_link( $term_id ) );

		$this->assertSame(
			array(),
			WPF_Template_Functions::no_robots( array() )
		);
	}

	/**
	 * 投稿用ページ
	 *
	 * @covers ::no_robots
	 * @preserveGlobalState disabled
	 */
	public function test_no_robots_with_page_for_posts() {
		$page_id = self::factory()->post->create(
			array(
				'post_type' => 'page',
			)
		);
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $page_id );

		$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );

		$this->go_to( get_permalink( get_option( 'page_for_posts' ) ) );

		$this->assertSame(
			array(),
			WPF_Template_Functions::no_robots( array() )
		);
	}

	/**
	 * CPT投稿用ページ
	 *
	 * @covers ::no_robots
	 * @preserveGlobalState disabled
	 */
	public function test_no_robots_with_page_for_cpt_posts() {
		register_post_type( self::$post_type, array( 'public' => true ) );

		$page_id = self::factory()->post->create(
			array(
				'post_type' => 'page',
				'post_name' => self::$post_type,
			)
		);

		$this->go_to( get_permalink( $page_id ) );

		$this->assertSame(
			array(),
			WPF_Template_Functions::no_robots( array() )
		);
	}
}
