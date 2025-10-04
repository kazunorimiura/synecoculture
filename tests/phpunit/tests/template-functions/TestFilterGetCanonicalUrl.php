<?php

/**
 * WPF_Template_Functions::filter_get_canonical_url のユニットテスト。
 *
 * @group template_functions
 * @covers WPF_Template_Functions
 * @coversDefaultClass WPF_Template_Functions
 */
class TestFilterGetCanonicalUrl extends WPF_UnitTestCase {

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
	 * 検索結果ページ
	 *
	 * @covers ::filter_get_canonical_url
	 * @preserveGlobalState disabled
	 */
	public function test_filter_get_canonical_url_with_search_result() {
		$this->go_to( home_url( '/?s=Foo' ) );

		$this->assertSame(
			home_url( user_trailingslashit( '/search/Foo' ) ),
			WPF_Template_Functions::filter_get_canonical_url( wp_get_canonical_url(), get_post( 0 ) )
		);
	}

	/**
	 * 投稿アーカイブ
	 *
	 * @covers ::filter_get_canonical_url
	 * @preserveGlobalState disabled
	 */
	public function test_filter_get_canonical_url_with_post_archive() {
		$this->go_to( get_post_type_archive_link( 'post' ) );

		$this->assertSame(
			home_url(), // DPTは rewrite => false なので、get_post_type_archive_link だとトレイリングスラッシュが考慮されない。
			WPF_Template_Functions::filter_get_canonical_url( wp_get_canonical_url(), get_post( 0 ) )
		);
	}

	/**
	 * CPT投稿アーカイブ
	 *
	 * @covers ::filter_get_canonical_url
	 * @preserveGlobalState disabled
	 */
	public function test_filter_get_canonical_url_with_cpt_post_archive() {
		register_post_type(
			self::$post_type,
			array(
				'public'      => true,
				'has_archive' => true,
			)
		);

		$this->go_to( get_post_type_archive_link( self::$post_type ) );

		$this->assertSame(
			home_url( user_trailingslashit( '/' . self::$post_type ) ),
			WPF_Template_Functions::filter_get_canonical_url( wp_get_canonical_url(), get_post( 0 ) )
		);
	}

	/**
	 * 日付アーカイブ
	 *
	 * @covers ::filter_get_canonical_url
	 * @preserveGlobalState disabled
	 */
	public function test_filter_get_canonical_url_with_date_archive() {
		$post_id = self::factory()->post->create(
			array(
				'post_type' => 'post',
				'post_date' => '2012-12-12',
			)
		);

		$this->go_to( home_url( user_trailingslashit( '/2012/12/12' ) ) );

		$this->assertSame(
			home_url( user_trailingslashit( '/2012/12/12' ) ),
			WPF_Template_Functions::filter_get_canonical_url( wp_get_canonical_url(), get_post( $post_id ) )
		);
	}

	/**
	 * 404エラー
	 *
	 * @covers ::filter_get_canonical_url
	 * @preserveGlobalState disabled
	 */
	public function test_filter_get_canonical_url_with_404() {
		$this->go_to( home_url( user_trailingslashit( '/blahblahblah' ) ) );

		$this->assertSame(
			false,
			WPF_Template_Functions::filter_get_canonical_url( wp_get_canonical_url(), get_post( 0 ) )
		);
	}

	/**
	 * フロントページ
	 *
	 * @covers ::filter_get_canonical_url
	 * @preserveGlobalState disabled
	 */
	public function test_filter_get_canonical_url_with_front_page() {
		$this->go_to( home_url() );

		$this->assertSame(
			home_url(),
			WPF_Template_Functions::filter_get_canonical_url( wp_get_canonical_url(), get_post( 0 ) )
		);
	}

	/**
	 * 個別投稿ページ
	 *
	 * @covers ::filter_get_canonical_url
	 * @preserveGlobalState disabled
	 */
	public function test_filter_get_canonical_url_with_post() {
		$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );

		$this->go_to( get_permalink( $post_id ) );

		$this->assertSame(
			get_permalink( $post_id ),
			WPF_Template_Functions::filter_get_canonical_url( wp_get_canonical_url(), get_post( $post_id ) )
		);
	}

	/**
	 * CPT個別投稿ページ
	 *
	 * @covers ::filter_get_canonical_url
	 * @preserveGlobalState disabled
	 */
	public function test_filter_get_canonical_url_with_cpt_post() {
		register_post_type( self::$post_type, array( 'public' => true ) );

		$post_id = self::factory()->post->create( array( 'post_type' => self::$post_type ) );

		$this->go_to( get_permalink( $post_id ) );

		$this->assertSame(
			get_permalink( $post_id ),
			WPF_Template_Functions::filter_get_canonical_url( wp_get_canonical_url(), get_post( $post_id ) )
		);
	}

	/**
	 * 固定ページ
	 *
	 * @covers ::filter_get_canonical_url
	 * @preserveGlobalState disabled
	 */
	public function test_filter_get_canonical_url_with_page() {
		$post_id = self::factory()->post->create( array( 'post_type' => 'page' ) );

		$this->go_to( get_permalink( $post_id ) );

		$this->assertSame(
			get_permalink( $post_id ),
			WPF_Template_Functions::filter_get_canonical_url( wp_get_canonical_url(), get_post( $post_id ) )
		);
	}

	/**
	 * カテゴリアーカイブ
	 *
	 * @covers ::filter_get_canonical_url
	 * @preserveGlobalState disabled
	 */
	public function test_filter_get_canonical_url_with_category_archive() {
		$cat_id  = self::factory()->category->create();
		$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );
		wp_set_post_categories( $post_id, array( $cat_id ) );

		$this->go_to( get_category_link( $cat_id ) );

		$this->assertSame(
			get_category_link( $cat_id ),
			WPF_Template_Functions::filter_get_canonical_url( wp_get_canonical_url(), get_post( 0 ) )
		);
	}

	/**
	 * タグアーカイブ
	 *
	 * @covers ::filter_get_canonical_url
	 * @preserveGlobalState disabled
	 */
	public function test_filter_get_canonical_url_with_tag_archive() {
		$tag_id  = self::factory()->tag->create();
		$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );
		wp_set_post_tags( $post_id, array( $tag_id ) );

		$this->go_to( get_tag_link( $tag_id ) );

		$this->assertSame(
			get_tag_link( $tag_id ),
			WPF_Template_Functions::filter_get_canonical_url( wp_get_canonical_url(), get_post( 0 ) )
		);
	}

	/**
	 * ctaxアーカイブ
	 *
	 * @covers ::filter_get_canonical_url
	 * @preserveGlobalState disabled
	 */
	public function test_filter_get_canonical_url_with_ctax_archive() {
		register_taxonomy( self::$taxonomy, self::$post_type );

		$term_id = self::factory()->term->create( array( 'taxonomy' => self::$taxonomy ) );
		$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );
		wp_set_post_terms( $post_id, array( $term_id ), self::$taxonomy );

		$this->go_to( get_term_link( $term_id ) );

		$this->assertSame(
			get_term_link( $term_id ),
			WPF_Template_Functions::filter_get_canonical_url( wp_get_canonical_url(), get_post( 0 ) )
		);
	}

	/**
	 * 投稿用ページ
	 *
	 * @covers ::filter_get_canonical_url
	 * @preserveGlobalState disabled
	 */
	public function test_filter_get_canonical_url_with_page_for_posts() {
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $page_id );

		$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );

		$this->go_to( get_permalink( get_option( 'page_for_posts' ) ) );

		$this->assertSame(
			get_permalink( get_option( 'page_for_posts' ) ),
			WPF_Template_Functions::filter_get_canonical_url( wp_get_canonical_url(), get_post( $page_id ) )
		);
	}

	/**
	 * CPT投稿用ページ
	 *
	 * @covers ::filter_get_canonical_url
	 * @preserveGlobalState disabled
	 */
	public function test_filter_get_canonical_url_with_page_for_cpt_posts() {
		register_post_type( self::$post_type, array( 'public' => true ) );

		$page_id = self::factory()->post->create(
			array(
				'post_type' => 'page',
				'post_name' => self::$post_type,
			)
		);

		$this->go_to( get_permalink( $page_id ) );

		$this->assertSame(
			get_permalink( $page_id ),
			WPF_Template_Functions::filter_get_canonical_url( wp_get_canonical_url(), get_post( $page_id ) )
		);
	}
}
