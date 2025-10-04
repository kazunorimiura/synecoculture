<?php

/**
 * WPF_Template_Tags::get_the_modified_date_tag のユニットテスト。
 *
 * @group template_tags
 * @covers WPF_Template_Tags
 * @coversDefaultClass WPF_Template_Tags
 */
class TestGetTheModifiedDateTag extends WPF_UnitTestCase {

	/**
	 * 個別投稿ページ
	 *
	 * @covers ::get_the_modified_date_tag
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_modified_date_tag_with_post() {
		$post_id = self::factory()->post->create( array( 'post_date' => '2012-12-12' ) );

		$this->go_to( get_permalink( $post_id ) );

		$this->assertQueryTrue( 'is_single', 'is_singular' );

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'<span class="date"><time datetime="2012-12-12T00:00:00+00:00">2012年12月12日</time></span>'
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				WPF_Template_Tags::get_the_modified_date_tag()
			)
		);
	}

	/**
	 * 個別投稿ページ
	 *
	 * テスト条件:
	 * - 投稿がない場合 => false が返される。
	 *
	 * @covers ::get_the_modified_date_tag
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_modified_date_tag_with_post_and_no_post() {
		$this->assertSame( '', WPF_Template_Tags::get_the_modified_date_tag() );
	}

	/**
	 * CPT個別投稿ページ
	 *
	 * @covers ::get_the_modified_date_tag
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_modified_date_tag_with_cpt_post() {
		register_post_type(
			'cpt',
			array(
				'public' => true,
			)
		);

		$post_id = self::factory()->post->create(
			array(
				'post_type' => 'cpt',
				'post_date' => '2012-12-12',
			)
		);

		$this->go_to( get_permalink( $post_id ) );

		$this->assertQueryTrue( 'is_single', 'is_singular' );

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'<span class="date"><time datetime="2012-12-12T00:00:00+00:00">2012年12月12日</time></span>'
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				WPF_Template_Tags::get_the_modified_date_tag()
			)
		);

		_unregister_post_type( 'cpt' );
	}
}
