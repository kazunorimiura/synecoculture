<?php

/**
 * WPF_Template_Functions::change_default_post_type_labels のユニットテスト。
 *
 * @group template_functions
 * @covers WPF_Template_Functions
 * @coversDefaultClass WPF_Template_Functions
 */
class TestChangeDefaultPostTypeLabel extends WPF_UnitTestCase {

	/**
	 * @covers ::change_default_post_type_labels
	 * @preserveGlobalState disabled
	 */
	public function test_change_default_post_type_labels() {
		$post_type_object = get_post_type_object( 'post' );

		$labels = get_post_type_labels( $post_type_object );

		$this->assertSame( 'Posts', $labels->name );

		// ラベル変更。
		WPF_Template_Functions::change_default_post_type_labels();

		$labels = get_post_type_labels( $post_type_object );

		$this->assertSame( 'ニュース', $labels->name );
	}
}
