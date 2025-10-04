<?php

/**
 * WPF_Utils::get_site_theme のユニットテスト。
 *
 * @group utils
 * @covers WPF_Utils
 * @coversDefaultClass WPF_Utils
 */
class TestGetSiteTheme extends WPF_UnitTestCase {

	/**
	 * @covers ::get_site_theme
	 * @preserveGlobalState disabled
	 */
	public function test_get_site_theme() {
		$this->assertSame( '', WPF_Utils::get_site_theme() );

		$_COOKIE['wpf_site_theme'] = 'dark';
		$this->assertSame( 'dark', WPF_Utils::get_site_theme() );

		$_COOKIE['wpf_site_theme'] = 'light';
		$this->assertSame( 'light', WPF_Utils::get_site_theme() );

		$_COOKIE['wpf_site_theme'] = 'foo';
		$this->assertSame( '', WPF_Utils::get_site_theme() );
	}
}
