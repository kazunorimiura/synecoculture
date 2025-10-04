<?php

/**
 * `WPF_Organization_Schema` クラスのユニットテスト
 *
 * @group schema
 * @covers WPF_Organization_Schema
 * @coversDefaultClass WPF_Organization_Schema
 */
class TestOrganizationSchema extends WPF_UnitTestCase {
	/**
	 * @var WPF_Organization_Schema
	 */
	private $schema;

	/**
	 * テストの実行前にOrganizationスキーマのインスタンスを作成し、
	 * 必要な設定を行う
	 */
	public function set_up() {
		parent::set_up();
		$this->schema = new WPF_Organization_Schema();
	}

	/**
	 * クラスの継承関係が正しいことを確認する
	 *
	 * Organizationスキーマが基底スキーマジェネレータを
	 * 継承していることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_inheritance() {
		$this->assertInstanceOf( WPF_Schema_Generator::class, $this->schema );
	}

	/**
	 * スキーマタイプが 'Organization' として設定されることを確認する
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_schema_type() {
		$data = $this->schema->get_data();
		$this->assertEquals( 'Organization', $data['@type'] );
	}

	/**
	 * 基本情報が正しく設定されることを確認する
	 *
	 * サイト名とURLが正しく設定されることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_basic_info() {
		$data = $this->schema->get_data();

		$this->assertEquals( get_bloginfo( 'name' ), $data['name'] );
		$this->assertEquals( home_url(), $data['url'] );
	}

	/**
	 * ロゴ画像が正しく設定されることを確認する
	 *
	 * カスタムロゴが設定された場合の動作を検証する
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_logo_setup() {
		// テスト用のロゴ画像をアップロード
		$logo_id = $this->factory->attachment->create_upload_object(
			DIR_TESTDATA . '/images/test-image.jpg'
		);

		// カスタムロゴを設定
		set_theme_mod( 'custom_logo', $logo_id );

		// スキーマを再生成（ロゴ設定後）
		$schema = new WPF_Organization_Schema();
		$data   = $schema->get_data();

		$this->assertArrayHasKey( 'logo', $data );
		$this->assertStringEndsWith( '.jpg', $data['logo'] );

		// クリーンアップ
		remove_theme_mod( 'custom_logo' );
	}

	/**
	 * 完全な連絡先情報が正しく設定されることを確認する
	 *
	 * すべての連絡先フィールドが指定された場合の
	 * データ構造を検証する
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_full_contact_point() {
		$contact_info = array(
			'telephone'         => '+81-3-1234-5678',
			'contactType'       => 'customer service',
			'availableLanguage' => array( 'en', 'ja' ),
		);

		$this->schema->set_contact_point( $contact_info );
		$data = $this->schema->get_data();

		$this->assertEquals( 'ContactPoint', $data['contactPoint']['@type'] );
		$this->assertEquals( $contact_info['telephone'], $data['contactPoint']['telephone'] );
		$this->assertEquals( $contact_info['contactType'], $data['contactPoint']['contactType'] );
		$this->assertEquals( $contact_info['availableLanguage'], $data['contactPoint']['availableLanguage'] );
	}

	/**
	 * 部分的な連絡先情報が正しく設定されることを確認する
	 *
	 * 一部の連絡先フィールドのみが指定された場合の
	 * データ構造を検証する
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_partial_contact_point() {
		$contact_info = array(
			'telephone' => '+81-3-1234-5678',
		);

		$this->schema->set_contact_point( $contact_info );
		$data = $this->schema->get_data();

		$this->assertEquals( 'ContactPoint', $data['contactPoint']['@type'] );
		$this->assertEquals( $contact_info['telephone'], $data['contactPoint']['telephone'] );
		$this->assertArrayNotHasKey( 'contactType', $data['contactPoint'] );
		$this->assertArrayNotHasKey( 'availableLanguage', $data['contactPoint'] );
	}

	/**
	 * 空の連絡先情報が正しく処理されることを確認する
	 *
	 * 空の配列や空の値が渡された場合の動作を検証する
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_empty_contact_point() {
		// 完全に空の配列
		$this->schema->set_contact_point( array() );
		$data = $this->schema->get_data();
		$this->assertArrayNotHasKey( 'contactPoint', $data );

		// 空の値を含む配列
		$contact_info = array(
			'telephone'         => '',
			'contactType'       => '',
			'availableLanguage' => '',
		);

		$this->schema->set_contact_point( $contact_info );
		$data = $this->schema->get_data();
		$this->assertArrayNotHasKey( 'contactPoint', $data );
	}

	/**
	 * SNSプロフィールが正しく設定されることを確認する
	 *
	 * 複数のSNSプロファイルURLが正しく設定されることを
	 * テストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_social_profiles() {
		$profiles = array(
			'x'         => 'https://x.com/example',
			'facebook'  => 'https://facebook.com/example',
			'instagram' => 'https://instagram.com/example',
		);

		$this->schema->set_social_profiles( $profiles );
		$data = $this->schema->get_data();

		$this->assertCount( 3, $data['sameAs'] );
		$this->assertContains( 'https://x.com/example', $data['sameAs'] );
		$this->assertContains( 'https://facebook.com/example', $data['sameAs'] );
		$this->assertContains( 'https://instagram.com/example', $data['sameAs'] );
	}

	/**
	 * 無効なSNSプロフィールが正しくフィルタリングされることを確認する
	 *
	 * 空や無効なURLが除外されることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_invalid_social_profiles() {
		$profiles = array(
			'x'        => 'https://x.com/example',
			'invalid'  => '',
			'empty'    => null,
			'facebook' => 'https://facebook.com/example',
		);

		$this->schema->set_social_profiles( $profiles );
		$data = $this->schema->get_data();

		$this->assertCount( 2, $data['sameAs'] );
		$this->assertContains( 'https://x.com/example', $data['sameAs'] );
		$this->assertContains( 'https://facebook.com/example', $data['sameAs'] );
	}

	/**
	 * メソッドチェーンが正しく機能することを確認する
	 *
	 * 各メソッドがインスタンスを返し、チェーン呼び出しが
	 * 可能であることをテストする
	 *
	 * @covers ::set_contact_point
	 * @covers ::set_social_profiles
	 * @preserveGlobalState disabled
	 */
	public function test_method_chaining() {
		$result = $this->schema
			->set_contact_point( array( 'telephone' => '123-456-789' ) )
			->set_social_profiles( array( 'x' => 'https://x.com/example' ) );

		$this->assertInstanceOf( WPF_Organization_Schema::class, $result );
	}

	/**
	 * JSON-LDの出力が正しいことを確認する
	 *
	 * 全ての情報を設定した場合のJSON-LD出力を検証する
	 *
	 * @covers ::get_json
	 * @preserveGlobalState disabled
	 */
	public function test_complete_json_output() {
		$this->schema
			->set_contact_point(
				array(
					'telephone'         => '+81-3-1234-5678',
					'contactType'       => 'customer service',
					'availableLanguage' => array( 'en', 'ja' ),
				)
			)
			->set_social_profiles(
				array(
					'x'        => 'https://x.com/example',
					'facebook' => 'https://facebook.com/example',
				)
			);

		$json = $this->schema->get_json();

		$this->assertJson( $json );
		$data = json_decode( $json, true );

		$this->assertEquals( 'Organization', $data['@type'] );
		$this->assertArrayHasKey( 'contactPoint', $data );
		$this->assertArrayHasKey( 'sameAs', $data );
	}
}
