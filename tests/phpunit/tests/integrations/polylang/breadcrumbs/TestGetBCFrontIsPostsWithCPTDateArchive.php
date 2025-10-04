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
	class TestGetBCFrontIsPostsWithCPTDateArchive extends PLL_UnitTestCase {

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
			self::$model->options['post_types']    = array(
				'trcpt' => 'trcpt',
			);
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

			register_post_type(
				'trcpt',
				array(
					'public'      => true,
					'has_archive' => true,
					'taxonomies'  => array( 'trtax' ),
					'wpf_cptp'    => array(
						'permalink_structure' => $this->structure,
					),
				)
			);
			register_taxonomy(
				'trtax',
				'trcpt',
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

		public function tear_down() {
			_unregister_post_type( 'trcpt' );
			_unregister_taxonomy( 'trtax' );

			parent::tear_down();
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
		 * CPT年アーカイブ
		 *
		 * @covers ::get_the_breadcrumbs
		 * @preserveGlobalState disabled
		 */
		public function test_get_the_breadcrumbs_with_cpt_year_archive() {
			$this->init_test();

			update_option( 'posts_per_page', 5 );

			/*
			 * 英語
			 */
			$en = 0;
			for ( $i = 0; $i < 9; $i ++ ) {
				$en = self::factory()->post->create(
					array(
						'post_type' => 'trcpt',
						'post_date' => '2012-12-12',
					)
				);
				self::$model->post->set_language( $en, 'en' );
			}

			/*
			 * フランス語
			 */
			$fr = 0;
			for ( $i = 0; $i < 9; $i ++ ) {
				$fr = self::factory()->post->create(
					array(
						'post_type' => 'trcpt',
						'post_date' => '2012-12-12',
					)
				);
				self::$model->post->set_language( $fr, 'fr' );
			}

			/*
			 * ドイツ語
			 */
			$de = 0;
			for ( $i = 0; $i < 9; $i ++ ) {
				$de = self::factory()->post->create(
					array(
						'post_type' => 'trcpt',
						'post_date' => '2012-12-12',
					)
				);
				self::$model->post->set_language( $de, 'de' );
			}

			self::$model->post->save_translations( $en, compact( 'en', 'fr', 'de' ) );

			$this->pll_env->curlang = self::$model->get_language( 'en' );
			$this->go_to( home_url( user_trailingslashit( '/en/trcpt/2012' ) ) );

			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_year' );

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

			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_year', 'is_paged' );

			// 2ページ目以降も変わらないことを確認。
			$this->assertSame(
				$expected,
				WPF_Template_Tags::get_the_breadcrumbs()
			);

			$this->pll_env->curlang = self::$model->get_language( 'fr' );
			$this->go_to( home_url( user_trailingslashit( '/fr/trcpt/2012' ) ) );

			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_year' );

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

			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_year', 'is_paged' );

			// 2ページ目以降も変わらないことを確認。
			$this->assertSame(
				$expected,
				WPF_Template_Tags::get_the_breadcrumbs()
			);

			$this->pll_env->curlang = self::$model->get_language( 'de' );
			$this->go_to( home_url( user_trailingslashit( '/de/trcpt/2012' ) ) );

			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_year' );

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

			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_year', 'is_paged' );

			// 2ページ目以降も変わらないことを確認。
			$this->assertSame(
				$expected,
				WPF_Template_Tags::get_the_breadcrumbs()
			);
		}

		/**
		 * CPT年月アーカイブ
		 *
		 * @covers ::get_the_breadcrumbs
		 * @preserveGlobalState disabled
		 */
		public function test_get_the_breadcrumbs_with_cpt_month_archive() {
			$this->init_test();

			update_option( 'posts_per_page', 5 );

			/*
			 * 英語
			 */
			$en = 0;
			for ( $i = 0; $i < 9; $i ++ ) {
				$en = self::factory()->post->create(
					array(
						'post_type' => 'trcpt',
						'post_date' => '2012-12-12',
					)
				);
				self::$model->post->set_language( $en, 'en' );
			}

			/*
			 * フランス語
			 */
			$fr = 0;
			for ( $i = 0; $i < 9; $i ++ ) {
				$fr = self::factory()->post->create(
					array(
						'post_type' => 'trcpt',
						'post_date' => '2012-12-12',
					)
				);
				self::$model->post->set_language( $fr, 'fr' );
			}

			/*
			 * ドイツ語
			 */
			$de = 0;
			for ( $i = 0; $i < 9; $i ++ ) {
				$de = self::factory()->post->create(
					array(
						'post_type' => 'trcpt',
						'post_date' => '2012-12-12',
					)
				);
				self::$model->post->set_language( $de, 'de' );
			}

			self::$model->post->save_translations( $en, compact( 'en', 'fr', 'de' ) );

			$this->pll_env->curlang = self::$model->get_language( 'en' );
			$this->go_to( home_url( user_trailingslashit( '/en/trcpt/2012/12' ) ) );

			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_month' );

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
					'text'  => '2012',
					'link'  => home_url( user_trailingslashit( '/en/trcpt/2012' ) ),
					'layer' => 'date',
				),
			);

			$this->assertSame(
				$expected,
				WPF_Template_Tags::get_the_breadcrumbs()
			);

			$this->go_to( next_posts( 0, false ) );

			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_month', 'is_paged' );

			// 2ページ目以降も変わらないことを確認。
			$this->assertSame(
				$expected,
				WPF_Template_Tags::get_the_breadcrumbs()
			);

			$this->pll_env->curlang = self::$model->get_language( 'fr' );
			$this->go_to( home_url( user_trailingslashit( '/fr/trcpt/2012/12' ) ) );

			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_month' );

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
					'text'  => '2012',
					'link'  => home_url( user_trailingslashit( '/fr/trcpt/2012' ) ),
					'layer' => 'date',
				),
			);

			$this->assertSame(
				$expected,
				WPF_Template_Tags::get_the_breadcrumbs()
			);

			$this->go_to( next_posts( 0, false ) );

			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_month', 'is_paged' );

			// 2ページ目以降も変わらないことを確認。
			$this->assertSame(
				$expected,
				WPF_Template_Tags::get_the_breadcrumbs()
			);

			$this->pll_env->curlang = self::$model->get_language( 'de' );
			$this->go_to( home_url( user_trailingslashit( '/de/trcpt/2012/12' ) ) );

			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_month' );

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
					'text'  => '2012',
					'link'  => home_url( user_trailingslashit( '/de/trcpt/2012' ) ),
					'layer' => 'date',
				),
			);

			$this->assertSame(
				$expected,
				WPF_Template_Tags::get_the_breadcrumbs()
			);

			$this->go_to( next_posts( 0, false ) );

			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_month', 'is_paged' );

			// 2ページ目以降も変わらないことを確認。
			$this->assertSame(
				$expected,
				WPF_Template_Tags::get_the_breadcrumbs()
			);
		}

		/**
		 * CPT年月日アーカイブ
		 *
		 * @covers ::get_the_breadcrumbs
		 * @preserveGlobalState disabled
		 */
		public function test_get_the_breadcrumbs_with_cpt_day_archive() {
			$this->init_test();

			update_option( 'posts_per_page', 5 );

			/*
			 * 英語
			 */
			$en = 0;
			for ( $i = 0; $i < 9; $i ++ ) {
				$en = self::factory()->post->create(
					array(
						'post_type' => 'trcpt',
						'post_date' => '2012-12-12',
					)
				);
				self::$model->post->set_language( $en, 'en' );
			}

			/*
			 * フランス語
			 */
			$fr = 0;
			for ( $i = 0; $i < 9; $i ++ ) {
				$fr = self::factory()->post->create(
					array(
						'post_type' => 'trcpt',
						'post_date' => '2012-12-12',
					)
				);
				self::$model->post->set_language( $fr, 'fr' );
			}

			/*
			 * ドイツ語
			 */
			$de = 0;
			for ( $i = 0; $i < 9; $i ++ ) {
				$de = self::factory()->post->create(
					array(
						'post_type' => 'trcpt',
						'post_date' => '2012-12-12',
					)
				);
				self::$model->post->set_language( $de, 'de' );
			}

			self::$model->post->save_translations( $en, compact( 'en', 'fr', 'de' ) );

			$this->pll_env->curlang = self::$model->get_language( 'en' );
			$this->go_to( home_url( user_trailingslashit( '/en/trcpt/2012/12/12' ) ) );

			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_day' );

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
					'text'  => '2012',
					'link'  => home_url( user_trailingslashit( '/en/trcpt/2012' ) ),
					'layer' => 'date',
				),
				array(
					'text'  => 'December',
					'link'  => home_url( user_trailingslashit( '/en/trcpt/2012/12' ) ),
					'layer' => 'date',
				),
			);

			$this->assertSame(
				$expected,
				WPF_Template_Tags::get_the_breadcrumbs()
			);

			$this->go_to( next_posts( 0, false ) );

			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_day', 'is_paged' );

			// 2ページ目以降も変わらないことを確認。
			$this->assertSame(
				$expected,
				WPF_Template_Tags::get_the_breadcrumbs()
			);

			$this->pll_env->curlang = self::$model->get_language( 'fr' );
			$this->go_to( home_url( user_trailingslashit( '/fr/trcpt/2012/12/12' ) ) );

			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_day' );

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
					'text'  => '2012',
					'link'  => home_url( user_trailingslashit( '/fr/trcpt/2012' ) ),
					'layer' => 'date',
				),
				array(
					'text'  => 'December',
					'link'  => home_url( user_trailingslashit( '/fr/trcpt/2012/12' ) ),
					'layer' => 'date',
				),
			);

			$this->assertSame(
				$expected,
				WPF_Template_Tags::get_the_breadcrumbs()
			);

			$this->go_to( next_posts( 0, false ) );

			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_day', 'is_paged' );

			// 2ページ目以降も変わらないことを確認。
			$this->assertSame(
				$expected,
				WPF_Template_Tags::get_the_breadcrumbs()
			);

			$this->pll_env->curlang = self::$model->get_language( 'de' );
			$this->go_to( home_url( user_trailingslashit( '/de/trcpt/2012/12/12' ) ) );

			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_day' );

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
					'text'  => '2012',
					'link'  => home_url( user_trailingslashit( '/de/trcpt/2012' ) ),
					'layer' => 'date',
				),
				array(
					'text'  => 'December',
					'link'  => home_url( user_trailingslashit( '/de/trcpt/2012/12' ) ),
					'layer' => 'date',
				),
			);

			$this->assertSame(
				$expected,
				WPF_Template_Tags::get_the_breadcrumbs()
			);

			$this->go_to( next_posts( 0, false ) );

			$this->assertQueryTrue( 'is_archive', 'is_post_type_archive', 'is_date', 'is_day', 'is_paged' );

			// 2ページ目以降も変わらないことを確認。
			$this->assertSame(
				$expected,
				WPF_Template_Tags::get_the_breadcrumbs()
			);
		}
	}
}
