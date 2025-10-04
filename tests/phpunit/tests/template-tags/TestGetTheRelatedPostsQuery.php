<?php

/**
 * WPF_Template_Tags::get_the_related_posts_query のユニットテスト。
 *
 * @group template_tags
 * @covers WPF_Template_Tags
 * @coversDefaultClass WPF_Template_Tags
 */
class TestGetTheRelatedPostsQuery extends WPF_UnitTestCase {

	public function tear_down() {
		$post_globals = array( 'request' );
		foreach ( $post_globals as $global ) {
			$GLOBALS[ $global ] = null;
		}

		parent::tear_down();
	}

	/**
	 * 個別投稿ページ
	 *
	 * @covers ::get_the_related_posts_query
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_related_posts_query_with_post() {
		$foo = self::factory()->term->create(
			array(
				'taxonomy' => 'category',
				'name'     => 'foo',
			)
		);
		$bar = self::factory()->term->create(
			array(
				'taxonomy' => 'post_tag',
				'name'     => 'bar',
			)
		);

		for ( $i = 0; $i < 9; $i++ ) {
			$posts[] = self::factory()->post->create(
				array(
					'post_date' => gmdate( DATE_W3C, strtotime( '2012-12-12 00:00:00 +' . $i . ' seconds' ) ), // 投稿順序を保証する。
				)
			);
		}

		wp_set_post_terms( $posts[0], array( $foo ), 'category' );
		wp_set_post_terms( $posts[0], array( $bar ), 'post_tag' );

		wp_set_post_terms( $posts[1], array( $foo ), 'category' );
		wp_set_post_terms( $posts[2], array( $foo ), 'category' );
		wp_set_post_terms( $posts[3], array( $bar ), 'post_tag' );
		wp_set_post_terms( $posts[4], array( $bar ), 'post_tag' );
		wp_set_post_terms( $posts[5], array( $bar ), 'post_tag' );
		wp_set_post_terms( $posts[6], array( $bar ), 'post_tag' );

		$post_link = get_permalink( $posts[0] );
		$this->go_to( $post_link );

		$this->assertQueryTrue( 'is_single', 'is_singular' );

		$expected = array(
			$posts[6],
			$posts[5],
			$posts[4],
			$posts[3],
			$posts[2],
			$posts[1],
		);

		$query  = WPF_Template_Tags::get_the_related_posts_query();
		$actual = array();
		foreach ( $query->posts as $post ) {
			array_push( $actual, $post->ID );
		}

		$this->assertSame( $expected, $actual );
	}

	/**
	 * 個別投稿ページ
	 *
	 * テスト条件:
	 * - 関連投稿がない場合 => 投稿数が0件。
	 *
	 * @covers ::get_the_related_posts_query
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_related_posts_query_with_post_and_no_realated_posts() {
		$foo = self::factory()->term->create(
			array(
				'taxonomy' => 'category',
				'name'     => 'foo',
			)
		);
		$bar = self::factory()->term->create(
			array(
				'taxonomy' => 'post_tag',
				'name'     => 'bar',
			)
		);

		$posts = self::factory()->post->create_many( 10 );

		wp_set_post_terms( $posts[0], array( $foo ), 'category' );
		wp_set_post_terms( $posts[0], array( $bar ), 'post_tag' );

		$post_link = get_permalink( $posts[0] );
		$this->go_to( $post_link );

		$this->assertQueryTrue( 'is_single', 'is_singular' );

		$query = WPF_Template_Tags::get_the_related_posts_query();

		$this->assertSame( 0, $query->post_count );
	}

	/**
	 * 個別投稿ページ
	 *
	 * テスト条件:
	 * - 1ページあたりの表示件数を変更
	 *
	 * @covers ::get_the_related_posts_query
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_related_posts_query_with_post_and_change_per_page() {
		$foo = self::factory()->term->create(
			array(
				'taxonomy' => 'category',
				'name'     => 'foo',
			)
		);
		$bar = self::factory()->term->create(
			array(
				'taxonomy' => 'post_tag',
				'name'     => 'bar',
			)
		);

		for ( $i = 0; $i < 9; $i++ ) {
			$posts[] = self::factory()->post->create(
				array(
					'post_date' => gmdate( DATE_W3C, strtotime( '2012-12-12 00:00:00 +' . $i . ' seconds' ) ), // 投稿順序を保証する。
				)
			);
		}

		wp_set_post_terms( $posts[0], array( $foo ), 'category' );
		wp_set_post_terms( $posts[0], array( $bar ), 'post_tag' );

		wp_set_post_terms( $posts[1], array( $foo ), 'category' );
		wp_set_post_terms( $posts[2], array( $foo ), 'category' );
		wp_set_post_terms( $posts[3], array( $bar ), 'post_tag' );
		wp_set_post_terms( $posts[4], array( $bar ), 'post_tag' );
		wp_set_post_terms( $posts[5], array( $bar ), 'post_tag' );
		wp_set_post_terms( $posts[6], array( $bar ), 'post_tag' );
		wp_set_post_terms( $posts[7], array( $bar ), 'post_tag' );

		$post_link = get_permalink( $posts[0] );
		$this->go_to( $post_link );

		$this->assertQueryTrue( 'is_single', 'is_singular' );

		$expected = array(
			$posts[7],
			$posts[6],
			$posts[5],
		);

		$args   = array(
			'posts_per_page' => 3,
		);
		$query  = WPF_Template_Tags::get_the_related_posts_query( null, $args );
		$actual = array();
		foreach ( $query->posts as $post ) {
			array_push( $actual, $post->ID );
		}

		$this->assertSame( $expected, $actual );
	}

	/**
	 * CPT個別投稿ページ
	 *
	 * @covers ::get_the_related_posts_query
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_related_posts_query_with_cpt_post() {
		register_taxonomy(
			'ctax_1',
			'cpt',
			array(
				'public'       => true,
				'hierarchical' => true,
				'rewrite'      => array(
					'hierarchical' => true,
				),
			)
		);
		register_taxonomy(
			'ctax_2',
			'cpt',
			array(
				'public' => true,
			)
		);

		register_post_type(
			'cpt',
			array(
				'public'     => true,
				'taxonomies' => array( 'ctax_1', 'ctax_2' ),
			)
		);

		$foo = self::factory()->term->create(
			array(
				'taxonomy' => 'ctax_1',
				'name'     => 'foo',
			)
		);
		$bar = self::factory()->term->create(
			array(
				'taxonomy' => 'ctax_2',
				'name'     => 'bar',
			)
		);

		for ( $i = 0; $i < 9; $i++ ) {
			$posts[] = self::factory()->post->create(
				array(
					'post_type' => 'cpt',
					'post_date' => gmdate( DATE_W3C, strtotime( '2012-12-12 00:00:00 +' . $i . ' seconds' ) ), // 投稿順序を保証する。
				)
			);
		}

		wp_set_post_terms( $posts[0], array( $foo ), 'ctax_1' );
		wp_set_post_terms( $posts[0], array( $bar ), 'ctax_2' );

		wp_set_post_terms( $posts[1], array( $foo ), 'ctax_1' );
		wp_set_post_terms( $posts[2], array( $foo ), 'ctax_1' );
		wp_set_post_terms( $posts[3], array( $bar ), 'ctax_2' );
		wp_set_post_terms( $posts[4], array( $bar ), 'ctax_2' );
		wp_set_post_terms( $posts[5], array( $bar ), 'ctax_2' );
		wp_set_post_terms( $posts[6], array( $bar ), 'ctax_2' );

		$post_link = get_permalink( $posts[0] );
		$this->go_to( $post_link );

		$this->assertQueryTrue( 'is_single', 'is_singular' );

		$expected = array(
			$posts[6],
			$posts[5],
			$posts[4],
			$posts[3],
			$posts[2],
			$posts[1],
		);

		$query  = WPF_Template_Tags::get_the_related_posts_query();
		$actual = array();
		foreach ( $query->posts as $post ) {
			array_push( $actual, $post->ID );
		}

		$this->assertSame( $expected, $actual );

		_unregister_post_type( 'cpt' );
		_unregister_taxonomy( 'ctax_1' );
		_unregister_taxonomy( 'ctax_2' );
	}

	/**
	 * CPT個別投稿ページ
	 *
	 * テスト条件:
	 * - ターム未選択 => null が返されるはず。
	 *
	 * @covers ::get_the_related_posts_query
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_related_posts_query_with_cpt_post_and_no_term_selected() {
		register_taxonomy(
			'ctax',
			'cpt',
			array(
				'public'       => true,
				'hierarchical' => true,
				'rewrite'      => array(
					'hierarchical' => true,
				),
			)
		);

		register_post_type(
			'cpt',
			array(
				'public'     => true,
				'taxonomies' => array( 'ctax' ),
			)
		);

		$foo = self::factory()->term->create(
			array(
				'taxonomy' => 'ctax',
				'name'     => 'foo',
			)
		);

		$posts = self::factory()->post->create_many( 10, array( 'post_type' => 'cpt' ) );

		$post_link = get_permalink( $posts[0] );
		$this->go_to( $post_link );

		$this->assertQueryTrue( 'is_single', 'is_singular' );

		$query = WPF_Template_Tags::get_the_related_posts_query();

		$this->assertSame( null, $query );

		_unregister_post_type( 'cpt' );
		_unregister_taxonomy( 'ctax' );
	}

	/**
	 * CPT個別投稿ページ
	 *
	 * テスト条件:
	 * - サブtaxのタームのみ選択 => 同じタームを持つ投稿クエリが返されるはず。
	 *
	 * @covers ::get_the_related_posts_query
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_related_posts_query_with_cpt_post_and_select_only_sub_tax() {
		register_taxonomy(
			'ctax_1',
			'cpt',
			array(
				'public'       => true,
				'hierarchical' => true,
				'rewrite'      => array(
					'hierarchical' => true,
				),
			)
		);
		register_taxonomy(
			'ctax_2',
			'cpt',
			array(
				'public' => true,
			)
		);

		register_post_type(
			'cpt',
			array(
				'public'     => true,
				'taxonomies' => array( 'ctax_1', 'ctax_2' ),
			)
		);

		$foo = self::factory()->term->create(
			array(
				'taxonomy' => 'ctax_1',
				'name'     => 'foo',
			)
		);
		$bar = self::factory()->term->create(
			array(
				'taxonomy' => 'ctax_2',
				'name'     => 'bar',
			)
		);

		for ( $i = 0; $i < 9; $i++ ) {
			$posts[] = self::factory()->post->create(
				array(
					'post_type' => 'cpt',
					'post_date' => gmdate( DATE_W3C, strtotime( '2012-12-12 00:00:00 +' . $i . ' seconds' ) ), // 投稿順序を保証する。
				)
			);
		}

		wp_set_post_terms( $posts[0], array( $bar ), 'ctax_2' );

		wp_set_post_terms( $posts[1], array( $foo ), 'ctax_1' );
		wp_set_post_terms( $posts[2], array( $foo ), 'ctax_1' );
		wp_set_post_terms( $posts[3], array( $bar ), 'ctax_2' );
		wp_set_post_terms( $posts[4], array( $bar ), 'ctax_2' );
		wp_set_post_terms( $posts[5], array( $bar ), 'ctax_2' );
		wp_set_post_terms( $posts[6], array( $bar ), 'ctax_2' );

		$post_link = get_permalink( $posts[0] );
		$this->go_to( $post_link );

		$this->assertQueryTrue( 'is_single', 'is_singular' );

		$expected = array(
			$posts[6],
			$posts[5],
			$posts[4],
			$posts[3],
		);

		$query  = WPF_Template_Tags::get_the_related_posts_query();
		$actual = array();
		foreach ( $query->posts as $post ) {
			array_push( $actual, $post->ID );
		}

		$this->assertSame( $expected, $actual );

		_unregister_post_type( 'cpt' );
		_unregister_taxonomy( 'ctax_1' );
		_unregister_taxonomy( 'ctax_2' );
	}
}
