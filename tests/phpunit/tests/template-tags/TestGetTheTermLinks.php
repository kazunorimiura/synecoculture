<?php

/**
 * WPF_Template_Tags::get_the_term_links のユニットテスト。
 *
 * @group template_tags
 * @covers WPF_Template_Tags
 * @coversDefaultClass WPF_Template_Tags
 */
class TestGetTheTermLinks extends WPF_UnitTestCase {

	/**
	 * @covers ::get_the_term_links
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_term_links_with_category() {
		$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );
		$cat_id  = self::factory()->category->create( array( 'name' => 'Foo' ) );
		wp_set_post_categories( $post_id, array( $cat_id ) );

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'
                <a class="pill pill--acbd18db4cc2f85cedef654fccc4a4d8" href="http://' . WP_TESTS_DOMAIN . '/category/foo/">Foo</a>
                '
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				WPF_Template_Tags::get_the_term_links( $post_id, 'category' )
			)
		);
	}

	/**
	 * @covers ::get_the_term_links
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_term_links_with_tag() {
		$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );
		$tag_id  = self::factory()->tag->create( array( 'name' => 'Foo' ) );
		wp_set_post_tags( $post_id, array( $tag_id ) );

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'
                <a class="pill pill--acbd18db4cc2f85cedef654fccc4a4d8" href="http://' . WP_TESTS_DOMAIN . '/tag/foo/">Foo</a>
                '
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				WPF_Template_Tags::get_the_term_links( $post_id, 'post_tag' )
			)
		);
	}

	/**
	 * WPF_Template_Tags::get_the_term_links にtaxを明示的に指定しない場合の挙動を確認。
	 *
	 * @covers ::get_the_term_links
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_term_links_with_unselected_tax() {
		$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );
		$cat_id  = self::factory()->category->create( array( 'name' => 'Foo' ) );
		wp_set_post_categories( $post_id, array( $cat_id ) );

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'
                <a class="pill pill--acbd18db4cc2f85cedef654fccc4a4d8" href="http://' . WP_TESTS_DOMAIN . '/category/foo/">Foo</a>
                '
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				WPF_Template_Tags::get_the_term_links( $post_id )
			)
		);
	}

	/**
	 * @covers ::get_the_term_links
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_term_links_with_ctax() {
		register_taxonomy( self::$taxonomy, self::$post_type, array( 'public' => true ) );
		register_post_type( self::$post_type, array( 'public' => true ) );

		global $wp_rewrite;
		$wp_rewrite->flush_rules();

		$post_id = self::factory()->post->create( array( 'post_type' => self::$post_type ) );
		$term_id = self::factory()->term->create(
			array(
				'taxonomy' => self::$taxonomy,
				'name'     => 'Foo',
			)
		);
		wp_set_post_terms( $post_id, array( $term_id ), self::$taxonomy );

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'
                <a class="pill pill--acbd18db4cc2f85cedef654fccc4a4d8" href="http://' . WP_TESTS_DOMAIN . '/ctax/foo/">Foo</a>
                '
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				WPF_Template_Tags::get_the_term_links( $post_id, self::$taxonomy )
			)
		);
	}

	/**
	 * taxにオブジェクトタイプ（cpt）が関連づけられていない場合の挙動を確認。
	 *
	 * @covers ::get_the_term_links
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_term_links_with_no_object_type() {
		register_taxonomy( self::$taxonomy, '', array( 'public' => true ) );
		register_post_type( self::$post_type, array( 'public' => true ) );

		global $wp_rewrite;
		$wp_rewrite->flush_rules();

		$post_id = self::factory()->post->create(
			array(
				'post_type' => self::$post_type,
			)
		);

		$this->assertSame( null, WPF_Template_Tags::get_the_term_links( $post_id, self::$taxonomy ) );
	}
}
