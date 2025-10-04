<?php
/**
 * CollectionPage型のスキーマを生成するクラス
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

/**
 * CollectionPage型のスキーマを生成するクラス
 *
 * @since 0.1.0
 */
class WPF_Collection_Page_Schema extends WPF_Web_Page_Schema {
	/**
	 * コンストラクタ
	 */
	public function __construct() {
		parent::__construct( 'CollectionPage' );
	}

	/**
	 * 一覧に含まれるアイテムを設定
	 *
	 * @param array $posts 投稿オブジェクトリスト
	 * @return WPF_Collection_Page_Schema
	 */
	public function set_items( array $posts ) {
		$items = array();
		foreach ( $posts as $post ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => array_search( $post, $posts, true ) + 1,
				'url'      => get_permalink( $post ),
				'name'     => get_the_title( $post ),
			);
		}

		if ( ! empty( $items ) ) {
			$this->data['mainEntity'] = array(
				'@type'           => 'ItemList',
				'itemListElement' => $items,
			);
		}

		return $this;
	}
}
