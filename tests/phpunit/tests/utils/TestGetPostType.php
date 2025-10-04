<?php

/**
 * WPF_Utils::get_post_type のユニットテスト。
 *
 * @group utils
 * @covers WPF_Utils
 * @coversDefaultClass WPF_Utils
 */
class TestGetPostType extends WPF_UnitTestCase {

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
	 * @covers ::get_post_type
	 * @preserveGlobalState disabled
	 */
	public function test_get_post_type_with_home() {
		$test_contents = self::create_cpt_test_contents();

		$this->go_to( get_home_url() );
		$this->assertQueryTrue( 'is_front_page', 'is_home' );
		$this->assertSame( 'post', WPF_Utils::get_post_type() );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( get_home_url() );
		$this->assertQueryTrue( 'is_front_page', 'is_home' );
		$this->assertSame( 'post', WPF_Utils::get_post_type() );
	}

	/**
	 * @covers ::get_post_type
	 * @preserveGlobalState disabled
	 */
	public function test_get_post_type_with_home_and_page_on_front() {
		$test_contents = self::create_cpt_test_contents();

		// フロントページを固定ページに設定する。
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $page_id );

		$this->go_to( get_home_url() );
		$this->assertQueryTrue( 'is_front_page', 'is_page', 'is_singular' );
		$this->assertSame( 'page', WPF_Utils::get_post_type() );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( get_home_url() );
		$this->assertQueryTrue( 'is_front_page', 'is_page', 'is_singular' );
		$this->assertSame( 'page', WPF_Utils::get_post_type() );
	}

	/**
	 * @covers ::get_post_type
	 * @preserveGlobalState disabled
	 */
	public function test_get_post_type_with_post_archive() {
		$test_contents = self::create_test_contents();

		$this->go_to( get_post_type_archive_link( 'post' ) );
		$this->assertQueryTrue( 'is_front_page', 'is_home' );
		$this->assertSame( 'post', WPF_Utils::get_post_type() );

		$this->go_to( next_posts( 0, false ) );
		$this->assertQueryTrue( 'is_front_page', 'is_home', 'is_paged' );
		$this->assertSame( 'post', WPF_Utils::get_post_type() );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( get_post_type_archive_link( 'post' ) );
		$this->assertQueryTrue( 'is_front_page', 'is_home' );
		$this->assertSame( 'post', WPF_Utils::get_post_type() );

		$this->go_to( next_posts( 0, false ) );

		$this->assertQueryTrue( 'is_front_page', 'is_home', 'is_paged' );
		$this->assertSame( 'post', WPF_Utils::get_post_type() );
	}

	/**
	 * @covers ::get_post_type
	 * @preserveGlobalState disabled
	 */
	public function test_get_post_type_with_page_for_posts() {
		$test_contents = self::create_test_contents();

		// ホームページを固定ページに設定する。
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $page_id );

		$this->go_to( get_post_type_archive_link( 'post' ) );
		$this->assertQueryTrue( 'is_home', 'is_posts_page' );
		$this->assertSame( 'post', WPF_Utils::get_post_type() );

		$this->go_to( next_posts( 0, false ) );
		$this->assertQueryTrue( 'is_home', 'is_posts_page', 'is_paged' );
		$this->assertSame( 'post', WPF_Utils::get_post_type() );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( get_post_type_archive_link( 'post' ) );
		$this->assertQueryTrue( 'is_home', 'is_posts_page' );
		$this->assertSame( 'post', WPF_Utils::get_post_type() );

		$this->go_to( get_post_type_archive_link( 'post' ) . '&paged=2' );
		$this->assertQueryTrue( 'is_home', 'is_posts_page', 'is_paged' );
		$this->assertSame( 'post', WPF_Utils::get_post_type() );
	}

	/**
	 * @covers ::get_post_type
	 * @preserveGlobalState disabled
	 */
	public function test_get_post_type_with_date_archive() {
		$test_contents = self::create_test_contents();

		$date_link = home_url( user_trailingslashit( '/2012/12/12' ) );
		$this->go_to( $date_link );
		$this->assertQueryTrue( 'is_archive', 'is_date', 'is_day' );
		$this->assertSame( 'post', WPF_Utils::get_post_type() );

		$this->go_to( next_posts( 0, false ) );
		$this->assertQueryTrue( 'is_archive', 'is_date', 'is_day', 'is_paged' );
		$this->assertSame( 'post', WPF_Utils::get_post_type() );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$date_link = home_url( '/?m=20121212' );
		$this->go_to( $date_link );
		$this->assertQueryTrue( 'is_archive', 'is_date', 'is_day' );
		$this->assertSame( 'post', WPF_Utils::get_post_type() );

		$this->go_to( $date_link . '&paged=2' );
		$this->assertQueryTrue( 'is_archive', 'is_date', 'is_day', 'is_paged' );
		$this->assertSame( 'post', WPF_Utils::get_post_type() );
	}

	/**
	 * @covers ::get_post_type
	 * @preserveGlobalState disabled
	 */
	public function test_get_post_type_with_date_archive_and_page_for_posts() {
		$test_contents = self::create_test_contents();

		// ホームページを固定ページに設定する。
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $page_id );

		$date_link = home_url( user_trailingslashit( '/2012/12/12' ) );
		$this->go_to( $date_link );
		$this->assertQueryTrue( 'is_archive', 'is_date', 'is_day' );
		$this->assertSame( 'post', WPF_Utils::get_post_type() );

		$this->go_to( next_posts( 0, false ) );
		$this->assertQueryTrue( 'is_archive', 'is_date', 'is_day', 'is_paged' );
		$this->assertSame( 'post', WPF_Utils::get_post_type() );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$date_link = home_url( '/?m=20121212' );
		$this->go_to( $date_link );
		$this->assertQueryTrue( 'is_archive', 'is_date', 'is_day' );
		$this->assertSame( 'post', WPF_Utils::get_post_type() );

		$this->go_to( $date_link . '&paged=2' );
		$this->assertQueryTrue( 'is_archive', 'is_date', 'is_day', 'is_paged' );
		$this->assertSame( 'post', WPF_Utils::get_post_type() );
	}

	/**
	 * @covers ::get_post_type
	 * @preserveGlobalState disabled
	 */
	public function test_get_post_type_with_category_archive() {
		$test_contents = self::create_test_contents();

		$category_link = get_term_link( $test_contents['cat_id'], 'category' );
		$this->go_to( $category_link );
		$this->assertQueryTrue( 'is_archive', 'is_category' );
		$this->assertSame( WPF_Utils::get_post_type(), 'post' ); // 優先順位が最も高い object_type が返されるはず。

		$this->go_to( next_posts( 0, false ) );
		$this->assertQueryTrue( 'is_archive', 'is_category', 'is_paged' );
		$this->assertSame( WPF_Utils::get_post_type(), 'post' );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$category_link = get_term_link( $test_contents['cat_id'], 'category' );
		$this->go_to( $category_link );
		$this->assertQueryTrue( 'is_archive', 'is_category' );
		$this->assertSame( WPF_Utils::get_post_type(), 'post' );

		$this->go_to( $category_link . '&paged=2' );
		$this->assertQueryTrue( 'is_archive', 'is_category', 'is_paged' );
		$this->assertSame( WPF_Utils::get_post_type(), 'post' );
	}

	/**
	 * @covers ::get_post_type
	 * @preserveGlobalState disabled
	 */
	public function test_get_post_type_with_cpt_category_archive() {
		$test_contents = self::create_cpt_test_contents();

		// カテゴリアーカイブのクエリにCPTを追加する（`category` には `post` のみ紐づいているため）。
		$pre_get_posts = function ( $query ) use ( $test_contents ) {
			if ( is_category() ) {
				$post_type = get_query_var( 'post_type' );
				if ( $post_type ) {
					$post_type = $post_type;
				} else {
					$post_type = array( 'nav_menu_item', 'post', $test_contents['post_type'] );
				}
				$query->set( 'post_type', $test_contents['post_type'] );
				return $query;
			}
		};
		add_action( 'pre_get_posts', $pre_get_posts );

		$category_link = get_term_link( $test_contents['cat_id'], 'category' );
		$this->go_to( $category_link );
		$this->assertQueryTrue( 'is_archive', 'is_category' );
		$this->assertSame( $test_contents['post_type'], WPF_Utils::get_post_type() );

		$this->go_to( next_posts( 0, false ) );
		$this->assertQueryTrue( 'is_archive', 'is_category', 'is_paged' );
		$this->assertSame( $test_contents['post_type'], WPF_Utils::get_post_type() );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$category_link = get_term_link( $test_contents['cat_id'], 'category' );
		$this->go_to( $category_link );
		$this->assertQueryTrue( 'is_archive', 'is_category' );
		$this->assertSame( $test_contents['post_type'], WPF_Utils::get_post_type() );

		$this->go_to( $category_link . '&paged=2' );
		$this->assertQueryTrue( 'is_archive', 'is_category', 'is_paged' );
		$this->assertSame( $test_contents['post_type'], WPF_Utils::get_post_type() );

		remove_filter( 'pre_get_posts', $pre_get_posts );
	}

	/**
	 * @covers ::get_post_type
	 * @preserveGlobalState disabled
	 */
	public function test_get_post_type_with_tag_archive() {
		$test_contents = self::create_test_contents();

		$tag_link = get_term_link( $test_contents['tag_id'], 'post_tag' );
		$this->go_to( $tag_link );
		$this->assertQueryTrue( 'is_archive', 'is_tag' );
		$this->assertSame( WPF_Utils::get_post_type(), 'post' );

		$this->go_to( next_posts( 0, false ) );
		$this->assertQueryTrue( 'is_archive', 'is_tag', 'is_paged' );
		$this->assertSame( WPF_Utils::get_post_type(), 'post' );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$tag_link = get_term_link( $test_contents['tag_id'], 'post_tag' );
		$this->go_to( $tag_link );
		$this->assertQueryTrue( 'is_archive', 'is_tag' );
		$this->assertSame( WPF_Utils::get_post_type(), 'post' );

		$this->go_to( $tag_link . '&paged=2' );
		$this->assertQueryTrue( 'is_archive', 'is_tag', 'is_paged' );
		$this->assertSame( WPF_Utils::get_post_type(), 'post' );
	}

	/**
	 * @covers ::get_post_type
	 * @preserveGlobalState disabled
	 */
	public function test_get_post_type_with_cpt_tag_archive() {
		$test_contents = self::create_cpt_test_contents();

		// カテゴリアーカイブのクエリにCPTを追加する（`category` には `post` のみ紐づいているため）。
		$pre_get_posts = function ( $query ) use ( $test_contents ) {
			if ( is_tag() ) {
				$post_type = get_query_var( 'post_type' );
				if ( $post_type ) {
					$post_type = $post_type;
				} else {
					$post_type = array( 'nav_menu_item', 'post', $test_contents['post_type'] );
				}
				$query->set( 'post_type', $test_contents['post_type'] );
				return $query;
			}
		};
		add_action( 'pre_get_posts', $pre_get_posts );

		$tag_link = get_term_link( $test_contents['tag_id'], 'post_tag' );
		$this->go_to( $tag_link );
		$this->assertQueryTrue( 'is_archive', 'is_tag' );
		$this->assertSame( $test_contents['post_type'], WPF_Utils::get_post_type() ); // 現在の投稿のクエリから投稿タイプが返されるはず。

		$this->go_to( next_posts( 0, false ) );
		$this->assertQueryTrue( 'is_archive', 'is_tag', 'is_paged' );
		$this->assertSame( $test_contents['post_type'], WPF_Utils::get_post_type() );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$tag_link = get_term_link( $test_contents['tag_id'], 'post_tag' );
		$this->go_to( $tag_link );
		$this->assertQueryTrue( 'is_archive', 'is_tag' );
		$this->assertSame( $test_contents['post_type'], WPF_Utils::get_post_type() ); // 現在の投稿のクエリから投稿タイプが返されるはず。

		$this->go_to( $tag_link . '&paged=2' );
		$this->assertQueryTrue( 'is_archive', 'is_tag', 'is_paged' );
		$this->assertSame( $test_contents['post_type'], WPF_Utils::get_post_type() );

		remove_filter( 'pre_get_posts', $pre_get_posts );
	}

	/**
	 * @covers ::get_post_type
	 * @preserveGlobalState disabled
	 */
	public function test_get_post_type_with_tax_archive() {
		$test_contents = self::create_cpt_test_contents();

		$term_link = get_term_link( $test_contents['term_id'], $test_contents['taxonomy'] );
		$this->go_to( $term_link );
		$this->assertQueryTrue( 'is_archive', 'is_tax' );
		$this->assertSame( WPF_Utils::get_post_type(), $test_contents['post_type'] );

		$this->go_to( next_posts( 0, false ) );
		$this->assertQueryTrue( 'is_archive', 'is_tax', 'is_paged' );
		$this->assertSame( WPF_Utils::get_post_type(), $test_contents['post_type'] );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$term_link = get_term_link( $test_contents['term_id'], $test_contents['taxonomy'] );
		$this->go_to( $term_link );
		$this->assertQueryTrue( 'is_archive', 'is_tax' );
		$this->assertSame( WPF_Utils::get_post_type(), $test_contents['post_type'] );

		$this->go_to( $term_link . '&paged=2' );
		$this->assertQueryTrue( 'is_archive', 'is_tax', 'is_paged' );
		$this->assertSame( WPF_Utils::get_post_type(), $test_contents['post_type'] );
	}

	/**
	 * @covers ::get_post_type
	 * @preserveGlobalState disabled
	 */
	public function test_get_post_type_with_single() {
		$test_contents = self::create_test_contents();

		$post_link = get_permalink( $test_contents['post_id'] );
		$this->go_to( $post_link );
		$this->assertQueryTrue( 'is_single', 'is_singular' );
		$this->assertSame( WPF_Utils::get_post_type(), 'post' );

		$next_page_link = user_trailingslashit( rtrim( $post_link, '/' ) . '/page/2' );
		$this->go_to( $next_page_link );
		$this->assertQueryTrue( 'is_single', 'is_singular', 'is_paged' );
		$this->assertSame( WPF_Utils::get_post_type(), 'post' );

		$next_page_link = user_trailingslashit( rtrim( $post_link, '/' ) . '/2' );
		$this->go_to( $next_page_link );
		$this->assertQueryTrue( 'is_single', 'is_singular' );
		$this->assertSame( WPF_Utils::get_post_type(), 'post' );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$post_link = get_permalink( $test_contents['post_id'] );
		$this->go_to( $post_link );
		$this->assertQueryTrue( 'is_single', 'is_singular' );
		$this->assertSame( WPF_Utils::get_post_type(), 'post' );

		$this->go_to( $post_link . '&paged=2' );
		$this->assertQueryTrue( 'is_single', 'is_singular', 'is_paged' );
		$this->assertSame( WPF_Utils::get_post_type(), 'post' );
	}

	/**
	 * @covers ::get_post_type
	 * @preserveGlobalState disabled
	 */
	public function test_get_post_type_with_cpt_post_archive() {
		$test_contents = self::create_cpt_test_contents();

		$this->go_to( get_post_type_archive_link( $test_contents['post_type'] ) );
		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive' );
		$this->assertSame( WPF_Utils::get_post_type(), $test_contents['post_type'] );

		$this->go_to( next_posts( 0, false ) );
		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_paged' );
		$this->assertSame( WPF_Utils::get_post_type(), $test_contents['post_type'] );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( get_post_type_archive_link( $test_contents['post_type'] ) );
		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive' );
		$this->assertSame( WPF_Utils::get_post_type(), $test_contents['post_type'] );

		$this->go_to( get_post_type_archive_link( $test_contents['post_type'] ) . '&paged=2' );
		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_paged' );
		$this->assertSame( WPF_Utils::get_post_type(), $test_contents['post_type'] );
	}

	/**
	 * @covers ::get_post_type
	 * @preserveGlobalState disabled
	 */
	public function test_get_post_type_with_cpt_date_archive() {
		$test_contents = self::create_cpt_test_contents();

		$cpt_slug   = get_post_type_object( $test_contents['post_type'] )->rewrite['slug'];
		$date_front = str_replace( WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $test_contents['post_type'] ), $cpt_slug, WPF_CPT_Rewrite::get_date_front( $test_contents['post_type'] ) );
		$date_link  = home_url( user_trailingslashit( $date_front . '2012/12/12' ) );
		$this->go_to( $date_link );
		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_day' );
		$this->assertSame( WPF_Utils::get_post_type(), $test_contents['post_type'] );

		$this->go_to( next_posts( 0, false ) );
		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_day', 'is_paged' );
		$this->assertSame( WPF_Utils::get_post_type(), $test_contents['post_type'] );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$date_link = home_url( '/?m=20121212&post_type=' . $test_contents['post_type'] );
		$this->go_to( $date_link );
		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_day' );
		$this->assertSame( WPF_Utils::get_post_type(), $test_contents['post_type'] );

		$this->go_to( $date_link . '&paged=2' );
		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_day', 'is_paged' );
		$this->assertSame( WPF_Utils::get_post_type(), $test_contents['post_type'] );
	}

	/**
	 * @covers ::get_post_type
	 * @preserveGlobalState disabled
	 */
	public function test_get_post_type_with_cpt_single() {
		$test_contents = self::create_cpt_test_contents();

		$post_link = get_permalink( $test_contents['post_id'] );
		$this->go_to( $post_link );
		$this->assertQueryTrue( 'is_single', 'is_singular' );
		$this->assertSame( WPF_Utils::get_post_type(), $test_contents['post_type'] );

		$next_page_link = user_trailingslashit( rtrim( $post_link, '/' ) . '/page/2' );
		$this->go_to( $next_page_link );
		$this->assertQueryTrue( 'is_single', 'is_singular', 'is_paged' );
		$this->assertSame( WPF_Utils::get_post_type(), $test_contents['post_type'] );

		$next_page_link = user_trailingslashit( rtrim( $post_link, '/' ) . '/2' );
		$this->go_to( $next_page_link );
		$this->assertQueryTrue( 'is_single', 'is_singular' );
		$this->assertSame( WPF_Utils::get_post_type(), $test_contents['post_type'] );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$post_link = get_permalink( $test_contents['post_id'] );
		$this->go_to( $post_link );
		$this->assertQueryTrue( 'is_single', 'is_singular' );
		$this->assertSame( WPF_Utils::get_post_type(), $test_contents['post_type'] );

		$this->go_to( $post_link . '&paged=2' );
		$this->assertQueryTrue( 'is_single', 'is_singular', 'is_paged' );
		$this->assertSame( WPF_Utils::get_post_type(), $test_contents['post_type'] );
	}

	/**
	 * @covers ::get_post_type
	 * @preserveGlobalState disabled
	 */
	public function test_get_post_type_with_page() {
		$test_contents = self::create_cpt_test_contents();

		$page_id = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_content' => 'Page 1 <!--nextpage--> Page 2',
			)
		);

		$post_link = get_permalink( $page_id );
		$this->go_to( $post_link );
		$this->assertQueryTrue( 'is_page', 'is_singular' );
		$this->assertSame( WPF_Utils::get_post_type(), 'page' );

		$next_page_link = user_trailingslashit( rtrim( $post_link, '/' ) . '/page/2' );
		$this->go_to( $next_page_link );
		$this->assertQueryTrue( 'is_page', 'is_singular', 'is_paged' );
		$this->assertSame( WPF_Utils::get_post_type(), 'page' );

		$next_page_link = user_trailingslashit( rtrim( $post_link, '/' ) . '/2' );
		$this->go_to( $next_page_link );
		$this->assertQueryTrue( 'is_page', 'is_singular' );
		$this->assertSame( WPF_Utils::get_post_type(), 'page' );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$post_link = get_permalink( $page_id );
		$this->go_to( $post_link );
		$this->assertQueryTrue( 'is_page', 'is_singular' );
		$this->assertSame( WPF_Utils::get_post_type(), 'page' );

		$this->go_to( $post_link . '&paged=2' );
		$this->assertQueryTrue( 'is_page', 'is_singular', 'is_paged' );
		$this->assertSame( WPF_Utils::get_post_type(), 'page' );
	}

	/**
	 * has_archive => true のとき、CPT投稿用ページでCPT名を返すか。
	 *
	 * has_archive の有効・無効によってテストを追加するのは冗長な気がするかもしれないが、
	 * 投稿用ページと同じ振る舞い（投稿タイプ名を返す）になっているか確認しておきたい。
	 *
	 * @covers ::get_post_type
	 * @preserveGlobalState disabled
	 */
	public function test_get_post_type_with_page_for_cpt_posts_and_has_archive_true() {
		register_post_type(
			self::$post_type,
			array(
				'public'      => true,
				'has_archive' => false,
			)
		);
		$page_id = self::factory()->post->create(
			array(
				'post_type' => 'page',
				'post_name' => self::$post_type,
			)
		);
		self::factory()->post->create( array( 'post_type' => self::$post_type ) );

		$this->go_to( home_url( user_trailingslashit( '/' . self::$post_type ) ) );

		$this->assertSame( self::$post_type, WPF_Utils::get_post_type() );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( home_url( '?post_type=' . self::$post_type ) );

		// HACK: UglyパーマリンクにおけるCPT投稿アーカイブページでは、テスト環境においてのみクエリが壊れているようなので強制的にクエリをセット。
		global $wp_query;
		$wp_query->is_front_page        = false;
		$wp_query->is_home              = false;
		$wp_query->is_post_type_archive = true;

		$this->assertSame( self::$post_type, WPF_Utils::get_post_type() );
	}

	/**
	 * has_archive => false のとき、CPT投稿用ページでCPT名を返すか。
	 *
	 * has_archive の有効・無効によってテストを追加するのは冗長な気がするかもしれないが、
	 * 投稿用ページと同じ振る舞い（投稿タイプ名を返す）になっているか確認しておきたい。
	 *
	 * @covers ::get_post_type
	 * @preserveGlobalState disabled
	 */
	public function test_get_post_type_with_page_for_cpt_posts_and_has_archive_false() {
		register_post_type(
			self::$post_type,
			array(
				'public'      => true,
				'has_archive' => false,
			)
		);
		$page_id = self::factory()->post->create(
			array(
				'post_type' => 'page',
				'post_name' => self::$post_type,
			)
		);
		self::factory()->post->create( array( 'post_type' => self::$post_type ) );

		$this->go_to( home_url( user_trailingslashit( '/' . self::$post_type ) ) );

		$this->assertSame( self::$post_type, WPF_Utils::get_post_type() );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( home_url( '?post_type=' . self::$post_type ) );

		// HACK: UglyパーマリンクにおけるCPT投稿アーカイブページでは、テスト環境においてのみクエリが壊れているようなので強制的にクエリをセット。
		global $wp_query;
		$wp_query->is_front_page        = false;
		$wp_query->is_home              = false;
		$wp_query->is_post_type_archive = true;

		$this->assertSame( self::$post_type, WPF_Utils::get_post_type() );
	}
}
