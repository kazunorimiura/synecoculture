<?php

/**
 * WPF_Template_Functions::user_contactmethods のユニットテスト。
 *
 * @group template_functions
 * @covers WPF_Template_Functions
 * @coversDefaultClass WPF_Template_Functions
 */
class TestUserContactmethods extends WPF_UnitTestCase {
	private static $wpf_template_functions;

	public function set_up() {
		parent::set_up();

		self::$wpf_template_functions = new WPF_Template_Functions();

		add_filter( 'user_contactmethods', array( self::$wpf_template_functions, 'user_contactmethods' ) );
	}

	public function tear_down() {
		remove_filter( 'user_contactmethods', array( self::$wpf_template_functions, 'user_contactmethods' ) );

		parent::tear_down();
	}

	/**
	 * @covers ::user_contactmethods
	 * @preserveGlobalState disabled
	 */
	public function test_user_contactmethods() {
		$user = self::factory()->user->create();

		$user_contactmethods = wp_get_user_contact_methods( $user );

		$this->assertSame(
			array(
				'position'  => '肩書き',
				'x'         => 'X',
				'instagram' => 'Instagram',
				'facebook'  => 'Facebook',
			),
			$user_contactmethods
		);

		update_user_meta( $user, 'position', 'Co-founder of WordPress' );
		update_user_meta( $user, 'x', 'https://x.com/photomatt' );
		update_user_meta( $user, 'instagram', 'https://www.instagram.com/photomatt/' );
		update_user_meta( $user, 'facebook', 'https://www.facebook.com/matt.mullenweg' );

		$this->assertSame(
			'Co-founder of WordPress',
			get_the_author_meta( 'position', $user )
		);
		$this->assertSame(
			'https://x.com/photomatt',
			get_the_author_meta( 'x', $user )
		);
		$this->assertSame(
			'https://www.instagram.com/photomatt/',
			get_the_author_meta( 'instagram', $user )
		);
		$this->assertSame(
			'https://www.facebook.com/matt.mullenweg',
			get_the_author_meta( 'facebook', $user )
		);
	}
}
