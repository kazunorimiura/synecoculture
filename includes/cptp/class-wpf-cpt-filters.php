<?php
/**
 * CPTPフィルターフック。
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

/**
 * フィルターフック。
 *
 * @since 0.1.0
 */
class WPF_CPT_Filters {

	/**
	 * コンストラクタ。フィルターをフックする。
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		$this->add_hooks();
	}

	/**
	 * アクションフック、フィルターフックを追加する。
	 *
	 * @return void
	 * @since 0.1.0
	 */
	public function add_hooks() {
		add_filter( 'year_link', array( $this, 'get_year_link' ), 10, 1 );
		add_filter( 'month_link', array( $this, 'get_month_link' ), 10, 1 );
		add_filter( 'day_link', array( $this, 'get_day_link' ), 10, 1 );
	}

	/**
	 * アクションフック、フィルターフックを外す。
	 *
	 * @return void
	 * @since 0.1.0
	 */
	public function remove_hooks() {
		remove_filter( 'year_link', array( $this, 'get_year_link' ), 10, 1 );
		remove_filter( 'month_link', array( $this, 'get_month_link' ), 10, 1 );
		remove_filter( 'day_link', array( $this, 'get_day_link' ), 10, 1 );
	}

	/**
	 * `get_year_link` 組み込み関数をカスタム投稿タイプに対応させる。
	 * このメソッドは `get_year_link` 組み込み関数から渡される `$yearlink` の日付フロントをCPT日付フロントで置き換える。
	 * また、Polylangプラグインがアクティベートされている場合、このメソッドは現在の言語スラッグをURLクエリに追加する。
	 *
	 * @see https://developer.wordpress.org/reference/functions/get_year_link/ 組み込みの get_year_link ドキュメント。
	 * @see WPF_CPT_Rewrite::get_year_permastruct CPT年アーカイブのパーマリンク構造を返す。
	 * @see WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag CPTアーカイブのリライトタグを返す。
	 *
	 * @since 0.1.0
	 * @param string $yearlink 年アーカイブのパーマリンク。
	 * @return string 指定された年のアーカイブのパーマリンク。
	 */
	public static function get_year_link( $yearlink ) {
		return self::get_date_link( $yearlink );
	}

	/**
	 * `get_month_link` 組み込み関数をカスタム投稿タイプに対応させる。
	 * このメソッドは `get_month_link` 組み込み関数から渡される `$monthlink` の日付フロントをCPT日付フロントで置き換える。
	 * また、Polylangプラグインがアクティベートされている場合、このメソッドは現在の言語スラッグをURLクエリに追加する。
	 *
	 * @see https://developer.wordpress.org/reference/functions/get_month_link/ 組み込みの get_month_link ドキュメント。
	 * @see WPF_CPT_Rewrite::get_month_permastruct CPT年月アーカイブのパーマリンク構造を返す。
	 * @see WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag CPTアーカイブのリライトタグを返す。
	 *
	 * @since 0.1.0
	 * @param string $monthlink 年月アーカイブのパーマリンク。
	 * @return string 指定された年のアーカイブのパーマリンク。
	 */
	public static function get_month_link( $monthlink ) {
		return self::get_date_link( $monthlink );
	}

	/**
	 * `get_day_link` 組み込み関数をカスタム投稿タイプに対応させる。
	 * このメソッドは `get_day_link` 組み込み関数から渡される `$daylink` の日付フロントをCPT日付フロントで置き換える。
	 * また、Polylangプラグインがアクティベートされている場合、このメソッドは現在の言語スラッグをURLクエリに追加する。
	 *
	 * @see https://developer.wordpress.org/reference/functions/get_day_link/ 組み込みの get_day_link ドキュメント。
	 * @see WPF_CPT_Rewrite::get_month_permastruct CPT年月アーカイブのパーマリンク構造を返す。
	 * @see WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag CPTアーカイブのリライトタグを返す。
	 *
	 * @since 0.1.0
	 * @param string $daylink 年月アーカイブのパーマリンク。
	 * @return string 指定された年のアーカイブのパーマリンク。
	 */
	public static function get_day_link( $daylink ) {
		return self::get_date_link( $daylink );
	}

	/**
	 * `get_month_link` 組み込み関数をカスタム投稿タイプに対応させる。
	 * このメソッドは `get_month_link` 組み込み関数から渡される `$monthlink` の日付フロントをCPT日付フロントで置き換える。
	 * また、Polylangプラグインがアクティベートされている場合、このメソッドは現在の言語スラッグをURLクエリに追加する。
	 *
	 * @see https://developer.wordpress.org/reference/functions/get_month_link/ 組み込みの get_month_link ドキュメント。
	 * @see WPF_CPT_Rewrite::get_month_permastruct CPT年月アーカイブのパーマリンク構造を返す。
	 * @see WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag CPTアーカイブのリライトタグを返す。
	 *
	 * @since 0.1.0
	 * @param string $datelink 日付アーカイブのパーマリンク。
	 * @return string CPT対応済みの日付アーカイブのパーマリンク。
	 */
	private static function get_date_link( $datelink ) {
		global $wp_rewrite;
		$date_permastruct = $wp_rewrite->get_date_permastruct();

		// 日付パーマリンク文字列から投稿タイプを特定する
		$post_type = '';
		$tokens    = array_filter( explode( '/', rtrim( $datelink, '/' ) ) );
		$numbers   = array();
		foreach ( array_reverse( $tokens ) as $token ) {
			if ( is_numeric( $token ) ) {
				array_push( $numbers, $token );
			}
		}
		$post_types = get_post_types( array( 'public' => true ) );
		foreach ( $post_types as $pt ) {
			if ( 'page' === $pt || 'attachment' === $pt ) {
				continue;
			}

			if ( 'post' === $pt ) {
				if ( 1 === count( $numbers ) ) {
					$date_permastruct = $wp_rewrite->get_year_permastruct();
				} elseif ( 2 === count( $numbers ) ) {
					$date_permastruct = $wp_rewrite->get_month_permastruct();
				} elseif ( 3 === count( $numbers ) ) {
					$date_permastruct = $wp_rewrite->get_date_permastruct();
				}

				// カスタム投稿タイプの場合
			} else {
				if ( 1 === count( $numbers ) ) {
					$date_permastruct = WPF_CPT_Rewrite::get_year_permastruct( $pt );
				} elseif ( 2 === count( $numbers ) ) {
					$date_permastruct = WPF_CPT_Rewrite::get_month_permastruct( $pt );
				} elseif ( 3 === count( $numbers ) ) {
					$date_permastruct = WPF_CPT_Rewrite::get_date_permastruct( $pt );
				}

				// リライトタグを実際のスラッグに置き換え
				$date_permastruct = str_replace( WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $pt ), $pt, $date_permastruct );
			}

			preg_match_all( '/%([^%]+)%/', $date_permastruct, $matches );

			$rewritecode    = $matches[0];
			$rewritereplace = array_reverse( $numbers );

			$date_permastruct = str_replace( $rewritecode, $rewritereplace, $date_permastruct );

			// パーマリンク構造がマッチする場合、投稿タイプを特定したとみなす
			$include = strpos( $datelink, $date_permastruct );
			if ( false !== $include ) {
				$post_type = $pt;
				break;
			}
		}

		// この日付パーマリンクの投稿タイプが不明、またはデフォルト投稿タイプの場合、何もせずに返却
		if ( empty( $post_type ) || ( ! empty( $post_type ) && 'post' === $post_type ) ) {
			return $datelink;
		}

		// デフォルトのパーマリンク構造が設定されている場合
		if ( ! empty( $date_permastruct ) ) {
			// デフォルトパーマリンク構造の日付フロント文字列を取得
			$date_front = substr( $date_permastruct, 0, strpos( $date_permastruct, '%' ) );

			// この投稿タイプの投稿タイプオブジェクトを取得
			$post_type_obj = get_post_type_object( $post_type );
			if ( ! (bool) $post_type_obj ) {
				return new WP_Error( 'post_type_error', __( 'Post type does not exist.', 'wordpressfoundation' ), $post_type );
			}

			// CPTパーマリンク構造の日付フロントを取得
			$cpt_date_front = WPF_CPT_Rewrite::get_date_front( $post_type );

			// この投稿タイプが `has_archive` プロパティを文字列で設定している場合、CPTアーカイブのリライトタグと置き換える
			// `has_archive` プロパティが未設定の場合、この投稿タイプのリライトスラッグをCPTアーカイブのリライトタグと置き換える
			if ( ! empty( $post_type_obj->has_archive ) && is_string( $post_type_obj->has_archive ) ) {
				$cpt_date_front = str_replace( WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $post_type ), $post_type_obj->has_archive, $cpt_date_front );
			} else {
				$cpt_date_front = str_replace( WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $post_type ), $post_type_obj->rewrite['slug'], $cpt_date_front );
			}

			// この投稿タイプがフロント接頭辞を有効にしている場合、フロント接頭辞とCPTパーマリンク構造の日付フロント接頭辞を結合
			// フロントを有効にしていない場合、基本接頭辞と日付フロント接頭辞を結合
			if ( $post_type_obj->rewrite['with_front'] ) {
				$cpt_date_front = $wp_rewrite->front . $cpt_date_front;
			} else {
				$cpt_date_front = $wp_rewrite->root . $cpt_date_front;
			}

			// 元の年リンクの日付フロントをCPT日付フロントで置き換える
			$datelink = str_replace( home_url( $date_front ), home_url( $cpt_date_front ), $datelink );

			// Polylangサポート
			if ( function_exists( 'PLL' ) ) {
				$pll      = PLL();
				$datelink = $pll->links_model->switch_language_in_link( $datelink, $pll->curlang );
			}

			return $datelink;

			// デフォルトの年パーマリンク構造が有効でない場合
		} else {
			// CPTパラメータを追加
			$datelink = $datelink . '&post_type=' . $post_type;

			// Polylangサポート
			if ( function_exists( 'pll_current_language' ) ) {
				$datelink = $datelink . '&lang=' . pll_current_language();
			}

			return $datelink;
		}
	}
}
