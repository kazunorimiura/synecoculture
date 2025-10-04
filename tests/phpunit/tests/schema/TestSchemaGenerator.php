<?php

/**
 * `WPF_Schema_Generator` クラスのユニットテスト
 *
 * @group schema
 * @covers WPF_Schema_Generator
 * @coversDefaultClass WPF_Schema_Generator
 */
class TestSchemaGenerator extends WPF_UnitTestCase {
	/**
	 * @var WPF_Schema_Generator
	 */
	private $generator;

	public function set_up() {
		parent::set_up();
		$this->generator = new WPF_Schema_Generator();
	}

	/**
	 * `@context` 設定の整合性をテスト
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_constructor_sets_context() {
		$data = $this->generator->get_data();
		$this->assertEquals( 'https://schema.org', $data['@context'] );
	}

	/**
	 * JSONデータ取得の整合性をテスト
	 *
	 * @covers ::get_json
	 * @preserveGlobalState disabled
	 */
	public function test_get_json_format() {
		$json = $this->generator->get_json();

		// JSONとして有効か
		$this->assertJson( $json );

		// 整形オプションが正しく適用されているか
		$this->assertStringContainsString( '"@context":', $json );
		$this->assertStringNotContainsString( '\\/', $json ); // JSON_UNESCAPED_SLASHES
	}

	/**
	 * scriptタグ取得の整合性をテスト
	 *
	 * @covers ::get_script
	 * @preserveGlobalState disabled
	 */
	public function test_get_script_element() {
		$script = $this->generator->get_script();

		// script要素の構造チェック
		$this->assertStringStartsWith( '<script type="application/ld+json">', $script );
		$this->assertStringEndsWith( '</script>', $script );

		// JSON内容の妥当性チェック
		$content = preg_replace( '/<\/?script[^>]*>/', '', $script );
		$this->assertJson( $content );
	}
}
