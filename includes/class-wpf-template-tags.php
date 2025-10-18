<?php
/**
 * テーマのためのテンプレートタグ（高レベルなHTML出力関数）を定義
 *
 * @package wordpressfoundation
 */

/**
 * テーマのためのテンプレートタグ（高レベルなHTML出力関数）を定義する。
 *
 * @since 0.1.0
 */
class WPF_Template_Tags {

	/**
	 * コンストラクタ
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
	}

	/**
	 * ページタイトルを返す。
	 *
	 * 背景:
	 * 投稿用ページを設定している投稿タイプなら、そのタイトルを優先したい。
	 * また、検索結果や著者ページ、404など、存在するすべてのページを一つの
	 * 高レベルな関数で賄えるようにして、テンプレートのコードを簡潔にしたい。
	 *
	 * @since 0.1.0
	 * @return string
	 */
	public static function get_the_page_title() {
		// 個別投稿ページ・固定ページの場合
		if ( is_singular() ) {
			$title = get_the_title();

			// その他のページの場合
		} else {
			// get_the_archive_title の接頭辞を削除する。
			add_filter( 'get_the_archive_title_prefix', '__return_false' );

			$title = get_the_archive_title();

			// ホームページの場合
			if ( is_front_page() ) {
				$page_for_posts = WPF_Utils::get_page_for_posts();

				if ( $page_for_posts ) {
					$title = get_the_title( $page_for_posts );
				}

				// デフォルト投稿アーカイブ、日付アーカイブの場合
			} elseif ( is_home() ) {
				$page_for_posts = WPF_Utils::get_page_for_posts();

				if ( $page_for_posts ) {
					$title = get_the_title( $page_for_posts );
				}

				// CPT投稿アーカイブ、CPT日付アーカイブの場合
			} elseif ( ( is_post_type_archive() && ! is_date() ) ) {
				$page_for_posts = WPF_Utils::get_page_for_posts();

				if ( $page_for_posts ) {
					$title = get_the_title( $page_for_posts );
				}

				// 検索結果ページの場合
			} elseif ( is_search() ) {
				$title = function_exists( 'pll__' ) ? pll__( 'Search' ) : __( 'Search', 'wordpressfoundation' );

				// 404ページの場合
			} elseif ( is_404() ) {
				$title = function_exists( 'pll__' ) ? pll__( 'Page not found' ) : __( 'Page not found', 'wordpressfoundation' );
			}
		}

		return $title;
	}

	/**
	 * ページのサブタイトルを取得する。
	 *
	 * 背景:
	 * 投稿用ページを設定している投稿タイプなら、そのサブタイトルを優先したい。
	 * また、検索結果や著者ページ、404など、存在するすべてのページを一つの高レ
	 * ベルな関数で賄えるようにして、テンプレートのコードを簡潔にしたい。
	 *
	 * @since 0.1.0
	 * @return string
	 */
	public static function get_the_page_subtitle() {
		$subtitle = '';

		// 個別投稿・固定ページの場合
		if ( is_singular() ) {
			$subtitle = get_post_meta( get_the_ID(), '_wpf_subtitle', true );
		} else {
			// ホームページの場合
			if ( is_front_page() ) {
				if ( get_option( 'page_on_front' ) ) {
					$subtitle = get_post_meta( get_option( 'page_on_front' ), '_wpf_subtitle', true );
				}

				// デフォルト投稿アーカイブ、日付アーカイブの場合
			} elseif ( is_home() || ( is_date() && ! is_post_type_archive() ) ) {
				if ( get_option( 'page_for_posts' ) ) {
					$subtitle = get_post_meta( get_option( 'page_for_posts' ), '_wpf_subtitle', true );
				}

				// カテゴリアーカイブの場合
			} elseif ( is_category() ) {
				$subtitle = WPF_Utils::get_the_term_description();

				// タグアーカイブの場合
			} elseif ( is_tag() ) {
				$page_for_posts = WPF_Utils::get_page_for_posts();

				if ( $page_for_posts ) {
					$subtitle = get_post_meta( $page_for_posts, '_wpf_subtitle', true );
				} else {
					$subtitle = WPF_Utils::get_the_term_description();
				}

				// CPT投稿アーカイブ、CPT日付アーカイブの場合
			} elseif ( ( is_post_type_archive() && ! is_date() ) || ( is_post_type_archive() && is_date() ) ) {
				$page_for_posts = WPF_Utils::get_page_for_posts();

				if ( $page_for_posts ) {
					$subtitle = get_post_meta( $page_for_posts, '_wpf_subtitle', true );
				}

				// ctaxアーカイブの場合
			} elseif ( is_tax() ) {
				$subtitle = WPF_Utils::get_the_term_description();

				if ( ! is_taxonomy_hierarchical( get_queried_object()->taxonomy ) ) {
					$page_for_posts = WPF_Utils::get_page_for_posts();

					if ( $page_for_posts ) {
						$subtitle = get_post_meta( $page_for_posts, '_wpf_subtitle', true );
					}
				}
			} elseif ( is_404() ) {
				$subtitle = __( 'お探しのページは見つかりませんでした。', 'wordpressfoundation' );
			}
		}

		return $subtitle;
	}

	/**
	 * 投稿タイプの表示名を返す。
	 *
	 * 背景:
	 * パンくずリストには、カテゴリやタグなどのサブアーカイブでも所属する投稿タイプの表示名が必要だった。
	 * ::get_the_page_title で代用しようとも思ったが、カテゴリアーカイブではカテゴリ名を使うなど、
	 * クエリのタイプによっては必ずしも投稿タイプ名が返されるわけではないため、専用の関数を実装した。
	 *
	 * @since 0.1.0
	 * @return string 固定ページで管理している場合は固定ページのタイトル、そうでない場合は投稿タイプのラベル名を返す。
	 */
	public static function get_the_post_type_display_name() {
		$post_type_display_name = '';

		$page_for_posts = WPF_Utils::get_page_for_posts();

		if ( $page_for_posts ) {
			$post_type_display_name = get_the_title( $page_for_posts );
		} else {
			$post_type = WPF_Utils::get_post_type();

			if ( ! empty( $post_type ) ) {
				$post_type_object       = get_post_type_object( $post_type );
				$post_type_display_name = function_exists( 'pll__' ) ? pll__( $post_type_object->labels->name ) : $post_type_object->labels->name;
			}
		}

		return $post_type_display_name;
	}

	/**
	 * 年アーカイブのパーマリンクを返す。
	 *
	 * 組み込みの get_year_link との違いは、CPTに対応している点。
	 * なお、このテーマのCPTP実装に依存しているので注意。
	 *
	 * @see WPF_CPT_Rewrite::get_year_permastruct CPT年アーカイブのパーマリンク構造を返す。
	 * @see WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag CPTアーカイブのリライトタグを返す。
	 * @see https://developer.wordpress.org/reference/functions/get_year_link/ 組み込みの get_year_link ドキュメント。
	 *
	 * @since 0.1.0
	 * @param int|false $year 年の整数。現在の年を取得する場合は false を渡す。
	 * @param string    $post_type オプション。対象の投稿タイプ。デフォルトは `post`。
	 * @return string 指定された年のアーカイブのパーマリンク。
	 */
	public static function get_year_link( $year, $post_type = 'post' ) {
		global $wp_rewrite;

		if ( ! $year ) {
			$year = current_time( 'Y' );
		}

		if ( 'post' === $post_type ) {
			$yearlink = $wp_rewrite->get_year_permastruct();
		} else {
			$yearlink = WPF_CPT_Rewrite::get_year_permastruct( $post_type );
		}

		if ( ! empty( $yearlink ) ) {
			$yearlink = str_replace( '%year%', $year, $yearlink );

			if ( 'post' !== $post_type ) {
				$post_type_object = get_post_type_object( $post_type );

				if ( ! (bool) $post_type_object ) {
					return new WP_Error( 'post_type_error', __( 'Post type does not exist.', 'wordpressfoundation' ), $post_type );
				}

				$cpt_archive_slug        = $post_type_object->rewrite['slug'];
				$cpt_archive_rewrite_tag = WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $post_type );
				$yearlink                = str_replace( $cpt_archive_rewrite_tag, $cpt_archive_slug, $yearlink );
			}

			$yearlink = home_url( user_trailingslashit( $yearlink, 'year' ) );

			if ( function_exists( 'PLL' ) ) {
				$pll      = PLL();
				$yearlink = $pll->links_model->switch_language_in_link( $yearlink, $pll->curlang );
			}
		} else {
			if ( 'post' === $post_type ) {
				$yearlink = home_url( '?m=' . $year );
			} else {
				$yearlink = home_url( '?m=' . $year . '&post_type=' . $post_type );
			}

			if ( function_exists( 'pll_current_language' ) ) {
				$yearlink = $yearlink . '&lang=' . pll_current_language();
			}
		}

		return $yearlink;
	}

	/**
	 * 年月アーカイブのパーマリンクを返す。
	 *
	 * 組み込みの get_month_link との違いは、CPTに対応している点。
	 * なお、このテーマのCPTP実装に依存しているので注意。
	 *
	 * @see WPF_CPT_Rewrite::get_month_permastruct CPT年月アーカイブのパーマリンク構造を返す。
	 * @see WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag CPTアーカイブのリライトタグを返す。
	 * @see https://developer.wordpress.org/reference/functions/get_month_link/ 組み込みの get_month_link ドキュメント。
	 *
	 * @since 0.1.0
	 * @param int|false $year 年の整数。現在の年を取得する場合は false を渡す。
	 * @param int|false $month 月の整数。現在の月を取得する場合は false を渡す。
	 * @param string    $post_type オプション。対象の投稿タイプ。デフォルトは `post`。
	 * @return string 指定された年のアーカイブのパーマリンク。
	 */
	public static function get_month_link( $year, $month, $post_type = 'post' ) {
		global $wp_rewrite;

		if ( ! $year ) {
			$year = current_time( 'Y' );
		}
		if ( ! $month ) {
			$month = current_time( 'm' );
		}

		if ( 'post' === $post_type ) {
			$monthlink = $wp_rewrite->get_month_permastruct();
		} else {
			$monthlink = WPF_CPT_Rewrite::get_month_permastruct( $post_type );
		}

		if ( ! empty( $monthlink ) ) {
			$monthlink = str_replace( '%year%', $year, $monthlink );
			$monthlink = str_replace( '%monthnum%', zeroise( (int) $month, 2 ), $monthlink );

			if ( 'post' !== $post_type ) {
				$post_type_object = get_post_type_object( $post_type );

				if ( ! (bool) $post_type_object ) {
					return new WP_Error( 'post_type_error', __( 'Post type does not exist.', 'wordpressfoundation' ), $post_type );
				}

				$cpt_archive_slug        = $post_type_object->rewrite['slug'];
				$cpt_archive_rewrite_tag = WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $post_type );
				$monthlink               = str_replace( $cpt_archive_rewrite_tag, $cpt_archive_slug, $monthlink );
			}

			$monthlink = home_url( user_trailingslashit( $monthlink, 'month' ) );

			if ( function_exists( 'PLL' ) ) {
				$pll       = PLL();
				$monthlink = $pll->links_model->switch_language_in_link( $monthlink, $pll->curlang );
			}
		} else {
			if ( 'post' === $post_type ) {
				$monthlink = home_url( '?m=' . $year . zeroise( $month, 2 ) );
			} else {
				$monthlink = home_url( '?m=' . $year . zeroise( $month, 2 ) . '&post_type=' . $post_type );
			}

			if ( function_exists( 'pll_current_language' ) ) {
				$monthlink = $monthlink . '&lang=' . pll_current_language();
			}
		}

		return $monthlink;
	}

	/**
	 * 年月日アーカイブのパーマリンクを返す。
	 *
	 * 組み込みの get_day_link との違いは、CPTに対応している点。
	 * なお、このテーマのCPTP実装に依存しているので注意。
	 *
	 * @see WPF_CPT_Rewrite::get_date_permastruct CPT日アーカイブのパーマリンク構造を返す。
	 * @see WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag CPTアーカイブのリライトタグを返す。
	 * @see https://developer.wordpress.org/reference/functions/get_day_link/ 組み込みの get_day_link ドキュメント。
	 *
	 * @since 0.1.0
	 * @param int|false $year 年の整数。現在の年を取得する場合は false を渡す。
	 * @param int|false $month 月の整数。現在の月を取得する場合は false を渡す。
	 * @param int|false $day 日の整数。現在の日を取得する場合は false を渡す。
	 * @param string    $post_type オプション。対象の投稿タイプ。デフォルトは `post`。
	 * @return string|WP_Error 指定された日のアーカイブのパーマリンク。投稿タイプが存在しない場合は WP_Error。
	 */
	public static function get_day_link( $year, $month, $day, $post_type = 'post' ) {
		global $wp_rewrite;

		if ( ! $year ) {
			$year = current_time( 'Y' );
		}
		if ( ! $month ) {
			$month = current_time( 'm' );
		}
		if ( ! $day ) {
			$day = current_time( 'd' );
		}

		if ( 'post' === $post_type ) {
			$daylink = $wp_rewrite->get_date_permastruct();
		} else {
			$daylink = WPF_CPT_Rewrite::get_date_permastruct( $post_type );
		}

		if ( ! empty( $daylink ) ) {
			$daylink = str_replace( '%year%', $year, $daylink );
			$daylink = str_replace( '%monthnum%', zeroise( (int) $month, 2 ), $daylink );
			$daylink = str_replace( '%day%', zeroise( (int) $day, 2 ), $daylink );

			if ( 'post' !== $post_type ) {
				$post_type_object = get_post_type_object( $post_type );

				if ( ! (bool) $post_type_object ) {
					return new WP_Error( 'post_type_error', __( 'Post type does not exist.', 'wordpressfoundation' ), $post_type );
				}

				$cpt_archive_slug        = $post_type_object->rewrite['slug'];
				$cpt_archive_rewrite_tag = WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $post_type );
				$daylink                 = str_replace( $cpt_archive_rewrite_tag, $cpt_archive_slug, $daylink );
			}

			$daylink = home_url( user_trailingslashit( $daylink, 'day' ) );

			if ( function_exists( 'PLL' ) ) {
				$pll     = PLL();
				$daylink = $pll->links_model->switch_language_in_link( $daylink, $pll->curlang );
			}
		} else {
			$date_query = $year . zeroise( $month, 2 ) . zeroise( $day, 2 );

			if ( 'post' === $post_type ) {
				$daylink = home_url( '?m=' . $date_query );
			} else {
				$daylink = home_url( '?m=' . $date_query . '&post_type=' . $post_type );
			}

			if ( function_exists( 'pll_current_language' ) ) {
				$daylink = $daylink . '&lang=' . pll_current_language();
			}
		}

		return $daylink;
	}

	/**
	 * ポリランを考慮して get_post_type_archive_link の値を返す。
	 *
	 * 背景:
	 * ポリランの home_url に対するフックが get_post_type_archive_link 内の
	 * get_home_url に対してのみ効いていない。理由はわからないが、このテーマでフ
	 * ックしなおしても、フックの優先順位を変えても、うまくいかなかった。
	 * 試しに get_post_type_archive_link をコピペして使ったらうまく動いたので
	 * get_post_type_archive_link の get_home_url フォールバックを再現する
	 * ことで対処することにした。
	 *
	 * @param string $post_type 取得対象の投稿タイプ。
	 * @return string 投稿用ページが設定されていたら get_post_type_archive_link を、なければ get_home_url を返す。
	 */
	public static function get_post_type_archive_link( $post_type ) {
		$post_type_archive_link = get_post_type_archive_link( $post_type );

		if ( function_exists( 'PLL' ) ) {
			$post_type_archive_link = WPF_Utils::get_page_for_posts() ? get_post_type_archive_link( $post_type ) : get_home_url();
		}

		return $post_type_archive_link;
	}

	/**
	 * 現在のクエリに基づきパンくずリストを取得
	 *
	 * @since 0.1.0
	 * @param string $context 使用コンテキスト 'display' または 'schema'
	 * @return array[]|null テキスト、リンク、レイヤー種類の配列の配列
	 */
	public static function get_the_breadcrumbs( $context = 'schema' ) {
		$queried_object = get_queried_object();

		// フロントページまたはホームページの表示が「投稿」の場合のホームページまたは404ページの場合はパンくずリストを表示しない
		if ( is_front_page() || ( 'post' === get_option( 'show_on_front' ) && is_home() ) || is_404() ) {
			return;
		}

		$breadcrumbs = array();

		/*
		 * ホームレイヤーをパンくずに追加する。
		 */
		$home_text = get_option( 'page_on_front' ) ? get_the_title( get_option( 'page_on_front' ) ) : __( 'ホーム', 'wordpressfoundation' );
		if ( 'display' === $context ) {
			$home_text = '<span class="screen-reader-text">' . $home_text . '</span><span class="home-icon">' . WPF_Icons::get_svg( 'ui', 'home', 24 ) . '</span>';
		}

		$home_layer = array(
			'text'  => $home_text,
			'link'  => get_home_url(),
			'layer' => 'home',
		);
		array_push( $breadcrumbs, $home_layer );

		$post_type = WPF_Utils::get_post_type();

		/**
		 * 固定ページまたは著者ページでなければ、投稿タイプレイヤーをパンくずに追加する。
		 * なお、投稿用ページが設定されている場合はそれを優先する。
		 */
		if ( ! is_page() && ! is_author() ) {
			$page_for_posts  = WPF_Utils::get_page_for_posts();
			$post_type_layer = array(
				'text'  => self::get_the_post_type_display_name(),
				'link'  => $page_for_posts ? get_permalink( $page_for_posts ) : get_post_type_archive_link( $post_type ),
				'layer' => 'post_type',
			);
			array_push( $breadcrumbs, $post_type_layer );
		}

		/*
		 * 年アーカイブの場合、現在の年を追加する。
		 */
		if ( is_year() ) {
			$yearnum = get_the_date( 'Y' );
			$year    = get_the_date( _x( 'Y年', 'year date format', 'wordpressfoundation' ) );

			// 現在の年を追加
			$current_year_layer = array(
				'text'  => (string) $year,
				'link'  => self::get_year_link( $yearnum, $post_type ),
				'layer' => 'current_date',
			);
			array_push( $breadcrumbs, $current_year_layer );
		}

		/*
		 * 年月アーカイブなら、年パーマリンクをパンくずに追加し、現在の月も追加する。
		 */
		if ( is_month() ) {
			$yearnum  = get_the_date( 'Y' );
			$monthnum = get_the_date( 'm' );

			// 現在の年を追加
			$year       = get_the_date( _x( 'Y年', 'year date format', 'wordpressfoundation' ) );
			$date_layer = array(
				'text'  => (string) $year,
				'link'  => self::get_year_link( $yearnum, $post_type ),
				'layer' => 'date',
			);
			array_push( $breadcrumbs, $date_layer );

			// 現在の月を追加
			$month               = get_the_date( _x( 'Y年n月', 'monthly archives date format', 'wordpressfoundation' ) );
			$current_month_layer = array(
				'text'  => (string) $month,
				'link'  => self::get_month_link( $yearnum, $monthnum, $post_type ),
				'layer' => 'current_date',
			);
			array_push( $breadcrumbs, $current_month_layer );
		}

		/*
		 * 年月日アーカイブなら、年月パーマリンクをパンくずに追加し、現在の日も追加する。
		 */
		if ( is_day() ) {
			$yearnum  = get_the_date( 'Y' );
			$monthnum = get_the_date( 'm' );
			$daynum   = get_the_date( 'd' );

			// 現在の年を追加
			$year       = get_the_date( _x( 'Y年', 'year date format', 'wordpressfoundation' ) );
			$date_layer = array(
				'text'  => (string) $year,
				'link'  => self::get_year_link( $yearnum, $post_type ),
				'layer' => 'date',
			);
			array_push( $breadcrumbs, $date_layer );

			// 現在の月を追加
			$month      = get_the_date( _x( 'Y年n月', 'monthly archives date format', 'wordpressfoundation' ) );
			$date_layer = array(
				'text'  => (string) $month,
				'link'  => self::get_month_link( $yearnum, $monthnum, $post_type ),
				'layer' => 'date',
			);
			array_push( $breadcrumbs, $date_layer );

			// 現在の日を追加
			$day               = get_the_date( _x( 'Y年n月j日', 'daily archives date format', 'wordpressfoundation' ) );
			$current_day_layer = array(
				'text'  => (string) $day,
				'link'  => self::get_day_link( $yearnum, $monthnum, $daynum, $post_type ),
				'layer' => 'current_date',
			);
			array_push( $breadcrumbs, $current_day_layer );
		}

		/*
		 * タクソノミーアーカイブなら、親タームをパンくずに追加し、現在のタームも追加する。
		 */
		if ( is_category() || is_tag() || is_tax() ) {
			// ヒエラルキーがある場合、親タームを追加
			if ( 0 !== $queried_object->parent ) {
				$taxonomy = $queried_object->taxonomy;
				$term_id  = $queried_object->term_id;

				$parents = get_ancestors( $term_id, $taxonomy, 'taxonomy' );

				foreach ( array_reverse( $parents ) as $term_id ) {
					$parent = get_term( $term_id, $taxonomy );

					$parent_term_layer = array(
						'text'  => $parent->name,
						'link'  => get_term_link( $parent->term_id, $taxonomy ),
						'layer' => 'parent_term',
					);
					array_push( $breadcrumbs, $parent_term_layer );
				}
			}

			// 現在のタームを追加
			$current_term_layer = array(
				'text'  => $queried_object->name,
				'link'  => get_term_link( $queried_object->term_id, $queried_object->taxonomy ),
				'layer' => 'current_term',
			);
			array_push( $breadcrumbs, $current_term_layer );
		}

		/**
		 * 著者ページレイヤーを追加
		 */
		if ( is_author() ) {
			$author_id    = (int) get_query_var( 'author' );
			$display_name = function_exists( 'pll__' ) ? pll__( get_the_author_meta( 'display_name', $author_id ), 'wordpressfoundation' ) : get_the_author_meta( 'display_name', $author_id );
			$author_link  = get_author_posts_url( $author_id );

			$post_type_layer = array(
				'text'  => $display_name,
				'link'  => $author_link,
				'layer' => 'author',
			);
			array_push( $breadcrumbs, $post_type_layer );
		}

		/*
		 * 個別投稿・固定ページなら、tax・親投稿レイヤーをパンくずに追加する。
		 */
		if ( is_singular() ) {
			$taxonomies = WPF_Utils::get_object_public_taxonomies( $post_type );

			if ( ! empty( $taxonomies ) ) {
				// 選択されているタームが複数taxにまたがる場合は、最初に登録されたtaxをメインとみなす。
				$main_tax = $taxonomies[0];

				// publicly_queryableがfalseの場合（taxアーカイブを無効にしている場合）はスルー
				$main_tax_obj = get_taxonomy( $main_tax );
				if ( $main_tax_obj && $main_tax_obj->publicly_queryable ) {
					// ヒエラルキーのあるtaxを大分類とみなし、それ以外は無視する
					if ( is_taxonomy_hierarchical( $main_tax ) ) {
						$terms = get_the_terms( $queried_object->ID, $main_tax );

						if ( ! empty( $terms ) ) {
							$main_term = $terms[0];
							$parents   = get_ancestors( $main_term->term_id, $main_tax, 'taxonomy' );

							foreach ( array_reverse( $parents ) as $term_id ) {
								$parent = get_term( $term_id, $main_tax );

								$term_layer = array(
									'text'  => $parent->name,
									'link'  => get_term_link( $parent->term_id, $main_tax ),
									'layer' => 'taxonomy',
								);
								array_push( $breadcrumbs, $term_layer );
							}

							// 「未分類」は除外
							if ( 'uncategorized' !== $main_term->slug ) {
								$term_layer = array(
									'text'  => $main_term->name,
									'link'  => get_term_link( $main_term->term_id, $main_tax ),
									'layer' => 'taxonomy',
								);
								array_push( $breadcrumbs, $term_layer );
							}
						}
					}
				}
			}

			// 親投稿のレイヤーを追加
			if ( 0 !== $queried_object->post_parent ) {
				$parents = get_post_ancestors( $queried_object->ID );

				foreach ( array_reverse( $parents ) as $post_id ) {
					$parent = get_post( $post_id );

					$parent_post_layer = array(
						'text'  => $parent->post_title,
						'link'  => get_permalink( $parent->ID ),
						'layer' => 'parent_post',
					);
					array_push( $breadcrumbs, $parent_post_layer );
				}
			}

			// 現在のページのレイヤーを追加
			$current_text = $queried_object->post_title;
			if ( 'display' === $context && 30 <= mb_strlen( $current_text ) ) {
				$current_text = __( '現在のページ', 'wordpressfoundation' );
			}

			$current_page_layer = array(
				'text'  => $current_text,
				'link'  => get_permalink( $queried_object->ID ),
				'layer' => 'current_page',
			);
			array_push( $breadcrumbs, $current_page_layer );
		}

		return apply_filters( 'wpf_breadcrumbs', $breadcrumbs );
	}

	/**
	 * 投稿IDに属するタームリンクの出力バッファを返す。
	 * なお、明示的にtaxが渡されない場合、メインtaxでの取得を試みる。
	 *
	 * @since 0.1.0
	 * @param int      $post_id 投稿ID
	 * @param string[] $taxonomies タクソノミースラッグの配列
	 * @return string
	 */
	public static function get_the_term_links( $post_id = null, $taxonomies = array() ) {
		if ( ! $post_id ) {
			return;
		}

		if ( empty( $taxonomies ) ) {
			$post_type = get_post_type( $post_id );

			if ( ! $post_type ) {
				return;
			}

			$taxonomies = WPF_Utils::get_object_public_taxonomies( $post_type );

			if ( empty( $taxonomies ) ) {
				return;
			}
		}

		$terms = wp_get_object_terms( $post_id, $taxonomies );

		if ( ! $terms ) {
			return;
		}

		ob_start();
		foreach ( $terms as $term ) {
			?>
			<a class="pill-secondary pill--<?php echo esc_attr( hash( 'md5', $term->slug ) ); ?>" href="<?php echo esc_url( get_term_link( $term->term_id ) ); ?>">
				<?php echo esc_html( $term->name ); ?>
			</a>
			<?php
		}
		return ob_get_clean();
	}

	/**
	 * 戻るリンクの出力バッファを返す。
	 *
	 * 個別投稿ページからアーカイブページ、添付ファイルページから
	 * 個別投稿ページへ戻るリンクに使用する。
	 *
	 * @since 0.1.0
	 * @return string
	 */
	public static function get_the_back_link() {
		if ( ! is_single() ) {
			return;
		}

		$post_type = get_post_type();

		if ( ! $post_type ) {
			return;
		}

		$post_type_object = get_post_type_object( $post_type );

		if ( ! $post_type_object ) {
			return;
		}

		if ( is_attachment() ) {
			$text = __( '記事', 'wordpressfoundation' );
		} else {
			$page_for_posts = WPF_Utils::get_page_for_posts( $post_type );
			if ( $page_for_posts ) {
				$text = get_the_title( $page_for_posts );
			} else {
				$text = function_exists( 'pll__' ) ? pll__( $post_type_object->labels->name ) : $post_type_object->labels->name;
			}
		}
		/* translators: 戻るボタンのテキスト */
		$text = sprintf( __( '%sに戻る', 'wordpressfoundation' ), $text );

		if ( is_attachment() ) {
			$parent_id = get_post_field( 'post_parent' );
			$url       = get_permalink( $parent_id );
		} else {
			$url = get_post_type_archive_link( $post_type );
		}

		if ( ! $text && ! $url ) {
			return;
		}

		ob_start();
		?>
		<a class="button:secondary" href="<?php echo esc_url( $url ); ?>" style="--button-size: var(--font-size-text--sm)">
			<?php echo WPF_Icons::get_svg( 'ui', 'arrow_left', 24 ); /* phpcs:ignore WordPress.Security.EscapeOutput */ ?>
			<span><?php echo esc_html( $text ); ?></span>
		</a>
		<?php
		return ob_get_clean();
	}

	/**
	 * htmlタグのカスタム属性の出力バッファを返す。
	 *
	 * @since 0.1.0
	 * @return string
	 */
	public static function get_custom_html_attrs() {
		$html_attributes = ' class="no-js" data-site-theme="' . WPF_Utils::get_site_theme() . '"';

		/**
		 * カスタムデータ属性のフィルタ。
		 *
		 * @param string $html_attributes カスタムデータ属性。
		 */
		$html_attributes = apply_filters( 'wpf_custom_html_attrs', $html_attributes );

		ob_start();
		echo $html_attributes; // phpcs:ignore WordPress.Security.EscapeOutput
		return ob_get_clean();
	}

	/**
	 * コピーライト表記を返す。
	 *
	 * 出力例: Copyright &copy;2012&ndash;2023 Company name
	 *
	 * @link https://wordpress.stackexchange.com/a/226640
	 *
	 * Copyright => 米国著作権法第401条にて規定。
	 * &copy; => 万国著作権条約にて規定。
	 * 日本では万国著作権条約とベルヌ条約（自動著作権付与）に加盟。
	 *
	 * @since 0.1.0
	 * @return string
	 */
	public static function get_copyright() {

		// コピーライト年が手動で設定されている場合はそれを優先する。
		// なければ、最終更新日が最も新しい投稿に基づき自動更新される。
		$years = get_option( 'copyright' );
		if ( ! $years ) {
			$args = array(
				'posts_per_page'         => 1,
				'post_type'              => get_post_types( array( 'public' => true ) ),
				'post_status'            => 'publish',
				'orderby'                => 'post_date',

				// 不要なメモリを浪費しない。
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
				'cache_results'          => false,
			);

			$newest = get_posts( array( 'order' => 'DESC' ) + $args );
			$oldest = get_posts( array( 'order' => 'ASC' ) + $args );
			$years  = array(
				'from' => $oldest ? mysql2date( 'Y', $oldest[0]->post_date_gmt ) : '',
				'to'   => $newest ? mysql2date( 'Y', $newest[0]->post_date_gmt ) : '',
			);

			update_option( 'copyright_years', $years );
		}

		$copyright = $years['from'];

		if ( $years['from'] !== $years['to'] ) {
			$copyright .= '&ndash;' . $years['to'];
		}

		$site_owner = ! empty( get_option( 'site_owner' ) ) ? get_option( 'site_owner' ) : get_bloginfo( 'name' );
		$site_owner = function_exists( 'pll__' ) ? pll__( $site_owner ) : $site_owner;

		return 'Copyright &copy;' . $copyright . ' ' . $site_owner;
	}

	/**
	 * 親ページのスラッグを返す。
	 *
	 * @since 0.1.0
	 * @return string
	 */
	public static function get_parent_slug() {
		global $post;
		if ( $post->post_parent ) {
			$post_data = get_post( $post->post_parent );
			return $post_data->post_name;
		}
	}

	/**
	 * 現在のタームスラッグを返す。
	 *
	 * @since 0.1.0
	 * @return WP_Term|null
	 */
	public static function get_the_current_term() {
		$term_id       = '';
		$taxonomy_slug = '';

		if ( is_category() ) {
			$taxonomy_slug = 'category';
			$term_id       = get_query_var( 'cat' );
		} elseif ( is_tag() ) {
			$taxonomy_slug = 'post_tag';
			$term_id       = get_query_var( 'tag_id' );
		} elseif ( is_tax() ) {
			$taxonomy_slug = get_query_var( 'taxonomy' );
			$term_slug     = get_query_var( 'term' );
			$term          = get_term_by( 'slug', $term_slug, $taxonomy_slug );
			$term_id       = $term->term_id;
		}

		if ( ! empty( $term_id ) && ! empty( $taxonomy_slug ) ) {
			return get_term( $term_id, $taxonomy_slug );
		}
	}

	/**
	 * 公開日のHTMLタグを取得
	 *
	 * @since 0.1.0
	 * @param int|WP_Post $post 投稿IDまたは WP_Post オブジェクト。デフォルトは現在の投稿。
	 * @return string
	 */
	public static function get_the_publish_date_tag( $post = null ) {
		$publish_date = get_the_date( '', $post );

		if ( ! $publish_date ) {
			return '';
		}

		$publish_date_w3c = get_the_date( DATE_W3C, $post );

		ob_start();
		?>
		<span class="date">
			<time datetime="<?php echo esc_attr( $publish_date_w3c ); ?>"><?php echo esc_html( $publish_date ); ?></time>
		</span>
		<?php
		return ob_get_clean();
	}

	/**
	 * 更新日のHTMLタグを取得
	 *
	 * @since 0.1.0
	 * @param int|WP_Post $post 投稿IDまたは WP_Post オブジェクト。デフォルトは現在の投稿。
	 * @return string
	 */
	public static function get_the_modified_date_tag( $post = null ) {
		$modified_date = get_the_modified_date( '', $post );

		if ( ! $modified_date ) {
			return '';
		}

		// 年月日は管理画面で日付形式を設定するのがWPの仕様。
		// ポリランを使用している場合は文字列翻訳で年月日の翻訳を定義できる。
		$modified_date_w3c = get_the_modified_date( DATE_W3C, $post );

		ob_start();
		?>
		<span class="date">
			<time datetime="<?php echo esc_attr( $modified_date_w3c ); ?>"><?php echo esc_html( $modified_date ); ?></time>
		</span>
		<?php
		return ob_get_clean();
	}

	/**
	 * 添付ファイルIDが存在する場合は img 要素、なければ no-image 画像を返す。
	 *
	 * @since 0.1.0
	 * @param int          $image_id 画像ファイルID。
	 * @param string|int[] $size 画像の大きさ。キーワードまたは数値（幅・高さの配列）。
	 * @param boolean      $icon 画像ファイルを表すメディアアイコンを使用するかどうか。
	 * @param string|array $attr img要素の属性。
	 * @param boolean      $use_no_image 画像がない場合にno-image画像を使用するかどうか。
	 * @return string|false 添付ファイルIDが存在する場合は img 要素、なければ no-image画像、それもなければ false を返す。
	 */
	public static function get_the_image( $image_id, $size = 'medium', $icon = false, $attr = array(), $use_no_image = true ) {
		$image = false;

		if ( ! empty( $attr ) ) {
			$attr = array( 'loading' => 'lazy' );
		}

		if ( $image_id ) {
			$image = wp_get_attachment_image(
				$image_id,
				$size,
				$icon,
				$attr
			);
		} elseif ( $use_no_image && get_theme_mod( 'wpf_no_image', false ) ) {
			$image = wp_get_attachment_image(
				get_theme_mod( 'wpf_no_image', false ),
				$size,
				$icon,
				$attr
			);
		}

		return $image;
	}

	/**
	 * 添付ファイルIDが存在する場合は img 要素、なければ no-image 画像を返す。
	 *
	 * @since 0.1.0
	 * @param int          $image_id 画像ファイルID。
	 * @param string|int[] $size 画像の大きさ。キーワードまたは数値（幅・高さの配列）。
	 * @param boolean      $icon 画像ファイルを表すメディアアイコンを使用するかどうか。
	 * @param string|array $attr img要素の属性。
	 * @param boolean      $use_no_image 画像がない場合にno-image画像を使用するかどうか。
	 * @return string|false 添付ファイルIDが存在する場合は img 要素、なければ no-image画像、それもなければ false を返す。
	 */
	public static function get_the_member_image( $image_id, $size = 'medium', $icon = false, $attr = array(), $use_no_image = true ) {
		$image = false;

		if ( ! empty( $attr ) ) {
			$attr = array( 'loading' => 'lazy' );
		}

		if ( $image_id ) {
			$image = wp_get_attachment_image(
				$image_id,
				$size,
				$icon,
				$attr
			);
		} elseif ( $use_no_image ) {
			$image = '<svg width="500" height="500" viewBox="0 0 500 500" xmlns="http://www.w3.org/2000/svg">
            <g clip-path="url(#clip0_9691_28496)">
            <rect width="500" height="500" fill="var(--color-background-disabled)"/>
            <path d="M249.999 288.732C333.618 288.732 401.407 356.521 401.407 440.141C401.407 441.317 401.393 442.493 401.366 443.662L401.369 500H98.5901V443.662C98.573 442.881 98.5764 442.101 98.5833 441.317L98.5901 440.141C98.5901 356.521 166.379 288.733 249.999 288.732Z" fill="var(--color-content-disabled)"/>
            <path d="M250 91.5493C294.727 91.5493 330.986 127.808 330.986 172.535C330.986 217.263 294.727 253.521 250 253.521C205.273 253.521 169.014 217.262 169.014 172.535C169.014 127.808 205.273 91.5494 250 91.5493Z" fill="var(--color-content-disabled)"/>
            </g>
            <defs>
            <clipPath id="clip0_9691_28496">
            <rect width="500" height="500" fill="var(--color-background-primary)"/>
            </clipPath>
            </defs>
            </svg>';
		}

		return $image;
	}

	/**
	 * {@see ::get_the_image} を出力する。
	 *
	 * @since 0.1.0
	 * @param int          $image_id 画像ファイルID。
	 * @param string|int[] $size 画像の大きさ。キーワードまたは数値（幅・高さの配列）。
	 * @param boolean      $icon 画像ファイルを表すメディアアイコンを使用するかどうか。
	 * @param string|array $attr img要素の属性。
	 * @param boolean      $use_no_image 画像がない場合にno-image画像を使用するかどうか。
	 */
	public static function the_image( $image_id, $size = 'medium', $icon = false, $attr = array(), $use_no_image = true ) {
		echo self::get_the_image( $image_id, $size, $icon, $attr, $use_no_image ); // phpcs:ignore WordPress.Security.EscapeOutput
	}

	/**
	 * {@see ::get_the_member_image} を出力する。
	 *
	 * @since 0.1.0
	 * @param int          $image_id 画像ファイルID。
	 * @param string|int[] $size 画像の大きさ。キーワードまたは数値（幅・高さの配列）。
	 * @param boolean      $icon 画像ファイルを表すメディアアイコンを使用するかどうか。
	 * @param string|array $attr img要素の属性。
	 * @param boolean      $use_no_image 画像がない場合にno-image画像を使用するかどうか。
	 */
	public static function the_member_image( $image_id, $size = 'medium', $icon = false, $attr = array(), $use_no_image = true ) {
		echo self::get_the_member_image( $image_id, $size, $icon, $attr, $use_no_image ); // phpcs:ignore WordPress.Security.EscapeOutput
	}

	/**
	 * タームナビゲーションの出力バッファを取得
	 * 引数が渡されなければ、現在のクエリに基づき、メインタクソノミーを設定する
	 *
	 * @since 0.1.0
	 * @param array|string $args 引数の配列または文字列。使用可能な引数については https://developer.wordpress.org/reference/classes/wp_term_query/__construct/ を参照のこと。
	 * @param string|null  $aria_label nav 要素のカスタム　aria-label 属性。
	 * @param bool         $top_link アーカイブページへのリンクを表示するかどうか。
	 * @return string|null
	 */
	public static function get_term_navigation( $args = '', $aria_label = null, $top_link = true ) {
		$parsed_args = '';

		// 引数が文字列の場合、WP_Term_Query引数としてそのまま渡す
		if ( is_string( $args ) && ! empty( $args ) ) {
			$parsed_args = $args;
		}

		// 引数が空文字列の場合、配列にする
		if ( is_string( $args ) && empty( $args ) ) {
			$args = array();
		}

		// 引数が配列の場合、タクソノミー名が指定されていなければ、WPクエリに基づきメインタクソノミーをセット
		if ( is_array( $args ) ) {
			$taxonomies = isset( $args['taxonomy'] ) ? (array) $args['taxonomy'] : array();

			// 入力値にタクソノミーがなければ、現在のクエリから取得を試みる
			if ( empty( $taxonomies ) ) {
				$queried_object = get_queried_object();
				$taxonomies     = isset( $queried_object->taxonomy ) ? (array) $queried_object->taxonomy : WPF_Utils::get_object_public_taxonomies( WPF_Utils::get_post_type() );
			}

			if ( empty( $taxonomies ) ) {
				return;
			}

			// メインタクソノミーをセット
			$args['taxonomy'] = $taxonomies[0];

			$defaults    = array(
				'hide_empty' => true,
			);
			$parsed_args = wp_parse_args( $args, $defaults );
		}

		$terms = get_terms( $parsed_args );

		ob_start();

		if ( ! is_wp_error( $terms ) ) {
			$current_term = self::get_the_current_term();
			$aria_label   = $aria_label ? $aria_label : __( 'カテゴリー', 'wordpressfoundation' );
			?>
			<nav aria-label="<?php echo esc_attr( $aria_label ); ?>" class="navigation tax-menu">
				<ul 
					class="navigation__list cluster"
					style="--cluster-space: var(--space-s-4)">
					<?php
					if ( $top_link ) {
						$aria_current_attr = ! $current_term ? ' aria-current="page"' : '';
						?>
						<li class="menu-item fs-text--sm">
							<a 
								href="<?php echo esc_url( get_post_type_archive_link( get_post_type() ) ); ?>"
								class="pill"
								style="--pill-padding-block: 0.5rem; --pill-padding-inline: 0.9rem"<?php echo $aria_current_attr; /* phpcs:ignore WordPress.Security.EscapeOutput */ ?>>
								<?php echo esc_html_e( 'すべて', 'wordpressfoundation' ); ?>
							</a>
						</li>
						<?php
					}

					foreach ( $terms as $term ) {
						$aria_current_attr = $current_term && $current_term->term_id === $term->term_id ? ' aria-current="page"' : '';
						?>
						<li class="menu-item fs-text--sm">
							<a 
								href="<?php echo esc_attr( get_term_link( $term->term_id ) ); ?>"
								class="pill"
								style="--pill-padding-block: 0.5rem; --pill-padding-inline: 0.9rem"<?php echo $aria_current_attr; /* phpcs:ignore WordPress.Security.EscapeOutput */ ?>>
								<?php echo esc_html( $term->name ); ?>
							</a>
						</li>
						<?php
					}
					?>
				</ul>
			</nav>
			<?php
		}

		return ob_get_clean();
	}

	/**
	 * 日付ナビゲーションの出力バッファを返す。
	 *
	 * 組み込みのwp_get_archivesは、カスタム投稿タイプの日付アーカイブにおいてクエリパラメータ形式
	 * のパーマリンクを出力するため {@see WPF_CPT_Rewrite} で、日付パーマリンクを成形している。
	 *
	 * @param string|array $args デフォルトのアーカイブリンクの引数（オプション）。
	 * @param bool         $top_link アーカイブページへのリンクを表示するかどうか。。
	 * @return string 日付ナビゲーションのHTML文字列を返す。
	 */
	public static function get_date_navigation( $args = '', $top_link = true ) {
		if ( is_year() ) {
			$date_type = 'yearly';
		} elseif ( is_month() ) {
			$date_type = 'monthly';
		} elseif ( is_day() ) {
			$date_type = 'daily';
		} else {
			$date_type = 'monthly';
		}

		$post_type = WPF_Utils::get_post_type();

		$defaults = array(
			'type'      => $date_type,
			'echo'      => false,
			'post_type' => $post_type,
		);

		$parsed_args = wp_parse_args( $args, $defaults );

		// 日付アーカイブのリストを取得する。
		$archives = wp_get_archives( $parsed_args );

		ob_start();

		if ( ! empty( $archives ) ) {
			?>
			<ul>
				<?php echo $archives; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</ul>
			<?php
		}

		return ob_get_clean();
	}

	/**
	 * 関連投稿のクエリを返す。
	 *
	 * メインtax、サブtaxにおいて、同じタームを持つ投稿を関連投稿とみなす。
	 *
	 * @param int|WP_Post|null $post 投稿IDまたは投稿オブジェクト。デフォルトはグローバルな $post
	 * @param array            $args WP_Query のオプション。
	 * @return WP_Query|null
	 * @since 0.1.0
	 */
	public static function get_the_related_posts_query( $post = null, $args = array() ) {
		$post = get_post( $post );

		if ( ! $post ) {
			return;
		}

		$taxonomies = WPF_Utils::get_object_public_taxonomies( $post->post_type );

		if ( ! $taxonomies ) {
			return;
		}

		$tax_queries = array();

		$main_tax = 0 < count( $taxonomies ) ? $taxonomies[0] : false;
		if ( $main_tax ) {
			$terms = get_the_terms( $post->ID, $main_tax );

			if ( $terms ) {
				$term_ids = array();
				foreach ( $terms as $term ) {
					array_push( $term_ids, $term->term_id );
				}

				array_push(
					$tax_queries,
					array(
						'taxonomy' => $main_tax,
						'field'    => 'term_id',
						'terms'    => $term_ids,
					)
				);
			}
		}

		$sub_tax = 1 < count( $taxonomies ) ? $taxonomies[1] : false;
		if ( $sub_tax ) {
			$terms = get_the_terms( $post->ID, $sub_tax );

			if ( $terms ) {
				$term_ids = array();
				foreach ( $terms as $term ) {
					array_push( $term_ids, $term->term_id );
				}

				array_push(
					$tax_queries,
					array(
						'taxonomy' => $sub_tax,
						'field'    => 'term_id',
						'terms'    => $term_ids,
					)
				);
			}
		}

		if ( ! $tax_queries ) {
			return;
		}

		$tax_queries = array_merge( array( 'relation' => 'OR' ), $tax_queries );

		$args = array_merge(
			array(
				'post_type'      => $post->post_type,
				'post__not_in'   => array( $post->ID ),
				'posts_per_page' => 6,
				'order'          => 'DESC',
				'orderby'        => 'date',
			),
			array(
				'tax_query' => $tax_queries, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			),
			$args
		);

		return new WP_Query( $args );
	}

	/**
	 * ページ付き著者リストの出力バッファを返す。
	 *
	 * @param array $args get_users の引数。
	 * @return string
	 */
	public static function get_paginate_authors( $args = '' ) {
		$paged_query = get_query_var( 'paged' );
		$page        = $paged_query ? $paged_query : 1;

		// ページあたりの表示件数を正規化
		// `WP_Query` の`posts_per_page` と違い、全件表示は `""` か `0` 。
		$per_page = 0;
		if ( is_array( $args ) && array_key_exists( 'number', $args ) && '' === $args['number'] ) {
			$per_page = 0;
		}
		if ( is_array( $args ) && array_key_exists( 'number', $args ) && is_int( $args['number'] ) ) {
			$per_page = $args['number'];
		}

		$defaults = array(
			'number'   => $per_page,
			'offset'   => $per_page > 0 ? ( $page - 1 ) * $per_page : 0,
			'role__in' => array( 'administrator', 'editor', 'author', 'contributor' ),
		);

		$parsed_args = wp_parse_args( $args, $defaults );

		$user_query = new WP_User_Query( $parsed_args );
		$users      = $user_query->get_results();

		ob_start();

		if ( ! empty( $users ) ) {
			foreach ( $users as $user ) {
				?>
				<article>
					<div>
						<a 
							href="<?php echo esc_url( get_author_posts_url( $user->ID ) ); ?>" 
							tabindex="-1" 
							title="<?php echo esc_attr( /* translators: 著者名 */ sprintf( __( '%sのプロフィールを見る', 'wordpressfoundation' ), $user->display_name ) ); ?>"
							aria-label="<?php echo esc_attr( /* translators: 著者名 */ sprintf( __( '%sのプロフィールを見る', 'wordpressfoundation' ), $user->display_name ) ); ?>">
							<?php echo get_avatar( $user->ID, 32, '', $user->display_name ); ?>
						</a>
					</div>

					<div>
						<a 
							href="<?php echo esc_url( get_author_posts_url( $user->ID ) ); ?>" 
							title="<?php echo esc_attr( /* translators: 著者名 */ sprintf( __( '%sのプロフィールを見る', 'wordpressfoundation' ), $user->display_name ) ); ?>"
							aria-label="<?php echo esc_attr( /* translators: 著者名 */ sprintf( __( '%sのプロフィールを見る', 'wordpressfoundation' ), $user->display_name ) ); ?>">
							<?php echo esc_html( $user->display_name ); ?>
						</a>
					</div>
				</article>
				<?php
			}
		}

		$total = $user_query->get_total();
		$pages = $per_page > 0 ? ceil( $total / $per_page ) : 0;
		$big   = 999999999; // ありえない整数が必要。

		if ( $pages > 1 ) {
			?>
			<nav aria-label="<?php echo esc_attr( _x( '著者', 'authors', 'wordpressfoundation' ) ); ?>" class="navigation pagination">
				<div class="nav-links">
					<?php
					echo paginate_links( /* phpcs:ignore WordPress.Security.EscapeOutput */
						array(
							'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
							'format'    => '/paged/%#%/',
							'current'   => max( 1, $page ),
							'total'     => $pages,
							'prev_text' => sprintf(
								'%s <span>%s</span>',
								WPF_Icons::get_svg( 'ui', 'angle_left' ),
								esc_html__( '前へ', 'wordpressfoundation' )
							),
							'next_text' => sprintf(
								'<span>%s</span> %s',
								esc_html__( '次へ', 'wordpressfoundation' ),
								WPF_Icons::get_svg( 'ui', 'angle_right' )
							),
						)
					);
					?>
				</div>
			</nav>
			<?php
		}

		return ob_get_clean();
	}

	/**
	 * ページ付きタームリストの出力バッファを返す。
	 *
	 * @param array $args get_termsの引数
	 * @return string
	 */
	public static function get_paginate_terms( $args = '' ) {
		$paged_query = get_query_var( 'paged' );
		$page        = $paged_query ? $paged_query : 1;

		// ページあたりの表示件数を正規化
		// `WP_Query` の`posts_per_page` と違い、全件表示は `""` か `0` 。
		$per_page = 0;
		if ( is_array( $args ) && array_key_exists( 'number', $args ) && '' === $args['number'] ) {
			$per_page = 0;
		}
		if ( is_array( $args ) && array_key_exists( 'number', $args ) && is_int( $args['number'] ) ) {
			$per_page = $args['number'];
		}

		$defaults = array(
			'taxonomy'     => 'category',
			'number'       => $per_page,
			'offset'       => $per_page > 0 ? ( $page - 1 ) * $per_page : 0,
			'hierarchical' => false, // 空の親タームを含めない
			'hide_empty'   => true,
		);

		$parsed_args = wp_parse_args( $args, $defaults );

		$terms = get_terms( $parsed_args );

		ob_start();

		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				?>
				<article>
					<h2>
						<a href="<?php echo esc_attr( get_term_link( $term->term_id ) ); ?>">
							<?php echo esc_html( $term->name ); ?>
						</a>
					</h2>

					<p>
						<?php echo esc_html( $term->description ); ?>
					</p>
				</article>
				<?php
			}
		}

		$parsed_args = wp_parse_args(
			array(
				'offset' => '',
			),
			$parsed_args
		);
		$total       = wp_count_terms( $parsed_args );
		$pages       = $per_page > 0 ? ceil( $total / $per_page ) : 0;
		$big         = 999999999; // ありえない整数が必要。

		if ( $pages > 1 ) {
			?>
			<nav aria-label="<?php echo esc_attr( _x( 'ターム', 'terms', 'wordpressfoundation' ) ); ?>" class="navigation pagination">
				<div class="nav-links">
					<?php
					echo paginate_links( /* phpcs:ignore WordPress.Security.EscapeOutput */
						array(
							'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
							'format'    => '/paged/%#%/',
							'current'   => max( 1, $page ),
							'total'     => $pages,
							'prev_text' => sprintf(
								'%s <span>%s</span>',
								WPF_Icons::get_svg( 'ui', 'angle_left' ),
								esc_html__( '前へ', 'wordpressfoundation' )
							),
							'next_text' => sprintf(
								'<span>%s</span> %s',
								esc_html__( '次へ', 'wordpressfoundation' ),
								WPF_Icons::get_svg( 'ui', 'angle_right' )
							),
						)
					);
					?>
				</div>
			</nav>
			<?php
		}

		return ob_get_clean();
	}

	/**
	 * 特定のメタキーをサポートしている投稿タイプのみを取得する
	 *
	 * @param string $meta_key 検索するメタキー
	 * @param int    $cache_time キャッシュ時間（秒単位、デフォルト: 1ヶ月）
	 * @return array サポートしている投稿タイプの配列
	 */
	public static function get_post_types_with_meta_value( $meta_key, $cache_time = MONTH_IN_SECONDS ) {
		global $wpdb;

		// メタキーに基づいたキャッシュキーを定義
		$cache_key = 'post_types_with_meta_' . sanitize_key( $meta_key );

		// キャッシュからデータを取得
		$post_types = wp_cache_get( $cache_key );

		// キャッシュにデータがない場合、DBから取得
		if ( false === $post_types ) {
			// メタキーが存在する投稿タイプのみを取得するクエリ
            $results = $wpdb->get_col( // phpcs:ignore
				$wpdb->prepare(
					"SELECT DISTINCT p.post_type
                    FROM {$wpdb->posts} p
                    INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                    WHERE pm.meta_key = %s
                    AND pm.meta_value != '' AND pm.meta_value != '0' AND pm.meta_value != 'false' AND pm.meta_value != 'no'
                    GROUP BY p.post_type",
					$meta_key
				)
			);

			// 結果を配列に格納
			$post_types = $results;

			// 結果をキャッシュに保存
			wp_cache_set( $cache_key, $post_types, '_wpf_post_types_with_meta_value', $cache_time );
		}

		return $post_types;
	}

	/**
	 * 特定のメタキーをサポートしている投稿タイプかどうかを確認する
	 *
	 * @param string $meta_key 検索するメタキー
	 * @param string $post_type 確認する投稿タイプ
	 * @param int    $cache_time キャッシュ時間（秒単位、デフォルト: 1ヶ月）
	 * @return bool 指定した投稿タイプがサポートしているか
	 */
	public static function has_post_type_with_meta_value( $meta_key, $post_type, $cache_time = MONTH_IN_SECONDS ) {
		$post_types = self::get_post_types_with_meta_value( $meta_key, $cache_time );

		return in_array( $post_type, $post_types, true );
	}

	/**
	 * カバーメディアを取得
	 *
	 * @param int $post_id 投稿ID。オプション。
	 * @return stdClass|null
	 */
	public static function get_the_cover_media( $post_id = 0 ) {
		if ( 0 === $post_id ) {
			$post_id = get_the_ID();
		}

		if ( ! $post_id ) {
			return;
		}

		$args = array();

		$media_id = get_post_meta( $post_id, '_wpf_cover_media_id', true );
		if ( ! empty( $media_id ) ) {
			$args['media_id'] = $media_id;
		}

		$media_metadata = get_post_meta( $post_id, '_wpf_cover_media_metadata', true );
		if ( ! empty( get_object_vars( $media_metadata ) ) ) {
			$args['media_metadata'] = (object) $media_metadata;
		}

		return (object) $args;
	}

	/**
	 * wp_kses_postに許可タグを追加
	 *
	 * @param string $content HTMLコンテンツ
	 * @return string
	 */
	public static function kses_post( $content ) {
		$kses_defaults = wp_kses_allowed_html( 'post' );

		$kses_defaults['img']['srcset'] = true; // 許可タグのリストに img の srcset 属性を追加
		$kses_defaults['img']['sizes']  = true; // 許可タグのリストに img の sizes を追加
		$kses_defaults['a']['tabindex'] = true;

		$svg_args = array(
			'svg'            => array(
				'class'           => true,
				'aria-hidden'     => true,
				'aria-labelledby' => true,
				'role'            => true,
				'xmlns'           => true,
				'width'           => true,
				'height'          => true,
				'viewbox'         => true, // <= Must be lower case!
				'fill'            => true,
			),
			'g'              => array(
				'fill' => true,
			),
			'title'          => array(
				'title' => true,
			),
			'path'           => array(
				'd'            => true,
				'fill'         => true,
				'stroke'       => true,
				'stroke-width' => true,
				'opacity'      => true,
			),
			'polygon'        => array(
				'points' => true,
			),
			'rect'           => array(
				'x'      => true,
				'y'      => true,
				'width'  => true,
				'height' => true,
				'fill'   => true,
			),
			'defs'           => array(),
			'lineargradient' => array(
				'id'            => true,
				'x1'            => true,
				'y1'            => true,
				'x2'            => true,
				'y2'            => true,
				'gradientunits' => true,
			),
			'stop'           => array(
				'stop-color'   => true,
				'stop-opacity' => true,
				'offset'       => true,
			),
		);

		$allowed_tags = array_merge( $kses_defaults, $svg_args );

		ob_start();
		echo wp_kses( $content, $allowed_tags );
		return ob_get_clean();
	}
}
