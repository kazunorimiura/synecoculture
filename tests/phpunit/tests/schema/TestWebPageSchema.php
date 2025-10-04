<?php

/**
 * `WPF_Web_Page_Schema` クラスのユニットテスト
 *
 * @group schema
 * @covers WPF_Web_Page_Schema
 * @coversDefaultClass WPF_Web_Page_Schema
 */
class TestWebPageSchema extends WPF_UnitTestCase {
	/**
	 * @var int
	 */
	private $post_id;

	/**
	 * テストの実行前に投稿データを作成し、カレント投稿として設定する
	 */
	public function set_up() {
		parent::set_up();

		// テスト用の投稿を作成
		$this->post_id = $this->factory->post->create(
			array(
				'post_title'   => 'Test Post',
				'post_content' => 'Test content',
				'post_excerpt' => 'Test excerpt',
				'post_status'  => 'publish',
				'post_date'    => '2024-01-01 12:00:00',
			)
		);

		$this->go_to( get_permalink( $this->post_id ) );
		setup_postdata( get_post( $this->post_id ) );
	}

	/**
	 * テストの実行後にテストデータをクリーンアップする
	 */
	public function tear_down() {
		wp_delete_post( $this->post_id, true );
		parent::tear_down();
	}

	/**
	 * デフォルトのスキーマタイプが 'WebPage' であることを確認する
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_default_type() {
		$schema = new WPF_Web_Page_Schema();
		$data   = $schema->get_data();
		$this->assertEquals( 'WebPage', $data['@type'] );
	}

	/**
	 * カスタムスキーマタイプが正しく設定されることを確認する
	 * コンストラクタで指定したスキーマタイプが、生成されたデータに反映されているかをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_custom_type() {
		$schema = new WPF_Web_Page_Schema( 'ArticlePage' );
		$data   = $schema->get_data();
		$this->assertEquals( 'ArticlePage', $data['@type'] );
	}

	/**
	 * 投稿の基本情報が正しくスキーマに反映されることを確認する
	 *
	 * 以下の項目をテスト：
	 * - タイトル（name）
	 * - 抜粋（description）
	 * - URL
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_basic_post_info() {
		$schema = new WPF_Web_Page_Schema();
		$data   = $schema->get_data();

		$this->assertEquals( 'Test Post', $data['name'] );
		$this->assertEquals( 'Test excerpt', $data['description'] );
		$this->assertStringStartsWith( 'http', $data['url'] );
	}

	/**
	 * 公開日と更新日が正しいISO 8601形式で出力されることを確認する
	 *
	 * 日付フォーマットが以下の形式に従っているかテスト：
	 * YYYY-MM-DDThh:mm:ss+hh:mm
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_dates_format() {
		$schema = new WPF_Web_Page_Schema();
		$data   = $schema->get_data();

		$date_pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[-+]\d{2}:\d{2}$/';

		$this->assertMatchesRegularExpression( $date_pattern, $data['datePublished'] );
		$this->assertMatchesRegularExpression( $date_pattern, $data['dateModified'] );
	}

	/**
	 * アイキャッチ画像が正しくスキーマに反映されることを確認する
	 *
	 * テスト用の画像をアップロードし、それがスキーマの
	 * image属性に正しく設定されているかを確認する
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

		$schema = new WPF_Web_Page_Schema();
		$data   = $schema->get_data();

		$this->assertArrayHasKey( 'image', $data );
		$this->assertStringEndsWith( '.jpg', $data['image'] );
	}

	/**
	 * 抜粋が設定されていない場合の動作を確認する
	 *
	 * 抜粋が空の場合、スキーマデータに description フィールドが
	 * 含まれないことを確認する
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_without_excerpt() {
		// 抜粋なしの投稿を作成
		$post_id = $this->factory->post->create(
			array(
				'post_title'   => 'No Excerpt Post',
				'post_content' => 'Test content',
				'post_excerpt' => '',
			)
		);

		$this->go_to( get_permalink( $post_id ) );
		setup_postdata( get_post( $post_id ) );

		$schema = new WPF_Web_Page_Schema();
		$data   = $schema->get_data();

		$this->assertArrayNotHasKey( 'description', $data );
	}
}
