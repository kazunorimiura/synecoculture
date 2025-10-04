<?php

/**
 * WPF_Template_Tags::get_the_breadcrumbs のユニットテスト。
 *
 * @group template_tags
 * @covers WPF_Template_Tags
 * @coversDefaultClass WPF_Template_Tags
 */
class TestGetBCCtaxArchive extends WPF_UnitTestCase {

	/**
	 * ヒエラルキーのあるctaxアーカイブ
	 *
	 * 条件:
	 * - デフォルト投稿タイプ => `Posts` がパンくずに追加されるはず。
	 * - 親タームあり => 親タームがパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_ctax_archive_has_hierarchical_has_parent_term() {
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

		$term_id = 0;
		for ( $i = 0; $i < 4; $i ++ ) {
			$term_id = self::factory()->term->create(
				array(
					'taxonomy' => self::$taxonomy,
					'name'     => 'Bar' . $i,
					'parent'   => $term_id,
				)
			);
		}
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );
			wp_set_post_terms( $post_id, array( $term_id ), self::$taxonomy );
		}

		$this->go_to( get_term_link( $term_id ) );

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
				'text'  => 'Bar0',
				'link'  => home_url( user_trailingslashit( '/' . self::$taxonomy . '/bar0' ) ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar1',
				'link'  => home_url( user_trailingslashit( '/' . self::$taxonomy . '/bar0/bar1' ) ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar2',
				'link'  => home_url( user_trailingslashit( '/' . self::$taxonomy . '/bar0/bar1/bar2' ) ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar3',
				'link'  => home_url( user_trailingslashit( '/' . self::$taxonomy . '/bar0/bar1/bar2/bar3' ) ),
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

		$this->go_to( get_term_link( $term_id ) );

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
				'text'  => 'Bar0',
				'link'  => home_url( '/?' . self::$taxonomy . '=bar0' ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar1',
				'link'  => home_url( '/?' . self::$taxonomy . '=bar1' ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar2',
				'link'  => home_url( '/?' . self::$taxonomy . '=bar2' ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar3',
				'link'  => home_url( '/?' . self::$taxonomy . '=bar3' ),
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
	 * ヒエラルキーのあるctaxアーカイブ
	 *
	 * 条件:
	 * - デフォルト投稿タイプ => `Posts` がパンくずに追加されるはず。
	 * - 親タームなし => 何も追加されないはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_ctax_archive_has_hierarchical_and_no_parent_term() {
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

		$term_id = self::factory()->term->create( array( 'taxonomy' => self::$taxonomy ) );
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );
			wp_set_post_terms( $post_id, array( $term_id ), self::$taxonomy );
		}

		$this->go_to( get_term_link( $term_id ) );

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
				'text'  => get_term( $term_id )->name,
				'link'  => get_term_link( $term_id ),
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

		$this->go_to( get_term_link( $term_id ) );

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
				'text'  => get_term( $term_id )->name,
				'link'  => home_url( '/?ctax=' . get_term( $term_id )->slug ),
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
	 * ヒエラルキーのあるctaxアーカイブ
	 *
	 * 条件:
	 * - 投稿用ページ => 投稿用ページのタイトルがパンくずに追加されるはず。
	 * - 親タームあり => 親タームがパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_ctax_archive_has_hierarchical_and_page_for_posts_and_has_parent_term() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Foo',
			)
		);
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $page_id );

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

		$term_id = 0;
		for ( $i = 0; $i < 4; $i ++ ) {
			$term_id = self::factory()->term->create(
				array(
					'taxonomy' => self::$taxonomy,
					'name'     => 'Bar' . $i,
					'parent'   => $term_id,
				)
			);
		}
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );
			wp_set_post_terms( $post_id, array( $term_id ), self::$taxonomy );
		}

		$this->go_to( get_term_link( $term_id ) );

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
				'link'  => home_url( user_trailingslashit( '/' . self::$taxonomy . '/bar0' ) ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar1',
				'link'  => home_url( user_trailingslashit( '/' . self::$taxonomy . '/bar0/bar1' ) ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar2',
				'link'  => home_url( user_trailingslashit( '/' . self::$taxonomy . '/bar0/bar1/bar2' ) ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar3',
				'link'  => home_url( user_trailingslashit( '/' . self::$taxonomy . '/bar0/bar1/bar2/bar3' ) ),
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

		$this->go_to( get_term_link( $term_id ) );

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
				'link'  => home_url( '/?' . self::$taxonomy . '=bar0' ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar1',
				'link'  => home_url( '/?' . self::$taxonomy . '=bar1' ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar2',
				'link'  => home_url( '/?' . self::$taxonomy . '=bar2' ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar3',
				'link'  => home_url( '/?' . self::$taxonomy . '=bar3' ),
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
	 * ヒエラルキーのあるctaxアーカイブ
	 *
	 * 条件:
	 * - 投稿用ページ => 投稿用ページのタイトルがパンくずに追加されるはず。
	 * - 親タームなし => 何も追加されないはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_ctax_archive_has_hierarchical_and_page_for_posts_and_no_parent_term() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Foo',
			)
		);
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $page_id );

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

		$term_id = self::factory()->term->create( array( 'taxonomy' => self::$taxonomy ) );
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );
			wp_set_post_terms( $post_id, array( $term_id ), self::$taxonomy );
		}

		$this->go_to( get_term_link( $term_id ) );

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
				'text'  => get_term( $term_id )->name,
				'link'  => get_term_link( $term_id ),
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

		$this->go_to( get_term_link( $term_id ) );

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
				'text'  => get_term( $term_id )->name,
				'link'  => home_url( '/?ctax=' . get_term( $term_id )->slug ),
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
	 * ヒエラルキーのあるctaxアーカイブ
	 *
	 * 条件:
	 * - CPT => CPT名がパンくずに追加されるはず。
	 * - 親タームあり => 親タームがパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_ctax_archive_has_hierarchical_and_cpt_and_has_parent_term() {
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

		$term_id = 0;
		for ( $i = 0; $i < 4; $i ++ ) {
			$term_id = self::factory()->term->create(
				array(
					'taxonomy' => self::$taxonomy,
					'name'     => 'Bar' . $i,
					'parent'   => $term_id,
				)
			);
		}
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id = self::factory()->post->create( array( 'post_type' => self::$post_type ) );
			wp_set_post_terms( $post_id, array( $term_id ), self::$taxonomy );
		}

		$this->go_to( get_term_link( $term_id ) );

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
				'link'  => home_url( user_trailingslashit( '/' . self::$taxonomy . '/bar0' ) ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar1',
				'link'  => home_url( user_trailingslashit( '/' . self::$taxonomy . '/bar0/bar1' ) ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar2',
				'link'  => home_url( user_trailingslashit( '/' . self::$taxonomy . '/bar0/bar1/bar2' ) ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar3',
				'link'  => home_url( user_trailingslashit( '/' . self::$taxonomy . '/bar0/bar1/bar2/bar3' ) ),
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

		$this->go_to( get_term_link( $term_id ) );

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
				'link'  => home_url( '/?' . self::$taxonomy . '=bar0' ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar1',
				'link'  => home_url( '/?' . self::$taxonomy . '=bar1' ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar2',
				'link'  => home_url( '/?' . self::$taxonomy . '=bar2' ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar3',
				'link'  => home_url( '/?' . self::$taxonomy . '=bar3' ),
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
	 * ヒエラルキーのあるctaxアーカイブ
	 *
	 * 条件:
	 * - CPT => CPT名がパンくずに追加されるはず。
	 * - 親タームなし => 何も追加されないはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_ctax_archive_has_hierarchical_and_cpt_and_no_parent_term() {
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

		$term_id = self::factory()->term->create( array( 'taxonomy' => self::$taxonomy ) );
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id = self::factory()->post->create( array( 'post_type' => self::$post_type ) );
			wp_set_post_terms( $post_id, array( $term_id ), self::$taxonomy );
		}

		$this->go_to( get_term_link( $term_id ) );

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
				'text'  => get_term( $term_id )->name,
				'link'  => get_term_link( $term_id ),
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

		$this->go_to( get_term_link( $term_id ) );

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
				'text'  => get_term( $term_id )->name,
				'link'  => home_url( '/?ctax=' . get_term( $term_id )->slug ),
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
	 * ヒエラルキーのあるctaxアーカイブ
	 *
	 * 条件:
	 * - CPT投稿用ページ => CPT投稿用ページのタイトルがパンくずに追加されるはず。
	 * - 親タームあり => 親タームがパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_ctax_archive_has_hierarchical_and_page_for_cpt_posts_and_has_parent_term() {
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
					'name'     => 'Bar' . $i,
					'parent'   => $term_id,
				)
			);
		}
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id = self::factory()->post->create( array( 'post_type' => self::$post_type ) );
			wp_set_post_terms( $post_id, array( $term_id ), self::$taxonomy );
		}

		$this->go_to( get_term_link( $term_id ) );

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
				'link'  => home_url( user_trailingslashit( '/' . self::$taxonomy . '/bar0' ) ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar1',
				'link'  => home_url( user_trailingslashit( '/' . self::$taxonomy . '/bar0/bar1' ) ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar2',
				'link'  => home_url( user_trailingslashit( '/' . self::$taxonomy . '/bar0/bar1/bar2' ) ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar3',
				'link'  => home_url( user_trailingslashit( '/' . self::$taxonomy . '/bar0/bar1/bar2/bar3' ) ),
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

		$this->go_to( get_term_link( $term_id ) );

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
				'link'  => home_url( '/?' . self::$taxonomy . '=bar0' ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar1',
				'link'  => home_url( '/?' . self::$taxonomy . '=bar1' ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar2',
				'link'  => home_url( '/?' . self::$taxonomy . '=bar2' ),
				'layer' => 'parent_term',
			),
			array(
				'text'  => 'Bar3',
				'link'  => home_url( '/?' . self::$taxonomy . '=bar3' ),
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
	 * ヒエラルキーのあるctaxアーカイブ
	 *
	 * 条件:
	 * - CPT投稿用ページ => CPT投稿用ページのタイトルがパンくずに追加されるはず。
	 * - 親タームなし => 何も追加されないはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_ctax_archive_has_hierarchical_and_page_for_cpt_posts_and_no_parent_term() {
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

		$term_id = self::factory()->term->create( array( 'taxonomy' => self::$taxonomy ) );
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id = self::factory()->post->create( array( 'post_type' => self::$post_type ) );
			wp_set_post_terms( $post_id, array( $term_id ), self::$taxonomy );
		}

		$this->go_to( get_term_link( $term_id ) );

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
				'text'  => get_term( $term_id )->name,
				'link'  => get_term_link( $term_id ),
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

		$this->go_to( get_term_link( $term_id ) );

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
				'text'  => get_term( $term_id )->name,
				'link'  => home_url( '/?ctax=' . get_term( $term_id )->slug ),
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
	 * ヒエラルキーのないctaxアーカイブ
	 *
	 * 条件:
	 * - デフォルト投稿タイプ => `Posts` がパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_ctax_archive_no_hierarchical() {
		register_taxonomy(
			self::$taxonomy,
			'post',
			array(
				'public' => true,
			)
		);
		register_taxonomy_for_object_type( self::$taxonomy, 'post' );

		$term_id = self::factory()->term->create( array( 'taxonomy' => self::$taxonomy ) );
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );
			wp_set_post_terms( $post_id, array( $term_id ), self::$taxonomy );
		}

		$this->go_to( get_term_link( $term_id ) );

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
				'text'  => get_term( $term_id )->name,
				'link'  => get_term_link( $term_id ),
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

		$this->go_to( get_term_link( $term_id ) );

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
				'text'  => get_term( $term_id )->name,
				'link'  => home_url( '/?ctax=' . get_term( $term_id )->slug ),
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
	 * ヒエラルキーのないctaxアーカイブ
	 *
	 * 条件:
	 * - 投稿用ページ => 投稿用ページのタイトルがパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_ctax_archive_no_hierarchical_and_page_for_posts() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Foo',
			)
		);
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $page_id );

		register_taxonomy(
			self::$taxonomy,
			'post',
			array(
				'public' => true,
			)
		);
		register_taxonomy_for_object_type( self::$taxonomy, 'post' );

		$term_id = self::factory()->term->create( array( 'taxonomy' => self::$taxonomy ) );
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );
			wp_set_post_terms( $post_id, array( $term_id ), self::$taxonomy );
		}

		$this->go_to( get_term_link( $term_id ) );

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
				'text'  => get_term( $term_id )->name,
				'link'  => get_term_link( $term_id ),
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

		$this->go_to( get_term_link( $term_id ) );

		$expected = array(
			array(
				'text'  => 'ホーム',
				'link'  => home_url(),
				'layer' => 'home',
			),
			array(
				'text'  => 'Foo',
				'link'  => home_url( '?page_id=' . $page_id ),
				'layer' => 'post_type',
			),
			array(
				'text'  => get_term( $term_id )->name,
				'link'  => home_url( '/?ctax=' . get_term( $term_id )->slug ),
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
	 * ヒエラルキーのないctaxアーカイブ
	 *
	 * 条件:
	 * - CPT => CPT名がパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_ctax_archive_no_hierarchical_and_cpt() {
		register_taxonomy(
			self::$taxonomy,
			self::$post_type,
			array(
				'public' => true,
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

		$term_id = self::factory()->term->create( array( 'taxonomy' => self::$taxonomy ) );
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id = self::factory()->post->create( array( 'post_type' => self::$post_type ) );
			wp_set_post_terms( $post_id, array( $term_id ), self::$taxonomy );
		}

		$this->go_to( get_term_link( $term_id ) );

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
				'text'  => get_term( $term_id )->name,
				'link'  => get_term_link( $term_id ),
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

		$this->go_to( get_term_link( $term_id ) );

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
				'text'  => get_term( $term_id )->name,
				'link'  => home_url( '/?ctax=' . get_term( $term_id )->slug ),
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
	 * ヒエラルキーのないctaxアーカイブ
	 *
	 * 条件:
	 * - CPT投稿用ページ => CPT投稿用ページのタイトルがパンくずに追加されるはず。
	 *
	 * @covers ::get_the_breadcrumbs
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_breadcrumbs_with_ctax_archive_no_hierarchical_and_page_for_cpt_posts() {
		register_taxonomy(
			self::$taxonomy,
			self::$post_type,
			array(
				'public' => true,
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

		$term_id = self::factory()->term->create( array( 'taxonomy' => self::$taxonomy ) );
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id = self::factory()->post->create( array( 'post_type' => self::$post_type ) );
			wp_set_post_terms( $post_id, array( $term_id ), self::$taxonomy );
		}

		$this->go_to( get_term_link( $term_id ) );

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
				'text'  => get_term( $term_id )->name,
				'link'  => get_term_link( $term_id ),
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

		// ダーティパーマリンクに変更
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( get_term_link( $term_id ) );

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
				'text'  => get_term( $term_id )->name,
				'link'  => home_url( '/?ctax=' . get_term( $term_id )->slug ),
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
}
