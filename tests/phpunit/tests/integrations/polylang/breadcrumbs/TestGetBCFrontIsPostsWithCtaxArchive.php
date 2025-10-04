<?php

if ( file_exists( ABSPATH . '/wp-content/plugins/polylang/polylang.php' ) && class_exists( 'PLL_UnitTestCase' ) ) {

	/**
	 * WPF_Template_Tags::get_the_breadcrumbs とPolylangプラグインの統合テスト。
	 *
	 * Inspired by Polylang:
	 * https://github.com/polylang/polylang/blob/ac045d38e7004de09d48161264f693d6abaf80f2/tests/phpunit/tests/test-static-pages.php
	 *
	 * @group template_tags
	 * @covers WPF_Template_Tags
	 * @coversDefaultClass WPF_Template_Tags
	 */
	class TestGetBCFrontIsPostsWithCtaxArchive extends PLL_UnitTestCase {

		public $structure = '/%postname%/';

		/**
		 * Inspired by Polylang:
		 * https://github.com/polylang/polylang/blob/ac045d38e7004de09d48161264f693d6abaf80f2/tests/phpunit/tests/test-static-pages.php#L15
		 *
		 * @param WP_UnitTest_Factory $factory WP_UnitTest_Factory オブジェクト。
		 */
		public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
			parent::wpSetUpBeforeClass( $factory );

			// home_url フィルタにフックしている PLL_Frontend_Filters_Links:home_url では、
			// template_redirect アクションなどを実行済みか判定している。この判定をスキップする。
			$GLOBALS['wp_actions']['template_redirect'] = 1; // phpcs:ignore WordPress.WP.GlobalVariablesOverride

			// home_url フィルタにフックしている PLL_Frontend_Filters_Links:home_url では、
			// カレント言語の home_url を返すホワイトリストに get_theme_root() を指定しているが、
			// この関数は、実行環境のテーマ設定に依存しており、テスト環境ではテーマディレクトリ配下の
			// /tests ディレクトリが返されてしまうため、明示的に許可する。
			add_filter(
				'pll_home_url_white_list',
				function( $white_list ) {
					$white_list = array_merge(
						$white_list,
						array(
							array( 'file' => dirname( get_theme_root() ) ),
						)
					);
					return $white_list;
				}
			);

			self::create_language( 'en_US' );
			self::create_language( 'fr_FR' );
			self::create_language( 'de_DE_formal' );
			self::create_language( 'es_ES' );
		}

		/**
		 * Inspired by Polylang:
		 * https://github.com/polylang/polylang/blob/ac045d38e7004de09d48161264f693d6abaf80f2/tests/phpunit/tests/test-static-pages.php#L66
		 */
		public function set_up() {
			parent::set_up();

			self::$model->options['hide_default']  = 0;
			self::$model->options['redirect_lang'] = 0;
			self::$model->options['taxonomies']    = array(
				'trtax' => 'trtax',
			);

			global $wp_rewrite;

			// switch to pretty permalinks
			$wp_rewrite->init();
			$wp_rewrite->extra_rules_top = array(); // brute force since WP does not do it :(
			$wp_rewrite->set_permalink_structure( $this->structure );

			// 必須taxやtaxパーマリンク構造を初期化する。
			create_initial_taxonomies();

			self::$model->post->register_taxonomy(); // needs this for 'lang' query var

			register_taxonomy(
				'trtax',
				'post',
				array(
					'public'       => true,
					'hierarchical' => true,
					'rewrite'      => array(
						'hierarchical' => true,
					),
				)
			);

			$this->links_model = self::$model->get_links_model();
			$this->links_model->init();

			$wp_rewrite->flush_rules();
		}

		/**
		 * Inspired by Polylang:
		 * https://github.com/polylang/polylang/blob/ac045d38e7004de09d48161264f693d6abaf80f2/tests/phpunit/tests/test-static-pages.php#L86
		 *
		 * @param string $env テスト環境。
		 */
		private function init_test( $env = 'frontend' ) {
			$pll_admin        = new PLL_Admin( $this->links_model );
			$pll_admin->links = new PLL_Admin_Links( $pll_admin );

			update_option( 'show_on_front', 'posts' );

			if ( 'frontend' === $env ) {
				// Go to frontend.
				$this->pll_env = new PLL_Frontend( $this->links_model );
			} else {
				// Go to admin.
				$this->pll_env = $pll_admin;
			}

			$this->pll_env->init();
			$GLOBALS['polylang'] = &$this->pll_env;
		}

		/**
		 * ctaxアーカイブ
		 *
		 * @covers ::get_the_breadcrumbs
		 * @preserveGlobalState disabled
		 */
		public function test_get_the_breadcrumbs_with_ctax_archive() {
			$this->init_test();

			update_option( 'posts_per_page', 5 );

			$term_en = self::factory()->term->create(
				array(
					'taxonomy' => 'trtax',
					'name'     => 'test',
				)
			);
			self::$model->term->set_language( $term_en, 'en' );

			$term_fr = self::factory()->term->create(
				array(
					'taxonomy' => 'trtax',
					'name'     => 'essai',
				)
			);
			self::$model->term->set_language( $term_fr, 'fr' );

			$term_de = self::factory()->term->create(
				array(
					'taxonomy' => 'trtax',
					'name'     => 'prufen',
				)
			);
			self::$model->term->set_language( $term_de, 'de' );

			self::$model->term->save_translations( $term_en, compact( 'term_en', 'term_fr', 'term_de' ) );

			/*
			 * 英語
			 */
			$en = 0;
			for ( $i = 0; $i < 9; $i ++ ) {
				$en = self::factory()->post->create( array( 'post_date' => '2012-12-12' ) );
				self::$model->post->set_language( $en, 'en' );
				wp_set_post_terms( $en, array( $term_en ), 'trtax' );
			}

			/*
			 * フランス語
			 */
			$fr = 0;
			for ( $i = 0; $i < 9; $i ++ ) {
				$fr = self::factory()->post->create( array( 'post_date' => '2012-12-12' ) );
				self::$model->post->set_language( $fr, 'fr' );
				wp_set_post_terms( $fr, array( $term_fr ), 'trtax' );
			}

			/*
			 * ドイツ語
			 */
			$de = 0;
			for ( $i = 0; $i < 9; $i ++ ) {
				$de = self::factory()->post->create( array( 'post_date' => '2012-12-12' ) );
				self::$model->post->set_language( $de, 'de' );
				wp_set_post_terms( $de, array( $term_de ), 'trtax' );
			}

			self::$model->post->save_translations( $en, compact( 'en', 'fr', 'de' ) );

			$this->pll_env->curlang = self::$model->get_language( 'en' );
			$this->go_to( get_term_link( $term_en, 'trtax' ) );

			$this->assertQueryTrue( 'is_archive', 'is_tax' );

			$expected = array(
				array(
					'text'  => 'ホーム',
					'link'  => home_url( '/en' ),
					'layer' => 'home',
				),
				array(
					'text'  => 'Posts',
					'link'  => home_url( '/en' ),
					'layer' => 'post_type',
				),
			);

			$this->assertSame(
				$expected,
				WPF_Template_Tags::get_the_breadcrumbs()
			);

			$this->go_to( next_posts( 0, false ) );

			$this->assertQueryTrue( 'is_archive', 'is_tax', 'is_paged' );

			// 2ページ目以降も変わらないことを確認。
			$this->assertSame(
				$expected,
				WPF_Template_Tags::get_the_breadcrumbs()
			);

			$this->pll_env->curlang = self::$model->get_language( 'fr' );
			$this->go_to( get_term_link( $term_fr, 'trtax' ) );

			$this->assertQueryTrue( 'is_archive', 'is_tax' );

			$expected = array(
				array(
					'text'  => 'ホーム',
					'link'  => home_url( '/fr' ),
					'layer' => 'home',
				),
				array(
					'text'  => 'Posts',
					'link'  => home_url( '/fr' ),
					'layer' => 'post_type',
				),
			);

			$this->assertSame(
				$expected,
				WPF_Template_Tags::get_the_breadcrumbs()
			);

			$this->go_to( next_posts( 0, false ) );

			$this->assertQueryTrue( 'is_archive', 'is_tax', 'is_paged' );

			// 2ページ目以降も変わらないことを確認。
			$this->assertSame(
				$expected,
				WPF_Template_Tags::get_the_breadcrumbs()
			);

			$this->pll_env->curlang = self::$model->get_language( 'de' );
			$this->go_to( get_term_link( $term_de, 'trtax' ) );

			$this->assertQueryTrue( 'is_archive', 'is_tax' );

			$expected = array(
				array(
					'text'  => 'ホーム',
					'link'  => home_url( '/de' ),
					'layer' => 'home',
				),
				array(
					'text'  => 'Posts',
					'link'  => home_url( '/de' ),
					'layer' => 'post_type',
				),
			);

			$this->assertSame(
				$expected,
				WPF_Template_Tags::get_the_breadcrumbs()
			);

			$this->go_to( next_posts( 0, false ) );

			$this->assertQueryTrue( 'is_archive', 'is_tax', 'is_paged' );

			// 2ページ目以降も変わらないことを確認。
			$this->assertSame(
				$expected,
				WPF_Template_Tags::get_the_breadcrumbs()
			);
		}

		/**
		 * ctaxアーカイブ
		 *
		 * テスト条件:
		 * - 親カテゴリあり => 親カテゴリがパンくずに追加されるはず。
		 *
		 * @covers ::get_the_breadcrumbs
		 * @preserveGlobalState disabled
		 */
		public function test_get_the_breadcrumbs_with_ctax_archive_and_has_parent_term() {
			$this->init_test();

			update_option( 'posts_per_page', 5 );

			$term_en = 0;
			$term_fr = 0;
			$term_de = 0;
			for ( $i = 0; $i < 3; $i++ ) {
				$term_en = self::factory()->term->create(
					array(
						'taxonomy' => 'trtax',
						'name'     => 'test_' . $i,
						'parent'   => $term_en,
					)
				);
				self::$model->term->set_language( $term_en, 'en' );

				$term_fr = self::factory()->term->create(
					array(
						'taxonomy' => 'trtax',
						'name'     => 'essai_' . $i,
						'parent'   => $term_fr,
					)
				);
				self::$model->term->set_language( $term_fr, 'fr' );

				$term_de = self::factory()->term->create(
					array(
						'taxonomy' => 'trtax',
						'name'     => 'prufen_' . $i,
						'parent'   => $term_de,
					)
				);
				self::$model->term->set_language( $term_de, 'de' );

				self::$model->term->save_translations( $term_en, compact( 'term_en', 'term_fr', 'term_de' ) );
			}

			/*
			 * 英語
			 */
			$en = 0;
			for ( $i = 0; $i < 9; $i ++ ) {
				$en = self::factory()->post->create( array( 'post_date' => '2012-12-12' ) );
				self::$model->post->set_language( $en, 'en' );
				wp_set_post_terms( $en, array( $term_en ), 'trtax' );
			}

			/*
			 * フランス語
			 */
			$fr = 0;
			for ( $i = 0; $i < 9; $i ++ ) {
				$fr = self::factory()->post->create( array( 'post_date' => '2012-12-12' ) );
				self::$model->post->set_language( $fr, 'fr' );
				wp_set_post_terms( $fr, array( $term_fr ), 'trtax' );
			}

			/*
			 * ドイツ語
			 */
			$de = 0;
			for ( $i = 0; $i < 9; $i ++ ) {
				$de = self::factory()->post->create( array( 'post_date' => '2012-12-12' ) );
				self::$model->post->set_language( $de, 'de' );
				wp_set_post_terms( $de, array( $term_de ), 'trtax' );
			}

			self::$model->post->save_translations( $en, compact( 'en', 'fr', 'de' ) );

			$this->pll_env->curlang = self::$model->get_language( 'en' );
			$this->go_to( get_term_link( $term_en, 'trtax' ) );

			$this->assertQueryTrue( 'is_archive', 'is_tax' );

			$expected = array(
				array(
					'text'  => 'ホーム',
					'link'  => home_url( '/en' ),
					'layer' => 'home',
				),
				array(
					'text'  => 'Posts',
					'link'  => home_url( '/en' ),
					'layer' => 'post_type',
				),
				array(
					'text'  => 'test_0',
					'link'  => home_url( user_trailingslashit( '/en/trtax/test_0' ) ),
					'layer' => 'parent_term',
				),
				array(
					'text'  => 'test_1',
					'link'  => home_url( user_trailingslashit( '/en/trtax/test_0/test_1' ) ),
					'layer' => 'parent_term',
				),
			);

			$this->assertSame(
				$expected,
				WPF_Template_Tags::get_the_breadcrumbs()
			);

			$this->go_to( next_posts( 0, false ) );

			$this->assertQueryTrue( 'is_archive', 'is_tax', 'is_paged' );

			// 2ページ目以降も変わらないことを確認。
			$this->assertSame(
				$expected,
				WPF_Template_Tags::get_the_breadcrumbs()
			);

			$this->pll_env->curlang = self::$model->get_language( 'fr' );
			$this->go_to( get_term_link( $term_fr, 'trtax' ) );

			$this->assertQueryTrue( 'is_archive', 'is_tax' );

			$expected = array(
				array(
					'text'  => 'ホーム',
					'link'  => home_url( '/fr' ),
					'layer' => 'home',
				),
				array(
					'text'  => 'Posts',
					'link'  => home_url( '/fr' ),
					'layer' => 'post_type',
				),
				array(
					'text'  => 'essai_0',
					'link'  => home_url( user_trailingslashit( '/fr/trtax/essai_0' ) ),
					'layer' => 'parent_term',
				),
				array(
					'text'  => 'essai_1',
					'link'  => home_url( user_trailingslashit( '/fr/trtax/essai_0/essai_1' ) ),
					'layer' => 'parent_term',
				),
			);

			$this->assertSame(
				$expected,
				WPF_Template_Tags::get_the_breadcrumbs()
			);

			$this->go_to( next_posts( 0, false ) );

			$this->assertQueryTrue( 'is_archive', 'is_tax', 'is_paged' );

			// 2ページ目以降も変わらないことを確認。
			$this->assertSame(
				$expected,
				WPF_Template_Tags::get_the_breadcrumbs()
			);

			$this->pll_env->curlang = self::$model->get_language( 'de' );
			$this->go_to( get_term_link( $term_de, 'trtax' ) );

			$this->assertQueryTrue( 'is_archive', 'is_tax' );

			$expected = array(
				array(
					'text'  => 'ホーム',
					'link'  => home_url( '/de' ),
					'layer' => 'home',
				),
				array(
					'text'  => 'Posts',
					'link'  => home_url( '/de' ),
					'layer' => 'post_type',
				),
				array(
					'text'  => 'prufen_0',
					'link'  => home_url( user_trailingslashit( '/de/trtax/prufen_0' ) ),
					'layer' => 'parent_term',
				),
				array(
					'text'  => 'prufen_1',
					'link'  => home_url( user_trailingslashit( '/de/trtax/prufen_0/prufen_1' ) ),
					'layer' => 'parent_term',
				),
			);

			$this->assertSame(
				$expected,
				WPF_Template_Tags::get_the_breadcrumbs()
			);

			$this->go_to( next_posts( 0, false ) );

			$this->assertQueryTrue( 'is_archive', 'is_tax', 'is_paged' );

			// 2ページ目以降も変わらないことを確認。
			$this->assertSame(
				$expected,
				WPF_Template_Tags::get_the_breadcrumbs()
			);
		}
	}
}
