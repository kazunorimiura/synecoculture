<?php

/**
 * WPF_Utils::get_object_public_taxonomies のユニットテスト。
 *
 * @group utils
 * @covers WPF_Utils
 * @coversDefaultClass WPF_Utils
 */
class TestGetObjectPublicTaxonomies extends WPF_UnitTestCase {

	/**
	 * @covers ::get_object_public_taxonomies
	 * @preserveGlobalState disabled
	 */
	public function test_get_object_public_taxonomies() {
		$this->assertSame( array( 'category', 'post_tag' ), WPF_Utils::get_object_public_taxonomies( 'post' ) );
	}

	/**
	 * @covers ::get_object_public_taxonomies
	 * @preserveGlobalState disabled
	 */
	public function test_get_object_public_taxonomies_with_cpt() {
		register_taxonomy( 'ctax_1', '', array( 'public' => true ) );
		register_taxonomy( 'ctax_2', '', array( 'public' => true ) );
		register_post_type(
			'cpt',
			array(
				'public'     => true,
				'taxonomies' => array(
					'ctax_1',
					'ctax_2',
				),
			)
		);

		$taxonomies = WPF_Utils::get_object_public_taxonomies( 'cpt' );

		// 配列に両方のタクソノミーが含まれていることを確認
		$this->assertContains( 'ctax_1', $taxonomies );
		$this->assertContains( 'ctax_2', $taxonomies );

		// 配列の長さが2であることも確認
		$this->assertCount( 2, $taxonomies );
	}

	/**
	 * @covers ::get_object_public_taxonomies
	 * @preserveGlobalState disabled
	 */
	public function test_get_object_public_taxonomies_with_public_false() {
		register_taxonomy( 'ctax_1', '', array( 'public' => false ) );
		register_taxonomy( 'ctax_2', '', array( 'public' => true ) );
		register_post_type(
			'cpt',
			array(
				'public'     => true,
				'taxonomies' => array(
					'ctax_1',
					'ctax_2',
				),
			)
		);

		global $wp_rewrite;
		$wp_rewrite->flush_rules();

		$taxonomies = WPF_Utils::get_object_public_taxonomies( 'cpt' );

		// 配列にタクソノミーが含まれていることを確認
		$this->assertContains( 'ctax_2', $taxonomies );

		// 配列の長さが1であることも確認
		$this->assertCount( 1, $taxonomies );
	}

	/**
	 * @covers ::get_object_public_taxonomies
	 * @preserveGlobalState disabled
	 */
	public function test_get_object_public_taxonomies_with_invalid_object_type() {
		register_taxonomy( 'ctax_1', '', array( 'public' => true ) );
		register_taxonomy( 'ctax_2', '', array( 'public' => true ) );
		register_post_type(
			'cpt',
			array(
				'public'     => true,
				'taxonomies' => array(
					'ctax_1',
					'ctax_2',
				),
			)
		);

		global $wp_rewrite;
		$wp_rewrite->flush_rules();

		$taxonomies = WPF_Utils::get_object_public_taxonomies( 'foo' );

		// 配列が空であることを確認
		$this->assertCount( 0, $taxonomies );
	}
}
