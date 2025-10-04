<?php

/**
 * WPF_Template_Tags::get_the_post_type_display_name のユニットテスト。
 *
 * @group template_tags
 * @covers WPF_Template_Tags
 * @coversDefaultClass WPF_Template_Tags
 */
class TestGetThePostTypeDisplayName extends WPF_UnitTestCase {

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
	 * 投稿アーカイブ（投稿用ページが設定されている場合）
	 *
	 * @covers ::get_the_post_type_display_name
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_post_type_display_name_with_page_for_posts() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Foo',
			)
		);
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $page_id );

		$post_ids = self::factory()->post->create_many( 10, array( 'post_type' => 'post' ) );

		$this->go_to( get_post_type_archive_link( 'post' ) );

		$this->assertSame(
			'Foo',
			WPF_Template_Tags::get_the_post_type_display_name()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'Foo',
			WPF_Template_Tags::get_the_post_type_display_name()
		);
	}

	/**
	 * 投稿アーカイブ（投稿用ページが未設定の場合）
	 *
	 * @covers ::get_the_post_type_display_name
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_post_type_display_name_with_post_archive() {
		$post_ids = self::factory()->post->create_many( 10, array( 'post_type' => 'post' ) );

		$this->go_to( get_post_type_archive_link( 'post' ) );

		$this->assertSame(
			'Posts',
			WPF_Template_Tags::get_the_post_type_display_name()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'Posts',
			WPF_Template_Tags::get_the_post_type_display_name()
		);
	}

	/**
	 * 日付アーカイブ（投稿用ページが設定されている場合）
	 *
	 * @covers ::get_the_post_type_display_name
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_post_type_display_name_with_page_for_posts_and_date_archive() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Foo',
			)
		);
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $page_id );

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
			WPF_Template_Tags::get_the_post_type_display_name()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'Foo',
			WPF_Template_Tags::get_the_post_type_display_name()
		);
	}

	/**
	 * 日付アーカイブ（投稿用ページが未設定の場合）
	 *
	 * @covers ::get_the_post_type_display_name
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_post_type_display_name_with_date_archive() {
		$post_ids = self::factory()->post->create_many(
			10,
			array(
				'post_type' => 'post',
				'post_date' => '2012-12-12',
			)
		);

		$this->go_to( home_url( user_trailingslashit( '/2012/12/12' ) ) );

		$this->assertSame(
			'Posts',
			WPF_Template_Tags::get_the_post_type_display_name()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'Posts',
			WPF_Template_Tags::get_the_post_type_display_name()
		);
	}

	/**
	 * カテゴリアーカイブ（投稿用ページが設定されている場合）
	 *
	 * @covers ::get_the_post_type_display_name
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_post_type_display_name_with_page_for_posts_and_category_archive() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Foo',
			)
		);
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $page_id );

		$cat_id   = self::factory()->category->create( array( 'name' => 'Bar' ) );
		$post_ids = self::factory()->post->create_many( 10, array( 'post_type' => 'post' ) );
		for ( $i = 0; $i < 9; $i++ ) {
			wp_set_post_categories( $post_ids[ $i ], array( $cat_id ) );
		}

		$this->go_to( get_category_link( $cat_id ) );

		$this->assertSame(
			'Foo',
			WPF_Template_Tags::get_the_post_type_display_name()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'Foo',
			WPF_Template_Tags::get_the_post_type_display_name()
		);
	}

	/**
	 * カテゴリアーカイブ（投稿用ページが未設定の場合）
	 *
	 * @covers ::get_the_post_type_display_name
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_post_type_display_name_with_category_archive() {
		$cat_id   = self::factory()->category->create( array( 'name' => 'Foo' ) );
		$post_ids = self::factory()->post->create_many( 10, array( 'post_type' => 'post' ) );
		for ( $i = 0; $i < 9; $i++ ) {
			wp_set_post_categories( $post_ids[ $i ], array( $cat_id ) );
		}

		$this->go_to( get_category_link( $cat_id ) );

		$this->assertSame(
			'Posts',
			WPF_Template_Tags::get_the_post_type_display_name()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'Posts',
			WPF_Template_Tags::get_the_post_type_display_name()
		);
	}

	/**
	 * タグアーカイブ（投稿用ページが設定されている場合）
	 *
	 * @covers ::get_the_post_type_display_name
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_post_type_display_name_with_page_for_posts_and_tag_archive() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Foo',
			)
		);
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $page_id );

		$tag_id   = self::factory()->tag->create( array( 'name' => 'Bar' ) );
		$post_ids = self::factory()->post->create_many( 10, array( 'post_type' => 'post' ) );
		for ( $i = 0; $i < 9; $i++ ) {
			wp_set_post_tags( $post_ids[ $i ], array( $tag_id ) );
		}

		$this->go_to( get_tag_link( $tag_id ) );

		$this->assertSame(
			'Foo',
			WPF_Template_Tags::get_the_post_type_display_name()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'Foo',
			WPF_Template_Tags::get_the_post_type_display_name()
		);
	}

	/**
	 * タグアーカイブ（投稿用ページが未設定の場合）
	 *
	 * @covers ::get_the_post_type_display_name
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_post_type_display_name_with_tag_archive() {
		$tag_id   = self::factory()->tag->create( array( 'name' => 'Foo' ) );
		$post_ids = self::factory()->post->create_many( 10, array( 'post_type' => 'post' ) );
		for ( $i = 0; $i < 9; $i++ ) {
			wp_set_post_tags( $post_ids[ $i ], array( $tag_id ) );
		}

		$this->go_to( get_tag_link( $tag_id ) );

		$this->assertSame(
			'Posts',
			WPF_Template_Tags::get_the_post_type_display_name()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'Posts',
			WPF_Template_Tags::get_the_post_type_display_name()
		);
	}

	/**
	 * ヒエラルキーなしのctaxアーカイブ（投稿用ページが設定されている場合）
	 *
	 * @covers ::get_the_post_type_display_name
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_post_type_display_name_with_page_for_posts_and_ctax_archive() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Foo',
			)
		);
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $page_id );

		register_taxonomy( self::$taxonomy, 'post', array( 'public' => true ) );

		$term_id  = self::factory()->term->create(
			array(
				'taxonomy' => self::$taxonomy,
				'name'     => 'Bar',
			)
		);
		$post_ids = self::factory()->post->create_many( 10, array( 'post_type' => 'post' ) );
		for ( $i = 0; $i < 9; $i++ ) {
			wp_set_post_terms( $post_ids[ $i ], array( $term_id ), self::$taxonomy );
		}

		$this->go_to( get_term_link( $term_id ) );

		$this->assertSame(
			'Foo',
			WPF_Template_Tags::get_the_post_type_display_name()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'Foo',
			WPF_Template_Tags::get_the_post_type_display_name()
		);
	}

	/**
	 * ヒエラルキーなしのctaxアーカイブ（投稿用ページが未設定の場合）
	 *
	 * @covers ::get_the_post_type_display_name
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_post_type_display_name_with_ctax_archive() {
		register_taxonomy( self::$taxonomy, 'post', array( 'public' => true ) );

		$term_id  = self::factory()->term->create(
			array(
				'taxonomy' => self::$taxonomy,
				'name'     => 'Foo',
			)
		);
		$post_ids = self::factory()->post->create_many( 10, array( 'post_type' => 'post' ) );
		for ( $i = 0; $i < 9; $i++ ) {
			wp_set_post_terms( $post_ids[ $i ], array( $term_id ), self::$taxonomy );
		}

		$this->go_to( get_term_link( $term_id ) );

		$this->assertSame(
			'Posts',
			WPF_Template_Tags::get_the_post_type_display_name()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'Posts',
			WPF_Template_Tags::get_the_post_type_display_name()
		);
	}

	/**
	 * ヒエラルキーありのctaxアーカイブ（投稿用ページが設定されている場合）
	 *
	 * @covers ::get_the_post_type_display_name
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_post_type_display_name_with_page_for_posts_and_ctax_archive_has_hierarchical() {
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
			)
		);

		$term_id  = self::factory()->term->create(
			array(
				'taxonomy' => self::$taxonomy,
				'name'     => 'Bar',
			)
		);
		$post_ids = self::factory()->post->create_many( 10, array( 'post_type' => 'post' ) );
		for ( $i = 0; $i < 9; $i++ ) {
			wp_set_post_terms( $post_ids[ $i ], array( $term_id ), self::$taxonomy );
		}

		$this->go_to( get_term_link( $term_id ) );

		$this->assertSame(
			'Foo',
			WPF_Template_Tags::get_the_post_type_display_name()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'Foo',
			WPF_Template_Tags::get_the_post_type_display_name()
		);
	}

	/**
	 * ヒエラルキーありのctaxアーカイブ（投稿用ページが未設定の場合）
	 *
	 * @covers ::get_the_post_type_display_name
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_post_type_display_name_with_ctax_archive_has_hierarchical() {
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
				'taxonomy' => self::$taxonomy,
				'name'     => 'Foo',
			)
		);
		$post_ids = self::factory()->post->create_many( 10, array( 'post_type' => 'post' ) );
		for ( $i = 0; $i < 9; $i++ ) {
			wp_set_post_terms( $post_ids[ $i ], array( $term_id ), self::$taxonomy );
		}

		$this->go_to( get_term_link( $term_id ) );

		$this->assertSame(
			'Posts',
			WPF_Template_Tags::get_the_post_type_display_name()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'Posts',
			WPF_Template_Tags::get_the_post_type_display_name()
		);
	}

	/**
	 * CPT投稿アーカイブ（投稿用ページが設定されている場合）
	 *
	 * @covers ::get_the_post_type_display_name
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_post_type_display_name_with_page_for_cpt_posts() {
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

		$post_ids = self::factory()->post->create_many( 10, array( 'post_type' => self::$post_type ) );

		$this->go_to( get_post_type_archive_link( self::$post_type ) );

		$this->assertSame(
			'Bar',
			WPF_Template_Tags::get_the_post_type_display_name()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'Bar',
			WPF_Template_Tags::get_the_post_type_display_name()
		);
	}

	/**
	 * CPT投稿アーカイブ（投稿用ページが未設定の場合）
	 *
	 * @covers ::get_the_post_type_display_name
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_post_type_display_name_with_cpt_post_archive() {
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
			'Foo',
			WPF_Template_Tags::get_the_post_type_display_name()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'Foo',
			WPF_Template_Tags::get_the_post_type_display_name()
		);
	}

	/**
	 * CPT日付アーカイブ（投稿用ページが設定されている場合）
	 *
	 * @covers ::get_the_post_type_display_name
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_post_type_display_name_with_page_for_cpt_posts_and_cpt_date_archive() {
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
			WPF_Template_Tags::get_the_post_type_display_name()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'Bar',
			WPF_Template_Tags::get_the_post_type_display_name()
		);
	}

	/**
	 * CPT日付アーカイブ（投稿用ページが未設定の場合）
	 *
	 * @covers ::get_the_post_type_display_name
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_post_type_display_name_with_cpt_date_archive() {
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
			'Foo',
			WPF_Template_Tags::get_the_post_type_display_name()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'Foo',
			WPF_Template_Tags::get_the_post_type_display_name()
		);
	}

	/**
	 * ヒエラルキーありのctaxアーカイブ（CPT投稿用ページが設定されている場合）
	 *
	 * @covers ::get_the_post_type_display_name
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_post_type_display_name_with_page_for_cpt_posts_and_ctax_archive_has_hierarchical() {
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

		register_taxonomy(
			self::$taxonomy,
			self::$post_type,
			array(
				'public'       => true,
				'hierarchical' => true,
			)
		);

		$term_id  = self::factory()->term->create(
			array(
				'taxonomy' => self::$taxonomy,
				'name'     => 'Bal',
			)
		);
		$post_ids = self::factory()->post->create_many( 10, array( 'post_type' => self::$post_type ) );
		for ( $i = 0; $i < 9; $i++ ) {
			wp_set_post_terms( $post_ids[ $i ], array( $term_id ), self::$taxonomy );
		}

		$this->go_to( get_term_link( $term_id ) );

		$this->assertSame(
			'Bar',
			WPF_Template_Tags::get_the_post_type_display_name()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'Bar',
			WPF_Template_Tags::get_the_post_type_display_name()
		);
	}

	/**
	 * ヒエラルキーありのctaxアーカイブ（CPT投稿用ページが未設定の場合）
	 *
	 * @covers ::get_the_post_type_display_name
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_post_type_display_name_with_cpt_ctax_archive_has_hierarchical() {
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

		register_taxonomy(
			self::$taxonomy,
			self::$post_type,
			array(
				'public'       => true,
				'hierarchical' => true,
			)
		);

		$term_id  = self::factory()->term->create(
			array(
				'taxonomy' => self::$taxonomy,
				'name'     => 'Bal',
			)
		);
		$post_ids = self::factory()->post->create_many( 10, array( 'post_type' => self::$post_type ) );
		for ( $i = 0; $i < 9; $i++ ) {
			wp_set_post_terms( $post_ids[ $i ], array( $term_id ), self::$taxonomy );
		}

		$this->go_to( get_term_link( $term_id ) );

		$this->assertSame(
			'Foo',
			WPF_Template_Tags::get_the_post_type_display_name()
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertSame(
			'Foo',
			WPF_Template_Tags::get_the_post_type_display_name()
		);
	}
}
