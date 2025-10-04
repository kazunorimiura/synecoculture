<?php

/**
 * WPF_Template_Functions::get_the_seo_settings のユニットテスト。
 *
 * @group template_functions
 * @covers WPF_Template_Functions
 * @coversDefaultClass WPF_Template_Functions
 */
class TestGetTheSEOSettings extends WPF_UnitTestCase {
	protected static $blogdescription = 'This is tagline.';
	protected static $thumbnail_id;
	protected static $thumbnail_object;
	protected static $ogp_image_id;
	protected static $ogp_image_object;

	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		parent::wpSetUpBeforeClass( $factory );

		self::$thumbnail_id     = $factory->attachment->create_upload_object( DIR_TESTROOT . '/data/images/a2-small.jpg' );
		self::$thumbnail_object = wp_get_attachment_image_src( self::$thumbnail_id, 'full' );

		self::$ogp_image_id = $factory->attachment->create_upload_object( DIR_TESTROOT . '/data/images/canola.jpg' );
		set_theme_mod( 'wpf_ogp_image', self::$ogp_image_id );
		self::$ogp_image_object = wp_get_attachment_image_src( get_theme_mod( 'wpf_ogp_image' ), 'full' );
	}

	public static function wpTearDownAfterClass() {
		set_theme_mod( 'wpf_ogp_image', 0 );
	}

	public function set_up() {
		parent::set_up();

		update_option( 'show_on_front', 'posts' );
		update_option( 'page_on_front', '' );
		update_option( 'page_for_posts', '' );
		update_option( 'blogdescription', self::$blogdescription );

		add_filter( 'document_title_parts', 'WPF_Template_Functions::filter_document_title_parts', 10, 1 );
		add_filter( 'get_canonical_url', 'WPF_Template_Functions::filter_get_canonical_url', 10, 2 );
	}

	public function tear_down() {
		update_option( 'show_on_front', 'posts' );
		update_option( 'page_on_front', '' );
		update_option( 'page_for_posts', '' );
		update_option( 'blogdescription', '' );

		remove_filter( 'document_title_parts', 'WPF_Template_Functions::filter_document_title_parts', 10, 1 );
		remove_filter( 'get_canonical_url', 'WPF_Template_Functions::filter_get_canonical_url', 10, 2 );

		parent::tear_down();
	}

	/**
	 * 検索結果ページ
	 *
	 * 検索スパム対策としてインデックスしない。
	 *
	 * @covers ::get_the_seo_settings
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_settings_with_search_result() {
		$post_id = self::factory()->post->create(
			array(
				'post_type'  => 'post',
				'post_title' => 'Foo',
			)
		);

		$this->go_to( home_url( '/?s=Foo' ) );

		$this->assertSame(
			array(
				'title'       => 'Fooの検索結果 &#8211; ' . WP_TESTS_TITLE,
				'description' => '「Foo」に関する記事を1件掲載しています。',
				'canonical'   => home_url( user_trailingslashit( '/search/Foo' ) ),
				'image_url'   => self::$ogp_image_object[0],
				'image_w'     => self::$ogp_image_object[1],
				'image_h'     => self::$ogp_image_object[2],
				'locale'      => get_locale(),
				'noindex'     => true,
			),
			WPF_Template_Functions::get_the_seo_settings()
		);
	}

	/**
	 * 投稿アーカイブ
	 *
	 * コンテンツではない（アイテムのコレクション）が、ホームページなのでインデックスする。
	 *
	 * @covers ::get_the_seo_settings
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_settings_with_post_archive() {
		self::factory()->post->create( array( 'post_type' => 'post' ) );

		$this->go_to( get_post_type_archive_link( 'post' ) );

		$this->assertSame(
			array(
				'title'       => WP_TESTS_TITLE . ' &#8211; ' . get_option( 'blogdescription' ),
				'description' => get_option( 'blogdescription' ),
				'canonical'   => home_url(),
				'image_url'   => self::$ogp_image_object[0],
				'image_w'     => self::$ogp_image_object[1],
				'image_h'     => self::$ogp_image_object[2],
				'locale'      => get_locale(),
				'noindex'     => false,
			),
			WPF_Template_Functions::get_the_seo_settings()
		);
	}

	/**
	 * CPT投稿アーカイブ
	 *
	 * コンテンツではない（アイテムのコレクション）のでインデックスしない。
	 *
	 * @covers ::get_the_seo_settings
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_settings_with_cpt_post_archive() {
		register_post_type(
			self::$post_type,
			array(
				'labels'      => array(
					'name' => self::$post_type,
				),
				'public'      => true,
				'has_archive' => true,
				'description' => 'This is cpt description.',
			)
		);

		self::factory()->post->create( array( 'post_type' => self::$post_type ) );

		$this->go_to( get_post_type_archive_link( self::$post_type ) );

		$this->assertSame(
			array(
				'title'       => 'cpt &#8211; ' . WP_TESTS_TITLE,
				'description' => 'This is cpt description.',
				'canonical'   => home_url( user_trailingslashit( '/' . self::$post_type ) ),
				'image_url'   => self::$ogp_image_object[0],
				'image_w'     => self::$ogp_image_object[1],
				'image_h'     => self::$ogp_image_object[2],
				'locale'      => get_locale(),
				'noindex'     => true,
			),
			WPF_Template_Functions::get_the_seo_settings()
		);
	}

	/**
	 * 日付アーカイブ
	 *
	 * コンテンツではない（アイテムのコレクション）のでインデックスしない。
	 *
	 * @covers ::get_the_seo_settings
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_settings_with_date_archive() {
		self::factory()->post->create(
			array(
				'post_type' => 'post',
				'post_date' => '2012-12-12',
			)
		);

		$this->go_to( home_url( user_trailingslashit( '/2012/12/12' ) ) );

		$this->assertSame(
			array(
				'title'       => '2012年12月12日 &#8211; ' . WP_TESTS_TITLE,
				'description' => '2012年12月12日に公開した記事を1件掲載しています。',
				'canonical'   => home_url( user_trailingslashit( '/2012/12/12' ) ),
				'image_url'   => self::$ogp_image_object[0],
				'image_w'     => self::$ogp_image_object[1],
				'image_h'     => self::$ogp_image_object[2],
				'locale'      => get_locale(),
				'noindex'     => true,
			),
			WPF_Template_Functions::get_the_seo_settings()
		);
	}

	/**
	 * 404エラー
	 *
	 * @covers ::get_the_seo_settings
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_settings_with_404() {
		$this->go_to( home_url( user_trailingslashit( '/blahblahblah' ) ) );

		$this->assertSame(
			array(
				'title'       => 'Page not found &#8211; ' . WP_TESTS_TITLE,
				'description' => get_option( 'blogdescription' ),
				'canonical'   => false,
				'image_url'   => self::$ogp_image_object[0],
				'image_w'     => self::$ogp_image_object[1],
				'image_h'     => self::$ogp_image_object[2],
				'locale'      => get_locale(),
				'noindex'     => true,
			),
			WPF_Template_Functions::get_the_seo_settings()
		);
	}

	/**
	 * フロントページ
	 *
	 * @covers ::get_the_seo_settings
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_settings_with_front_page() {
		self::factory()->post->create( array( 'post_type' => 'post' ) );

		$this->go_to( home_url() );

		$this->assertSame(
			array(
				'title'       => WP_TESTS_TITLE . ' &#8211; ' . get_option( 'blogdescription' ),
				'description' => get_option( 'blogdescription' ),
				'canonical'   => home_url(),
				'image_url'   => self::$ogp_image_object[0],
				'image_w'     => self::$ogp_image_object[1],
				'image_h'     => self::$ogp_image_object[2],
				'locale'      => get_locale(),
				'noindex'     => false,
			),
			WPF_Template_Functions::get_the_seo_settings()
		);
	}

	/**
	 * フロントページ: タグラインが空の場合
	 *
	 * @covers ::get_the_seo_settings
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_settings_with_front_page_and_empty_tagline() {
		self::factory()->post->create( array( 'post_type' => 'post' ) );
		update_option( 'blogdescription', '' );

		$this->go_to( home_url() );

		$this->assertSame(
			array(
				'title'       => WP_TESTS_TITLE,
				'description' => '',
				'canonical'   => home_url(),
				'image_url'   => self::$ogp_image_object[0],
				'image_w'     => self::$ogp_image_object[1],
				'image_h'     => self::$ogp_image_object[2],
				'locale'      => get_locale(),
				'noindex'     => false,
			),
			WPF_Template_Functions::get_the_seo_settings()
		);

		update_option( 'blogdescription', self::$blogdescription );
	}

	/**
	 * 個別投稿ページ
	 *
	 * @covers ::get_the_seo_settings
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_settings_with_post() {
		$post_id = self::factory()->post->create(
			array(
				'post_type'    => 'post',
				'post_title'   => 'The title',
				'post_content' => 'This is content.',
				'post_excerpt' => 'This is excerpt.',
			)
		);
		set_post_thumbnail( $post_id, self::$thumbnail_id );

		$this->go_to( get_permalink( $post_id ) );

		$post = get_post( $post_id );
		$this->assertSame(
			array(
				'title'       => $post->post_title . ' &#8211; ' . WP_TESTS_TITLE,
				'description' => $post->post_excerpt,
				'canonical'   => get_permalink( $post_id ),
				'image_url'   => self::$thumbnail_object[0],
				'image_w'     => self::$thumbnail_object[1],
				'image_h'     => self::$thumbnail_object[2],
				'locale'      => get_locale(),
				'noindex'     => false,
			),
			WPF_Template_Functions::get_the_seo_settings()
		);
	}

	/**
	 * 個別投稿ページ: サムネイル未設定の場合、OGP画像が設定されるか。
	 *
	 * @covers ::get_the_seo_settings
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_settings_with_post_and_no_thumbnail() {
		$post_id = self::factory()->post->create(
			array(
				'post_type'    => 'post',
				'post_title'   => 'The title',
				'post_content' => 'This is content.',
				'post_excerpt' => 'This is excerpt.',
			)
		);

		$this->go_to( get_permalink( $post_id ) );

		$post = get_post( $post_id );
		$this->assertSame(
			array(
				'title'       => $post->post_title . ' &#8211; ' . WP_TESTS_TITLE,
				'description' => $post->post_excerpt,
				'canonical'   => get_permalink( $post_id ),
				'image_url'   => self::$ogp_image_object[0],
				'image_w'     => self::$ogp_image_object[1],
				'image_h'     => self::$ogp_image_object[2],
				'locale'      => get_locale(),
				'noindex'     => false,
			),
			WPF_Template_Functions::get_the_seo_settings()
		);
	}

	/**
	 * CPT個別投稿ページ
	 *
	 * @covers ::get_the_seo_settings
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_settings_with_cpt_post() {
		register_post_type( self::$post_type, array( 'public' => true ) );

		$post_id = self::factory()->post->create(
			array(
				'post_type'    => self::$post_type,
				'post_title'   => 'The title',
				'post_content' => 'This is content.',
				'post_excerpt' => 'This is excerpt.',
			)
		);
		set_post_thumbnail( $post_id, self::$thumbnail_id );

		$this->go_to( get_permalink( $post_id ) );

		$post = get_post( $post_id );
		$this->assertSame(
			array(
				'title'       => $post->post_title . ' &#8211; ' . WP_TESTS_TITLE,
				'description' => $post->post_excerpt,
				'canonical'   => get_permalink( $post_id ),
				'image_url'   => self::$thumbnail_object[0],
				'image_w'     => self::$thumbnail_object[1],
				'image_h'     => self::$thumbnail_object[2],
				'locale'      => get_locale(),
				'noindex'     => false,
			),
			WPF_Template_Functions::get_the_seo_settings()
		);
	}

	/**
	 * CPT個別投稿ページ: サムネイル未設定の場合、OGP画像が設定されるか。
	 *
	 * @covers ::get_the_seo_settings
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_settings_with_cpt_post_and_no_thumbnail() {
		register_post_type( self::$post_type, array( 'public' => true ) );

		$post_id = self::factory()->post->create(
			array(
				'post_type'    => self::$post_type,
				'post_title'   => 'The title',
				'post_content' => 'This is content.',
				'post_excerpt' => 'This is excerpt.',
			)
		);

		$this->go_to( get_permalink( $post_id ) );

		$post = get_post( $post_id );
		$this->assertSame(
			array(
				'title'       => $post->post_title . ' &#8211; ' . WP_TESTS_TITLE,
				'description' => $post->post_excerpt,
				'canonical'   => get_permalink( $post_id ),
				'image_url'   => self::$ogp_image_object[0],
				'image_w'     => self::$ogp_image_object[1],
				'image_h'     => self::$ogp_image_object[2],
				'locale'      => get_locale(),
				'noindex'     => false,
			),
			WPF_Template_Functions::get_the_seo_settings()
		);
	}

	/**
	 * 固定ページ
	 *
	 * @covers ::get_the_seo_settings
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_settings_with_page() {
		$post_id = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_title'   => 'The title',
				'post_content' => 'This is content.',
				'post_excerpt' => 'This is excerpt.',
			)
		);
		set_post_thumbnail( $post_id, self::$thumbnail_id );

		$this->go_to( get_permalink( $post_id ) );

		$post = get_post( $post_id );
		$this->assertSame(
			array(
				'title'       => $post->post_title . ' &#8211; ' . WP_TESTS_TITLE,
				'description' => $post->post_excerpt,
				'canonical'   => get_permalink( $post_id ),
				'image_url'   => self::$thumbnail_object[0],
				'image_w'     => self::$thumbnail_object[1],
				'image_h'     => self::$thumbnail_object[2],
				'locale'      => get_locale(),
				'noindex'     => false,
			),
			WPF_Template_Functions::get_the_seo_settings()
		);
	}

	/**
	 * 固定ページ: サムネイル未設定の場合、OGP画像が設定されるか。
	 *
	 * @covers ::get_the_seo_settings
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_settings_with_page_no_thumbnail() {
		$post_id = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_title'   => 'The title',
				'post_content' => 'This is content.',
				'post_excerpt' => 'This is excerpt.',
			)
		);

		$this->go_to( get_permalink( $post_id ) );

		$post = get_post( $post_id );
		$this->assertSame(
			array(
				'title'       => $post->post_title . ' &#8211; ' . WP_TESTS_TITLE,
				'description' => $post->post_excerpt,
				'canonical'   => get_permalink( $post_id ),
				'image_url'   => self::$ogp_image_object[0],
				'image_w'     => self::$ogp_image_object[1],
				'image_h'     => self::$ogp_image_object[2],
				'locale'      => get_locale(),
				'noindex'     => false,
			),
			WPF_Template_Functions::get_the_seo_settings()
		);
	}

	/**
	 * カテゴリアーカイブ
	 *
	 * コンテンツではない（アイテムのコレクション）が、SEO評価の高い
	 * コンテンツをまとめるページとして検索順位が上がると流入インパク
	 * トがでかいのでインデックスする。
	 *
	 * @covers ::get_the_seo_settings
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_settings_with_category_archive() {
		$cat_id  = self::factory()->category->create();
		$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );
		wp_set_post_categories( $post_id, array( $cat_id ) );

		$this->go_to( get_category_link( $cat_id ) );

		$this->assertSame(
			array(
				'title'       => get_cat_name( $cat_id ) . ' &#8211; ' . WP_TESTS_TITLE,
				'description' => WPF_Utils::get_the_term_description(),
				'canonical'   => get_category_link( $cat_id ),
				'image_url'   => self::$ogp_image_object[0],
				'image_w'     => self::$ogp_image_object[1],
				'image_h'     => self::$ogp_image_object[2],
				'locale'      => get_locale(),
				'noindex'     => false,
			),
			WPF_Template_Functions::get_the_seo_settings()
		);
	}

	/**
	 * タグアーカイブ
	 *
	 * コンテンツではない（アイテムのコレクション）が、SEO評価の高い
	 * コンテンツをまとめるページとして検索順位が上がると流入インパク
	 * トがでかいのでインデックスする。
	 *
	 * @covers ::get_the_seo_settings
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_settings_with_tag_archive() {
		$tag_id  = self::factory()->tag->create();
		$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );
		wp_set_post_tags( $post_id, array( $tag_id ) );

		$this->go_to( get_tag_link( $tag_id ) );

		$tag = get_tag( $tag_id );
		$this->assertSame(
			array(
				'title'       => $tag->name . ' &#8211; ' . WP_TESTS_TITLE,
				'description' => WPF_Utils::get_the_term_description(),
				'canonical'   => get_tag_link( $tag_id ),
				'image_url'   => self::$ogp_image_object[0],
				'image_w'     => self::$ogp_image_object[1],
				'image_h'     => self::$ogp_image_object[2],
				'locale'      => get_locale(),
				'noindex'     => false,
			),
			WPF_Template_Functions::get_the_seo_settings()
		);
	}

	/**
	 * ctaxアーカイブ
	 *
	 * コンテンツではない（アイテムのコレクション）が、SEO評価の高い
	 * コンテンツをまとめるページとして検索順位が上がると流入インパク
	 * トがでかいのでインデックスする。
	 *
	 * @covers ::get_the_seo_settings
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_settings_with_ctax_archive() {
		register_taxonomy( self::$taxonomy, self::$post_type );

		$term_id = self::factory()->term->create( array( 'taxonomy' => self::$taxonomy ) );
		$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );
		wp_set_post_terms( $post_id, array( $term_id ), self::$taxonomy );

		$this->go_to( get_term_link( $term_id ) );

		$term = get_term( $term_id );
		$this->assertSame(
			array(
				'title'       => $term->name . ' &#8211; ' . WP_TESTS_TITLE,
				'description' => WPF_Utils::get_the_term_description(),
				'canonical'   => get_term_link( $term_id ),
				'image_url'   => self::$ogp_image_object[0],
				'image_w'     => self::$ogp_image_object[1],
				'image_h'     => self::$ogp_image_object[2],
				'locale'      => get_locale(),
				'noindex'     => false,
			),
			WPF_Template_Functions::get_the_seo_settings()
		);
	}

	/**
	 * 投稿用ページ
	 *
	 * @covers ::get_the_seo_settings
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_settings_with_page_for_posts() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_title'   => 'The title',
				'post_excerpt' => 'This is excerpt.',
			)
		);
		set_post_thumbnail( $page_id, self::$thumbnail_id );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $page_id );

		$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );

		$this->go_to( get_permalink( get_option( 'page_for_posts' ) ) );

		$this->assertSame(
			array(
				'title'       => 'The title &#8211; ' . WP_TESTS_TITLE,
				'description' => 'This is excerpt.',
				'canonical'   => home_url( user_trailingslashit( '/the-title' ) ),
				'image_url'   => self::$thumbnail_object[0],
				'image_w'     => self::$thumbnail_object[1],
				'image_h'     => self::$thumbnail_object[2],
				'locale'      => get_locale(),
				'noindex'     => false,
			),
			WPF_Template_Functions::get_the_seo_settings()
		);
	}

	/**
	 * 投稿用ページ: サムネイル未設定の場合、OGP画像が設定されるか
	 *
	 * @covers ::get_the_seo_settings
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_settings_with_page_for_posts_and_no_thumbnail() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_title'   => 'The title',
				'post_excerpt' => 'This is excerpt.',
			)
		);
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $page_id );

		self::factory()->post->create( array( 'post_type' => 'post' ) );

		$this->go_to( get_permalink( get_option( 'page_for_posts' ) ) );

		$this->assertSame(
			array(
				'title'       => 'The title &#8211; ' . WP_TESTS_TITLE,
				'description' => 'This is excerpt.',
				'canonical'   => home_url( user_trailingslashit( '/the-title' ) ),
				'image_url'   => self::$ogp_image_object[0],
				'image_w'     => self::$ogp_image_object[1],
				'image_h'     => self::$ogp_image_object[2],
				'locale'      => get_locale(),
				'noindex'     => false,
			),
			WPF_Template_Functions::get_the_seo_settings()
		);
	}

	/**
	 * CPT投稿用ページ
	 *
	 * @covers ::get_the_seo_settings
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_settings_with_page_for_cpt_posts() {
		register_post_type( self::$post_type, array( 'public' => true ) );

		$page_id = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_name'    => self::$post_type,
				'post_title'   => 'The title',
				'post_content' => 'This is content.',
				'post_excerpt' => 'This is excerpt.',
			)
		);
		set_post_thumbnail( $page_id, self::$thumbnail_id );

		$this->go_to( get_permalink( $page_id ) );

		$post = get_post( $page_id );
		$this->assertSame(
			array(
				'title'       => $post->post_title . ' &#8211; ' . WP_TESTS_TITLE,
				'description' => $post->post_excerpt,
				'canonical'   => home_url( user_trailingslashit( '/' . self::$post_type ) ),
				'image_url'   => self::$thumbnail_object[0],
				'image_w'     => self::$thumbnail_object[1],
				'image_h'     => self::$thumbnail_object[2],
				'locale'      => get_locale(),
				'noindex'     => false,
			),
			WPF_Template_Functions::get_the_seo_settings()
		);
	}

	/**
	 * CPT投稿用ページ: サムネイル未設定の場合、OGP画像が設定されるか
	 *
	 * @covers ::get_the_seo_settings
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_settings_with_page_for_cpt_posts_and_no_thumbnail() {
		register_post_type( self::$post_type, array( 'public' => true ) );

		$page_id = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_name'    => self::$post_type,
				'post_title'   => 'The title',
				'post_content' => 'This is content.',
				'post_excerpt' => 'This is excerpt.',
			)
		);

		$this->go_to( get_permalink( $page_id ) );

		$post = get_post( $page_id );
		$this->assertSame(
			array(
				'title'       => $post->post_title . ' &#8211; ' . WP_TESTS_TITLE,
				'description' => $post->post_excerpt,
				'canonical'   => home_url( user_trailingslashit( '/' . self::$post_type ) ),
				'image_url'   => self::$ogp_image_object[0],
				'image_w'     => self::$ogp_image_object[1],
				'image_h'     => self::$ogp_image_object[2],
				'locale'      => get_locale(),
				'noindex'     => false,
			),
			WPF_Template_Functions::get_the_seo_settings()
		);
	}
}
