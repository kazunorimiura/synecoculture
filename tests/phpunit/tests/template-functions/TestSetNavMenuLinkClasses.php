<?php

/**
 * WPF_Template_Functions::set_nav_menu_link_classes のユニットテスト。
 *
 * @group template_functions
 * @covers WPF_Template_Functions
 * @coversDefaultClass WPF_Template_Functions
 */
class TestSetNavMenuLinkClasses extends WPF_UnitTestCase {
	public static $wpf_template_functions;
	private static $menu_id        = 0;
	private static $lvl0_menu_item = 0;

	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		parent::wpSetUpBeforeClass( $factory );

		self::$wpf_template_functions = new WPF_Template_Functions();

		add_filter( 'nav_menu_link_attributes', array( self::$wpf_template_functions, 'set_nav_menu_link_classes' ), 10, 3 );

		self::$menu_id = wp_create_nav_menu( 'test' );

		self::$lvl0_menu_item = wp_update_nav_menu_item(
			self::$menu_id,
			0,
			array(
				'menu-item-title'  => 'Root menu item',
				'menu-item-url'    => '#',
				'menu-item-status' => 'publish',
			)
		);

		remove_filter( 'nav_menu_item_id', '_nav_menu_item_id_use_once' );
	}

	public static function wpTearDownAfterClass() {
		remove_filter( 'nav_menu_link_attributes', array( self::$wpf_template_functions, 'set_nav_menu_link_classes' ), 10, 3 );
	}

	/**
	 * @covers ::set_nav_menu_link_classes
	 * @preserveGlobalState disabled
	 */
	public function test_set_nav_menu_link_classes() {
		$menu_html = wp_nav_menu(
			array(
				'menu' => self::$menu_id,
				'echo' => false,
			)
		);
		$this->assertStringNotContainsString(
			'<a href="#" class="foo bar">',
			$menu_html
		);

		$menu_html = wp_nav_menu(
			array(
				'menu'             => self::$menu_id,
				'echo'             => false,
				'wpf_link_classes' => 'foo bar',
			)
		);
		$this->assertStringContainsString(
			'<a href="#" class="foo bar">',
			$menu_html
		);
	}
}
