<?php

/**
 * WPF_Template_Functions::search_include_terms のユニットテスト。
 *
 * @group template_functions
 * @covers WPF_Template_Functions
 * @coversDefaultClass WPF_Template_Functions
 */
class TestSearchIncludeTerms extends WPF_UnitTestCase {

	/**
	 * @covers ::search_include_terms
	 * @preserveGlobalState disabled
	 */
	public function test_search_include_terms() {
		$this->go_to( home_url( '/?s=Foo' ) );

		$actual = WPF_Template_Functions::search_include_terms( '', $GLOBALS['wp_query'] );

		$this->assertMatchesRegularExpression( '/t.name LIKE \'{.*}Foo{.*}\'/', $actual );
		$this->assertMatchesRegularExpression( '/t.slug LIKE \'{.*}Foo{.*}\'/', $actual );
		$this->assertMatchesRegularExpression( '/tt.description LIKE \'{.*}Foo{.*}\'/', $actual );
	}

	/**
	 * @covers ::search_include_terms
	 * @preserveGlobalState disabled
	 */
	public function test_search_include_terms_with_multiple_words() {
		$this->go_to( home_url( '/?s=Foo+Bar' ) );

		$actual = WPF_Template_Functions::search_include_terms( '', $GLOBALS['wp_query'] );

		$this->assertMatchesRegularExpression( '/t.name LIKE \'{.*}Foo{.*}\'/', $actual );
		$this->assertMatchesRegularExpression( '/t.slug LIKE \'{.*}Foo{.*}\'/', $actual );
		$this->assertMatchesRegularExpression( '/tt.description LIKE \'{.*}Foo{.*}\'/', $actual );
		$this->assertMatchesRegularExpression( '/t.name LIKE \'{.*}Bar{.*}\'/', $actual );
		$this->assertMatchesRegularExpression( '/t.slug LIKE \'{.*}Bar{.*}\'/', $actual );
		$this->assertMatchesRegularExpression( '/tt.description LIKE \'{.*}Bar{.*}\'/', $actual );
	}
}
