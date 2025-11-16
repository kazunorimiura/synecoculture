<?php
/**
 * カスタムREST APIルートの定義。
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

/**
 * `WPF_Rest_API` クラス。
 */
class WPF_Rest_API {
	/**
	 * コンストラクタ。
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
	}

	/**
	 * APIルートを登録。
	 *
	 * @return void
	 */
	public function register_rest_routes() {
		register_rest_route(
			'custom/v1',
			'/posts',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_posts' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * `custom/v1/posts` エンドポイントへのGETリクエストに対するカスタムクエリを処理するコールバックメソッド。
	 *
	 * @param WP_REST_Request $request リクエストオブジェクト.
	 * @return WP_REST_Response レスポンスオブジェクト.
	 */
	public static function get_posts( WP_REST_Request $request ) {
		$type     = $request->get_param( 'type' );
		$exclude  = $request->get_param( 'exclude' );
		$page     = $request->get_param( 'page' );
		$per_page = $request->get_param( 'per_page' );
		$meta_key = $request->get_param( 'meta_key' );
		$order    = $request->get_param( 'order' );
		$orderby  = $request->get_param( 'orderby' );
		$lang     = $request->get_param( 'lang' );
		$size     = $request->get_param( 'size' );
		if ( ! empty( $size ) ) {
			$size = 'medium';
		}

		$args = array(
			'post_type'      => ! empty( $type ) ? explode( ',', $type ) : 'post', // デフォルト値 'post'
			'post__not_in'   => ! empty( $exclude ) ? explode( ',', $exclude ) : array(), // デフォルト値 空の配列
			'paged'          => ! empty( $page ) ? intval( $page ) : 1, // デフォルト値 1
			'posts_per_page' => ! empty( $per_page ) ? intval( $per_page ) : 10, // デフォルト値 10
			'order'          => ! empty( $order ) ? $order : 'DESC', // デフォルト値 'DESC'
			'orderby'        => ! empty( $orderby ) ? $orderby : 'date', // デフォルト値 'date'
			'lang'           => ! empty( $lang ) ? $lang : '', // デフォルト値 空文字
		);

		// meta_key の処理
		if ( ! empty( $meta_key ) ) {
			$args['meta_key'] = $meta_key; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		}

		// post__in の処理
		$post_in = $request->get_param( 'include' );
		if ( ! empty( $post_in ) ) {
			if ( is_string( $post_in ) ) {
				$post_in = explode( ',', $post_in );
			}
			$args['post__in'] = $post_in;
		}

		// tag__in の処理
		$tag_in = $request->get_param( 'tags' );
		if ( ! empty( $tag_in ) ) {
			if ( is_string( $tag_in ) ) {
				$tag_in = explode( ',', $tag_in );
			}
			$args['tag__in'] = $tag_in;
		}

		// orderby の処理
		$orderby_param = $request->get_param( 'orderby' );
		if ( ! empty( $orderby_param ) ) {
			$orderby = json_decode( rawurldecode( $orderby_param ), true );
			if ( ! empty( $orderby ) ) {
				$args['orderby'] = $orderby;
			}
		}

		// meta_query の処理
		$meta_query_param = $request->get_param( 'meta_query' );
		if ( ! empty( $meta_query_param ) ) {
			$meta_query = json_decode( $meta_query_param, true );
			if ( ! empty( $meta_query ) ) {
				$args['meta_query'] = $meta_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			}
		}

		// tax_query の処理
		$tax_query_param = $request->get_param( 'tax_query' );
		if ( ! empty( $tax_query_param ) ) {
			$tax_query = json_decode( rawurldecode( $tax_query_param ), true );
			if ( ! empty( $tax_query ) ) {
				// ※重要: あとで追加されるPolylangの言語条件がORに含まれないようにANDでネストする
				$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					'relation' => 'AND',
					$tax_query, // ユーザーが指定したOR条件
					// ←ここにPolylangの言語条件が追加される（AND条件として）
				);
			}
		}

		// 検索クエリの処理
		$search = $request->get_param( 'search' );
		if ( ! empty( $search ) ) {
			$args['s'] = $search;
		}

		// WP_Query を実行
		$query = new WP_Query( $args );

		// 結果をレスポンスとして返す
		$response = array();
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$post_id           = get_the_ID();
				$post_thumbnail_id = get_post_thumbnail_id();
				$image_data        = array();

				if ( $post_thumbnail_id ) {
					$image     = wp_get_attachment_image_src( $post_thumbnail_id, $size );
					$image_alt = get_post_meta( $post_thumbnail_id, '_wp_attachment_image_alt', true );

					if ( ! empty( $image ) ) {
						$image_data = array(
							'src'    => $image[0],
							'width'  => $image[1],
							'height' => $image[2],
							'crop'   => $image[3],
							'srcset' => wp_get_attachment_image_srcset( $post_thumbnail_id, $size ),
							'sizes'  => wp_get_attachment_image_sizes( $post_thumbnail_id, $size ),
							'alt'    => $image_alt,
						);
					}
				} elseif ( get_theme_mod( 'wpf_no_image', false ) ) {
					$no_image_id = get_theme_mod( 'wpf_no_image', false );
					$image       = wp_get_attachment_image_src( $no_image_id, $size );

					if ( ! empty( $image ) ) {
						$image_data = array(
							'src'    => $image[0],
							'width'  => $image[1],
							'height' => $image[2],
							'crop'   => $image[3],
							'srcset' => wp_get_attachment_image_srcset( $no_image_id, $size ),
							'sizes'  => wp_get_attachment_image_sizes( $no_image_id, $size ),
							'alt'    => '',
						);
					}
				}

				$data = array(
					'id'                 => $post_id,
					'title'              => get_the_title(),
					'link'               => get_permalink(),
					'excerpt'            => get_the_excerpt(),
					'date'               => get_the_date( '' ),
					'date_w3c'           => get_the_date( DATE_W3C ),
					'featured_media'     => $post_thumbnail_id,
					'featured_image_src' => $image_data,
					'meta'               => array(),
				);

				$post_type = get_post_type();
				if ( 'post' === $post_type ) {
					$taxonomies = WPF_Utils::get_object_public_taxonomies( $post_type );

					foreach ( $taxonomies as $taxonomy ) {
						$terms = get_the_terms( get_the_ID(), $taxonomy );

						if ( $terms && ! is_wp_error( $terms ) ) {
							$data[ $taxonomy ] = array();

							foreach ( $terms as $term ) {
								if ( 'uncategorized' !== $term->slug ) {
									array_push(
										$data[ $taxonomy ],
										array(
											'name' => $term->name,
											'link' => get_term_link( $term->term_id, $taxonomy ),
										)
									);
								}
							}
						}
					}
				} elseif ( 'blog' === $post_type ) {
					$taxonomies = WPF_Utils::get_object_public_taxonomies( $post_type );

					foreach ( $taxonomies as $taxonomy ) {
						$terms = get_the_terms( get_the_ID(), $taxonomy );

						if ( $terms && ! is_wp_error( $terms ) ) {
							$data[ $taxonomy ] = array();

							foreach ( $terms as $term ) {
								if ( 'uncategorized' !== $term->slug ) {
									array_push(
										$data[ $taxonomy ],
										array(
											'name' => $term->name,
											'link' => get_term_link( $term->term_id, $taxonomy ),
										)
									);
								}
							}
						}
					}
				} elseif ( 'project' === $post_type ) {
					$cat_terms           = get_the_terms( get_the_ID(), 'project_cat' );
					$cat_term_has_parent = true;
					$domain_terms        = get_the_terms( get_the_ID(), 'project_domain' );

					// 選択しているタームの最祖先を出力
					if ( $cat_terms && ! is_wp_error( $cat_terms ) ) {
						$data['project_cat']['main'] = array();

						foreach ( $cat_terms as $term ) {
							// 祖先タームのIDを配列で取得（最も近い親から最上位の順）
							$ancestors = get_ancestors( $term->term_id, 'project_cat', 'taxonomy' );

							if ( ! empty( $ancestors ) ) {
								// 配列の最後が最上位のターム
								$top_parent_id   = end( $ancestors );
								$top_parent      = get_term( $top_parent_id, 'project_cat' );
								$top_parent_link = get_term_link( $top_parent );

								array_push(
									$data['project_cat']['main'],
									array(
										'name' => $top_parent->name,
										'link' => $top_parent_link,
									)
								);
							} else {
								// 祖先がいない場合は自身が最上位
								array_push(
									$data['project_cat']['main'],
									array(
										'name' => $term->name,
										'link' => get_term_link( $term->term_id ),
									)
								);

								$cat_term_has_parent = false;
							}
						}
					}

					// 選択しているタームと領域タームを出力
					if ( ( $cat_terms && ! is_wp_error( $cat_terms ) && $cat_term_has_parent ) || ( $domain_terms && ! is_wp_error( $domain_terms ) ) ) {
						if ( $cat_terms && ! is_wp_error( $cat_terms ) && $cat_term_has_parent ) {
							$data['project_cat']['sub'] = array();

							foreach ( $cat_terms as $term ) {
								array_push(
									$data['project_cat']['sub'],
									array(
										'name' => $term->name,
										'link' => get_term_link( $term->term_id ),
									)
								);
							}
						}

						if ( $domain_terms && ! is_wp_error( $domain_terms ) ) {
							foreach ( $domain_terms as $term ) {
								array_push(
									$data['project_cat']['sub'],
									array(
										'name' => $term->name,
										'link' => get_term_link( $term->term_id ),
									)
								);
							}
						}
					}
				}

				$response[] = $data;
			}
			wp_reset_postdata();
		}

		return new WP_REST_Response( $response, 200 );
	}
}
