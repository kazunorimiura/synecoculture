<?php

/**
 * CPTアーカイブのクエリが wp_query オブジェクトに及ぼす影響をテストする。
 *
 * @group query
 * @group cpt_post_archive
 * @covers WPF_CPT_Rewrite
 * @covers WPF_CPT_Permalink
 */
class TestCPTPostsQuery extends WPF_UnitTestCase {

	/**
	 * @covers WPF_CPT_Rewrite::register_post_type_rules
	 * @covers WPF_CPT_Permalink::post_type_link
	 * @preserveGlobalState disabled
	 * @dataProvider data_cpt_permastruct
	 *
	 * @param string $cpt_permastruct CPTパーマリンク構造。
	 */
	public function test_cpt_posts( $cpt_permastruct ) {
		$test_contents = self::create_cpt_test_contents(
			array(
				'post_type' => array(
					'wpf_cptp' => array(
						'permalink_structure' => $cpt_permastruct,
					),
				),
			)
		);

		$this->go_to( get_post_type_archive_link( $test_contents['post_type'] ) );
		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive' );
		$this->go_to( next_posts( 0, false ) );
		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_paged' );
	}

	/**
	 * @covers WPF_CPT_Rewrite::register_post_type_rules
	 * @covers WPF_CPT_Permalink::post_type_link
	 * @preserveGlobalState disabled
	 * @dataProvider data_cpt_permastruct
	 *
	 * @param string $cpt_permastruct CPTパーマリンク構造。
	 */
	public function test_cpt_posts_with_front( $cpt_permastruct ) {
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

		$this->go_to( get_post_type_archive_link( $test_contents['post_type'] ) );
		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive' );
		$this->go_to( next_posts( 0, false ) );
		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_paged' );
	}
}
