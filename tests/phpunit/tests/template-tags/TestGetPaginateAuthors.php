<?php

/**
 * WPF_Template_Tags::get_paginate_authors のユニットテスト。
 *
 * @group template_tags
 * @covers WPF_Template_Tags
 * @coversDefaultClass WPF_Template_Tags
 */
class TestGetPaginateAuthors extends WPF_UnitTestCase {

	/**
	 * @covers ::get_paginate_authors
	 * @preserveGlobalState disabled
	 */
	public function test_get_paginate_authors() {
		for ( $i = 0; $i < 3; $i++ ) {
			self::factory()->user->create(
				array(
					'display_name'  => 'User_' . $i,
					'user_nicename' => 'user_' . $i,
					'role'          => 'author',
				)
			);
		}

		$this->go_to( home_url() );

		$actual = WPF_Template_Tags::get_paginate_authors();

		$this->assertMatchesRegularExpression( '/<article>/', $actual );
		$this->assertMatchesRegularExpression( '/<a[^>]*href="http:\/\/example.org\/author\/user_0\/"[^>]*title="User_0のプロフィールを見る"[^>]*>\s*<img[^>]*alt=\'User_0\'[^>]*>\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a[^>]*href="http:\/\/example.org\/author\/user_0\/"[^>]*title="User_0のプロフィールを見る"[^>]*>\s*User_0\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a[^>]*href="http:\/\/example.org\/author\/user_1\/"[^>]*title="User_1のプロフィールを見る"[^>]*>\s*<img[^>]*alt=\'User_1\'[^>]*>\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a[^>]*href="http:\/\/example.org\/author\/user_1\/"[^>]*title="User_1のプロフィールを見る"[^>]*>\s*User_1\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a[^>]*href="http:\/\/example.org\/author\/user_2\/"[^>]*title="User_2のプロフィールを見る"[^>]*>\s*<img[^>]*alt=\'User_2\'[^>]*>\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a[^>]*href="http:\/\/example.org\/author\/user_2\/"[^>]*title="User_2のプロフィールを見る"[^>]*>\s*User_2\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/article>/', $actual );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( home_url() );

		$actual = WPF_Template_Tags::get_paginate_authors();

		$this->assertMatchesRegularExpression( '/<article>/', $actual );
		$this->assertMatchesRegularExpression( '/<a[^>]*href="http:\/\/example.org\/\?author=' . get_user_by( 'slug', 'user_0' )->ID . '"[^>]*title="User_0のプロフィールを見る"[^>]*>\s*<img[^>]*alt=\'User_0\'[^>]*>\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a[^>]*href="http:\/\/example.org\/\?author=' . get_user_by( 'slug', 'user_0' )->ID . '"[^>]*title="User_0のプロフィールを見る"[^>]*>\s*User_0\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a[^>]*href="http:\/\/example.org\/\?author=' . get_user_by( 'slug', 'user_1' )->ID . '"[^>]*title="User_1のプロフィールを見る"[^>]*>\s*<img[^>]*alt=\'User_1\'[^>]*>\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a[^>]*href="http:\/\/example.org\/\?author=' . get_user_by( 'slug', 'user_1' )->ID . '"[^>]*title="User_1のプロフィールを見る"[^>]*>\s*User_1\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a[^>]*href="http:\/\/example.org\/\?author=' . get_user_by( 'slug', 'user_2' )->ID . '"[^>]*title="User_2のプロフィールを見る"[^>]*>\s*<img[^>]*alt=\'User_2\'[^>]*>\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a[^>]*href="http:\/\/example.org\/\?author=' . get_user_by( 'slug', 'user_2' )->ID . '"[^>]*title="User_2のプロフィールを見る"[^>]*>\s*User_2\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/article>/', $actual );
	}

	/**
	 * ページ付き
	 *
	 * @covers ::get_paginate_authors
	 * @preserveGlobalState disabled
	 */
	public function test_get_paginate_authors_with_pagination() {
		for ( $i = 0; $i < 12; $i++ ) {
			self::factory()->user->create(
				array(
					'display_name'  => 'User_' . $i,
					'user_nicename' => 'user_' . $i,
					'role'          => 'author',
				)
			);
		}

		$this->go_to( home_url() );

		$actual = WPF_Template_Tags::get_paginate_authors();

		$this->assertMatchesRegularExpression( '/<article>/', $actual );

		// User_0のリンク（画像付き）をチェック
		$this->assertMatchesRegularExpression( '/href="[^"]*\/author\/user_0\/[^"]*"[^>]*tabindex="-1"[^>]*title="User_0のプロフィールを見る"[^>]*>\s*<img[^>]*alt=\'User_0\'/', $actual );

		// User_0のリンク（テキスト）をチェック
		$this->assertMatchesRegularExpression( '/href="[^"]*\/author\/user_0\/[^"]*"[^>]*title="User_0のプロフィールを見る"[^>]*>\s*User_0\s*<\/a>/', $actual );

		// User_1のリンク（画像付き）をチェック
		$this->assertMatchesRegularExpression( '/href="[^"]*\/author\/user_1\/[^"]*"[^>]*tabindex="-1"[^>]*title="User_1のプロフィールを見る"[^>]*>\s*<img[^>]*alt=\'User_1\'/', $actual );

		// User_1のリンク（テキスト）をチェック
		$this->assertMatchesRegularExpression( '/href="[^"]*\/author\/user_1\/[^"]*"[^>]*title="User_1のプロフィールを見る"[^>]*>\s*User_1\s*<\/a>/', $actual );

		// User_5のリンク（画像付き）をチェック
		$this->assertMatchesRegularExpression( '/href="[^"]*\/author\/user_5\/[^"]*"[^>]*tabindex="-1"[^>]*title="User_5のプロフィールを見る"[^>]*>\s*<img[^>]*alt=\'User_5\'/', $actual );

		// User_5のリンク（テキスト）をチェック
		$this->assertMatchesRegularExpression( '/href="[^"]*\/author\/user_5\/[^"]*"[^>]*title="User_5のプロフィールを見る"[^>]*>\s*User_5\s*<\/a>/', $actual );

		// User_10のリンク（画像付き）をチェック
		$this->assertMatchesRegularExpression( '/href="[^"]*\/author\/user_10\/[^"]*"[^>]*tabindex="-1"[^>]*title="User_10のプロフィールを見る"[^>]*>\s*<img[^>]*alt=\'User_10\'/', $actual );

		// User_10のリンク（テキスト）をチェック
		$this->assertMatchesRegularExpression( '/href="[^"]*\/author\/user_10\/[^"]*"[^>]*title="User_10のプロフィールを見る"[^>]*>\s*User_10\s*<\/a>/', $actual );

		$this->assertMatchesRegularExpression( '/<\/article>/', $actual );

		$this->go_to( next_posts( 0, false ) );

		$actual = WPF_Template_Tags::get_paginate_authors();

		$this->assertMatchesRegularExpression( '/<article>/', $actual );
		$this->assertMatchesRegularExpression( '/href="[^"]*\/author\/user_11\/[^"]*"[^>]*tabindex="-1"[^>]*title="User_11のプロフィールを見る"[^>]*>\s*<img[^>]*alt=\'User_11\'/', $actual );
		$this->assertMatchesRegularExpression( '/href="[^"]*\/author\/user_11\/[^"]*"[^>]*title="User_11のプロフィールを見る"[^>]*>\s*User_11\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/article>/', $actual );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( home_url() );

		$actual = WPF_Template_Tags::get_paginate_authors();

		$this->assertMatchesRegularExpression( '/<article>/', $actual );
		$user0_id = get_user_by( 'slug', 'user_0' )->ID;
		$this->assertMatchesRegularExpression( '/href="[^"]*\?author=' . $user0_id . '[^"]*"[^>]*tabindex="-1"[^>]*title="User_0のプロフィールを見る"[^>]*>\s*<img[^>]*alt=\'User_0\'/', $actual );
		$this->assertMatchesRegularExpression( '/href="[^"]*\?author=' . $user0_id . '[^"]*"[^>]*title="User_0のプロフィールを見る"[^>]*>\s*User_0\s*<\/a>/', $actual );

		$user1_id = get_user_by( 'slug', 'user_1' )->ID;
		$this->assertMatchesRegularExpression( '/href="[^"]*\?author=' . $user1_id . '[^"]*"[^>]*tabindex="-1"[^>]*title="User_1のプロフィールを見る"[^>]*>\s*<img[^>]*alt=\'User_1\'/', $actual );
		$this->assertMatchesRegularExpression( '/href="[^"]*\?author=' . $user1_id . '[^"]*"[^>]*title="User_1のプロフィールを見る"[^>]*>\s*User_1\s*<\/a>/', $actual );

		$user5_id = get_user_by( 'slug', 'user_5' )->ID;
		$this->assertMatchesRegularExpression( '/href="[^"]*\?author=' . $user5_id . '[^"]*"[^>]*tabindex="-1"[^>]*title="User_5のプロフィールを見る"[^>]*>\s*<img[^>]*alt=\'User_5\'/', $actual );
		$this->assertMatchesRegularExpression( '/href="[^"]*\?author=' . $user5_id . '[^"]*"[^>]*title="User_5のプロフィールを見る"[^>]*>\s*User_5\s*<\/a>/', $actual );

		$user10_id = get_user_by( 'slug', 'user_10' )->ID;
		$this->assertMatchesRegularExpression( '/href="[^"]*\?author=' . $user10_id . '[^"]*"[^>]*tabindex="-1"[^>]*title="User_10のプロフィールを見る"[^>]*>\s*<img[^>]*alt=\'User_10\'/', $actual );
		$this->assertMatchesRegularExpression( '/href="[^"]*\?author=' . $user10_id . '[^"]*"[^>]*title="User_10のプロフィールを見る"[^>]*>\s*User_10\s*<\/a>/', $actual );

		$this->assertMatchesRegularExpression( '/<\/article>/', $actual );

		$this->go_to( next_posts( 0, false ) );

		$actual = WPF_Template_Tags::get_paginate_authors();

		$this->assertMatchesRegularExpression( '/<article>/', $actual );
		$user11_id = get_user_by( 'slug', 'user_11' )->ID;
		$this->assertMatchesRegularExpression( '/href="[^"]*\?author=' . $user11_id . '[^"]*"[^>]*tabindex="-1"[^>]*title="User_11のプロフィールを見る"[^>]*>\s*<img[^>]*alt=\'User_11\'/', $actual );
		$this->assertMatchesRegularExpression( '/href="[^"]*\?author=' . $user11_id . '[^"]*"[^>]*title="User_11のプロフィールを見る"[^>]*>\s*User_11\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/article>/', $actual );
	}

	/**
	 * 特定のユーザーを除外
	 *
	 * @covers ::get_paginate_authors
	 * @preserveGlobalState disabled
	 */
	public function test_get_paginate_authors_with_exclude_user() {
		for ( $i = 0; $i < 3; $i++ ) {
			self::factory()->user->create(
				array(
					'display_name'  => 'User_' . $i,
					'user_nicename' => 'user_' . $i,
					'role'          => 'author',
				)
			);
		}

		$this->go_to( home_url() );

		$args   = array(
			'nicename__not_in' => array( 'user_0' ),
		);
		$actual = WPF_Template_Tags::get_paginate_authors( $args );

		$this->assertMatchesRegularExpression( '/<article>/', $actual );

		// user_0 が含まれていないことを確認（より柔軟な正規表現）
		$this->assertDoesNotMatchRegularExpression( '/href="[^"]*\/author\/user_0\/[^"]*"/', $actual );
		$this->assertDoesNotMatchRegularExpression( '/alt=\'User_0\'/', $actual );
		$this->assertDoesNotMatchRegularExpression( '/>User_0</', $actual );

		// user_1 が含まれていることを確認（より柔軟な正規表現）
		$this->assertMatchesRegularExpression( '/href="[^"]*\/author\/user_1\/[^"]*"/', $actual );
		$this->assertMatchesRegularExpression( '/alt=\'User_1\'/', $actual );
		$this->assertDoesNotMatchRegularExpression( '/>User_1</', $actual );

		// user_2 が含まれていることを確認（より柔軟な正規表現）
		$this->assertMatchesRegularExpression( '/href="[^"]*\/author\/user_2\/[^"]*"/', $actual );
		$this->assertMatchesRegularExpression( '/alt=\'User_2\'/', $actual );
		$this->assertDoesNotMatchRegularExpression( '/>User_2</', $actual );

		$this->assertMatchesRegularExpression( '/<\/article>/', $actual );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( home_url() );

		$actual = WPF_Template_Tags::get_paginate_authors( $args );

		$this->assertMatchesRegularExpression( '/<article>/', $actual );

		// permalink構造変更後のテスト
		$user0_id = get_user_by( 'slug', 'user_0' )->ID;
		$user1_id = get_user_by( 'slug', 'user_1' )->ID;
		$user2_id = get_user_by( 'slug', 'user_2' )->ID;

		// user_0 が含まれていないことを確認
		$this->assertDoesNotMatchRegularExpression( '/href="[^"]*\?author=' . $user0_id . '[^"]*"/', $actual );
		$this->assertDoesNotMatchRegularExpression( '/alt=\'User_0\'/', $actual );
		$this->assertDoesNotMatchRegularExpression( '/>User_0</', $actual );

		// user_1 が含まれていることを確認
		$this->assertMatchesRegularExpression( '/href="[^"]*\?author=' . $user1_id . '[^"]*"/', $actual );
		$this->assertMatchesRegularExpression( '/alt=\'User_1\'/', $actual );
		$this->assertDoesNotMatchRegularExpression( '/>User_1</', $actual );

		// user_2 が含まれていることを確認
		$this->assertMatchesRegularExpression( '/href="[^"]*\?author=' . $user2_id . '[^"]*"/', $actual );
		$this->assertMatchesRegularExpression( '/alt=\'User_2\'/', $actual );
		$this->assertDoesNotMatchRegularExpression( '/>User_2</', $actual );

		$this->assertMatchesRegularExpression( '/<\/article>/', $actual );
	}

	/**
	 * 1ページあたりの表示件数を変更
	 *
	 * @covers ::get_paginate_authors
	 * @preserveGlobalState disabled
	 */
	public function test_get_paginate_authors_with_change_per_page() {
		for ( $i = 0; $i < 3; $i++ ) {
			self::factory()->user->create(
				array(
					'display_name'  => 'User_' . $i,
					'user_nicename' => 'user_' . $i,
					'role'          => 'author',
				)
			);
		}

		$this->go_to( home_url() );

		$args   = array(
			'number' => 2,
		);
		$actual = WPF_Template_Tags::get_paginate_authors( $args );

		$this->assertMatchesRegularExpression( '/<article>/', $actual );
		$this->assertMatchesRegularExpression( '/href="[^"]*\/author\/user_0\/[^"]*"[^>]*tabindex="-1"[^>]*title="User_0のプロフィールを見る"/', $actual );
		$this->assertMatchesRegularExpression( '/alt=\'User_0\'/', $actual );
		$this->assertMatchesRegularExpression( '/href="[^"]*\/author\/user_0\/[^"]*"[^>]*title="User_0のプロフィールを見る"[^>]*>\s*User_0\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/article>/', $actual );
		$this->assertMatchesRegularExpression( '/<nav aria-label="著者".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<span aria-current="page" class="page-numbers current">1<\/span>/', $actual );
		$this->assertMatchesRegularExpression( '/<a class="page-numbers" href="[^"]*\/page\/2\/">2<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a class="next page-numbers" href="[^"]*\/page\/2\/"><span>次へ<\/span>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/nav>/', $actual );

		$this->go_to( next_posts( 0, false ) );

		$actual = WPF_Template_Tags::get_paginate_authors( $args );

		$this->assertMatchesRegularExpression( '/<article>/', $actual );
		$this->assertMatchesRegularExpression( '/href="[^"]*\/author\/user_[12]\/[^"]*"[^>]*tabindex="-1"[^>]*title="User_[12]のプロフィールを見る"/', $actual );
		$this->assertMatchesRegularExpression( '/alt=\'User_[12]\'/', $actual );
		$this->assertMatchesRegularExpression( '/href="[^"]*\/author\/user_[12]\/[^"]*"[^>]*title="User_[12]のプロフィールを見る"[^>]*>\s*User_[12]\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/article>/', $actual );
		$this->assertMatchesRegularExpression( '/<nav aria-label="著者".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<a class="prev page-numbers" href="[^"]*\/page\/1\/">.*<span>前へ<\/span><\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a class="page-numbers" href="[^"]*\/page\/1\/">1<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<span aria-current="page" class="page-numbers current">2<\/span>/', $actual );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( home_url() );

		$actual = WPF_Template_Tags::get_paginate_authors( $args );

		$this->assertMatchesRegularExpression( '/<article>/', $actual );
		$this->assertMatchesRegularExpression( '/href="[^"]*\?author=\d+"[^>]*tabindex="-1"[^>]*title="User_\d+のプロフィールを見る"/', $actual );
		$this->assertMatchesRegularExpression( '/alt=\'User_\d+\'/', $actual );
		$this->assertMatchesRegularExpression( '/href="[^"]*\?author=\d+"[^>]*title="User_\d+のプロフィールを見る"[^>]*>\s*User_\d+\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/article>/', $actual );
		$this->assertMatchesRegularExpression( '/<nav aria-label="著者".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<span aria-current="page" class="page-numbers current">1<\/span>/', $actual );
		$this->assertMatchesRegularExpression( '/<a class="page-numbers" href="[^"]*\?paged=2">2<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a class="next page-numbers" href="[^"]*\?paged=2"><span>次へ<\/span>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/nav>/', $actual );

		$this->go_to( next_posts( 0, false ) );

		$actual = WPF_Template_Tags::get_paginate_authors( $args );

		$this->assertMatchesRegularExpression( '/<article>/', $actual );
		$this->assertMatchesRegularExpression( '/href="[^"]*\?author=\d+"[^>]*tabindex="-1"[^>]*title="User_\d+のプロフィールを見る"/', $actual );
		$this->assertMatchesRegularExpression( '/alt=\'User_\d+\'/', $actual );
		$this->assertMatchesRegularExpression( '/href="[^"]*\?author=\d+"[^>]*title="User_\d+のプロフィールを見る"[^>]*>\s*User_\d+\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/article>/', $actual );
		$this->assertMatchesRegularExpression( '/<nav aria-label="著者".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<a class="prev page-numbers" href="[^"]*\?paged=1">.*<span>前へ<\/span><\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a class="page-numbers" href="[^"]*\?paged=1">1<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<span aria-current="page" class="page-numbers current">2<\/span>/', $actual );
	}
}
