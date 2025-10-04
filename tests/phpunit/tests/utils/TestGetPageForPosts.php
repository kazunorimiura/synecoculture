<?php

/**
 * WPF_Utils::get_page_for_posts のユニットテスト。
 *
 * @group utils
 * @covers WPF_Utils
 * @coversDefaultClass WPF_Utils
 */
class TestGetPageForPosts extends WPF_UnitTestCase {
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
	 * @covers ::get_page_for_posts
	 * @preserveGlobalState disabled
	 */
	public function test_get_page_for_posts_with_cpt() {
		register_post_type( self::$post_type, array( 'public' => true ) );
		$page_id = self::factory()->post->create(
			array(
				'post_type' => 'page',
				'post_name' => self::$post_type,
			)
		);

		$this->assertSame( $page_id, WPF_Utils::get_page_for_posts( self::$post_type ) );
	}

	/**
	 * @covers ::get_page_for_posts
	 * @preserveGlobalState disabled
	 */
	public function test_get_page_for_posts_with_dpt() {
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $page_id );

		$this->assertSame( $page_id, WPF_Utils::get_page_for_posts( 'post' ) );
	}

	/**
	 * @covers ::get_page_for_posts
	 * @preserveGlobalState disabled
	 */
	public function test_get_page_for_posts_with_unset_page_for_posts() {
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', '' );

		$this->assertSame( 0, WPF_Utils::get_page_for_posts( 'post' ) );
	}

	/**
	 * 現在のクエリがアーカイブページの場合、投稿用ページのIDを取得できるか。
	 *
	 * @covers ::get_page_for_posts
	 * @preserveGlobalState disabled
	 */
	public function test_get_page_for_posts_with_post_archive() {
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $page_id );
		self::factory()->post->create( array( 'post_type' => 'post' ) );

		$this->go_to( get_post_type_archive_link( 'post' ) );

		$this->assertSame( $page_id, WPF_Utils::get_page_for_posts() );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( get_post_type_archive_link( 'post' ) );

		$this->assertSame( $page_id, WPF_Utils::get_page_for_posts() );
	}

	/**
	 * 現在のクエリが個別投稿ページの場合、投稿用ページのIDを取得できるか。
	 *
	 * @covers ::get_page_for_posts
	 * @preserveGlobalState disabled
	 */
	public function test_get_page_for_posts_with_post() {
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $page_id );
		$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );

		$this->go_to( get_permalink( $post_id ) );

		$this->assertSame( $page_id, WPF_Utils::get_page_for_posts() );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( get_permalink( $post_id ) );

		$this->assertSame( $page_id, WPF_Utils::get_page_for_posts() );
	}

	/**
	 * 現在のクエリがCPT投稿アーカイブの場合、CPT投稿用ページのIDを取得できるか。
	 *
	 * @covers ::get_page_for_posts
	 * @preserveGlobalState disabled
	 */
	public function test_get_page_for_posts_with_cpt_post_archive() {
		register_post_type( self::$post_type, array( 'public' => true ) );
		$page_id = self::factory()->post->create(
			array(
				'post_type' => 'page',
				'post_name' => self::$post_type,
			)
		);
		self::factory()->post->create( array( 'post_type' => self::$post_type ) );

		$this->go_to( home_url( user_trailingslashit( '/' . self::$post_type ) ) );

		$this->assertQueryTrue( 'is_page', 'is_singular' );

		$this->assertSame( $page_id, WPF_Utils::get_page_for_posts() );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( home_url( '/?post_type=' . self::$post_type ) );

		// HACK: UglyパーマリンクにおけるCPT投稿アーカイブページでは、テスト環境においてのみクエリが壊れているようなので強制的にクエリをセット。
		global $wp_query;
		$wp_query->is_front_page        = false;
		$wp_query->is_home              = false;
		$wp_query->is_post_type_archive = true;

		$this->assertSame( $page_id, WPF_Utils::get_page_for_posts() );
	}

	/**
	 * 現在のクエリがCPT個別投稿ページの場合、CPT投稿用ページのIDを取得できるか。
	 *
	 * @covers ::get_page_for_posts
	 * @preserveGlobalState disabled
	 */
	public function test_get_page_for_posts_with_cpt_post() {
		register_post_type( self::$post_type, array( 'public' => true ) );
		$page_id = self::factory()->post->create(
			array(
				'post_type' => 'page',
				'post_name' => self::$post_type,
			)
		);
		$post_id = self::factory()->post->create( array( 'post_type' => self::$post_type ) );

		$this->go_to( get_permalink( $post_id ) );

		$this->assertSame( $page_id, WPF_Utils::get_page_for_posts() );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( get_permalink( $post_id ) );

		$this->assertSame( $page_id, WPF_Utils::get_page_for_posts() );
	}

	/**
	 * 現在のクエリが投稿用ページの場合、そのIDが返るか。
	 *
	 * @covers ::get_page_for_posts
	 * @preserveGlobalState disabled
	 */
	public function test_get_page_for_posts_with_page_for_posts() {
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $page_id );

		$this->go_to( get_post_type_archive_link( 'post' ) );

		$this->assertSame( $page_id, WPF_Utils::get_page_for_posts() );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( get_post_type_archive_link( 'post' ) );

		$this->assertSame( $page_id, WPF_Utils::get_page_for_posts() );
	}

	/**
	 * 現在のクエリがCPT投稿用ページの場合、そのIDが返るか。
	 *
	 * @covers ::get_page_for_posts
	 * @preserveGlobalState disabled
	 */
	public function test_get_page_for_posts_with_page_for_cpt_posts() {
		register_post_type( self::$post_type, array( 'public' => true ) );
		$page_id = self::factory()->post->create(
			array(
				'post_type' => 'page',
				'post_name' => self::$post_type,
			)
		);

		$this->go_to( get_permalink( $page_id ) );

		$this->assertSame( $page_id, WPF_Utils::get_page_for_posts() );
	}
}
