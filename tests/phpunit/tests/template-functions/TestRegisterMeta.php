<?php

/**
 * WPF_Template_Functions::register_meta のユニットテスト。
 *
 * @group template_functions
 * @covers WPF_Template_Functions
 * @coversDefaultClass WPF_Template_Functions
 */
class TestRegisterMeta extends WPF_UnitTestCase {
	private static $wpf_template_functions;

	public function set_up() {
		parent::set_up();

		self::$wpf_template_functions = new WPF_Template_Functions();

		add_action( 'init', array( self::$wpf_template_functions, 'register_meta' ) );

		do_action( 'init' );
	}

	public function tear_down() {
		remove_action( 'init', array( self::$wpf_template_functions, 'register_meta' ) );

		parent::tear_down();
	}

	/**
	 * @covers ::register_meta
	 * @preserveGlobalState disabled
	 */
	public function test_register_meta() {
		global $wp_meta_keys;

		$meta_keys = $wp_meta_keys['post']['post'];

		foreach ( $meta_keys as $meta_key => $value ) {
			switch ( $meta_key ) {
				case '_wpf_subtitle':
					$this->assertSame( 'string', $value['type'] );
					$this->assertSame( '', $value['default'] );
					break;
			}
		}
	}
}
