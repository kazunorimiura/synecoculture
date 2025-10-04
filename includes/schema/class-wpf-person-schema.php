<?php
/**
 * Person型のスキーマを生成するクラス
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

/**
 * Person型のスキーマを生成するクラス
 *
 * @since 0.1.0
 */
class WPF_Person_Schema extends WPF_Schema_Generator {
	/**
	 * 著者ID
	 *
	 * @var int
	 */
	protected $author_id;

	/**
	 * コンストラクタ
	 *
	 * @param int $author_id 著者ID
	 */
	public function __construct( $author_id = null ) {
		parent::__construct();

		$this->data['@type'] = 'Person';
		$this->author_id     = $author_id ? $author_id : get_queried_object_id();

		// 基本情報を設定
		$this->set_basic_info();
	}

	/**
	 * 基本情報を設定
	 */
	protected function set_basic_info() {
		if ( ! $this->author_id ) {
			return;
		}

		// 名前
		$this->data['name'] = get_the_author_meta( 'display_name', $this->author_id );

		// URL（著者アーカイブページ）
		$this->data['url'] = get_author_posts_url( $this->author_id );

		// 説明文
		$description = get_the_author_meta( 'description', $this->author_id );
		if ( ! empty( $description ) ) {
			$this->data['description'] = $description;
		}

		// 画像（アバター）
		$avatar_url = get_avatar_url( $this->author_id, array( 'size' => 512 ) );
		if ( $avatar_url ) {
			$this->data['image'] = array(
				'@type' => 'ImageObject',
				'url'   => $avatar_url,
			);
		}

		// 勤務先・所属組織
		$position = get_the_author_meta( 'position', $this->author_id );
		if ( ! empty( $position ) ) {
			$this->data['jobTitle'] = $position;
		}

		// 連絡先情報
		$this->set_contact_info();

		// ソーシャルメディアプロフィール
		$this->set_social_profiles();
	}

	/**
	 * 連絡先情報を設定
	 */
	protected function set_contact_info() {
		// セキュリティ上の理由でメールアドレスは構造化データに含めない
		$website = get_the_author_meta( 'user_url', $this->author_id );

		if ( ! empty( $website ) ) {
			// user_urlはすでにurlプロパティで使用されているので、別のプロパティを使用
			if ( ! isset( $this->data['sameAs'] ) ) {
				$this->data['sameAs'] = array();
			}
			$this->data['sameAs'][] = $website;
		}
	}

	/**
	 * ソーシャルメディアプロフィールを設定
	 */
	protected function set_social_profiles() {
		$social_fields = array(
			'x'         => get_the_author_meta( 'x', $this->author_id ),
			'instagram' => get_the_author_meta( 'instagram', $this->author_id ),
			'facebook'  => get_the_author_meta( 'facebook', $this->author_id ),
		);

		$profiles = array();

		foreach ( $social_fields as $platform => $value ) {
			if ( ! empty( $value ) ) {
				// URLでない場合はプラットフォームのベースURLを追加
				if ( ! filter_var( $value, FILTER_VALIDATE_URL ) ) {
					switch ( $platform ) {
						case 'x':
							$value = 'https://x.com/' . ltrim( $value, '@' );
							break;
						case 'instagram':
							$value = 'https://instagram.com/' . ltrim( $value, '@' );
							break;
						case 'facebook':
							$value = 'https://facebook.com/' . ltrim( $value, '@' );
							break;
					}
				}
				$profiles[] = $value;
			}
		}

		if ( ! empty( $profiles ) ) {
			if ( ! isset( $this->data['sameAs'] ) ) {
				$this->data['sameAs'] = array();
			}
			$this->data['sameAs'] = array_merge( $this->data['sameAs'], $profiles );
		}

		// 重複を削除
		if ( isset( $this->data['sameAs'] ) ) {
			$this->data['sameAs'] = array_values( array_unique( $this->data['sameAs'] ) );
		}
	}

	/**
	 * 著者の投稿情報を設定
	 *
	 * @return WPF_Person_Schema
	 */
	public function set_author_posts_info() {
		if ( ! $this->author_id ) {
			return $this;
		}

		// 著者の投稿数を取得
		$post_count = count_user_posts( $this->author_id, 'post', true );

		if ( $post_count > 0 ) {
			// 最新の投稿を取得
			$latest_posts = get_posts(
				array(
					'author'         => $this->author_id,
					'posts_per_page' => 5,
					'post_status'    => 'publish',
				)
			);

			if ( ! empty( $latest_posts ) ) {
				$this->data['creator'] = array();

				foreach ( $latest_posts as $post ) {
					$this->data['creator'][] = array(
						'@type'         => 'BlogPosting',
						'name'          => get_the_title( $post ),
						'url'           => get_permalink( $post ),
						'datePublished' => get_the_date( 'c', $post ),
					);
				}
			}
		}

		return $this;
	}
}
