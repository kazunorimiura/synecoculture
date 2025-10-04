<?php

/**
 * `WPF_Contact_Page_Schema` クラスのユニットテスト
 *
 * @group schema
 * @covers WPF_Contact_Page_Schema
 * @coversDefaultClass WPF_Contact_Page_Schema
 */
class TestContactPageSchema extends WPF_UnitTestCase {
	/**
	 * @var WPF_Contact_Page_Schema
	 */
	private $schema;

	/**
	 * @var int
	 */
	private $page_id;

	/**
	 * テストの実行前にContactPageスキーマのインスタンスを作成し、
	 * テスト用のページデータを準備する
	 */
	public function set_up() {
		parent::set_up();

		$this->page_id = $this->factory->post->create(
			array(
				'post_title'   => 'Contact Us',
				'post_content' => 'Contact page content',
				'post_excerpt' => 'Contact page excerpt',
				'post_type'    => 'page',
				'post_status'  => 'publish',
			)
		);

		$this->go_to( get_permalink( $this->page_id ) );
		setup_postdata( get_post( $this->page_id ) );

		$this->schema = new WPF_Contact_Page_Schema();
	}

	/**
	 * テストの実行後にテストデータをクリーンアップする
	 */
	public function tear_down() {
		wp_delete_post( $this->page_id, true );
		parent::tear_down();
	}

	/**
	 * クラスの継承関係が正しいことを確認する
	 *
	 * ContactPageスキーマがWebPageスキーマを
	 * 継承していることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_inheritance() {
		$this->assertInstanceOf( WPF_Web_Page_Schema::class, $this->schema );
	}

	/**
	 * スキーマタイプが 'ContactPage' として設定されることを確認する
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_schema_type() {
		$data = $this->schema->get_data();
		$this->assertEquals( 'ContactPage', $data['@type'] );
	}

	/**
	 * すべての連絡先情報が正しく設定されることを確認する
	 *
	 * 全ての連絡先フィールドが指定された場合の
	 * データ構造を検証する
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_full_contact_info() {
		$contact_info = array(
			'telephone'         => '+81-3-1234-5678',
			'email'             => 'contact@example.com',
			'contactType'       => 'customer service',
			'availableLanguage' => 'en,ja,zh',
			'hoursAvailable'    => 'Mo-Fr 09:00-17:00',
		);

		$this->schema->set_contact_info( $contact_info );
		$data = $this->schema->get_data();

		$this->assertEquals( 'ContactPoint', $data['mainEntity']['@type'] );
		$this->assertEquals( $contact_info['telephone'], $data['mainEntity']['telephone'] );
		$this->assertEquals( $contact_info['email'], $data['mainEntity']['email'] );
		$this->assertEquals( $contact_info['contactType'], $data['mainEntity']['contactType'] );
		$this->assertEquals( array( 'en', 'ja', 'zh' ), $data['mainEntity']['availableLanguage'] );
		$this->assertEquals( $contact_info['hoursAvailable'], $data['mainEntity']['hoursAvailable'] );
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
	public function test_partial_contact_info() {
		$contact_info = array(
			'telephone' => '+81-3-1234-5678',
			'email'     => 'contact@example.com',
		);

		$this->schema->set_contact_info( $contact_info );
		$data = $this->schema->get_data();

		$this->assertEquals( 'ContactPoint', $data['mainEntity']['@type'] );
		$this->assertEquals( $contact_info['telephone'], $data['mainEntity']['telephone'] );
		$this->assertEquals( $contact_info['email'], $data['mainEntity']['email'] );
		$this->assertArrayNotHasKey( 'contactType', $data['mainEntity'] );
		$this->assertArrayNotHasKey( 'availableLanguage', $data['mainEntity'] );
		$this->assertArrayNotHasKey( 'hoursAvailable', $data['mainEntity'] );
	}

	/**
	 * 空の連絡先情報が正しく処理されることを確認する
	 *
	 * 空の配列や空の値が渡された場合の動作を検証する
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_empty_contact_info() {
		// 完全に空の配列
		$this->schema->set_contact_info( array() );
		$data = $this->schema->get_data();
		$this->assertArrayNotHasKey( 'mainEntity', $data );

		// 空の値を含む配列
		$contact_info = array(
			'telephone'         => '',
			'email'             => '',
			'contactType'       => '',
			'availableLanguage' => '',
			'hoursAvailable'    => '',
		);

		$this->schema->set_contact_info( $contact_info );
		$data = $this->schema->get_data();
		$this->assertArrayNotHasKey( 'mainEntity', $data );
	}

	/**
	 * メソッドチェーンが正しく機能することを確認する
	 *
	 * set_contact_infoメソッドがインスタンスを返し、
	 * チェーン呼び出しが可能であることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_method_chaining() {
		$result = $this->schema->set_contact_info( array( 'telephone' => '123-456-789' ) );
		$this->assertInstanceOf( WPF_Contact_Page_Schema::class, $result );
	}

	/**
	 * 利用可能言語の処理が正しく行われることを確認する
	 *
	 * カンマ区切りの言語リストが配列に
	 * 正しく変換されることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_available_languages_processing() {
		$test_cases = array(
			'en,ja'       => array( 'en', 'ja' ),
			'en'          => array( 'en' ),
			'en,ja,zh,ko' => array( 'en', 'ja', 'zh', 'ko' ),
		);

		foreach ( $test_cases as $input => $expected ) {
			$this->schema->set_contact_info( array( 'availableLanguage' => $input ) );
			$data = $this->schema->get_data();
			$this->assertEquals( $expected, $data['mainEntity']['availableLanguage'] );
		}
	}

	/**
	 * 特殊な電話番号形式が正しく処理されることを確認する
	 *
	 * 様々な形式の電話番号が正しく処理されることを
	 * テストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_telephone_formats() {
		$test_numbers = array(
			'+81-3-1234-5678',
			'03-1234-5678',
			'0312345678',
			'+1 (555) 123-4567',
		);

		foreach ( $test_numbers as $number ) {
			$this->schema->set_contact_info( array( 'telephone' => $number ) );
			$data = $this->schema->get_data();
			$this->assertEquals( $number, $data['mainEntity']['telephone'] );
		}
	}

	/**
	 * 日本語の連絡先情報が正しく処理されることを確認する
	 *
	 * 日本語を含む連絡先情報が正しく処理されることを
	 * テストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_japanese_contact_info() {
		$contact_info = array(
			'contactType'    => 'カスタマーサービス',
			'hoursAvailable' => '月曜日〜金曜日 9時から17時まで',
		);

		$this->schema->set_contact_info( $contact_info );
		$data = $this->schema->get_data();

		$this->assertEquals( $contact_info['contactType'], $data['mainEntity']['contactType'] );
		$this->assertEquals( $contact_info['hoursAvailable'], $data['mainEntity']['hoursAvailable'] );
	}
}
