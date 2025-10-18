<?php
/**
 * 投稿リストのHTMLを出力する
 *
 * @package wordpressfoundation
 */

/**
 * 投稿リストのHTMLを返すクラス
 */
class WPF_Posts {
	/**
	 * コンストラクタ
	 *
	 * @since 0.1.0
	 */
	public function __construct() {}

	/**
	 * 投稿リストのHTMLを取得する。
	 * このメソッドは、デフォルトのWP_Query引数を定義し、REST APIのURLを生成した上で、
	 * 指定のテンプレートに基づき、HTMLテンプレートを返す。
	 * 常に、このメソッドを通じてテンプレートを呼び出す。
	 *
	 * @param string  $template テンプレート名（実行するクラスメソッド名）
	 * @param array   $args WP_Queryオブジェクトの引数。オプション
	 * @param boolean $is_ajax 非同期の投稿アイテムの読み込みを有効にするかどうか。デフォルトはtrue
	 * @return string
	 */
	public static function get_posts( $template, $args = array(), $is_ajax = true ) {
		$default = array(
			'post_type'   => 'post',
			'post_status' => 'publish',
		);

		if ( function_exists( 'pll_current_language' ) ) {
			$default['lang'] = pll_current_language();
		}

		$args = array_merge( $default, $args );

		$query = new WP_Query( $args );

		// パラメータ付きのAPI URLを生成
		$rest_url = $is_ajax ? self::wp_query_to_rest_url( $args, '/wp-json/custom/v1/posts' ) : '';

		// $templateのメソッドを呼び出す
		$posts = self::$template( $query, $rest_url, $args );

		return $posts;
	}

	/**
	 * `news` テンプレートHTMLの出力バッファを取得
	 *
	 * @param WP_Query $query WP_Queryオブジェクト
	 * @param string   $rest_url REST URL
	 * @param string[] $args 元の引数
	 * @return string
	 */
	private static function news( $query, $rest_url, $args = array() ) {
		ob_start();
		if ( $query->have_posts() ) {
			$container_id = self::create_uid( 'wpf-' . __FUNCTION__ );
			?>
			<div class="news-posts-container">
				<div class="news-posts" id="<?php echo esc_attr( $container_id ); ?>">
					<?php
					while ( $query->have_posts() ) {
						$query->the_post();
						?>
						<article class="news-posts__item">
							<div class="news-posts__item__inner">
								<div class="news-posts__item__main">
									<?php
									$wpf_terms = WPF_Utils::get_the_terms();
									if ( ! empty( $wpf_terms ) && 'uncategorized' !== $wpf_terms[0]->slug ) {
										?>
										<div class="news-posts__item__main-categories">
											<a href="<?php echo esc_url( get_term_link( $wpf_terms[0]->term_id, $wpf_terms[0]->taxonomy ) ); ?>" class="news-posts__item__main-category pill">
												<?php echo esc_html( $wpf_terms[0]->name ); ?>
											</a>
										</div>
										<?php
									}
									?>

									<a class="news-posts__item__title" href="<?php the_permalink(); ?>">
										<?php the_title(); ?>
									</a>

									<div class="news-posts__item__date">
										<?php echo WPF_Template_Tags::get_the_publish_date_tag(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
									</div>
								</div>

								<?php
								$size = 'medium';
								if ( ! empty( $args ) && isset( $args['size'] ) ) {
									$size = $args['size'];
								}
								?>
								<a data-post-elment-id="<?php echo esc_attr( 'thumbnail-' . get_the_ID() ); ?>" href="<?php the_permalink(); ?>" class="news-posts__item__thubmnail frame" title="<?php the_title(); ?>" aria-hidden="true" tabindex="-1">
									<?php WPF_Template_Tags::the_image( get_post_thumbnail_id(), $size ); ?>
								</a>
							</div>
						</article>
						<?php
					}
					wp_reset_postdata();
					?>
				</div>

				<?php echo self::more_button( $query, __FUNCTION__, $container_id, $rest_url ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
			<?php
		}
		return ob_get_clean();
	}

	/**
	 * Moreボタンテンプレートの出力バッファを取得する。
	 *
	 * @param WP_Query $query WP_Queryオブジェクト。
	 * @param string   $template テンプレートスラッグ。
	 * @param string   $container_id コンテナID。
	 * @param string   $rest_url REST URL。
	 * @return string
	 */
	private static function more_button( $query, $template, $container_id, $rest_url ) {
		ob_start();
		if ( $query->max_num_pages > 1 && ! empty( $rest_url ) ) {
			?>
			<div class="paginate-posts-more-buttom pbs-s3 pbe-s0">
				<div class="text-center">
					<button 
						class="button:secondary:wide" 
						data-template="<?php echo esc_attr( $template ); ?>" 
						data-container-id="<?php echo esc_attr( $container_id ); ?>"
						data-more-link="<?php echo esc_attr( $rest_url ); ?>" 
						data-max-page-num="<?php echo esc_attr( $query->max_num_pages ); ?>">
						<?php echo esc_html( __( 'More', 'wordpressfoundation' ) ); ?>
					</button>
				</div>
			</div>
			<?php
		}
		return ob_get_clean();
	}

	/**
	 * WPクエリ引数をWP REST APIのURLに変換する。
	 *
	 * @param object $query_args WP_Query引数。
	 * @param string $base_url パラメータを除くベースURL。
	 * @return string
	 */
	private static function wp_query_to_rest_url( $query_args, $base_url ) {
		// WP REST API に対応するパラメータの変換マップ
		$param_map = array(
			'post_type'      => 'type',
			'post__not_in'   => 'exclude',
			'post__in'       => 'include',
			'tag__in'        => 'tags',
			'paged'          => 'page',
			'posts_per_page' => 'per_page', // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
			'order'          => 'order',
			'orderby'        => 'orderby',
			's'              => 'search',
		);

		// URL パラメータを構築するための配列を初期化
		$url_params = array();

		// 各引数をループ処理
		foreach ( $query_args as $key => $value ) {
			// 対応するキーがある場合は変換
			if ( array_key_exists( $key, $param_map ) ) {
				$key = $param_map[ $key ];
			}

			// order パラメータは小文字に変換
			if ( 'order' === $key ) {
				$value = strtolower( $value );
			}

			// page パラメータは次のページを設定
			if ( 'page' === $key ) {
				$value = (int) $value + 1;
			}

			// meta_query と tax_query の特別な処理
			if ( in_array( $key, array( 'meta_query', 'tax_query', 'orderby' ), true ) ) {
				$value = rawurlencode( wp_json_encode( $value ) );
			} elseif ( is_array( $value ) ) {
				$value = implode( ',', $value );
			}

			// URL パラメータとして追加
			$url_params[ $key ] = $value;
		}

		// 言語パラメータを追加
		if ( isset( $query_args['lang'] ) ) {
			$url_params['lang'] = $query_args['lang'];
		}

		// ベース URL にパラメータを追加
		$url = add_query_arg( $url_params, $base_url );

		return $url;
	}

	/**
	 * 識別子を作成する。
	 * 指定の接頭辞にランダムなユニークIDを結合した文字列を返す。
	 *
	 * @param string $prefix 接頭辞。オプション。
	 * @return string
	 */
	private static function create_uid( $prefix = 'wpf-' ) {
		return uniqid( $prefix );
	}
}
