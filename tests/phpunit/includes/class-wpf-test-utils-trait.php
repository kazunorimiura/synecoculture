<?php

/**
 * ユニットテストのユーティリティトレイト。
 */
trait WPF_Test_Utils_Trait {

	public static function get_rewrite_rules() {
		if ( class_exists( 'Rewrite_Rules_Inspector' ) ) {
			$rri   = new Rewrite_Rules_Inspector();
			$rules = $rri->get_rules();
		} else {
			global $wp_rewrite;
			$rules = $wp_rewrite->rules;
		}

		return $rules;
	}

	/**
	 * マッチしたルールを返す。
	 *
	 * Rewrite Rules Inspectorプラグインの get_rules 関数を利用する。
	 * ルールが属するパーマリンク構造（ソース）がわかるのでデバッグしやすい。
	 * 事前に bash install-plugins.sh を実行しておくこと。クエリ応答の
	 * テストではソースのチェックも行っている。
	 *
	 * （使用例）最初にマッチしたルールを取得する:
	 * reset( self::get_matched_rules( $path )[ $path ] )
	 *
	 * @param string $path 対象のパス。
	 * @return string[]
	 */
	public static function get_matched_rules( $path ) {
		$rules = self::get_rewrite_rules();

		foreach ( $rules as $regex => $query ) {
			preg_match( '#' . $regex . '#', $path, $matches );

			if ( $matches ) {
				$match_rules[ $regex ] = $query;
			}
		}

		return array( $path => $match_rules );
	}

	/**
	 * 多次元配列に値が含まれているかどうか。
	 *
	 * @param string $value 値。
	 * @param array  $array 配列。
	 * @return boolean
	 */
	public static function has_value( $value, $array ) {
		$has_value = false;

		$callback = function ( $v, $k ) use ( $value, &$has_value ) {
			if ( $value === $v ) {
				$has_value = true;
			}
		};
		array_walk_recursive( $array, $callback );
		return $has_value;
	}

	/**
	 * 多次元配列にクラスのオブジェクトが含まれているかどうか。
	 *
	 * @param string $type 値。
	 * @param array  $array 配列。
	 * @return boolean
	 */
	public static function has_obj( $type, $array ) {
		$has_obj  = false;
		$callback = function ( $v, $k ) use ( $type, &$has_obj ) {
			if ( is_object( $v ) ) {
				if ( get_class( $v ) === $type ) {
					$has_obj = true;
				}
			}
		};
		array_walk_recursive( $array, $callback );
		return $has_obj;
	}

	/**
	 * WordPress has_action() 関数のラッパー。
	 *
	 * @param string $action アクション名。
	 * @param object $obj アクションフックを定義しているオブジェクト。
	 * @param string $function アクションフックに渡すメソッド名。
	 * @return boolean
	 */
	public static function has_action( $action, $obj, $function ) {
		$registered = has_action(
			$action,
			array(
				$obj,
				$function,
			)
		);
		if ( $registered ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * WordPress has_filter() 関数のラッパー。
	 *
	 * @param string $filter フィルタ名。
	 * @param object $obj アクションフックを定義しているオブジェクト。
	 * @param string $function フィルターフックに渡すメソッド名。
	 * @return boolean
	 */
	public static function has_filter( $filter, $obj, $function ) {
		$registered = has_filter(
			$filter,
			array(
				$obj,
				$function,
			)
		);
		if ( $registered ) {
			return true;
		} else {
			return false;
		}
	}
}
