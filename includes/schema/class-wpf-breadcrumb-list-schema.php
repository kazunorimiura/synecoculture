<?php
/**
 * BreadcrumbList型のスキーマを生成するクラス
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

/**
 * BreadcrumbList型のスキーマを生成するクラス
 *
 * @since 0.1.0
 */
class WPF_Breadcrumb_List_Schema extends WPF_Schema_Generator {
	/**
	 * コンストラクタ
	 */
	public function __construct() {
		parent::__construct();
		$this->data['@type']           = 'BreadcrumbList';
		$this->data['itemListElement'] = array();
	}

	/**
	 * パンくず項目を追加
	 *
	 * @param string $name 項目名
	 * @param string $url 項目のURL
	 * @param int    $position 項目の階層
	 * @return WPF_Breadcrumb_List_Schema
	 */
	public function add_item( $name, $url, $position ) {
		$this->data['itemListElement'][] = array(
			'@type'    => 'ListItem',
			'position' => $position,
			'item'     => array(
				'@id'  => $url,
				'name' => $name,
			),
		);
		return $this;
	}
}
