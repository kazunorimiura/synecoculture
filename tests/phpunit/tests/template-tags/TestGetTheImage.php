<?php

/**
 * WPF_Template_Tags::get_the_image のユニットテスト。
 *
 * @group template_tags
 * @covers WPF_Template_Tags
 * @coversDefaultClass WPF_Template_Tags
 */
class TestGetTheImage extends WPF_UnitTestCase {

	/**
	 * テスト条件:
	 * - $image_id 引数（必須引数）を指定 => 指定IDの画像が src 属性に設定される。
	 *
	 * @covers ::get_the_image
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_image() {
		$attachment_id = self::factory()->attachment->create_object( 'image.jpg', 0, array( 'post_mime_type' => 'image/jpeg' ) );

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'<img width="1" height="1" src="http://example.org/wp-content/uploads/image.jpg" class="attachment-medium size-medium" alt="" decoding="async" loading="lazy" />'
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				WPF_Template_Tags::get_the_image( $attachment_id )
			)
		);
	}

	/**
	 * テスト条件:
	 * - 存在しない $image_id 引数を指定 => no-image画像が src 属性に設定される。
	 *
	 * @covers ::get_the_image
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_image_with_no_image() {
		$no_image_id = self::factory()->attachment->create_object( 'no-image.jpg', 0, array( 'post_mime_type' => 'image/jpeg' ) );
		set_theme_mod( 'wpf_no_image', $no_image_id );

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'<img width="1" height="1" src="http://example.org/wp-content/uploads/no-image.jpg" class="attachment-medium size-medium" alt="" decoding="async" loading="lazy" />'
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				WPF_Template_Tags::get_the_image( 0 )
			)
		);
	}

	/**
	 * テスト条件:
	 * - 存在しない $image_id 引数を指定。no-imageもなし => 空文字列が返される。
	 *
	 * @covers ::get_the_image
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_image_with_all_images_nothing() {
		$this->assertSame(
			'',
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				WPF_Template_Tags::get_the_image( 0 )
			)
		);
	}

	/**
	 * テスト条件:
	 * - $use_no_image 引数を false に設定 => 空文字列が返される。
	 *
	 * @covers ::get_the_image
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_image_with_no_image_but_use_no_image_false() {
		$no_image_id = self::factory()->attachment->create_object( 'no-image.jpg', 0, array( 'post_mime_type' => 'image/jpeg' ) );
		set_theme_mod( 'wpf_no_image', $no_image_id );

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				''
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				WPF_Template_Tags::get_the_image( 0, 'medium', false, array(), false )
			)
		);
	}

	/**
	 * テスト条件:
	 * - $size 引数を指定 => クラス文字列が変わる。
	 *
	 * @covers ::get_the_image
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_image_with_size() {
		$attachment_id = self::factory()->attachment->create_object( 'image.jpg', 0, array( 'post_mime_type' => 'image/jpeg' ) );

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'<img width="1" height="1" src="http://example.org/wp-content/uploads/image.jpg" class="attachment-large size-large" alt="" decoding="async" loading="lazy" />'
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				WPF_Template_Tags::get_the_image( $attachment_id, 'large' )
			)
		);
	}

	/**
	 * テスト条件:
	 * - $icon 引数を指定 => フォールバックアイコンが src 属性に設定される。
	 *
	 * @covers ::get_the_image
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_image_with_icon() {
		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'<img width="48" height="64" src="http://example.org/wp-includes/images/media/default.svg" class="attachment-medium size-medium" alt="" decoding="async" loading="lazy" />'
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				WPF_Template_Tags::get_the_image( 9999, 'medium', true ) // 存在しないIDを指定
			)
		);
	}

	/**
	 * テスト条件:
	 * - $attr（class, alt） 引数を指定 => 各属性が設定される。
	 *
	 * @covers ::get_the_image
	 * @preserveGlobalState disabled
	 */
	public function test_get_the_image_with_attr() {
		$attachment_id = self::factory()->attachment->create_object( 'image.jpg', 0, array( 'post_mime_type' => 'image/jpeg' ) );

		$this->assertSame(
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				'<img width="1" height="1" src="http://example.org/wp-content/uploads/image.jpg" class="foo" alt="Foo" decoding="async" loading="lazy" />'
			),
			preg_replace(
				array( '/\r/', '/\n/', '/\t/', '/\s\s+/' ),
				'',
				WPF_Template_Tags::get_the_image(
					$attachment_id,
					'medium',
					false,
					array(
						'class' => 'foo',
						'alt'   => 'Foo',
					)
				)
			)
		);
	}
}
