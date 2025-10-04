<?php
/**
 * BlogPosting型のスキーマを生成するクラス
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

/**
 * BlogPosting型のスキーマを生成するクラス
 *
 * @since 0.1.0
 */
class WPF_Blog_Posting_Schema extends WPF_Article_Schema {
	/**
	 * コンストラクタ
	 */
	public function __construct() {
		parent::__construct( 'BlogPosting' );
	}
}
