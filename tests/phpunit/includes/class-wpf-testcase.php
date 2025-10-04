<?php

/**
 * WordPress Foundation テーマの標準テストのためのテストケースクラス。
 */
class WPF_UnitTestCase extends WP_UnitTestCase {
	use WPF_Test_Utils_Trait;
	use WPF_CPTP_Trait;

	public static $post_type      = 'cpt';
	public static $post_type_slug = 'cpt_slug';
	public static $taxonomy       = 'ctax';
	public static $taxonomy_slug  = 'ctax_slug';

	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		switch_to_locale( 'ja_JP' );
		load_theme_textdomain( 'wordpressfoundation', 'languages' );
		update_option( 'date_format', 'Y年n月j日' );

		self::activate_cptp();
	}

	public static function wpTearDownAfterClass() {
		self::deactivate_cptp();
	}

	public function set_up() {
		parent::set_up();

		global $wp_rewrite;
		$wp_rewrite->init();
		$wp_rewrite->set_permalink_structure( '/%year%/%monthnum%/%day%/%postname%/' );
		create_initial_taxonomies();
		update_option( 'page_comments', true );
		update_option( 'comments_per_page', 5 );
		update_option( 'posts_per_page', 5 );
	}

	public function tear_down() {
		_unregister_post_type( self::$post_type );
		_unregister_taxonomy( self::$taxonomy );

		global $wp_rewrite;
		$wp_rewrite->extra_rules_top = array(); // WPにはできないリセットなのでこちらで。

		parent::tear_down();
	}

	/**
	 * CPTパーマリンク構造のテストデータ。
	 *
	 * @return array
	 */
	public function data_cpt_permastruct() {
		return array(
			array( '/%post_id%/' ),
			array( '/%postname%/' ),
			array( '/%year%/%monthnum%/%day%/%post_id%/' ),
			array( '/%year%/%monthnum%/%day%/%postname%/' ),
			array( '/%author%/%post_id%/' ),
			array( '/%author%/%postname%/' ),
			array( '/%ctax%/%post_id%/' ),
			array( '/%ctax%/%postname%/' ),
			array( '/%category%/%post_id%/' ),
			array( '/%category%/%postname%/' ),
			array( '/%category%/%ctax%/%post_id%/' ),
			array( '/%category%/%ctax%/%postname%/' ),
			array( '/%ctax%/%category%/%post_id%/' ),
			array( '/%ctax%/%category%/%postname%/' ),
			array( '/%ctax%/%author%/%year%/%monthnum%/%day%/%category%/%post_id%/' ),
			array( '/%ctax%/%author%/%year%/%monthnum%/%day%/%category%/%postname%/' ),
		);
	}

	/**
	 * デフォルト投稿タイプのテストコンテンツを作成する。
	 *
	 * @return array テストコンテンツの各種データ。
	 */
	public static function create_test_contents() {
		$user_id = self::factory()->user->create();
		$cat_id  = 0;
		for ( $i = 0; $i < 4; $i ++ ) {
			$cat_id    = self::factory()->category->create(
				array(
					'parent' => $cat_id,
				)
			);
			$cat_ids[] = $cat_id;
		}
		$tag_ids = self::factory()->tag->create_many( 5 );
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id = self::factory()->post->create(
				array(
					'post_type'    => 'post',
					'post_author'  => $user_id,
					'post_date'    => '2012-12-12',
					'post_parent'  => 5 < $i ? $post_id : false,
					'post_content' => 'Page 1 <!--nextpage--> Page 2',
				)
			);

			wp_set_post_categories( $post_id, array( $cat_id ) );
			wp_set_post_tags( $post_id, $tag_ids );
		}
		$attachment_id = self::factory()->attachment->create_object( 'image.jpg', $post_id, array( 'post_mime_type' => 'image/jpeg' ) );

		return array(
			'user_id'       => $user_id,
			'post_id'       => $post_id,
			'cat_id'        => $cat_id,
			'tag_id'        => $tag_ids[0],
			'tag_ids'       => $tag_ids,
			'attachment_id' => $attachment_id,
		);
	}

	/**
	 * CPTのテストコンテンツを作成する。
	 *
	 * cpt, ctaxの登録、テスト投稿の作成など。
	 *
	 * @param string|array $args register_post_type、register_taxonomy の引数。それぞれ 'post_type'、'tax' キーで指定する。オプション。
	 * @return array テストコンテンツの各種データ。
	 */
	public static function create_cpt_test_contents( $args = '' ) {
		$tax_args = array(
			'public'  => true,
			'rewrite' => array(
				'slug' => self::$taxonomy_slug,
			),
		);
		if ( ! empty( $args ) && isset( $args['tax'] ) ) {
			$tax_args = array_replace_recursive( $tax_args, $args['tax'] );
		}
		register_taxonomy( self::$taxonomy, self::$post_type, $tax_args );

		$pt_args = array(
			'public'      => true,
			'has_archive' => true,
			'taxonomies'  => array( 'category', self::$taxonomy ),
			'rewrite'     => array(
				'slug' => self::$post_type_slug,
			),
		);
		if ( ! empty( $args ) && isset( $args['post_type'] ) ) {
			$pt_args = array_replace_recursive( $pt_args, $args['post_type'] );
		}
		register_post_type( self::$post_type, $pt_args );
		register_taxonomy_for_object_type( 'category', self::$post_type );

		global $wp_rewrite;
		$wp_rewrite->flush_rules();

		$user_id = self::factory()->user->create();
		$cat_id  = self::factory()->category->create();
		$tag_id  = self::factory()->tag->create();
		$term_id = 0;
		for ( $i = 0; $i < 4; $i ++ ) {
			$term_id    = self::factory()->term->create(
				array(
					'taxonomy' => self::$taxonomy,
					'parent'   => $term_id,
				)
			);
			$term_ids[] = $term_id;
		}
		$post_id = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			$post_id = self::factory()->post->create(
				array(
					'post_type'    => self::$post_type,
					'post_author'  => $user_id,
					'post_date'    => '2012-12-12',
					'post_parent'  => 5 < $i ? $post_id : false,
					'post_content' => 'Page 1 <!--nextpage--> Page 2',
				)
			);

			wp_set_post_categories( $post_id, array( $cat_id ) );
			wp_set_post_tags( $post_id, array( $tag_id ) );
			wp_set_post_terms( $post_id, array( $term_id ), self::$taxonomy );
		}
		$attachment_id = self::factory()->attachment->create_object( 'image.jpg', $post_id, array( 'post_mime_type' => 'image/jpeg' ) );

		return array(
			'taxonomy'      => self::$taxonomy,
			'post_type'     => self::$post_type,
			'user_id'       => $user_id,
			'post_id'       => $post_id,
			'cat_id'        => $cat_id,
			'tag_id'        => $tag_id,
			'term_id'       => $term_id,
			'term_ids'      => $term_ids,
			'attachment_id' => $attachment_id,
		);
	}
}
