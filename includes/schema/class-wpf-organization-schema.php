<?php
/**
 * Organization型のスキーマを生成するクラス
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

/**
 * Organization型のスキーマを生成するクラス
 *
 * @since 0.1.0
 */
class WPF_Organization_Schema extends WPF_Schema_Generator {
	/**
	 * コンストラクタ
	 */
	public function __construct() {
		parent::__construct();
		$this->data['@type'] = 'Organization';

		// 基本情報を設定
		$this->set_basic_info();
	}

	/**
	 * 基本情報を設定
	 */
	protected function set_basic_info() {
		$this->data['name'] = get_bloginfo( 'name' );
		$this->data['url']  = home_url();

		// ロゴ画像があれば設定
		$custom_logo_id = get_theme_mod( 'custom_logo' );
		if ( $custom_logo_id ) {
			$logo_image = wp_get_attachment_image_src( $custom_logo_id, 'full' );
			if ( $logo_image ) {
				$this->data['logo'] = $logo_image[0];
			}
		}
	}

	/**
	 * 連絡先情報を設定
	 *
	 * @param array $contact_info 連絡先情報オブジェクト
	 * @return WPF_Organization_Schema
	 */
	public function set_contact_point( array $contact_info ) {
		if (
			isset( $contact_info['telephone'] ) && ! empty( $contact_info['telephone'] ) ||
			isset( $contact_info['contactType'] ) && ! empty( $contact_info['contactType'] ) ||
			isset( $contact_info['availableLanguage'] ) && ! empty( $contact_info['availableLanguage'] )
		) {
			$this->data['contactPoint'] = array( '@type' => 'ContactPoint' );

			if ( isset( $contact_info['telephone'] ) && ! empty( $contact_info['telephone'] ) ) {
				$this->data['contactPoint']['telephone'] = $contact_info['telephone'];
			}

			if ( isset( $contact_info['contactType'] ) && ! empty( $contact_info['contactType'] ) ) {
				$this->data['contactPoint']['contactType'] = $contact_info['contactType'];
			}

			if ( isset( $contact_info['availableLanguage'] ) && ! empty( $contact_info['availableLanguage'] ) ) {
				$this->data['contactPoint']['availableLanguage'] = $contact_info['availableLanguage'];
			}
		}

		return $this;
	}

	/**
	 * SNSプロファイルを設定
	 *
	 * @param array $profiles プロフィールオブジェクト
	 * @return WPF_Organization_Schema
	 */
	public function set_social_profiles( array $profiles ) {
		$this->data['sameAs'] = array_values( array_filter( $profiles ) );
		return $this;
	}
}
