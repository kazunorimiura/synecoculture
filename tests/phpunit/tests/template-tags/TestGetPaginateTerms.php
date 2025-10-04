<?php

/**
 * WPF_Template_Tags::get_paginate_terms のユニットテスト。
 *
 * @group template_tags
 * @covers WPF_Template_Tags
 * @coversDefaultClass WPF_Template_Tags
 */
class TestGetPaginateTerms extends WPF_UnitTestCase {

	/**
	 * @covers ::get_paginate_terms
	 * @preserveGlobalState disabled
	 */
	public function test_get_paginate_terms() {
		for ( $i = 0; $i < 3; $i++ ) {
			$terms[] = self::factory()->term->create(
				array(
					'taxonomy' => 'category',
					'name'     => 'Term_' . $i,
				)
			);
		}
		$post = self::factory()->post->create();
		wp_set_post_terms( $post, $terms, 'category' );

		$this->go_to( home_url() );

		$actual = WPF_Template_Tags::get_paginate_terms();

		$this->assertMatchesRegularExpression( '/<article>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/category\/term_0\/">\s*Term_0\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/category\/term_1\/">\s*Term_1\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/category\/term_2\/">\s*Term_2\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/article>/', $actual );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( home_url() );

		$actual = WPF_Template_Tags::get_paginate_terms();

		$this->assertMatchesRegularExpression( '/<article>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/\?cat=' . get_cat_ID( 'term_0' ) . '">\s*Term_0\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/\?cat=' . get_cat_ID( 'term_1' ) . '">\s*Term_1\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/\?cat=' . get_cat_ID( 'term_2' ) . '">\s*Term_2\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/article>/', $actual );
	}

	/**
	 * ページ付き
	 *
	 * @covers ::get_paginate_terms
	 * @preserveGlobalState disabled
	 */
	public function test_get_paginate_terms_with_pagination() {
		for ( $i = 0; $i < 16; $i++ ) {
			$terms[] = self::factory()->term->create(
				array(
					'taxonomy' => 'category',
					'name'     => 'Term_' . $i,
				)
			);
		}
		$post = self::factory()->post->create();
		wp_set_post_terms( $post, $terms, 'category' );

		$this->go_to( home_url() );

		$actual = WPF_Template_Tags::get_paginate_terms();

		$this->assertMatchesRegularExpression( '/<article>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/category\/term_0\/">\s*Term_0\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/category\/term_5\/">\s*Term_5\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/article>/', $actual );

		$this->go_to( next_posts( 0, false ) );

		$actual = WPF_Template_Tags::get_paginate_terms();

		$this->assertMatchesRegularExpression( '/<article>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/category\/term_9\/">\s*Term_9\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/article>/', $actual );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( home_url() );

		$actual = WPF_Template_Tags::get_paginate_terms();

		$this->assertMatchesRegularExpression( '/<article>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/\?cat=' . get_cat_ID( 'term_0' ) . '">\s*Term_0\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/\?cat=' . get_cat_ID( 'term_5' ) . '">\s*Term_5\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/article>/', $actual );

		$this->go_to( next_posts( 0, false ) );

		$actual = WPF_Template_Tags::get_paginate_terms();

		$this->assertMatchesRegularExpression( '/<article>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/\?cat=' . get_cat_ID( 'term_9' ) . '">\s*Term_9\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/article>/', $actual );
	}

	/**
	 * ヒエラルキーあり => 親タームを含める（たとえ空でも）。
	 *
	 * @covers ::get_paginate_terms
	 * @preserveGlobalState disabled
	 */
	public function test_get_paginate_terms_with_hierarchical() {
		$term = 0;
		for ( $i = 0; $i < 3; $i++ ) {
			$term = self::factory()->term->create(
				array(
					'taxonomy' => 'category',
					'name'     => 'Term_' . $i,
					'parent'   => $term,
				)
			);
		}
		$post = self::factory()->post->create();
		wp_set_post_terms( $post, array( $term ), 'category' );

		$this->go_to( home_url() );

		$args   = array(
			'hierarchical' => true,
		);
		$actual = WPF_Template_Tags::get_paginate_terms( $args );

		$this->assertMatchesRegularExpression( '/<article>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/category\/term_0\/">\s*Term_0\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/category\/term_0\/term_1\/">\s*Term_1\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/category\/term_0\/term_1\/term_2\/">\s*Term_2\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/article>/', $actual );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( home_url() );

		$actual = WPF_Template_Tags::get_paginate_terms( $args );

		$this->assertMatchesRegularExpression( '/<article>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/\?cat=' . get_cat_ID( 'term_0' ) . '">\s*Term_0\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/\?cat=' . get_cat_ID( 'term_1' ) . '">\s*Term_1\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/\?cat=' . get_cat_ID( 'term_2' ) . '">\s*Term_2\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/article>/', $actual );
	}

	/**
	 * 1ページあたりの表示件数を変更
	 *
	 * @covers ::get_paginate_terms
	 * @preserveGlobalState disabled
	 */
	public function test_get_paginate_terms_with_change_per_page() {
		for ( $i = 0; $i < 3; $i++ ) {
			$terms[] = self::factory()->term->create(
				array(
					'taxonomy' => 'category',
					'name'     => 'Term_' . $i,
				)
			);
		}
		$post = self::factory()->post->create();
		wp_set_post_terms( $post, $terms, 'category' );

		$this->go_to( home_url() );

		$args   = array(
			'number' => 2,
		);
		$actual = WPF_Template_Tags::get_paginate_terms( $args );

		$this->assertMatchesRegularExpression( '/<article>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/category\/term_0\/">\s*Term_0\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/category\/term_1\/">\s*Term_1\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/article>/', $actual );
		$this->assertMatchesRegularExpression( '/<nav aria-label="ターム".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<span aria-current="page" class="page-numbers current">1<\/span>/', $actual );
		$this->assertMatchesRegularExpression( '/<a class="page-numbers" href="http:\/\/example.org\/page\/2\/">2<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a class="next page-numbers" href="http:\/\/example.org\/page\/2\/"><span>次へ<\/span>.*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/nav>/', $actual );

		$this->go_to( next_posts( 0, false ) );

		$actual = WPF_Template_Tags::get_paginate_terms( $args );

		$this->assertMatchesRegularExpression( '/<article>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/category\/term_2\/">\s*Term_2\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/article>/', $actual );
		$this->assertMatchesRegularExpression( '/<nav aria-label="ターム".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<a class="prev page-numbers" href="http:\/\/example.org\/page\/1\/">.*<span>前へ<\/span><\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a class="page-numbers" href="http:\/\/example.org\/page\/1\/">1<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<span aria-current="page" class="page-numbers current">2<\/span>/', $actual );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( home_url() );

		$actual = WPF_Template_Tags::get_paginate_terms( $args );

		$this->assertMatchesRegularExpression( '/<article>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/\?cat=' . get_cat_ID( 'term_0' ) . '">\s*Term_0\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/\?cat=' . get_cat_ID( 'term_1' ) . '">\s*Term_1\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/article>/', $actual );
		$this->assertMatchesRegularExpression( '/<nav aria-label="ターム".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<span aria-current="page" class="page-numbers current">1<\/span>/', $actual );
		$this->assertMatchesRegularExpression( '/<a class="page-numbers" href="http:\/\/example.org\/\?paged=2">2<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a class="next page-numbers" href="http:\/\/example.org\/\?paged=2"><span>次へ<\/span>.*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/nav>/', $actual );

		$this->go_to( next_posts( 0, false ) );

		$actual = WPF_Template_Tags::get_paginate_terms( $args );

		$this->assertMatchesRegularExpression( '/<article>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/\?cat=' . get_cat_ID( 'term_2' ) . '">\s*Term_2\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/article>/', $actual );
		$this->assertMatchesRegularExpression( '/<nav aria-label="ターム".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<a class="prev page-numbers" href="http:\/\/example.org\/\?paged=1">.*<span>前へ<\/span><\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a class="page-numbers" href="http:\/\/example.org\/\?paged=1">1<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<span aria-current="page" class="page-numbers current">2<\/span>/', $actual );
	}
}
