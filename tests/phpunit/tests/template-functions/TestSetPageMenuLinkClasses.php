<?php

/**
 * WPF_Template_Functions::set_page_menu_link_classes のユニットテスト。
 *
 * @group template_functions
 * @covers WPF_Template_Functions
 * @coversDefaultClass WPF_Template_Functions
 */
class TestSetPageMenuLinkClasses extends WPF_UnitTestCase {
	public static $wpf_template_functions;
	private static $menu_id = 0;

	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		parent::wpSetUpBeforeClass( $factory );

		self::$wpf_template_functions = new WPF_Template_Functions();

		add_filter( 'page_menu_link_attributes', array( self::$wpf_template_functions, 'set_page_menu_link_classes' ), 10, 5 );

		self::$menu_id = wp_create_nav_menu( 'test' );
	}

	public static function wpTearDownAfterClass() {
		remove_filter( 'page_menu_link_attributes', array( self::$wpf_template_functions, 'set_page_menu_link_classes' ), 10, 5 );
	}

	/**
	 * @covers ::set_page_menu_link_classes
	 * @preserveGlobalState disabled
	 */
	public function test_set_page_menu_link_classes() {
		$pages = self::factory()->post->create_many( 3, array( 'post_type' => 'page' ) );

		$menu_html = wp_nav_menu( array( 'echo' => false ) );
		$this->assertStringNotContainsString(
			' class="foo bar">',
			$menu_html
		);

		$menu_html = wp_nav_menu(
			array(
				'echo'             => false,
				'wpf_link_classes' => 'foo bar',
			)
		);
		$this->assertStringContainsString(
			' class="foo bar">',
			$menu_html
		);
	}
}
