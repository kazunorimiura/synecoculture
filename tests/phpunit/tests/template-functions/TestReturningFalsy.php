<?php

/**
 * WPF_Template_Functions の falsy な値を返すメソッド群のユニットテスト。
 *
 * @group template_functions
 * @covers WPF_Template_Functions
 * @coversDefaultClass WPF_Template_Functions
 */
class TestReturningFalsy extends WPF_UnitTestCase {

	/**
	 * @covers ::return_false
	 * @preserveGlobalState disabled
	 */
	public function test_return_false() {
		$this->assertFalse( WPF_Template_Functions::return_false() );
	}

	/**
	 * @covers ::return_false
	 * @preserveGlobalState disabled
	 */
	public function test_return_empty_array() {
		$this->assertSame( array(), WPF_Template_Functions::return_empty_array() );
	}
}
