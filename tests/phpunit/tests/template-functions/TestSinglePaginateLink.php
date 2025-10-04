<?php

/**
 * WPF_Template_Functions::single_paginate_link のユニットテスト。
 *
 * @group template_functions
 * @covers WPF_Template_Functions
 * @coversDefaultClass WPF_Template_Functions
 */
class TestSinglePaginateLink extends WPF_UnitTestCase {
	public static $wpf_template_functions;

	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		parent::wpSetUpBeforeClass( $factory );

		self::$wpf_template_functions = new WPF_Template_Functions();

		add_filter( 'wp_link_pages_args', array( self::$wpf_template_functions, 'single_paginate_link' ) );
	}

	public static function wpTearDownAfterClass() {
		remove_filter( 'wp_link_pages_args', array( self::$wpf_template_functions, 'single_paginate_link' ) );
	}

	/**
	 * @covers ::single_paginate_link
	 * @preserveGlobalState disabled
	 */
	public function test_single_paginate_link() {
		$post_id = self::factory()->post->create( array( 'post_content' => 'Page 1 <!--nextpage--> Page 2' ) );

		$this->go_to( get_permalink( $post_id ) );
		setup_postdata( get_post( $post_id ) );

		$wp_link_pages = wp_link_pages(
			array(
				'next_or_number' => 'next_and_number', // next_or_number に独自の値を指定。
				'echo'           => false,
			)
		);

		$this->assertStringContainsString(
			' aria-current="page">1</span>',
			$wp_link_pages
		);
		$this->assertStringContainsString(
			' class="post-page-numbers">2</a>',
			$wp_link_pages
		);
		$this->assertStringContainsString(
			' class="post-page-numbers"> Next page</a>',
			$wp_link_pages
		);

		$GLOBALS['page'] = 2; // phpcs:ignore

		$wp_link_pages = wp_link_pages(
			array(
				'next_or_number' => 'next_and_number',
				'echo'           => false,
			)
		);

		$this->assertStringContainsString(
			' class="post-page-numbers">Previous page</a>',
			$wp_link_pages
		);
		$this->assertStringContainsString(
			' class="post-page-numbers">1</a>',
			$wp_link_pages
		);
		$this->assertStringContainsString(
			' aria-current="page">2</span>',
			$wp_link_pages
		);
	}
}
