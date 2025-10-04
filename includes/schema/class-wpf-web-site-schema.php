<?php
/**
 * WebSite型のスキーマを生成するクラス
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

/**
 * WebSite型のスキーマを生成するクラス
 *
 * @since 0.1.0
 */
class WPF_Web_Site_Schema extends WPF_Schema_Generator {
	/**
	 * コンストラクタ
	 */
	public function __construct() {
		parent::__construct();
		$this->data['@type'] = 'WebSite';

		// 基本情報を設定
		$this->set_basic_info();
	}

	/**
	 * 基本情報を設定
	 *
	 * @return void
	 */
	protected function set_basic_info() {
		$this->data['name']        = get_bloginfo( 'name' );
		$this->data['url']         = home_url();
		$this->data['description'] = get_bloginfo( 'description' );
	}

	/**
	 * 検索機能の情報を設定
	 *
	 * @return WPF_Web_Site_Schema
	 */
	public function set_search_action() {
		$this->data['potentialAction'] = array(
			'@type'       => 'SearchAction',
			'target'      => home_url( '?s={search_term_string}' ),
			'query-input' => 'required name=search_term_string',
		);
		return $this;
	}
}
