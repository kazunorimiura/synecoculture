<?php
/**
 * WebPage型のスキーマを生成するクラス
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

/**
 * WebPage型のスキーマを生成するクラス
 *
 * @since 0.1.0
 */
class WPF_Web_Page_Schema extends WPF_Schema_Generator {
	/**
	 * コンストラクタ
	 *
	 * @param string $type スキーマタイプ。デフォルトは `WebPage`
	 */
	public function __construct( $type = 'WebPage' ) {
		parent::__construct();
		$this->data['@type'] = $type;

		// 基本情報を設定
		$this->set_basic_info();
	}

	/**
	 * 基本情報を設定
	 */
	protected function set_basic_info() {
		global $post;

		$this->data['name'] = get_the_title();
		$this->data['url']  = get_permalink();

		if ( has_excerpt() ) {
			$this->data['description'] = get_the_excerpt();
		}

		if ( has_post_thumbnail() ) {
			$image = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );
			if ( $image ) {
				$this->data['image'] = $image[0];
			}
		}

		$this->data['datePublished'] = get_the_date( 'c' );
		$this->data['dateModified']  = get_the_modified_date( 'c' );
	}
}
