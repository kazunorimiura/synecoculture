<?php
/**
 * 目次コンポーネント
 *
 * @package wordpressfoundation
 */

if ( ! class_exists( 'WPF_Toc' ) ) {

	/**
	 * 目次
	 *
	 * @since 0.1.0
	 */
	class WPF_Toc {
		/**
		 * TOC\MarkupFixer
		 *
		 * @var TOC\MarkupFixer
		 */
		private $markup_fixer;

		/**
		 * TOC\TocGenerator
		 *
		 * @var TOC\TocGenerator
		 */
		private $toc_generator;

		/**
		 * コンストラクタ
		 *
		 * @since 0.1.0
		 */
		public function __construct() {
			$this->markup_fixer  = new TOC\MarkupFixer( null, new WPF_Slugifier() );
			$this->toc_generator = new TOC\TocGenerator();

			add_filter( 'body_class', array( $this, 'body_class' ), 10, 1 );
		}

		/**
		 * 見出し要素にアンカーを付与する。
		 * なお、すでに付与されている場合は無視する。
		 *
		 * @link https://github.com/caseyamcl/toc/blob/master/src/MarkupFixer.php
		 *
		 * @since 0.1.0
		 * @param string $content 対象の文字列。
		 * @return string
		 **/
		public function markup_fixer( $content ) {
			return $this->markup_fixer->fix( $content );
		}

		/**
		 * 目次要素を作成する。
		 *
		 * @link https://github.com/caseyamcl/toc/blob/master/src/MarkupFixer.php
		 *
		 * @since 0.1.0
		 * @param string                 $markup 目次項目を取得するためのコンテンツ。
		 * @param int                    $top_level 最上位のレベル (1〜6)
		 * @param int                    $depth 見出しレベルの深さ (1〜6)
		 * @param RendererInterface|null $renderer RendererInterface オブジェクト。
		 * @param bool                   $ordered 順序付きリストにするかどうか。
		 * @return string HTML <li> アイテム。
		 */
		public function get_html_menu( $markup, $top_level = 1, $depth = 2, $renderer = null, $ordered = false ) {
			return $this->toc_generator->getHtmlMenu( $markup, $top_level, $depth, $renderer, $ordered );
		}

		/**
		 * TOC\MarkupFixer() 済みのコンテンツを返す。
		 *
		 * get_the_content が the_content と同じ内容を返すようにするため、$this->markup_fixer
		 * 以外のロジックを the_content からコピペしている。これにより、テンプレート内でのコンテンツ
		 * の呼び出しを一度で済ませられるようにしつつ、get_html_menu を取得できるようにしている。
		 *
		 * @link https://developer.wordpress.org/reference/functions/the_content/
		 *
		 * @param string  $more_link_text Moreテキスト。
		 * @param boolean $strip_teaser 本文の前にティーザー・コンテンツを掲載する。
		 * @param string  $additional_content 追加のコンテンツ
		 * @return string
		 */
		public function get_the_content( $more_link_text = null, $strip_teaser = false, $additional_content = '' ) {
			/**
			 * ショートコードなどの展開が行われた状態のコンテンツを取得する（get_the_contentはフィルターを通過しない）
			 *
			 * @see https://developer.wordpress.org/reference/functions/get_the_content/
			 */
			$content = apply_filters( 'the_content', get_the_content( $more_link_text, $strip_teaser ) . $additional_content ); // phpcs:ignore

			$content = $this->markup_fixer( $content );
			$content = str_replace( ']]>', ']]&gt;', $content );
			return $content;
		}

		/**
		 * body 要素に目次要素の存在有無を示す class 属性を追加する。
		 *
		 * @param string[] $classes ボディクラス名の配列。
		 * @return string[]
		 */
		public static function body_class( $classes ) {
			if ( is_singular() ) {
				global $post;

				$content  = get_post_field( 'post_content', $post->ID );
				$from_tag = 1;
				$to_tag   = 6;

				preg_match_all( '/<h([' . $from_tag . '-' . $to_tag . '])([^<]*)>(.*)<\/h[' . $from_tag . '-' . $to_tag . ']>/', $content, $matches );

				// 投稿に見出しがある場合はTOCが存在することを意味するクラスを追加（ページヘッダーの幅をページコンテンツの幅に合わせるのに使用）。
				if ( count( $matches[1] ) ) {
					$classes[] = 'post-has-headings';
				}
			}

			return $classes;
		}
	}
}
