<?php

/**
 * WPF_Template_Tags::get_the_page_subtitle のユニットテスト。
 *
 * @group template_tags
 * @covers WPF_Template_Tags
 * @coversDefaultClass WPF_Template_Tags
 */
class TestGetThePageSubtitle extends WPF_UnitTestCase {

	public function set_up() {
		parent::set_up();

		update_option( 'show_on_front', 'posts' );
		update_option( 'page_on_front', '' );
		update_option( 'page_for_posts', '' );
	}

	public function tear_down() {
		update_option( 'show_on_front', 'posts' );
		update_option( 'page_on_front', '' );
		update_option( 'page_for_posts', '' );

		parent::tear_down();
	}

	/**
	 * 投稿アーカイブ
	 *
	 * デフォルトの動作（not 投稿用ページ）をテスト。
	 *
	 * @covers ::get_the_page_subtitle
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_page_subtitle_with_post_archive() {
		$post_ids = self::factory()->post->create_many( 10, array( 'post_type' => 'post' ) );

		$this->go_to( get_post_type_archive_link( 'post' ) );

		$this->assertSame(
			'',
			WPF_Template_Tags::get_the_page_subtitle()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'',
			WPF_Template_Tags::get_the_page_subtitle()
		);
	}

	/**
	 * CPT投稿アーカイブ
	 *
	 * デフォルトの動作（not CPT投稿用ページ）をテスト。
	 *
	 * @covers ::get_the_page_subtitle
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_page_subtitle_with_cpt_post_archive() {
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

		$this->assertSame(
			'',
			WPF_Template_Tags::get_the_page_subtitle()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'',
			WPF_Template_Tags::get_the_page_subtitle()
		);
	}

	/**
	 * 日付アーカイブ
	 *
	 * デフォルトの動作（not 投稿用ページ）をテスト。
	 *
	 * @covers ::get_the_page_subtitle
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_page_subtitle_with_date_archive() {
		$post_ids = self::factory()->post->create_many(
			10,
			array(
				'post_type' => 'post',
				'post_date' => '2012-12-12',
			)
		);

		$this->go_to( home_url( user_trailingslashit( '/2012/12/12' ) ) );

		$this->assertSame(
			'',
			WPF_Template_Tags::get_the_page_subtitle()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'',
			WPF_Template_Tags::get_the_page_subtitle()
		);
	}

	/**
	 * CPT日付アーカイブ
	 *
	 * デフォルトの動作（not CPT投稿用ページ）をテスト。
	 *
	 * @covers ::get_the_page_subtitle
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_page_subtitle_with_cpt_date_archive() {
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

		$this->assertSame(
			'',
			WPF_Template_Tags::get_the_page_subtitle()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'',
			WPF_Template_Tags::get_the_page_subtitle()
		);
	}

	/**
	 * 404エラー
	 *
	 * デフォルトの動作（not 投稿用ページ）をテスト。
	 *
	 * @covers ::get_the_page_subtitle
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_page_subtitle_with_404() {
		$this->go_to( home_url( user_trailingslashit( '/blahblahblah' ) ) );

		$this->assertSame(
			__( 'お探しのページは見つかりませんでした。', 'wordpressfoundation' ),
			WPF_Template_Tags::get_the_page_subtitle()
		);
	}

	/**
	 *  ホームページを「最新の投稿」（デフォルト）に設定している場合のフロントページ
	 *
	 * デフォルトの動作（not 投稿用ページ）をテスト。
	 *
	 * @covers ::get_the_page_subtitle
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_page_subtitle_with_front_page() {
		$this->go_to( home_url() );

		$this->assertSame(
			'',
			WPF_Template_Tags::get_the_page_subtitle()
		);
	}

	/**
	 * 個別投稿ページ
	 *
	 * デフォルトの動作（not 投稿用ページ）をテスト。
	 *
	 * @covers ::get_the_page_subtitle
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_page_subtitle_with_post() {
		$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );
		update_post_meta( $post_id, '_wpf_subtitle', 'Foo' );

		$this->go_to( get_permalink( $post_id ) );

		$this->assertSame(
			'Foo',
			WPF_Template_Tags::get_the_page_subtitle()
		);
	}

	/**
	 * CPT個別投稿ページ
	 *
	 * デフォルトの動作（not CPT投稿用ページ）をテスト。
	 *
	 * @covers ::get_the_page_subtitle
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_page_subtitle_with_cpt_post() {
		register_post_type( self::$post_type, array( 'public' => true ) );

		$post_id = self::factory()->post->create( array( 'post_type' => self::$post_type ) );
		update_post_meta( $post_id, '_wpf_subtitle', 'Foo' );

		$this->go_to( get_permalink( $post_id ) );

		$this->assertSame(
			'Foo',
			WPF_Template_Tags::get_the_page_subtitle()
		);
	}

	/**
	 * 固定ページ
	 *
	 * @covers ::get_the_page_subtitle
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_page_subtitle_with_page() {
		$post_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_post_meta( $post_id, '_wpf_subtitle', 'Foo' );

		$this->go_to( get_permalink( $post_id ) );

		$this->assertSame(
			'Foo',
			WPF_Template_Tags::get_the_page_subtitle()
		);
	}

	/**
	 * カテゴリアーカイブ
	 *
	 * デフォルトの動作（not 投稿用ページ）をテスト。
	 *
	 * @covers ::get_the_page_subtitle
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_page_subtitle_with_category_archive() {
		$cat_id   = self::factory()->category->create(
			array(
				'name'        => 'Foo',
				'description' => 'Bar',
			)
		);
		$post_ids = self::factory()->post->create_many( 10, array( 'post_type' => 'post' ) );
		for ( $i = 0; $i < 9; $i++ ) {
			wp_set_post_categories( $post_ids[ $i ], array( $cat_id ) );
		}

		$this->go_to( get_category_link( $cat_id ) );

		$this->assertSame(
			'Bar',
			WPF_Template_Tags::get_the_page_subtitle()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'Bar',
			WPF_Template_Tags::get_the_page_subtitle()
		);
	}

	/**
	 * タグアーカイブ
	 *
	 * デフォルトの動作（not 投稿用ページ）をテスト。
	 *
	 * @covers ::get_the_page_subtitle
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_page_subtitle_with_tag_archive() {
		$tag_id   = self::factory()->tag->create(
			array(
				'description' => 'Foo',
			)
		);
		$post_ids = self::factory()->post->create_many( 10, array( 'post_type' => 'post' ) );
		for ( $i = 0; $i < 9; $i++ ) {
			wp_set_post_tags( $post_ids[ $i ], array( $tag_id ) );
		}

		$this->go_to( get_tag_link( $tag_id ) );

		$this->assertSame(
			'Foo',
			WPF_Template_Tags::get_the_page_subtitle()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'Foo',
			WPF_Template_Tags::get_the_page_subtitle()
		);
	}

	/**
	 * ctaxアーカイブ（ヒエラルキーなし）
	 *
	 * デフォルトの動作（not CPT投稿用ページ）をテスト。
	 *
	 * @covers ::get_the_page_subtitle
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_page_subtitle_with_ctax_archive() {
		register_taxonomy( self::$taxonomy, 'post', array( 'public' => true ) );

		$term_id  = self::factory()->term->create(
			array(
				'taxonomy'    => self::$taxonomy,
				'description' => 'Foo',
			)
		);
		$post_ids = self::factory()->post->create_many( 10, array( 'post_type' => 'post' ) );
		for ( $i = 0; $i < 9; $i++ ) {
			wp_set_post_terms( $post_ids[ $i ], array( $term_id ), self::$taxonomy );
		}

		$this->go_to( get_term_link( $term_id ) );

		$this->assertSame(
			'Foo',
			WPF_Template_Tags::get_the_page_subtitle()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'Foo',
			WPF_Template_Tags::get_the_page_subtitle()
		);
	}

	/**
	 * ctaxアーカイブ（ヒエラルキーあり）
	 *
	 * デフォルトの動作（not CPT投稿用ページ）をテスト。
	 *
	 * @covers ::get_the_page_subtitle
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_page_subtitle_with_ctax_archive_has_hierarchical() {
		register_taxonomy(
			self::$taxonomy,
			'post',
			array(
				'public'       => true,
				'hierarchical' => true,
			)
		);

		$term_id  = self::factory()->term->create(
			array(
				'taxonomy'    => self::$taxonomy,
				'name'        => 'Foo',
				'description' => 'Bar',
			)
		);
		$post_ids = self::factory()->post->create_many( 10, array( 'post_type' => 'post' ) );
		for ( $i = 0; $i < 9; $i++ ) {
			wp_set_post_terms( $post_ids[ $i ], array( $term_id ), self::$taxonomy );
		}

		$this->go_to( get_term_link( $term_id ) );

		$this->assertSame(
			'Bar',
			WPF_Template_Tags::get_the_page_subtitle()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'Bar',
			WPF_Template_Tags::get_the_page_subtitle()
		);
	}

	/**
	 * 著者ページ
	 *
	 * @covers ::get_the_page_subtitle
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_page_subtitle_with_author_page() {
		$user_id  = self::factory()->user->create(
			array(
				'user_login'   => 'johndoe',
				'display_name' => 'John Doe',
			)
		);
		$post_ids = self::factory()->post->create_many(
			10,
			array(
				'post_type'   => 'post',
				'post_author' => $user_id,
			)
		);

		$this->go_to( get_author_posts_url( $user_id ) );

		$this->assertSame(
			'',
			WPF_Template_Tags::get_the_page_subtitle()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'',
			WPF_Template_Tags::get_the_page_subtitle()
		);
	}

	/**
	 * 検索結果ページ
	 *
	 * @covers ::get_the_page_subtitle
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_page_subtitle_with_search_results() {
		$post_ids = self::factory()->post->create_many(
			10,
			array(
				'post_type'  => 'post',
				'post_title' => 'Foo',
			)
		);

		$this->go_to( home_url( user_trailingslashit( '/search/foo' ) ) );

		$this->assertSame(
			'',
			WPF_Template_Tags::get_the_page_subtitle()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'',
			WPF_Template_Tags::get_the_page_subtitle()
		);
	}

	/**
	 * 投稿用ページ
	 *
	 * @covers ::get_the_page_subtitle
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_page_subtitle_with_page_for_posts() {
		$page_id = self::factory()->post->create(
			array(
				'post_type' => 'page',
			)
		);
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $page_id );
		update_post_meta( $page_id, '_wpf_subtitle', 'Foo' );

		$post_ids = self::factory()->post->create_many( 10, array( 'post_type' => 'post' ) );

		$this->go_to( get_post_type_archive_link( 'post' ) );

		$this->assertSame(
			'Foo',
			WPF_Template_Tags::get_the_page_subtitle()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'Foo',
			WPF_Template_Tags::get_the_page_subtitle()
		);
	}

	/**
	 * 投稿用ページの日付アーカイブ
	 *
	 * @covers ::get_the_page_subtitle
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_page_subtitle_with_page_for_posts_and_date_archive() {
		$page_id = self::factory()->post->create(
			array(
				'post_type' => 'page',
			)
		);
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $page_id );
		update_post_meta( $page_id, '_wpf_subtitle', 'Foo' );

		$post_ids = self::factory()->post->create_many(
			10,
			array(
				'post_type' => 'post',
				'post_date' => '2012-12-12',
			)
		);

		$this->go_to( home_url( user_trailingslashit( '/2012/12/12' ) ) );

		$this->assertSame(
			'Foo',
			WPF_Template_Tags::get_the_page_subtitle()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'Foo',
			WPF_Template_Tags::get_the_page_subtitle()
		);
	}

	/**
	 * 投稿用ページを設定している場合の404エラー
	 *
	 * @covers ::get_the_page_subtitle
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_page_subtitle_with_page_for_posts_and_404() {
		$page_id = self::factory()->post->create(
			array(
				'post_type' => 'page',
			)
		);
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $page_id );
		update_post_meta( $page_id, '_wpf_subtitle', 'Foo' );

		$this->go_to( home_url( user_trailingslashit( '/blahblahblah' ) ) );

		$this->assertSame(
			__( 'お探しのページは見つかりませんでした。', 'wordpressfoundation' ),
			WPF_Template_Tags::get_the_page_subtitle()
		);
	}

	/**
	 * ホームページを「固定ページ」に設定している場合のフロントページ
	 *
	 * @covers ::get_the_page_subtitle
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_page_subtitle_with_page_on_front() {
		$page_id = self::factory()->post->create(
			array(
				'post_type' => 'page',
			)
		);
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $page_id );
		update_post_meta( $page_id, '_wpf_subtitle', 'Foo' );

		$this->go_to( home_url() );

		$this->assertSame(
			'Foo',
			WPF_Template_Tags::get_the_page_subtitle()
		);
	}

	/**
	 * 投稿用ページのカテゴリアーカイブ
	 *
	 * @covers ::get_the_page_subtitle
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_page_subtitle_with_page_for_posts_and_category_archive() {
		$page_id = self::factory()->post->create(
			array(
				'post_type' => 'page',
			)
		);
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $page_id );
		update_post_meta( $page_id, '_wpf_subtitle', 'Foo' );

		$cat_id   = self::factory()->category->create( array( 'description' => 'Bal' ) );
		$post_ids = self::factory()->post->create_many( 10, array( 'post_type' => 'post' ) );
		for ( $i = 0; $i < 9; $i++ ) {
			wp_set_post_categories( $post_ids[ $i ], array( $cat_id ) );
		}

		$this->go_to( get_category_link( $cat_id ) );

		$this->assertSame(
			'Bal', // NOTE: カテゴリアーカイブは、ドメインの評価が上がった時の検索流入インパクトが大きいため、投稿用ページが設定されていてもカテゴリデスクリプションを表示する。
			WPF_Template_Tags::get_the_page_subtitle()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'Bal',
			WPF_Template_Tags::get_the_page_subtitle()
		);
	}

	/**
	 * 投稿用ページのタグアーカイブ
	 *
	 * @covers ::get_the_page_subtitle
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_page_subtitle_with_page_for_posts_and_tag_archive() {
		$page_id = self::factory()->post->create(
			array(
				'post_type' => 'page',
			)
		);
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $page_id );
		update_post_meta( $page_id, '_wpf_subtitle', 'Foo' );

		$tag_id   = self::factory()->tag->create( array( 'description' => 'Bar' ) );
		$post_ids = self::factory()->post->create_many( 10, array( 'post_type' => 'post' ) );
		for ( $i = 0; $i < 9; $i++ ) {
			wp_set_post_tags( $post_ids[ $i ], array( $tag_id ) );
		}

		$this->go_to( get_tag_link( $tag_id ) );

		$this->assertSame(
			'Foo',
			WPF_Template_Tags::get_the_page_subtitle()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'Foo',
			WPF_Template_Tags::get_the_page_subtitle()
		);
	}

	/**
	 * 投稿用ページのctaxアーカイブ（ヒエラルキーなし）
	 *
	 * デフォルトの動作（not CPT投稿用ページ）をテスト。
	 *
	 * @covers ::get_the_page_subtitle
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_page_subtitle_with_page_for_posts_and_ctax_archive() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Foo',
			)
		);
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $page_id );
		update_post_meta( $page_id, '_wpf_subtitle', 'Bal' );

		register_taxonomy( self::$taxonomy, 'post', array( 'public' => true ) );

		$term_id  = self::factory()->term->create(
			array(
				'taxonomy'    => self::$taxonomy,
				'description' => 'Bar',
			)
		);
		$post_ids = self::factory()->post->create_many( 10, array( 'post_type' => 'post' ) );
		for ( $i = 0; $i < 9; $i++ ) {
			wp_set_post_terms( $post_ids[ $i ], array( $term_id ), self::$taxonomy );
		}

		$this->go_to( get_term_link( $term_id ) );

		$this->assertSame(
			'Bal',
			WPF_Template_Tags::get_the_page_subtitle()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'Bal',
			WPF_Template_Tags::get_the_page_subtitle()
		);
	}

	/**
	 * 投稿用ページのctaxアーカイブ（ヒエラルキーあり）
	 *
	 * @covers ::get_the_page_subtitle
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_page_subtitle_with_page_for_posts_and_ctax_archive_has_hierarchical() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Foo',
			)
		);
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $page_id );
		update_post_meta( $page_id, '_wpf_subtitle', 'Bal' );

		register_taxonomy(
			self::$taxonomy,
			'post',
			array(
				'public'       => true,
				'hierarchical' => true,
			)
		);

		$term_id  = self::factory()->term->create(
			array(
				'taxonomy'    => self::$taxonomy,
				'description' => 'Bar',
			)
		);
		$post_ids = self::factory()->post->create_many( 10, array( 'post_type' => 'post' ) );
		for ( $i = 0; $i < 9; $i++ ) {
			wp_set_post_terms( $post_ids[ $i ], array( $term_id ), self::$taxonomy );
		}

		$this->go_to( get_term_link( $term_id ) );

		$this->assertSame(
			'Bar', // NOTE: ヒエラルキーがあるctaxは大分類（カテゴリ）とみなし、ドメインの評価が上がった時の検索流入インパクトが大きいため、投稿用ページが設定されていてもタームデスクリプションを表示する。
			WPF_Template_Tags::get_the_page_subtitle()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'Bar',
			WPF_Template_Tags::get_the_page_subtitle()
		);
	}

	/**
	 * CPT投稿用ページ
	 *
	 * @covers ::get_the_page_subtitle
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_page_subtitle_with_page_for_cpt_posts() {
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
				'post_title' => 'Foo',
				'post_name'  => self::$post_type,
			)
		);
		update_post_meta( $page_id, '_wpf_subtitle', 'Bar' );

		$post_ids = self::factory()->post->create_many( 10, array( 'post_type' => self::$post_type ) );

		$this->go_to( get_post_type_archive_link( self::$post_type ) );

		$this->assertSame(
			'Bar',
			WPF_Template_Tags::get_the_page_subtitle()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'Bar',
			WPF_Template_Tags::get_the_page_subtitle()
		);
	}

	/**
	 * CPT投稿用ページのCPT日付アーカイブ
	 *
	 * デフォルトの動作（not CPT投稿用ページ）をテスト。
	 *
	 * @covers ::get_the_page_subtitle
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_page_subtitle_with_page_for_cpt_posts_and_cpt_date_archive() {
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
				'post_title' => 'Foo',
				'post_name'  => self::$post_type,
			)
		);
		update_post_meta( $page_id, '_wpf_subtitle', 'Bar' );

		$post_ids = self::factory()->post->create_many(
			10,
			array(
				'post_type' => self::$post_type,
				'post_date' => '2012-12-12',
			)
		);

		$this->go_to( home_url( user_trailingslashit( '/' . self::$post_type . '/date/2012/12/12' ) ) );

		$this->assertSame(
			'Bar',
			WPF_Template_Tags::get_the_page_subtitle()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'Bar',
			WPF_Template_Tags::get_the_page_subtitle()
		);
	}
}
