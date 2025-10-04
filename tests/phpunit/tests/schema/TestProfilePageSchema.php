<?php

/**
 * `WPF_Profile_Page_Schema` クラスのユニットテスト
 *
 * @group schema
 * @covers WPF_Profile_Page_Schema
 * @coversDefaultClass WPF_Profile_Page_Schema
 */
class TestProfilePageSchema extends WPF_UnitTestCase {
	/**
	 * @var WPF_Profile_Page_Schema
	 */
	private $schema;

	/**
	 * @var int
	 */
	private $author_id;

	/**
	 * テストの実行前にProfilePageスキーマのインスタンスを作成し、
	 * テスト用の著者データを準備する
	 */
	public function set_up() {
		parent::set_up();

		// テスト用の著者を作成
		$this->author_id = $this->factory->user->create(
			array(
				'user_login'   => 'profileauthor',
				'display_name' => 'Profile Author',
				'user_email'   => 'profile@example.com',
				'user_url'     => 'https://profileauthor.com',
				'description'  => 'Profile author description',
				'role'         => 'author',
			)
		);

		// カスタムフィールドを設定
		update_user_meta( $this->author_id, 'position', 'Lead Developer' );
		update_user_meta( $this->author_id, 'x', 'profileauthor' );
		update_user_meta( $this->author_id, 'instagram', '@profileauthor' );

		// 著者ページをシミュレート
		$this->go_to( get_author_posts_url( $this->author_id ) );
		$this->assertTrue( is_author() );

		$this->schema = new WPF_Profile_Page_Schema();
	}

	/**
	 * テストの実行後にテストデータをクリーンアップする
	 */
	public function tear_down() {
		wp_delete_user( $this->author_id );
		parent::tear_down();
	}

	/**
	 * クラスの継承関係が正しいことを確認する
	 *
	 * ProfilePageスキーマがWebPageスキーマを継承していることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_inheritance() {
		$this->assertInstanceOf( WPF_Web_Page_Schema::class, $this->schema );
	}

	/**
	 * スキーマタイプが 'ProfilePage' として設定されることを確認する
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_schema_type() {
		$data = $this->schema->get_data();
		$this->assertEquals( 'ProfilePage', $data['@type'] );
	}

	/**
	 * 著者ページの基本情報が正しく設定されることを確認する
	 *
	 * 著者名、URL、説明文、画像が正しく設定されることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_author_page_basic_info() {
		$data = $this->schema->get_data();

		$this->assertEquals( 'Profile Author', $data['name'] );
		$this->assertEquals( get_author_posts_url( $this->author_id ), $data['url'] );
		$this->assertEquals( 'Profile author description', $data['description'] );
		$this->assertArrayHasKey( 'image', $data );
		$this->assertStringStartsWith( 'http', $data['image'] );
	}

	/**
	 * 日付フィールドが削除されることを確認する
	 *
	 * ProfilePageでは日付情報が不要なため、
	 * datePublishedとdateModifiedが削除されることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_date_fields_removal() {
		$data = $this->schema->get_data();

		$this->assertArrayNotHasKey( 'datePublished', $data );
		$this->assertArrayNotHasKey( 'dateModified', $data );
	}

	/**
	 * メインエンティティとしてPersonが設定されることを確認する
	 *
	 * mainEntityプロパティにPersonスキーマが
	 * 正しく設定されることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_main_entity_person() {
		$data = $this->schema->get_data();

		$this->assertArrayHasKey( 'mainEntity', $data );
		$this->assertEquals( 'Person', $data['mainEntity']['@type'] );
		$this->assertEquals( 'Profile Author', $data['mainEntity']['name'] );
		$this->assertEquals( get_author_posts_url( $this->author_id ), $data['mainEntity']['url'] );
	}

	/**
	 * @contextの重複が除外されることを確認する
	 *
	 * mainEntityに含まれるPersonスキーマから
	 * @contextが除去されることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_context_duplication_removal() {
		$data = $this->schema->get_data();

		// ProfilePageレベルでは@contextが存在する
		$this->assertEquals( 'https://schema.org', $data['@context'] );

		// mainEntity（Person）では@contextが除去されている
		$this->assertArrayNotHasKey( '@context', $data['mainEntity'] );
	}

	/**
	 * Personエンティティに職位情報が含まれることを確認する
	 *
	 * mainEntityのPersonスキーマに職位情報が
	 * 正しく設定されることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_person_job_title() {
		$data = $this->schema->get_data();

		$this->assertEquals( 'Lead Developer', $data['mainEntity']['jobTitle'] );
	}

	/**
	 * Personエンティティにソーシャルプロフィールが含まれることを確認する
	 *
	 * mainEntityのPersonスキーマにソーシャルメディア情報が
	 * 正しく設定されることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_person_social_profiles() {
		$data = $this->schema->get_data();

		$this->assertArrayHasKey( 'sameAs', $data['mainEntity'] );
		$this->assertContains( 'https://profileauthor.com', $data['mainEntity']['sameAs'] );
		$this->assertContains( 'https://x.com/profileauthor', $data['mainEntity']['sameAs'] );
		$this->assertContains( 'https://instagram.com/profileauthor', $data['mainEntity']['sameAs'] );
	}

	/**
	 * 説明文がない著者の場合の動作を確認する
	 *
	 * 著者の説明文が空の場合の動作を検証する
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_without_author_description() {
		$author_id = $this->factory->user->create(
			array(
				'user_login'   => 'nodescauthor',
				'display_name' => 'No Description Author',
				'description'  => '',
			)
		);

		// 新しい著者ページに移動
		$this->go_to( get_author_posts_url( $author_id ) );
		$this->assertTrue( is_author() );

		$schema = new WPF_Profile_Page_Schema();
		$data   = $schema->get_data();

		$this->assertArrayNotHasKey( 'description', $data );
		$this->assertArrayNotHasKey( 'description', $data['mainEntity'] );

		wp_delete_user( $author_id );
	}

	/**
	 * 職位がない著者の場合の動作を確認する
	 *
	 * 職位情報が設定されていない著者の場合の動作を検証する
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_without_position() {
		$author_id = $this->factory->user->create(
			array(
				'user_login'   => 'noposauthor',
				'display_name' => 'No Position Author',
			)
		);

		// 新しい著者ページに移動
		$this->go_to( get_author_posts_url( $author_id ) );
		$this->assertTrue( is_author() );

		$schema = new WPF_Profile_Page_Schema();
		$data   = $schema->get_data();

		$this->assertArrayNotHasKey( 'jobTitle', $data['mainEntity'] );

		wp_delete_user( $author_id );
	}

	/**
	 * 日本語の著者情報が正しく処理されることを確認する
	 *
	 * 日本語を含む著者情報が正しく処理されることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_japanese_author_info() {
		$author_id = $this->factory->user->create(
			array(
				'user_login'   => 'jpprofileauthor',
				'display_name' => '佐藤花子',
				'description'  => 'UI/UXデザイナーです。',
			)
		);

		update_user_meta( $author_id, 'position', 'デザインディレクター' );

		// 新しい著者ページに移動
		$this->go_to( get_author_posts_url( $author_id ) );
		$this->assertTrue( is_author() );

		$schema = new WPF_Profile_Page_Schema();
		$data   = $schema->get_data();

		$this->assertEquals( '佐藤花子', $data['name'] );
		$this->assertEquals( 'UI/UXデザイナーです。', $data['description'] );
		$this->assertEquals( '佐藤花子', $data['mainEntity']['name'] );
		$this->assertEquals( 'デザインディレクター', $data['mainEntity']['jobTitle'] );

		wp_delete_user( $author_id );
	}

	/**
	 * JSON-LDの出力が正しいことを確認する
	 *
	 * 生成されたJSONが有効なJSON-LDフォーマットであることをテストする
	 *
	 * @covers ::get_json
	 * @preserveGlobalState disabled
	 */
	public function test_json_output() {
		$json = $this->schema->get_json();

		$this->assertJson( $json );
		$data = json_decode( $json, true );

		$this->assertEquals( 'https://schema.org', $data['@context'] );
		$this->assertEquals( 'ProfilePage', $data['@type'] );
		$this->assertEquals( 'Profile Author', $data['name'] );
		$this->assertArrayHasKey( 'mainEntity', $data );
		$this->assertEquals( 'Person', $data['mainEntity']['@type'] );

		// mainEntityには@contextが含まれていないことを確認
		$this->assertArrayNotHasKey( '@context', $data['mainEntity'] );
	}

	/**
	 * script要素の出力が正しいことを確認する
	 *
	 * 生成されたscript要素が正しい形式であることをテストする
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

		// JSONの内容を確認
		$data = json_decode( $json, true );
		$this->assertEquals( 'ProfilePage', $data['@type'] );
		$this->assertArrayHasKey( 'mainEntity', $data );
	}

	/**
	 * WebPageからの継承プロパティが正しく動作することを確認する
	 *
	 * 親クラスからのプロパティが正しく継承されることをテストする
	 * （ただし、日付フィールドは除外される）
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_inherited_properties() {
		$data = $this->schema->get_data();

		// 継承されるプロパティ
		$this->assertArrayHasKey( '@context', $data );
		$this->assertArrayHasKey( 'name', $data );
		$this->assertArrayHasKey( 'url', $data );

		// ProfilePage特有で除外されるプロパティ
		$this->assertArrayNotHasKey( 'datePublished', $data );
		$this->assertArrayNotHasKey( 'dateModified', $data );
	}

	/**
	 * アバター画像が正しくProfilePageレベルに設定されることを確認する
	 *
	 * 著者のアバター画像がページレベルとPersonレベルの
	 * 両方に正しく設定されることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_avatar_image_setup() {
		$data = $this->schema->get_data();

		// ProfilePageレベルでの画像設定
		$this->assertArrayHasKey( 'image', $data );
		$this->assertStringStartsWith( 'http', $data['image'] );

		// Personエンティティレベルでの画像設定
		$this->assertArrayHasKey( 'image', $data['mainEntity'] );
		$this->assertEquals( 'ImageObject', $data['mainEntity']['image']['@type'] );
		$this->assertStringStartsWith( 'http', $data['mainEntity']['image']['url'] );
	}
}
