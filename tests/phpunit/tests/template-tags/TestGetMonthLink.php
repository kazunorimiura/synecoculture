<?php

/**
 * WPF_Template_Tags::get_month_link のユニットテスト。
 *
 * @group template_tags
 * @covers WPF_Template_Tags
 * @coversDefaultClass WPF_Template_Tags
 */
class TestGetMonthLink extends WPF_UnitTestCase {

	/**
	 * デフォルトの投稿タイプで年月アーカイブのパーマリンクが返されるか。
	 *
	 * @covers ::get_month_link
	 * @preserveGlobalState disabled
	 */
	public function test_get_month_link() {
		$post_ids = self::factory()->post->create_many(
			10,
			array(
				'post_type' => 'post',
				'post_date' => '2012-12-12',
			)
		);

		$this->assertSame(
			home_url( user_trailingslashit( '/2012/12' ) ),
			WPF_Template_Tags::get_month_link( 2012, 12 )
		);
	}

	/**
	 * パーマリンク構造がデフォルトの場合、年月アーカイブのUglyパーマリンクが返されるか。
	 *
	 * @covers ::get_month_link
	 * @preserveGlobalState disabled
	 */
	public function test_get_month_link_with_ugly_permalink() {
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$post_ids = self::factory()->post->create_many(
			10,
			array(
				'post_type' => 'post',
				'post_date' => '2012-12-12',
			)
		);

		$this->assertSame(
			home_url( user_trailingslashit( '?m=201212' ) ),
			WPF_Template_Tags::get_month_link( 2012, 12 )
		);
	}

	/**
	 * CPTでCPT年月アーカイブのパーマリンクが返されるか。
	 *
	 * @covers ::get_month_link
	 * @preserveGlobalState disabled
	 */
	public function test_get_month_link_with_cpt() {
		register_post_type(
			self::$post_type,
			array(
				'labels'      => array(
					'name' => 'Foo',
				),
				'public'      => true,
				'has_archive' => true,
			)
		);

		$post_ids = self::factory()->post->create_many(
			10,
			array(
				'post_type' => self::$post_type,
				'post_date' => '2012-12-12',
			)
		);

		$this->assertSame(
			home_url( user_trailingslashit( '/' . self::$post_type . '/date/2012/12' ) ),
			WPF_Template_Tags::get_month_link( 2012, 12, self::$post_type )
		);
	}

	/**
	 * CPTパーマリンク構造が未設定の場合、CPT年月アーカイブのUglyパーマリンクが返されるか。
	 *
	 * @covers ::get_month_link
	 * @preserveGlobalState disabled
	 */
	public function test_get_month_link_with_cpt_and_ugly_permalink() {
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		register_post_type(
			self::$post_type,
			array(
				'labels'      => array(
					'name' => 'Foo',
				),
				'public'      => true,
				'has_archive' => true,
			)
		);

		$post_ids = self::factory()->post->create_many(
			10,
			array(
				'post_type' => self::$post_type,
				'post_date' => '2012-12-12',
			)
		);

		$this->assertSame(
			home_url( user_trailingslashit( '/?m=201212&post_type=cpt' ) ),
			WPF_Template_Tags::get_month_link( 2012, 12, self::$post_type )
		);
	}

	/**
	 * 存在しない投稿タイプを渡した場合、WPエラーが返されるか。
	 *
	 * @covers ::get_month_link
	 * @preserveGlobalState disabled
	 */
	public function test_get_month_link_raise_wp_error() {
		$post_ids = self::factory()->post->create_many(
			10,
			array(
				'post_type' => 'post',
				'post_date' => '2012-12-12',
			)
		);

		$actual = WPF_Template_Tags::get_month_link( 2012, 12, self::$post_type );

		$this->assertInstanceOf(
			'WP_Error',
			$actual
		);
		$this->assertSame(
			array(
				'post_type_error' => array( 'Post type does not exist.' ),
			),
			$actual->errors
		);
		$this->assertSame(
			array(
				'post_type_error' => 'cpt',
			),
			$actual->error_data
		);
	}
}
