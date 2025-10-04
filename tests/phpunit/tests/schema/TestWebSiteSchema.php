<?php

/**
 * `WPF_Web_Site_Schema` クラスのユニットテスト
 *
 * @group schema
 * @covers WPF_Web_Site_Schema
 * @coversDefaultClass WPF_Web_Site_Schema
 */
class TestWebSiteSchema extends WPF_UnitTestCase {
	/**
	 * @var WPF_Web_Site_Schema
	 */
	private $schema;

	/**
	 * テストの実行前にWebSiteスキーマのインスタンスを作成し、
	 * 必要なWordPress設定を準備する
	 */
	public function set_up() {
		parent::set_up();

		// テスト用のサイト情報を設定
		update_option( 'blogname', 'Test Site' );
		update_option( 'blogdescription', 'Test Description' );

		$this->schema = new WPF_Web_Site_Schema();
	}

	/**
	 * テストの実行後にWordPress設定をクリーンアップする
	 */
	public function tear_down() {
		delete_option( 'blogname' );
		delete_option( 'blogdescription' );
		parent::tear_down();
	}

	/**
	 * クラスの継承関係が正しいことを確認する
	 *
	 * WebSiteスキーマが基底スキーマジェネレータを
	 * 継承していることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_inheritance() {
		$this->assertInstanceOf( WPF_Schema_Generator::class, $this->schema );
	}

	/**
	 * スキーマタイプが 'WebSite' として設定されることを確認する
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_schema_type() {
		$data = $this->schema->get_data();
		$this->assertEquals( 'WebSite', $data['@type'] );
	}

	/**
	 * 基本情報が正しく設定されることを確認する
	 *
	 * サイト名、URL、説明文が正しく設定されることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_basic_info() {
		$data = $this->schema->get_data();

		$this->assertEquals( 'Test Site', $data['name'] );
		$this->assertEquals( home_url(), $data['url'] );
		$this->assertEquals( 'Test Description', $data['description'] );
	}

	/**
	 * 検索アクション情報が正しく設定されることを確認する
	 *
	 * SearchActionの構造と内容を検証する
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_search_action() {
		$this->schema->set_search_action();
		$data = $this->schema->get_data();

		$this->assertArrayHasKey( 'potentialAction', $data );
		$this->assertEquals( 'SearchAction', $data['potentialAction']['@type'] );
		$this->assertEquals(
			home_url( '?s={search_term_string}' ),
			$data['potentialAction']['target']
		);
		$this->assertEquals(
			'required name=search_term_string',
			$data['potentialAction']['query-input']
		);
	}

	/**
	 * メソッドチェーンが正しく機能することを確認する
	 *
	 * set_search_actionメソッドがインスタンスを返し、
	 * チェーン呼び出しが可能であることをテストする
	 *
	 * @covers ::set_search_action
	 * @preserveGlobalState disabled
	 */
	public function test_method_chaining() {
		$result = $this->schema->set_search_action();
		$this->assertInstanceOf( WPF_Web_Site_Schema::class, $result );
	}

	/**
	 * 日本語のサイト情報が正しく処理されることを確認する
	 *
	 * マルチバイト文字を含むサイト情報が
	 * 正しく処理されることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_japanese_site_info() {
		update_option( 'blogname', 'テストサイト' );
		update_option( 'blogdescription', 'サイトの説明文' );

		$schema = new WPF_Web_Site_Schema();
		$data   = $schema->get_data();

		$this->assertEquals( 'テストサイト', $data['name'] );
		$this->assertEquals( 'サイトの説明文', $data['description'] );
	}

	/**
	 * サイトの説明文が空の場合の動作を確認する
	 *
	 * description が空の場合でもスキーマが
	 * 正しく生成されることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_empty_description() {
		update_option( 'blogdescription', '' );

		$schema = new WPF_Web_Site_Schema();
		$data   = $schema->get_data();

		$this->assertEmpty( $data['description'] );
	}

	/**
	 * 特殊文字を含むURLが正しく処理されることを確認する
	 *
	 * クエリパラメータや特殊文字を含むURLが
	 * 正しく処理されることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_url_with_special_chars() {
		$this->schema->set_search_action();
		$data = $this->schema->get_data();

		$target_url = $data['potentialAction']['target'];

		// 検索URLに特殊文字が正しくエンコードされているか確認
		$this->assertStringContainsString( '{search_term_string}', $target_url );
		$this->assertStringStartsWith( home_url(), $target_url );
	}

	/**
	 * JSON-LDの出力が正しいことを確認する
	 *
	 * 生成されたJSONが有効なJSON-LDフォーマットであることを
	 * テストする
	 *
	 * @covers ::get_json
	 * @preserveGlobalState disabled
	 */
	public function test_json_output() {
		$this->schema->set_search_action();
		$json = $this->schema->get_json();

		$this->assertJson( $json );
		$data = json_decode( $json, true );

		$this->assertEquals( 'WebSite', $data['@type'] );
		$this->assertEquals( 'Test Site', $data['name'] );
		$this->assertArrayHasKey( 'potentialAction', $data );
	}

	/**
	 * script要素の出力が正しいことを確認する
	 *
	 * 生成されたscript要素が正しい形式であることを
	 * テストする
	 *
	 * @covers ::get_script
	 * @preserveGlobalState disabled
	 */
	public function test_script_element() {
		$script = $this->schema->get_script();

		$this->assertStringStartsWith( '<script type="application/ld+json">', $script );
		$this->assertStringEndsWith( '</script>', $script );

		// script要素内のJSONを抽出して検証
		$json = preg_replace( '/^<script.*?>|<\/script>$/', '', $script );
		$this->assertJson( $json );
	}
}
