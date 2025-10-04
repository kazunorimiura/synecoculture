<?php

/**
 * WPF_Template_Functions::hide_username_from_comment_class のユニットテスト。
 *
 * @group template_functions
 * @covers WPF_Template_Functions
 * @coversDefaultClass WPF_Template_Functions
 */
class TestHideUsernameFromCommentClass extends WPF_UnitTestCase {

	/**
	 * @covers ::hide_username_from_comment_class
	 * @preserveGlobalState disabled
	 */
	public function test_hide_username_from_comment_class() {
		$testdata = array( 'comments-area', 'show-avatars', 'comment-author-john' );

		$this->assertSame(
			array( 'comments-area', 'show-avatars' ),
			WPF_Template_Functions::hide_username_from_comment_class( $testdata )
		);
	}
}
