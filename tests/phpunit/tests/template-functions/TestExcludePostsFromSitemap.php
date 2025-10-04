<?php

/**
 * WPF_Template_Functions::exclude_posts_from_sitemap のユニットテスト
 *
 * @group template_functions
 * @covers WPF_Template_Functions
 * @coversDefaultClass WPF_Template_Functions
 */
class TestExcludePostsFromSitemap extends WPF_UnitTestCase {

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
	 * _wpf_hide_search_engineフラグがある場合に投稿が除外されることをテスト
	 */
	public function test_exclude_posts_with_hide_flag() {
		$meta_key = '_wpf_hide_search_engine';

		// 表示する投稿と非表示にする投稿を作成
		$visible_post_ids = $this->create_posts_with_meta( $meta_key, '0', 'post', 2 );
		$hidden_post_ids  = $this->create_posts_with_meta( $meta_key, '1', 'post', 2 );

		// メタキーがない通常の投稿も作成
		$normal_post_ids = self::factory()->post->create_many( 2 );

		// 全ての投稿IDリスト
		$all_post_ids = array_merge( $visible_post_ids, $hidden_post_ids, $normal_post_ids );

		// 初期クエリ引数
		$args = array(
			'post_type'      => 'post',
			'posts_per_page' => -1,
			'post__in'       => $all_post_ids,
			'orderby'        => 'ID',
			'order'          => 'ASC',
		);

		// フィルターを適用
		$filtered_args = WPF_Template_Functions::exclude_posts_from_sitemap( $args, 'post' );

		// フィルターが適用されたかどうかは実際にクエリを実行して確認
		$query          = new WP_Query( $filtered_args );
		$found_post_ids = wp_list_pluck( $query->posts, 'ID' );

		// 非表示に設定された投稿が取得されないことを確認（フィルターが機能していれば）
		foreach ( $hidden_post_ids as $hidden_id ) {
			$this->assertNotContains(
				$hidden_id,
				$found_post_ids,
				"非表示に設定された投稿（ID: {$hidden_id}）が結果に含まれています"
			);
		}

		// 通常の投稿と表示する投稿は結果に含まれることを確認
		foreach ( $visible_post_ids as $visible_id ) {
			$this->assertContains(
				$visible_id,
				$found_post_ids,
				"表示に設定された投稿（ID: {$visible_id}）が結果から除外されています"
			);
		}

		foreach ( $normal_post_ids as $normal_id ) {
			$this->assertContains(
				$normal_id,
				$found_post_ids,
				"通常の投稿（ID: {$normal_id}）が結果から除外されています"
			);
		}
	}

	/**
	 * 既存のメタクエリがある場合でも機能することをテスト
	 */
	public function test_meta_query_added_to_existing_meta_query() {
		$meta_key = '_wpf_hide_search_engine';

		// 表示する投稿と非表示にする投稿を作成
		$visible_post_ids = $this->create_posts_with_meta( $meta_key, '0', 'post', 2 );
		$hidden_post_ids  = $this->create_posts_with_meta( $meta_key, '1', 'post', 2 );

		// 別のメタキーを持つ投稿も作成
		$other_meta_key    = 'test_meta_key';
		$featured_post_ids = $this->create_posts_with_meta( $other_meta_key, 'featured', 'post', 2 );
		$normal_post_ids   = $this->create_posts_with_meta( $other_meta_key, 'normal', 'post', 2 );

		// 全ての投稿IDリスト
		$all_post_ids = array_merge( $visible_post_ids, $hidden_post_ids, $featured_post_ids, $normal_post_ids );

		// 既存のメタクエリを含む初期クエリ引数
		$args = array(
			'post_type'      => 'post',
			'posts_per_page' => -1,
			'post__in'       => $all_post_ids,
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => $other_meta_key,
					'value'   => 'featured',
					'compare' => '=',
				),
			),
		);

		// フィルターを適用
		$filtered_args = WPF_Template_Functions::exclude_posts_from_sitemap( $args, 'post' );

		// クエリを実行
		$query          = new WP_Query( $filtered_args );
		$found_post_ids = wp_list_pluck( $query->posts, 'ID' );

		// featured属性の投稿しか含まれないことを確認（元のメタクエリが機能）
		foreach ( $featured_post_ids as $featured_id ) {
			if ( ! in_array( $featured_id, $hidden_post_ids, true ) ) {
				$this->assertContains(
					$featured_id,
					$found_post_ids,
					"featured属性の表示可能な投稿（ID: {$featured_id}）が結果から除外されています"
				);
			}
		}

		// normal属性の投稿は含まれないことを確認（元のメタクエリが機能）
		foreach ( $normal_post_ids as $normal_id ) {
			$this->assertNotContains(
				$normal_id,
				$found_post_ids,
				"normal属性の投稿（ID: {$normal_id}）が誤って結果に含まれています"
			);
		}

		// 非表示に設定された投稿は含まれないことを確認（新しいメタクエリが機能）
		foreach ( $hidden_post_ids as $hidden_id ) {
			$this->assertNotContains(
				$hidden_id,
				$found_post_ids,
				"非表示に設定された投稿（ID: {$hidden_id}）が結果に含まれています"
			);
		}
	}

	/**
	 * メタキーをサポートしていない投稿タイプではメタクエリが追加されないことをテスト
	 */
	public function test_meta_query_not_added_for_unsupported_post_type() {
		$meta_key = '_wpf_hide_search_engine';

		// 投稿を作成（postタイプのみメタキーを持つ）
		$post_ids = $this->create_posts_with_meta( $meta_key, '1', 'post', 2 );
		$page_ids = self::factory()->post->create_many( 2, array( 'post_type' => 'page' ) );

		// pageタイプ用のクエリ引数
		$args = array(
			'post_type'      => 'page',
			'posts_per_page' => -1,
		);

		// フィルター適用前のクエリ引数をコピー
		$original_args = $args;

		// フィルターを適用
		$filtered_args = WPF_Template_Functions::exclude_posts_from_sitemap( $args, 'page' );

		// pageタイプの場合、メタクエリが追加されないことを確認
		$this->assertEquals( $original_args, $filtered_args, 'サポートされていない投稿タイプにメタクエリが追加されました' );
	}
}
