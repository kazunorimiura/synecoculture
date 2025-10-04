<?php

/**
 * WPF_Shortcode::darkmode_switch のユニットテスト。
 *
 * @group template_tags
 * @covers WPF_Shortcode
 * @coversDefaultClass WPF_Shortcode
 */
class TestDarkmodeSwitch extends WPF_UnitTestCase {

	/**
	 * @covers ::darkmode_switch
	 * @preserveGlobalState disabled
	 */
	public function test_darkmode_switch() {
		$atts   = array();
		$actual = WPF_Shortcode::darkmode_switch( $atts );

		$this->assertMatchesRegularExpression( '/<label for="darkmode-toggle" data-darkmode-toggle.*>/', $actual );
		$this->assertMatchesRegularExpression( '/<span class="darkmode-toggle__label screen-reader-text">ダークモード<\/span>/', $actual );
		$this->assertMatchesRegularExpression( '/<input class="darkmode-toggle__input" id="darkmode-toggle" role="switch" type="checkbox">/', $actual );
		$this->assertMatchesRegularExpression( '/<div class="darkmode-toggle__decor" aria-hidden="true">/', $actual );
		$this->assertMatchesRegularExpression( '/<div class="darkmode-toggle__light"><\/div>/', $actual );
		$this->assertMatchesRegularExpression( '/<div class="darkmode-toggle__thumb"><\/div>/', $actual );
		$this->assertMatchesRegularExpression( '/<div class="darkmode-toggle__dark"><\/div>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/div>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/label>/', $actual );
	}
}
