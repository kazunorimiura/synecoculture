<?php

/**
 * WPF_Template_Functions::get_the_seo_tag のユニットテスト。
 *
 * @group template_functions
 * @covers WPF_Template_Functions
 * @coversDefaultClass WPF_Template_Functions
 */
class TestGetTheSEOTags extends WPF_UnitTestCase {
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
	 * @covers ::get_the_seo_tag
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_tag_with_search_result() {
		$post_id = self::factory()->post->create(
			array(
				'post_type'  => 'post',
				'post_title' => 'Foo',
			)
		);

		$this->go_to( home_url( '/?s=Foo' ) );

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'
                <link rel="canonical" href="' . home_url( user_trailingslashit( '/search/Foo' ) ) . '" />
                <meta name="description" content="「Foo」に関する記事を1件掲載しています。" />
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:title" content="Fooの検索結果 &#8211; ' . WP_TESTS_TITLE . '">
                <meta name="twitter:description" content="「Foo」に関する記事を1件掲載しています。">
                <meta name="twitter:image" content="' . self::$ogp_image_object[0] . '">
                <meta property="og:locale" content="' . get_locale() . '" />
                <meta property="og:type" content="website" />
                <meta property="og:site_name" content="' . WP_TESTS_TITLE . '" />
                <meta property="og:title" content="Fooの検索結果 &#8211; ' . WP_TESTS_TITLE . '" />
                <meta property="og:description" content="「Foo」に関する記事を1件掲載しています。" />
                <meta property="og:url" content="' . home_url( user_trailingslashit( '/search/Foo' ) ) . '" />
                <meta property="og:image" content="' . self::$ogp_image_object[0] . '" />
                <meta property="og:image:width" content="' . self::$ogp_image_object[1] . '">
                <meta property="og:image:height" content="' . self::$ogp_image_object[2] . '">
                '
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				WPF_Template_Functions::get_the_seo_tag()
			)
		);
	}

	/**
	 * 投稿アーカイブ
	 *
	 * @covers ::get_the_seo_tag
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_tag_with_post_archive() {
		self::factory()->post->create( array( 'post_type' => 'post' ) );

		$this->go_to( get_post_type_archive_link( 'post' ) );

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'
                <link rel="canonical" href="' . get_post_type_archive_link( 'post' ) . '" />
                <meta name="description" content="' . get_option( 'blogdescription' ) . '" />
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:title" content="' . WP_TESTS_TITLE . ' &#8211; ' . get_option( 'blogdescription' ) . '">
                <meta name="twitter:description" content="' . get_option( 'blogdescription' ) . '">
                <meta name="twitter:image" content="' . self::$ogp_image_object[0] . '">
                <meta property="og:locale" content="' . get_locale() . '" />
                <meta property="og:type" content="website" />
                <meta property="og:site_name" content="' . WP_TESTS_TITLE . '" />
                <meta property="og:title" content="' . WP_TESTS_TITLE . ' &#8211; ' . get_option( 'blogdescription' ) . '" />
                <meta property="og:description" content="' . get_option( 'blogdescription' ) . '" />
                <meta property="og:url" content="' . get_post_type_archive_link( 'post' ) . '" />
                <meta property="og:image" content="' . self::$ogp_image_object[0] . '" />
                <meta property="og:image:width" content="' . self::$ogp_image_object[1] . '">
                <meta property="og:image:height" content="' . self::$ogp_image_object[2] . '">
                '
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				WPF_Template_Functions::get_the_seo_tag()
			)
		);
	}

	/**
	 * CPT投稿アーカイブ
	 *
	 * @covers ::get_the_seo_tag
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_tag_with_cpt_post_archive() {
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

		$post_type_object = get_post_type_object( self::$post_type );

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'
                <link rel="canonical" href="' . get_post_type_archive_link( self::$post_type ) . '" />
                <meta name="description" content="This is cpt description." />
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:title" content="cpt &#8211; ' . WP_TESTS_TITLE . '">
                <meta name="twitter:description" content="This is cpt description.">
                <meta name="twitter:image" content="' . self::$ogp_image_object[0] . '">
                <meta property="og:locale" content="' . get_locale() . '" />
                <meta property="og:type" content="website" />
                <meta property="og:site_name" content="' . WP_TESTS_TITLE . '" />
                <meta property="og:title" content="cpt &#8211; ' . WP_TESTS_TITLE . '" />
                <meta property="og:description" content="This is cpt description." />
                <meta property="og:url" content="' . get_post_type_archive_link( self::$post_type ) . '" />
                <meta property="og:image" content="' . self::$ogp_image_object[0] . '" />
                <meta property="og:image:width" content="' . self::$ogp_image_object[1] . '">
                <meta property="og:image:height" content="' . self::$ogp_image_object[2] . '">
                '
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				WPF_Template_Functions::get_the_seo_tag()
			)
		);
	}

	/**
	 * 日付アーカイブ
	 *
	 * @covers ::get_the_seo_tag
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_tag_with_date_archive() {
		self::factory()->post->create(
			array(
				'post_type' => 'post',
				'post_date' => '2012-12-12',
			)
		);

		$this->go_to( home_url( user_trailingslashit( '/2012/12/12' ) ) );

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'
                <link rel="canonical" href="' . home_url( user_trailingslashit( '/2012/12/12' ) ) . '" />
                <meta name="description" content="2012年12月12日に公開した記事を1件掲載しています。" />
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:title" content="2012年12月12日 &#8211; ' . WP_TESTS_TITLE . '">
                <meta name="twitter:description" content="2012年12月12日に公開した記事を1件掲載しています。">
                <meta name="twitter:image" content="' . self::$ogp_image_object[0] . '">
                <meta property="og:locale" content="' . get_locale() . '" />
                <meta property="og:type" content="website" />
                <meta property="og:site_name" content="' . WP_TESTS_TITLE . '" />
                <meta property="og:title" content="2012年12月12日 &#8211; ' . WP_TESTS_TITLE . '" />
                <meta property="og:description" content="2012年12月12日に公開した記事を1件掲載しています。" />
                <meta property="og:url" content="' . home_url( user_trailingslashit( '/2012/12/12' ) ) . '" />
                <meta property="og:image" content="' . self::$ogp_image_object[0] . '" />
                <meta property="og:image:width" content="' . self::$ogp_image_object[1] . '">
                <meta property="og:image:height" content="' . self::$ogp_image_object[2] . '">
                '
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				WPF_Template_Functions::get_the_seo_tag()
			)
		);
	}

	/**
	 * 404エラー
	 *
	 * @covers ::get_the_seo_tag
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_tag_with_404() {
		$this->go_to( home_url( user_trailingslashit( '/blahblahblah' ) ) );

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'
                <meta name="description" content="' . get_option( 'blogdescription' ) . '" />
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:title" content="Page not found &#8211; ' . WP_TESTS_TITLE . '">
                <meta name="twitter:description" content="' . get_option( 'blogdescription' ) . '">
                <meta name="twitter:image" content="' . self::$ogp_image_object[0] . '">
                <meta property="og:locale" content="' . get_locale() . '" />
                <meta property="og:type" content="website" />
                <meta property="og:site_name" content="' . WP_TESTS_TITLE . '" />
                <meta property="og:title" content="Page not found &#8211; ' . WP_TESTS_TITLE . '" />
                <meta property="og:description" content="' . get_option( 'blogdescription' ) . '" />
                <meta property="og:url" content="" />
                <meta property="og:image" content="' . self::$ogp_image_object[0] . '" />
                <meta property="og:image:width" content="' . self::$ogp_image_object[1] . '">
                <meta property="og:image:height" content="' . self::$ogp_image_object[2] . '">
                '
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				WPF_Template_Functions::get_the_seo_tag()
			)
		);
	}

	/**
	 * フロントページ
	 *
	 * @covers ::get_the_seo_tag
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_tag_with_front_page() {
		self::factory()->post->create( array( 'post_type' => 'post' ) );

		$this->go_to( home_url() );

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'
                <link rel="canonical" href="http://' . WP_TESTS_DOMAIN . '" />
                <meta name="description" content="' . get_option( 'blogdescription' ) . '" />
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:title" content="' . WP_TESTS_TITLE . ' &#8211; ' . get_option( 'blogdescription' ) . '">
                <meta name="twitter:description" content="' . get_option( 'blogdescription' ) . '">
                <meta name="twitter:image" content="' . self::$ogp_image_object[0] . '">
                <meta property="og:locale" content="' . get_locale() . '" />
                <meta property="og:type" content="website" />
                <meta property="og:site_name" content="' . WP_TESTS_TITLE . '" />
                <meta property="og:title" content="' . WP_TESTS_TITLE . ' &#8211; ' . get_option( 'blogdescription' ) . '" />
                <meta property="og:description" content="' . get_option( 'blogdescription' ) . '" />
                <meta property="og:url" content="http://' . WP_TESTS_DOMAIN . '" />
                <meta property="og:image" content="' . self::$ogp_image_object[0] . '" />
                <meta property="og:image:width" content="' . self::$ogp_image_object[1] . '">
                <meta property="og:image:height" content="' . self::$ogp_image_object[2] . '">
                '
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				WPF_Template_Functions::get_the_seo_tag()
			)
		);
	}

	/**
	 * フロントページ: タグラインが空の場合
	 *
	 * @covers ::get_the_seo_tag
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_tag_with_front_page_and_empty_tagline() {
		self::factory()->post->create( array( 'post_type' => 'post' ) );
		update_option( 'blogdescription', '' );

		$this->go_to( home_url() );

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'
                <link rel="canonical" href="http://' . WP_TESTS_DOMAIN . '" />
                <meta name="description" content="" />
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:title" content="' . WP_TESTS_TITLE . '">
                <meta name="twitter:description" content="">
                <meta name="twitter:image" content="' . self::$ogp_image_object[0] . '">
                <meta property="og:locale" content="' . get_locale() . '" />
                <meta property="og:type" content="website" />
                <meta property="og:site_name" content="' . WP_TESTS_TITLE . '" />
                <meta property="og:title" content="' . WP_TESTS_TITLE . '" />
                <meta property="og:description" content="" />
                <meta property="og:url" content="http://' . WP_TESTS_DOMAIN . '" />
                <meta property="og:image" content="' . self::$ogp_image_object[0] . '" />
                <meta property="og:image:width" content="' . self::$ogp_image_object[1] . '">
                <meta property="og:image:height" content="' . self::$ogp_image_object[2] . '">
                '
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				WPF_Template_Functions::get_the_seo_tag()
			)
		);

		update_option( 'blogdescription', self::$blogdescription );
	}

	/**
	 * 個別投稿ページ
	 *
	 * @covers ::get_the_seo_tag
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_tag_with_post() {
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

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'
                <link rel="canonical" href="' . get_permalink( $post_id ) . '" />
                <meta name="description" content="' . get_the_excerpt( $post_id ) . '" />
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:title" content="' . get_the_title( $post_id ) . ' &#8211; ' . WP_TESTS_TITLE . '">
                <meta name="twitter:description" content="' . get_the_excerpt( $post_id ) . '">
                <meta name="twitter:image" content="' . self::$thumbnail_object[0] . '">
                <meta property="og:locale" content="' . get_locale() . '" />
                <meta property="og:type" content="website" />
                <meta property="og:site_name" content="' . WP_TESTS_TITLE . '" />
                <meta property="og:title" content="' . get_the_title( $post_id ) . ' &#8211; ' . WP_TESTS_TITLE . '" />
                <meta property="og:description" content="' . get_the_excerpt( $post_id ) . '" />
                <meta property="og:url" content="' . get_permalink( $post_id ) . '" />
                <meta property="og:image" content="' . self::$thumbnail_object[0] . '" />
                <meta property="og:image:width" content="' . self::$thumbnail_object[1] . '">
                <meta property="og:image:height" content="' . self::$thumbnail_object[2] . '">
                '
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				WPF_Template_Functions::get_the_seo_tag()
			)
		);
	}

	/**
	 * 個別投稿ページ: サムネイル未設定の場合、OGP画像が設定されるか。
	 *
	 * @covers ::get_the_seo_tag
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_tag_with_post_and_no_thumbnail() {
		$post_id = self::factory()->post->create(
			array(
				'post_type'    => 'post',
				'post_title'   => 'The title',
				'post_content' => 'This is content.',
				'post_excerpt' => 'This is excerpt.',
			)
		);

		$this->go_to( get_permalink( $post_id ) );

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'
                <link rel="canonical" href="' . get_permalink( $post_id ) . '" />
                <meta name="description" content="' . get_the_excerpt( $post_id ) . '" />
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:title" content="' . get_the_title( $post_id ) . ' &#8211; ' . WP_TESTS_TITLE . '">
                <meta name="twitter:description" content="' . get_the_excerpt( $post_id ) . '">
                <meta name="twitter:image" content="' . self::$ogp_image_object[0] . '">
                <meta property="og:locale" content="' . get_locale() . '" />
                <meta property="og:type" content="website" />
                <meta property="og:site_name" content="' . WP_TESTS_TITLE . '" />
                <meta property="og:title" content="' . get_the_title( $post_id ) . ' &#8211; ' . WP_TESTS_TITLE . '" />
                <meta property="og:description" content="' . get_the_excerpt( $post_id ) . '" />
                <meta property="og:url" content="' . get_permalink( $post_id ) . '" />
                <meta property="og:image" content="' . self::$ogp_image_object[0] . '" />
                <meta property="og:image:width" content="' . self::$ogp_image_object[1] . '">
                <meta property="og:image:height" content="' . self::$ogp_image_object[2] . '">
                '
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				WPF_Template_Functions::get_the_seo_tag()
			)
		);
	}

	/**
	 * CPT個別投稿ページ
	 *
	 * @covers ::get_the_seo_tag
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_tag_with_cpt_post() {
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

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'
                <link rel="canonical" href="' . get_permalink( $post_id ) . '" />
                <meta name="description" content="' . get_the_excerpt( $post_id ) . '" />
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:title" content="' . get_the_title( $post_id ) . ' &#8211; ' . WP_TESTS_TITLE . '">
                <meta name="twitter:description" content="' . get_the_excerpt( $post_id ) . '">
                <meta name="twitter:image" content="' . self::$thumbnail_object[0] . '">
                <meta property="og:locale" content="' . get_locale() . '" />
                <meta property="og:type" content="website" />
                <meta property="og:site_name" content="' . WP_TESTS_TITLE . '" />
                <meta property="og:title" content="' . get_the_title( $post_id ) . ' &#8211; ' . WP_TESTS_TITLE . '" />
                <meta property="og:description" content="' . get_the_excerpt( $post_id ) . '" />
                <meta property="og:url" content="' . get_permalink( $post_id ) . '" />
                <meta property="og:image" content="' . self::$thumbnail_object[0] . '" />
                <meta property="og:image:width" content="' . self::$thumbnail_object[1] . '">
                <meta property="og:image:height" content="' . self::$thumbnail_object[2] . '">
                '
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				WPF_Template_Functions::get_the_seo_tag()
			)
		);
	}

	/**
	 * CPT個別投稿ページ: サムネイル未設定の場合、OGP画像が設定されるか。
	 *
	 * @covers ::get_the_seo_tag
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_tag_with_cpt_post_and_no_thumbnail() {
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

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'
                <link rel="canonical" href="' . get_permalink( $post_id ) . '" />
                <meta name="description" content="' . get_the_excerpt( $post_id ) . '" />
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:title" content="' . get_the_title( $post_id ) . ' &#8211; ' . WP_TESTS_TITLE . '">
                <meta name="twitter:description" content="' . get_the_excerpt( $post_id ) . '">
                <meta name="twitter:image" content="' . self::$ogp_image_object[0] . '">
                <meta property="og:locale" content="' . get_locale() . '" />
                <meta property="og:type" content="website" />
                <meta property="og:site_name" content="' . WP_TESTS_TITLE . '" />
                <meta property="og:title" content="' . get_the_title( $post_id ) . ' &#8211; ' . WP_TESTS_TITLE . '" />
                <meta property="og:description" content="' . get_the_excerpt( $post_id ) . '" />
                <meta property="og:url" content="' . get_permalink( $post_id ) . '" />
                <meta property="og:image" content="' . self::$ogp_image_object[0] . '" />
                <meta property="og:image:width" content="' . self::$ogp_image_object[1] . '">
                <meta property="og:image:height" content="' . self::$ogp_image_object[2] . '">
                '
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				WPF_Template_Functions::get_the_seo_tag()
			)
		);
	}

	/**
	 * 固定ページ
	 *
	 * @covers ::get_the_seo_tag
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_tag_with_page() {
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

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'
                <link rel="canonical" href="' . get_permalink( $post_id ) . '" />
                <meta name="description" content="' . get_the_excerpt( $post_id ) . '" />
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:title" content="' . get_the_title( $post_id ) . ' &#8211; ' . WP_TESTS_TITLE . '">
                <meta name="twitter:description" content="' . get_the_excerpt( $post_id ) . '">
                <meta name="twitter:image" content="' . self::$thumbnail_object[0] . '">
                <meta property="og:locale" content="' . get_locale() . '" />
                <meta property="og:type" content="website" />
                <meta property="og:site_name" content="' . WP_TESTS_TITLE . '" />
                <meta property="og:title" content="' . get_the_title( $post_id ) . ' &#8211; ' . WP_TESTS_TITLE . '" />
                <meta property="og:description" content="' . get_the_excerpt( $post_id ) . '" />
                <meta property="og:url" content="' . get_permalink( $post_id ) . '" />
                <meta property="og:image" content="' . self::$thumbnail_object[0] . '" />
                <meta property="og:image:width" content="' . self::$thumbnail_object[1] . '">
                <meta property="og:image:height" content="' . self::$thumbnail_object[2] . '">
                '
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				WPF_Template_Functions::get_the_seo_tag()
			)
		);
	}

	/**
	 * 固定ページ: サムネイル未設定の場合、OGP画像が設定されるか。
	 *
	 * @covers ::get_the_seo_tag
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_tag_with_page_no_thumbnail() {
		$post_id = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_title'   => 'The title',
				'post_content' => 'This is content.',
				'post_excerpt' => 'This is excerpt.',
			)
		);

		$this->go_to( get_permalink( $post_id ) );

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'
                <link rel="canonical" href="' . get_permalink( $post_id ) . '" />
                <meta name="description" content="' . get_the_excerpt( $post_id ) . '" />
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:title" content="' . get_the_title( $post_id ) . ' &#8211; ' . WP_TESTS_TITLE . '">
                <meta name="twitter:description" content="' . get_the_excerpt( $post_id ) . '">
                <meta name="twitter:image" content="' . self::$ogp_image_object[0] . '">
                <meta property="og:locale" content="' . get_locale() . '" />
                <meta property="og:type" content="website" />
                <meta property="og:site_name" content="' . WP_TESTS_TITLE . '" />
                <meta property="og:title" content="' . get_the_title( $post_id ) . ' &#8211; ' . WP_TESTS_TITLE . '" />
                <meta property="og:description" content="' . get_the_excerpt( $post_id ) . '" />
                <meta property="og:url" content="' . get_permalink( $post_id ) . '" />
                <meta property="og:image" content="' . self::$ogp_image_object[0] . '" />
                <meta property="og:image:width" content="' . self::$ogp_image_object[1] . '">
                <meta property="og:image:height" content="' . self::$ogp_image_object[2] . '">
                '
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				WPF_Template_Functions::get_the_seo_tag()
			)
		);
	}

	/**
	 * カテゴリアーカイブ
	 *
	 * @covers ::get_the_seo_tag
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_tag_with_category_archive() {
		$cat_id  = self::factory()->category->create();
		$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );
		wp_set_post_categories( $post_id, array( $cat_id ) );

		$this->go_to( get_category_link( $cat_id ) );

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'
                <link rel="canonical" href="' . get_category_link( $cat_id ) . '" />
                <meta name="description" content="' . WPF_Utils::get_the_term_description() . '" />
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:title" content="' . get_cat_name( $cat_id ) . ' &#8211; ' . WP_TESTS_TITLE . '">
                <meta name="twitter:description" content="' . WPF_Utils::get_the_term_description() . '">
                <meta name="twitter:image" content="' . self::$ogp_image_object[0] . '">
                <meta property="og:locale" content="' . get_locale() . '" />
                <meta property="og:type" content="website" />
                <meta property="og:site_name" content="' . WP_TESTS_TITLE . '" />
                <meta property="og:title" content="' . get_cat_name( $cat_id ) . ' &#8211; ' . WP_TESTS_TITLE . '" />
                <meta property="og:description" content="' . WPF_Utils::get_the_term_description() . '" />
                <meta property="og:url" content="' . get_category_link( $cat_id ) . '" />
                <meta property="og:image" content="' . self::$ogp_image_object[0] . '" />
                <meta property="og:image:width" content="' . self::$ogp_image_object[1] . '">
                <meta property="og:image:height" content="' . self::$ogp_image_object[2] . '">
                '
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				WPF_Template_Functions::get_the_seo_tag()
			)
		);
	}

	/**
	 * タグアーカイブ
	 *
	 * @covers ::get_the_seo_tag
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_tag_with_tag_archive() {
		$tag_id  = self::factory()->tag->create();
		$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );
		wp_set_post_tags( $post_id, array( $tag_id ) );

		$this->go_to( get_tag_link( $tag_id ) );

		$tag = get_tag( $tag_id );
		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'
                <link rel="canonical" href="' . get_tag_link( $tag_id ) . '" />
                <meta name="description" content="' . WPF_Utils::get_the_term_description() . '" />
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:title" content="' . $tag->name . ' &#8211; ' . WP_TESTS_TITLE . '">
                <meta name="twitter:description" content="' . WPF_Utils::get_the_term_description() . '">
                <meta name="twitter:image" content="' . self::$ogp_image_object[0] . '">
                <meta property="og:locale" content="' . get_locale() . '" />
                <meta property="og:type" content="website" />
                <meta property="og:site_name" content="' . WP_TESTS_TITLE . '" />
                <meta property="og:title" content="' . $tag->name . ' &#8211; ' . WP_TESTS_TITLE . '" />
                <meta property="og:description" content="' . WPF_Utils::get_the_term_description() . '" />
                <meta property="og:url" content="' . get_tag_link( $tag_id ) . '" />
                <meta property="og:image" content="' . self::$ogp_image_object[0] . '" />
                <meta property="og:image:width" content="' . self::$ogp_image_object[1] . '">
                <meta property="og:image:height" content="' . self::$ogp_image_object[2] . '">
                '
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				WPF_Template_Functions::get_the_seo_tag()
			)
		);
	}

	/**
	 * ctaxアーカイブ
	 *
	 * @covers ::get_the_seo_tag
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_tag_with_ctax_archive() {
		register_taxonomy( self::$taxonomy, self::$post_type );

		$term_id = self::factory()->term->create( array( 'taxonomy' => self::$taxonomy ) );
		$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );
		wp_set_post_terms( $post_id, array( $term_id ), self::$taxonomy );

		$this->go_to( get_term_link( $term_id ) );

		$term = get_term( $term_id );
		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'
                <link rel="canonical" href="' . get_term_link( $term_id ) . '" />
                <meta name="description" content="' . WPF_Utils::get_the_term_description() . '" />
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:title" content="' . $term->name . ' &#8211; ' . WP_TESTS_TITLE . '">
                <meta name="twitter:description" content="' . WPF_Utils::get_the_term_description() . '">
                <meta name="twitter:image" content="' . self::$ogp_image_object[0] . '">
                <meta property="og:locale" content="' . get_locale() . '" />
                <meta property="og:type" content="website" />
                <meta property="og:site_name" content="' . WP_TESTS_TITLE . '" />
                <meta property="og:title" content="' . $term->name . ' &#8211; ' . WP_TESTS_TITLE . '" />
                <meta property="og:description" content="' . WPF_Utils::get_the_term_description() . '" />
                <meta property="og:url" content="' . get_term_link( $term_id ) . '" />
                <meta property="og:image" content="' . self::$ogp_image_object[0] . '" />
                <meta property="og:image:width" content="' . self::$ogp_image_object[1] . '">
                <meta property="og:image:height" content="' . self::$ogp_image_object[2] . '">
                '
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				WPF_Template_Functions::get_the_seo_tag()
			)
		);
	}

	/**
	 * 投稿用ページ
	 *
	 * @covers ::get_the_seo_tag
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_tag_with_page_for_posts() {
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
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'
                <link rel="canonical" href="' . get_permalink( $page_id ) . '" />
                <meta name="description" content="' . get_the_excerpt( $page_id ) . '" />
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:title" content="' . get_the_title( $page_id ) . ' &#8211; ' . WP_TESTS_TITLE . '">
                <meta name="twitter:description" content="' . get_the_excerpt( $page_id ) . '">
                <meta name="twitter:image" content="' . self::$thumbnail_object[0] . '">
                <meta property="og:locale" content="' . get_locale() . '" />
                <meta property="og:type" content="website" />
                <meta property="og:site_name" content="' . WP_TESTS_TITLE . '" />
                <meta property="og:title" content="' . get_the_title( $page_id ) . ' &#8211; ' . WP_TESTS_TITLE . '" />
                <meta property="og:description" content="' . get_the_excerpt( $page_id ) . '" />
                <meta property="og:url" content="' . get_permalink( $page_id ) . '" />
                <meta property="og:image" content="' . self::$thumbnail_object[0] . '" />
                <meta property="og:image:width" content="' . self::$thumbnail_object[1] . '">
                <meta property="og:image:height" content="' . self::$thumbnail_object[2] . '">
                '
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				WPF_Template_Functions::get_the_seo_tag()
			)
		);
	}

	/**
	 * 投稿用ページ: サムネイル未設定の場合、OGP画像が設定されるか
	 *
	 * @covers ::get_the_seo_tag
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_tag_with_page_for_posts_and_no_thumbnail() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_title'   => 'The title',
				'post_excerpt' => 'This is excerpt.',
			)
		);
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $page_id );

		$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );

		$this->go_to( get_permalink( get_option( 'page_for_posts' ) ) );

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'
                <link rel="canonical" href="' . get_permalink( $page_id ) . '" />
                <meta name="description" content="' . get_the_excerpt( $page_id ) . '" />
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:title" content="' . get_the_title( $page_id ) . ' &#8211; ' . WP_TESTS_TITLE . '">
                <meta name="twitter:description" content="' . get_the_excerpt( $page_id ) . '">
                <meta name="twitter:image" content="' . self::$ogp_image_object[0] . '">
                <meta property="og:locale" content="' . get_locale() . '" />
                <meta property="og:type" content="website" />
                <meta property="og:site_name" content="' . WP_TESTS_TITLE . '" />
                <meta property="og:title" content="' . get_the_title( $page_id ) . ' &#8211; ' . WP_TESTS_TITLE . '" />
                <meta property="og:description" content="' . get_the_excerpt( $page_id ) . '" />
                <meta property="og:url" content="' . get_permalink( $page_id ) . '" />
                <meta property="og:image" content="' . self::$ogp_image_object[0] . '" />
                <meta property="og:image:width" content="' . self::$ogp_image_object[1] . '">
                <meta property="og:image:height" content="' . self::$ogp_image_object[2] . '">
                '
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				WPF_Template_Functions::get_the_seo_tag()
			)
		);
	}

	/**
	 * CPT投稿用ページ
	 *
	 * @covers ::get_the_seo_tag
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_tag_with_page_for_cpt_posts() {
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

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'
                <link rel="canonical" href="' . get_permalink( $page_id ) . '" />
                <meta name="description" content="' . get_the_excerpt( $page_id ) . '" />
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:title" content="' . get_the_title( $page_id ) . ' &#8211; ' . WP_TESTS_TITLE . '">
                <meta name="twitter:description" content="' . get_the_excerpt( $page_id ) . '">
                <meta name="twitter:image" content="' . self::$thumbnail_object[0] . '">
                <meta property="og:locale" content="' . get_locale() . '" />
                <meta property="og:type" content="website" />
                <meta property="og:site_name" content="' . WP_TESTS_TITLE . '" />
                <meta property="og:title" content="' . get_the_title( $page_id ) . ' &#8211; ' . WP_TESTS_TITLE . '" />
                <meta property="og:description" content="' . get_the_excerpt( $page_id ) . '" />
                <meta property="og:url" content="' . get_permalink( $page_id ) . '" />
                <meta property="og:image" content="' . self::$thumbnail_object[0] . '" />
                <meta property="og:image:width" content="' . self::$thumbnail_object[1] . '">
                <meta property="og:image:height" content="' . self::$thumbnail_object[2] . '">
                '
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				WPF_Template_Functions::get_the_seo_tag()
			)
		);
	}

	/**
	 * CPT投稿用ページ: サムネイル未設定の場合、OGP画像が設定されるか
	 *
	 * @covers ::get_the_seo_tag
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_seo_tag_with_page_for_cpt_posts_and_no_thumbnail() {
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

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'
                <link rel="canonical" href="' . get_permalink( $page_id ) . '" />
                <meta name="description" content="' . get_the_excerpt( $page_id ) . '" />
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:card" content="summary_large_image">
                <meta name="twitter:title" content="' . get_the_title( $page_id ) . ' &#8211; ' . WP_TESTS_TITLE . '">
                <meta name="twitter:description" content="' . get_the_excerpt( $page_id ) . '">
                <meta name="twitter:image" content="' . self::$ogp_image_object[0] . '">
                <meta property="og:locale" content="' . get_locale() . '" />
                <meta property="og:type" content="website" />
                <meta property="og:site_name" content="' . WP_TESTS_TITLE . '" />
                <meta property="og:title" content="' . get_the_title( $page_id ) . ' &#8211; ' . WP_TESTS_TITLE . '" />
                <meta property="og:description" content="' . get_the_excerpt( $page_id ) . '" />
                <meta property="og:url" content="' . get_permalink( $page_id ) . '" />
                <meta property="og:image" content="' . self::$ogp_image_object[0] . '" />
                <meta property="og:image:width" content="' . self::$ogp_image_object[1] . '">
                <meta property="og:image:height" content="' . self::$ogp_image_object[2] . '">
                '
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				WPF_Template_Functions::get_the_seo_tag()
			)
		);
	}
}
