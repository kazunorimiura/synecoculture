<?php

/**
 * WPF_Template_Tags::get_the_current_term のユニットテスト。
 *
 * @group template_tags
 * @covers WPF_Template_Tags
 * @coversDefaultClass WPF_Template_Tags
 */
class TestGetTheCurrentTerm extends WPF_UnitTestCase {

	/**
	 * カテゴリアーカイブ
	 *
	 * @covers ::get_the_current_term
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_current_term_with_category_archive() {
		$term  = self::factory()->term->create(
			array(
				'taxonomy' => 'category',
				'name'     => 'foo',
			)
		);
		$posts = self::factory()->post->create_many( 10 );
		foreach ( $posts as $post_id ) {
			wp_set_post_terms( $post_id, array( $term ), 'category' );
		}

		$this->go_to( get_category_link( $term ) );

		$this->assertQueryTrue( 'is_archive', 'is_category' );

		$this->assertSame(
			'foo',
			WPF_Template_Tags::get_the_current_term()->name
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertQueryTrue( 'is_archive', 'is_category', 'is_paged' );

		$this->assertSame(
			'foo',
			WPF_Template_Tags::get_the_current_term()->name
		);
	}

	/**
	 * タグアーカイブ
	 *
	 * @covers ::get_the_current_term
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_current_term_with_tag_archive() {
		$term  = self::factory()->term->create(
			array(
				'taxonomy' => 'post_tag',
				'name'     => 'foo',
			)
		);
		$posts = self::factory()->post->create_many( 10 );
		foreach ( $posts as $post_id ) {
			wp_set_post_terms( $post_id, array( $term ), 'post_tag' );
		}

		$this->go_to( get_tag_link( $term ) );

		$this->assertQueryTrue( 'is_archive', 'is_tag' );

		$this->assertSame(
			'foo',
			WPF_Template_Tags::get_the_current_term()->name
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertQueryTrue( 'is_archive', 'is_tag', 'is_paged' );

		$this->assertSame(
			'foo',
			WPF_Template_Tags::get_the_current_term()->name
		);
	}

	/**
	 * ctaxアーカイブ（ヒエラルキーなし）
	 *
	 * @covers ::get_the_current_term
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_current_term_with_ctax_archive() {
		register_taxonomy(
			'ctax',
			'post',
			array(
				'public' => true,
			)
		);

		$term  = self::factory()->term->create(
			array(
				'taxonomy' => 'ctax',
				'name'     => 'foo',
			)
		);
		$posts = self::factory()->post->create_many( 10 );
		foreach ( $posts as $post_id ) {
			wp_set_post_terms( $post_id, array( $term ), 'ctax' );
		}

		$this->go_to( get_term_link( $term ) );

		$this->assertQueryTrue( 'is_archive', 'is_tax' );

		$this->assertSame(
			'foo',
			WPF_Template_Tags::get_the_current_term()->name
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertQueryTrue( 'is_archive', 'is_tax', 'is_paged' );

		$this->assertSame(
			'foo',
			WPF_Template_Tags::get_the_current_term()->name
		);

		_unregister_taxonomy( 'ctax' );
	}

	/**
	 * ctaxアーカイブ（ヒエラルキーあり）
	 *
	 * @covers ::get_the_current_term
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_current_term_with_ctax_archive_has_hierarchical_tax() {
		register_taxonomy(
			'ctax',
			'post',
			array(
				'public'       => true,
				'hierarchical' => true,
				'rewrite'      => array(
					'hierarchical' => true,
				),
			)
		);

		$term  = self::factory()->term->create(
			array(
				'taxonomy' => 'ctax',
				'name'     => 'foo',
			)
		);
		$posts = self::factory()->post->create_many( 10 );
		foreach ( $posts as $post_id ) {
			wp_set_post_terms( $post_id, array( $term ), 'ctax' );
		}

		$this->go_to( get_term_link( $term ) );

		$this->assertQueryTrue( 'is_archive', 'is_tax' );

		$this->assertSame(
			'foo',
			WPF_Template_Tags::get_the_current_term()->name
		);

		$this->go_to( next_posts( 0, false ) );

		$this->assertQueryTrue( 'is_archive', 'is_tax', 'is_paged' );

		$this->assertSame(
			'foo',
			WPF_Template_Tags::get_the_current_term()->name
		);

		_unregister_taxonomy( 'ctax' );
	}
}
