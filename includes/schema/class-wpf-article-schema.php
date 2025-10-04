<?php
/**
 * Article型のスキーマを生成するクラス
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

/**
 * Article型のスキーマを生成するクラス
 *
 * @since 0.1.0
 */
class WPF_Article_Schema extends WPF_Web_Page_Schema {
	/**
	 * コンストラクタ
	 *
	 * @param string $type スキーマタイプ
	 */
	public function __construct( $type = 'Article' ) {
		parent::__construct();
		$this->data['@type'] = $type;

		// 基本情報を設定
		$this->set_basic_info();
	}

	/**
	 * 基本情報を設定
	 *
	 * @return void
	 */
	protected function set_basic_info() {
		global $post;

		$this->data['headline']      = get_the_title();
		$this->data['url']           = get_permalink();
		$this->data['datePublished'] = get_the_date( 'c' );
		$this->data['dateModified']  = get_the_modified_date( 'c' );

		// 抜粋文があれば設定
		if ( has_excerpt() ) {
			$this->data['description'] = get_the_excerpt();
		}

		// アイキャッチ画像があれば設定
		if ( has_post_thumbnail() ) {
			$image = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );
			if ( $image ) {
				$this->data['image'] = array(
					'@type'  => 'ImageObject',
					'url'    => $image[0],
					'width'  => $image[1],
					'height' => $image[2],
				);
			}
		}

		// 著者情報を設定
		$author_id = get_the_author_meta( 'ID' );
		if ( $author_id ) {
			$this->data['author'] = array(
				'@type' => 'Person',
				'name'  => get_the_author(),
				'url'   => get_author_posts_url( $author_id ),
			);
		}

		// 組織情報を設定
		$this->data['publisher'] = array(
			'@type' => 'Organization',
			'name'  => get_bloginfo( 'name' ),
			'url'   => home_url(),
		);

		// 組織のロゴを設定
		$custom_logo_id = get_theme_mod( 'custom_logo' );
		if ( $custom_logo_id ) {
			$logo_image = wp_get_attachment_image_src( $custom_logo_id, 'full' );
			if ( $logo_image ) {
				$this->data['publisher']['logo'] = array(
					'@type' => 'ImageObject',
					'url'   => $logo_image[0],
				);
			}
		}
	}
}
