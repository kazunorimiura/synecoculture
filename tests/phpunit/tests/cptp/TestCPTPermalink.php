<?php

/**
 * WPF_CPT_Permalink クラスのユニットテスト。
 *
 * @group permalink
 * @covers WPF_CPT_Permalink
 * @coversDefaultClass WPF_CPT_Permalink
 */
class TestCPTPermalink extends WPF_UnitTestCase {

	/**
	 * @covers ::post_type_link
	 * @preserveGlobalState disabled
	 * @dataProvider data_cpt_permastruct
	 *
	 * @param string $cpt_permastruct CPTパーマリンク構造。
	 */
	public function test_post_type_link_retval( $cpt_permastruct ) {
		$test_contents = self::create_cpt_test_contents(
			array(
				'post_type' => array(
					'wpf_cptp' => array(
						'permalink_structure' => $cpt_permastruct,
					),
				),
			)
		);

		$this->assertSame( $test_contents['post_id'], url_to_postid( get_post_permalink( $test_contents['post_id'] ) ) );
	}

	/**
	 * @covers ::post_type_link
	 * @preserveGlobalState disabled
	 * @dataProvider data_cpt_permastruct
	 *
	 * @param string $cpt_permastruct CPTパーマリンク構造。
	 */
	public function test_post_type_link_retval_with_hierarchical_cpt( $cpt_permastruct ) {
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

		$this->assertSame( $test_contents['post_id'], url_to_postid( get_post_permalink( $test_contents['post_id'] ) ) );
	}

	/**
	 * @covers ::post_type_link
	 * @preserveGlobalState disabled
	 * @dataProvider data_cpt_permastruct
	 *
	 * @param string $cpt_permastruct CPTパーマリンク構造。
	 */
	public function test_post_type_link_retval_with_hierarchical_tax( $cpt_permastruct ) {
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

		$this->assertSame( $test_contents['post_id'], url_to_postid( get_post_permalink( $test_contents['post_id'] ) ) );

		// タームをすべて選択した場合、一番若いIDのタームスラッグが使用される。
		wp_set_post_terms( $test_contents['post_id'], $test_contents['term_ids'], $test_contents['taxonomy'] );
		$this->assertSame( $test_contents['post_id'], url_to_postid( get_post_permalink( $test_contents['post_id'] ) ) );
	}

	/**
	 * @covers ::post_type_link
	 * @preserveGlobalState disabled
	 * @dataProvider data_cpt_permastruct
	 *
	 * @param string $cpt_permastruct CPTパーマリンク構造。
	 */
	public function test_post_type_link_retval_with_front( $cpt_permastruct ) {
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

		$this->assertSame( $test_contents['post_id'], url_to_postid( get_post_permalink( $test_contents['post_id'] ) ) );
	}

	/**
	 * /author/name => /cpt/author/name
	 *
	 * @covers ::author_link
	 * @preserveGlobalState disabled
	 */
	public function test_author_link() {
		$test_contents = self::create_cpt_test_contents();

		$user             = get_userdata( $test_contents['user_id'] );
		$post_type_object = get_post_type_object( $test_contents['post_type'] );
		$author_link      = get_author_posts_url( $test_contents['user_id'] );

		$this->assertSame( $author_link, home_url( user_trailingslashit( '/author/' . $user->user_nicename ) ) );

		$this->go_to( get_permalink( $test_contents['post_id'] ) );

		$author_link = get_author_posts_url( $test_contents['user_id'] );
		$this->assertSame( $author_link, home_url( user_trailingslashit( '/' . $post_type_object->rewrite['slug'] . '/author/' . $user->user_nicename ) ) );
	}

	/**
	 * /foo/author/name => /foo/cpt/author/name
	 *
	 * @covers ::author_link
	 * @preserveGlobalState disabled
	 */
	public function test_author_link_with_front() {
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/foo/%year%/%monthnum%/%day%/%postname%/' );

		$test_contents = self::create_cpt_test_contents();

		$user             = get_userdata( $test_contents['user_id'] );
		$post_type_object = get_post_type_object( $test_contents['post_type'] );
		$author_link      = get_author_posts_url( $test_contents['user_id'] );

		$this->assertSame( $author_link, home_url( user_trailingslashit( '/foo/author/' . $user->user_nicename ) ) );

		$this->go_to( get_permalink( $test_contents['post_id'] ) );

		$author_link = get_author_posts_url( $test_contents['user_id'] );
		$this->assertSame( $author_link, home_url( user_trailingslashit( '/foo/' . $post_type_object->rewrite['slug'] . '/author/' . $user->user_nicename ) ) );
	}

	/**
	 * /foo/author/name => /cpt/author/name
	 *
	 * @covers ::author_link
	 * @preserveGlobalState disabled
	 */
	public function test_author_link_with_front_false() {
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/foo/%year%/%monthnum%/%day%/%postname%/' );

		$test_contents = self::create_cpt_test_contents(
			array(
				'post_type' => array(
					'rewrite' => array(
						'with_front' => false,
					),
				),
			)
		);

		$user             = get_userdata( $test_contents['user_id'] );
		$post_type_object = get_post_type_object( $test_contents['post_type'] );
		$author_link      = get_author_posts_url( $test_contents['user_id'] );

		$this->assertSame( $author_link, home_url( user_trailingslashit( '/foo/author/' . $user->user_nicename ) ) );

		$this->go_to( get_permalink( $test_contents['post_id'] ) );

		$author_link = get_author_posts_url( $test_contents['user_id'] );
		$this->assertSame( $author_link, home_url( user_trailingslashit( $post_type_object->rewrite['slug'] . '/author/' . $user->user_nicename ) ) );
	}



	/**
	 * /date/2012/?post_type=cpt => /cpt/date/2012/
	 *
	 * @covers ::get_archives_link
	 * @preserveGlobalState disabled
	 */
	public function test_get_archives_link_for_year() {
		$test_contents = self::create_cpt_test_contents();

		$cpt_slug   = get_post_type_object( $test_contents['post_type'] )->rewrite['slug'];
		$date_front = str_replace( WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $test_contents['post_type'] ), $cpt_slug, WPF_CPT_Rewrite::get_date_front( $test_contents['post_type'] ) );
		$date_link  = home_url( user_trailingslashit( $date_front . '2012' ) );

		$this->go_to( $date_link );

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'
                <li><a href=\'http://' . WP_TESTS_DOMAIN . '/cpt_slug/date/2012/\' aria-current="page">2012</a></li>
                '
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				wp_get_archives(
					array(
						'type'      => 'yearly',
						'echo'      => false,
						'post_type' => $test_contents['post_type'],
					)
				)
			)
		);
	}

	/**
	 * /foo/date/2012/?post_type=cpt => /foo/cpt/date/2012/
	 *
	 * @covers ::get_archives_link
	 * @preserveGlobalState disabled
	 */
	public function test_get_archives_link_for_year_with_front() {
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/foo/%year%/%monthnum%/%day%/%postname%/' );

		$test_contents = self::create_cpt_test_contents();

		$cpt_slug   = get_post_type_object( $test_contents['post_type'] )->rewrite['slug'];
		$date_front = str_replace( WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $test_contents['post_type'] ), $cpt_slug, WPF_CPT_Rewrite::get_date_front( $test_contents['post_type'] ) );
		$date_link  = home_url( user_trailingslashit( '/foo/' . $date_front . '2012' ) );

		$this->go_to( $date_link );

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'
                <li><a href=\'http://' . WP_TESTS_DOMAIN . '/foo/cpt_slug/date/2012/\' aria-current="page">2012</a></li>
                '
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				wp_get_archives(
					array(
						'type'      => 'yearly',
						'echo'      => false,
						'post_type' => $test_contents['post_type'],
					)
				)
			)
		);
	}

	/**
	 * /foo/date/2012/?post_type=cpt => /cpt/date/2012/
	 *
	 * @covers ::get_archives_link
	 * @preserveGlobalState disabled
	 */
	public function test_get_archives_link_for_year_with_front_false() {
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/foo/%year%/%monthnum%/%day%/%postname%/' );

		$test_contents = self::create_cpt_test_contents(
			array(
				'post_type' => array(
					'rewrite' => array(
						'with_front' => false,
					),
				),
			)
		);

		$cpt_slug   = get_post_type_object( $test_contents['post_type'] )->rewrite['slug'];
		$date_front = str_replace( WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $test_contents['post_type'] ), $cpt_slug, WPF_CPT_Rewrite::get_date_front( $test_contents['post_type'] ) );
		$date_link  = home_url( user_trailingslashit( $date_front . '2012' ) );

		$this->go_to( $date_link );

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'
                <li><a href=\'http://' . WP_TESTS_DOMAIN . '/cpt_slug/date/2012/\' aria-current="page">2012</a></li>
                '
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				wp_get_archives(
					array(
						'type'      => 'yearly',
						'echo'      => false,
						'post_type' => $test_contents['post_type'],
					)
				)
			)
		);
	}

	/**
	 * /date/2012/12/?post_type=cpt => /cpt/date/2012/12/
	 *
	 * @covers ::get_archives_link
	 * @preserveGlobalState disabled
	 */
	public function test_get_archives_link_for_month() {
		$test_contents = self::create_cpt_test_contents();

		$cpt_slug   = get_post_type_object( $test_contents['post_type'] )->rewrite['slug'];
		$date_front = str_replace( WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $test_contents['post_type'] ), $cpt_slug, WPF_CPT_Rewrite::get_date_front( $test_contents['post_type'] ) );
		$date_link  = home_url( user_trailingslashit( $date_front . '2012/12' ) );

		$this->go_to( $date_link );

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'
                <li><a href=\'http://' . WP_TESTS_DOMAIN . '/cpt_slug/date/2012/12/\' aria-current="page">December 2012</a></li>
                '
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				wp_get_archives(
					array(
						'type'      => 'monthly',
						'echo'      => false,
						'post_type' => $test_contents['post_type'],
					)
				)
			)
		);
	}

	/**
	 * /foo/date/2012/12/?post_type=cpt => /foo/cpt/date/2012/12/
	 *
	 * @covers ::get_archives_link
	 * @preserveGlobalState disabled
	 */
	public function test_get_archives_link_for_month_with_front() {
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/foo/%year%/%monthnum%/%day%/%postname%/' );

		$test_contents = self::create_cpt_test_contents(
			array(
				'post_type' => array(
					'rewrite' => array(
						'with_front' => false,
					),
				),
			)
		);

		$cpt_slug   = get_post_type_object( $test_contents['post_type'] )->rewrite['slug'];
		$date_front = str_replace( WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $test_contents['post_type'] ), $cpt_slug, WPF_CPT_Rewrite::get_date_front( $test_contents['post_type'] ) );
		$date_link  = home_url( user_trailingslashit( $date_front . '2012/12' ) );

		$this->go_to( $date_link );

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'
                <li><a href=\'http://' . WP_TESTS_DOMAIN . '/cpt_slug/date/2012/12/\' aria-current="page">December 2012</a></li>
                '
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				wp_get_archives(
					array(
						'type'      => 'monthly',
						'echo'      => false,
						'post_type' => $test_contents['post_type'],
					)
				)
			)
		);
	}

	/**
	 * /foo/date/2012/12/?post_type=cpt => /cpt/date/2012/12/
	 *
	 * @covers ::get_archives_link
	 * @preserveGlobalState disabled
	 */
	public function test_get_archives_link_for_month_with_front_false() {
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/foo/%year%/%monthnum%/%day%/%postname%/' );

		$test_contents = self::create_cpt_test_contents();

		$cpt_slug   = get_post_type_object( $test_contents['post_type'] )->rewrite['slug'];
		$date_front = str_replace( WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $test_contents['post_type'] ), $cpt_slug, WPF_CPT_Rewrite::get_date_front( $test_contents['post_type'] ) );
		$date_link  = home_url( user_trailingslashit( '/foo/' . $date_front . '2012/12' ) );

		$this->go_to( $date_link );

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'
                <li><a href=\'http://' . WP_TESTS_DOMAIN . '/foo/cpt_slug/date/2012/12/\' aria-current="page">December 2012</a></li>
                '
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				wp_get_archives(
					array(
						'type'      => 'monthly',
						'echo'      => false,
						'post_type' => $test_contents['post_type'],
					)
				)
			)
		);
	}

	/**
	 * /date/2012/12/12/?post_type=cpt => /cpt/date/2012/12/12/
	 *
	 * @covers ::get_archives_link
	 * @preserveGlobalState disabled
	 */
	public function test_get_archives_link_for_day() {
		$test_contents = self::create_cpt_test_contents();

		$cpt_slug   = get_post_type_object( $test_contents['post_type'] )->rewrite['slug'];
		$date_front = str_replace( WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $test_contents['post_type'] ), $cpt_slug, WPF_CPT_Rewrite::get_date_front( $test_contents['post_type'] ) );
		$date_link  = home_url( user_trailingslashit( $date_front . '2012/12/12' ) );

		$this->go_to( $date_link );

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'
                <li><a href=\'http://' . WP_TESTS_DOMAIN . '/cpt_slug/date/2012/12/12/\' aria-current="page">2012年12月12日</a></li>
                '
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				wp_get_archives(
					array(
						'type'      => 'daily',
						'echo'      => false,
						'post_type' => $test_contents['post_type'],
					)
				)
			)
		);
	}

	/**
	 * /foo/date/2012/12/12/?post_type=cpt => /foo/cpt/date/2012/12/12/
	 *
	 * @covers ::get_archives_link
	 * @preserveGlobalState disabled
	 */
	public function test_get_archives_link_for_day_with_front() {
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/foo/%year%/%monthnum%/%day%/%postname%/' );

		$test_contents = self::create_cpt_test_contents();

		$cpt_slug   = get_post_type_object( $test_contents['post_type'] )->rewrite['slug'];
		$date_front = str_replace( WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $test_contents['post_type'] ), $cpt_slug, WPF_CPT_Rewrite::get_date_front( $test_contents['post_type'] ) );
		$date_link  = home_url( user_trailingslashit( '/foo/' . $date_front . '2012/12/12' ) );

		$this->go_to( $date_link );

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'
                <li><a href=\'http://' . WP_TESTS_DOMAIN . '/foo/cpt_slug/date/2012/12/12/\' aria-current="page">2012年12月12日</a></li>
                '
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				wp_get_archives(
					array(
						'type'      => 'daily',
						'echo'      => false,
						'post_type' => $test_contents['post_type'],
					)
				)
			)
		);
	}

	/**
	 * /foo/date/2012/12/12/?post_type=cpt => /cpt/date/2012/12/12/
	 *
	 * @covers ::get_archives_link
	 * @preserveGlobalState disabled
	 */
	public function test_get_archives_link_for_day_with_front_false() {
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/foo/%year%/%monthnum%/%day%/%postname%/' );

		$test_contents = self::create_cpt_test_contents(
			array(
				'post_type' => array(
					'rewrite' => array(
						'with_front' => false,
					),
				),
			)
		);

		$cpt_slug   = get_post_type_object( $test_contents['post_type'] )->rewrite['slug'];
		$date_front = str_replace( WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $test_contents['post_type'] ), $cpt_slug, WPF_CPT_Rewrite::get_date_front( $test_contents['post_type'] ) );
		$date_link  = home_url( user_trailingslashit( $date_front . '2012/12/12' ) );

		$this->go_to( $date_link );

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'
                <li><a href=\'http://' . WP_TESTS_DOMAIN . '/cpt_slug/date/2012/12/12/\' aria-current="page">2012年12月12日</a></li>
                '
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				wp_get_archives(
					array(
						'type'      => 'daily',
						'echo'      => false,
						'post_type' => $test_contents['post_type'],
					)
				)
			)
		);
	}
}
