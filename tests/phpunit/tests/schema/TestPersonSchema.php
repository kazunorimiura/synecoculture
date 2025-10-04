<?php

/**
 * `WPF_Person_Schema` クラスのユニットテスト
 *
 * @group schema
 * @covers WPF_Person_Schema
 * @coversDefaultClass WPF_Person_Schema
 */
class TestPersonSchema extends WPF_UnitTestCase {
	/**
	 * @var WPF_Person_Schema
	 */
	private $schema;

	/**
	 * @var int
	 */
	private $author_id;

	/**
	 * テストの実行前にPersonスキーマのインスタンスを作成し、
	 * テスト用の著者データを準備する
	 */
	public function set_up() {
		parent::set_up();

		// テスト用の著者を作成
		$this->author_id = $this->factory->user->create(
			array(
				'user_login'   => 'testauthor',
				'display_name' => 'Test Author',
				'user_email'   => 'test@example.com',
				'user_url'     => 'https://testauthor.com',
				'description'  => 'Test author description',
				'role'         => 'author',
			)
		);

		// カスタムフィールドを設定
		update_user_meta( $this->author_id, 'position', 'Senior Developer' );
		update_user_meta( $this->author_id, 'x', 'testauthor' );
		update_user_meta( $this->author_id, 'instagram', '@testauthor' );
		update_user_meta( $this->author_id, 'facebook', 'testauthor' );

		$this->schema = new WPF_Person_Schema( $this->author_id );
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
	 * PersonスキーマがSchemaGeneratorを継承していることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_inheritance() {
		$this->assertInstanceOf( WPF_Schema_Generator::class, $this->schema );
	}

	/**
	 * スキーマタイプが 'Person' として設定されることを確認する
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_schema_type() {
		$data = $this->schema->get_data();
		$this->assertEquals( 'Person', $data['@type'] );
	}

	/**
	 * 基本情報が正しく設定されることを確認する
	 *
	 * 名前、URL、説明文が正しく設定されることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_basic_info() {
		$data = $this->schema->get_data();

		$this->assertEquals( 'Test Author', $data['name'] );
		$this->assertEquals( get_author_posts_url( $this->author_id ), $data['url'] );
		$this->assertEquals( 'Test author description', $data['description'] );
	}

	/**
	 * アバター画像が正しく設定されることを確認する
	 *
	 * アバター画像がImageObject型として正しく設定されることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_avatar_image() {
		$data = $this->schema->get_data();

		$this->assertArrayHasKey( 'image', $data );
		$this->assertEquals( 'ImageObject', $data['image']['@type'] );
		$this->assertStringStartsWith( 'http', $data['image']['url'] );
	}

	/**
	 * 職位情報が正しく設定されることを確認する
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_job_title() {
		$data = $this->schema->get_data();

		$this->assertEquals( 'Senior Developer', $data['jobTitle'] );
	}

	/**
	 * メールアドレスが除外されることを確認する
	 *
	 * セキュリティ上の理由でメールアドレスが構造化データに
	 * 含まれないことをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_email_exclusion() {
		$data = $this->schema->get_data();

		$this->assertArrayNotHasKey( 'email', $data );
	}

	/**
	 * ソーシャルメディアプロフィールが正しく設定されることを確認する
	 *
	 * X、Instagram、FacebookのURLが正しく生成されることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_social_profiles() {
		$data = $this->schema->get_data();

		$this->assertArrayHasKey( 'sameAs', $data );
		$this->assertContains( 'https://testauthor.com', $data['sameAs'] );
		$this->assertContains( 'https://x.com/testauthor', $data['sameAs'] );
		$this->assertContains( 'https://instagram.com/testauthor', $data['sameAs'] );
		$this->assertContains( 'https://facebook.com/testauthor', $data['sameAs'] );
	}

	/**
	 * 部分的なソーシャルプロフィールが正しく処理されることを確認する
	 *
	 * 一部のソーシャルフィールドのみが設定された場合の動作を検証する
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_partial_social_profiles() {
		// 新しい著者を作成（一部のソーシャルフィールドのみ）
		$author_id = $this->factory->user->create(
			array(
				'user_login'   => 'partialauthor',
				'display_name' => 'Partial Author',
			)
		);

		update_user_meta( $author_id, 'x', 'partialauthor' );
		// instagram, facebook は設定しない

		$schema = new WPF_Person_Schema( $author_id );
		$data   = $schema->get_data();

		$this->assertArrayHasKey( 'sameAs', $data );
		$this->assertContains( 'https://x.com/partialauthor', $data['sameAs'] );
		$this->assertCount( 1, $data['sameAs'] );

		wp_delete_user( $author_id );
	}

	/**
	 * URLフォーマットのソーシャルプロフィールが正しく処理されることを確認する
	 *
	 * 完全なURLが提供された場合の動作を検証する
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_url_format_social_profiles() {
		$author_id = $this->factory->user->create(
			array(
				'user_login'   => 'urlauthor',
				'display_name' => 'URL Author',
			)
		);

		// 完全なURLを設定
		update_user_meta( $author_id, 'x', 'https://x.com/customuser' );
		update_user_meta( $author_id, 'instagram', 'https://instagram.com/customuser' );

		$schema = new WPF_Person_Schema( $author_id );
		$data   = $schema->get_data();

		$this->assertContains( 'https://x.com/customuser', $data['sameAs'] );
		$this->assertContains( 'https://instagram.com/customuser', $data['sameAs'] );

		wp_delete_user( $author_id );
	}

	/**
	 * 著者投稿情報の設定が正しく動作することを確認する
	 *
	 * set_author_posts_infoメソッドで投稿情報が
	 * 正しく設定されることをテストする
	 *
	 * @covers ::set_author_posts_info
	 * @preserveGlobalState disabled
	 */
	public function test_author_posts_info() {
		// テスト用の投稿を作成
		$post_ids = array(
			$this->factory->post->create(
				array(
					'post_title'  => 'Author Post 1',
					'post_author' => $this->author_id,
					'post_status' => 'publish',
					'post_date'   => '2024-01-01 12:00:00',
				)
			),
			$this->factory->post->create(
				array(
					'post_title'  => 'Author Post 2',
					'post_author' => $this->author_id,
					'post_status' => 'publish',
					'post_date'   => '2024-01-02 12:00:00',
				)
			),
		);

		$this->schema->set_author_posts_info();
		$data = $this->schema->get_data();

		$this->assertArrayHasKey( 'creator', $data );
		$this->assertCount( 2, $data['creator'] );

		foreach ( $data['creator'] as $post ) {
			$this->assertEquals( 'BlogPosting', $post['@type'] );
			$this->assertArrayHasKey( 'name', $post );
			$this->assertArrayHasKey( 'url', $post );
			$this->assertArrayHasKey( 'datePublished', $post );
		}

		// クリーンアップ
		foreach ( $post_ids as $post_id ) {
			wp_delete_post( $post_id, true );
		}
	}

	/**
	 * 投稿のない著者での投稿情報設定を確認する
	 *
	 * 投稿を持たない著者の場合の動作を検証する
	 *
	 * @covers ::set_author_posts_info
	 * @preserveGlobalState disabled
	 */
	public function test_author_posts_info_no_posts() {
		$author_id = $this->factory->user->create(
			array(
				'user_login'   => 'nopostsauthor',
				'display_name' => 'No Posts Author',
			)
		);

		$schema = new WPF_Person_Schema( $author_id );
		$schema->set_author_posts_info();
		$data = $schema->get_data();

		$this->assertArrayNotHasKey( 'creator', $data );

		wp_delete_user( $author_id );
	}

	/**
	 * メソッドチェーンが正しく機能することを確認する
	 *
	 * set_author_posts_infoメソッドがインスタンスを返し、
	 * チェーン呼び出しが可能であることをテストする
	 *
	 * @covers ::set_author_posts_info
	 * @preserveGlobalState disabled
	 */
	public function test_method_chaining() {
		$result = $this->schema->set_author_posts_info();
		$this->assertInstanceOf( WPF_Person_Schema::class, $result );
	}

	/**
	 * 職位情報がない場合の動作を確認する
	 *
	 * 職位が設定されていない場合、jobTitleが設定されないことをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_without_position() {
		$author_id = $this->factory->user->create(
			array(
				'user_login'   => 'nopositionauthor',
				'display_name' => 'No Position Author',
			)
		);

		$schema = new WPF_Person_Schema( $author_id );
		$data   = $schema->get_data();

		$this->assertArrayNotHasKey( 'jobTitle', $data );

		wp_delete_user( $author_id );
	}

	/**
	 * 説明文がない場合の動作を確認する
	 *
	 * 説明文が空の場合、descriptionフィールドが
	 * 設定されないことをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_without_description() {
		$author_id = $this->factory->user->create(
			array(
				'user_login'   => 'nodescauthor',
				'display_name' => 'No Description Author',
				'description'  => '',
			)
		);

		$schema = new WPF_Person_Schema( $author_id );
		$data   = $schema->get_data();

		$this->assertArrayNotHasKey( 'description', $data );

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
				'user_login'   => 'jpauthor',
				'display_name' => '田中太郎',
				'description'  => 'フロントエンド開発者です。',
			)
		);

		update_user_meta( $author_id, 'position', 'シニアエンジニア' );

		$schema = new WPF_Person_Schema( $author_id );
		$data   = $schema->get_data();

		$this->assertEquals( '田中太郎', $data['name'] );
		$this->assertEquals( 'フロントエンド開発者です。', $data['description'] );
		$this->assertEquals( 'シニアエンジニア', $data['jobTitle'] );

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
		$this->assertEquals( 'Person', $data['@type'] );
		$this->assertEquals( 'Test Author', $data['name'] );
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
	}

	/**
	 * 不正な著者IDでの動作を確認する
	 *
	 * 存在しない著者IDが渡された場合の動作を検証する
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_invalid_author_id() {
		$schema = new WPF_Person_Schema( 99999 );
		$data   = $schema->get_data();

		// 基本的な構造は維持されるが、コンテンツは空になる
		$this->assertEquals( 'Person', $data['@type'] );
		$this->assertEquals( 'https://schema.org', $data['@context'] );
	}
}
