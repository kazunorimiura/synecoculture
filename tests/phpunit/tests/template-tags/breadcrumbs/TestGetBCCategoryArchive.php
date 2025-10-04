<?php

/**
 * WPF_Template_Tags::get_the_breadcrumbs のユニットテスト。
 *
 * @group template_tags
 * @covers WPF_Template_Tags
 * @coversDefaultClass WPF_Template_Tags
 */
class TestGetBCCategoryArchive extends WPF_UnitTestCase {

	/**
	 * カテゴリアーカイブ
	 *
	 * 条件:
	 * - デフォルト投稿タイプ => `Posts` がパンくずに追加されるはず。
	 * - 親カテゴリなし => 何も追加されないはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_category_archive() {
		$cat_id  = self::factory()->category->create();
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );
			wp_set_post_categories( $post_id, array( $cat_id ) );
		}

		$this->go_to( get_category_link( $cat_id ) );

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
				'text'  => get_cat_name( $cat_id ),
				'link'  => get_category_link( $cat_id ),
				'layer' => 'current_term',
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

		$this->go_to( get_category_link( $cat_id ) );

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
				'text'  => get_cat_name( $cat_id ),
				'link'  => home_url( '/?cat=' . $cat_id ),
				'layer' => 'current_term',
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
	 * カテゴリアーカイブ
	 *
	 * 条件:
	 * - デフォルト投稿タイプ => `Posts` がパンくずに追加されるはず。
	 * - 親カテゴリあり => 親カテゴリがパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_category_archive_and_has_parent_cat() {
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
			$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );
			wp_set_post_categories( $post_id, array( $cat_id ) );
		}

		$this->go_to( get_category_link( $cat_id ) );

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
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Foo1',
				'link'  => home_url( user_trailingslashit( '/category/foo0/foo1' ) ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Foo2',
				'link'  => home_url( user_trailingslashit( '/category/foo0/foo1/foo2' ) ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Foo3',
				'link'  => home_url( user_trailingslashit( '/category/foo0/foo1/foo2/foo3' ) ),
				'layer' => 'current_term',
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

		$this->go_to( get_category_link( $cat_id ) );

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
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Foo1',
				'link'  => home_url( '/?cat=' . get_cat_ID( 'foo1' ) ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Foo2',
				'link'  => home_url( '/?cat=' . get_cat_ID( 'foo2' ) ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Foo3',
				'link'  => home_url( '/?cat=' . get_cat_ID( 'foo3' ) ),
				'layer' => 'current_term',
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
	 * カテゴリアーカイブ
	 *
	 * 条件:
	 * - CPT => CPT名がパンくずに追加されるはず。
	 * - 親カテゴリなし => 何も追加されないはず。
	 *
	 * 備考:
	 * このテストは相当なエッジケースであり、どちらかといえば、CPTに `category` taxを
	 * 紐づけたときの振る舞いを理解しておく目的で作った。
	 * なお、CPTとデフォルト投稿タイプ間において、同じカテゴリを選択している投稿が存在す
	 * る場合、`category` taxに紐づけれられた最も優先度の高い投稿タイプ（つまり、
	 * `post`）が投稿タイプクエリとして扱われるため、このテストのように、CPT名がパンく
	 * ずに追加されることはない。CPTを優先したいなら `category` tax の object_type
	 * キー配列をソートすれば、おそらく変更できるが、そんなことするなら、専用taxを作成す
	 * るほうがよいと思う。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_category_archive_and_cpt() {
		register_post_type(
			self::$post_type,
			array(
				'labels'      => array(
					'name' => 'Foo',
				),
				'public'      => true,
				'has_archive' => true,
				'taxonomies'  => array( 'category' ),
			)
		);
		register_taxonomy_for_object_type( 'category', self::$post_type );

		$post_type     = self::$post_type;
		$pre_get_posts = function ( $query ) use ( $post_type ) {
			if ( is_category() ) {
				// カテゴリアーカイブの投稿タイプクエリに $post_type を追加する。
				$query->set( 'post_type', array( 'nav_menu_item', 'post', $post_type ) );
				return $query;
			}
		};
		add_action( 'pre_get_posts', $pre_get_posts );

		$cat_id  = self::factory()->category->create();
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id = self::factory()->post->create( array( 'post_type' => self::$post_type ) );
			wp_set_post_categories( $post_id, array( $cat_id ) );
		}

		$this->go_to( get_category_link( $cat_id ) );

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
				'text'  => get_cat_name( $cat_id ),
				'link'  => get_category_link( $cat_id ),
				'layer' => 'current_term',
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

		$this->go_to( get_category_link( $cat_id ) );

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
				'text'  => get_cat_name( $cat_id ),
				'link'  => home_url( '/?cat=' . $cat_id ),
				'layer' => 'current_term',
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

		remove_filter( 'pre_get_posts', $pre_get_posts );
	}

	/**
	 * カテゴリアーカイブ
	 *
	 * 条件:
	 * - CPT => CPT名がパンくずに追加されるはず。
	 * - 親カテゴリあり => 親カテゴリがパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_category_archive_and_cpt_and_has_parent_cat() {
		register_post_type(
			self::$post_type,
			array(
				'labels'      => array(
					'name' => 'Foo',
				),
				'public'      => true,
				'has_archive' => true,
				'taxonomies'  => array( 'category' ),
			)
		);
		register_taxonomy_for_object_type( 'category', self::$post_type );

		// カテゴリアーカイブのクエリにCPTを追加する（`category` には `post` のみ紐づいているため）。
		$post_type     = self::$post_type;
		$pre_get_posts = function ( $query ) use ( $post_type ) {
			if ( is_category() ) {
				$post_type = get_query_var( 'post_type' );
				if ( $post_type ) {
					$post_type = $post_type;
				} else {
					$post_type = array( 'nav_menu_item', 'post', self::$post_type );
				}
				$query->set( 'post_type', $post_type );
				return $query;
			}
		};
		add_action( 'pre_get_posts', $pre_get_posts );

		$cat_id = 0;
		for ( $i = 0; $i < 4; $i ++ ) {
			$cat_id = self::factory()->category->create(
				array(
					'name'   => 'Bar' . $i,
					'parent' => $cat_id,
				)
			);
		}
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id = self::factory()->post->create( array( 'post_type' => self::$post_type ) );
			wp_set_post_categories( $post_id, array( $cat_id ) );
		}

		$this->go_to( get_category_link( $cat_id ) );

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
				'text'  => 'Bar0',
				'link'  => home_url( user_trailingslashit( '/category/bar0' ) ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar1',
				'link'  => home_url( user_trailingslashit( '/category/bar0/bar1' ) ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar2',
				'link'  => home_url( user_trailingslashit( '/category/bar0/bar1/bar2' ) ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar3',
				'link'  => home_url( user_trailingslashit( '/category/bar0/bar1/bar2/bar3' ) ),
				'layer' => 'current_term',
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

		$this->go_to( get_category_link( $cat_id ) );

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
				'text'  => 'Bar0',
				'link'  => home_url( '?cat=' . get_cat_ID( 'bar0' ) ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar1',
				'link'  => home_url( '?cat=' . get_cat_ID( 'bar1' ) ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar2',
				'link'  => home_url( '?cat=' . get_cat_ID( 'bar2' ) ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar3',
				'link'  => home_url( '?cat=' . get_cat_ID( 'bar3' ) ),
				'layer' => 'current_term',
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

		remove_filter( 'pre_get_posts', $pre_get_posts );
	}

	/**
	 * カテゴリアーカイブ
	 *
	 * 条件:
	 * - 投稿用ページ => 投稿用ページのタイトルがパンくずに追加されるはず。
	 * - 親カテゴリなし => 何も追加されないはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_category_archive_and_page_for_posts() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Foo',
			)
		);
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $page_id );

		$cat_id  = self::factory()->category->create();
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );
			wp_set_post_categories( $post_id, array( $cat_id ) );
		}

		$this->go_to( get_category_link( $cat_id ) );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Foo',
				'link'  => home_url( user_trailingslashit( '/foo' ) ),
				'layer' => 'post_type',
			),
			array(
				'text'  => get_cat_name( $cat_id ),
				'link'  => get_category_link( $cat_id ),
				'layer' => 'current_term',
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

		$this->go_to( get_category_link( $cat_id ) );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Foo',
				'link'  => home_url( '/?page_id=' . $page_id ),
				'layer' => 'post_type',
			),
			array(
				'text'  => get_cat_name( $cat_id ),
				'link'  => home_url( '/?cat=' . $cat_id ),
				'layer' => 'current_term',
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
	 * カテゴリアーカイブ
	 *
	 * 条件:
	 * - 投稿用ページ => 投稿用ページのタイトルがパンくずに追加されるはず。
	 * - 親カテゴリあり => 親カテゴリがパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_category_archive_and_page_for_posts_and_has_parent_cat() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Foo',
			)
		);
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $page_id );

		$cat_id = 0;
		for ( $i = 0; $i < 4; $i ++ ) {
			$cat_id = self::factory()->category->create(
				array(
					'name'   => 'Bar' . $i,
					'parent' => $cat_id,
				)
			);
		}
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );
			wp_set_post_categories( $post_id, array( $cat_id ) );
		}

		$this->go_to( get_category_link( $cat_id ) );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Foo',
				'link'  => home_url( user_trailingslashit( '/foo' ) ),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Bar0',
				'link'  => home_url( user_trailingslashit( '/category/bar0' ) ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar1',
				'link'  => home_url( user_trailingslashit( '/category/bar0/bar1' ) ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar2',
				'link'  => home_url( user_trailingslashit( '/category/bar0/bar1/bar2' ) ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar3',
				'link'  => home_url( user_trailingslashit( '/category/bar0/bar1/bar2/bar3' ) ),
				'layer' => 'current_term',
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

		$this->go_to( get_category_link( $cat_id ) );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Foo',
				'link'  => home_url( '/?page_id=' . $page_id ),
				'layer' => 'post_type',
			),
			array(
				'text'  => 'Bar0',
				'link'  => home_url( '/?cat=' . get_cat_ID( 'bar0' ) ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar1',
				'link'  => home_url( '/?cat=' . get_cat_ID( 'bar1' ) ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar2',
				'link'  => home_url( '/?cat=' . get_cat_ID( 'bar2' ) ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar3',
				'link'  => home_url( '/?cat=' . get_cat_ID( 'bar3' ) ),
				'layer' => 'current_term',
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
	 * カテゴリアーカイブ
	 *
	 * 条件:
	 * - CPT投稿用ページ => CPT投稿用ページのタイトルがパンくずに追加されるはず。
	 * - 親カテゴリなし => 何も追加されないはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_category_archive_and_page_for_cpt_posts() {
		register_post_type(
			self::$post_type,
			array(
				'labels'      => array(
					'name' => 'Foo',
				),
				'public'      => true,
				'has_archive' => true,
				'taxonomies'  => array( 'category' ),
			)
		);
		register_taxonomy_for_object_type( 'category', self::$post_type );

		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Bar',
				'post_name'  => self::$post_type,
			)
		);

		// カテゴリアーカイブのクエリにCPTを追加する（`category` には `post` のみ紐づいているため）。
		$post_type     = self::$post_type;
		$pre_get_posts = function ( $query ) use ( $post_type ) {
			if ( is_category() ) {
				$post_type = get_query_var( 'post_type' );
				if ( $post_type ) {
					$post_type = $post_type;
				} else {
					$post_type = array( 'nav_menu_item', 'post', self::$post_type );
				}
				$query->set( 'post_type', $post_type );
				return $query;
			}
		};
		add_action( 'pre_get_posts', $pre_get_posts );

		$cat_id  = self::factory()->category->create();
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id = self::factory()->post->create( array( 'post_type' => self::$post_type ) );
			wp_set_post_categories( $post_id, array( $cat_id ) );
		}

		$this->go_to( get_category_link( $cat_id ) );

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
				'text'  => get_cat_name( $cat_id ),
				'link'  => get_category_link( $cat_id ),
				'layer' => 'current_term',
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

		$this->go_to( get_category_link( $cat_id ) );

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
				'text'  => get_cat_name( $cat_id ),
				'link'  => home_url( '/?cat=' . $cat_id ),
				'layer' => 'current_term',
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

		remove_filter( 'pre_get_posts', $pre_get_posts );
	}

	/**
	 * カテゴリアーカイブ
	 *
	 * 条件:
	 * - CPT投稿用ページ => CPT投稿用ページのタイトルがパンくずに追加されるはず。
	 * - 親カテゴリあり => 親カテゴリがパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_category_archive_and_page_for_cpt_posts_and_has_parent_cat() {
		register_post_type(
			self::$post_type,
			array(
				'labels'      => array(
					'name' => 'Foo',
				),
				'public'      => true,
				'has_archive' => true,
				'taxonomies'  => array( 'category' ),
			)
		);
		register_taxonomy_for_object_type( 'category', self::$post_type );

		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Bar',
				'post_name'  => self::$post_type,
			)
		);

		// カテゴリアーカイブのクエリにCPTを追加する（`category` には `post` のみ紐づいているため）。
		$post_type     = self::$post_type;
		$pre_get_posts = function ( $query ) use ( $post_type ) {
			if ( is_category() ) {
				$post_type = get_query_var( 'post_type' );
				if ( $post_type ) {
					$post_type = $post_type;
				} else {
					$post_type = array( 'nav_menu_item', 'post', self::$post_type );
				}
				$query->set( 'post_type', $post_type );
				return $query;
			}
		};
		add_action( 'pre_get_posts', $pre_get_posts );

		$cat_id = 0;
		for ( $i = 0; $i < 4; $i ++ ) {
			$cat_id = self::factory()->category->create(
				array(
					'name'   => 'Bar' . $i,
					'parent' => $cat_id,
				)
			);
		}
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id = self::factory()->post->create( array( 'post_type' => self::$post_type ) );
			wp_set_post_categories( $post_id, array( $cat_id ) );
		}

		$this->go_to( get_category_link( $cat_id ) );

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
				'text'  => 'Bar0',
				'link'  => home_url( user_trailingslashit( '/category/bar0' ) ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar1',
				'link'  => home_url( user_trailingslashit( '/category/bar0/bar1' ) ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar2',
				'link'  => home_url( user_trailingslashit( '/category/bar0/bar1/bar2' ) ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar3',
				'link'  => home_url( user_trailingslashit( '/category/bar0/bar1/bar2/bar3' ) ),
				'layer' => 'current_term',
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

		$this->go_to( get_category_link( $cat_id ) );

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
				'text'  => 'Bar0',
				'link'  => home_url( '?cat=' . get_cat_ID( 'bar0' ) ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar1',
				'link'  => home_url( '?cat=' . get_cat_ID( 'bar1' ) ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar2',
				'link'  => home_url( '?cat=' . get_cat_ID( 'bar2' ) ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar3',
				'link'  => home_url( '?cat=' . get_cat_ID( 'bar3' ) ),
				'layer' => 'current_term',
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

		remove_filter( 'pre_get_posts', $pre_get_posts );
	}
}
