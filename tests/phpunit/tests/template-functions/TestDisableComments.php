<?php

/**
 * WPF_Template_Functions のコメントを無効化するメソッド群のユニットテスト。
 *
 * @group template_functions
 * @covers WPF_Template_Functions
 * @coversDefaultClass WPF_Template_Functions
 */
class TestDisableComments extends WPF_UnitTestCase {

	/**
	 * @covers ::disable_all_post_types_comments_support
	 * @preserveGlobalState disabled
	 */
	public function test_disable_all_post_types_comments_support() {
		register_post_type(
			'cpt',
			array(
				'public'   => true,
				'supports' => array( 'comments', 'trackbacks' ),
			)
		);

		$post_types = array( 'post', 'cpt' );

		foreach ( $post_types as $post_type ) {
			$this->assertTrue( post_type_supports( $post_type, 'comments' ) );
			$this->assertTrue( post_type_supports( $post_type, 'trackbacks' ) );
		}

		// コメントを無効化。
		WPF_Template_Functions::disable_all_post_types_comments_support();

		foreach ( $post_types as $post_type ) {
			$this->assertFalse( post_type_supports( $post_type, 'comments' ) );
			$this->assertFalse( post_type_supports( $post_type, 'trackbacks' ) );
		}
	}

	/**
	 * @covers ::remove_comments_menu_from_adminbar
	 * @preserveGlobalState disabled
	 */
	public function test_remove_comments_menu_from_adminbar() {
		$editor = self::factory()->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $editor );

		global $wp_admin_bar;

		_wp_admin_bar_init();

		do_action_ref_array( 'admin_bar_menu', array( &$wp_admin_bar ) );

		$nodes = $wp_admin_bar->get_nodes();
		$this->assertTrue( isset( $nodes['comments'] ) );

		_wp_admin_bar_init();

		// 管理バーのコメントメニューを削除。
		WPF_Template_Functions::remove_comments_menu_from_adminbar();

		do_action_ref_array( 'admin_bar_menu', array( &$wp_admin_bar ) );

		$nodes = $wp_admin_bar->get_nodes();
		$this->assertFalse( isset( $nodes['comments'] ) );

		self::delete_user( $editor );
	}

	/**
	 * @covers ::deny_access_to_comments_admin
	 * @preserveGlobalState disabled
	 */
	public function test_deny_access_to_comments_admin() {
		$mock_builder = $this->getMockBuilder( 'WPF_Template_Functions' );
		$mock         = $mock_builder->setMethods( array( 'safe_redirect', 'terminate' ) )->getMock();
		$mock->expects( $this->any() )->method( 'safe_redirect' )->will(
			$this->returnValue( true )
		);
		$mock->expects( $this->any() )->method( 'terminate' )->will(
			$this->returnCallback(
				function( $code ) {
					throw new Exception( $code );
				}
			)
		);

		global $pagenow;

		$editor = self::factory()->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $editor );

		$this->assertSame( null, $mock->deny_access_to_comments_admin() );

		// コメント管理画面に遷移。
		$pagenow = 'edit-comments.php'; //phpcs:ignore

		$this->expectException( Exception::class );
		$mock->deny_access_to_comments_admin();

		// ディスカッション設定画面に遷移。
		$pagenow = 'options-discussion.php'; //phpcs:ignore

		$this->expectException( Exception::class );
		$mock->deny_access_to_comments_admin();

		self::delete_user( $editor );
	}

	/**
	 * @covers ::remove_comments_meta_box
	 * @preserveGlobalState disabled
	 */
	public function test_remove_comments_meta_box() {
		global $wp_meta_boxes;

		// コメントメタボックスを削除する。
		WPF_Template_Functions::remove_comments_meta_box();

		$this->assertFalse( $wp_meta_boxes['dashboard']['normal']['default']['dashboard_recent_comments'] );
		$this->assertArrayHasKey( 'dashboard_recent_comments', $wp_meta_boxes['dashboard']['normal']['default'] );
	}

	/**
	 * @covers ::remove_comments_menu_from_admin
	 * @preserveGlobalState disabled
	 */
	public function test_remove_comments_menu_from_admin() {
		global $menu;

		// https://github.com/WordPress/wordpress-develop/blob/7c254c5510f6e5227e320ea3118f411504f1fe1a/src/wp-admin/menu.php#L96
		$menu = array( // phpcs:ignore
			array(
				'Comments',
				'edit_posts',
				'edit-comments.php',
				'',
				'menu-top menu-icon-comments',
				'menu-comments',
				'dashicons-admin-comments',
			),
		);

		// 管理画面からコメントメニューを削除する。
		WPF_Template_Functions::remove_comments_menu_from_admin();

		$this->assertSame( array(), $menu );
	}

	/**
	 * @covers ::disable_x_pingback
	 * @preserveGlobalState disabled
	 */
	public function test_disable_x_pingback() {
		$headers = array( 'X-Pingback' => get_bloginfo( 'pingback_url', 'display' ) );

		// リクエストヘッダの X-Pingback を無効化する。
		$headers = WPF_Template_Functions::disable_x_pingback( $headers, new WP() );

		$this->assertSame( array(), $headers );
	}
}
