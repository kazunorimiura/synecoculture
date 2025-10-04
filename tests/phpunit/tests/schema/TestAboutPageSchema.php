<?php

/**
 * `WPF_About_Page_Schema` クラスのユニットテスト
 *
 * @group schema
 * @covers WPF_About_Page_Schema
 * @coversDefaultClass WPF_About_Page_Schema
 */
class TestAboutPageSchema extends WPF_UnitTestCase {
	/**
	 * @var WPF_About_Page_Schema
	 */
	private $schema;

	/**
	 * @var int
	 */
	private $post_id;

	/**
	 * テストの実行前にAboutPageスキーマのインスタンスを作成し、
	 * テスト用の投稿データを準備する
	 */
	public function set_up() {
		parent::set_up();

		// テスト用の投稿を作成
		$this->post_id = $this->factory->post->create(
			array(
				'post_title'   => 'About Us',
				'post_content' => 'Company information',
				'post_excerpt' => 'About our company',
				'post_status'  => 'publish',
			)
		);

		$this->go_to( get_permalink( $this->post_id ) );
		setup_postdata( get_post( $this->post_id ) );

		$this->schema = new WPF_About_Page_Schema();
	}

	/**
	 * テストの実行後にテストデータをクリーンアップする
	 */
	public function tear_down() {
		wp_delete_post( $this->post_id, true );
		parent::tear_down();
	}

	/**
	 * スキーマタイプが 'AboutPage' として設定されることを確認する
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_schema_type() {
		$data = $this->schema->get_data();
		$this->assertEquals( 'AboutPage', $data['@type'] );
	}

	/**
	 * 会社情報がデフォルト値（WordPressの設定値）で正しく設定されることを確認する
	 *
	 * 会社情報を指定せずにset_company_infoを呼び出した場合、
	 * WordPressのサイト設定から値が設定されることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_default_company_info() {
		$this->schema->set_company_info( array() );
		$data = $this->schema->get_data();

		$this->assertEquals( 'Organization', $data['mainEntity']['@type'] );
		$this->assertEquals( get_bloginfo( 'name' ), $data['mainEntity']['name'] );
		$this->assertEquals( home_url(), $data['mainEntity']['url'] );
		$this->assertEquals( get_bloginfo( 'description' ), $data['mainEntity']['description'] );
	}

	/**
	 * カスタムの会社情報が正しく設定されることを確認する
	 *
	 * 基本的な会社情報（名前、URL、説明）が
	 * 指定した値で正しく設定されることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_custom_company_info() {
		$company_info = array(
			'name'         => 'Test Company',
			'url'          => 'https://example.com',
			'description'  => 'Test Description',
			'foundingDate' => '2020-01-01',
		);

		$this->schema->set_company_info( $company_info );
		$data = $this->schema->get_data();

		$this->assertEquals( $company_info['name'], $data['mainEntity']['name'] );
		$this->assertEquals( $company_info['url'], $data['mainEntity']['url'] );
		$this->assertEquals( $company_info['description'], $data['mainEntity']['description'] );
		$this->assertEquals( $company_info['foundingDate'], $data['mainEntity']['foundingDate'] );
	}

	/**
	 * 住所情報が正しく設定されることを確認する
	 *
	 * 住所に関する全ての項目（街路、市区町村、都道府県、郵便番号、国）が
	 * PostalAddress型として正しく設定されることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_full_address() {
		$company_info = array(
			'streetAddress'   => '123 Test St',
			'addressLocality' => 'Test City',
			'addressRegion'   => 'Test Region',
			'postalCode'      => '123-4567',
			'addressCountry'  => 'JP',
		);

		$this->schema->set_company_info( $company_info );
		$data = $this->schema->get_data();

		$this->assertEquals( 'PostalAddress', $data['mainEntity']['address']['@type'] );
		$this->assertEquals( $company_info['streetAddress'], $data['mainEntity']['address']['streetAddress'] );
		$this->assertEquals( $company_info['addressLocality'], $data['mainEntity']['address']['addressLocality'] );
		$this->assertEquals( $company_info['addressRegion'], $data['mainEntity']['address']['addressRegion'] );
		$this->assertEquals( $company_info['postalCode'], $data['mainEntity']['address']['postalCode'] );
		$this->assertEquals( $company_info['addressCountry'], $data['mainEntity']['address']['addressCountry'] );
	}

	/**
	 * 部分的な住所情報が正しく設定されることを確認する
	 *
	 * 一部の住所情報のみが提供された場合に、
	 * 提供された項目のみが設定されることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_partial_address() {
		$company_info = array(
			'addressLocality' => 'Test City',
			'addressCountry'  => 'JP',
		);

		$this->schema->set_company_info( $company_info );
		$data = $this->schema->get_data();

		$this->assertEquals( 'PostalAddress', $data['mainEntity']['address']['@type'] );
		$this->assertEquals( $company_info['addressLocality'], $data['mainEntity']['address']['addressLocality'] );
		$this->assertEquals( $company_info['addressCountry'], $data['mainEntity']['address']['addressCountry'] );
		$this->assertArrayNotHasKey( 'streetAddress', $data['mainEntity']['address'] );
		$this->assertArrayNotHasKey( 'postalCode', $data['mainEntity']['address'] );
	}

	/**
	 * メソッドチェーンが正しく機能することを確認する
	 *
	 * set_company_infoメソッドが自身のインスタンスを返し、
	 * メソッドチェーンが可能であることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_method_chaining() {
		$result = $this->schema->set_company_info( array( 'name' => 'Test Company' ) );
		$this->assertInstanceOf( WPF_About_Page_Schema::class, $result );
	}

	/**
	 * 無効なデータが渡された場合の動作を確認する
	 *
	 * 空文字や無効な値が渡された場合に、
	 * デフォルト値が使用されることをテストする
	 *
	 * @covers ::get_data
	 * @preserveGlobalState disabled
	 */
	public function test_invalid_data() {
		$company_info = array(
			'name'         => '',
			'url'          => '',
			'description'  => '',
			'foundingDate' => '',
		);

		$this->schema->set_company_info( $company_info );
		$data = $this->schema->get_data();

		$this->assertEquals( get_bloginfo( 'name' ), $data['mainEntity']['name'] );
		$this->assertEquals( home_url(), $data['mainEntity']['url'] );
		$this->assertEquals( get_bloginfo( 'description' ), $data['mainEntity']['description'] );
		$this->assertArrayNotHasKey( 'foundingDate', $data['mainEntity'] );
	}
}
