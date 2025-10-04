<?php

/**
 * WPF_Template_Tags::get_the_breadcrumbs のユニットテスト。
 *
 * @group template_tags
 * @covers WPF_Template_Tags
 * @coversDefaultClass WPF_Template_Tags
 */
class TestGetBCCPTPostWithPageForCPTPosts extends WPF_UnitTestCase {

	public function tear_down() {
		_unregister_taxonomy( self::$taxonomy . '_2' );

		parent::tear_down();
	}

	/**
	 * CPT個別投稿ページ
	 *
	 * 条件:
	 * - CPT投稿用ページ => CPT投稿用ページのタイトルがパンくずに追加されるはず。
	 * - 親投稿がある => 親投稿がパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_cpt_post_and_page_for_cpt_posts_and_has_parent_post() {
		register_post_type(
			self::$post_type,
			array(
				'labels'      => array(
					'name' => 'Foo',
				),
				'public'      => true,
				'has_archive' => true,
				'wpf_cptp'    => array(
					'permalink_structure' => '/%year%/%monthnum%/%day%/%postname%/',
				),
			)
		);

		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Bar',
				'post_name'  => self::$post_type,
			)
		);

		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id = self::factory()->post->create(
				array(
					'post_type'    => self::$post_type,
					'post_title'   => 'Post title ' . $i,
					'post_parent'  => 5 < $i ? $post_id : false,
					'post_date'    => '2012-12-12',
					'post_content' => 'Page 1 <!--nextpage--> Page 2',
				)
			);
		}

		$post_link = get_permalink( $post_id );
		$this->go_to( $post_link );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Bar',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type ) ),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Post title 5',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type . '/2012/12/12/post-title-5' ) ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 6',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type . '/2012/12/12/post-title-6' ) ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 7',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type . '/2012/12/12/post-title-7' ) ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type . '/2012/12/12/post-title-8' ) ),
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

		$post_link = get_permalink( $post_id );
		$this->go_to( $post_link );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Bar',
				'link'  => home_url( '/?page_id=' . $page_id ),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Post title 5',
				'link'  => home_url( '/?' . self::$post_type . '=post-title-5' ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 6',
				'link'  => home_url( '/?' . self::$post_type . '=post-title-6' ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 7',
				'link'  => home_url( '/?' . self::$post_type . '=post-title-7' ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( '/?' . self::$post_type . '=post-title-8' ),
				'layer' => 'current_page',
			),
		);

		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);

		$next_page_link = $post_link . '&page=2';
		$this->go_to( $next_page_link );

		// 2ページ目以降も変わらないことを確認。
		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);
	}

	/**
	 * CPT個別投稿ページ
	 *
	 * 条件:
	 * - CPT投稿用ページ => CPT投稿用ページのタイトルがパンくずに追加されるはず。
	 * - 単一タームを選択 => そのタームがパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_cpt_post_and_page_for_cpt_posts_and_single_term() {
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
				'post_title' => 'Bar',
				'post_name'  => self::$post_type,
			)
		);

		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id    = self::factory()->post->create(
				array(
					'post_type'    => self::$post_type,
					'post_title'   => 'Post title ' . $i,
					'post_date'    => '2012-12-12',
					'post_content' => 'Page 1 <!--nextpage--> Page 2',
				)
			);
			$post_ids[] = $post_id;
		}

		$post_link = get_permalink( $post_id );
		$this->go_to( $post_link );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Bar',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type ) ),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type . '/' . $post_ids[8] ) ),
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

		$post_link = get_permalink( $post_id );
		$this->go_to( $post_link );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Bar',
				'link'  => home_url( '/?page_id=' . $page_id ),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( '/?' . self::$post_type . '=post-title-8' ),
				'layer' => 'current_page',
			),
		);

		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);

		$next_page_link = $post_link . '&page=2';
		$this->go_to( $next_page_link );

		// 2ページ目以降も変わらないことを確認。
		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);
	}

	/**
	 * CPT個別投稿ページ
	 *
	 * 条件:
	 * - CPT投稿用ページ => CPT投稿用ページのタイトルがパンくずに追加されるはず。
	 * - 親を持つタームを選択 => 親も含めたタームがパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_cpt_post_and_page_for_cpt_posts_and_has_parent_term() {
		register_taxonomy(
			self::$taxonomy,
			self::$post_type,
			array(
				'public'       => true,
				'hierarchical' => true,
				'rewrite'      => array(
					'hierarchical' => true,
				),
			)
		);
		register_post_type(
			self::$post_type,
			array(
				'labels'      => array(
					'name' => 'Foo',
				),
				'public'      => true,
				'has_archive' => true,
				'taxonomies'  => array( self::$taxonomy ),
			)
		);

		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Bar',
				'post_name'  => self::$post_type,
			)
		);

		$term_id = 0;
		for ( $i = 0; $i < 4; $i ++ ) {
			$term_id = self::factory()->term->create(
				array(
					'taxonomy' => self::$taxonomy,
					'name'     => 'Baz' . $i,
					'parent'   => $term_id,
				)
			);
		}
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id    = self::factory()->post->create(
				array(
					'post_type'    => self::$post_type,
					'post_title'   => 'Post title ' . $i,
					'post_date'    => '2012-12-12',
					'post_content' => 'Page 1 <!--nextpage--> Page 2',
				)
			);
			$post_ids[] = $post_id;

			wp_set_post_terms( $post_id, array( $term_id ), self::$taxonomy );
		}

		$post_link = get_permalink( $post_id );
		$this->go_to( $post_link );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Bar',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type ) ),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Baz0',
				'link'  => home_url( user_trailingslashit( '/' . self::$taxonomy . '/baz0' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Baz1',
				'link'  => home_url( user_trailingslashit( '/' . self::$taxonomy . '/baz0/baz1' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Baz2',
				'link'  => home_url( user_trailingslashit( '/' . self::$taxonomy . '/baz0/baz1/baz2' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Baz3',
				'link'  => home_url( user_trailingslashit( '/' . self::$taxonomy . '/baz0/baz1/baz2/baz3' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type . '/' . $post_ids[8] ) ),
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

		$post_link = get_permalink( $post_id );
		$this->go_to( $post_link );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Bar',
				'link'  => home_url( '/?page_id=' . $page_id ),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Baz0',
				'link'  => home_url( '/?' . self::$taxonomy . '=baz0' ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Baz1',
				'link'  => home_url( '/?' . self::$taxonomy . '=baz1' ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Baz2',
				'link'  => home_url( '/?' . self::$taxonomy . '=baz2' ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Baz3',
				'link'  => home_url( '/?' . self::$taxonomy . '=baz3' ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( '/?' . self::$post_type . '=post-title-8' ),
				'layer' => 'current_page',
			),
		);

		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);

		$next_page_link = $post_link . '&page=2';
		$this->go_to( $next_page_link );

		// 2ページ目以降も変わらないことを確認。
		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);
	}

	/**
	 * CPT個別投稿ページ
	 *
	 * 条件:
	 * - CPT投稿用ページ => CPT投稿用ページのタイトルがパンくずに追加されるはず。
	 * - 複数タームを選択 => 最も若いidのタームがメインタームとしてパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_cpt_post_and_page_for_cpt_posts_and_multiple_terms() {
		register_taxonomy(
			self::$taxonomy,
			self::$post_type,
			array(
				'public'       => true,
				'hierarchical' => true,
				'rewrite'      => array(
					'hierarchical' => true,
				),
			)
		);
		register_post_type(
			self::$post_type,
			array(
				'labels'      => array(
					'name' => 'Foo',
				),
				'public'      => true,
				'has_archive' => true,
				'taxonomies'  => array( self::$taxonomy ),
			)
		);

		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Bar',
				'post_name'  => self::$post_type,
			)
		);

		$term_id = 0;
		for ( $i = 0; $i < 4; $i ++ ) {
			$term_id    = self::factory()->term->create(
				array(
					'taxonomy' => self::$taxonomy,
					'name'     => 'Baz' . $i,
					'parent'   => $term_id,
				)
			);
			$term_ids[] = $term_id;
		}
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id    = self::factory()->post->create(
				array(
					'post_type'    => self::$post_type,
					'post_title'   => 'Post title ' . $i,
					'post_date'    => '2012-12-12',
					'post_content' => 'Page 1 <!--nextpage--> Page 2',
				)
			);
			$post_ids[] = $post_id;

			wp_set_post_terms( $post_id, $term_ids, self::$taxonomy );
		}

		$post_link = get_permalink( $post_id );
		$this->go_to( $post_link );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Bar',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type ) ),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Baz0',
				'link'  => home_url( user_trailingslashit( '/' . self::$taxonomy . '/baz0' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type . '/' . $post_ids[8] ) ),
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

		$post_link = get_permalink( $post_id );
		$this->go_to( $post_link );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Bar',
				'link'  => home_url( '/?page_id=' . $page_id ),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Baz0',
				'link'  => home_url( '/?' . self::$taxonomy . '=baz0' ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( '/?' . self::$post_type . '=post-title-8' ),
				'layer' => 'current_page',
			),
		);

		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);

		$next_page_link = $post_link . '&page=2';
		$this->go_to( $next_page_link );

		// 2ページ目以降も変わらないことを確認。
		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);
	}

	/**
	 * CPT個別投稿ページ
	 *
	 * 条件:
	 * - CPT投稿用ページ => CPT投稿用ページのタイトルがパンくずに追加されるはず。
	 * - 複数taxにまたがるタームを選択 => taxonomiesの先頭のctaxがメインtaxに選定されているはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_cpt_post_and_page_for_cpt_posts_and_multiple_taxs() {
		register_taxonomy(
			self::$taxonomy,
			self::$post_type,
			array(
				'public'       => true,
				'hierarchical' => true,
				'rewrite'      => array(
					'hierarchical' => true,
				),
			)
		);
		register_taxonomy(
			self::$taxonomy . '_2',
			self::$post_type,
			array(
				'public'       => true,
				'hierarchical' => true,
				'rewrite'      => array(
					'hierarchical' => true,
				),
			)
		);
		register_post_type(
			self::$post_type,
			array(
				'labels'      => array(
					'name' => 'Foo',
				),
				'public'      => true,
				'has_archive' => true,
				'taxonomies'  => array( self::$taxonomy, self::$taxonomy . '_2' ),
			)
		);

		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Bar',
				'post_name'  => self::$post_type,
			)
		);

		$term_id = 0;
		for ( $i = 0; $i < 4; $i ++ ) {
			$term_id = self::factory()->term->create(
				array(
					'taxonomy' => self::$taxonomy,
					'name'     => 'Baz' . $i,
					'parent'   => $term_id,
				)
			);
		}

		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id    = self::factory()->post->create(
				array(
					'post_type'    => self::$post_type,
					'post_title'   => 'Post title ' . $i,
					'post_date'    => '2012-12-12',
					'post_content' => 'Page 1 <!--nextpage--> Page 2',
				)
			);
			$post_ids[] = $post_id;

			wp_set_post_terms( $post_id, array( $term_id ), self::$taxonomy );
		}

		$term_id = 0;
		for ( $i = 0; $i < 4; $i ++ ) {
			$term_id = self::factory()->term->create(
				array(
					'taxonomy' => self::$taxonomy . '_2',
					'name'     => 'Qux' . $i,
					'parent'   => $term_id,
				)
			);
		}

		for ( $i = 0; $i < 9; $i ++ ) {
			wp_set_post_terms( $post_id, array( $term_id ), self::$taxonomy . '_2' );
		}

		$post_link = get_permalink( $post_id );
		$this->go_to( $post_link );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Bar',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type ) ),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Baz0',
				'link'  => home_url( user_trailingslashit( '/' . self::$taxonomy . '/baz0' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Baz1',
				'link'  => home_url( user_trailingslashit( '/' . self::$taxonomy . '/baz0/baz1' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Baz2',
				'link'  => home_url( user_trailingslashit( '/' . self::$taxonomy . '/baz0/baz1/baz2' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Baz3',
				'link'  => home_url( user_trailingslashit( '/' . self::$taxonomy . '/baz0/baz1/baz2/baz3' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type . '/' . $post_ids[8] ) ),
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

		$post_link = get_permalink( $post_id );
		$this->go_to( $post_link );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Bar',
				'link'  => home_url( '/?page_id=' . $page_id ),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Baz0',
				'link'  => home_url( '/?' . self::$taxonomy . '=baz0' ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Baz1',
				'link'  => home_url( '/?' . self::$taxonomy . '=baz1' ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Baz2',
				'link'  => home_url( '/?' . self::$taxonomy . '=baz2' ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Baz3',
				'link'  => home_url( '/?' . self::$taxonomy . '=baz3' ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( '/?' . self::$post_type . '=post-title-8' ),
				'layer' => 'current_page',
			),
		);

		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);

		$next_page_link = $post_link . '&page=2';
		$this->go_to( $next_page_link );

		// 2ページ目以降も変わらないことを確認。
		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);
	}

	/**
	 * CPT個別投稿ページ
	 *
	 * 条件:
	 * - CPT投稿用ページ => CPT投稿用ページのタイトルがパンくずに追加されるはず。
	 * - 単一タームを選択 => そのタームがパンくずに追加されるはず。
	 * - 親投稿がある => 親投稿がパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_cpt_post_and_page_for_cpt_posts_and_single_term_and_has_parent_post() {
		register_taxonomy(
			self::$taxonomy,
			self::$post_type,
			array(
				'public'       => true,
				'hierarchical' => true,
				'rewrite'      => array(
					'hierarchical' => true,
				),
			)
		);
		register_post_type(
			self::$post_type,
			array(
				'labels'      => array(
					'name' => 'Foo',
				),
				'public'      => true,
				'has_archive' => true,
				'taxonomies'  => array( self::$taxonomy ),
				'wpf_cptp'    => array(
					'permalink_structure' => '/%year%/%monthnum%/%day%/%postname%/',
				),
			)
		);

		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Bar',
				'post_name'  => self::$post_type,
			)
		);

		$term_id = self::factory()->term->create(
			array(
				'taxonomy' => self::$taxonomy,
				'name'     => 'Baz',
			)
		);
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id = self::factory()->post->create(
				array(
					'post_type'    => self::$post_type,
					'post_title'   => 'Post title ' . $i,
					'post_parent'  => 5 < $i ? $post_id : false,
					'post_date'    => '2012-12-12',
					'post_content' => 'Page 1 <!--nextpage--> Page 2',
				)
			);
			wp_set_post_terms( $post_id, array( $term_id ), self::$taxonomy );
		}

		$post_link = get_permalink( $post_id );
		$this->go_to( $post_link );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Bar',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type ) ),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Baz',
				'link'  => home_url( user_trailingslashit( '/' . self::$taxonomy . '/baz' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Post title 5',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type . '/2012/12/12/post-title-5' ) ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 6',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type . '/2012/12/12/post-title-6' ) ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 7',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type . '/2012/12/12/post-title-7' ) ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type . '/2012/12/12/post-title-8' ) ),
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

		$post_link = get_permalink( $post_id );
		$this->go_to( $post_link );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Bar',
				'link'  => home_url( '/?page_id=' . $page_id ),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Baz',
				'link'  => home_url( '/?' . self::$taxonomy . '=baz' ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Post title 5',
				'link'  => home_url( '/?' . self::$post_type . '=post-title-5' ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 6',
				'link'  => home_url( '/?' . self::$post_type . '=post-title-6' ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 7',
				'link'  => home_url( '/?' . self::$post_type . '=post-title-7' ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( '/?' . self::$post_type . '=post-title-8' ),
				'layer' => 'current_page',
			),
		);

		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);

		$next_page_link = $post_link . '&page=2';
		$this->go_to( $next_page_link );

		// 2ページ目以降も変わらないことを確認。
		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);
	}

	/**
	 * CPT個別投稿ページ
	 *
	 * 条件:
	 * - CPT投稿用ページ => CPT投稿用ページのタイトルがパンくずに追加されるはず。
	 * - 親を持つタームを選択 => 親も含めたタームがパンくずに追加されるはず。
	 * - 親投稿がある => 親投稿がパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_cpt_post_and_page_for_cpt_posts_and_has_parent_term_and_has_parent_post() {
		register_taxonomy(
			self::$taxonomy,
			self::$post_type,
			array(
				'public'       => true,
				'hierarchical' => true,
				'rewrite'      => array(
					'hierarchical' => true,
				),
			)
		);
		register_post_type(
			self::$post_type,
			array(
				'labels'      => array(
					'name' => 'Foo',
				),
				'public'      => true,
				'has_archive' => true,
				'taxonomies'  => array( self::$taxonomy ),
				'wpf_cptp'    => array(
					'permalink_structure' => '/%year%/%monthnum%/%day%/%postname%/',
				),
			)
		);

		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Bar',
				'post_name'  => self::$post_type,
			)
		);

		$term_id = 0;
		for ( $i = 0; $i < 4; $i ++ ) {
			$term_id = self::factory()->term->create(
				array(
					'taxonomy' => self::$taxonomy,
					'name'     => 'Baz' . $i,
					'parent'   => $term_id,
				)
			);
		}
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id = self::factory()->post->create(
				array(
					'post_type'    => self::$post_type,
					'post_title'   => 'Post title ' . $i,
					'post_parent'  => 5 < $i ? $post_id : false,
					'post_date'    => '2012-12-12',
					'post_content' => 'Page 1 <!--nextpage--> Page 2',
				)
			);
			wp_set_post_terms( $post_id, array( $term_id ), self::$taxonomy );
		}

		$post_link = get_permalink( $post_id );
		$this->go_to( $post_link );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Bar',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type ) ),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Baz0',
				'link'  => home_url( user_trailingslashit( '/' . self::$taxonomy . '/baz0' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Baz1',
				'link'  => home_url( user_trailingslashit( '/' . self::$taxonomy . '/baz0/baz1' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Baz2',
				'link'  => home_url( user_trailingslashit( '/' . self::$taxonomy . '/baz0/baz1/baz2' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Baz3',
				'link'  => home_url( user_trailingslashit( '/' . self::$taxonomy . '/baz0/baz1/baz2/baz3' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Post title 5',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type . '/2012/12/12/post-title-5' ) ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 6',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type . '/2012/12/12/post-title-6' ) ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 7',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type . '/2012/12/12/post-title-7' ) ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type . '/2012/12/12/post-title-8' ) ),
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

		$post_link = get_permalink( $post_id );
		$this->go_to( $post_link );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Bar',
				'link'  => home_url( '/?page_id=' . $page_id ),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Baz0',
				'link'  => home_url( '/?' . self::$taxonomy . '=baz0' ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Baz1',
				'link'  => home_url( '/?' . self::$taxonomy . '=baz1' ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Baz2',
				'link'  => home_url( '/?' . self::$taxonomy . '=baz2' ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Baz3',
				'link'  => home_url( '/?' . self::$taxonomy . '=baz3' ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Post title 5',
				'link'  => home_url( '/?' . self::$post_type . '=post-title-5' ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 6',
				'link'  => home_url( '/?' . self::$post_type . '=post-title-6' ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 7',
				'link'  => home_url( '/?' . self::$post_type . '=post-title-7' ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( '/?' . self::$post_type . '=post-title-8' ),
				'layer' => 'current_page',
			),
		);

		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);

		$next_page_link = $post_link . '&page=2';
		$this->go_to( $next_page_link );

		// 2ページ目以降も変わらないことを確認。
		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);
	}

	/**
	 * CPT個別投稿ページ
	 *
	 * 条件:
	 * - CPT投稿用ページ => CPT投稿用ページのタイトルがパンくずに追加されるはず。
	 * - 複数タームを選択 => 最も若いidのタームがメインタームとしてパンくずに追加されるはず。
	 * - 親投稿がある => 親投稿がパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_cpt_post_and_page_for_cpt_posts_and_multiple_terms_and_has_parent_post() {
		register_taxonomy(
			self::$taxonomy,
			self::$post_type,
			array(
				'public'       => true,
				'hierarchical' => true,
				'rewrite'      => array(
					'hierarchical' => true,
				),
			)
		);
		register_post_type(
			self::$post_type,
			array(
				'labels'      => array(
					'name' => 'Foo',
				),
				'public'      => true,
				'has_archive' => true,
				'taxonomies'  => array( self::$taxonomy ),
				'wpf_cptp'    => array(
					'permalink_structure' => '/%year%/%monthnum%/%day%/%postname%/',
				),
			)
		);

		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Bar',
				'post_name'  => self::$post_type,
			)
		);

		$term_id = 0;
		for ( $i = 0; $i < 4; $i ++ ) {
			$term_id    = self::factory()->term->create(
				array(
					'taxonomy' => self::$taxonomy,
					'name'     => 'Baz' . $i,
					'parent'   => $term_id,
				)
			);
			$term_ids[] = $term_id;
		}
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id = self::factory()->post->create(
				array(
					'post_type'    => self::$post_type,
					'post_title'   => 'Post title ' . $i,
					'post_parent'  => 5 < $i ? $post_id : false,
					'post_date'    => '2012-12-12',
					'post_content' => 'Page 1 <!--nextpage--> Page 2',
				)
			);
			wp_set_post_terms( $post_id, $term_ids, self::$taxonomy );
		}

		$post_link = get_permalink( $post_id );
		$this->go_to( $post_link );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Bar',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type ) ),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Baz0',
				'link'  => home_url( user_trailingslashit( '/' . self::$taxonomy . '/baz0' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Post title 5',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type . '/2012/12/12/post-title-5' ) ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 6',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type . '/2012/12/12/post-title-6' ) ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 7',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type . '/2012/12/12/post-title-7' ) ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type . '/2012/12/12/post-title-8' ) ),
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

		$post_link = get_permalink( $post_id );
		$this->go_to( $post_link );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Bar',
				'link'  => home_url( '/?page_id=' . $page_id ),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Baz0',
				'link'  => home_url( '/?' . self::$taxonomy . '=baz0' ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Post title 5',
				'link'  => home_url( '/?' . self::$post_type . '=post-title-5' ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 6',
				'link'  => home_url( '/?' . self::$post_type . '=post-title-6' ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 7',
				'link'  => home_url( '/?' . self::$post_type . '=post-title-7' ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( '/?' . self::$post_type . '=post-title-8' ),
				'layer' => 'current_page',
			),
		);

		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);

		$next_page_link = $post_link . '&page=2';
		$this->go_to( $next_page_link );

		// 2ページ目以降も変わらないことを確認。
		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);
	}

	/**
	 * CPT個別投稿ページ
	 *
	 * 条件:
	 * - CPT投稿用ページ => CPT投稿用ページのタイトルがパンくずに追加されるはず。
	 * - 複数taxにまたがるタームを選択 => taxonomiesの先頭のctaxがメインtaxに選定されているはず。
	 * - 親投稿がある => 親投稿がパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_cpt_post_and_page_for_cpt_posts_and_multiple_taxs_and_has_parent_post() {
		register_taxonomy(
			self::$taxonomy,
			self::$post_type,
			array(
				'public'       => true,
				'hierarchical' => true,
				'rewrite'      => array(
					'hierarchical' => true,
				),
			)
		);
		register_taxonomy(
			self::$taxonomy . '_2',
			self::$post_type,
			array(
				'public'       => true,
				'hierarchical' => true,
				'rewrite'      => array(
					'hierarchical' => true,
				),
			)
		);
		register_post_type(
			self::$post_type,
			array(
				'labels'      => array(
					'name' => 'Foo',
				),
				'public'      => true,
				'has_archive' => true,
				'taxonomies'  => array( self::$taxonomy, self::$taxonomy . '_2' ),
				'wpf_cptp'    => array(
					'permalink_structure' => '/%year%/%monthnum%/%day%/%postname%/',
				),
			)
		);

		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Bar',
				'post_name'  => self::$post_type,
			)
		);

		$term_id = 0;
		for ( $i = 0; $i < 4; $i ++ ) {
			$term_id = self::factory()->term->create(
				array(
					'taxonomy' => self::$taxonomy,
					'name'     => 'Baz' . $i,
					'parent'   => $term_id,
				)
			);
		}

		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id = self::factory()->post->create(
				array(
					'post_type'    => self::$post_type,
					'post_title'   => 'Post title ' . $i,
					'post_parent'  => 5 < $i ? $post_id : false,
					'post_date'    => '2012-12-12',
					'post_content' => 'Page 1 <!--nextpage--> Page 2',
				)
			);
			wp_set_post_terms( $post_id, array( $term_id ), self::$taxonomy );
		}

		$term_id = 0;
		for ( $i = 0; $i < 4; $i ++ ) {
			$term_id = self::factory()->term->create(
				array(
					'taxonomy' => self::$taxonomy . '_2',
					'name'     => 'Qux' . $i,
					'parent'   => $term_id,
				)
			);
		}

		for ( $i = 0; $i < 9; $i ++ ) {
			wp_set_post_terms( $post_id, array( $term_id ), self::$taxonomy . '_2' );
		}

		$post_link = get_permalink( $post_id );
		$this->go_to( $post_link );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Bar',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type ) ),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Baz0',
				'link'  => home_url( user_trailingslashit( '/' . self::$taxonomy . '/baz0' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Baz1',
				'link'  => home_url( user_trailingslashit( '/' . self::$taxonomy . '/baz0/baz1' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Baz2',
				'link'  => home_url( user_trailingslashit( '/' . self::$taxonomy . '/baz0/baz1/baz2' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Baz3',
				'link'  => home_url( user_trailingslashit( '/' . self::$taxonomy . '/baz0/baz1/baz2/baz3' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Post title 5',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type . '/2012/12/12/post-title-5/' ) ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 6',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type . '/2012/12/12/post-title-6/' ) ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 7',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type . '/2012/12/12/post-title-7/' ) ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( user_trailingslashit( '/' . self::$post_type . '/2012/12/12/post-title-8/' ) ),
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

		$post_link = get_permalink( $post_id );
		$this->go_to( $post_link );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Bar',
				'link'  => home_url( '/?page_id=' . $page_id ),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Baz0',
				'link'  => home_url( '/?' . self::$taxonomy . '=baz0' ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Baz1',
				'link'  => home_url( '/?' . self::$taxonomy . '=baz1' ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Baz2',
				'link'  => home_url( '/?' . self::$taxonomy . '=baz2' ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Baz3',
				'link'  => home_url( '/?' . self::$taxonomy . '=baz3' ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Post title 5',
				'link'  => home_url( '/?' . self::$post_type . '=post-title-5' ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 6',
				'link'  => home_url( '/?' . self::$post_type . '=post-title-6' ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 7',
				'link'  => home_url( '/?' . self::$post_type . '=post-title-7' ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( '/?' . self::$post_type . '=post-title-8' ),
				'layer' => 'current_page',
			),
		);

		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);

		$next_page_link = $post_link . '&page=2';
		$this->go_to( $next_page_link );

		// 2ページ目以降も変わらないことを確認。
		$this->assertSame(
			$expected,
			WPF_Template_Tags::get_the_breadcrumbs()
		);
	}
}
