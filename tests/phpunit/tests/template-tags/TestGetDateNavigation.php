<?php

/**
 * WPF_Template_Tags::get_date_navigation のユニットテスト。
 *
 * @group template_tags
 * @covers WPF_Template_Tags
 * @coversDefaultClass WPF_Template_Tags
 */
class TestGetDateNavigation extends WPF_UnitTestCase {

	/**
	 * @covers ::get_date_navigation
	 * @covers WPF_Template_Functions::add_attributes_to_archives_link
	 * @preserveGlobalState disabled
	 */
	public function test_get_date_navigation() {
		$posts = self::factory()->post->create_many( 10, array( 'post_date' => '2012-12-12' ) );

		// 年
		$this->go_to( home_url( user_trailingslashit( '/2012' ) ) );

		$this->assertQueryTrue( 'is_archive', 'is_date', 'is_year' );

		$actual = WPF_Template_Tags::get_date_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="日付".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org".*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href=\'http:\/\/example.org\/2012\/\' aria-current="page">2012<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		$this->go_to( next_posts( 0, false ) );

		$this->assertQueryTrue( 'is_archive', 'is_date', 'is_year', 'is_paged' );

		$actual = WPF_Template_Tags::get_date_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="日付".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org".*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href=\'http:\/\/example.org\/2012\/\' aria-current="page">2012<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		// 年月
		$this->go_to( home_url( user_trailingslashit( '/2012/12' ) ) );

		$this->assertQueryTrue( 'is_archive', 'is_date', 'is_month' );

		$actual = WPF_Template_Tags::get_date_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="日付".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org".*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href=\'http:\/\/example.org\/2012\/12\/\' aria-current="page">December 2012<\/a>/', $actual ); // TODO: テスト環境で wp-content/languages/ja.mo を適用する方法を見つける。
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		$this->go_to( next_posts( 0, false ) );

		$this->assertQueryTrue( 'is_archive', 'is_date', 'is_month', 'is_paged' );

		$actual = WPF_Template_Tags::get_date_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="日付".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org".*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href=\'http:\/\/example.org\/2012\/12\/\' aria-current="page">December 2012<\/a>/', $actual ); // TODO: テスト環境で wp-content/languages/ja.mo を適用する方法を見つける。
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		// 年月日
		$this->go_to( home_url( user_trailingslashit( '/2012/12/12' ) ) );

		$this->assertQueryTrue( 'is_archive', 'is_date', 'is_day' );

		$actual = WPF_Template_Tags::get_date_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="日付".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org".*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href=\'http:\/\/example.org\/2012\/12\/12\/\' aria-current="page">2012年12月12日<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		$this->go_to( next_posts( 0, false ) );

		$this->assertQueryTrue( 'is_archive', 'is_date', 'is_day', 'is_paged' );

		$actual = WPF_Template_Tags::get_date_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="日付".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org".*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href=\'http:\/\/example.org\/2012\/12\/12\/\' aria-current="page">2012年12月12日<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		// 年
		$this->go_to( home_url( user_trailingslashit( '/2012' ) ) );

		$this->assertQueryTrue( 'is_archive', 'is_date', 'is_year' );

		$actual = WPF_Template_Tags::get_date_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="日付".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org".*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href=\'http:\/\/example.org\/\?m=2012\' aria-current="page">2012<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		$this->go_to( next_posts( 0, false ) );

		$this->assertQueryTrue( 'is_archive', 'is_date', 'is_year', 'is_paged' );

		$actual = WPF_Template_Tags::get_date_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="日付".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org".*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href=\'http:\/\/example.org\/\?m=2012\' aria-current="page">2012<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		// 年月
		$this->go_to( home_url( user_trailingslashit( '/2012/12' ) ) );

		$this->assertQueryTrue( 'is_archive', 'is_date', 'is_month' );

		$actual = WPF_Template_Tags::get_date_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="日付".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org".*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href=\'http:\/\/example.org\/\?m=201212\' aria-current="page">December 2012<\/a>/', $actual ); // TODO: テスト環境で wp-content/languages/ja.mo を適用する方法を見つける。
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		$this->go_to( next_posts( 0, false ) );

		$this->assertQueryTrue( 'is_archive', 'is_date', 'is_month', 'is_paged' );

		$actual = WPF_Template_Tags::get_date_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="日付".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org".*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href=\'http:\/\/example.org\/\?m=201212\' aria-current="page">December 2012<\/a>/', $actual ); // TODO: テスト環境で wp-content/languages/ja.mo を適用する方法を見つける。
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		// 年月日
		$this->go_to( home_url( user_trailingslashit( '/2012/12/12' ) ) );

		$this->assertQueryTrue( 'is_archive', 'is_date', 'is_day' );

		$actual = WPF_Template_Tags::get_date_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="日付".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org".*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href=\'http:\/\/example.org\/\?m=20121212\' aria-current="page">2012年12月12日<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		$this->go_to( next_posts( 0, false ) );

		$this->assertQueryTrue( 'is_archive', 'is_date', 'is_day', 'is_paged' );

		$actual = WPF_Template_Tags::get_date_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="日付".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org".*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href=\'http:\/\/example.org\/\?m=20121212\' aria-current="page">2012年12月12日<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );
	}

	/**
	 * @covers ::get_date_navigation
	 * @covers WPF_Template_Functions::add_attributes_to_archives_link
	 * @preserveGlobalState disabled
	 */
	public function test_get_date_navigation_with_cpt() {
		register_post_type(
			'cpt',
			array(
				'public'      => true,
				'has_archive' => true,
			)
		);

		$posts = self::factory()->post->create_many(
			10,
			array(
				'post_type' => 'cpt',
				'post_date' => '2012-12-12',
			)
		);

		// 年
		$this->go_to( home_url( user_trailingslashit( '/cpt/date/2012' ) ) );

		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_year' );

		$actual = WPF_Template_Tags::get_date_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="日付".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/cpt\/".*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href=\'http:\/\/example.org\/cpt\/date\/2012\/\' aria-current="page">2012<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		$this->go_to( next_posts( 0, false ) );

		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_year', 'is_paged' );

		$actual = WPF_Template_Tags::get_date_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="日付".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/cpt\/".*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href=\'http:\/\/example.org\/cpt\/date\/2012\/\' aria-current="page">2012<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		// 年月
		$this->go_to( home_url( user_trailingslashit( '/cpt/date/2012/12' ) ) );

		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_month' );

		$actual = WPF_Template_Tags::get_date_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="日付".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/cpt\/".*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href=\'http:\/\/example.org\/cpt\/date\/2012\/12\/\' aria-current="page">December 2012<\/a>/', $actual ); // TODO: テスト環境で wp-content/languages/ja.mo を適用する方法を見つける。
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		$this->go_to( next_posts( 0, false ) );

		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_month', 'is_paged' );

		$actual = WPF_Template_Tags::get_date_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="日付".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/cpt\/".*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href=\'http:\/\/example.org\/cpt\/date\/2012\/12\/\' aria-current="page">December 2012<\/a>/', $actual ); // TODO: テスト環境で wp-content/languages/ja.mo を適用する方法を見つける。
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		// 年月日
		$this->go_to( home_url( user_trailingslashit( '/cpt/date/2012/12/12' ) ) );

		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_day' );

		$actual = WPF_Template_Tags::get_date_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="日付".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/cpt\/".*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href=\'http:\/\/example.org\/cpt\/date\/2012\/12\/12\/\' aria-current="page">2012年12月12日<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		$this->go_to( next_posts( 0, false ) );

		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_day', 'is_paged' );

		$actual = WPF_Template_Tags::get_date_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="日付".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/cpt\/".*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href=\'http:\/\/example.org\/cpt\/date\/2012\/12\/12\/\' aria-current="page">2012年12月12日<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		// 年
		$this->go_to( home_url( user_trailingslashit( '/cpt/date/2012' ) ) );

		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_year' );

		$actual = WPF_Template_Tags::get_date_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="日付".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/\?post_type=cpt".*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href=\'http:\/\/example.org\/\?m=2012&#038;post_type=cpt\' aria-current="page">2012<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		$this->go_to( next_posts( 0, false ) );

		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_year', 'is_paged' );

		$actual = WPF_Template_Tags::get_date_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="日付".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/\?post_type=cpt".*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href=\'http:\/\/example.org\/\?m=2012&#038;post_type=cpt\' aria-current="page">2012<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		// 年月
		$this->go_to( home_url( user_trailingslashit( '/cpt/date/2012/12' ) ) );

		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_month' );

		$actual = WPF_Template_Tags::get_date_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="日付".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/\?post_type=cpt".*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href=\'http:\/\/example.org\/\?m=201212&#038;post_type=cpt\' aria-current="page">December 2012<\/a>/', $actual ); // TODO: テスト環境で wp-content/languages/ja.mo を適用する方法を見つける。
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		$this->go_to( next_posts( 0, false ) );

		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_month', 'is_paged' );

		$actual = WPF_Template_Tags::get_date_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="日付".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/\?post_type=cpt".*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href=\'http:\/\/example.org\/\?m=201212&#038;post_type=cpt\' aria-current="page">December 2012<\/a>/', $actual ); // TODO: テスト環境で wp-content/languages/ja.mo を適用する方法を見つける。
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		// 年月日
		$this->go_to( home_url( user_trailingslashit( '/cpt/date/2012/12/12' ) ) );

		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_day' );

		$actual = WPF_Template_Tags::get_date_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="日付".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/\?post_type=cpt".*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href=\'http:\/\/example.org\/\?m=20121212&#038;post_type=cpt\' aria-current="page">2012年12月12日<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		$this->go_to( next_posts( 0, false ) );

		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_day', 'is_paged' );

		$actual = WPF_Template_Tags::get_date_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="日付".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org\/\?post_type=cpt".*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href=\'http:\/\/example.org\/\?m=20121212&#038;post_type=cpt\' aria-current="page">2012年12月12日<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );
	}

	/**
	 * @covers ::get_date_navigation
	 * @covers WPF_Template_Functions::add_attributes_to_archives_link
	 * @preserveGlobalState disabled
	 */
	public function test_get_date_navigation_with_weekly() {
		$posts = self::factory()->post->create_many( 10, array( 'post_date' => '2012-12-12' ) );

		$this->go_to( home_url() );

		$this->assertQueryTrue( 'is_front_page', 'is_home' );

		$args   = array(
			'type' => 'weekly',
		);
		$actual = WPF_Template_Tags::get_date_navigation( $args );

		$this->assertMatchesRegularExpression( '/<nav aria-label="日付".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a href="http:\/\/example.org".*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href=\'http:\/\/example.org\/\?m=2012&#038;w=50\'>2012年12月10日&#8211;2012年12月16日<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );
	}

	/**
	 * @covers ::get_date_navigation
	 * @covers WPF_Template_Functions::add_attributes_to_archives_link
	 * @preserveGlobalState disabled
	 */
	public function test_get_date_navigation_with_hide_top_link() {
		$posts = self::factory()->post->create_many( 10, array( 'post_date' => '2012-12-12' ) );

		$this->go_to( home_url( user_trailingslashit( '/2012' ) ) );

		$this->assertQueryTrue( 'is_archive', 'is_date', 'is_year' );

		$actual = WPF_Template_Tags::get_date_navigation( '', false );

		$this->assertMatchesRegularExpression( '/<nav aria-label="日付".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertDoesNotMatchRegularExpression( '/<a href="http:\/\/example.org".*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a href=\'http:\/\/example.org\/2012\/\' aria-current="page">2012<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );
	}
}
