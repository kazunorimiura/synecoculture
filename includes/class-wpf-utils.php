<?php
/**
 * テーマのためのユーティリティ
 *
 * @package wordpressfoundation
 */

if ( ! class_exists( 'WPF_Utils' ) ) {
	/**
	 * テーマのためのユーティリティクラス。
	 *
	 * @since 0.1.0
	 */
	class WPF_Utils {

		/**
		 * コンストラクタ
		 *
		 * @since 0.1.0
		 */
		public function __construct() {
		}

		/**
		 * 投稿用ページのIDを取得する。
		 * なお、明示的に $post_type が渡されていなければ、
		 * 現在のクエリに基づき投稿タイプの取得を試みる。
		 * {@see WPF_Utils::get_post_type}
		 *
		 * get_option( 'page_for_posts' ) との違いは、CPTにも対応していること。
		 * 例えば、リライトスラッグがblogというCPTがあった場合、スラッグをblogに設
		 * 定した固定ページがあれば、そのページを投稿用ページとみなす。
		 *
		 * @since 0.1.0
		 * @param string $post_type オプション。対象の投稿タイプ。
		 * @return int 固定ページで管理している場合はそのID、そうでない場合は0を返す。
		 */
		public static function get_page_for_posts( $post_type = '' ) {
			if ( empty( $post_type ) ) {
				$post_type = self::get_post_type();
			}

			if ( empty( $post_type ) ) {
				return 0;
			}

			if ( 'page' === get_option( 'show_on_front' ) && 'post' === $post_type ) {
				$page_for_posts = (int) get_option( 'page_for_posts' );
				return function_exists( 'pll_get_post' ) ? pll_get_post( $page_for_posts ) : $page_for_posts; // Polylang プラグインをサポート。
			}

			$post_type_object = get_post_type_object( $post_type );

			if ( ! $post_type_object ) {
				return 0;
			}

			if ( ! empty( $post_type_object->rewrite ) && isset( $post_type_object->rewrite['slug'] ) ) {
				$page_object = get_page_by_path( $post_type_object->rewrite['slug'] );
			} else {
				$page_object = get_page_by_path( $post_type );
			}

			if ( $page_object ) {
				$page_for_cpt_posts = $page_object->ID;
				return function_exists( 'pll_get_post' ) ? pll_get_post( $page_for_cpt_posts ) : $page_for_cpt_posts;
			}

			return 0;
		}

		/**
		 * 現在のクエリに基づく投稿タイプを（投稿が0件でも）返す。
		 *
		 * 組み込みの get_post_type だと、投稿が0件のときに何も返さないので、
		 * パンくずリストやページデスクリプションを常に表示したいときに不便。
		 *
		 * なお、taxアーカイブは object_type 以外に投稿タイプを参照できる場所
		 * がないため、配列の先頭の投稿タイプを返すようにしている。もし、
		 * object_type に複数の投稿タイプが紐づいていたら、意図しない投稿タイプ
		 * が返る可能性もあるが、確実に取得したいなら、専用のtaxを紐づけるなり、ク
		 * エリに依存しない方法を取るなりする。
		 *
		 * @since 0.1.0
		 * @return string
		 */
		public static function get_post_type() {
			// 投稿ページ、日付アーカイブ
			if ( is_home() || is_date() && ! is_post_type_archive() ) {
				// post投稿タイプには明示的な投稿タイプ名がクエリに設定されない。
				$post_type = 'post';

				// カテゴリアーカイブ
			} elseif ( is_category() ) {
				/*
				 * taxに複数の投稿タイプを紐づけている場合を考慮（CPTを `category` に紐づけるなど）し、
				 * 現在の投稿に基づく投稿タイプを最優先し、なければ、taxに紐づく最初の投稿タイプを返す。
				 */
				$post_type = get_post_type();

				if ( ! $post_type ) {
					$post_type = get_taxonomy( 'category' )->object_type[0];
				}

				// タグアーカイブ
			} elseif ( is_tag() ) {
				/*
				 * taxに複数の投稿タイプを紐づけている場合を考慮（CPTを `post_tag` に紐づけるなど）し、
				 * 現在の投稿に基づく投稿タイプを最優先し、なければ、taxに紐づく最初の投稿タイプを返す。
				 */
				$post_type = get_post_type();

				if ( ! $post_type ) {
					$post_type = get_taxonomy( 'post_tag' )->object_type[0];
				}

				// taxアーカイブ
			} elseif ( is_tax() ) {
				/*
				 * taxに複数の投稿タイプを紐づけている場合を考慮し、
				 * 現在の投稿に基づく投稿タイプを最優先し、なければ、
				 * taxに紐づく最初の投稿タイプを返す。
				 */
				$post_type = get_post_type();

				if ( ! $post_type ) {
					$post_type = get_taxonomy( get_query_var( 'taxonomy' ) )->object_type[0];
				}

				// CPT
			} elseif ( is_post_type_archive() ) {
				$queried_object = get_queried_object();
				$post_type      = $queried_object->name;

				// 個別投稿ページ
			} elseif ( is_single() ) {
				$queried_object = get_queried_object();
				$post_type      = $queried_object->post_type;

				// 固定ページ
			} elseif ( is_page() ) {
				$queried_object = get_queried_object();
				$post_type      = $queried_object->post_type;

				/*
				 * CPT投稿用ページだったら、CPT名を返す。
				 */
				global $wp_rewrite;
				if ( ! $wp_rewrite->using_permalinks() ) {
					return $post_type;
				}

				$post_types = get_post_types(
					array(
						'_builtin' => false,
						'public'   => true,
					)
				);
				foreach ( $post_types as $pt ) {
					$post_type_object = get_post_type_object( $pt );

					// リライトスラッグとページスラッグが同一だったら、CPT投稿用ページとみなす。
					if ( $queried_object->post_name === $post_type_object->rewrite['slug'] ) {
						$post_type = $pt;
						break;
					}
				}
			} else {
				$post_type = '';
			}

			return $post_type;
		}

		/**
		 * 投稿タイプに紐づく public なタクソノミーを返す。
		 *
		 * get_object_taxonomies のロジックを流用し、public => true なtaxに限定しているだけ。
		 *
		 * @link https://developer.wordpress.org/reference/functions/get_object_taxonomies/
		 *
		 * @since 0.1.0
		 * @param string|string[]|WP_Post $object_type taxオブジェクトのタイプ名、またはそのオブジェクト。
		 * @param string                  $output 配列で返す出力の型。names' あるいは 'objects' のいずれかを指定。
		 * @return string[]|WP_Taxonomy[]
		 */
		public static function get_object_public_taxonomies( $object_type, $output = 'names' ) {
			global $wp_taxonomies;

			if ( is_object( $object_type ) ) {
				if ( 'attachment' === $object_type->post_type ) {
					return get_attachment_taxonomies( $object_type, $output );
				}
				$object_type = $object_type->post_type;
			}

			$object_type = (array) $object_type;

			$taxonomies = array();
			foreach ( (array) $wp_taxonomies as $tax_name => $tax_obj ) {
				if ( $tax_obj->public && $tax_obj->show_ui && array_intersect( $object_type, (array) $tax_obj->object_type ) ) {
					if ( 'names' === $output ) {
						$taxonomies[] = $tax_name;
					} else {
						$taxonomies[ $tax_name ] = $tax_obj;
					}
				}
			}

			return $taxonomies;
		}

		/**
		 * cookieに保存されているサイトテーマ設定を返す。
		 *
		 * @since 0.1.0
		 * @return string
		 */
		public static function get_site_theme() {
			$theme = '';

			if ( ! empty( $_COOKIE['wpf_site_theme'] ) ) {
				if ( 'dark' === $_COOKIE['wpf_site_theme'] ) {
					$theme = 'dark';
				} elseif ( 'light' === $_COOKIE['wpf_site_theme'] ) {
					$theme = 'light';
				}
			}

			return $theme;
		}

		/**
		 * タームデスクリプションを返す。
		 * なお、明示的にタームやtaxが指定されない場合は現在のクエリに基づく。
		 *
		 * WP組み込みの term_description は 'display' コンテキスト固定のため、
		 * HTMLタグ付きの文字列が返る。プレーンテキストを返すユーティリティが意外に
		 * もなさそうだったので自作した。term_description のコードを大いに参考に
		 * している。
		 *
		 * @since 0.1.0
		 * @param int    $term ターム名。
		 * @param string $taxonomy tax名。
		 * @param string $context タームフィールドをサニタイズするためのコンテキスト。詳しくは sanitize_term_field() を参照。
		 * @return string タームデスクリプション、なければ空を返す。
		 */
		public static function get_the_term_description( $term = 0, $taxonomy = '', $context = 'raw' ) {
			if ( ! $term || empty( $taxonomy ) && ( is_tax() || is_tag() || is_category() ) ) {
				$term = get_queried_object();

				if ( $term ) {
					$taxonomy = $term->taxonomy;
					$term     = $term->term_id;
				}
			}

			$description = get_term_field( 'description', $term, $taxonomy, $context );

			return is_wp_error( $description ) ? '' : $description;
		}

		/**
		 * 現在のクエリ（投稿ID）におけるメインタクソノミーに基づき `WP_Term` オブジェクトを取得
		 *
		 * @since 0.1.0
		 * @param int $post 投稿オブジェクト
		 * @return WP_Term[]|false|WP_Error
		 */
		public static function get_the_terms( $post = null ) {
			$post = get_post( $post );

			if ( ! $post ) {
				return false;
			}

			$post_type = get_post_type( $post->ID );

			if ( ! $post_type ) {
				return false;
			}

			$taxonomies = self::get_object_public_taxonomies( $post_type );

			if ( empty( $taxonomies ) ) {
				return false;
			}

			// メインタクソノミーを選択
			$taxonomy = $taxonomies[0];

			return get_the_terms( $post->ID, $taxonomy );
		}

		/**
		 * 指定したスラッグの子ページかどうかを判定
		 *
		 * @param string $parent_slug 親ページのスラッグ
		 * @return bool
		 */
		public static function is_child_of( $parent_slug ) {
			global $post;

			if ( ! is_page() || ! $post->post_parent ) {
				return false;
			}

			$parent = get_post( $post->post_parent );
			return ( $parent && $parent->post_name === $parent_slug );
		}

		/**
		 * 指定したスラッグの子孫ページかどうかを判定（孫ページなども含む）
		 *
		 * @param string $ancestor_slug 祖先ページのスラッグ
		 * @return bool
		 */
		public static function is_descendant_of( $ancestor_slug ) {
			global $post;

			if ( ! is_page() || ! $post->post_parent ) {
				return false;
			}

			$ancestors = get_post_ancestors( $post->ID );
			foreach ( $ancestors as $ancestor_id ) {
				$ancestor_post = get_post( $ancestor_id );
				if ( $ancestor_post && $ancestor_post->post_name === $ancestor_slug ) {
					return true;
				}
			}
			return false;
		}

		/**
		 * 配列が実質的に空かどうかを再帰的にチェックする
		 *
		 * すべての要素が空（empty値）の場合、または空文字列のみの場合にtrueを返します。
		 * ネストされた配列も再帰的にチェックされます。
		 *
		 * @param mixed $array チェック対象の配列または値
		 * @return bool すべての要素が空の場合true、1つでも値がある場合false
		 *
		 * @example
		 * is_array_empty(['', ['', '']]) // true
		 * is_array_empty(['test', '']) // false
		 * is_array_empty([0 => ['key' => '']]) // true
		 */
		public static function is_array_empty( $array ) {
			if ( ! is_array( $array ) ) {
				return empty( $array );
			}

			foreach ( $array as $value ) {
				if ( is_array( $value ) ) {
					if ( ! self::is_array_empty( $value ) ) {
						return false;
					}
				} else {
					if ( ! empty( $value ) ) {
						return false;
					}
				}
			}

			return true;
		}
	}
}
