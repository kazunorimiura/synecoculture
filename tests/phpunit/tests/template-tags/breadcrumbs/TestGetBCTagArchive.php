<?php

/**
 * WPF_Template_Tags::get_the_breadcrumbs のユニットテスト。
 *
 * @group template_tags
 * @covers WPF_Template_Tags
 * @coversDefaultClass WPF_Template_Tags
 */
class TestGetBCTagArchive extends WPF_UnitTestCase {

	/**
	 * タグアーカイブ
	 *
	 * 条件:
	 * - デフォルト投稿タイプ => `Posts` がパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_tag_archive() {
		$tag_id  = self::factory()->tag->create();
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );
			wp_set_post_tags( $post_id, array( $tag_id ) );
		}

		$this->go_to( get_tag_link( $tag_id ) );

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
				'text'  => get_term( $tag_id )->name,
				'link'  => get_term_link( $tag_id ),
				'layer' => 'current_term',
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

		$this->go_to( get_tag_link( $tag_id ) );

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
				'text'  => get_term( $tag_id )->name,
				'link'  => home_url( '/?tag=' . get_term( $tag_id )->slug ),
				'layer' => 'current_term',
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
	 * タグアーカイブ
	 *
	 * 条件:
	 * - 投稿用ページ => 投稿用ページのタイトルがパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_tag_archive_and_page_for_posts() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Foo',
			)
		);
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $page_id );

		$tag_id  = self::factory()->tag->create();
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );
			wp_set_post_tags( $post_id, array( $tag_id ) );
		}

		$this->go_to( get_tag_link( $tag_id ) );

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
				'text'  => get_term( $tag_id )->name,
				'link'  => get_term_link( $tag_id ),
				'layer' => 'current_term',
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

		$this->go_to( get_tag_link( $tag_id ) );

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
				'text'  => get_term( $tag_id )->name,
				'link'  => home_url( '/?tag=' . get_term( $tag_id )->slug ),
				'layer' => 'current_term',
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
	 * タグアーカイブ
	 *
	 * 条件:
	 * - CPT => CPT名がパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_tag_archive_and_cpt() {
		register_post_type(
			self::$post_type,
			array(
				'labels'      => array(
					'name' => 'Foo',
				),
				'public'      => true,
				'has_archive' => true,
				'taxonomies'  => array( 'post_tag' ),
			)
		);
		register_taxonomy_for_object_type( 'post_tag', self::$post_type );

		// タグアーカイブのクエリにCPTを追加する（`post_tag` には `post` のみ紐づいているため）。
		$post_type     = self::$post_type;
		$pre_get_posts = function ( $query ) use ( $post_type ) {
			if ( is_tag() ) {
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

		$tag_id  = self::factory()->tag->create();
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id = self::factory()->post->create( array( 'post_type' => self::$post_type ) );
			wp_set_post_tags( $post_id, array( $tag_id ) );
		}

		$this->go_to( get_tag_link( $tag_id ) );

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
				'text'  => get_term( $tag_id )->name,
				'link'  => get_term_link( $tag_id ),
				'layer' => 'current_term',
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

		$this->go_to( get_tag_link( $tag_id ) );

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
				'text'  => get_term( $tag_id )->name,
				'link'  => home_url( '/?tag=' . get_term( $tag_id )->slug ),
				'layer' => 'current_term',
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

		remove_filter( 'pre_get_posts', $pre_get_posts );
	}

	/**
	 * タグアーカイブ
	 *
	 * 条件:
	 * - CPT投稿用ページ => CPT投稿用ページのタイトルがパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_tag_archive_and_page_for_cpt_posts() {
		register_post_type(
			self::$post_type,
			array(
				'labels'      => array(
					'name' => 'Foo',
				),
				'public'      => true,
				'has_archive' => true,
				'taxonomies'  => array( 'post_tag' ),
			)
		);
		register_taxonomy_for_object_type( 'post_tag', self::$post_type );

		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Bar',
				'post_name'  => self::$post_type,
			)
		);

		// タグアーカイブのクエリにCPTを追加する（`post_tag` には `post` のみ紐づいているため）。
		$post_type     = self::$post_type;
		$pre_get_posts = function ( $query ) use ( $post_type ) {
			if ( is_tag() ) {
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

		$tag_id  = self::factory()->tag->create();
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id = self::factory()->post->create( array( 'post_type' => self::$post_type ) );
			wp_set_post_tags( $post_id, array( $tag_id ) );
		}

		$this->go_to( get_tag_link( $tag_id ) );

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
				'text'  => get_term( $tag_id )->name,
				'link'  => get_term_link( $tag_id ),
				'layer' => 'current_term',
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

		$this->go_to( get_tag_link( $tag_id ) );

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
				'text'  => get_term( $tag_id )->name,
				'link'  => home_url( '/?tag=' . get_term( $tag_id )->slug ),
				'layer' => 'current_term',
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

		remove_filter( 'pre_get_posts', $pre_get_posts );
	}
}
