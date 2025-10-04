<?php

/**
 * `WPF_Breadcrumb_List_Schema` クラスのユニットテスト
 *
 * @group schema
 * @covers WPF_Breadcrumb_List_Schema
 * @coversDefaultClass WPF_Breadcrumb_List_Schema
 */
class TestBreadcrumbListSchema extends WPF_UnitTestCase {
	/**
	 * @var WPF_Breadcrumb_List_Schema
	 */
	private $schema;

	/**
	 * テストの実行前にBreadcrumbListスキーマのインスタンスを作成する
	 */
	public function set_up() {
		parent::set_up();
		$this->schema = new WPF_Breadcrumb_List_Schema();
	}

	/**
	 * クラスの継承関係が正しいことを確認する
	 *
	 * BreadcrumbListスキーマが基底スキーマジェネレータを
	 * 継承していることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_inheritance() {
		$this->assertInstanceOf( WPF_Schema_Generator::class, $this->schema );
	}

	/**
	 * スキーマの初期状態を確認する
	 *
	 * コンストラクタで設定される基本プロパティを検証する
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_initial_state() {
		$data = $this->schema->get_data();

		$this->assertEquals( 'https://schema.org', $data['@context'] );
		$this->assertEquals( 'BreadcrumbList', $data['@type'] );
		$this->assertIsArray( $data['itemListElement'] );
		$this->assertEmpty( $data['itemListElement'] );
	}

	/**
	 * 単一のパンくず項目が正しく追加されることを確認する
	 *
	 * add_itemメソッドで追加された項目が正しい構造で
	 * 保存されることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_add_single_item() {
		$this->schema->add_item( 'Home', 'https://example.com', 1 );
		$data = $this->schema->get_data();

		$this->assertCount( 1, $data['itemListElement'] );

		$item = $data['itemListElement'][0];
		$this->assertEquals( 'ListItem', $item['@type'] );
		$this->assertEquals( 1, $item['position'] );
		$this->assertEquals( 'https://example.com', $item['item']['@id'] );
		$this->assertEquals( 'Home', $item['item']['name'] );
	}

	/**
	 * 複数のパンくず項目が正しく追加されることを確認する
	 *
	 * 複数の項目を追加した際の順序と構造を検証する
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_add_multiple_items() {
		$items = array(
			array( 'Home', 'https://example.com', 1 ),
			array( 'Blog', 'https://example.com/blog', 2 ),
			array( 'Article', 'https://example.com/blog/article', 3 ),
		);

		foreach ( $items as $item ) {
			$this->schema->add_item( $item[0], $item[1], $item[2] );
		}

		$data = $this->schema->get_data();

		$this->assertCount( 3, $data['itemListElement'] );

		// 各項目の検証
		foreach ( $data['itemListElement'] as $index => $item ) {
			$this->assertEquals( 'ListItem', $item['@type'] );
			$this->assertEquals( $items[ $index ][2], $item['position'] );
			$this->assertEquals( $items[ $index ][1], $item['item']['@id'] );
			$this->assertEquals( $items[ $index ][0], $item['item']['name'] );
		}
	}

	/**
	 * メソッドチェーンが正しく機能することを確認する
	 *
	 * add_itemメソッドがインスタンスを返し、
	 * チェーン呼び出しが可能であることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_method_chaining() {
		$result = $this->schema
			->add_item( 'Home', 'https://example.com', 1 )
			->add_item( 'Blog', 'https://example.com/blog', 2 );

		$this->assertInstanceOf( WPF_Breadcrumb_List_Schema::class, $result );

		$data = $result->get_data();
		$this->assertCount( 2, $data['itemListElement'] );
	}

	/**
	 * 日本語のパンくず項目が正しく処理されることを確認する
	 *
	 * マルチバイト文字を含む項目名が正しく処理されることを
	 * テストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_japanese_items() {
		$this->schema
			->add_item( 'ホーム', 'https://example.com', 1 )
			->add_item( 'ブログ', 'https://example.com/blog', 2 )
			->add_item( '記事', 'https://example.com/blog/article', 3 );

		$data = $this->schema->get_data();

		$this->assertEquals( 'ホーム', $data['itemListElement'][0]['item']['name'] );
		$this->assertEquals( 'ブログ', $data['itemListElement'][1]['item']['name'] );
		$this->assertEquals( '記事', $data['itemListElement'][2]['item']['name'] );
	}

	/**
	 * JSON-LDの出力形式が正しいことを確認する
	 *
	 * 生成されたJSONが有効なJSON-LDフォーマットであることを
	 * テストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_json_output() {
		$this->schema
			->add_item( 'Home', 'https://example.com', 1 )
			->add_item( 'Blog', 'https://example.com/blog', 2 );

		$json = $this->schema->get_json();

		$this->assertJson( $json );
		$data = json_decode( $json, true );

		$this->assertEquals( 'https://schema.org', $data['@context'] );
		$this->assertEquals( 'BreadcrumbList', $data['@type'] );
		$this->assertCount( 2, $data['itemListElement'] );
	}

	/**
	 * 不正な位置番号の処理を確認する
	 *
	 * ゼロや負の数が位置番号として指定された場合の
	 * 動作を検証する
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_invalid_position() {
		$this->schema->add_item( 'Test', 'https://example.com', 0 );
		$data = $this->schema->get_data();

		// 位置番号がそのまま保存されることを確認
		$this->assertEquals( 0, $data['itemListElement'][0]['position'] );

		$this->schema->add_item( 'Test2', 'https://example.com/test', -1 );
		$data = $this->schema->get_data();
		$this->assertEquals( -1, $data['itemListElement'][1]['position'] );
	}

	/**
	 * 特殊文字を含むURLの処理を確認する
	 *
	 * クエリパラメータや日本語を含むURLが
	 * 正しく処理されることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_special_urls() {
		$urls = array(
			'https://example.com/page?id=123&test=true',
			'https://example.com/カテゴリー/記事',
			'https://example.com/page#section',
		);

		foreach ( $urls as $index => $url ) {
			$this->schema->add_item( 'Page ' . ( $index + 1 ), $url, $index + 1 );
		}

		$data = $this->schema->get_data();

		foreach ( $urls as $index => $url ) {
			$this->assertEquals( $url, $data['itemListElement'][ $index ]['item']['@id'] );
		}
	}
}
