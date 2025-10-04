<?php

/**
 * WPF_Template_Functions::disable_keyboard_focus_with_specific_anchor のユニットテスト。
 *
 * @group template_functions
 * @covers WPF_Template_Functions
 * @coversDefaultClass WPF_Template_Functions
 */
class TestDisableKeyboardFocusWithSpecificAnchor extends WPF_UnitTestCase {
	public static $wpf_template_functions;
	private static $menu_id        = 0;
	private static $lvl0_menu_item = 0;

	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		parent::wpSetUpBeforeClass( $factory );

		self::$wpf_template_functions = new WPF_Template_Functions();

		add_filter( 'nav_menu_link_attributes', array( self::$wpf_template_functions, 'disable_keyboard_focus_with_specific_anchor' ), 10, 3 );

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
		remove_filter( 'nav_menu_link_attributes', array( self::$wpf_template_functions, 'disable_keyboard_focus_with_specific_anchor' ), 10, 3 );
	}

	/**
	 * @covers ::disable_keyboard_focus_with_specific_anchor
	 * @preserveGlobalState disabled
	 */
	public function test_disable_keyboard_focus_with_specific_anchor() {
		$menu_html = wp_nav_menu(
			array(
				'menu' => self::$menu_id,
				'echo' => false,
			)
		);

		$this->assertStringNotContainsString(
			'<a href="#menu" tabindex="-1">',
			$menu_html
		);

		self::$lvl0_menu_item = wp_update_nav_menu_item(
			self::$menu_id,
			0,
			array(
				'menu-item-title'  => 'Root menu item',
				'menu-item-url'    => '#menu', // キーボードフォーカスを無効にするアンカーを指定。
				'menu-item-status' => 'publish',
			)
		);

		$menu_html = wp_nav_menu(
			array(
				'menu' => self::$menu_id,
				'echo' => false,
			)
		);

		$this->assertStringContainsString(
			'<a href="#menu" tabindex="-1">',
			$menu_html
		);
	}
}
