<?php
/**
 * 見出しIDのカスタムスラッグ生成クラス
 *
 * スラッグ生成に使われているcocur/slugifyは日本語に対応していないため、'section' と言う文字列を接頭辞として使うように変更する
 * https://github.com/cocur/slugify/issues/46
 *
 * このクラスをスラッグ生成に使うように TOC\MarkupFixer に指示する
 *
 * 元のソースコード:
 * https://github.com/caseyamcl/toc/blob/master/src/UniqueSlugify.php
 *
 * @package wordpressfoundation
 */

use Cocur\Slugify\Slugify;
use Cocur\Slugify\SlugifyInterface;

// phpcs:disable

/**
 * UniqueSlugify creates slugs from text without repeating the same slug twice per instance
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class WPF_Slugifier implements SlugifyInterface {

	/**
	 * @var SlugifyInterface
	 */
	private $slugify;

	/**
	 * @var array
	 */
	private $used;

	/**
	 * Constructor
	 *
	 * @param SlugifyInterface|null $slugify
	 */
	public function __construct( ?SlugifyInterface $slugify = null ) {
		$this->used    = array();
		$this->slugify = $slugify ?: new Slugify();
	}

	/**
	 * Slugify
	 *
	 * @param string $string
	 * @param null   $options
	 * @return string
	 */
	public function slugify( $string, $options = null ): string {
        if ( ! empty( $string ) ) {
            return self::replace_safe_id( $string );
        }

		$slugged = 'section'; // 接頭辞をハードコード

		$count = 1;
		$orig  = $slugged;
		while ( in_array( $slugged, $this->used ) ) {
			$slugged = $orig . '-' . $count;
			$count++;
		}

		$this->used[] = $slugged;
		return $slugged;
	}

    /**
     * マルチバイト文字列や無効な文字を含む場合にUUIDに置換、
     * それ以外の場合は安全なID文字列に正規化
     *
     * @param string $string 文字列
     * @return string
     */
    public function replace_safe_id($string) {
        // 空文字列の場合
        if (empty($string)) {
            return 'section-' . self::generate_uuid();
        }

        // マルチバイト文字列かチェック
        if (strlen($string) !== mb_strlen($string, 'UTF-8')) {
            // UUIDを生成して返す
            return 'section-' . self::generate_uuid();
        }

        // スペースをハイフンに置き換え
        $string = str_replace(' ', '-', $string);

        // id属性として使えない文字を削除(英数字、ハイフン、アンダースコアのみ許可)
        $string = preg_replace('/[^a-zA-Z0-9_-]/', '', $string);

        // 連続するハイフンを1つにまとめる
        $string = preg_replace('/-+/', '-', $string);

        // 先頭と末尾のハイフンを削除
        $string = trim($string, '-');

        // 空文字列になった場合、または数字で始まる場合はUUIDを使用
        // (HTML5では数字始まりも許可されていますが、より安全にするため)
        if (empty($string) || preg_match('/^[0-9]/', $string)) {
            return 'section-' . self::generate_uuid();
        }

        return $string;
    }

    /**
     * UUID v4 生成関数
     *
     * @return string
     */
    public function generate_uuid() {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // version 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // variant
        
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
