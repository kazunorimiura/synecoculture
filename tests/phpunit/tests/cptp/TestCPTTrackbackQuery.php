<?php

/**
 * CPTトラックバックのクエリが wp_query オブジェクトに及ぼす影響をテストする。
 *
 * @group query
 * @group cpt_trackback
 * @covers WPF_CPT_Rewrite
 * @covers WPF_CPT_Permalink
 */
class TestCPTTrackbackQuery extends WPF_UnitTestCase {

	/**
	 * @covers WPF_CPT_Rewrite::register_post_type_rules
	 * @covers WPF_CPT_Permalink::post_type_link
	 * @preserveGlobalState disabled
	 * @dataProvider data_cpt_permastruct
	 *
	 * @param string $cpt_permastruct CPTパーマリンク構造。
	 */
	public function test_cpt_trackback( $cpt_permastruct ) {
		$test_contents = self::create_cpt_test_contents(
			array(
				'post_type' => array(
					'wpf_cptp' => array(
						'permalink_structure' => $cpt_permastruct,
					),
				),
			)
		);

		$cpt_trackback_link = user_trailingslashit( rtrim( get_permalink( $test_contents['post_id'] ), '/' ) . '/trackback' );

		$this->go_to( $cpt_trackback_link );
		$this->assertQueryTrue( 'is_single', 'is_singular', 'is_trackback' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $cpt_trackback_link, PHP_URL_PATH );
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
	public function test_cpt_trackback_with_hierarchical_cpt( $cpt_permastruct ) {
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

		$cpt_trackback_link = user_trailingslashit( rtrim( get_permalink( $test_contents['post_id'] ), '/' ) . '/trackback' );

		$this->go_to( $cpt_trackback_link );
		$this->assertQueryTrue( 'is_single', 'is_singular', 'is_trackback' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $cpt_trackback_link, PHP_URL_PATH );
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
	public function test_cpt_trackback_with_hierarchical_tax( $cpt_permastruct ) {
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

		$cpt_trackback_link = user_trailingslashit( rtrim( get_permalink( $test_contents['post_id'] ), '/' ) . '/trackback' );

		$this->go_to( $cpt_trackback_link );
		$this->assertQueryTrue( 'is_single', 'is_singular', 'is_trackback' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $cpt_trackback_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, \WPF_CPT_Rewrite::get_cpt_permastruct_name( $test_contents['post_type'] ) );
		}
	}
}
