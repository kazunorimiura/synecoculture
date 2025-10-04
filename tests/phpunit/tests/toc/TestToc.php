<?php

/**
 * WPF_Toc のユニットテスト。
 *
 * @group toc
 * @covers WPF_Toc
 * @coversDefaultClass WPF_Toc
 */
class TestToc extends WPF_UnitTestCase {

	/**
	 * @covers ::markup_fixer
	 * @covers ::get_html_menu
	 * @preserveGlobalState disabled
	 */
	public function test_toc() {
		$content = <<<END
<h1>This is a header tag with no anchor id</h1>
<p>Lorum ipsum doler sit amet</p>
<h2 id="foo">This is a header tag with an anchor id</h2>
<p>Stuff here</p>
<h3 id="bar">This is a header tag with an anchor id</h3>
END;

		$wpf_toc = new WPF_Toc();
		$content = $wpf_toc->markup_fixer( $content );
		$toc     = $wpf_toc->get_html_menu( $content );

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				<<<END
<ul>
    <li class="first last">
        <a href="#section">This is a header tag with no anchor id</a>
        <ul class="menu_level_1">
            <li class="first last">
                <a href="#section-1">This is a header tag with an anchor id</a>
                <ul class="menu_level_2">
                    <li class="first last">
                        <a href="#section-2">This is a header tag with an anchor id</a>
                    </li>
                </ul>
            </li>
        </ul>
    </li>
</ul>
END
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				$toc
			)
		);
	}

	/**
	 * @covers ::markup_fixer
	 * @covers ::get_html_menu
	 * @preserveGlobalState disabled
	 */
	public function test_toc_with_no_toc() {
		$content = <<<END
<p>Lorum ipsum doler sit amet</p>
<p>Stuff here</p>
END;

		$wpf_toc = new WPF_Toc();
		$content = $wpf_toc->markup_fixer( $content );
		$toc     = $wpf_toc->get_html_menu( $content );

		$this->assertSame( '', $toc );
	}

	/**
	 * @covers ::markup_fixer
	 * @covers ::get_html_menu
	 * @preserveGlobalState disabled
	 */
	public function test_toc_body_class() {
		$post = self::factory()->post->create(
			array(
				'post_content' => <<<END
<h1>This is a header tag with no anchor id</h1>
<p>Lorum ipsum doler sit amet</p>
<h2 id="foo">This is a header tag with an anchor id</h2>
<p>Stuff here</p>
<h3 id="bar">This is a header tag with an anchor id</h3>
END
			,
			)
		);

		$this->go_to( get_permalink( $post ) );

		$this->assertSame(
			array( 'post-has-headings' ),
			WPF_Toc::body_class( array() )
		);

		$post = self::factory()->post->create();

		$this->go_to( get_permalink( $post ) );

		$this->assertSame(
			array(),
			WPF_Toc::body_class( array() )
		);
	}
}
