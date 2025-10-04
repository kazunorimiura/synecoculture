<?php

/**
 * WPF_Template_Functions::add_submenu_toggle のユニットテスト。
 *
 * @group template_functions
 * @covers WPF_Template_Functions
 * @coversDefaultClass WPF_Template_Functions
 */
class TestAddSubmenuToggle extends WPF_UnitTestCase {
	public static $wpf_template_functions;
	private static $menu_id        = 0;
	private static $lvl0_menu_item = 0;
	private static $lvl1_menu_item = 0;
	private static $lvl2_menu_item = 0;
	private static $lvl3_menu_item = 0;

	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		parent::wpSetUpBeforeClass( $factory );

		self::$wpf_template_functions = new WPF_Template_Functions();

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

		self::$lvl1_menu_item = wp_update_nav_menu_item(
			self::$menu_id,
			0,
			array(
				'menu-item-title'     => 'Lvl1 menu item',
				'menu-item-url'       => '#',
				'menu-item-parent-id' => self::$lvl0_menu_item,
				'menu-item-status'    => 'publish',
			)
		);

		self::$lvl2_menu_item = wp_update_nav_menu_item(
			self::$menu_id,
			0,
			array(
				'menu-item-title'     => 'Lvl2 menu item',
				'menu-item-url'       => '#',
				'menu-item-parent-id' => self::$lvl1_menu_item,
				'menu-item-status'    => 'publish',
			)
		);

		self::$lvl3_menu_item = wp_update_nav_menu_item(
			self::$menu_id,
			0,
			array(
				'menu-item-title'     => 'Lvl3 menu item',
				'menu-item-url'       => '#',
				'menu-item-parent-id' => self::$lvl2_menu_item,
				'menu-item-status'    => 'publish',
			)
		);

		remove_filter( 'nav_menu_item_id', '_nav_menu_item_id_use_once' );
	}


	/**
	 * @covers ::add_submenu_toggle
	 * @preserveGlobalState disabled
	 */
	public function test_add_submenu_toggle() {
		$menu_html = wp_nav_menu(
			array(
				'menu'   => self::$menu_id,
				'walker' => new WPF_Walker_Nav_Menu(),
				'echo'   => false,
			)
		);

		// Root menu item のボタン要素をチェック
		$this->assertMatchesRegularExpression(
			'/<button[^>]*class="menu-item-toggle"[^>]*aria-expanded="false"[^>]*>.*?<span[^>]*class="screen-reader-text"[^>]*data-open-text="Root menu itemのサブメニューを開く"[^>]*data-close-text="Root menu itemのサブメニューを閉じる"[^>]*>Root menu itemのサブメニューを開く<\/span>.*?<svg[^>]*class="icon"[^>]*>.*?<path[^>]*d="M12 17\.8546L3\.45996 9\.79256L5\.28693 7\.87L12 14\.2006L18\.713 7\.87L20\.54 9\.79256L12 17\.8546Z"[^>]*>.*?<\/svg>.*?<\/button>.*?<div[^>]*class=\'sub-menu-wrapper\'[^>]*>.*?<ul[^>]*class="sub-menu"[^>]*>/s',
			$menu_html
		);

		// Lvl1 menu item のボタン要素をチェック
		$this->assertMatchesRegularExpression(
			'/<button[^>]*class="menu-item-toggle"[^>]*aria-expanded="false"[^>]*>.*?<span[^>]*class="screen-reader-text"[^>]*data-open-text="Lvl1 menu itemのサブメニューを開く"[^>]*data-close-text="Lvl1 menu itemのサブメニューを閉じる"[^>]*>Lvl1 menu itemのサブメニューを開く<\/span>.*?<svg[^>]*class="icon"[^>]*>.*?<path[^>]*d="M12 17\.8546L3\.45996 9\.79256L5\.28693 7\.87L12 14\.2006L18\.713 7\.87L20\.54 9\.79256L12 17\.8546Z"[^>]*>.*?<\/svg>.*?<\/button>.*?<div[^>]*class=\'sub-menu-wrapper\'[^>]*>.*?<ul[^>]*class="sub-menu"[^>]*>/s',
			$menu_html
		);

		// Lvl2 menu item のボタン要素をチェック
		$this->assertMatchesRegularExpression(
			'/<button[^>]*class="menu-item-toggle"[^>]*aria-expanded="false"[^>]*>.*?<span[^>]*class="screen-reader-text"[^>]*data-open-text="Lvl2 menu itemのサブメニューを開く"[^>]*data-close-text="Lvl2 menu itemのサブメニューを閉じる"[^>]*>Lvl2 menu itemのサブメニューを開く<\/span>.*?<svg[^>]*class="icon"[^>]*>.*?<path[^>]*d="M12 17\.8546L3\.45996 9\.79256L5\.28693 7\.87L12 14\.2006L18\.713 7\.87L20\.54 9\.79256L12 17\.8546Z"[^>]*>.*?<\/svg>.*?<\/button>.*?<div[^>]*class=\'sub-menu-wrapper\'[^>]*>.*?<ul[^>]*class="sub-menu"[^>]*>/s',
			$menu_html
		);
	}
}
