<?php

/**
 * WPF_Template_Tags::get_post_types_with_meta_value, WPF_Template_Tags::has_post_type_with_meta_value のユニットテスト
 *
 * @group template_tags
 * @covers WPF_Template_Tags
 * @coversDefaultClass WPF_Template_Tags
 */
class TestGetPostTypesWithMetaValue extends WPF_UnitTestCase {

	/**
	 * テスト後にクリーンアップを行う
	 */
	public function tear_down() {
		// キャッシュをクリア
		wp_cache_flush();

		parent::tear_down();
	}

	/**
	 * テストデータ作成ヘルパー - 指定したメタ値を持つ投稿を作成
	 *
	 * @param string $meta_key メタキー
	 * @param mixed  $meta_value メタ値
	 * @param string $post_type 投稿タイプ
	 * @param int    $count 作成する投稿数
	 * @return array 作成した投稿ID配列
	 */
	private function create_posts_with_meta( $meta_key, $meta_value, $post_type = 'post', $count = 3 ) {
		$post_ids = array();

		for ( $i = 0; $i < $count; $i++ ) {
			$post_id = self::factory()->post->create(
				array(
					'post_type'   => $post_type,
					'post_title'  => "テスト投稿 {$post_type} {$i} ({$meta_key})",
					'post_status' => 'publish',
				)
			);

			update_post_meta( $post_id, $meta_key, $meta_value );
			$post_ids[] = $post_id;
		}

		return $post_ids;
	}

	/**
	 * 基本的な機能テスト - 有効なメタ値を持つ投稿タイプが正しく取得できることを確認
	 */
	public function test_get_post_types_with_valid_meta_values() {
		$meta_key = 'test_feature_key_' . uniqid();

		// 有効なメタ値を持つ投稿を作成
		$valid_values = array( 'yes', '1', 'true', '有効' );

		// 複数の投稿タイプで投稿を作成
		foreach ( $valid_values as $value ) {
			$this->create_posts_with_meta( $meta_key, $value, 'post', 1 );
			$this->create_posts_with_meta( $meta_key, $value, self::$post_type, 1 );
		}

		// 関数を実行
		$result = WPF_Template_Tags::get_post_types_with_meta_value( $meta_key );

		// 結果の検証
		$this->assertIsArray( $result );
		$this->assertContains( 'post', $result );
		$this->assertContains( self::$post_type, $result );
	}

	/**
	 * 無効なメタ値を持つ投稿が除外されることを確認
	 */
	public function test_exclude_posts_with_invalid_meta_values() {
		$meta_key = 'test_feature_key_' . uniqid();

		// 有効なメタ値を持つ投稿を作成
		$this->create_posts_with_meta( $meta_key, 'yes', 'post', 2 );

		// 無効なメタ値を持つ投稿を作成
		$invalid_values = array( '', '0', 'no', 'false' );

		foreach ( $invalid_values as $value ) {
			$this->create_posts_with_meta( $meta_key, $value, 'page', 1 );
		}

		// 関数を実行
		$result = WPF_Template_Tags::get_post_types_with_meta_value( $meta_key );

		// 有効な投稿タイプだけが含まれていることを確認
		$this->assertIsArray( $result );
		$this->assertContains( 'post', $result );
		$this->assertNotContains( 'page', $result );
	}

	/**
	 * キャッシュが正しく機能しているかテスト
	 */
	public function test_caching() {
		$meta_key   = 'test_cache_key_' . uniqid();
		$cache_time = 600; // 10分

		// キャッシュキーを確認
		$cache_key = 'post_types_with_meta_' . sanitize_key( $meta_key );

		// キャッシュが存在しないことを確認
		$this->assertFalse( wp_cache_get( $cache_key, '_wpf_post_types_with_meta_value' ), 'テスト開始時点でキャッシュが既に存在しています' );

		// テスト用の投稿を作成
		$this->create_posts_with_meta( $meta_key, 'yes', 'post', 3 );

		// 関数を初回実行
		$result1 = WPF_Template_Tags::get_post_types_with_meta_value( $meta_key, $cache_time );

		// キャッシュが保存されたことを確認
		$cached_data = wp_cache_get( $cache_key, '_wpf_post_types_with_meta_value' );
		$this->assertNotFalse( $cached_data, 'データがキャッシュに保存されていません' );

		// キャッシュをクリアして再テスト
		wp_cache_delete( $cache_key, '_wpf_post_types_with_meta_value' );
		$this->assertFalse( wp_cache_get( $cache_key, '_wpf_post_types_with_meta_value' ), 'キャッシュが正しく削除されていません' );

		// 再度関数を実行し、結果が同じであることを確認
		$result2 = WPF_Template_Tags::get_post_types_with_meta_value( $meta_key, $cache_time );

		$this->assertEquals( $result1, $result2, 'キャッシュクリア後、結果が一致しません' );
	}

	/**
	 * 異なるメタキーで正しく動作することを確認
	 */
	public function test_multiple_meta_keys() {
		$meta_key1 = 'test_feature_one_' . uniqid();
		$meta_key2 = 'test_feature_two_' . uniqid();

		// 異なるメタキーを持つ投稿を作成
		$this->create_posts_with_meta( $meta_key1, 'yes', 'post', 3 );
		$this->create_posts_with_meta( $meta_key2, 'yes', 'page', 2 );

		// それぞれのメタキーで関数を実行
		$result1 = WPF_Template_Tags::get_post_types_with_meta_value( $meta_key1 );
		$result2 = WPF_Template_Tags::get_post_types_with_meta_value( $meta_key2 );

		// 結果の検証
		$this->assertIsArray( $result1 );
		$this->assertIsArray( $result2 );

		$this->assertContains( 'post', $result1 );
		$this->assertNotContains( 'page', $result1 );

		$this->assertContains( 'page', $result2 );
		$this->assertNotContains( 'post', $result2 );

		// キャッシュが別々に保存されていることを確認
		$cache_key1 = 'post_types_with_meta_' . sanitize_key( $meta_key1 );
		$cache_key2 = 'post_types_with_meta_' . sanitize_key( $meta_key2 );

		$cache1 = wp_cache_get( $cache_key1, '_wpf_post_types_with_meta_value' );
		$cache2 = wp_cache_get( $cache_key2, '_wpf_post_types_with_meta_value' );

		$this->assertNotFalse( $cache1 );
		$this->assertNotFalse( $cache2 );
		$this->assertNotEquals( $cache1, $cache2 );
	}

	/**
	 * 複数の投稿タイプが正しく検出されることを確認
	 */
	public function test_multiple_post_types() {
		$meta_key = 'test_featured_item_' . uniqid();

		// 異なる投稿タイプで投稿を作成
		$this->create_posts_with_meta( $meta_key, 'yes', 'post', 2 );
		$this->create_posts_with_meta( $meta_key, 'yes', 'page', 2 );
		$this->create_posts_with_meta( $meta_key, 'yes', self::$post_type, 2 );

		// 関数を実行
		$result = WPF_Template_Tags::get_post_types_with_meta_value( $meta_key );

		// 結果の検証
		$this->assertIsArray( $result );
		$this->assertCount( 3, $result ); // 3つの投稿タイプ

		$this->assertContains( 'post', $result );
		$this->assertContains( 'page', $result );
		$this->assertContains( self::$post_type, $result );
	}

	/**
	 * メタキーが存在しない場合のテスト
	 */
	public function test_nonexistent_meta_key() {
		$meta_key = 'nonexistent_meta_key_' . uniqid();

		// 関数を実行
		$result = WPF_Template_Tags::get_post_types_with_meta_value( $meta_key );

		// 結果が空の配列であることを確認
		$this->assertIsArray( $result );
		$this->assertEmpty( $result );
	}

	/**
	 * has_post_type_with_meta_valueメソッドのテスト
	 */
	public function test_has_post_type_with_meta_value() {
		$meta_key = 'test_feature_key_' . uniqid();

		// 「post」タイプの投稿だけを作成
		$this->create_posts_with_meta( $meta_key, 'yes', 'post', 2 );

		// 関数を呼び出し
		$has_post = WPF_Template_Tags::has_post_type_with_meta_value( $meta_key, 'post' );
		$has_page = WPF_Template_Tags::has_post_type_with_meta_value( $meta_key, 'page' );
		$has_cpt  = WPF_Template_Tags::has_post_type_with_meta_value( $meta_key, self::$post_type );

		// アサーション
		$this->assertTrue( $has_post, '「post」タイプを検出できませんでした' );
		$this->assertFalse( $has_page, '「page」タイプが誤って検出されました' );
		$this->assertFalse( $has_cpt, 'CPTタイプが誤って検出されました' );
	}
}
