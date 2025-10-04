<?php

/**
 * WPF_Template_Tags::get_the_breadcrumbs のユニットテスト。
 *
 * @group template_tags
 * @covers WPF_Template_Tags
 * @coversDefaultClass WPF_Template_Tags
 */
class TestGetBCPost extends WPF_UnitTestCase {

	/**
	 * 個別投稿ページ
	 *
	 * 条件:
	 * - デフォルト投稿タイプ => `Posts` がパンくずに追加されるはず。
	 * - 親投稿がある => 親投稿がパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_post_and_has_parent_post() {
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id    = self::factory()->post->create(
				array(
					'post_type'    => 'post',
					'post_title'   => 'Post title ' . $i,
					'post_date'    => '2012-12-12',
					'post_parent'  => 5 < $i ? $post_id : false,
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
				'text'  => 'Posts',
				'link'  => home_url(),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Post title 5',
				'link'  => home_url( user_trailingslashit( '/2012/12/12/post-title-5/' ) ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 6',
				'link'  => home_url( user_trailingslashit( '/2012/12/12/post-title-6/' ) ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 7',
				'link'  => home_url( user_trailingslashit( '/2012/12/12/post-title-7/' ) ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( user_trailingslashit( '/2012/12/12/post-title-8/' ) ),
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
				'text'  => 'Posts',
				'link'  => home_url(),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Post title 5',
				'link'  => home_url( '/?p=' . $post_ids[5] ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 6',
				'link'  => home_url( '/?p=' . $post_ids[6] ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 7',
				'link'  => home_url( '/?p=' . $post_ids[7] ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( '/?p=' . $post_ids[8] ),
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
	 * 個別投稿ページ
	 *
	 * 条件:
	 * - デフォルト投稿タイプ => `Posts` がパンくずに追加されるはず。
	 * - 単一タームを選択 => そのタームがパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_post_and_single_term() {
		$cat_id  = self::factory()->category->create( array( 'name' => 'Foo' ) );
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id    = self::factory()->post->create(
				array(
					'post_type'    => 'post',
					'post_title'   => 'Post title ' . $i,
					'post_date'    => '2012-12-12',
					'post_content' => 'Page 1 <!--nextpage--> Page 2',
				)
			);
			$post_ids[] = $post_id;

			wp_set_post_categories( $post_id, array( $cat_id ) );
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
				'text'  => 'Posts',
				'link'  => home_url(),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Foo',
				'link'  => home_url( user_trailingslashit( '/category/foo' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( user_trailingslashit( '/2012/12/12/post-title-8/' ) ),
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
				'text'  => 'Posts',
				'link'  => home_url(),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Foo',
				'link'  => home_url( '/?cat=' . get_cat_ID( 'foo' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( '/?p=' . $post_ids[8] ),
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
	 * 個別投稿ページ
	 *
	 * 条件:
	 * - デフォルト投稿タイプ => `Posts` がパンくずに追加されるはず。
	 * - 親を持つタームを選択 => 親も含めたタームがパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_post_and_has_parent_term() {
		$cat_id = 0;
		for ( $i = 0; $i < 4; $i ++ ) {
			$cat_id = self::factory()->category->create(
				array(
					'name'   => 'Foo' . $i,
					'parent' => $cat_id,
				)
			);
		}
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id    = self::factory()->post->create(
				array(
					'post_type'    => 'post',
					'post_title'   => 'Post title ' . $i,
					'post_date'    => '2012-12-12',
					'post_content' => 'Page 1 <!--nextpage--> Page 2',
				)
			);
			$post_ids[] = $post_id;

			wp_set_post_categories( $post_id, array( $cat_id ) );
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
				'text'  => 'Posts',
				'link'  => home_url(),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Foo0',
				'link'  => home_url( user_trailingslashit( '/category/foo0' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Foo1',
				'link'  => home_url( user_trailingslashit( '/category/foo0/foo1' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Foo2',
				'link'  => home_url( user_trailingslashit( '/category/foo0/foo1/foo2' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Foo3',
				'link'  => home_url( user_trailingslashit( '/category/foo0/foo1/foo2/foo3' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( user_trailingslashit( '/2012/12/12/post-title-8/' ) ),
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
				'text'  => 'Posts',
				'link'  => home_url(),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Foo0',
				'link'  => home_url( '/?cat=' . get_cat_ID( 'foo0' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Foo1',
				'link'  => home_url( '/?cat=' . get_cat_ID( 'foo1' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Foo2',
				'link'  => home_url( '/?cat=' . get_cat_ID( 'foo2' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Foo3',
				'link'  => home_url( '/?cat=' . get_cat_ID( 'foo3' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( '/?p=' . $post_ids[8] ),
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
	 * 個別投稿ページ
	 *
	 * 条件:
	 * - デフォルト投稿タイプ => `Posts` がパンくずに追加されるはず。
	 * - 複数タームを選択 => 最も若いidのタームがメインタームとしてパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_post_and_multiple_terms() {
		$cat_id = 0;
		for ( $i = 0; $i < 4; $i ++ ) {
			$cat_id    = self::factory()->category->create(
				array(
					'name'   => 'Foo' . $i,
					'parent' => $cat_id,
				)
			);
			$cat_ids[] = $cat_id;
		}
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id    = self::factory()->post->create(
				array(
					'post_type'    => 'post',
					'post_title'   => 'Post title ' . $i,
					'post_date'    => '2012-12-12',
					'post_content' => 'Page 1 <!--nextpage--> Page 2',
				)
			);
			$post_ids[] = $post_id;

			wp_set_post_categories( $post_id, $cat_ids ); // 複数タームを設定。
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
				'text'  => 'Posts',
				'link'  => home_url(),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Foo0',
				'link'  => home_url( user_trailingslashit( '/category/foo0' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( user_trailingslashit( '/2012/12/12/post-title-8/' ) ),
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
				'text'  => 'Posts',
				'link'  => home_url(),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Foo0',
				'link'  => home_url( '/?cat=' . get_cat_ID( 'foo0' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( '/?p=' . $post_ids[8] ),
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
	 * 個別投稿ページ
	 *
	 * 条件:
	 * - デフォルト投稿タイプ => `Posts` がパンくずに追加されるはず。
	 * - 複数taxにまたがるタームを選択 => 最も若いidのctaxがメインtaxに選定されているはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_post_and_multiple_taxs() {
		register_taxonomy(
			self::$taxonomy,
			'post',
			array(
				'public'       => true,
				'hierarchical' => true,
				'rewrite'      => array(
					'hierarchical' => true,
				),
			)
		);
		register_taxonomy_for_object_type( self::$taxonomy, 'post' );

		$cat_id = 0;
		for ( $i = 0; $i < 4; $i ++ ) {
			$cat_id = self::factory()->category->create(
				array(
					'name'   => 'Foo' . $i,
					'parent' => $cat_id,
				)
			);
		}

		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id    = self::factory()->post->create(
				array(
					'post_type'  => 'post',
					'post_title' => 'Post title ' . $i,
					'post_date'  => '2012-12-12',
				)
			);
			$post_ids[] = $post_id;

			wp_set_post_categories( $post_id, array( $cat_id ) );
		}

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

		for ( $i = 0; $i < 9; $i ++ ) {
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
				'text'  => 'Posts',
				'link'  => home_url(),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Foo0',
				'link'  => home_url( user_trailingslashit( '/category/foo0' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Foo1',
				'link'  => home_url( user_trailingslashit( '/category/foo0/foo1' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Foo2',
				'link'  => home_url( user_trailingslashit( '/category/foo0/foo1/foo2' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Foo3',
				'link'  => home_url( user_trailingslashit( '/category/foo0/foo1/foo2/foo3' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( user_trailingslashit( '/2012/12/12/post-title-8/' ) ),
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
				'text'  => 'Posts',
				'link'  => home_url(),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Foo0',
				'link'  => home_url( '/?cat=' . get_cat_ID( 'foo0' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Foo1',
				'link'  => home_url( '/?cat=' . get_cat_ID( 'foo1' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Foo2',
				'link'  => home_url( '/?cat=' . get_cat_ID( 'foo2' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Foo3',
				'link'  => home_url( '/?cat=' . get_cat_ID( 'foo3' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( '/?p=' . $post_ids[8] ),
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
	 * 個別投稿ページ
	 *
	 * 条件:
	 * - デフォルト投稿タイプ => `Posts` がパンくずに追加されるはず。
	 * - 単一タームを選択 => そのタームがパンくずに追加されるはず。
	 * - 親投稿がある => 親投稿がパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_post_and_single_term_and_has_parent_post() {
		$cat_id  = self::factory()->category->create( array( 'name' => 'Foo' ) );
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id    = self::factory()->post->create(
				array(
					'post_type'    => 'post',
					'post_title'   => 'Post title ' . $i,
					'post_date'    => '2012-12-12',
					'post_parent'  => 5 < $i ? $post_id : false,
					'post_content' => 'Page 1 <!--nextpage--> Page 2',
				)
			);
			$post_ids[] = $post_id;

			wp_set_post_categories( $post_id, array( $cat_id ) );
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
				'text'  => 'Posts',
				'link'  => home_url(),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Foo',
				'link'  => home_url( user_trailingslashit( '/category/foo' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Post title 5',
				'link'  => home_url( user_trailingslashit( '/2012/12/12/post-title-5/' ) ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 6',
				'link'  => home_url( user_trailingslashit( '/2012/12/12/post-title-6/' ) ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 7',
				'link'  => home_url( user_trailingslashit( '/2012/12/12/post-title-7/' ) ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( user_trailingslashit( '/2012/12/12/post-title-8/' ) ),
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
				'text'  => 'Posts',
				'link'  => home_url(),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Foo',
				'link'  => home_url( '/?cat=' . get_cat_ID( 'foo' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Post title 5',
				'link'  => home_url( '/?p=' . $post_ids[5] ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 6',
				'link'  => home_url( '/?p=' . $post_ids[6] ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 7',
				'link'  => home_url( '/?p=' . $post_ids[7] ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( '/?p=' . $post_ids[8] ),
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
	 * 個別投稿ページ
	 *
	 * 条件:
	 * - デフォルト投稿タイプ => `Posts` がパンくずに追加されるはず。
	 * - 親を持つタームを選択 => 親も含めたタームがパンくずに追加されるはず。
	 * - 親投稿がある => 親投稿がパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_post_and_has_parent_term_and_has_parent_post() {
		$cat_id = 0;
		for ( $i = 0; $i < 4; $i ++ ) {
			$cat_id = self::factory()->category->create(
				array(
					'name'   => 'Foo' . $i,
					'parent' => $cat_id,
				)
			);
		}
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id    = self::factory()->post->create(
				array(
					'post_type'    => 'post',
					'post_title'   => 'Post title ' . $i,
					'post_date'    => '2012-12-12',
					'post_parent'  => 5 < $i ? $post_id : false,
					'post_content' => 'Page 1 <!--nextpage--> Page 2',
				)
			);
			$post_ids[] = $post_id;

			wp_set_post_categories( $post_id, array( $cat_id ) );
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
				'text'  => 'Posts',
				'link'  => home_url(),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Foo0',
				'link'  => home_url( user_trailingslashit( '/category/foo0' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Foo1',
				'link'  => home_url( user_trailingslashit( '/category/foo0/foo1' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Foo2',
				'link'  => home_url( user_trailingslashit( '/category/foo0/foo1/foo2' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Foo3',
				'link'  => home_url( user_trailingslashit( '/category/foo0/foo1/foo2/foo3' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Post title 5',
				'link'  => home_url( user_trailingslashit( '/2012/12/12/post-title-5/' ) ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 6',
				'link'  => home_url( user_trailingslashit( '/2012/12/12/post-title-6/' ) ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 7',
				'link'  => home_url( user_trailingslashit( '/2012/12/12/post-title-7/' ) ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( user_trailingslashit( '/2012/12/12/post-title-8/' ) ),
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
				'text'  => 'Posts',
				'link'  => home_url(),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Foo0',
				'link'  => home_url( '/?cat=' . get_cat_ID( 'foo0' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Foo1',
				'link'  => home_url( '/?cat=' . get_cat_ID( 'foo1' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Foo2',
				'link'  => home_url( '/?cat=' . get_cat_ID( 'foo2' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Foo3',
				'link'  => home_url( '/?cat=' . get_cat_ID( 'foo3' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Post title 5',
				'link'  => home_url( '/?p=' . $post_ids[5] ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 6',
				'link'  => home_url( '/?p=' . $post_ids[6] ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 7',
				'link'  => home_url( '/?p=' . $post_ids[7] ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( '/?p=' . $post_ids[8] ),
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
	 * 個別投稿ページ
	 *
	 * 条件:
	 * - デフォルト投稿タイプ => `Posts` がパンくずに追加されるはず。
	 * - 複数タームを選択 => 最も若いidのタームがメインタームとしてパンくずに追加されるはず。
	 * - 親投稿がある => 親投稿がパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_post_and_multiple_terms_and_has_parent_post() {
		$cat_id = 0;
		for ( $i = 0; $i < 4; $i ++ ) {
			$cat_id    = self::factory()->category->create(
				array(
					'name'   => 'Foo' . $i,
					'parent' => $cat_id,
				)
			);
			$cat_ids[] = $cat_id;
		}
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id    = self::factory()->post->create(
				array(
					'post_type'    => 'post',
					'post_title'   => 'Post title ' . $i,
					'post_date'    => '2012-12-12',
					'post_parent'  => 5 < $i ? $post_id : false,
					'post_content' => 'Page 1 <!--nextpage--> Page 2',
				)
			);
			$post_ids[] = $post_id;

			wp_set_post_categories( $post_id, $cat_ids ); // 複数タームを設定。
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
				'text'  => 'Posts',
				'link'  => home_url(),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Foo0',
				'link'  => home_url( user_trailingslashit( '/category/foo0' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Post title 5',
				'link'  => home_url( user_trailingslashit( '/2012/12/12/post-title-5/' ) ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 6',
				'link'  => home_url( user_trailingslashit( '/2012/12/12/post-title-6/' ) ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 7',
				'link'  => home_url( user_trailingslashit( '/2012/12/12/post-title-7/' ) ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( user_trailingslashit( '/2012/12/12/post-title-8/' ) ),
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
				'text'  => 'Posts',
				'link'  => home_url(),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Foo0',
				'link'  => home_url( '/?cat=' . get_cat_ID( 'foo0' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Post title 5',
				'link'  => home_url( '/?p=' . $post_ids[5] ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 6',
				'link'  => home_url( '/?p=' . $post_ids[6] ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 7',
				'link'  => home_url( '/?p=' . $post_ids[7] ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( '/?p=' . $post_ids[8] ),
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
	 * 個別投稿ページ
	 *
	 * 条件:
	 * - デフォルト投稿タイプ => `Posts` がパンくずに追加されるはず。
	 * - 複数taxにまたがるタームを選択 => 最も若いidのctaxがメインtaxに選定されているはず。
	 * - 親投稿がある => 親投稿がパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_post_and_multiple_taxs_and_has_parent_post() {
		register_taxonomy(
			self::$taxonomy,
			'post',
			array(
				'public'       => true,
				'hierarchical' => true,
				'rewrite'      => array(
					'hierarchical' => true,
				),
			)
		);
		register_taxonomy_for_object_type( self::$taxonomy, 'post' );

		$cat_id = 0;
		for ( $i = 0; $i < 4; $i ++ ) {
			$cat_id    = self::factory()->category->create(
				array(
					'name'   => 'Foo' . $i,
					'parent' => $cat_id,
				)
			);
			$cat_ids[] = $cat_id;
		}

		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id    = self::factory()->post->create(
				array(
					'post_type'    => 'post',
					'post_title'   => 'Post title ' . $i,
					'post_date'    => '2012-12-12',
					'post_parent'  => 5 < $i ? $post_id : false,
					'post_content' => 'Page 1 <!--nextpage--> Page 2',
				)
			);
			$post_ids[] = $post_id;

			wp_set_post_categories( $post_id, $cat_ids ); // 複数タームを設定。
		}

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

		for ( $i = 0; $i < 9; $i ++ ) {
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
				'text'  => 'Posts',
				'link'  => home_url(),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Foo0',
				'link'  => home_url( user_trailingslashit( '/category/foo0' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Post title 5',
				'link'  => home_url( user_trailingslashit( '/2012/12/12/post-title-5/' ) ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 6',
				'link'  => home_url( user_trailingslashit( '/2012/12/12/post-title-6/' ) ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 7',
				'link'  => home_url( user_trailingslashit( '/2012/12/12/post-title-7/' ) ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( user_trailingslashit( '/2012/12/12/post-title-8/' ) ),
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
				'text'  => 'Posts',
				'link'  => home_url(),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Foo0',
				'link'  => home_url( '/?cat=' . get_cat_ID( 'foo0' ) ),
				'layer' => 'taxonomy',
			),
			array(
				'text'  => 'Post title 5',
				'link'  => home_url( '/?p=' . $post_ids[5] ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 6',
				'link'  => home_url( '/?p=' . $post_ids[6] ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 7',
				'link'  => home_url( '/?p=' . $post_ids[7] ),
				'layer' => 'parent_post',
			),
			array(
				'text'  => 'Post title 8',
				'link'  => home_url( '/?p=' . $post_ids[8] ),
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
