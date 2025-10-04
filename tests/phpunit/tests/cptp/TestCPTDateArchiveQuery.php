<?php

/**
 * CPT日付アーカイブのクエリが wp_query オブジェクトに及ぼす影響をテストする。
 *
 * @group query
 * @group cpt_date_archive
 * @covers WPF_CPT_Rewrite
 * @covers WPF_CPT_Permalink
 */
class TestCPTDateArchiveQuery extends WPF_UnitTestCase {

	/**
	 * @group cpt_year_archive
	 * @covers WPF_CPT_Rewrite::register_post_type_rules
	 * @covers WPF_CPT_Permalink::post_type_link
	 * @preserveGlobalState disabled
	 * @dataProvider data_cpt_permastruct
	 *
	 * @param string $cpt_permastruct CPTパーマリンク構造。
	 */
	public function test_cpt_year_archive( $cpt_permastruct ) {
		$test_contents = self::create_cpt_test_contents(
			array(
				'post_type' => array(
					'wpf_cptp' => array(
						'permalink_structure' => $cpt_permastruct,
					),
				),
			)
		);

		$cpt_slug   = get_post_type_object( $test_contents['post_type'] )->rewrite['slug'];
		$date_front = str_replace( WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $test_contents['post_type'] ), $cpt_slug, WPF_CPT_Rewrite::get_date_front( $test_contents['post_type'] ) );
		$date_link  = home_url( user_trailingslashit( $date_front . '2012' ) );

		$this->go_to( $date_link );
		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_year' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $date_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_year_permastruct_name( $test_contents['post_type'] ) );
		}

		$this->go_to( next_posts( 0, false ) );
		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_year', 'is_paged' );
	}

	/**
	 * @group cpt_year_archive
	 * @covers WPF_CPT_Rewrite::register_post_type_rules
	 * @covers WPF_CPT_Permalink::post_type_link
	 * @preserveGlobalState disabled
	 * @dataProvider data_cpt_permastruct
	 *
	 * @param string $cpt_permastruct CPTパーマリンク構造。
	 */
	public function test_cpt_year_archive_with_front( $cpt_permastruct ) {
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/foo/%year%/%monthnum%/%day%/%postname%/' );

		$test_contents = self::create_cpt_test_contents(
			array(
				'post_type' => array(
					'wpf_cptp' => array(
						'permalink_structure' => $cpt_permastruct,
					),
				),
			)
		);

		$cpt_slug   = get_post_type_object( $test_contents['post_type'] )->rewrite['slug'];
		$date_front = str_replace( WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $test_contents['post_type'] ), $cpt_slug, WPF_CPT_Rewrite::get_date_front( $test_contents['post_type'] ) );
		$date_link  = home_url( user_trailingslashit( '/foo/' . $date_front . '2012' ) );

		$this->go_to( $date_link );
		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_year' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $date_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_year_permastruct_name( $test_contents['post_type'] ) );
		}

		$this->go_to( next_posts( 0, false ) );
		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_year', 'is_paged' );
	}

	/**
	 * @group cpt_month_archive
	 * @covers WPF_CPT_Rewrite::register_post_type_rules
	 * @covers WPF_CPT_Permalink::post_type_link
	 * @preserveGlobalState disabled
	 * @dataProvider data_cpt_permastruct
	 *
	 * @param string $cpt_permastruct CPTパーマリンク構造。
	 */
	public function test_cpt_month_archive( $cpt_permastruct ) {
		$test_contents = self::create_cpt_test_contents(
			array(
				'post_type' => array(
					'wpf_cptp' => array(
						'permalink_structure' => $cpt_permastruct,
					),
				),
			)
		);

		$cpt_slug   = get_post_type_object( $test_contents['post_type'] )->rewrite['slug'];
		$date_front = str_replace( WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $test_contents['post_type'] ), $cpt_slug, WPF_CPT_Rewrite::get_date_front( $test_contents['post_type'] ) );
		$date_link  = home_url( user_trailingslashit( $date_front . '2012/12' ) );

		$this->go_to( $date_link );
		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_month' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $date_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_month_permastruct_name( $test_contents['post_type'] ) );
		}

		$this->go_to( next_posts( 0, false ) );
		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_month', 'is_paged' );
	}

	/**
	 * @group cpt_month_archive
	 * @covers WPF_CPT_Rewrite::register_post_type_rules
	 * @covers WPF_CPT_Permalink::post_type_link
	 * @preserveGlobalState disabled
	 * @dataProvider data_cpt_permastruct
	 *
	 * @param string $cpt_permastruct CPTパーマリンク構造。
	 */
	public function test_cpt_month_archive_with_front( $cpt_permastruct ) {
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/foo/%year%/%monthnum%/%day%/%postname%/' );

		$test_contents = self::create_cpt_test_contents(
			array(
				'post_type' => array(
					'wpf_cptp' => array(
						'permalink_structure' => $cpt_permastruct,
					),
				),
			)
		);

		$cpt_slug   = get_post_type_object( $test_contents['post_type'] )->rewrite['slug'];
		$date_front = str_replace( WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $test_contents['post_type'] ), $cpt_slug, WPF_CPT_Rewrite::get_date_front( $test_contents['post_type'] ) );
		$date_link  = home_url( user_trailingslashit( '/foo/' . $date_front . '2012/12' ) );

		$this->go_to( $date_link );
		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_month' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $date_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_month_permastruct_name( $test_contents['post_type'] ) );
		}

		$this->go_to( next_posts( 0, false ) );
		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_month', 'is_paged' );
	}

	/**
	 * @group cpt_day_archive
	 * @covers WPF_CPT_Rewrite::register_post_type_rules
	 * @covers WPF_CPT_Permalink::post_type_link
	 * @preserveGlobalState disabled
	 * @dataProvider data_cpt_permastruct
	 *
	 * @param string $cpt_permastruct CPTパーマリンク構造。
	 */
	public function test_cpt_day_archive( $cpt_permastruct ) {
		$test_contents = self::create_cpt_test_contents(
			array(
				'post_type' => array(
					'wpf_cptp' => array(
						'permalink_structure' => $cpt_permastruct,
					),
				),
			)
		);

		$cpt_slug   = get_post_type_object( $test_contents['post_type'] )->rewrite['slug'];
		$date_front = str_replace( WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $test_contents['post_type'] ), $cpt_slug, WPF_CPT_Rewrite::get_date_front( $test_contents['post_type'] ) );
		$date_link  = home_url( user_trailingslashit( $date_front . '2012/12/12' ) );

		$this->go_to( $date_link );
		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_day' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $date_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_date_permastruct_name( $test_contents['post_type'] ) );
		}

		$this->go_to( next_posts( 0, false ) );
		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_day', 'is_paged' );
	}

	/**
	 * @group cpt_day_archive
	 * @covers WPF_CPT_Rewrite::register_post_type_rules
	 * @covers WPF_CPT_Permalink::post_type_link
	 * @preserveGlobalState disabled
	 * @dataProvider data_cpt_permastruct
	 *
	 * @param string $cpt_permastruct CPTパーマリンク構造。
	 */
	public function test_cpt_day_archive_with_front( $cpt_permastruct ) {
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/foo/%year%/%monthnum%/%day%/%postname%/' );

		$test_contents = self::create_cpt_test_contents(
			array(
				'post_type' => array(
					'wpf_cptp' => array(
						'permalink_structure' => $cpt_permastruct,
					),
				),
			)
		);

		$cpt_slug   = get_post_type_object( $test_contents['post_type'] )->rewrite['slug'];
		$date_front = str_replace( WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $test_contents['post_type'] ), $cpt_slug, WPF_CPT_Rewrite::get_date_front( $test_contents['post_type'] ) );
		$date_link  = home_url( user_trailingslashit( '/foo/' . $date_front . '2012/12/12' ) );

		$this->go_to( $date_link );
		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_day' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $date_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_date_permastruct_name( $test_contents['post_type'] ) );
		}

		$this->go_to( next_posts( 0, false ) );
		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_day', 'is_paged' );
	}

	/**
	 * @covers WPF_CPT_Rewrite::register_post_type_rules
	 * @covers WPF_CPT_Permalink::post_type_link
	 * @preserveGlobalState disabled
	 * @dataProvider data_cpt_permastruct
	 *
	 * @param string $cpt_permastruct CPTパーマリンク構造。
	 */
	public function test_cpt_date_archive_disabled( $cpt_permastruct ) {
		$test_contents = self::create_cpt_test_contents(
			array(
				'post_type' => array(
					'wpf_cptp' => array(
						'date_archive'        => false,
						'permalink_structure' => $cpt_permastruct,
					),
				),
			)
		);

		$cpt_slug   = get_post_type_object( $test_contents['post_type'] )->rewrite['slug'];
		$date_front = str_replace( WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $test_contents['post_type'] ), $cpt_slug, WPF_CPT_Rewrite::get_date_front( $test_contents['post_type'] ) );
		$this->go_to( home_url( user_trailingslashit( $date_front . '2012' ) ) );
		$this->assertQueryTrue( 'is_404' );
		$this->go_to( home_url( user_trailingslashit( $date_front . '2012/12' ) ) );
		$this->assertQueryTrue( 'is_404' );
		$this->go_to( home_url( user_trailingslashit( $date_front . '2012/12/12' ) ) );
		$this->assertQueryTrue( 'is_404' );
	}

	/**
	 * FIXME: has_archive に文字列を指定したテストはクラスの最後に実行しないと
	 * 後のテストに影響を与えるよう。リセットできていない変数があるはず。
	 *
	 * @group cpt_day_archive
	 * @covers WPF_CPT_Rewrite::register_post_type_rules
	 * @covers WPF_CPT_Permalink::post_type_link
	 * @preserveGlobalState disabled
	 * @dataProvider data_cpt_permastruct
	 *
	 * @param string $cpt_permastruct CPTパーマリンク構造。
	 */
	public function test_cpt_day_archive_with_has_archive_is_string( $cpt_permastruct ) {
		$test_contents = self::create_cpt_test_contents(
			array(
				'post_type' => array(
					'has_archive' => 'foo',
					'wpf_cptp'    => array(
						'permalink_structure' => $cpt_permastruct,
					),
				),
			)
		);

		$cpt_slug   = get_post_type_object( $test_contents['post_type'] )->has_archive;
		$date_front = str_replace( WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $test_contents['post_type'] ), $cpt_slug, WPF_CPT_Rewrite::get_date_front( $test_contents['post_type'] ) );
		$this->go_to( home_url( user_trailingslashit( $date_front . '2012/12/12' ) ) );
		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_day' );
		$this->go_to( next_posts( 0, false ) );
		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_day', 'is_paged' );
	}
}
