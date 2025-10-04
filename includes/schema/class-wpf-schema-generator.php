<?php
/**
 * Schema.orgのJSON-LDデータを生成する基底クラス
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

/**
 * Schema.orgのJSON-LDデータを生成する基底クラス
 *
 * @since 0.1.0
 */
class WPF_Schema_Generator {
	/**
	 * スキーマデータ
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * 共通のコンテキストを設定
	 */
	public function __construct() {
		$this->data['@context'] = 'https://schema.org';
	}

	/**
	 * JSON-LDデータを取得
	 *
	 * @return array
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * JSON形式の文字列を取得
	 *
	 * @return string
	 */
	public function get_json() {
		return wp_json_encode( $this->data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	}

	/**
	 * script要素を生成
	 *
	 * @return string
	 */
	public function get_script() {
		return sprintf(
			'<script type="application/ld+json">%s</script>',
			$this->get_json()
		);
	}
}
