<?php

/**
 * WPF_Template_Functions::should_redirect_301 のユニットテスト。
 *
 * @group template_functions
 * @covers WPF_Template_Functions
 * @coversDefaultClass WPF_Template_Functions
 */
class TestShouldRedirect301 extends WPF_UnitTestCase {

	/**
	 * @covers ::should_redirect_301
	 * @preserveGlobalState disabled
	 */
	public function test_should_redirect_301_with_author_page() {
		$user = self::factory()->user->create( array( 'user_nicename' => 'yamada' ) );
		self::factory()->post->create(
			array(
				'post_type'   => 'post',
				'post_author' => $user,
			)
		);

		update_option( 'wpf_disable_author_page', false );
		$this->go_to( home_url( '/author/yamada' ) );
		$this->assertFalse( WPF_Template_Functions::should_redirect_301() );
		$this->assertQueryTrue( 'is_archive', 'is_author' );

		update_option( 'wpf_disable_author_page', true );
		$this->go_to( home_url( '/author/yamada' ) );
		$this->assertTrue( WPF_Template_Functions::should_redirect_301() );
	}

	/**
	 * @covers ::should_redirect_301
	 * @preserveGlobalState disabled
	 */
	public function test_should_redirect_301_with_date_archive() {
		self::factory()->post->create(
			array(
				'post_type' => 'post',
				'post_date' => '2012-12-12',
			)
		);

		update_option( 'wpf_disable_date_archive', false );
		$this->go_to( home_url( '/2012/12/12' ) );
		$this->assertFalse( WPF_Template_Functions::should_redirect_301() );
		$this->assertQueryTrue( 'is_archive', 'is_date', 'is_day' );

		update_option( 'wpf_disable_date_archive', true );
		$this->go_to( home_url( '/2012/12/12' ) );
		$this->assertTrue( WPF_Template_Functions::should_redirect_301() );
	}
}
