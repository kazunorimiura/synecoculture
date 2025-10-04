<?php

/**
 * WPF_Template_Functions::add_attributes_to_archives_link のユニットテスト。
 *
 * @group template_functions
 * @covers WPF_Template_Functions
 * @coversDefaultClass WPF_Template_Functions
 */
class TestAddAttributesToArchivesLink extends WPF_UnitTestCase {

	/**
	 * @covers ::add_attributes_to_archives_link
	 * @preserveGlobalState disabled
	 */
	public function test_add_attributes_to_archives_link() {
		$link_html = <<<EOD
<li>
    <a href="http://example.org/2012/">2012</a>
</li>
EOD;

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				<<<EOD
<li class="menu-item">
    <a title="2012のすべての投稿を見る" href="http://example.org/2012/">2012</a>
</li>
EOD
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				WPF_Template_Functions::add_attributes_to_archives_link( $link_html, 'http://example.org/2012/', '2012' )
			)
		);
	}
}
