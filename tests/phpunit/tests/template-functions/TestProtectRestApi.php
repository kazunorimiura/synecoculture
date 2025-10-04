<?php

/**
 * WPF_Template_Functions::protect_rest_api のユニットテスト。
 *
 * @group template_functions
 * @covers WPF_Template_Functions
 * @coversDefaultClass WPF_Template_Functions
 */
class TestProtectRestApi extends WPF_UnitTestCase {
	public static $wpf_template_functions;

	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		parent::wpSetUpBeforeClass( $factory );

		activate_plugin( 'akismet/akismet.php' );
		activate_plugin( 'redirection/redirection.php' );
		activate_plugin( 'contact-form-7/wp-contact-form-7.php' );
		activate_plugin( 'wordpress-seo/wp-seo.php' );
		activate_plugin( 'jetpack/jetpack.php' );

		global $wp_rest_server;
		$wp_rest_server = new Spy_REST_Server();
		do_action( 'rest_api_init', $wp_rest_server );
	}

	public static function wpTearDownAfterClass() {
		deactivate_plugins( 'akismet/akismet.php' );
		deactivate_plugins( 'redirection/redirection.php' );
		deactivate_plugins( 'contact-form-7/wp-contact-form-7.php' );
		deactivate_plugins( 'wordpress-seo/wp-seo.php' );

		// HACK: Jetpack 無効化時の Plugin_Storage::ensure_configured の WP_Error を回避する。
		// https://github.com/Automattic/jetpack-production/blob/5809d653f67ff464404c2915cf35a72ce0b06cec/jetpack_vendor/automattic/jetpack-connection/src/class-plugin-storage.php#L149C1-L149C1
		require_once ABSPATH . '/wp-content/plugins/jetpack/jetpack_vendor/automattic/jetpack-connection/src/class-plugin-storage.php';
		$plugin_storage = new Automattic\Jetpack\Connection\Plugin_Storage();
		$plugin_storage->configure();
		deactivate_plugins( 'jetpack/jetpack.php' );
	}

	public function set_up() {
		parent::set_up();

		self::$wpf_template_functions = new WPF_Template_Functions();

		add_filter( 'rest_pre_dispatch', array( self::$wpf_template_functions, 'protect_rest_api' ), 10, 4 );
	}

	public function tear_down() {
		remove_filter( 'rest_pre_dispatch', array( self::$wpf_template_functions, 'protect_rest_api' ), 10, 4 );

		parent::tear_down();
	}

	/**
	 * @covers ::protect_rest_api
	 * @preserveGlobalState disabled
	 */
	public function test_protect_rest_api_from_authorized_user() {
		$editor = self::factory()->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $editor );

		$request  = new WP_REST_Request( 'GET', '/wp/v2/posts' );
		$response = rest_get_server()->dispatch( $request );
		$status   = $response->get_status();

		$this->assertSame( 200, $status );

		self::delete_user( $editor );
	}

	/**
	 * @covers ::protect_rest_api
	 * @preserveGlobalState disabled
	 */
	public function test_protect_rest_api_from_unauthorized_user() {
		$request  = new WP_REST_Request( 'GET', '/wp/v2/posts' );
		$response = rest_get_server()->dispatch( $request );
		$status   = $response->get_status();
		$data     = $response->get_data();

		$this->assertSame( 401, $status );
		$this->assertSame( 'wpf_rest_forbidden', $data['code'] );
	}

	/**
	 * @covers ::protect_rest_api
	 * @preserveGlobalState disabled
	 */
	public function test_protect_rest_api_with_oembed() {
		$request  = new WP_REST_Request( 'GET', '/oembed/1.0' );
		$response = rest_get_server()->dispatch( $request );
		$status   = $response->get_status();

		$this->assertSame( 200, $status );
	}

	/**
	 * @covers ::protect_rest_api
	 * @preserveGlobalState disabled
	 */
	public function test_protect_rest_api_with_akismet_plugin() {
		$request  = new WP_REST_Request( 'GET', '/akismet/v1' );
		$response = rest_get_server()->dispatch( $request );
		$status   = $response->get_status();

		$this->assertSame( 200, $status );
	}

	/**
	 * @covers ::protect_rest_api
	 * @preserveGlobalState disabled
	 */
	public function test_protect_rest_api_with_redirection_plugin() {
		$request  = new WP_REST_Request( 'GET', '/redirection/v1' );
		$response = rest_get_server()->dispatch( $request );
		$status   = $response->get_status();

		$this->assertSame( 200, $status );
	}

	/**
	 * @covers ::protect_rest_api
	 * @preserveGlobalState disabled
	 */
	public function test_protect_rest_api_with_contact_form_7_plugin() {
		$request  = new WP_REST_Request( 'GET', '/contact-form-7/v1' );
		$response = rest_get_server()->dispatch( $request );
		$status   = $response->get_status();

		$this->assertSame( 200, $status );
	}

	/**
	 * @covers ::protect_rest_api
	 * @preserveGlobalState disabled
	 */
	public function test_protect_rest_api_with_yoast_plugin() {
		$test = is_plugin_active( 'wordpress-seo/wp-seo.php' );
		$this->assertTrue( $test );

		$request  = new WP_REST_Request( 'GET', '/yoast/v1' );
		$response = rest_get_server()->dispatch( $request );
		$status   = $response->get_status();

		$this->assertSame( 200, $status );
	}

	/**
	 * @covers ::protect_rest_api
	 * @preserveGlobalState disabled
	 */
	public function test_protect_rest_api_with_jetpack_plugin() {
		$request  = new WP_REST_Request( 'GET', '/jetpack/v4' );
		$response = rest_get_server()->dispatch( $request );
		$status   = $response->get_status();

		$this->assertSame( 200, $status );
	}
}
