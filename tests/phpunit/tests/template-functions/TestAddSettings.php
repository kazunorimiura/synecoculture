<?php

/**
 * WPF_Template_Functions::add_settings のユニットテスト。
 *
 * @group template_functions
 * @covers WPF_Template_Functions
 * @coversDefaultClass WPF_Template_Functions
 */
class TestAddSettings extends WPF_UnitTestCase {
	protected static $administrator;

	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		parent::wpSetUpBeforeClass( $factory );

		self::$administrator = $factory->user->create(
			array(
				'role' => 'administrator',
			)
		);
	}

	public static function wpTearDownAfterClass() {
		self::delete_user( self::$administrator );
	}

	/**
	 * @covers ::add_settings
	 * @preserveGlobalState disabled
	 */
	public function test_add_settings_with_site_owner() {
		wp_set_current_user( self::$administrator );

		WPF_Template_Functions::add_settings();

		$data    = array(
			'site_owner' => 'John Doe',
		);
		$request = new WP_REST_Request( 'PUT', '/wp/v2/settings' );
		$request->add_header( 'Content-Type', 'application/json' );
		$request->set_body( wp_json_encode( $data ) );

		$response = rest_do_request( $request );

		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( 'John Doe', $response->data['site_owner'] );
	}

	/**
	 * @covers ::add_settings
	 * @preserveGlobalState disabled
	 */
	public function test_add_settings_with_posts_per_page_on_mobile() {
		wp_set_current_user( self::$administrator );

		WPF_Template_Functions::add_settings();

		$data    = array(
			'posts_per_page_on_mobile' => 3,
		);
		$request = new WP_REST_Request( 'PUT', '/wp/v2/settings' );
		$request->add_header( 'Content-Type', 'application/json' );
		$request->set_body( wp_json_encode( $data ) );

		$response = rest_do_request( $request );

		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( 3, $response->data['posts_per_page_on_mobile'] );
	}
}
