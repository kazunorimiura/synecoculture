<?php

/**
 * CPT個別投稿のクエリが wp_query オブジェクトに及ぼす影響をテストする。
 *
 * @group query
 * @group cpt_post
 * @covers WPF_CPT_Rewrite
 * @covers WPF_CPT_Permalink
 */
class TestCPTPostQuery extends WPF_UnitTestCase {

	/**
	 * @covers WPF_CPT_Rewrite::register_post_type_rules
	 * @covers WPF_CPT_Permalink::post_type_link
	 * @preserveGlobalState disabled
	 * @dataProvider data_cpt_permastruct
	 *
	 * @param string $cpt_permastruct CPTパーマリンク構造。
	 */
	public function test_cpt_post( $cpt_permastruct ) {
		$test_contents = self::create_cpt_test_contents(
			array(
				'post_type' => array(
					'wpf_cptp' => array(
						'permalink_structure' => $cpt_permastruct,
					),
				),
			)
		);

		$cpt_post_link = get_permalink( $test_contents['post_id'] );
		$this->assertSame( $test_contents['post_id'], url_to_postid( $cpt_post_link ) );
		$this->go_to( $cpt_post_link );
		$this->assertQueryTrue( 'is_single', 'is_singular' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $cpt_post_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_cpt_permastruct_name( $test_contents['post_type'] ) );
		}

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $cpt_post_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_cpt_permastruct_name( $test_contents['post_type'] ) );
		}

		// コメント
		$this->factory->comment->create_post_comments( $test_contents['post_type'], 15 );
		$next_comment_page_link = $cpt_post_link . 'comment-page-2';
		$this->go_to( $next_comment_page_link );
		$this->assertEquals( get_query_var( 'cpage' ), 2 );
		$this->assertQueryTrue( 'is_single', 'is_singular' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $next_comment_page_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_cpt_permastruct_name( $test_contents['post_type'] ) );
		}

		// 改ページ
		$next_page_link = user_trailingslashit( rtrim( $cpt_post_link, '/' ) . '/page/2' ); // 長いバージョン。
		$this->go_to( $next_page_link );
		$this->assertQueryTrue( 'is_single', 'is_singular', 'is_paged' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $next_page_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_cpt_permastruct_name( $test_contents['post_type'] ) );
		}

		$next_page_link = user_trailingslashit( rtrim( $cpt_post_link, '/' ) . '/2' ); // 短いバージョン。
		$this->go_to( $next_page_link );
		$this->assertQueryTrue( 'is_single', 'is_singular' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $next_page_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_cpt_permastruct_name( $test_contents['post_type'] ) );
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
	public function test_cpt_post_with_hierarchical_cpt( $cpt_permastruct ) {
		$test_contents = self::create_cpt_test_contents(
			array(
				'post_type' => array(
					'hierarchical' => true,
					'wpf_cptp'     => array(
						'permalink_structure' => $cpt_permastruct,
					),
				),
			)
		);

		$cpt_post_link = get_permalink( $test_contents['post_id'] );

		$this->assertSame( $test_contents['post_id'], url_to_postid( $cpt_post_link ) );
		$this->go_to( $cpt_post_link );
		$this->assertQueryTrue( 'is_single', 'is_singular' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $cpt_post_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_cpt_permastruct_name( $test_contents['post_type'] ) );
		}

		// コメント
		$this->factory->comment->create_post_comments( $test_contents['post_type'], 15 );
		$next_comment_page_link = $cpt_post_link . 'comment-page-2';
		$this->go_to( $next_comment_page_link );
		$this->assertEquals( get_query_var( 'cpage' ), 2 );
		$this->assertQueryTrue( 'is_single', 'is_singular' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $next_comment_page_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_cpt_permastruct_name( $test_contents['post_type'] ) );
		}

		// 改ページ
		$next_page_link = user_trailingslashit( rtrim( $cpt_post_link, '/' ) . '/page/2' ); // 長いバージョン。
		$this->go_to( $next_page_link );
		$this->assertQueryTrue( 'is_single', 'is_singular', 'is_paged' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $next_page_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_cpt_permastruct_name( $test_contents['post_type'] ) );
		}

		$next_page_link = user_trailingslashit( rtrim( $cpt_post_link, '/' ) . '/2' ); // 短いバージョン。
		$this->go_to( $next_page_link );
		$this->assertQueryTrue( 'is_single', 'is_singular' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $next_page_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_cpt_permastruct_name( $test_contents['post_type'] ) );
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
	public function test_cpt_post_with_hierarchical_tax( $cpt_permastruct ) {
		$test_contents = self::create_cpt_test_contents(
			array(
				'tax'       => array(
					'hierarchical' => true,
					'rewrite'      => array(
						'hierarchical' => true,
					),
				),
				'post_type' => array(
					'wpf_cptp' => array(
						'permalink_structure' => $cpt_permastruct,
					),
				),
			)
		);

		$cpt_post_link = get_permalink( $test_contents['post_id'] );

		$this->assertSame( $test_contents['post_id'], url_to_postid( $cpt_post_link ) );
		$this->go_to( $cpt_post_link );
		$this->assertQueryTrue( 'is_single', 'is_singular' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $cpt_post_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_cpt_permastruct_name( $test_contents['post_type'] ) );
		}

		// タームをすべて選択した場合、一番若いIDのタームスラッグが使用される。
		wp_set_post_terms( $test_contents['post_id'], $test_contents['term_ids'], $test_contents['taxonomy'] );
		$cpt_post_link = get_permalink( $test_contents['post_id'] );
		$this->assertSame( $test_contents['post_id'], url_to_postid( $cpt_post_link ) );
		$this->go_to( $cpt_post_link );
		$this->assertQueryTrue( 'is_single', 'is_singular' );

		// コメント
		$this->factory->comment->create_post_comments( $test_contents['post_type'], 15 );
		$next_comment_page_link = $cpt_post_link . 'comment-page-2';
		$this->go_to( $next_comment_page_link );
		$this->assertEquals( get_query_var( 'cpage' ), 2 );
		$this->assertQueryTrue( 'is_single', 'is_singular' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $next_comment_page_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_cpt_permastruct_name( $test_contents['post_type'] ) );
		}

		// 改ページ
		$next_page_link = user_trailingslashit( rtrim( $cpt_post_link, '/' ) . '/page/2' ); // 長いバージョン。
		$this->go_to( $next_page_link );
		$this->assertQueryTrue( 'is_single', 'is_singular', 'is_paged' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $next_page_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_cpt_permastruct_name( $test_contents['post_type'] ) );
		}

		$next_page_link = user_trailingslashit( rtrim( $cpt_post_link, '/' ) . '/2' ); // 短いバージョン。
		$this->go_to( $next_page_link );
		$this->assertQueryTrue( 'is_single', 'is_singular' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $next_page_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_cpt_permastruct_name( $test_contents['post_type'] ) );
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
	public function test_cpt_post_with_front( $cpt_permastruct ) {
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

		$cpt_post_link = get_permalink( $test_contents['post_id'] );

		$this->assertSame( $test_contents['post_id'], url_to_postid( $cpt_post_link ) );
		$this->go_to( $cpt_post_link );
		$this->assertQueryTrue( 'is_single', 'is_singular' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $cpt_post_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_cpt_permastruct_name( $test_contents['post_type'] ) );
		}

		// コメント
		$this->factory->comment->create_post_comments( $test_contents['post_type'], 15 );
		$next_comment_page_link = $cpt_post_link . 'comment-page-2';
		$this->go_to( $next_comment_page_link );
		$this->assertEquals( get_query_var( 'cpage' ), 2 );
		$this->assertQueryTrue( 'is_single', 'is_singular' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $next_comment_page_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_cpt_permastruct_name( $test_contents['post_type'] ) );
		}

		// 改ページ
		$next_page_link = user_trailingslashit( rtrim( $cpt_post_link, '/' ) . '/page/2' ); // 長いバージョン。
		$this->go_to( $next_page_link );
		$this->assertQueryTrue( 'is_single', 'is_singular', 'is_paged' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $next_page_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_cpt_permastruct_name( $test_contents['post_type'] ) );
		}

		$next_page_link = user_trailingslashit( rtrim( $cpt_post_link, '/' ) . '/2' ); // 短いバージョン。
		$this->go_to( $next_page_link );
		$this->assertQueryTrue( 'is_single', 'is_singular' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $next_page_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_cpt_permastruct_name( $test_contents['post_type'] ) );
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
	public function test_cpt_post_with_private_post_type( $cpt_permastruct ) {
		$test_contents = self::create_cpt_test_contents(
			array(
				'post_type' => array(
					'public'   => false,
					'wpf_cptp' => array(
						'permalink_structure' => $cpt_permastruct,
					),
				),
			)
		);

		$cpt_post_link = get_permalink( $test_contents['post_id'] );

		// URLがデフォルトのままであることを確認する。
		global $wp_rewrite;
		$this->assertSame( $cpt_post_link, home_url( user_trailingslashit( str_replace( '%' . $test_contents['post_type'] . '%', get_post( $test_contents['post_id'] )->post_name, $wp_rewrite->get_extra_permastruct( $test_contents['post_type'] ) ) ) ) );

		$this->go_to( $cpt_post_link );
		$this->assertFalse( is_single() );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $cpt_post_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, $test_contents['post_type'] );
		}
	}

	/**
	 * ターム未選択の場合、default_term 値がスラッグに設定されるか。
	 *
	 * @covers WPF_CPT_Rewrite::register_post_type_rules
	 * @covers WPF_CPT_Permalink::post_type_link
	 * @preserveGlobalState disabled
	 */
	public function test_cpt_post_when_term_not_selected() {
		$test_contents = self::create_cpt_test_contents(
			array(
				'tax'       => array(
					'default_term' => array(
						'name' => 'Foo',
						'slug' => 'foo',
					),
				),
				'post_type' => array(
					'wpf_cptp' => array(
						'permalink_structure' => '/%ctax%/%post_id%/',
					),
				),
			)
		);

		wp_set_post_terms( $test_contents['post_id'], array(), $test_contents['taxonomy'] );

		$cpt_post_link = get_permalink( $test_contents['post_id'] );

		$this->assertSame( $test_contents['post_id'], url_to_postid( $cpt_post_link ) );
		$this->go_to( $cpt_post_link );
		$this->assertQueryTrue( 'is_single', 'is_singular' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $cpt_post_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_cpt_permastruct_name( $test_contents['post_type'] ) );
		}

		// コメント
		$this->factory->comment->create_post_comments( $test_contents['post_type'], 15 );
		$next_comment_page_link = $cpt_post_link . 'comment-page-2';
		$this->go_to( $next_comment_page_link );
		$this->assertEquals( get_query_var( 'cpage' ), 2 );
		$this->assertQueryTrue( 'is_single', 'is_singular' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $next_comment_page_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_cpt_permastruct_name( $test_contents['post_type'] ) );
		}

		// 改ページ
		$next_page_link = user_trailingslashit( rtrim( $cpt_post_link, '/' ) . '/page/2' ); // 長いバージョン。
		$this->go_to( $next_page_link );
		$this->assertQueryTrue( 'is_single', 'is_singular', 'is_paged' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $next_page_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_cpt_permastruct_name( $test_contents['post_type'] ) );
		}

		$next_page_link = user_trailingslashit( rtrim( $cpt_post_link, '/' ) . '/2' ); // 短いバージョン。
		$this->go_to( $next_page_link );
		$this->assertQueryTrue( 'is_single', 'is_singular' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $next_page_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_cpt_permastruct_name( $test_contents['post_type'] ) );
		}
	}

	/**
	 * ターム未選択、かつ default_term オプション未設定の場合、
	 * デフォルトスラッグ（category_base）が設定されるか。
	 *
	 * @covers WPF_CPT_Rewrite::register_post_type_rules
	 * @covers WPF_CPT_Permalink::post_type_link
	 * @preserveGlobalState disabled
	 */
	public function test_cpt_post_when_term_not_selected_with_no_default_term() {
		$test_contents = self::create_cpt_test_contents(
			array(
				'post_type' => array(
					'wpf_cptp' => array(
						'permalink_structure' => '/%ctax%/%post_id%/',
					),
				),
			)
		);

		wp_set_post_terms( $test_contents['post_id'], array(), $test_contents['taxonomy'] );

		$cpt_post_link = get_permalink( $test_contents['post_id'] );

		$this->assertSame( $test_contents['post_id'], url_to_postid( $cpt_post_link ) );
		$this->go_to( $cpt_post_link );
		$this->assertQueryTrue( 'is_single', 'is_singular' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $cpt_post_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_cpt_permastruct_name( $test_contents['post_type'] ) );
		}

		// コメント
		$this->factory->comment->create_post_comments( $test_contents['post_type'], 15 );
		$next_comment_page_link = $cpt_post_link . 'comment-page-2';
		$this->go_to( $next_comment_page_link );
		$this->assertEquals( get_query_var( 'cpage' ), 2 );
		$this->assertQueryTrue( 'is_single', 'is_singular' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $next_comment_page_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_cpt_permastruct_name( $test_contents['post_type'] ) );
		}

		// 改ページ
		$next_page_link = user_trailingslashit( rtrim( $cpt_post_link, '/' ) . '/page/2' ); // 長いバージョン。
		$this->go_to( $next_page_link );
		$this->assertQueryTrue( 'is_single', 'is_singular', 'is_paged' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $next_page_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_cpt_permastruct_name( $test_contents['post_type'] ) );
		}

		$next_page_link = user_trailingslashit( rtrim( $cpt_post_link, '/' ) . '/2' ); // 短いバージョン。
		$this->go_to( $next_page_link );
		$this->assertQueryTrue( 'is_single', 'is_singular' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $next_page_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_cpt_permastruct_name( $test_contents['post_type'] ) );
		}
	}

	/**
	 * カテゴリ未選択の場合、デフォルトスラッグが設定されるか。
	 *
	 * @covers WPF_CPT_Rewrite::register_post_type_rules
	 * @covers WPF_CPT_Permalink::post_type_link
	 * @preserveGlobalState disabled
	 */
	public function test_cpt_post_when_category_not_selected() {
		$test_contents = self::create_cpt_test_contents(
			array(
				'post_type' => array(
					'wpf_cptp' => array(
						'permalink_structure' => '/%category%/%post_id%/',
					),
				),
			)
		);

		wp_set_post_categories( $test_contents['post_id'], array() );

		$cpt_post_link = get_permalink( $test_contents['post_id'] );

		$this->assertSame( $test_contents['post_id'], url_to_postid( $cpt_post_link ) );
		$this->go_to( $cpt_post_link );
		$this->assertQueryTrue( 'is_single', 'is_singular' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $cpt_post_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_cpt_permastruct_name( $test_contents['post_type'] ) );
		}

		// コメント
		$this->factory->comment->create_post_comments( $test_contents['post_type'], 15 );
		$next_comment_page_link = $cpt_post_link . 'comment-page-2';
		$this->go_to( $next_comment_page_link );
		$this->assertEquals( get_query_var( 'cpage' ), 2 );
		$this->assertQueryTrue( 'is_single', 'is_singular' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $next_comment_page_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_cpt_permastruct_name( $test_contents['post_type'] ) );
		}

		// 改ページ
		$next_page_link = user_trailingslashit( rtrim( $cpt_post_link, '/' ) . '/page/2' ); // 長いバージョン。
		$this->go_to( $next_page_link );
		$this->assertQueryTrue( 'is_single', 'is_singular', 'is_paged' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $next_page_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_cpt_permastruct_name( $test_contents['post_type'] ) );
		}

		$next_page_link = user_trailingslashit( rtrim( $cpt_post_link, '/' ) . '/2' ); // 短いバージョン。
		$this->go_to( $next_page_link );
		$this->assertQueryTrue( 'is_single', 'is_singular' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $next_page_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, WPF_CPT_Rewrite::get_cpt_permastruct_name( $test_contents['post_type'] ) );
		}
	}
}
