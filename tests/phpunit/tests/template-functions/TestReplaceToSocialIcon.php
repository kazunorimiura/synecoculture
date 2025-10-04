<?php

/**
 * WPF_Template_Functions::replace_to_social_icon のユニットテスト。
 *
 * @group template_functions
 * @covers WPF_Template_Functions
 * @coversDefaultClass WPF_Template_Functions
 */
class TestReplaceToSocialIcon extends WPF_UnitTestCase {
	public static $wpf_template_functions;
	private static $menu_id        = 0;
	private static $lvl0_menu_item = 0;

	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		parent::wpSetUpBeforeClass( $factory );

		self::$wpf_template_functions = new WPF_Template_Functions();

		add_filter( 'walker_nav_menu_start_el', array( self::$wpf_template_functions, 'replace_to_social_icon' ), 10, 4 );

		self::$menu_id = wp_create_nav_menu( 'test' );

		self::$lvl0_menu_item = wp_update_nav_menu_item(
			self::$menu_id,
			0,
			array(
				'menu-item-title'  => 'X',
				'menu-item-url'    => 'https://x.com',
				'menu-item-status' => 'publish',
			)
		);

		remove_filter( 'nav_menu_item_id', '_nav_menu_item_id_use_once' );
	}

	public static function wpTearDownAfterClass() {
		remove_filter( 'walker_nav_menu_start_el', array( self::$wpf_template_functions, 'replace_to_social_icon' ), 10, 4 );
	}

	/**
	 * @covers ::replace_to_social_icon
	 * @preserveGlobalState disabled
	 */
	public function test_replace_to_social_icon() {
		$menu_html = wp_nav_menu(
			array(
				'theme_location' => 'social_links', // social_links ロケーションを指定。
				'link_before'    => '<span>', // ソーシャルリンクをアイコンに置換するために必須。
				'link_after'     => '</span>',
				'menu'           => self::$menu_id,
				'echo'           => false,
			)
		);

		$this->assertStringContainsString(
			'<a href="https://x.com"><svg class="icon" width="24" height="24" aria-hidden="true" focusable="false" width="24" height="24" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg"><path d="M13.6,10.7l6.2-7.2h-1.5l-5.4,6.3L8.6,3.5h-5l6.5,9.5l-6.5,7.6h1.5l5.7-6.6l4.5,6.6h5L13.6,10.7L13.6,10.7z M11.6,13 l-0.7-0.9L5.7,4.6h2.3l4.2,6.1l0.7,0.9l5.5,7.9h-2.3L11.6,13L11.6,13z"/></svg><span class="screen-reader-text">X</span></a>',
			$menu_html
		);
	}
}
