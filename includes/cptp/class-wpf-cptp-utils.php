<?php
/**
 * リライトユーティリティ
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

/**
 * リライトユーティリティ。
 */
class WPF_CPTP_Utils {

	/**
	 * コンストラクタ
	 */
	public function __construct() {
	}

	/**
	 * リライトが有効なカスタム投稿タイプを返す。
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public static function get_post_types() {
		$param      = array(
			'_builtin' => false,
			'public'   => true,
		);
		$post_types = get_post_types( $param );

		return array_filter( $post_types, array( __CLASS__, 'is_rewrite_supported_by' ) );
	}

	/**
	 * 投稿タイプがリライトをサポートしているか。
	 *
	 * @since 0.1.0
	 *
	 * @param string $post_type 投稿タイプ名。
	 *
	 * @return bool サポートしていればtrue、そうでなければfalse。
	 */
	private static function is_rewrite_supported_by( $post_type ) {
		$post_type_object = get_post_type_object( $post_type );

		if ( false === $post_type_object->rewrite ) {
			$support = false;
		} else {
			$support = true;
		}

		return $support;
	}

	/**
	 * ユーザー定義のカスタム投稿タイプのパーマリンク構造を返す。
	 *
	 * @since 0.1.0
	 *
	 * @param string|WP_Post_Type $post_type 投稿タイプ名、または投稿タイプオブジェクト。
	 *
	 * @return string wpf_cptp['permalink_structure'] の値を返す。未定義ならば空の文字列を返す。
	 */
	public static function get_permalink_structure( $post_type ) {
		if ( is_string( $post_type ) ) {
			$post_type = get_post_type_object( $post_type );
		}

		if ( ! empty( $post_type->wpf_cptp ) && isset( $post_type->wpf_cptp['permalink_structure'] ) ) {
			return $post_type->wpf_cptp['permalink_structure'];
		}

		return '';
	}

	/**
	 * 著者アーカイブをサポートしているか。
	 *
	 * @since 0.1.0
	 *
	 * @param string|WP_Post_Type $post_type 投稿タイプ名、または投稿タイプオブジェクト。
	 *
	 * @return bool
	 */
	public static function get_post_type_author_archive_support( $post_type ) {
		if ( is_string( $post_type ) ) {
			$post_type = get_post_type_object( $post_type );
		}

		if ( ! empty( $post_type->wpf_cptp ) && isset( $post_type->wpf_cptp['author_archive'] ) ) {
			return ! ! $post_type->wpf_cptp['author_archive'];
		}

		return true;
	}

	/**
	 * 日付アーカイブをサポートしているか。
	 *
	 * @since 0.1.0
	 *
	 * @param string|WP_Post_Type $post_type 投稿タイプ名、または投稿タイプオブジェクト。
	 *
	 * @return bool
	 */
	public static function get_post_type_date_archive_support( $post_type ) {
		if ( is_string( $post_type ) ) {
			$post_type = get_post_type_object( $post_type );
		}

		if ( ! empty( $post_type->wpf_cptp ) && isset( $post_type->wpf_cptp['date_archive'] ) ) {
			return ! ! $post_type->wpf_cptp['date_archive'];
		}

		return true;
	}

	/**
	 * カスタムタクソノミーを返す。
	 *
	 * @param bool $objects 配列で返す出力の種類を指定する。true の場合は 'objects'、false の場合はタクソノミーの 'names'。
	 *
	 * @return array
	 */
	public static function get_taxonomies( $objects = false ) {
		if ( $objects ) {
			$output = 'objects';
		} else {
			$output = 'names';
		}

		return get_taxonomies(
			array(
				'_builtin' => false,
				'public'   => true,
			),
			$output
		);
	}
}
