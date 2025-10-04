<?php

/**
 * WPF_Utils::get_the_term_description のユニットテスト。
 *
 * @group utils
 * @covers WPF_Utils
 * @coversDefaultClass WPF_Utils
 */
class TestGetTheTermDescription extends WPF_UnitTestCase {

	/**
	 * 個別投稿に設定されたタームのデスクリプションが取得できるか。
	 *
	 * @covers ::get_the_term_description
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_term_description() {
		$cat_id  = self::factory()->category->create( array( 'description' => 'Foo' ) );
		$tag_id  = self::factory()->tag->create( array( 'description' => 'Bar' ) );
		$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );
		wp_set_post_categories( $post_id, array( $cat_id ) );

		$this->assertSame( 'Foo', WPF_Utils::get_the_term_description( $cat_id, 'category' ) );
		$this->assertSame( 'Bar', WPF_Utils::get_the_term_description( $tag_id, 'post_tag' ) );

		$this->go_to( get_category_link( $cat_id ) );

		$this->assertSame( 'Foo', WPF_Utils::get_the_term_description() );
	}

	/**
	 * CPT個別投稿に設定されたタームのデスクリプションが取得できるか。
	 *
	 * @covers ::get_the_term_description
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_term_description_with_cpt() {
		register_taxonomy( self::$taxonomy, self::$post_type, array( 'public' => true ) );
		register_post_type( self::$post_type, array( 'public' => true ) );

		$term_id = self::factory()->term->create(
			array(
				'taxonomy'    => self::$taxonomy,
				'description' => 'Foo',
			)
		);
		$post_id = self::factory()->post->create( array( 'post_type' => self::$post_type ) );
		wp_set_post_terms( $post_id, array( $term_id ), self::$taxonomy );

		$this->assertSame( 'Foo', WPF_Utils::get_the_term_description( $term_id, self::$taxonomy ) );

		$this->go_to( get_term_link( $term_id ) );

		$this->assertSame( 'Foo', WPF_Utils::get_the_term_description() );
	}

	/**
	 * 現在のクエリがない場合、空を返すか。
	 *
	 * @covers ::get_the_term_description
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_term_description_with_no_term() {
		$this->assertSame( '', WPF_Utils::get_the_term_description() );
	}
}
