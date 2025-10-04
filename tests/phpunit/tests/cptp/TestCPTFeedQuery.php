<?php

/**
 * CPTフィードのクエリが wp_query オブジェクトに及ぼす影響をテストする。
 *
 * @group query
 * @group cpt_feed
 * @covers WPF_CPT_Rewrite
 * @covers WPF_CPT_Permalink
 */
class TestCPTFeedQuery extends WPF_UnitTestCase {

	/**
	 * フィードにしてはやりすぎ感が否めないが、
	 * ルールの優先順位に問題がないか確認しておきたいので、
	 * 念のため、あらゆるパーマリンク構造でテスト。
	 *
	 * @covers WPF_CPT_Rewrite::register_post_type_rules
	 * @covers WPF_CPT_Permalink::post_type_link
	 * @preserveGlobalState disabled
	 * @dataProvider data_cpt_permastruct
	 *
	 * @param string $cpt_permastruct CPTパーマリンク構造。
	 */
	public function test_cpt_feed( $cpt_permastruct ) {
		$test_contents = self::create_cpt_test_contents(
			array(
				'post_type' => array(
					'wpf_cptp' => array(
						'permalink_structure' => $cpt_permastruct,
					),
				),
			)
		);

		$cpt_slug = get_post_type_object( $test_contents['post_type'] )->rewrite['slug'];

		global $wp_rewrite;
		$types = $wp_rewrite->feeds;

		// 長いバージョン。
		foreach ( $types as $type ) {
			$cpt_feed_link = home_url( user_trailingslashit( '/' . $cpt_slug . '/feed/' . $type ) );
			$this->go_to( $cpt_feed_link );
			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_feed' );

			if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
				$path   = wp_parse_url( $cpt_feed_link, PHP_URL_PATH );
				$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
				$this->assertSame( $source, 'other' );
			}
		}

		// 短いバージョン。
		foreach ( $types as $type ) {
			$this->go_to( home_url( user_trailingslashit( '/' . $cpt_slug . '/' . $type ) ) );
			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_feed' );

			if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
				$path   = wp_parse_url( $cpt_feed_link, PHP_URL_PATH );
				$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
				$this->assertSame( $source, 'other' );
			}
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
	public function test_cpt_comment_feed( $cpt_permastruct ) {
		$test_contents = self::create_cpt_test_contents(
			array(
				'post_type' => array(
					'wpf_cptp' => array(
						'permalink_structure' => $cpt_permastruct,
					),
				),
			)
		);

		$this->factory->comment->create_post_comments( $test_contents['post_id'], 2 );
		$comments_feed_link = get_post_comments_feed_link( $test_contents['post_id'] );

		$this->go_to( $comments_feed_link );
		$this->assertQueryTrue( 'is_feed', 'is_single', 'is_singular', 'is_comment_feed' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $comments_feed_link, PHP_URL_PATH );
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
	public function test_term_feed( $cpt_permastruct ) {
		$test_contents = self::create_cpt_test_contents(
			array(
				'post_type' => array(
					'wpf_cptp' => array(
						'permalink_structure' => $cpt_permastruct,
					),
				),
			)
		);

		$term     = get_term( $test_contents['term_id'], $test_contents['taxonomy'] );
		$taxonomy = get_taxonomy( $test_contents['taxonomy'] );

		global $wp_rewrite;
		$types = $wp_rewrite->feeds;

		// 長いバージョン。
		foreach ( $types as $type ) {
			$term_feed_link = rtrim( get_term_link( $term, $taxonomy ), '/' ) . user_trailingslashit( '/feed/' . $type );

			$this->go_to( $term_feed_link );
			$this->assertQueryTrue( 'is_archive', 'is_feed', 'is_tax' );

			if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
				$path   = wp_parse_url( $term_feed_link, PHP_URL_PATH );
				$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
				$this->assertSame( $source, $test_contents['taxonomy'] );
			}
		}

		// 短いバージョン。
		foreach ( $types as $type ) {
			$term_feed_link = rtrim( get_term_link( $term, $taxonomy ), '/' ) . user_trailingslashit( '/' . $type );
			$this->go_to( $term_feed_link );
			$this->assertQueryTrue( 'is_archive', 'is_feed', 'is_tax' );

			if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
				$path   = wp_parse_url( $term_feed_link, PHP_URL_PATH );
				$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
				$this->assertSame( $source, $test_contents['taxonomy'] );
			}
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
	public function test_term_feed_with_hierarchical_tax( $cpt_permastruct ) {
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

		$term     = get_term( $test_contents['term_id'], $test_contents['taxonomy'] );
		$taxonomy = get_taxonomy( $test_contents['taxonomy'] );

		global $wp_rewrite;
		$types = $wp_rewrite->feeds;

		// 長いバージョン。
		foreach ( $types as $type ) {
			$term_feed_link = rtrim( get_term_link( $term, $taxonomy ), '/' ) . user_trailingslashit( '/feed/' . $type );
			$this->go_to( $term_feed_link );
			$this->assertQueryTrue( 'is_archive', 'is_feed', 'is_tax' );

			if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
				$path   = wp_parse_url( $term_feed_link, PHP_URL_PATH );
				$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
				$this->assertSame( $source, $test_contents['taxonomy'] );
			}
		}

		// 短いバージョン。
		foreach ( $types as $type ) {
			$term_feed_link = trailingslashit( get_term_link( $term, $taxonomy ) ) . user_trailingslashit( '/' . $type );
			$this->go_to( $term_feed_link );
			$this->assertQueryTrue( 'is_archive', 'is_feed', 'is_tax' );

			if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
				$path   = wp_parse_url( $term_feed_link, PHP_URL_PATH );
				$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
				$this->assertSame( $source, $test_contents['taxonomy'] );
			}
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
	public function test_term_feed_with_front( $cpt_permastruct ) {
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

		$term     = get_term( $test_contents['term_id'], $test_contents['taxonomy'] );
		$taxonomy = get_taxonomy( $test_contents['taxonomy'] );

		global $wp_rewrite;
		$types = $wp_rewrite->feeds;

		// 長いバージョン。
		foreach ( $types as $type ) {
			$term_feed_link = rtrim( get_term_link( $term, $taxonomy ), '/' ) . user_trailingslashit( '/feed/' . $type );
			$this->go_to( $term_feed_link );
			$this->assertQueryTrue( 'is_archive', 'is_feed', 'is_tax' );

			if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
				$path   = wp_parse_url( $term_feed_link, PHP_URL_PATH );
				$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
				$this->assertSame( $source, $test_contents['taxonomy'] );
			}
		}

		// 短いバージョン。
		foreach ( $types as $type ) {
			$term_feed_link = rtrim( get_term_link( $term, $taxonomy ), '/' ) . user_trailingslashit( '/' . $type );
			$this->go_to( $term_feed_link );
			$this->assertQueryTrue( 'is_archive', 'is_feed', 'is_tax' );

			if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
				$path   = wp_parse_url( $term_feed_link, PHP_URL_PATH );
				$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
				$this->assertSame( $source, $test_contents['taxonomy'] );
			}
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
	public function test_author_feed( $cpt_permastruct ) {
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

		global $wp_rewrite;
		$types = $wp_rewrite->feeds;

		// 長いバージョン。
		foreach ( $types as $type ) {
			$author_feed_link = home_url( user_trailingslashit( str_replace( array( WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $test_contents['post_type'] ), '%author%' ), array( $cpt_slug, $user->user_nicename ), $author_permastruct ) . '/feed/' . $type ) );
			$this->go_to( $author_feed_link );
			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_feed', 'is_author' );

			if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
				$path   = wp_parse_url( $author_feed_link, PHP_URL_PATH );
				$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
				$this->assertSame( $source, WPF_CPT_Rewrite::get_author_permastruct_name( $test_contents['post_type'] ) );
			}
		}

		// 短いバージョン。
		foreach ( $types as $type ) {
			$author_feed_link = home_url( user_trailingslashit( str_replace( array( WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $test_contents['post_type'] ), '%author%' ), array( $cpt_slug, $user->user_nicename ), $author_permastruct ) . '/' . $type ) );
			$this->go_to( $author_feed_link );
			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_feed', 'is_author' );

			if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
				$path   = wp_parse_url( $author_feed_link, PHP_URL_PATH );
				$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
				$this->assertSame( $source, WPF_CPT_Rewrite::get_author_permastruct_name( $test_contents['post_type'] ) );
			}
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
	public function test_author_feed_with_front( $cpt_permastruct ) {
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

		global $wp_rewrite;
		$types = $wp_rewrite->feeds;

		// 長いバージョン。
		foreach ( $types as $type ) {
			$author_link = home_url( user_trailingslashit( '/foo/' . str_replace( array( WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $test_contents['post_type'] ), '%author%' ), array( $cpt_slug, $user->user_nicename ), $author_permastruct ) . '/feed/' . $type ) );
			$this->go_to( $author_link );
			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_feed', 'is_author' );

			if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
				$path   = wp_parse_url( $author_link, PHP_URL_PATH );
				$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
				$this->assertSame( $source, WPF_CPT_Rewrite::get_author_permastruct_name( $test_contents['post_type'] ) );
			}
		}

		// 短いバージョン。
		foreach ( $types as $type ) {
			$author_link = home_url( user_trailingslashit( '/foo/' . str_replace( array( WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $test_contents['post_type'] ), '%author%' ), array( $cpt_slug, $user->user_nicename ), $author_permastruct ) . '/' . $type ) );
			$this->go_to( $author_link );
			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_feed', 'is_author' );

			if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
				$path   = wp_parse_url( $author_link, PHP_URL_PATH );
				$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
				$this->assertSame( $source, WPF_CPT_Rewrite::get_author_permastruct_name( $test_contents['post_type'] ) );
			}
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
	public function test_cpt_year_feed( $cpt_permastruct ) {
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

		global $wp_rewrite;
		$types = $wp_rewrite->feeds;

		// 長いバージョン。
		foreach ( $types as $type ) {
			$day_feed_link = home_url( user_trailingslashit( $date_front . '2012/feed/' . $type ) );
			$this->go_to( $day_feed_link );
			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_feed', 'is_year', 'is_date' );

			if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
				$path   = wp_parse_url( $day_feed_link, PHP_URL_PATH );
				$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
				$this->assertSame( $source, WPF_CPT_Rewrite::get_year_permastruct_name( $test_contents['post_type'] ) );
			}
		}

		// 短いバージョン。
		foreach ( $types as $type ) {
			$day_feed_link = home_url( user_trailingslashit( $date_front . '2012/' . $type ) );
			$this->go_to( $day_feed_link );
			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_feed', 'is_year', 'is_date' );

			if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
				$path   = wp_parse_url( $day_feed_link, PHP_URL_PATH );
				$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
				$this->assertSame( $source, WPF_CPT_Rewrite::get_year_permastruct_name( $test_contents['post_type'] ) );
			}
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
	public function test_cpt_year_feed_with_front( $cpt_permastruct ) {
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

		global $wp_rewrite;
		$types = $wp_rewrite->feeds;

		// 長いバージョン。
		foreach ( $types as $type ) {
			$day_feed_link = home_url( user_trailingslashit( '/foo/' . $date_front . '2012/feed/' . $type ) );
			$this->go_to( $day_feed_link );
			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_feed', 'is_year', 'is_date' );

			if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
				$path   = wp_parse_url( $day_feed_link, PHP_URL_PATH );
				$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
				$this->assertSame( $source, WPF_CPT_Rewrite::get_year_permastruct_name( $test_contents['post_type'] ) );
			}
		}

		// 短いバージョン。
		foreach ( $types as $type ) {
			$day_feed_link = home_url( user_trailingslashit( '/foo/' . $date_front . '2012/' . $type ) );
			$this->go_to( $day_feed_link );
			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_feed', 'is_year', 'is_date' );

			if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
				$path   = wp_parse_url( $day_feed_link, PHP_URL_PATH );
				$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
				$this->assertSame( $source, WPF_CPT_Rewrite::get_year_permastruct_name( $test_contents['post_type'] ) );
			}
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
	public function test_cpt_month_feed( $cpt_permastruct ) {
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

		global $wp_rewrite;
		$types = $wp_rewrite->feeds;

		// 長いバージョン。
		foreach ( $types as $type ) {
			$day_feed_link = home_url( user_trailingslashit( $date_front . '2012/12/feed/' . $type ) );
			$this->go_to( $day_feed_link );
			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_feed', 'is_month', 'is_date' );

			if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
				$path   = wp_parse_url( $day_feed_link, PHP_URL_PATH );
				$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
				$this->assertSame( $source, WPF_CPT_Rewrite::get_month_permastruct_name( $test_contents['post_type'] ) );
			}
		}

		// 短いバージョン。
		foreach ( $types as $type ) {
			$day_feed_link = home_url( user_trailingslashit( $date_front . '2012/12/' . $type ) );
			$this->go_to( $day_feed_link );
			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_feed', 'is_month', 'is_date' );

			if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
				$path   = wp_parse_url( $day_feed_link, PHP_URL_PATH );
				$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
				$this->assertSame( $source, WPF_CPT_Rewrite::get_month_permastruct_name( $test_contents['post_type'] ) );
			}
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
	public function test_cpt_month_feed_with_front( $cpt_permastruct ) {
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

		global $wp_rewrite;
		$types = $wp_rewrite->feeds;

		// 長いバージョン。
		foreach ( $types as $type ) {
			$day_feed_link = home_url( user_trailingslashit( '/foo/' . $date_front . '2012/12/feed/' . $type ) );
			$this->go_to( $day_feed_link );
			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_feed', 'is_month', 'is_date' );

			if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
				$path   = wp_parse_url( $day_feed_link, PHP_URL_PATH );
				$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
				$this->assertSame( $source, WPF_CPT_Rewrite::get_month_permastruct_name( $test_contents['post_type'] ) );
			}
		}

		// 短いバージョン。
		foreach ( $types as $type ) {
			$day_feed_link = home_url( user_trailingslashit( '/foo/' . $date_front . '2012/12/' . $type ) );
			$this->go_to( $day_feed_link );
			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_feed', 'is_month', 'is_date' );

			if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
				$path   = wp_parse_url( $day_feed_link, PHP_URL_PATH );
				$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
				$this->assertSame( $source, WPF_CPT_Rewrite::get_month_permastruct_name( $test_contents['post_type'] ) );
			}
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
	public function test_cpt_day_feed( $cpt_permastruct ) {
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

		global $wp_rewrite;
		$types = $wp_rewrite->feeds;

		// 長いバージョン。
		foreach ( $types as $type ) {
			$day_feed_link = home_url( user_trailingslashit( $date_front . '2012/12/12/feed/' . $type ) );
			$this->go_to( $day_feed_link );
			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_feed', 'is_day', 'is_date' );

			if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
				$path   = wp_parse_url( $day_feed_link, PHP_URL_PATH );
				$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
				$this->assertSame( $source, WPF_CPT_Rewrite::get_date_permastruct_name( $test_contents['post_type'] ) );
			}
		}

		// 短いバージョン。
		foreach ( $types as $type ) {
			$day_feed_link = home_url( user_trailingslashit( $date_front . '2012/12/12/' . $type ) );
			$this->go_to( $day_feed_link );
			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_feed', 'is_day', 'is_date' );

			if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
				$path   = wp_parse_url( $day_feed_link, PHP_URL_PATH );
				$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
				$this->assertSame( $source, WPF_CPT_Rewrite::get_date_permastruct_name( $test_contents['post_type'] ) );
			}
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
	public function test_cpt_day_feed_with_front( $cpt_permastruct ) {
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

		global $wp_rewrite;
		$types = $wp_rewrite->feeds;

		// 長いバージョン。
		foreach ( $types as $type ) {
			$day_feed_link = home_url( user_trailingslashit( '/foo/' . $date_front . '2012/12/12/feed/' . $type ) );
			$this->go_to( $day_feed_link );
			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_feed', 'is_day', 'is_date' );

			if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
				$path   = wp_parse_url( $day_feed_link, PHP_URL_PATH );
				$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
				$this->assertSame( $source, WPF_CPT_Rewrite::get_date_permastruct_name( $test_contents['post_type'] ) );
			}
		}

		// 短いバージョン。
		foreach ( $types as $type ) {
			$day_feed_link = home_url( user_trailingslashit( '/foo/' . $date_front . '2012/12/12/' . $type ) );
			$this->go_to( $day_feed_link );
			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_feed', 'is_day', 'is_date' );

			if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
				$path   = wp_parse_url( $day_feed_link, PHP_URL_PATH );
				$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
				$this->assertSame( $source, WPF_CPT_Rewrite::get_date_permastruct_name( $test_contents['post_type'] ) );
			}
		}
	}
}
