<?php

/**
 * WPF_Template_Functions::add_schema のユニットテスト。
 *
 * @group template_functions
 * @covers WPF_Template_Functions
 * @coversDefaultClass WPF_Template_Functions
 */
class TestAddSchema extends WPF_UnitTestCase {
	/**
	 * @var int
	 */
	private $post_id;

	/**
	 * テストの実行前に必要なデータを準備する
	 */
	public function set_up() {
		parent::set_up();

		// テスト用の投稿を作成
		$this->post_id = $this->factory->post->create(
			array(
				'post_title'   => 'Test Post',
				'post_content' => 'Test content',
				'post_status'  => 'publish',
			)
		);

		// グローバル変数の設定
		global $post;
		$post = get_post( $this->post_id ); // phpcs:ignore
	}

	/**
	 * テストの実行後にデータをクリーンアップする
	 */
	public function tear_down() {
		wp_delete_post( $this->post_id, true );
		parent::tear_down();
	}

	/**
	 * スキーマタイプが設定されていない場合の動作を確認する
	 *
	 * スキーマタイプが未設定の場合、何も出力されないことを
	 * テストする
	 *
	 * @covers ::add_schema
	 * @preserveGlobalState disabled
	 */
	public function test_no_schema_type() {
		ob_start();
		WPF_Template_Functions::add_schema();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'WebPage', $output );
		$this->assertStringContainsString( 'Test Post', $output );
		$this->assertStringContainsString( 'http://example.org/' . gmdate( 'Y/m/d' ) . '/test-post/', $output );

		// 複数のスクリプトタグを分割
		preg_match_all( '/<script.*?>(.*?)<\/script>/s', $output, $matches );

		// 1つ目のタグをテスト
		$json = preg_replace( '/^<script.*?>|<\/script>$/', '', $matches[1][0] );
		$this->assertJson( $json );

		// 2つ目のタグをテスト
		$json = preg_replace( '/^<script.*?>|<\/script>$/', '', $matches[1][1] );
		$this->assertJson( $json );
	}

	/**
	 * クエリタイプごとのデフォルトスキーマ設定をテストする
	 *
	 * @covers ::add_schema
	 * @preserveGlobalState disabled
	 */
	public function test_default_schema_by_query_type() {
		// 投稿タイプごとのテスト用記事を作成
		$post_id = $this->factory->post->create(
			array(
				'post_title'  => 'Test Post',
				'post_type'   => 'post',
				'post_status' => 'publish',
			)
		);

		$page_id = $this->factory->post->create(
			array(
				'post_title'  => 'Test Page',
				'post_type'   => 'page',
				'post_status' => 'publish',
			)
		);

		// カテゴリーの作成
		$category_id = $this->factory->term->create(
			array(
				'name'     => 'Test Category',
				'taxonomy' => 'category',
			)
		);

		/**
		 * 投稿ページのテスト
		 */
		$this->go_to( get_permalink( $post_id ) );
		$this->assertTrue( is_single() );

		global $post;

		ob_start();
		WPF_Template_Functions::add_schema();
		$output = ob_get_clean();

		// 投稿ページではArticleスキーマが使用されることを確認
		$this->assertStringContainsString( 'Article', $output );

		/**
		 * 固定ページのテスト
		 */
		$this->go_to( get_permalink( $page_id ) );
		$this->assertTrue( is_page() );

		ob_start();
		WPF_Template_Functions::add_schema();
		$output = ob_get_clean();

		// 固定ページではWebPageスキーマが使用されることを確認
		$this->assertStringContainsString( 'WebPage', $output );

		/**
		 * アーカイブページのテスト
		 */
		$this->go_to( '/?cat=' . $category_id );
		$this->assertTrue( is_archive() );

		ob_start();
		WPF_Template_Functions::add_schema();
		$output = ob_get_clean();

		// アーカイブページではCollectionPageスキーマが使用されることを確認
		$this->assertStringContainsString( 'CollectionPage', $output );

		/**
		 * フロントページのテスト
		 */
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $page_id );
		$this->go_to( home_url() );
		$this->assertTrue( is_front_page() );

		ob_start();
		WPF_Template_Functions::add_schema();
		$output = ob_get_clean();

		// フロントページではWebSiteスキーマが使用されることを確認
		$this->assertStringContainsString( 'WebSite', $output );
	}

	/**
	 * AboutPageスキーマの出力を確認する
	 *
	 * AboutPage用のメタデータが正しく処理され、
	 * 適切なスキーマが出力されることをテストする
	 *
	 * @covers ::add_schema
	 * @preserveGlobalState disabled
	 */
	public function test_about_page_schema() {
		// AboutPage用のメタデータを設定
		update_post_meta( $this->post_id, '_wpf_schema_type', 'AboutPage' );
		update_post_meta( $this->post_id, '_wpf_company_foundingDate', '2020-01-01' );
		update_post_meta( $this->post_id, '_wpf_company_streetAddress', '123 Test St' );
		update_post_meta( $this->post_id, '_wpf_company_addressLocality', 'Test City' );
		update_post_meta( $this->post_id, '_wpf_company_addressRegion', 'Test Region' );
		update_post_meta( $this->post_id, '_wpf_company_postalCode', '123-4567' );
		update_post_meta( $this->post_id, '_wpf_company_addressCountry', 'JP' );

		ob_start();
		WPF_Template_Functions::add_schema();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'AboutPage', $output );
		$this->assertStringContainsString( '2020-01-01', $output );
		$this->assertStringContainsString( '123 Test St', $output );

		// 複数のスクリプトタグを分割
		preg_match_all( '/<script.*?>(.*?)<\/script>/s', $output, $matches );

		// 1つ目のタグをテスト
		$json = preg_replace( '/^<script.*?>|<\/script>$/', '', $matches[1][0] );
		$this->assertJson( $json );

		// 2つ目のタグをテスト
		$json = preg_replace( '/^<script.*?>|<\/script>$/', '', $matches[1][1] );
		$this->assertJson( $json );
	}

	/**
	 * ContactPageスキーマの出力を確認する
	 *
	 * ContactPage用のメタデータが正しく処理され、
	 * 適切なスキーマが出力されることをテストする
	 *
	 * @covers ::add_schema
	 * @preserveGlobalState disabled
	 */
	public function test_contact_page_schema() {
		// ContactPage用のメタデータを設定
		update_post_meta( $this->post_id, '_wpf_schema_type', 'ContactPage' );
		update_post_meta( $this->post_id, '_wpf_contact_telephone', '+81-3-1234-5678' );
		update_post_meta( $this->post_id, '_wpf_contact_email', 'test@example.com' );
		update_post_meta( $this->post_id, '_wpf_contact_contactType', 'customer service' );
		update_post_meta( $this->post_id, '_wpf_contact_availableLanguage', 'en,ja' );
		update_post_meta( $this->post_id, '_wpf_contact_hoursAvailable', '9:00-17:00' );

		ob_start();
		WPF_Template_Functions::add_schema();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'ContactPage', $output );
		$this->assertStringContainsString( 'test@example.com', $output );

		// 複数のスクリプトタグを分割
		preg_match_all( '/<script.*?>(.*?)<\/script>/s', $output, $matches );

		// 1つ目のタグをテスト
		$json = preg_replace( '/^<script.*?>|<\/script>$/', '', $matches[1][0] );
		$this->assertJson( $json );

		// 2つ目のタグをテスト
		$json = preg_replace( '/^<script.*?>|<\/script>$/', '', $matches[1][1] );
		$this->assertJson( $json );
	}

	/**
	 * NewsArticleスキーマの出力を確認する
	 *
	 * NewsArticle型のスキーマが正しく出力されることを
	 * テストする
	 *
	 * @covers ::add_schema
	 * @preserveGlobalState disabled
	 */
	public function test_news_article_schema() {
		update_post_meta( $this->post_id, '_wpf_schema_type', 'NewsArticle' );

		ob_start();
		WPF_Template_Functions::add_schema();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'NewsArticle', $output );

		// 複数のスクリプトタグを分割
		preg_match_all( '/<script.*?>(.*?)<\/script>/s', $output, $matches );

		// 1つ目のタグをテスト
		$json = preg_replace( '/^<script.*?>|<\/script>$/', '', $matches[1][0] );
		$this->assertJson( $json );

		// 2つ目のタグをテスト
		$json = preg_replace( '/^<script.*?>|<\/script>$/', '', $matches[1][1] );
		$this->assertJson( $json );
	}

	/**
	 * BlogPostingスキーマの出力を確認する
	 *
	 * BlogPosting型のスキーマが正しく出力されることを
	 * テストする
	 *
	 * @covers ::add_schema
	 * @preserveGlobalState disabled
	 */
	public function test_blog_posting_schema() {
		update_post_meta( $this->post_id, '_wpf_schema_type', 'BlogPosting' );

		ob_start();
		WPF_Template_Functions::add_schema();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'BlogPosting', $output );

		// 複数のスクリプトタグを分割
		preg_match_all( '/<script.*?>(.*?)<\/script>/s', $output, $matches );

		// 1つ目のタグをテスト
		$json = preg_replace( '/^<script.*?>|<\/script>$/', '', $matches[1][0] );
		$this->assertJson( $json );

		// 2つ目のタグをテスト
		$json = preg_replace( '/^<script.*?>|<\/script>$/', '', $matches[1][1] );
		$this->assertJson( $json );
	}

	/**
	 * CollectionPageスキーマの出力を確認する
	 *
	 * CollectionPage型のスキーマが正しく出力されることを
	 * テストする
	 *
	 * @covers ::add_schema
	 * @preserveGlobalState disabled
	 */
	public function test_collection_page_schema() {
		update_post_meta( $this->post_id, '_wpf_schema_type', 'CollectionPage' );

		ob_start();
		WPF_Template_Functions::add_schema();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'CollectionPage', $output );

		// 複数のスクリプトタグを分割
		preg_match_all( '/<script.*?>(.*?)<\/script>/s', $output, $matches );

		// 1つ目のタグをテスト
		$json = preg_replace( '/^<script.*?>|<\/script>$/', '', $matches[1][0] );
		$this->assertJson( $json );

		// 2つ目のタグをテスト
		$json = preg_replace( '/^<script.*?>|<\/script>$/', '', $matches[1][1] );
		$this->assertJson( $json );
	}

	/**
	 * デフォルトのWebPageスキーマの出力を確認する
	 *
	 * 未知のスキーマタイプが指定された場合に
	 * WebPage型が出力されることをテストする
	 *
	 * @covers ::add_schema
	 * @preserveGlobalState disabled
	 */
	public function test_default_web_page_schema() {
		update_post_meta( $this->post_id, '_wpf_schema_type', 'UnknownType' );

		ob_start();
		WPF_Template_Functions::add_schema();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'WebPage', $output );

		// 複数のスクリプトタグを分割
		preg_match_all( '/<script.*?>(.*?)<\/script>/s', $output, $matches );

		// 1つ目のタグをテスト
		$json = preg_replace( '/^<script.*?>|<\/script>$/', '', $matches[1][0] );
		$this->assertJson( $json );

		// 2つ目のタグをテスト
		$json = preg_replace( '/^<script.*?>|<\/script>$/', '', $matches[1][1] );
		$this->assertJson( $json );
	}

	/**
	 * パンくずリストスキーマの出力を確認する
	 *
	 * パンくずリストが正しく処理され、スキーマとして
	 * 出力されることをテストする
	 *
	 * @covers ::add_schema
	 * @preserveGlobalState disabled
	 */
	public function test_breadcrumb_schema() {
		// WPF_Template_Tags::get_the_breadcrumbs の戻り値をモック
		add_filter(
			'wpf_breadcrumbs',
			function() {
				return array(
					array(
						'text' => 'Home',
						'link' => 'https://example.com',
					),
					array(
						'text' => 'Category',
						'link' => 'https://example.com/category',
					),
					array(
						'text' => 'Post',
						'link' => 'https://example.com/category/post',
					),
				);
			}
		);

		// モックを使用してスキーマを出力
		update_post_meta( $this->post_id, '_wpf_schema_type', 'WebPage' );

		ob_start();
		WPF_Template_Functions::add_schema();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'BreadcrumbList', $output );
		$this->assertStringContainsString( 'ListItem', $output );

		// 複数のスクリプトタグを分割
		preg_match_all( '/<script.*?>(.*?)<\/script>/s', $output, $matches );

		// 1つ目のタグをテスト
		$json = preg_replace( '/^<script.*?>|<\/script>$/', '', $matches[1][0] );
		$this->assertJson( $json );

		// 2つ目のタグをテスト
		$json = preg_replace( '/^<script.*?>|<\/script>$/', '', $matches[1][1] );
		$this->assertJson( $json );
	}

	/**
	 * 日本語コンテンツでの出力を確認する
	 *
	 * 日本語のメタデータが正しく処理され、出力されることを
	 * テストする
	 *
	 * @covers ::add_schema
	 * @preserveGlobalState disabled
	 */
	public function test_japanese_content() {
		update_post_meta( $this->post_id, '_wpf_schema_type', 'AboutPage' );
		update_post_meta( $this->post_id, '_wpf_company_addressLocality', '東京都' );
		update_post_meta( $this->post_id, '_wpf_company_addressRegion', '港区' );

		ob_start();
		WPF_Template_Functions::add_schema();
		$output = ob_get_clean();

		$this->assertStringContainsString( '東京都', $output );
		$this->assertStringContainsString( '港区', $output );

		// 複数のスクリプトタグを分割
		preg_match_all( '/<script.*?>(.*?)<\/script>/s', $output, $matches );

		// 1つ目のタグをテスト
		$json = preg_replace( '/^<script.*?>|<\/script>$/', '', $matches[1][0] );
		$this->assertJson( $json );

		// 2つ目のタグをテスト
		$json = preg_replace( '/^<script.*?>|<\/script>$/', '', $matches[1][1] );
		$this->assertJson( $json );
	}

	/**
	 * 著者ページでProfilePageスキーマが使用されることを確認する
	 *
	 * is_author()の場合にProfilePageスキーマが
	 * 正しく出力されることをテストする
	 *
	 * @covers ::add_schema
	 * @preserveGlobalState disabled
	 */
	public function test_author_page_profile_schema() {
		// テスト用の著者を作成
		$author_id = $this->factory->user->create(
			array(
				'user_login'   => 'schemaauthor',
				'display_name' => 'Schema Author',
				'description'  => 'Test author for schema',
				'role'         => 'author',
			)
		);

		// カスタムフィールドを設定
		update_user_meta( $author_id, 'position', 'Schema Developer' );
		update_user_meta( $author_id, 'x', 'schemaauthor' );

		// 著者ページをシミュレート
		global $wp_query;
		$wp_query->is_author         = true;
		$wp_query->queried_object_id = $author_id;

		$this->go_to( get_author_posts_url( $author_id ) );
		$this->assertTrue( is_author() );

		ob_start();
		WPF_Template_Functions::add_schema();
		$output = ob_get_clean();

		// ProfilePageスキーマが使用されることを確認
		$this->assertStringContainsString( 'ProfilePage', $output );
		$this->assertStringContainsString( 'Schema Author', $output );
		$this->assertStringContainsString( 'Person', $output );
		$this->assertStringContainsString( 'Schema Developer', $output );

		// メールアドレスが含まれていないことを確認（セキュリティ）
		$this->assertStringNotContainsString( '@example.org', $output );

		// 複数のスクリプトタグを分割して検証
		preg_match_all( '/<script.*?>(.*?)<\/script>/s', $output, $matches );

		// ProfilePageスキーマのスクリプトタグをテスト
		$profile_page_found = false;
		foreach ( $matches[1] as $json_content ) {
			$data = json_decode( $json_content, true );
			if ( isset( $data['@type'] ) && 'ProfilePage' === $data['@type'] ) {
				$profile_page_found = true;

				// 基本構造の確認
				$this->assertEquals( 'ProfilePage', $data['@type'] );
				$this->assertEquals( 'Schema Author', $data['name'] );
				$this->assertArrayHasKey( 'mainEntity', $data );
				$this->assertEquals( 'Person', $data['mainEntity']['@type'] );

				// @contextの重複がないことを確認
				$this->assertArrayNotHasKey( '@context', $data['mainEntity'] );

				// 日付フィールドが除外されていることを確認
				$this->assertArrayNotHasKey( 'datePublished', $data );
				$this->assertArrayNotHasKey( 'dateModified', $data );

				break;
			}
		}

		$this->assertTrue( $profile_page_found, 'ProfilePage schema was not found in output' );

		// クリーンアップ
		wp_delete_user( $author_id );
		$wp_query->is_author         = false;
		$wp_query->queried_object_id = null;
	}

	/**
	 * 著者ページのメタデータがProfilePageスキーマに正しく反映されることを確認する
	 *
	 * カスタムスキーマタイプが設定された著者ページでの動作を検証する
	 *
	 * @covers ::add_schema
	 * @preserveGlobalState disabled
	 */
	public function test_author_page_with_custom_schema_type() {
		// テスト用の著者を作成
		$author_id = $this->factory->user->create(
			array(
				'user_login'   => 'customauthor',
				'display_name' => 'Custom Author',
				'description'  => 'Custom author description',
			)
		);

		// カスタムフィールドを設定
		update_user_meta( $author_id, 'position', 'Custom Position' );
		update_user_meta( $author_id, 'x', 'customauthor' );
		update_user_meta( $author_id, 'instagram', '@customauthor' );
		update_user_meta( $author_id, 'facebook', 'customauthor' );

		// 著者ページをシミュレート
		global $wp_query;
		$wp_query->is_author         = true;
		$wp_query->queried_object_id = $author_id;

		$this->go_to( get_author_posts_url( $author_id ) );

		ob_start();
		WPF_Template_Functions::add_schema();
		$output = ob_get_clean();

		// ソーシャルメディアプロフィールが正しく含まれていることを確認
		$this->assertStringContainsString( 'https://x.com/customauthor', $output );
		$this->assertStringContainsString( 'https://instagram.com/customauthor', $output );
		$this->assertStringContainsString( 'https://facebook.com/customauthor', $output );

		// 職位情報が含まれていることを確認
		$this->assertStringContainsString( 'Custom Position', $output );

		// JSON構造の確認
		preg_match_all( '/<script.*?>(.*?)<\/script>/s', $output, $matches );

		$profile_page_found = false;
		foreach ( $matches[1] as $json_content ) {
			$data = json_decode( $json_content, true );
			if ( isset( $data['@type'] ) && 'ProfilePage' === $data['@type'] ) {
				$profile_page_found = true;

				// Person エンティティの詳細確認
				$person = $data['mainEntity'];
				$this->assertEquals( 'Custom Author', $person['name'] );
				$this->assertEquals( 'Custom Position', $person['jobTitle'] );
				$this->assertArrayHasKey( 'sameAs', $person );
				$this->assertContains( 'https://x.com/customauthor', $person['sameAs'] );
				$this->assertContains( 'https://instagram.com/customauthor', $person['sameAs'] );
				$this->assertContains( 'https://facebook.com/customauthor', $person['sameAs'] );

				break;
			}
		}

		$this->assertTrue( $profile_page_found, 'ProfilePage schema with custom data was not found' );

		// クリーンアップ
		wp_delete_user( $author_id );
		$wp_query->is_author         = false;
		$wp_query->queried_object_id = null;
	}

	/**
	 * 著者ページで最小限の情報しかない場合の動作を確認する
	 *
	 * 基本情報のみの著者でProfilePageスキーマが
	 * 正しく生成されることをテストする
	 *
	 * @covers ::add_schema
	 * @preserveGlobalState disabled
	 */
	public function test_author_page_minimal_info() {
		// 最小限の情報を持つ著者を作成
		$author_id = $this->factory->user->create(
			array(
				'user_login'   => 'minimalauthor',
				'display_name' => 'Minimal Author',
				// description, position, social媒体は設定しない
			)
		);

		// 著者ページをシミュレート
		global $wp_query;
		$wp_query->is_author         = true;
		$wp_query->queried_object_id = $author_id;

		$this->go_to( get_author_posts_url( $author_id ) );

		ob_start();
		WPF_Template_Functions::add_schema();
		$output = ob_get_clean();

		// 基本的なProfilePageスキーマが出力されることを確認
		$this->assertStringContainsString( 'ProfilePage', $output );
		$this->assertStringContainsString( 'Minimal Author', $output );

		// JSON構造の確認
		preg_match_all( '/<script.*?>(.*?)<\/script>/s', $output, $matches );

		$profile_page_found = false;
		foreach ( $matches[1] as $json_content ) {
			$data = json_decode( $json_content, true );
			if ( isset( $data['@type'] ) && 'ProfilePage' === $data['@type'] ) {
				$profile_page_found = true;

				// 最小限の情報で正しく構成されていることを確認
				$this->assertEquals( 'Minimal Author', $data['name'] );
				$this->assertArrayHasKey( 'mainEntity', $data );
				$this->assertEquals( 'Person', $data['mainEntity']['@type'] );
				$this->assertEquals( 'Minimal Author', $data['mainEntity']['name'] );

				// 設定されていない項目が含まれていないことを確認
				$this->assertArrayNotHasKey( 'description', $data );
				$this->assertArrayNotHasKey( 'jobTitle', $data['mainEntity'] );
				$this->assertArrayNotHasKey( 'worksFor', $data['mainEntity'] );

				break;
			}
		}

		$this->assertTrue( $profile_page_found, 'ProfilePage schema for minimal author was not found' );

		// クリーンアップ
		wp_delete_user( $author_id );
		$wp_query->is_author         = false;
		$wp_query->queried_object_id = null;
	}

	/**
	 * 日本語の著者ページでProfilePageスキーマが正しく動作することを確認する
	 *
	 * 日本語を含む著者情報でのスキーマ生成をテストする
	 *
	 * @covers ::add_schema
	 * @preserveGlobalState disabled
	 */
	public function test_japanese_author_page_schema() {
		// 日本語の著者を作成
		$author_id = $this->factory->user->create(
			array(
				'user_login'   => 'jpauthor',
				'display_name' => '山田太郎',
				'description'  => 'フルスタック開発者として活動しています。',
			)
		);

		// 日本語のカスタムフィールドを設定
		update_user_meta( $author_id, 'position', 'シニアエンジニア' );

		// 著者ページをシミュレート
		global $wp_query;
		$wp_query->is_author         = true;
		$wp_query->queried_object_id = $author_id;

		$this->go_to( get_author_posts_url( $author_id ) );

		ob_start();
		WPF_Template_Functions::add_schema();
		$output = ob_get_clean();

		// 日本語コンテンツが正しく含まれていることを確認
		$this->assertStringContainsString( '山田太郎', $output );
		$this->assertStringContainsString( 'フルスタック開発者として活動しています。', $output );
		$this->assertStringContainsString( 'シニアエンジニア', $output );

		// JSON構造の確認
		preg_match_all( '/<script.*?>(.*?)<\/script>/s', $output, $matches );

		$profile_page_found = false;
		foreach ( $matches[1] as $json_content ) {
			$data = json_decode( $json_content, true );
			if ( isset( $data['@type'] ) && 'ProfilePage' === $data['@type'] ) {
				$profile_page_found = true;

				// 日本語データが正しく設定されていることを確認
				$this->assertEquals( '山田太郎', $data['name'] );
				$this->assertEquals( 'フルスタック開発者として活動しています。', $data['description'] );
				$this->assertEquals( '山田太郎', $data['mainEntity']['name'] );
				$this->assertEquals( 'シニアエンジニア', $data['mainEntity']['jobTitle'] );

				break;
			}
		}

		$this->assertTrue( $profile_page_found, 'Japanese ProfilePage schema was not found' );

		// クリーンアップ
		wp_delete_user( $author_id );
		$wp_query->is_author         = false;
		$wp_query->queried_object_id = null;
	}
}
