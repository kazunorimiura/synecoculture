<?php

/**
 * `WPF_Collection_Page_Schema` クラスのユニットテスト
 *
 * @group schema
 * @covers WPF_Collection_Page_Schema
 * @coversDefaultClass WPF_Collection_Page_Schema
 */
class TestCollectionPageSchema extends WPF_UnitTestCase {
	/**
	 * @var WPF_Collection_Page_Schema
	 */
	private $schema;

	/**
	 * @var array
	 */
	private $test_posts;

	/**
	 * テストの実行前にCollectionPageスキーマのインスタンスを作成し、
	 * テスト用の投稿データを準備する
	 */
	public function set_up() {
		parent::set_up();

		// テスト用の投稿を複数作成
		$this->test_posts = array(
			$this->factory->post->create(
				array(
					'post_title'  => 'Test Post 1',
					'post_status' => 'publish',
					'post_date'   => '2024-01-01 12:00:00',
				)
			),
			$this->factory->post->create(
				array(
					'post_title'  => 'Test Post 2',
					'post_status' => 'publish',
					'post_date'   => '2024-01-02 12:00:00',
				)
			),
			$this->factory->post->create(
				array(
					'post_title'  => 'Test Post 3',
					'post_status' => 'publish',
					'post_date'   => '2024-01-03 12:00:00',
				)
			),
		);

		$this->schema = new WPF_Collection_Page_Schema();
	}

	/**
	 * テストの実行後にテストデータをクリーンアップする
	 */
	public function tear_down() {
		foreach ( $this->test_posts as $post_id ) {
			wp_delete_post( $post_id, true );
		}
		parent::tear_down();
	}

	/**
	 * クラスの継承関係が正しいことを確認する
	 *
	 * CollectionPageスキーマがWebPageスキーマを
	 * 継承していることをテストする
	 *
	 * @test
	 */
	public function test_inheritance() {
		$this->assertInstanceOf( WPF_Collection_Page_Schema::class, $this->schema );
	}

	/**
	 * スキーマタイプが 'CollectionPage' として設定されることを確認する
	 *
	 * @test
	 */
	public function test_schema_type() {
		$data = $this->schema->get_data();
		$this->assertEquals( 'CollectionPage', $data['@type'] );
	}

	/**
	 * 投稿一覧が正しく設定されることを確認する
	 *
	 * set_itemsメソッドで投稿一覧を設定した際の
	 * データ構造を検証する
	 *
	 * @test
	 */
	public function test_set_items() {
		$posts = array_map( 'get_post', $this->test_posts );

		$this->schema->set_items( $posts );
		$data = $this->schema->get_data();

		$this->assertArrayHasKey( 'mainEntity', $data );
		$this->assertEquals( 'ItemList', $data['mainEntity']['@type'] );
		$this->assertCount( 3, $data['mainEntity']['itemListElement'] );

		// 各アイテムの構造を確認
		foreach ( $data['mainEntity']['itemListElement'] as $index => $item ) {
			$this->assertEquals( 'ListItem', $item['@type'] );
			$this->assertEquals( $index + 1, $item['position'] );
			$this->assertEquals( 'Test Post ' . ( $index + 1 ), $item['name'] );
			$this->assertStringStartsWith( 'http', $item['url'] );
		}
	}

	/**
	 * メソッドチェーンが正しく機能することを確認する
	 *
	 * set_itemsメソッドがインスタンスを返し、
	 * チェーン呼び出しが可能であることをテストする
	 *
	 * @test
	 */
	public function test_method_chaining() {
		$posts  = array_map( 'get_post', $this->test_posts );
		$result = $this->schema->set_items( $posts );

		$this->assertInstanceOf( WPF_Collection_Page_Schema::class, $result );
	}

	/**
	 * 空の投稿リストが正しく処理されることを確認する
	 *
	 * 空の配列が渡された場合、mainEntityが
	 * 設定されないことをテストする
	 *
	 * @test
	 */
	public function test_empty_items() {
		$this->schema->set_items( array() );
		$data = $this->schema->get_data();

		$this->assertArrayNotHasKey( 'mainEntity', $data );
	}

	/**
	 * 日本語タイトルの投稿が正しく処理されることを確認する
	 *
	 * マルチバイト文字を含むタイトルが正しく
	 * 処理されることをテストする
	 *
	 * @test
	 */
	public function test_japanese_titles() {
		$japanese_posts = array(
			$this->factory->post->create(
				array(
					'post_title'  => '日本語タイトル1',
					'post_status' => 'publish',
				)
			),
			$this->factory->post->create(
				array(
					'post_title'  => '日本語タイトル2',
					'post_status' => 'publish',
				)
			),
		);

		$posts = array_map( 'get_post', $japanese_posts );
		$this->schema->set_items( $posts );
		$data = $this->schema->get_data();

		$this->assertEquals( '日本語タイトル1', $data['mainEntity']['itemListElement'][0]['name'] );
		$this->assertEquals( '日本語タイトル2', $data['mainEntity']['itemListElement'][1]['name'] );
	}

	/**
	 * 親クラスの基本情報が正しく継承されることを確認する
	 *
	 * WebPage型の基本プロパティが正しく設定されることを
	 * テストする
	 *
	 * @test
	 */
	public function test_inherited_properties() {
		// カレントページとして設定
		$collection_page = $this->factory->post->create(
			array(
				'post_title'   => 'Collection Page',
				'post_content' => 'Collection content',
				'post_excerpt' => 'Collection excerpt',
				'post_status'  => 'publish',
			)
		);

		$this->go_to( get_permalink( $collection_page ) );
		setup_postdata( get_post( $collection_page ) );

		$schema = new WPF_Collection_Page_Schema();
		$data   = $schema->get_data();

		$this->assertEquals( 'Collection Page', $data['name'] );
		$this->assertEquals( 'Collection excerpt', $data['description'] );
		$this->assertStringStartsWith( 'http', $data['url'] );

		// 日付フォーマットの確認
		$date_pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[-+]\d{2}:\d{2}$/';
		$this->assertMatchesRegularExpression( $date_pattern, $data['datePublished'] );
		$this->assertMatchesRegularExpression( $date_pattern, $data['dateModified'] );
	}

	/**
	 * JSON-LDの出力が正しいことを確認する
	 *
	 * 投稿一覧を含むJSON-LDが正しく生成されることを
	 * テストする
	 *
	 * @test
	 */
	public function test_json_output() {
		$posts = array_map( 'get_post', $this->test_posts );
		$this->schema->set_items( $posts );

		$json = $this->schema->get_json();

		$this->assertJson( $json );
		$data = json_decode( $json, true );

		$this->assertEquals( 'CollectionPage', $data['@type'] );
		$this->assertEquals( 'ItemList', $data['mainEntity']['@type'] );
		$this->assertCount( 3, $data['mainEntity']['itemListElement'] );
	}
}
