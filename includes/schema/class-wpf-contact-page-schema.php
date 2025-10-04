<?php
/**
 * ContactPage型のスキーマを生成するクラス
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

/**
 * ContactPage型のスキーマを生成するクラス
 *
 * @since 0.1.0
 */
class WPF_Contact_Page_Schema extends WPF_Web_Page_Schema {
	/**
	 * コンストラクタ
	 */
	public function __construct() {
		parent::__construct( 'ContactPage' );
	}

	/**
	 * 連絡先情報を設定
	 *
	 * @param array $info 連絡先情報オブジェクト
	 * @return WPF_Contact_Page_Schema
	 */
	public function set_contact_info( $info ) {
		if ( isset( $info['telephone'] ) && ! empty( $info['telephone'] ) ||
			isset( $info['email'] ) && ! empty( $info['email'] ) ||
			isset( $info['contactType'] ) && ! empty( $info['contactType'] ) ||
			isset( $info['availableLanguage'] ) && ! empty( $info['availableLanguage'] ) ||
			isset( $info['hoursAvailable'] ) && ! empty( $info['hoursAvailable'] )
		) {
			$this->data['mainEntity'] = array( '@type' => 'ContactPoint' );

			if ( isset( $info['telephone'] ) && ! empty( $info['telephone'] ) ) {
				$this->data['mainEntity']['telephone'] = $info['telephone'];
			}

			if ( isset( $info['email'] ) && ! empty( $info['email'] ) ) {
				$this->data['mainEntity']['email'] = $info['email'];
			}

			if ( isset( $info['contactType'] ) && ! empty( $info['contactType'] ) ) {
				$this->data['mainEntity']['contactType'] = $info['contactType'];
			}

			if ( isset( $info['availableLanguage'] ) && ! empty( $info['availableLanguage'] ) ) {
				$this->data['mainEntity']['availableLanguage'] = explode( ',', $info['availableLanguage'] );
			}

			if ( isset( $info['hoursAvailable'] ) && ! empty( $info['hoursAvailable'] ) ) {
				$this->data['mainEntity']['hoursAvailable'] = $info['hoursAvailable'];
			}
		}

		return $this;
	}
}
