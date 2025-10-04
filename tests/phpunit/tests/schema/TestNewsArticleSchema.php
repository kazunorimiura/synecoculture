<?php

/**
 * `WPF_News_Article_Schema` クラスのユニットテスト
 *
 * @group schema
 * @covers WPF_News_Article_Schema
 * @coversDefaultClass WPF_News_Article_Schema
 */
class TestNewsArticleSchema extends WPF_UnitTestCase {
	/**
	 * @var WPF_News_Article_Schema
	 */
	private $schema;

	/**
	 * @var int
	 */
	private $post_id;

	/**
	 * @var int
	 */
	private $author_id;

	/**
	 * テストの実行前にNewsArticleスキーマのインスタンスを作成し、
	 * テスト用の投稿データと著者データを準備する
	 */
	public function set_up() {
		parent::set_up();

		// テスト用の著者を作成
		$this->author_id = $this->factory->user->create(
			array(
				'user_login'   => 'newsauthor',
				'display_name' => 'News Author',
				'role'         => 'author',
			)
		);

		// テスト用のニュース記事を作成
		$this->post_id = $this->factory->post->create(
			array(
				'post_title'   => 'Breaking News',
				'post_content' => 'News article content',
				'post_excerpt' => 'News summary',
				'post_status'  => 'publish',
				'post_author'  => $this->author_id,
				'post_date'    => '2024-01-01 12:00:00',
			)
		);

		$this->go_to( get_permalink( $this->post_id ) );
		setup_postdata( get_post( $this->post_id ) );

		$this->schema = new WPF_News_Article_Schema();
	}

	/**
	 * テストの実行後にテストデータをクリーンアップする
	 */
	public function tear_down() {
		wp_delete_post( $this->post_id, true );
		wp_delete_user( $this->author_id );
		parent::tear_down();
	}

	/**
	 * クラスの継承関係が正しいことを確認する
	 *
	 * NewsArticleスキーマがArticleスキーマを
	 * 継承していることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_inheritance() {
		$this->assertInstanceOf( WPF_Article_Schema::class, $this->schema );
	}

	/**
	 * スキーマタイプが 'NewsArticle' として設定されることを確認する
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_schema_type() {
		$data = $this->schema->get_data();
		$this->assertEquals( 'NewsArticle', $data['@type'] );
	}

	/**
	 * 親クラスの基本情報が正しく継承されることを確認する
	 *
	 * Article型の基本プロパティが正しく設定されることを
	 * テストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_inherited_properties() {
		$data = $this->schema->get_data();

		// 記事の基本情報
		$this->assertEquals( 'Breaking News', $data['headline'] );
		$this->assertEquals( 'News summary', $data['description'] );
		$this->assertStringStartsWith( 'http', $data['url'] );

		// 日付情報
		$this->assertArrayHasKey( 'datePublished', $data );
		$this->assertArrayHasKey( 'dateModified', $data );

		// 日付フォーマットの確認
		$date_pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[-+]\d{2}:\d{2}$/';
		$this->assertMatchesRegularExpression( $date_pattern, $data['datePublished'] );
		$this->assertMatchesRegularExpression( $date_pattern, $data['dateModified'] );
	}

	/**
	 * 著者情報が正しく継承されることを確認する
	 *
	 * 著者情報が Person 型として正しく設定されることを
	 * テストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_inherited_author_info() {
		$data = $this->schema->get_data();

		$this->assertArrayHasKey( 'author', $data );
		$this->assertEquals( 'Person', $data['author']['@type'] );
		$this->assertEquals( 'News Author', $data['author']['name'] );
		$this->assertStringStartsWith( 'http', $data['author']['url'] );
	}

	/**
	 * 発行者情報が正しく継承されることを確認する
	 *
	 * 発行者情報が Organization 型として正しく設定されることを
	 * テストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_inherited_publisher_info() {
		$data = $this->schema->get_data();

		$this->assertArrayHasKey( 'publisher', $data );
		$this->assertEquals( 'Organization', $data['publisher']['@type'] );
		$this->assertEquals( get_bloginfo( 'name' ), $data['publisher']['name'] );
		$this->assertEquals( home_url(), $data['publisher']['url'] );
	}

	/**
	 * アイキャッチ画像の処理が正しく継承されることを確認する
	 *
	 * アイキャッチ画像が ImageObject 型として
	 * 正しく設定されることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_inherited_featured_image() {
		// テスト用の画像をアップロード
		$attachment_id = $this->factory->attachment->create_upload_object(
			DIR_TESTDATA . '/images/test-image.jpg',
			$this->post_id
		);

		set_post_thumbnail( $this->post_id, $attachment_id );

		// スキーマを再生成（画像設定後）
		$schema = new WPF_News_Article_Schema();
		$data   = $schema->get_data();

		$this->assertArrayHasKey( 'image', $data );
		$this->assertEquals( 'ImageObject', $data['image']['@type'] );
		$this->assertStringEndsWith( '.jpg', $data['image']['url'] );
		$this->assertIsInt( $data['image']['width'] );
		$this->assertIsInt( $data['image']['height'] );
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
		$json = $this->schema->get_json();

		$this->assertJson( $json );
		$data = json_decode( $json, true );

		$this->assertEquals( 'https://schema.org', $data['@context'] );
		$this->assertEquals( 'NewsArticle', $data['@type'] );
		$this->assertEquals( 'Breaking News', $data['headline'] );
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
