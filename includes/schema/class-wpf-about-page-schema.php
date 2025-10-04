<?php
/**
 * AboutPage型のスキーマを生成するクラス
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

/**
 * AboutPage型のスキーマを生成するクラス
 *
 * @since 0.1.0
 */
class WPF_About_Page_Schema extends WPF_Web_Page_Schema {
	/**
	 * コンストラクタ
	 */
	public function __construct() {
		parent::__construct( 'AboutPage' );
	}

	/**
	 * 会社情報を設定
	 *
	 * @param array $info 会社情報オブジェクト
	 * @return WPF_About_Page_Schema
	 */
	public function set_company_info( $info ) {
		$this->data['mainEntity'] = array(
			'@type'       => 'Organization',
			'name'        => isset( $info['name'] ) && ! empty( $info['name'] ) ? $info['name'] : get_bloginfo( 'name' ),
			'url'         => isset( $info['url'] ) && ! empty( $info['url'] ) ? $info['url'] : home_url(),
			'description' => isset( $info['description'] ) && ! empty( $info['description'] ) ? $info['description'] : get_bloginfo( 'description' ),
		);

		if ( isset( $info['foundingDate'] ) && ! empty( $info['foundingDate'] ) ) {
			$this->data['mainEntity']['foundingDate'] = $info['foundingDate'];
		}

		if ( isset( $info['streetAddress'] ) && ! empty( $info['streetAddress'] ) ||
			isset( $info['addressLocality'] ) && ! empty( $info['addressLocality'] ) ||
			isset( $info['addressRegion'] ) && ! empty( $info['addressRegion'] ) ||
			isset( $info['postalCode'] ) && ! empty( $info['postalCode'] ) ||
			isset( $info['addressCountry'] ) && ! empty( $info['addressCountry'] )
		) {
			$this->data['mainEntity']['address'] = array( '@type' => 'PostalAddress' );

			if ( isset( $info['streetAddress'] ) && ! empty( $info['streetAddress'] ) ) {
				$this->data['mainEntity']['address']['streetAddress'] = $info['streetAddress'];
			}

			if ( isset( $info['addressLocality'] ) && ! empty( $info['addressLocality'] ) ) {
				$this->data['mainEntity']['address']['addressLocality'] = $info['addressLocality'];
			}

			if ( isset( $info['addressRegion'] ) && ! empty( $info['addressRegion'] ) ) {
				$this->data['mainEntity']['address']['addressRegion'] = $info['addressRegion'];
			}

			if ( isset( $info['postalCode'] ) && ! empty( $info['postalCode'] ) ) {
				$this->data['mainEntity']['address']['postalCode'] = $info['postalCode'];
			}

			if ( isset( $info['addressCountry'] ) && ! empty( $info['addressCountry'] ) ) {
				$this->data['mainEntity']['address']['addressCountry'] = $info['addressCountry'];
			}
		}

		return $this;
	}
}
