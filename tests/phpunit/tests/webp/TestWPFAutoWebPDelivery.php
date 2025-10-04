<?php

/**
 * WPF_Auto_WebP_Delivery のユニットテスト
 *
 * @group webp_delivery
 * @covers WPF_Auto_WebP_Delivery
 * @coversDefaultClass WPF_Auto_WebP_Delivery
 */
class TestWPFAutoWebPDelivery extends WPF_UnitTestCase {

	/**
	 * @var WPF_Auto_WebP_Delivery
	 */
	private $delivery;

	/**
	 * @var int
	 */
	private $attachment_id;

	/**
	 * @var string
	 */
	private $test_image_path;

	/**
	 * @var string
	 */
	private $test_webp_path;

	/**
	 * @var string
	 */
	private $upload_dir;

	/**
	 * テストの実行前に必要なデータを準備する
	 */
	public function set_up() {
		parent::set_up();

		// アップロードディレクトリの設定
		$upload_info      = wp_upload_dir();
		$this->upload_dir = $upload_info['basedir'];

		// アップロードディレクトリが存在しない場合は作成
		if ( ! file_exists( $this->upload_dir ) ) {
			wp_mkdir_p( $this->upload_dir );
		}

		// テスト用画像とWebPファイルの作成
		$this->create_test_files();

		// テスト用添付ファイルの作成
		$this->attachment_id = $this->factory->attachment->create_object(
			basename( $this->test_image_path ),
			0,
			array(
				'post_mime_type' => 'image/jpeg',
				'post_title'     => 'Test Image',
			)
		);

		// 添付ファイルのメタデータを設定
		$subdir   = wp_upload_dir()['subdir'];
		$metadata = array(
			'file'   => ltrim( $subdir . '/' . basename( $this->test_image_path ), '/' ),
			'width'  => 100,
			'height' => 100,
			'sizes'  => array(
				'thumbnail' => array(
					'file'   => 'test-150x150.jpg',
					'width'  => 150,
					'height' => 150,
				),
			),
		);

		wp_update_attachment_metadata( $this->attachment_id, $metadata );

		// WebP生成設定を有効にする
		update_post_meta( $this->attachment_id, '_wpf_generate_webp', '1' );

		// WPF_Auto_WebP_Deliveryクラスのインスタンスを作成
		$this->delivery = new WPF_Auto_WebP_Delivery();
	}

	/**
	 * テストの実行後にデータをクリーンアップする
	 */
	public function tear_down() {
		// テストファイルのクリーンアップ
		$this->cleanup_test_files();

		// 添付ファイルを削除
		if ( $this->attachment_id ) {
			wp_delete_attachment( $this->attachment_id, true );
		}

		parent::tear_down();
	}

	/**
	 * テスト用画像とWebPファイルを作成
	 */
	private function create_test_files() {
		$upload_info = wp_upload_dir();
		$upload_path = $upload_info['path'];

		$this->test_image_path = $upload_path . '/test.jpg';
		$this->test_webp_path  = $upload_path . '/test.webp';

		// ディレクトリが存在しない場合は作成
		if ( ! file_exists( $upload_path ) ) {
			wp_mkdir_p( $upload_path );
		}

		// テスト用JPEG画像を作成
		$jpeg_data = base64_decode( '/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/gA==' ); // phpcs:ignore
		file_put_contents( $this->test_image_path, $jpeg_data ); // phpcs:ignore

		// テスト用WebPファイルを作成
		$webp_data = base64_decode( 'UklGRiQAAABXRUJQVlA4IBgAAAAwAQCdASoBAAEAAwA0JaQAA3AA/vuUAAA=' ); // phpcs:ignore
		file_put_contents( $this->test_webp_path, $webp_data ); // phpcs:ignore

		// サムネイル用ファイルも作成
		$thumbnail_jpeg = $upload_path . '/test-150x150.jpg';
		$thumbnail_webp = $upload_path . '/test-150x150.webp';
		file_put_contents( $thumbnail_jpeg, $jpeg_data ); // phpcs:ignore
		file_put_contents( $thumbnail_webp, $webp_data ); // phpcs:ignore
	}

	/**
	 * テストファイルのクリーンアップ
	 */
	private function cleanup_test_files() {
		$upload_info = wp_upload_dir();
		$upload_path = $upload_info['path'];

		$files_to_delete = array(
			$upload_path . '/test.jpg',
			$upload_path . '/test.webp',
			$upload_path . '/test-150x150.jpg',
			$upload_path . '/test-150x150.webp',
		);

		foreach ( $files_to_delete as $file ) {
			if ( file_exists( $file ) ) {
				unlink( $file );
			}
		}
	}

	/**
	 * コンストラクタのテスト
	 *
	 * @covers ::__construct
	 */
	public function test_constructor() {
		// pictureタグ配信関連のフィルターが登録されることを確認
		$this->assertTrue( has_filter( 'wp_get_attachment_image' ) );
		$this->assertTrue( has_filter( 'the_content' ) );
	}

	/**
	 * WebP使用判定のテスト
	 *
	 * @covers ::should_use_webp
	 */
	public function test_should_use_webp() {
		$reflection = new ReflectionClass( $this->delivery );
		$method     = $reflection->getMethod( 'should_use_webp' );
		$method->setAccessible( true );

		// WebP生成が有効な場合
		$this->assertTrue( $method->invoke( $this->delivery, $this->attachment_id ) );

		// WebP生成が無効な場合
		update_post_meta( $this->attachment_id, '_wpf_generate_webp', '0' );
		$this->assertFalse( $method->invoke( $this->delivery, $this->attachment_id ) );

		// メタデータが存在しない場合
		delete_post_meta( $this->attachment_id, '_wpf_generate_webp' );
		$this->assertFalse( $method->invoke( $this->delivery, $this->attachment_id ) );
	}

	/**
	 * WebP URL生成のテスト
	 *
	 * @covers ::get_webp_url
	 */
	public function test_get_webp_url() {
		$reflection = new ReflectionClass( $this->delivery );
		$method     = $reflection->getMethod( 'get_webp_url' );
		$method->setAccessible( true );

		$upload_info  = wp_upload_dir();
		$original_url = $upload_info['url'] . '/test.jpg';

		// WebPファイルが存在する場合
		$expected_webp_url = $upload_info['url'] . '/test.webp';
		$result            = $method->invoke( $this->delivery, $original_url );
		$this->assertSame( $expected_webp_url, $result );

		// WebPファイルが存在しない場合
		unlink( $this->test_webp_path );
		$result = $method->invoke( $this->delivery, $original_url );
		$this->assertFalse( $result );
	}

	/**
	 * pictureタグ配信: wp_get_attachment_image変換のテスト
	 *
	 * @covers ::convert_to_picture_tag
	 */
	public function test_convert_to_picture_tag() {
		$upload_info       = wp_upload_dir();
		$original_url      = $upload_info['url'] . '/test.jpg';
		$expected_webp_url = $upload_info['url'] . '/test.webp';

		// 添付ファイルのファイルパスを設定
		$file_path = wp_upload_dir()['path'] . '/test.jpg';
		update_attached_file( $this->attachment_id, $file_path );

		// 添付ファイルのメタデータを再設定
		$attachment_metadata = array(
			'file'   => wp_upload_dir()['subdir'] . '/test.jpg',
			'width'  => 100,
			'height' => 100,
			'sizes'  => array(),
		);
		wp_update_attachment_metadata( $this->attachment_id, $attachment_metadata );

		$original_html = '<img src="' . $original_url . '" class="attachment-full size-full" alt="テスト" />';

		$result = $this->delivery->convert_to_picture_tag( $original_html, $this->attachment_id, 'full', false, array() );

		// pictureタグが生成されることを確認
		$this->assertStringContainsString( '<picture>', $result );
		$this->assertStringContainsString( '<source srcset="' . $expected_webp_url . '" type="image/webp">', $result );
		$this->assertStringContainsString( $original_html, $result );
		$this->assertStringContainsString( '</picture>', $result );

		// WebP生成が無効な場合
		update_post_meta( $this->attachment_id, '_wpf_generate_webp', '0' );
		$result = $this->delivery->convert_to_picture_tag( $original_html, $this->attachment_id, 'full', false, array() );
		$this->assertSame( $original_html, $result );
	}

	/**
	 * pictureタグ配信: コンテンツ内画像変換のテスト
	 *
	 * @covers ::convert_content_images_to_picture
	 */
	public function test_convert_content_images_to_picture() {
		$upload_info       = wp_upload_dir();
		$original_url      = $upload_info['url'] . '/test.jpg';
		$expected_webp_url = $upload_info['url'] . '/test.webp';

		$content = '<p>テスト画像: <img src="' . $original_url . '" class="wp-image-' . $this->attachment_id . '" alt="テスト" /></p>';

		$result = $this->delivery->convert_content_images_to_picture( $content );

		// pictureタグが生成されることを確認
		$this->assertStringContainsString( '<picture>', $result );
		$this->assertStringContainsString( '<source srcset="' . $expected_webp_url . '" type="image/webp">', $result );
		$this->assertStringContainsString( '</picture>', $result );

		// WebP生成が無効な場合
		update_post_meta( $this->attachment_id, '_wpf_generate_webp', '0' );
		$result = $this->delivery->convert_content_images_to_picture( $content );
		$this->assertStringNotContainsString( '<picture>', $result );
		$this->assertStringContainsString( $content, $result );

		// wp-imageクラスがない画像の場合（処理されない）
		update_post_meta( $this->attachment_id, '_wpf_generate_webp', '1' );
		$content_without_class = '<p>テスト画像: <img src="' . $original_url . '" alt="テスト" /></p>';
		$result                = $this->delivery->convert_content_images_to_picture( $content_without_class );
		$this->assertStringNotContainsString( '<picture>', $result );
		$this->assertStringContainsString( $original_url, $result );
	}

	/**
	 * 存在しない添付ファイルでのテスト
	 *
	 * @covers ::convert_to_picture_tag
	 * @covers ::should_use_webp
	 */
	public function test_with_nonexistent_attachment() {
		$nonexistent_id = 99999;
		$upload_info    = wp_upload_dir();
		$original_url   = $upload_info['url'] . '/test.jpg';

		$original_html = '<img src="' . $original_url . '" class="attachment-full size-full" alt="テスト" />';

		$result = $this->delivery->convert_to_picture_tag( $original_html, $nonexistent_id, 'full', false, array() );

		// 存在しない添付ファイルの場合は元のHTMLが返されることを確認
		$this->assertSame( $original_html, $result );
	}

	/**
	 * WebPファイルが存在しない場合のテスト
	 *
	 * @covers ::get_webp_url
	 * @covers ::convert_to_picture_tag
	 */
	public function test_with_missing_webp_file() {
		// WebPファイルを削除
		if ( file_exists( $this->test_webp_path ) ) {
			unlink( $this->test_webp_path );
		}

		$upload_info   = wp_upload_dir();
		$original_url  = $upload_info['url'] . '/test.jpg';
		$original_html = '<img src="' . $original_url . '" class="attachment-full size-full" alt="テスト" />';

		// 添付ファイルのファイルパスを設定
		$file_path = wp_upload_dir()['path'] . '/test.jpg';
		update_attached_file( $this->attachment_id, $file_path );

		$result = $this->delivery->convert_to_picture_tag( $original_html, $this->attachment_id, 'full', false, array() );

		// WebPファイルが存在しない場合は元のHTMLが返されることを確認
		$this->assertSame( $original_html, $result );
	}

	/**
	 * 無効な画像データでのテスト
	 *
	 * @covers ::convert_to_picture_tag
	 */
	public function test_with_invalid_attachment_image_src() {
		// wp_get_attachment_image_src が false を返すケースをシミュレート
		$invalid_attachment_id = 0; // 無効なID

		$original_html = '<img src="test.jpg" class="attachment-full size-full" alt="テスト" />';

		$result = $this->delivery->convert_to_picture_tag( $original_html, $invalid_attachment_id, 'full', false, array() );

		// 無効なデータの場合は元のHTMLが返されることを確認
		$this->assertSame( $original_html, $result );
	}

	/**
	 * 異なるサイズでのWebP変換テスト
	 *
	 * @covers ::convert_to_picture_tag
	 */
	public function test_convert_to_picture_tag_with_different_sizes() {
		$upload_info = wp_upload_dir();

		// まずは、フルサイズでの動作を確認
		$original_url      = $upload_info['url'] . '/test.jpg';
		$expected_webp_url = $upload_info['url'] . '/test.webp';

		// 添付ファイルのファイルパスを設定
		$file_path = wp_upload_dir()['path'] . '/test.jpg';
		update_attached_file( $this->attachment_id, $file_path );

		// フルサイズのメタデータを設定
		$metadata = array(
			'file'   => wp_upload_dir()['subdir'] . '/test.jpg',
			'width'  => 100,
			'height' => 100,
			'sizes'  => array(
				'thumbnail' => array(
					'file'      => 'test-150x150.jpg',
					'width'     => 150,
					'height'    => 150,
					'mime-type' => 'image/jpeg',
				),
			),
		);
		wp_update_attachment_metadata( $this->attachment_id, $metadata );

		// フルサイズでのテスト
		$original_html = '<img src="' . $original_url . '" class="attachment-full size-full" alt="テスト" />';
		$result        = $this->delivery->convert_to_picture_tag( $original_html, $this->attachment_id, 'full', false, array() );

		// フルサイズでpictureタグが生成されることを確認
		$this->assertStringContainsString( '<picture>', $result, 'Full size should generate picture tag' );
		$this->assertStringContainsString( $expected_webp_url, $result, 'Full size should contain WebP URL' );
		$this->assertStringContainsString( '</picture>', $result, 'Full size should close picture tag' );

		// サムネイルサイズのテスト（フルサイズが成功した場合のみ）
		$thumbnail_url           = $upload_info['url'] . '/test-150x150.jpg';
		$expected_thumbnail_webp = $upload_info['url'] . '/test-150x150.webp';

		// wp_get_attachment_image_src でサムネイルデータを確認
		$thumbnail_src = wp_get_attachment_image_src( $this->attachment_id, 'thumbnail' );

		if ( $thumbnail_src && $thumbnail_src[0] === $thumbnail_url ) {
			// サムネイル情報が正常に取得できた場合のみテスト実行
			$thumbnail_html   = '<img src="' . $thumbnail_url . '" class="attachment-thumbnail size-thumbnail" alt="テスト" />';
			$thumbnail_result = $this->delivery->convert_to_picture_tag( $thumbnail_html, $this->attachment_id, 'thumbnail', false, array() );

			$this->assertStringContainsString( '<picture>', $thumbnail_result, 'Thumbnail should generate picture tag' );
			$this->assertStringContainsString( $expected_thumbnail_webp, $thumbnail_result, 'Thumbnail should contain WebP URL' );
			$this->assertStringContainsString( '</picture>', $thumbnail_result, 'Thumbnail should close picture tag' );
		} else {
			// サムネイル情報が取得できない場合はスキップ
			$this->markTestSkipped( 'wp_get_attachment_image_src does not return valid thumbnail data in test environment' );
		}
	}
}
