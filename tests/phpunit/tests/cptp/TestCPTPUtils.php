<?php

/**
 * WPF_CPTP_Utils クラスのユニットテスト
 *
 * @group cptp_utils
 * @covers WPF_CPTP_Utils
 * @coversDefaultClass WPF_CPTP_Utils
 */
class TestCPTPUtils extends WPF_UnitTestCase {

	/**
	 * @covers ::get_post_types
	 * @preserveGlobalState disabled
	 */
	public function test_get_post_types() {
		$post_type_foo        = 'foo';
		$post_type_bar        = 'bar';
		$post_type_baz        = 'baz';
		$post_type_no_rewrite = 'no_rewrite';

		register_post_type(
			$post_type_foo,
			array(
				'public'      => true,
				'has_archive' => true,
			)
		);

		register_post_type(
			$post_type_bar,
			array(
				'public'      => true,
				'has_archive' => true,
			)
		);

		register_post_type(
			$post_type_baz,
			array(
				'public'      => true,
				'has_archive' => true,
			)
		);

		register_post_type(
			$post_type_no_rewrite,
			array(
				'public'      => true,
				'has_archive' => true,
				'rewrite'     => false,
			)
		);

		global $wp_rewrite;
		$wp_rewrite->flush_rules();

		$post_types = WPF_CPTP_Utils::get_post_types();

		$this->assertTrue( array_key_exists( $post_type_foo, $post_types ) );
		$this->assertTrue( array_key_exists( $post_type_bar, $post_types ) );
		$this->assertTrue( array_key_exists( $post_type_baz, $post_types ) );
		$this->assertFalse( array_key_exists( $post_type_no_rewrite, $post_types ) );

		_unregister_post_type( $post_type_foo );
		_unregister_post_type( $post_type_bar );
		_unregister_post_type( $post_type_baz );
		_unregister_post_type( $post_type_no_rewrite );
	}

	/**
	 * @covers ::get_permalink_structure
	 * @preserveGlobalState disabled
	 */
	public function test_get_permalink_structure() {
		register_post_type(
			self::$post_type,
			array(
				'public'      => true,
				'has_archive' => true,
				'wpf_cptp'    => array(
					'permalink_structure' => '/%year%/%monthnum%/%day%/%postname%/',
				),
			)
		);

		global $wp_rewrite;
		$wp_rewrite->flush_rules();

		$this->assertSame( '/%year%/%monthnum%/%day%/%postname%/', WPF_CPTP_Utils::get_permalink_structure( self::$post_type ) );

		_unregister_post_type( self::$post_type );
	}

	/**
	 * @covers ::get_post_type_author_archive_support
	 * @preserveGlobalState disabled
	 */
	public function test_get_post_type_author_archive_support() {
		register_post_type(
			self::$post_type,
			array(
				'public'      => true,
				'has_archive' => true,
			)
		);

		global $wp_rewrite;
		$wp_rewrite->flush_rules();

		$this->assertTrue( WPF_CPTP_Utils::get_post_type_author_archive_support( self::$post_type ) );

		_unregister_post_type( self::$post_type );
	}

	/**
	 * @covers ::get_post_type_author_archive_support
	 * @preserveGlobalState disabled
	 */
	public function test_get_post_type_author_archive_support_with_false() {
		register_post_type(
			self::$post_type,
			array(
				'public'      => true,
				'has_archive' => true,
				'wpf_cptp'    => array(
					'author_archive' => false,
				),
			)
		);

		global $wp_rewrite;
		$wp_rewrite->flush_rules();

		$this->assertFalse( WPF_CPTP_Utils::get_post_type_author_archive_support( self::$post_type ) );

		_unregister_post_type( self::$post_type );
	}

	/**
	 * @covers ::get_post_type_date_archive_support
	 * @preserveGlobalState disabled
	 */
	public function test_get_post_type_date_archive_support() {
		register_post_type(
			self::$post_type,
			array(
				'public'      => true,
				'has_archive' => true,
			)
		);

		global $wp_rewrite;
		$wp_rewrite->flush_rules();

		$this->assertTrue( WPF_CPTP_Utils::get_post_type_date_archive_support( self::$post_type ) );

		_unregister_post_type( self::$post_type );
	}

	/**
	 * @covers ::get_post_type_date_archive_support
	 * @preserveGlobalState disabled
	 */
	public function test_get_post_type_date_archive_support_with_false() {
		register_post_type(
			self::$post_type,
			array(
				'public'      => true,
				'has_archive' => true,
				'wpf_cptp'    => array(
					'date_archive' => false,
				),
			)
		);

		global $wp_rewrite;
		$wp_rewrite->flush_rules();

		$this->assertFalse( WPF_CPTP_Utils::get_post_type_date_archive_support( self::$post_type ) );

		_unregister_post_type( self::$post_type );
	}
}
