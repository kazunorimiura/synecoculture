<?php

if ( file_exists( ABSPATH . '/wp-content/plugins/polylang/polylang.php' ) && class_exists( 'PLL_UnitTestCase' ) ) {

	/**
	 * WPF_Template_Tags::pll_register_strings とPolylangプラグインの統合テスト。
	 *
	 * Inspired by Polylang:
	 * https://github.com/polylang/polylang/blob/ac045d38e7004de09d48161264f693d6abaf80f2/tests/phpunit/tests/test-static-pages.php
	 *
	 * @group template_tags
	 * @covers WPF_Template_Tags
	 * @coversDefaultClass WPF_Template_Tags
	 */
	class TestCPTAttachmentQuery extends PLL_UnitTestCase {

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
					'wpf_cptp'    => array(
						'permalink_structure' => $this->structure,
					),
				)
			);

			$this->links_model = self::$model->get_links_model();
			$this->links_model->init();

			$wp_rewrite->flush_rules();
		}

		public function tear_down() {
			_unregister_post_type( 'trcpt' );

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

				new PLL_Filters_Links( $this->pll_env ); // _get_page_link などのフィルタフックが含まれる。
			} else {
				// Go to admin.
				$this->pll_env = $pll_admin;
			}

			$this->pll_env->init();
			$GLOBALS['polylang'] = &$this->pll_env;
		}

		/**
		 * CPT投稿を親に持つ添付ファイルページのクエリをテスト。
		 *
		 * Inspired by Polylang:
		 * https://github.com/polylang/polylang/blob/ac045d38e7004de09d48161264f693d6abaf80f2/tests/phpunit/tests/test-query.php#L507
		 *
		 * @covers ::pll_register_strings
		 * @preserveGlobalState disabled
		 */
		public function test_cpt_attachment() {
			$this->init_test();

			$post_en = self::factory()->post->create(
				array(
					'post_type'  => 'trcpt',
					'post_title' => 'test',
				)
			);
			self::$model->post->set_language( $post_en, 'en' );

			$post_fr = self::factory()->post->create(
				array(
					'post_type'  => 'trcpt',
					'post_title' => 'essai',
				)
			);
			self::$model->post->set_language( $post_fr, 'fr' );

			self::$model->post->save_translations( $post_en, compact( 'post_en', 'post_fr' ) );

			$en = self::factory()->post->create(
				array(
					'post_title'  => 'img_en',
					'post_type'   => 'attachment',
					'post_parent' => $post_en,
				)
			);
			self::$model->post->set_language( $en, 'en' );

			$fr = self::factory()->post->create(
				array(
					'post_title'  => 'img_fr',
					'post_type'   => 'attachment',
					'post_parent' => $post_fr,
				)
			);
			self::$model->post->set_language( $fr, 'fr' );

			self::$model->post->save_translations( $en, compact( 'en', 'fr' ) );

			$test = self::factory()->post->create(
				array(
					'post_title' => 'img',
					'post_type'  => 'attachment',
				)
			);
			$test = self::factory()->attachment->create_object( 'image.jpg', 0, array( 'post_mime_type' => 'image/jpeg' ) );

			$this->go_to( get_attachment_link( $fr ) );

			$this->assertQueryTrue( 'is_attachment', 'is_singular', 'is_single' );
			$this->assertEquals( array( get_post( $fr ) ), $GLOBALS['wp_query']->posts );
			$this->assertEquals( home_url( '/en/trcpt/test/img_en/' ), $this->pll_env->links->get_translation_url( self::$model->get_language( 'en' ) ) );
		}
	}
}
