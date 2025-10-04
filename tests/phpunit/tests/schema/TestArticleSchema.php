<?php

/**
 * `WPF_Article_Schema` クラスのユニットテスト
 *
 * @group schema
 * @covers WPF_Article_Schema
 * @coversDefaultClass WPF_Article_Schema
 */
class TestArticleSchema extends WPF_UnitTestCase {
	/**
	 * @var WPF_Article_Schema
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
	 * テストの実行前にArticleスキーマのインスタンスを作成し、
	 * テスト用の投稿データと著者データを準備する
	 */
	public function set_up() {
		parent::set_up();

		// テスト用の著者を作成
		$this->author_id = $this->factory->user->create(
			array(
				'user_login'   => 'testauthor',
				'display_name' => 'Test Author',
				'role'         => 'author',
			)
		);

		// テスト用の投稿を作成
		$this->post_id = $this->factory->post->create(
			array(
				'post_title'   => 'Test Article',
				'post_content' => 'Test content',
				'post_excerpt' => 'Test excerpt',
				'post_status'  => 'publish',
				'post_author'  => $this->author_id,
				'post_date'    => '2024-01-01 12:00:00',
			)
		);

		$this->go_to( get_permalink( $this->post_id ) );
		setup_postdata( get_post( $this->post_id ) );

		$this->schema = new WPF_Article_Schema();
	}

	/**
	 * テストの実行後にテストデータをクリーンアップする
	 */
	public function tear_down() {
		wp_delete_post( $this->post_id, true );
		wp_delete_user( $this->author_id );
		remove_theme_mod( 'custom_logo' );
		parent::tear_down();
	}

	/**
	 * デフォルトのスキーマタイプが 'Article' として設定されることを確認する
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_default_schema_type() {
		$data = $this->schema->get_data();
		$this->assertEquals( 'Article', $data['@type'] );
	}

	/**
	 * カスタムのArticleタイプが正しく設定されることを確認する
	 *
	 * BlogPosting等の派生タイプを指定した場合に、
	 * 正しく設定されることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_custom_article_type() {
		$schema = new WPF_Article_Schema( 'BlogPosting' );
		$data   = $schema->get_data();
		$this->assertEquals( 'BlogPosting', $data['@type'] );
	}

	/**
	 * 記事の基本情報が正しく設定されることを確認する
	 *
	 * ヘッドライン、URL、公開日、更新日、説明文が
	 * 正しく設定されることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_basic_article_info() {
		$data = $this->schema->get_data();

		$this->assertEquals( 'Test Article', $data['headline'] );
		$this->assertStringStartsWith( 'http', $data['url'] );
		$this->assertEquals( 'Test excerpt', $data['description'] );

		// 日付フォーマットの確認
		$date_pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[-+]\d{2}:\d{2}$/';
		$this->assertMatchesRegularExpression( $date_pattern, $data['datePublished'] );
		$this->assertMatchesRegularExpression( $date_pattern, $data['dateModified'] );
	}

	/**
	 * アイキャッチ画像情報が正しく設定されることを確認する
	 *
	 * 画像のURL、幅、高さが ImageObject 型として
	 * 正しく設定されることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_featured_image() {
		// テスト用の画像をアップロード
		$attachment_id = $this->factory->attachment->create_upload_object(
			DIR_TESTDATA . '/images/test-image.jpg',
			$this->post_id
		);

		set_post_thumbnail( $this->post_id, $attachment_id );

		// スキーマを再生成（画像設定後）
		$schema = new WPF_Article_Schema();
		$data   = $schema->get_data();

		$this->assertEquals( 'ImageObject', $data['image']['@type'] );
		$this->assertStringEndsWith( '.jpg', $data['image']['url'] );
		$this->assertIsInt( $data['image']['width'] );
		$this->assertIsInt( $data['image']['height'] );
	}

	/**
	 * 著者情報が正しく設定されることを確認する
	 *
	 * 著者名とURLが Person 型として正しく設定されることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_author_info() {
		$data = $this->schema->get_data();

		$this->assertEquals( 'Person', $data['author']['@type'] );
		$this->assertEquals( 'Test Author', $data['author']['name'] );
		$this->assertStringStartsWith( 'http', $data['author']['url'] );
		$this->assertStringContainsString( 'author', $data['author']['url'] );
	}

	/**
	 * 発行者（組織）情報が正しく設定されることを確認する
	 *
	 * サイト名とURLが Organization 型として
	 * 正しく設定されることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_publisher_info() {
		$data = $this->schema->get_data();

		$this->assertEquals( 'Organization', $data['publisher']['@type'] );
		$this->assertEquals( get_bloginfo( 'name' ), $data['publisher']['name'] );
		$this->assertEquals( home_url(), $data['publisher']['url'] );
	}

	/**
	 * 組織ロゴが正しく設定されることを確認する
	 *
	 * カスタムロゴが ImageObject 型として
	 * 正しく設定されることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_publisher_logo() {
		// テスト用のロゴ画像をアップロード
		$logo_id = $this->factory->attachment->create_upload_object(
			DIR_TESTDATA . '/images/test-image.jpg'
		);

		// カスタムロゴを設定
		set_theme_mod( 'custom_logo', $logo_id );

		// スキーマを再生成（ロゴ設定後）
		$schema = new WPF_Article_Schema();
		$data   = $schema->get_data();

		$this->assertEquals( 'ImageObject', $data['publisher']['logo']['@type'] );
		$this->assertStringEndsWith( '.jpg', $data['publisher']['logo']['url'] );
	}

	/**
	 * 抜粋文がない場合の動作を確認する
	 *
	 * 抜粋文が設定されていない場合、description フィールドが
	 * 存在しないことをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_without_excerpt() {
		// 抜粋なしの投稿を作成
		$post_id = $this->factory->post->create(
			array(
				'post_title'   => 'No Excerpt Article',
				'post_content' => 'Test content',
				'post_excerpt' => '',
				'post_author'  => $this->author_id,
			)
		);

		$this->go_to( get_permalink( $post_id ) );
		setup_postdata( get_post( $post_id ) );

		$schema = new WPF_Article_Schema();
		$data   = $schema->get_data();

		$this->assertArrayNotHasKey( 'description', $data );
	}
}
