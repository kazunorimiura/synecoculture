<?php
/**
 * WebP自動配信機能 - srcset対応版
 *
 * <picture>タグによる適切なWebP配信のみを実装
 * - レスポンシブ画像のsrcset対応
 * - ブラウザが自動でWebP/元画像を選択
 * - フォールバック対応
 * - SEO対応
 *
 * @package wordpressfoundation
 */

/**
 * WebP自動配信クラス - 改良版
 */
class WPF_Auto_WebP_Delivery {

	/**
	 * WebP生成フラグのメタキー
	 *
	 * @var string
	 */
	private $meta_key = '_wpf_generate_webp';

	/**
	 * コンストラクタ - pictureタグ配信を初期化
	 */
	public function __construct() {
		$this->init_picture_tag_delivery();
	}

	/**
	 * <picture>タグによるWebP配信の初期化
	 */
	private function init_picture_tag_delivery() {
		// wp_get_attachment_image を <picture> タグに置換
		add_filter( 'wp_get_attachment_image', array( $this, 'convert_to_picture_tag' ), 999, 5 );

		// エディタ内の画像も処理
		add_filter( 'the_content', array( $this, 'convert_content_images_to_picture' ), 999 );
	}

	/**
	 * wp_get_attachment_image を <picture> タグに変換（srcset対応版）
	 *
	 * @param string       $html HTMLの<img>要素。失敗した場合は空文字列
	 * @param int          $attachment_id 画像の添付ファイルID
	 * @param string|int[] $size 要求された画像サイズ。登録済みの画像サイズ名、または [幅, 高さ] のピクセル値配列
	 * @param bool         $icon 画像をアイコンとして扱うかどうか
	 * @param string[]     $attr 画像マークアップ用の属性値を格納した配列（キーは属性名）
	 *
	 * @return string HTMLマークアップ
	 */
	public function convert_to_picture_tag( $html, $attachment_id, $size, $icon, $attr ) {
		// WebPを使用すべきかチェック
		if ( ! $this->should_use_webp( $attachment_id ) ) {
			return $html;
		}

		// 元のimg要素からsrcset、sizes、media、width、heightを抽出してWebP版を構築
		$webp_data = $this->build_webp_srcset_from_html( $html, $attachment_id );

		if ( ! $webp_data ) {
			return $html;
		}

		// <source>要素の属性を構築
		$source_attributes = 'srcset="' . esc_attr( $webp_data['srcset'] ) . '" type="image/webp"';

		// sizes属性がある場合は追加
		if ( ! empty( $webp_data['sizes'] ) ) {
			$source_attributes .= ' sizes="' . esc_attr( $webp_data['sizes'] ) . '"';
		}

		// media属性がある場合は追加
		if ( ! empty( $webp_data['media'] ) ) {
			$source_attributes .= ' media="' . esc_attr( $webp_data['media'] ) . '"';
		}

		// width属性がある場合は追加
		if ( ! empty( $webp_data['width'] ) ) {
			$source_attributes .= ' width="' . esc_attr( $webp_data['width'] ) . '"';
		}

		// height属性がある場合は追加
		if ( ! empty( $webp_data['height'] ) ) {
			$source_attributes .= ' height="' . esc_attr( $webp_data['height'] ) . '"';
		}

		// <picture>タグを生成
		$picture_html  = '<picture>';
		$picture_html .= '<source ' . $source_attributes . '>';
		$picture_html .= $html; // 元のimgタグをフォールバックとして使用
		$picture_html .= '</picture>';

		return $picture_html;
	}

	/**
	 * コンテンツ内の画像を<picture>タグに変換（srcset対応版）
	 *
	 * @param string $content 投稿コンテンツ
	 * @return string 変換後のコンテンツ
	 */
	public function convert_content_images_to_picture( $content ) {
		// wp-image-{ID} クラスを持つ画像を検索
		$pattern = '/<img[^>]+wp-image-(\d+)[^>]*>/i';

		return preg_replace_callback(
			$pattern,
			function( $matches ) {
				$attachment_id = intval( $matches[1] );

				// WebPを使用すべきかチェック
				if ( ! $this->should_use_webp( $attachment_id ) ) {
					return $matches[0];
				}

				$img_tag = $matches[0];

				// WebP srcset、sizes、media、width、heightを構築
				$webp_data = $this->build_webp_srcset_from_html( $img_tag, $attachment_id );

				if ( $webp_data ) {
					// <source>要素の属性を構築
					$source_attributes = 'srcset="' . esc_attr( $webp_data['srcset'] ) . '" type="image/webp"';

					// sizes属性がある場合は追加
					if ( ! empty( $webp_data['sizes'] ) ) {
						$source_attributes .= ' sizes="' . esc_attr( $webp_data['sizes'] ) . '"';
					}

					// media属性がある場合は追加
					if ( ! empty( $webp_data['media'] ) ) {
						$source_attributes .= ' media="' . esc_attr( $webp_data['media'] ) . '"';
					}

					// width属性がある場合は追加
					if ( ! empty( $webp_data['width'] ) ) {
						$source_attributes .= ' width="' . esc_attr( $webp_data['width'] ) . '"';
					}

					// height属性がある場合は追加
					if ( ! empty( $webp_data['height'] ) ) {
						$source_attributes .= ' height="' . esc_attr( $webp_data['height'] ) . '"';
					}

					// <picture>タグを生成
					$picture_html  = '<picture>';
					$picture_html .= '<source ' . $source_attributes . '>';
					$picture_html .= $img_tag;
					$picture_html .= '</picture>';

					return $picture_html;
				}

				return $img_tag;
			},
			$content
		);
	}

	/**
	 * HTMLからWebP版srcsetを構築
	 *
	 * @param string $html img要素のHTML
	 * @param int    $attachment_id 添付ファイルID
	 * @return array|false WebP srcset、sizes、media、width、heightの配列またはfalse
	 */
	private function build_webp_srcset_from_html( $html, $attachment_id ) {
		// srcset属性を抽出
		$srcset = $this->extract_srcset_from_html( $html );

		if ( $srcset ) {
			// sizes、media、width、height属性も抽出
			$sizes  = $this->extract_sizes_from_html( $html );
			$media  = $this->extract_media_from_html( $html );
			$width  = $this->extract_width_from_html( $html );
			$height = $this->extract_height_from_html( $html );

			// srcsetからWebP版を構築
			$webp_srcset = $this->build_webp_srcset_from_srcset( $srcset );

			if ( $webp_srcset ) {
				return array(
					'srcset' => $webp_srcset,
					'sizes'  => $sizes,
					'media'  => $media,
					'width'  => $width,
					'height' => $height,
				);
			}
		}

		// srcset属性がない場合はsrc属性から単一URLを取得
		if ( preg_match( '/src=["\']([^"\']+)["\']/', $html, $src_matches ) ) {
			$original_src = $src_matches[1];
			$webp_url     = $this->get_webp_url( $original_src );

			if ( $webp_url ) {
				return array(
					'srcset' => $webp_url,
					'sizes'  => null,
					'media'  => $this->extract_media_from_html( $html ),
					'width'  => $this->extract_width_from_html( $html ),
					'height' => $this->extract_height_from_html( $html ),
				);
			}
		}

		return false;
	}

	/**
	 * HTMLからsrcset属性を抽出
	 *
	 * @param string $html img要素のHTML
	 * @return string|false srcset値またはfalse
	 */
	private function extract_srcset_from_html( $html ) {
		if ( preg_match( '/srcset=["\']([^"\']+)["\']/', $html, $matches ) ) {
			return $matches[1];
		}
		return false;
	}

	/**
	 * HTMLからsizes属性を抽出
	 *
	 * @param string $html img要素のHTML
	 * @return string|false sizes値またはfalse
	 */
	private function extract_sizes_from_html( $html ) {
		if ( preg_match( '/sizes=["\']([^"\']+)["\']/', $html, $matches ) ) {
			return $matches[1];
		}
		return false;
	}

	/**
	 * HTMLからmedia属性を抽出
	 *
	 * @param string $html img要素のHTML
	 * @return string|false media値またはfalse
	 */
	private function extract_media_from_html( $html ) {
		if ( preg_match( '/media=["\']([^"\']+)["\']/', $html, $matches ) ) {
			return $matches[1];
		}
		return false;
	}

	/**
	 * HTMLからwidth属性を抽出
	 *
	 * @param string $html img要素のHTML
	 * @return string|false width値またはfalse
	 */
	private function extract_width_from_html( $html ) {
		if ( preg_match( '/width=["\']?(\d+)["\']?/', $html, $matches ) ) {
			return $matches[1];
		}
		return false;
	}

	/**
	 * HTMLからheight属性を抽出
	 *
	 * @param string $html img要素のHTML
	 * @return string|false height値またはfalse
	 */
	private function extract_height_from_html( $html ) {
		if ( preg_match( '/height=["\']?(\d+)["\']?/', $html, $matches ) ) {
			return $matches[1];
		}
		return false;
	}

	/**
	 * srcset文字列からWebP版srcsetを構築
	 *
	 * @param string $srcset 元のsrcset文字列
	 * @return string|false WebP版srcsetまたはfalse
	 */
	private function build_webp_srcset_from_srcset( $srcset ) {
		$webp_sources = array();

		// srcsetを個別のソースに分割（カンマ区切り）
		$sources = explode( ',', $srcset );

		foreach ( $sources as $source ) {
			$source = trim( $source );

			// URLと幅指定子を分離
			if ( preg_match( '/^(\S+)\s+(.+)$/', $source, $matches ) ) {
				$url        = $matches[1];
				$descriptor = $matches[2]; // "1024w" など

				// WebP版URLを生成
				$webp_url = $this->get_webp_url( $url );

				if ( $webp_url ) {
					$webp_sources[] = $webp_url . ' ' . $descriptor;
				}
			} else {
				// 幅指定子がない場合（単一URL）
				$webp_url = $this->get_webp_url( $source );
				if ( $webp_url ) {
					$webp_sources[] = $webp_url;
				}
			}
		}

		// WebP版が1つ以上ある場合はsrcsetとして返す
		return ! empty( $webp_sources ) ? implode( ', ', $webp_sources ) : false;
	}

	/**
	 * WebPを使用すべきかチェック
	 *
	 * @param int $attachment_id 添付ファイルID
	 * @return bool WebPを使用する場合はtrue
	 */
	private function should_use_webp( $attachment_id ) {
		$generate_webp = get_post_meta( $attachment_id, $this->meta_key, true );
		return '1' === $generate_webp;
	}

	/**
	 * 元のURLからWebPのURLを生成
	 *
	 * @param string $original_url 元画像のURL
	 * @return string|false WebP URLまたはfalse（ファイルが存在しない場合）
	 */
	private function get_webp_url( $original_url ) {
		// ファイル拡張子をwebpに変更
		$path_info = pathinfo( $original_url );
		$webp_url  = $path_info['dirname'] . '/' . $path_info['filename'] . '.webp';

		// WebPファイルの物理的な存在を確認
		$upload_dir = wp_upload_dir();
		$webp_path  = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $webp_url );

		return file_exists( $webp_path ) ? $webp_url : false;
	}
}
