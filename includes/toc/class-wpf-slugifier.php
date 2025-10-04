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
}
