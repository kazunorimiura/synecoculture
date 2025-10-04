<?php

/**
 * ctaxアーカイブのクエリが wp_query オブジェクトに及ぼす影響をテストする。
 *
 * @group query
 * @group term_archive
 * @covers WPF_CPT_Rewrite
 * @covers WPF_CPT_Permalink
 */
class TestCPTCtaxArchiveQuery extends WPF_UnitTestCase {

	/**
	 * @covers WPF_CPT_Rewrite::register_post_type_rules
	 * @covers WPF_CPT_Permalink::post_type_link
	 * @preserveGlobalState disabled
	 * @dataProvider data_cpt_permastruct
	 *
	 * @param string $cpt_permastruct CPTパーマリンク構造。
	 */
	public function test_term_archive( $cpt_permastruct ) {
		$test_contents = self::create_cpt_test_contents(
			array(
				'post_type' => array(
					'wpf_cptp' => array(
						'permalink_structure' => $cpt_permastruct,
					),
				),
			)
		);

		$term      = get_term( $test_contents['term_id'], $test_contents['taxonomy'] );
		$taxonomy  = get_taxonomy( $test_contents['taxonomy'] );
		$term_link = get_term_link( $term, $taxonomy );

		$this->go_to( $term_link );
		$this->assertQueryTrue( 'is_archive', 'is_tax' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $term_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, $test_contents['taxonomy'] );
		}

		$this->go_to( next_posts( 0, false ) );
		$this->assertQueryTrue( 'is_archive', 'is_tax', 'is_paged' );
	}

	/**
	 * @covers WPF_CPT_Rewrite::register_post_type_rules
	 * @covers WPF_CPT_Permalink::post_type_link
	 * @preserveGlobalState disabled
	 * @dataProvider data_cpt_permastruct
	 *
	 * @param string $cpt_permastruct CPTパーマリンク構造。
	 */
	public function test_term_archive_with_front( $cpt_permastruct ) {
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

		$term      = get_term( $test_contents['term_id'], $test_contents['taxonomy'] );
		$taxonomy  = get_taxonomy( $test_contents['taxonomy'] );
		$term_link = get_term_link( $term, $taxonomy );

		$this->go_to( $term_link );
		$this->assertQueryTrue( 'is_archive', 'is_tax' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $term_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, $test_contents['taxonomy'] );
		}

		$this->go_to( next_posts( 0, false ) );
		$this->assertQueryTrue( 'is_archive', 'is_tax', 'is_paged' );
	}

	/**
	 * @covers WPF_CPT_Rewrite::register_post_type_rules
	 * @covers WPF_CPT_Permalink::post_type_link
	 * @preserveGlobalState disabled
	 * @dataProvider data_cpt_permastruct
	 *
	 * @param string $cpt_permastruct CPTパーマリンク構造。
	 */
	public function test_term_archive_with_hierarchical_tax( $cpt_permastruct ) {
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/foo/%year%/%monthnum%/%day%/%postname%/' );

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

		$term      = get_term( $test_contents['term_id'], $test_contents['taxonomy'] );
		$taxonomy  = get_taxonomy( $test_contents['taxonomy'] );
		$term_link = get_term_link( $term, $taxonomy );

		$this->go_to( $term_link );
		$this->assertQueryTrue( 'is_archive', 'is_tax' );

		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$path   = wp_parse_url( $term_link, PHP_URL_PATH );
			$source = reset( self::get_matched_rules( $path )[ $path ] )['source'];
			$this->assertSame( $source, $test_contents['taxonomy'] );
		}

		$this->go_to( next_posts( 0, false ) );
		$this->assertQueryTrue( 'is_archive', 'is_tax', 'is_paged' );
	}
}
