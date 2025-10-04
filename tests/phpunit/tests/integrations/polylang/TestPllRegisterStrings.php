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
	class TestPllRegisterStrings extends PLL_UnitTestCase {

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

			self::create_language( 'ja' );
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
					'labels'      => array(
						'name' => 'trcpt',
					),
					'public'      => true,
					'description' => 'this is description.',
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
		 * 「テーマ」グループの文字列翻訳が正しく登録されているか。
		 *
		 * @covers ::pll_register_strings
		 * @preserveGlobalState disabled
		 */
		public function test_pll_register_strings_with_theme_group() {
			$this->init_test( 'admin' );

			WPF_Polylang_Functions::pll_register_strings();

			$registered_strings = PLL_Admin_Strings::get_strings();

			foreach ( $registered_strings as $string ) {
				$names[] = $string['name'];
			}

			$this->assertTrue( in_array( 'wpf_search_form_title', $names, true ) );
			$this->assertTrue( in_array( 'wpf_404_title', $names, true ) );
			$this->assertTrue( in_array( 'wpf_search_results_description', $names, true ) );
			$this->assertTrue( in_array( 'wpf_date_archive_description', $names, true ) );
			$this->assertTrue( in_array( 'wpf_prev_text', $names, true ) );
			$this->assertTrue( in_array( 'wpf_next_text', $names, true ) );
			$this->assertTrue( in_array( 'wpf_comment_text', $names, true ) );
			$this->assertTrue( in_array( 'wpf_leave_a_comment_text', $names, true ) );
			$this->assertTrue( in_array( 'wpf_comment_is_closed_text', $names, true ) );
			$this->assertTrue( in_array( 'wpf_terms', $names, true ) );
			$this->assertTrue( in_array( 'wpf_authors', $names, true ) );
			$this->assertTrue( in_array( 'wpf_cta_text_to_archive', $names, true ) );
			$this->assertTrue( in_array( 'wpf_cta_text_to_author_page', $names, true ) );
		}

		/**
		 * 「投稿タイプ」グループの文字列翻訳が正しく登録されているか。
		 *
		 * @covers ::pll_register_strings
		 * @preserveGlobalState disabled
		 */
		public function test_pll_register_strings_with_post_type_group() {
			$this->init_test( 'admin' );

			WPF_Polylang_Functions::pll_register_strings();

			$registered_strings = PLL_Admin_Strings::get_strings();

			foreach ( $registered_strings as $string ) {
				$names[] = $string['name'];
			}

			$this->assertTrue( in_array( 'wpf_post_label_name', $names, true ) );
			$this->assertTrue( in_array( 'wpf_page_label_name', $names, true ) );
			$this->assertTrue( in_array( 'wpf_attachment_label_name', $names, true ) );
			$this->assertTrue( in_array( 'wpf_trcpt_label_name', $names, true ) );
			$this->assertTrue( in_array( 'wpf_trcpt_description', $names, true ) );
		}

		/**
		 * 「ユーザー」グループの文字列翻訳が正しく登録されているか。
		 *
		 * @covers ::pll_register_strings
		 * @preserveGlobalState disabled
		 */
		public function test_pll_register_strings_with_user_group() {
			$this->init_test( 'admin' );

			$users = self::factory()->user->create_many( 10 );

			foreach ( $users as $user ) {
				update_user_meta( $user, 'position', 'foo' );
			}

			WPF_Polylang_Functions::pll_register_strings();

			$registered_strings = PLL_Admin_Strings::get_strings();

			foreach ( $registered_strings as $string ) {
				$names[] = $string['name'];
			}

			$this->assertTrue( ! empty( preg_grep( '/wpf_.*_display_name/', $names ) ) );
			$this->assertTrue( ! empty( preg_grep( '/wpf_.*_position/', $names ) ) );
		}
	}
}
