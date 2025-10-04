<?php

/**
 * WPF_Template_Tags::get_custom_html_attrs のユニットテスト。
 *
 * @group template_tags
 * @covers WPF_Template_Tags
 * @coversDefaultClass WPF_Template_Tags
 */
class TestGetCustomHtmlAttrs extends WPF_UnitTestCase {

	/**
	 * @covers ::get_custom_html_attrs
	 * @preserveGlobalState disabled
	 */
	public function test_get_custom_html_attrs() {
		$this->assertSame(
			WPF_Template_Tags::get_custom_html_attrs(),
			' class="no-js" data-site-theme=""'
		);
	}

	/**
	 * @covers ::get_custom_html_attrs
	 * @preserveGlobalState disabled
	 */
	public function test_wpf_custom_html_attrs_hook() {
		add_filter(
			'wpf_custom_html_attrs',
			function( $html_attributes ) {
				return $html_attributes . ' data-foo="Foo"';
			},
			10,
			1
		);

		$this->assertSame(
			' class="no-js" data-site-theme="" data-foo="Foo"',
			WPF_Template_Tags::get_custom_html_attrs()
		);
	}
}
