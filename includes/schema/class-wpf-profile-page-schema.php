<?php
/**
 * ProfilePage型のスキーマを生成するクラス
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

/**
 * ProfilePage型のスキーマを生成するクラス
 *
 * @since 0.1.0
 */
class WPF_Profile_Page_Schema extends WPF_Web_Page_Schema {
	/**
	 * コンストラクタ
	 */
	public function __construct() {
		parent::__construct( 'ProfilePage' );

		// 著者ページ固有の基本情報を設定
		$this->set_author_page_info();
	}

	/**
	 * 著者ページの基本情報を設定
	 */
	protected function set_author_page_info() {
		$author_id = get_queried_object_id();

		if ( $author_id ) {
			// ページタイトルを著者名に設定
			$this->data['name'] = get_the_author_meta( 'display_name', $author_id );

			// URLを著者アーカイブページに設定
			$this->data['url'] = get_author_posts_url( $author_id );

			// 著者の説明文があれば設定
			$author_description = get_the_author_meta( 'description', $author_id );
			if ( ! empty( $author_description ) ) {
				$this->data['description'] = $author_description;
			}

			// 著者のアバター画像を設定
			$avatar_url = get_avatar_url( $author_id, array( 'size' => 512 ) );
			if ( $avatar_url ) {
				$this->data['image'] = $avatar_url;
			}

			// ProfilePageには日付情報は不要なので削除
			unset( $this->data['datePublished'] );
			unset( $this->data['dateModified'] );

			// 著者のPersonエンティティを設定
			$this->set_main_entity( $author_id );
		}
	}

	/**
	 * メインエンティティ（Person）を設定
	 *
	 * @param int $author_id 著者ID
	 */
	protected function set_main_entity( $author_id ) {
		$person_schema = new WPF_Person_Schema( $author_id );
		$person_data   = $person_schema->get_data();

		// @contextの重複を避けるため除外
		unset( $person_data['@context'] );

		$this->data['mainEntity'] = $person_data;
	}
}
