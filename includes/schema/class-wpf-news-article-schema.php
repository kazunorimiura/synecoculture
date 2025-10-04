<?php
/**
 * NewsArticle型のスキーマを生成するクラス
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

/**
 * NewsArticle型のスキーマを生成するクラス
 *
 * @since 0.1.0
 */
class WPF_News_Article_Schema extends WPF_Article_Schema {
	/**
	 * コンストラクタ
	 */
	public function __construct() {
		parent::__construct( 'NewsArticle' );
	}
}
