<?php

/**
 * CPT著者アーカイブのクエリが wp_query オブジェクトに及ぼす影響をテストする。
 *
 * @group query
 * @group cpt_author_archive
 * @covers WPF_CPT_Rewrite
 * @covers WPF_CPT_Permalink
 */
class TestCPTAuthorArchiveQuery extends WPF_UnitTestCase {

	/**
	 * @covers WPF_CPT_Rewrite::register_post_type_rules
	 * @covers WPF_CPT_Permalink::post_type_link
	 * @preserveGlobalState disabled
	 * @dataProvider data_cpt_permastruct
	 *
	 * @param string $cpt_permastruct CPTパーマリンク構造。
	 */
	public function test_cpt_author_archive( $cpt_permastruct ) {
		$test_contents = self::create_cpt_test_contents(
			array(
				'post_type' => array(
					'wpf_cptp' => array(
						'permalink_structure' => $cpt_permastruct,
					),
				),
			)
		);

		$cpt_slug           = get_post_type_object( $test_contents['post_type'] )->rewrite['slug'];
		$user               = get_userdata( $test_contents['user_id'] );
		$author_permastruct = WPF_CPT_Rewrite::get_author_permastruct( $test_contents['post_type'] );
		$author_link        = home_url( user_trailingslashit( str_replace( array( WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $test_contents['post_type'] ), '%author%' ), array( $cpt_slug, $user->user_nicename ), $author_permastruct ) ) );

		$this->go_to( $author_link );
		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_author' );
		$this->go_to( next_posts( 0, false ) );
		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_author', 'is_paged' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $author_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_author_permastruct_name( $test_contents['post_type'] ) );
		}
	}

	/**
	 * @covers WPF_CPT_Rewrite::register_post_type_rules
	 * @covers WPF_CPT_Permalink::post_type_link
	 * @preserveGlobalState disabled
	 * @dataProvider data_cpt_permastruct
	 *
	 * @param string $cpt_permastruct CPTパーマリンク構造。
	 */
	public function test_cpt_author_archive_change_author_base( $cpt_permastruct ) {
		$test_contents = self::create_cpt_test_contents(
			array(
				'post_type' => array(
					'wpf_cptp' => array(
						'author_archive'      => 'foo', // 著者ベースを変更にする。
						'permalink_structure' => $cpt_permastruct,
					),
				),
			)
		);

		$cpt_slug           = get_post_type_object( $test_contents['post_type'] )->rewrite['slug'];
		$user               = get_userdata( $test_contents['user_id'] );
		$author_permastruct = WPF_CPT_Rewrite::get_author_permastruct( $test_contents['post_type'] );
		$author_link        = home_url( user_trailingslashit( str_replace( array( WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $test_contents['post_type'] ), '%author%' ), array( $cpt_slug, $user->user_nicename ), $author_permastruct ) ) );

		$this->go_to( $author_link );
		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_author' );
		$this->go_to( next_posts( 0, false ) );
		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_author', 'is_paged' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $author_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_author_permastruct_name( $test_contents['post_type'] ) );
		}
	}

	/**
	 * @covers WPF_CPT_Rewrite::register_post_type_rules
	 * @covers WPF_CPT_Permalink::post_type_link
	 * @preserveGlobalState disabled
	 * @dataProvider data_cpt_permastruct
	 *
	 * @param string $cpt_permastruct CPTパーマリンク構造。
	 */
	public function test_cpt_author_archive_with_front( $cpt_permastruct ) {
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

		$cpt_slug           = get_post_type_object( $test_contents['post_type'] )->rewrite['slug'];
		$user               = get_userdata( $test_contents['user_id'] );
		$author_permastruct = WPF_CPT_Rewrite::get_author_permastruct( $test_contents['post_type'] );
		$author_link        = home_url( '/foo/' . user_trailingslashit( str_replace( array( WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $test_contents['post_type'] ), '%author%' ), array( $cpt_slug, $user->user_nicename ), $author_permastruct ) ) );

		$this->go_to( $author_link );
		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_author' );
		$this->go_to( next_posts( 0, false ) );
		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_author', 'is_paged' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $author_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_author_permastruct_name( $test_contents['post_type'] ) );
		}
	}

	/**
	 * @covers WPF_CPT_Rewrite::register_post_type_rules
	 * @covers WPF_CPT_Permalink::post_type_link
	 * @preserveGlobalState disabled
	 * @dataProvider data_cpt_permastruct
	 *
	 * @param string $cpt_permastruct CPTパーマリンク構造。
	 */
	public function test_cpt_author_archive_disabled( $cpt_permastruct ) {
		$test_contents = self::create_cpt_test_contents(
			array(
				'post_type' => array(
					'wpf_cptp' => array(
						'author_archive'      => false, // 著者ベースを無効にする。
						'permalink_structure' => $cpt_permastruct,
					),
				),
			)
		);

		$cpt_slug           = get_post_type_object( $test_contents['post_type'] )->rewrite['slug'];
		$user               = get_userdata( $test_contents['user_id'] );
		$author_permastruct = WPF_CPT_Rewrite::get_author_permastruct( $test_contents['post_type'] );
		$author_link        = home_url( user_trailingslashit( str_replace( array( WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $test_contents['post_type'] ), '%author%' ), array( $cpt_slug, $user->user_nicename ), $author_permastruct ) ) );
		$this->go_to( $author_link );
		$this->assertQueryTrue( 'is_404' );
	}

	/**
	 * FIXME: has_archive に文字列を指定したテストはクラスの最後に実行しないと
	 * 後のテストに影響を与えるようだ。リセットできていない変数があるはず。
	 *
	 * @covers WPF_CPT_Rewrite::register_post_type_rules
	 * @covers WPF_CPT_Permalink::post_type_link
	 * @preserveGlobalState disabled
	 * @dataProvider data_cpt_permastruct
	 *
	 * @param string $cpt_permastruct CPTパーマリンク構造。
	 */
	public function test_cpt_author_archive_with_has_archive_is_string( $cpt_permastruct ) {
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

		$cpt_slug           = get_post_type_object( $test_contents['post_type'] )->has_archive;
		$user               = get_userdata( $test_contents['user_id'] );
		$author_permastruct = WPF_CPT_Rewrite::get_author_permastruct( $test_contents['post_type'] );
		$author_link        = home_url( user_trailingslashit( str_replace( array( WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $test_contents['post_type'] ), '%author%' ), array( $cpt_slug, $user->user_nicename ), $author_permastruct ) ) );

		$this->go_to( $author_link );
		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_author' );
		$this->go_to( next_posts( 0, false ) );
		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_author', 'is_paged' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $author_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_author_permastruct_name( $test_contents['post_type'] ) );
		}
	}
}
