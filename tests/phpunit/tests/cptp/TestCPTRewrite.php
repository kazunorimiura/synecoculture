<?php

/**
 * WPF_CPT_Rewrite クラスのユニットテスト。
 *
 * @group rewrite
 * @covers WPF_CPT_Rewrite
 * @coversDefaultClass WPF_CPT_Rewrite
 */
class TestCPTRewrite extends WPF_UnitTestCase {

	/**
	 * 各種パーマリンク構造のリライトルールを設定できているか。
	 *
	 * @covers ::register_post_type_rules
	 * @covers ::change_extra_parmastruct_priority
	 */
	public function test_register_post_type_rules() {
		register_post_type(
			self::$post_type,
			array(
				'public'     => true,
				'taxonomies' => array( 'category' ),
			)
		);

		global $wp_rewrite;
		$wp_rewrite->flush_rules();

		$permastruct_keys = array_keys( $wp_rewrite->extra_permastructs );

		$this->assertTrue( in_array( WPF_CPT_Rewrite::get_author_permastruct_name( self::$post_type ), $permastruct_keys, true ) );
		$this->assertTrue( in_array( WPF_CPT_Rewrite::get_date_permastruct_name( self::$post_type ), $permastruct_keys, true ) );
		$this->assertTrue( in_array( WPF_CPT_Rewrite::get_cpt_permastruct_name( self::$post_type ), $permastruct_keys, true ) );

		// ルールの優先順位を確認。
		$this->assertTrue( array_search( WPF_CPT_Rewrite::get_author_permastruct_name( self::$post_type ), $permastruct_keys, true ) < array_search( WPF_CPT_Rewrite::get_date_permastruct_name( self::$post_type ), $permastruct_keys, true ) );
		$this->assertTrue( array_search( WPF_CPT_Rewrite::get_date_permastruct_name( self::$post_type ), $permastruct_keys, true ) < array_search( WPF_CPT_Rewrite::get_cpt_permastruct_name( self::$post_type ), $permastruct_keys, true ) );

		_unregister_post_type( self::$post_type );
	}

	/**
	 * リライトを無効にしている場合、早期に返却されるか。
	 *
	 * @covers ::register_post_type_rules
	 */
	public function test_register_post_type_rules_with_rewrite_false() {
		register_post_type(
			self::$post_type,
			array(
				'rewrite' => false,
				'public'  => true,
			)
		);

		global $wp_rewrite;
		$wp_rewrite->flush_rules();

		$cpt_permastruct = WPF_CPTP_Utils::get_permalink_structure( self::$post_type );
		$this->assertSame( '', $cpt_permastruct ); // CPTのパーマリンクが設定されないはず。

		_unregister_post_type( self::$post_type );
	}

	/**
	 * ビルトイン投稿タイプの場合、早期に返却されるか。
	 *
	 * @covers ::register_post_type_rules
	 */
	public function test_register_post_type_rules_with_builtin_post_type() {
		register_post_type(
			self::$post_type,
			array(
				'_builtin' => true,
				'public'   => true,
			)
		);

		global $wp_rewrite;
		$wp_rewrite->flush_rules();

		$cpt_permastruct = WPF_CPTP_Utils::get_permalink_structure( self::$post_type );
		$this->assertSame( '', $cpt_permastruct ); // CPTのパーマリンクが設定されないはず。

		_unregister_post_type( self::$post_type );
	}

	/**
	 * パーマリンク構造を使用していない場合、早期に返却されるか。
	 *
	 * @covers ::register_post_type_rules
	 */
	public function test_register_post_type_rules_with_no_permalinks() {
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' ); // パーマリンク構造を削除。

		register_post_type(
			self::$post_type,
			array(
				'public' => true,
			)
		);

		$wp_rewrite->flush_rules();

		$cpt_permastruct = WPF_CPTP_Utils::get_permalink_structure( self::$post_type );
		$this->assertSame( '', $cpt_permastruct ); // CPTのパーマリンクが設定されないはず。

		_unregister_post_type( self::$post_type );
	}

	/**
	 * 著者パーマリンク構造を正しく返しているか。
	 *
	 * @covers ::get_author_permastruct
	 * @covers ::get_author_base
	 */
	public function test_get_author_permastruct() {
		register_post_type( self::$post_type );

		global $wp_rewrite;
		$wp_rewrite->flush_rules();

		$author_permastruct = self::$wpf_cpt_rewrite->get_author_permastruct( self::$post_type );
		$this->assertSame( WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( self::$post_type ) . '/author/%author%', $author_permastruct );

		_unregister_post_type( self::$post_type );
	}

	/**
	 * デフォルトパーマリンク構造が空の場合、CPT著者パーマリンク構造が false を返すか。
	 *
	 * @covers ::get_author_permastruct
	 */
	public function test_get_author_permastruct_with_empty_default_permastruct() {
		global $wp_rewrite;
		$wp_rewrite->permalink_structure = '';

		register_post_type( self::$post_type );

		global $wp_rewrite;
		$wp_rewrite->flush_rules();

		$author_permastruct = self::$wpf_cpt_rewrite->get_author_permastruct( self::$post_type );
		$this->assertFalse( $author_permastruct );

		_unregister_post_type( self::$post_type );
	}

	/**
	 * author_archive オプションに文字列を指定した場合、著者ベースを正しく返しているか。
	 *
	 * @covers ::get_author_base
	 */
	public function test_get_author_base_change_base() {
		register_post_type(
			self::$post_type,
			array(
				'wpf_cptp' => array(
					'author_archive' => 'foo', // 文字列を指定。
				),
			)
		);

		global $wp_rewrite;
		$wp_rewrite->flush_rules();

		$author_base = self::$wpf_cpt_rewrite->get_author_base( self::$post_type );
		$this->assertSame( 'foo', $author_base );

		_unregister_post_type( self::$post_type );
	}

	/**
	 * CPT日付パーマリンク構造を正しく返しているか。
	 *
	 * @covers ::get_date_permastruct
	 * @covers ::get_date_front
	 */
	public function test_get_date_permastruct() {
		register_post_type(
			self::$post_type,
			array(
				'public' => true,
			)
		);

		global $wp_rewrite;
		$wp_rewrite->flush_rules();

		$this->assertSame(
			WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( self::$post_type ) . '/date/%year%/%monthnum%/%day%',
			self::$wpf_cpt_rewrite->get_date_permastruct( self::$post_type )
		);

		_unregister_post_type( self::$post_type );
	}

	/**
	 * デフォルトパーマリンク構造が空の場合、CPT日付パーマリンク構造が false を返すか。
	 *
	 * @covers ::get_date_permastruct
	 */
	public function test_get_date_permastruct_with_empty_default_permastruct() {
		global $wp_rewrite;
		$wp_rewrite->permalink_structure = '';

		register_post_type( self::$post_type );

		global $wp_rewrite;
		$wp_rewrite->flush_rules();

		$date_permastruct = self::$wpf_cpt_rewrite->get_date_permastruct( self::$post_type );
		$this->assertFalse( $date_permastruct );

		_unregister_post_type( self::$post_type );
	}

	/**
	 * 日付パーマリンクのフロントを正しく返しているか。
	 *
	 * @covers ::get_date_front
	 */
	public function test_get_date_front() {
		register_post_type(
			self::$post_type,
			array(
				'public' => true,
			)
		);

		global $wp_rewrite;
		$wp_rewrite->flush_rules();

		$this->assertSame(
			WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( self::$post_type ) . '/date/',
			self::$wpf_cpt_rewrite->get_date_front( self::$post_type )
		);

		_unregister_post_type( self::$post_type );
	}

	/**
	 * rewrite->with_front が false の場合、日付パーマリンクのフロントを正しく返しているか。
	 *
	 * @covers ::get_date_front
	 */
	public function test_get_date_front_with_front_false() {
		register_post_type(
			self::$post_type,
			array(
				'public'  => true,
				'rewrite' => array(
					'with_front' => false, // フロントを無効にする。
				),
			)
		);

		global $wp_rewrite;
		$wp_rewrite->flush_rules();

		$this->assertSame(
			WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( self::$post_type ) . '/date/',
			self::$wpf_cpt_rewrite->get_date_front( self::$post_type )
		);

		_unregister_post_type( self::$post_type );
	}

	/**
	 * %post_id% が3階層より外にある場合、日付パーマリンクのフロントを正しく返しているか。
	 *
	 * @covers ::get_date_front
	 */
	public function test_get_date_front_with_post_id_outside_3levels() {
		register_post_type(
			self::$post_type,
			array(
				'public'   => true,
				'wpf_cptp' => array(
					'permalink_structure' => '/%year%/%monthnum%/%day%/%post_id%/', // %post_id% を4階層以降に配置。
				),
			)
		);

		global $wp_rewrite;
		$wp_rewrite->flush_rules();

		$this->assertSame(
			WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( self::$post_type ) . '/',
			self::$wpf_cpt_rewrite->get_date_front( self::$post_type )
		);

		_unregister_post_type( self::$post_type );
	}

	/**
	 * CPTパーマリンク構造を正しく返しているか。
	 *
	 * @covers ::get_cpt_permastruct
	 */
	public function test_get_cpt_permastruct() {
		register_post_type(
			self::$post_type,
			array(
				'public' => true,
			)
		);

		global $wp_rewrite;
		$wp_rewrite->flush_rules();

		$this->assertSame(
			WPF_CPT_Rewrite::get_cpt_rewrite_tag( self::$post_type ) . '/%post_id%/',
			self::$wpf_cpt_rewrite->get_cpt_permastruct( self::$post_type )
		);

		_unregister_post_type( self::$post_type );
	}
}
