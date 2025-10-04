<?php

/**
 * CPT添付ファイルページのクエリが wp_query オブジェクトに及ぼす影響をテストする。
 *
 * @group query
 * @group cpt_attachment
 * @covers WPF_CPT_Rewrite
 * @covers WPF_CPT_Permalink
 */
class TestCPTAttachmentQuery extends WPF_UnitTestCase {

	/**
	 * @covers WPF_CPT_Rewrite::register_post_type_rules
	 * @covers WPF_CPT_Permalink::post_type_link
	 * @preserveGlobalState disabled
	 * @dataProvider data_cpt_permastruct
	 *
	 * @param string $cpt_permastruct CPTパーマリンク構造。
	 */
	public function test_cpt_attachment( $cpt_permastruct ) {
		$test_contents = self::create_cpt_test_contents(
			array(
				'post_type' => array(
					'wpf_cptp' => array(
						'permalink_structure' => $cpt_permastruct,
					),
				),
			)
		);

		$attachment_link = get_attachment_link( $test_contents['attachment_id'] );
		$this->go_to( $attachment_link );
		$this->assertQueryTrue( 'is_attachment', 'is_single', 'is_singular' );

		// コメント
		self::factory()->comment->create_post_comments( $test_contents['attachment_id'], 15 );
		$next_comment_page_link = $attachment_link . 'comment-page-2';
		$this->go_to( $next_comment_page_link );
		$this->assertEquals( get_query_var( 'cpage' ), 2 );
		$this->assertQueryTrue( 'is_attachment', 'is_single', 'is_singular' );
	}

	/**
	 * @covers WPF_CPT_Rewrite::register_post_type_rules
	 * @covers WPF_CPT_Permalink::post_type_link
	 * @preserveGlobalState disabled
	 * @dataProvider data_cpt_permastruct
	 *
	 * @param string $cpt_permastruct CPTパーマリンク構造。
	 */
	public function test_cpt_attachment_with_unattached( $cpt_permastruct ) {
		$test_contents = self::create_cpt_test_contents(
			array(
				'post_type' => array(
					'wpf_cptp' => array(
						'permalink_structure' => $cpt_permastruct,
					),
				),
			)
		);

		$attachment_id   = self::factory()->attachment->create_object( 'image.jpg', 0, array( 'post_mime_type' => 'image/jpeg' ) );
		$attachment_link = get_attachment_link( $attachment_id );

		$this->go_to( $attachment_link );
		$this->assertQueryTrue( 'is_attachment', 'is_single', 'is_singular' );

		// コメント
		self::factory()->comment->create_post_comments( $test_contents['attachment_id'], 15 );
		$next_comment_page_link = $attachment_link . 'comment-page-2';
		$this->go_to( $next_comment_page_link );
		$this->assertEquals( get_query_var( 'cpage' ), 2 );
		$this->assertQueryTrue( 'is_attachment', 'is_single', 'is_singular' );
	}

	/**
	 * @covers WPF_CPT_Rewrite::register_post_type_rules
	 * @covers WPF_CPT_Permalink::post_type_link
	 * @preserveGlobalState disabled
	 * @dataProvider data_cpt_permastruct
	 *
	 * @param string $cpt_permastruct CPTパーマリンク構造。
	 */
	public function test_cpt_attachment_with_hierarchical_cpt( $cpt_permastruct ) {
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

		$attachment_link = get_attachment_link( $test_contents['attachment_id'] );
		$this->go_to( $attachment_link );
		$this->assertQueryTrue( 'is_attachment', 'is_single', 'is_singular' );

		// コメント
		self::factory()->comment->create_post_comments( $test_contents['attachment_id'], 15 );
		$next_comment_page_link = $attachment_link . 'comment-page-2';
		$this->go_to( $next_comment_page_link );
		$this->assertEquals( get_query_var( 'cpage' ), 2 );
		$this->assertQueryTrue( 'is_attachment', 'is_single', 'is_singular' );
	}

	/**
	 * @covers WPF_CPT_Rewrite::register_post_type_rules
	 * @covers WPF_CPT_Permalink::post_type_link
	 * @preserveGlobalState disabled
	 * @dataProvider data_cpt_permastruct
	 *
	 * @param string $cpt_permastruct CPTパーマリンク構造。
	 */
	public function test_cpt_attachment_with_hierarchical_tax( $cpt_permastruct ) {
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

		$attachment_link = get_attachment_link( $test_contents['attachment_id'] );
		$this->go_to( $attachment_link );
		$this->assertQueryTrue( 'is_attachment', 'is_single', 'is_singular' );

		// コメント
		self::factory()->comment->create_post_comments( $test_contents['attachment_id'], 15 );
		$next_comment_page_link = $attachment_link . 'comment-page-2';
		$this->go_to( $next_comment_page_link );
		$this->assertEquals( get_query_var( 'cpage' ), 2 );
		$this->assertQueryTrue( 'is_attachment', 'is_single', 'is_singular' );
	}

	/**
	 * @covers WPF_CPT_Rewrite::register_post_type_rules
	 * @covers WPF_CPT_Permalink::post_type_link
	 * @preserveGlobalState disabled
	 * @dataProvider data_cpt_permastruct
	 *
	 * @param string $cpt_permastruct CPTパーマリンク構造。
	 */
	public function test_cpt_attachment_with_front( $cpt_permastruct ) {
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

		$attachment_link = get_attachment_link( $test_contents['attachment_id'] );
		$this->go_to( $attachment_link );
		$this->assertQueryTrue( 'is_attachment', 'is_single', 'is_singular' );

		// コメント
		self::factory()->comment->create_post_comments( $test_contents['attachment_id'], 15 );
		$next_comment_page_link = $attachment_link . 'comment-page-2';
		$this->go_to( $next_comment_page_link );
		$this->assertEquals( get_query_var( 'cpage' ), 2 );
		$this->assertQueryTrue( 'is_attachment', 'is_single', 'is_singular' );
	}

	/**
	 * @covers WPF_CPT_Rewrite::register_post_type_rules
	 * @covers WPF_CPT_Permalink::post_type_link
	 * @preserveGlobalState disabled
	 * @dataProvider data_cpt_permastruct
	 *
	 * @param string $cpt_permastruct CPTパーマリンク構造。
	 */
	public function test_cpt_attachment_with_private_post_type( $cpt_permastruct ) {
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

		$attachment_link = get_attachment_link( $test_contents['attachment_id'] );

		// URLがデフォルトのままであることを確認する。
		global $wp_version;
		if ( version_compare( $wp_version, '5.7', '>=' ) ) {
			$this->assertSame( $attachment_link, home_url( '?attachment_id=' . $test_contents['attachment_id'] ) );
		} else {
			$this->assertSame( $attachment_link, user_trailingslashit( trailingslashit( $post_link ) . get_post( $attachment_id )->post_name ) );
		}

		$this->go_to( $attachment_link );
		$this->assertQueryTrue( 'is_attachment', 'is_single', 'is_singular' );
	}
}
