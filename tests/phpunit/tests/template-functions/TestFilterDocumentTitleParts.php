<?php

/**
 * WPF_Template_Functions::filter_document_title_parts のユニットテスト。
 *
 * @group template_functions
 * @covers WPF_Template_Functions
 * @coversDefaultClass WPF_Template_Functions
 */
class TestFilterDocumentTitleParts extends WPF_UnitTestCase {

	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		parent::wpSetUpBeforeClass( $factory );

		update_option( 'show_on_front', 'posts' );
		update_option( 'page_on_front', '' );
		update_option( 'page_for_posts', '' );
	}

	public static function wpTearDownAfterClass() {
		update_option( 'show_on_front', 'posts' );
		update_option( 'page_on_front', '' );
		update_option( 'page_for_posts', '' );
	}

	/**
	 * 検索結果ページ
	 *
	 * @covers ::filter_document_title_parts
	 * @preserveGlobalState disabled
	 */
	public function test_filter_document_title_parts_with_search_result() {
		$post_id = self::factory()->post->create(
			array(
				'post_type'  => 'post',
				'post_title' => 'Foo',
			)
		);
		$post    = get_post( $post_id );

		$this->go_to( home_url( '/?s=Foo' ) );

		$this->assertSame(
			array(
				'title'   => 'Fooの検索結果',
				'page'    => '',
				'tagline' => '',
				'site'    => '',
			),
			WPF_Template_Functions::filter_document_title_parts(
				array(
					'title'   => '',
					'page'    => '',
					'tagline' => '',
					'site'    => '',
				)
			)
		);
	}

	/**
	 * 投稿用ページ
	 *
	 * @covers ::filter_document_title_parts
	 * @preserveGlobalState disabled
	 */
	public function test_filter_document_title_parts_with_page_for_posts() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Foo',
			)
		);
		$page    = get_post( $page_id );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $page_id );

		$this->go_to( get_post_type_archive_link( 'post' ) );

		$this->assertSame(
			array(
				'title'   => $page->post_title,
				'page'    => '',
				'tagline' => '',
				'site'    => '',
			),
			WPF_Template_Functions::filter_document_title_parts(
				array(
					'title'   => 'Foo',
					'page'    => '',
					'tagline' => '',
					'site'    => '',
				)
			)
		);
	}

	/**
	 * CPT投稿用ページ
	 *
	 * @covers ::filter_document_title_parts
	 * @preserveGlobalState disabled
	 */
	public function test_filter_document_title_parts_with_page_for_cpt_posts() {
		register_post_type( self::$post_type, array( 'public' => true ) );
		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Foo',
				'post_name'  => self::$post_type,
			)
		);
		$page    = get_post( $page_id );

		$this->go_to( get_permalink( $page_id ) );

		$this->assertSame(
			array(
				'title'   => $page->post_title,
				'page'    => '',
				'tagline' => '',
				'site'    => '',
			),
			WPF_Template_Functions::filter_document_title_parts(
				array(
					'title'   => 'Foo',
					'page'    => '',
					'tagline' => '',
					'site'    => '',
				)
			)
		);
	}
}
