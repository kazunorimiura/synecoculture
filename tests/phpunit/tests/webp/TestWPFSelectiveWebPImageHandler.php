<?php

/**
 * WPF_Selective_WebP_Image_Handler のユニットテスト
 *
 * @group webp_handler
 * @covers WPF_Selective_WebP_Image_Handler
 * @coversDefaultClass WPF_Selective_WebP_Image_Handler
 */
class TestWPFSelectiveWebPImageHandler extends WPF_UnitTestCase {

	/**
	 * @var WPF_Selective_WebP_Image_Handler
	 */
	private $handler;

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
	private $upload_dir;

	/**
	 * テストの実行前に必要なデータを準備する
	 */
	public function set_up() {
		parent::set_up();

		// WebPハンドラーのインスタンスを作成
		$this->handler = new WPF_Selective_WebP_Image_Handler();

		// アップロードディレクトリの設定
		$upload_info      = wp_upload_dir();
		$this->upload_dir = $upload_info['basedir'];

		// アップロードディレクトリが存在しない場合は作成
		if ( ! file_exists( $this->upload_dir ) ) {
			wp_mkdir_p( $this->upload_dir );
		}

		// テスト用画像の作成
		$this->create_test_image();

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
				'medium'    => array(
					'file'   => 'test-300x300.jpg',
					'width'  => 300,
					'height' => 300,
				),
			),
		);

		wp_update_attachment_metadata( $this->attachment_id, $metadata );

		// メタデータを確認
		$saved_metadata = wp_get_attachment_metadata( $this->attachment_id );

		// サイズバリエーション用のテスト画像を作成
		$this->create_test_image( 'test-150x150.jpg' );
		$this->create_test_image( 'test-300x300.jpg' );
	}

	/**
	 * テストの実行後にデータをクリーンアップする
	 */
	public function tear_down() {
		// テスト画像とWebPファイルを削除
		$this->cleanup_test_files();

		// 添付ファイルを削除
		if ( $this->attachment_id ) {
			wp_delete_attachment( $this->attachment_id, true );
		}

		parent::tear_down();
	}

	/**
	 * テスト用画像を作成
	 *
	 * @param string $filename ファイル名（省略時はtest.jpg）
	 */
	private function create_test_image( $filename = 'test.jpg' ) {
		// 年月のサブディレクトリを取得
		$upload_info = wp_upload_dir();
		$upload_dir  = $upload_info['path']; // 年月を含むパス
		$image_path  = $upload_dir . '/' . $filename;

		// ディレクトリが存在しない場合は作成
		if ( ! file_exists( $upload_dir ) ) {
			wp_mkdir_p( $upload_dir );
		}

		// GD拡張が利用可能かチェック
		if ( ! function_exists( 'imagecreate' ) ) {
			// 代替案：ダミーJPEGファイルを作成
			$jpeg_data = base64_decode( '/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/gA==' ); // phpcs:ignore
			file_put_contents( $image_path, $jpeg_data ); // phpcs:ignore
		} else {
			// 100x100ピクセルのJPEG画像を作成
			$image = imagecreate( 100, 100 );
			if ( false === $image ) {
				return;
			}

			$white = imagecolorallocate( $image, 255, 255, 255 );
			$black = imagecolorallocate( $image, 0, 0, 0 );

			// テキストを描画（テスト用）
			imagestring( $image, 2, 10, 10, 'TEST', $black );

			$result = imagejpeg( $image, $image_path, 90 );
			imagedestroy( $image );
		}

		if ( 'test.jpg' === $filename ) {
			$this->test_image_path = $image_path;
		}
	}

	/**
	 * テストファイルのクリーンアップ
	 */
	private function cleanup_test_files() {
		// 実際のアップロードディレクトリを取得
		$upload_info = wp_upload_dir();
		$upload_path = $upload_info['path'];

		$files_to_delete = array(
			$upload_path . '/test.jpg',
			$upload_path . '/test.webp',
			$upload_path . '/test-150x150.jpg',
			$upload_path . '/test-150x150.webp',
			$upload_path . '/test-300x300.jpg',
			$upload_path . '/test-300x300.webp',
		);

		foreach ( $files_to_delete as $file ) {
			if ( file_exists( $file ) ) {
				unlink( $file );
			}
		}
	}

	/**
	 * フックが正しく登録されているかテスト
	 *
	 * @covers ::__construct
	 */
	public function test_hooks_are_registered() {
		// 管理画面用フック
		$this->assertTrue( has_action( 'admin_enqueue_scripts' ) );
		$this->assertTrue( has_action( 'add_meta_boxes' ) );
		$this->assertTrue( has_action( 'edit_attachment' ) );

		// AJAX用フック
		$this->assertTrue( has_action( 'wp_ajax_toggle_webp_generation' ) );
		$this->assertTrue( has_action( 'wp_ajax_bulk_webp_action' ) );

		// 削除用フック
		$this->assertTrue( has_action( 'delete_attachment' ) );

		// メディアライブラリ用フック
		$this->assertTrue( has_filter( 'manage_media_columns' ) );
		$this->assertTrue( has_action( 'manage_media_custom_column' ) );

		// 一括操作用フック
		$this->assertTrue( has_filter( 'bulk_actions-upload' ) );
		$this->assertTrue( has_filter( 'handle_bulk_actions-upload' ) );
	}

	/**
	 * WebP生成機能のテスト
	 *
	 * @covers ::generate_webp_for_attachment
	 */
	public function test_generate_webp_for_attachment() {
		// 実際のアップロードパスを取得
		$upload_info = wp_upload_dir();
		$upload_path = $upload_info['path'];

		$webp_files = array(
			$upload_path . '/test.webp',
			$upload_path . '/test-150x150.webp',
			$upload_path . '/test-300x300.webp',
		);

		// WebP生成前の状態確認
		foreach ( $webp_files as $webp_file ) {
			$this->assertFalse( file_exists( $webp_file ), 'WebP file should not exist before generation: ' . $webp_file );
		}

		// WebP生成を実行
		$reflection = new ReflectionClass( $this->handler );
		$method     = $reflection->getMethod( 'generate_webp_for_attachment' );
		$method->setAccessible( true );

		$result = $method->invoke( $this->handler, $this->attachment_id );

		// 生成が成功することを確認
		$this->assertTrue( $result, 'WebP generation should succeed' );

		// WebPファイルが作成されることを確認
		foreach ( $webp_files as $webp_file ) {
			$this->assertTrue( file_exists( $webp_file ), 'WebP file should be created: ' . $webp_file );
		}
	}

	/**
	 * WebPファイル存在確認のテスト
	 *
	 * @covers ::check_webp_exists
	 */
	public function test_check_webp_exists() {
		$reflection = new ReflectionClass( $this->handler );
		$method     = $reflection->getMethod( 'check_webp_exists' );
		$method->setAccessible( true );

		// 実際のアップロードパスを取得
		$upload_info = wp_upload_dir();
		$upload_path = $upload_info['path'];
		$webp_path   = $upload_path . '/test.webp';

		// WebPファイルが存在しない状態
		$this->assertFalse( $method->invoke( $this->handler, $this->attachment_id ) );

		// WebPファイルを作成
		file_put_contents( $webp_path, 'dummy webp content' ); // phpcs:ignore

		// WebPファイルが存在する状態
		$this->assertTrue( $method->invoke( $this->handler, $this->attachment_id ) );
	}

	/**
	 * WebPファイル削除のテスト
	 *
	 * @covers ::delete_webp_versions
	 */
	public function test_delete_webp_versions() {
		// 実際のアップロードパスを取得
		$upload_info = wp_upload_dir();
		$upload_path = $upload_info['path'];

		// WebPファイルを事前に作成
		$webp_files = array(
			$upload_path . '/test.webp',
			$upload_path . '/test-150x150.webp',
			$upload_path . '/test-300x300.webp',
		);

		foreach ( $webp_files as $webp_file ) {
			file_put_contents( $webp_file, 'dummy webp content' ); // phpcs:ignore
		}

		// ファイルが存在することを確認
		foreach ( $webp_files as $webp_file ) {
			$this->assertTrue( file_exists( $webp_file ), 'File should exist: ' . $webp_file );
		}

		// 削除を実行
		$this->handler->delete_webp_versions( $this->attachment_id );

		// ファイルが削除されることを確認
		foreach ( $webp_files as $webp_file ) {
			$this->assertFalse( file_exists( $webp_file ), 'File should be deleted: ' . $webp_file );
		}
	}

	/**
	 * WebP設定保存のテスト
	 *
	 * @covers ::save_webp_preference
	 */
	public function test_save_webp_preference() {
		// nonce設定
		$_POST['webp_meta_box_nonce'] = wp_create_nonce( 'webp_meta_box_nonce' );

		// WebP生成を有効にする場合
		$_POST['generate_webp'] = '1';

		$this->handler->save_webp_preference( $this->attachment_id );

		// メタデータが保存されることを確認
		$this->assertSame( '1', get_post_meta( $this->attachment_id, '_wpf_generate_webp', true ) );

		// WebP生成を無効にする場合
		unset( $_POST['generate_webp'] ); // phpcs:ignore

		$this->handler->save_webp_preference( $this->attachment_id );

		// メタデータが更新されることを確認
		$this->assertSame( '0', get_post_meta( $this->attachment_id, '_wpf_generate_webp', true ) );

		// POSTデータをクリーンアップ
		unset( $_POST['webp_meta_box_nonce'] ); // phpcs:ignore
	}

	/**
	 * AJAX処理のテスト（WebP生成の状態確認）
	 *
	 * AJAX処理は wp_die() で終了するため、結果の状態のみをテストします
	 *
	 * @covers ::save_webp_preference
	 */
	public function test_ajax_webp_generation_state() {
		// 管理者権限を設定
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// 実際のアップロードパスを取得
		$upload_info = wp_upload_dir();
		$upload_path = $upload_info['path'];
		$webp_path   = $upload_path . '/test.webp';

		// WebPが存在しないことを確認
		$this->assertFalse( file_exists( $webp_path ) );

		// WebP生成設定を有効にする（AJAX処理の代わりに直接設定保存をテスト）
		$_POST['webp_meta_box_nonce'] = wp_create_nonce( 'webp_meta_box_nonce' );
		$_POST['generate_webp']       = '1';

		$this->handler->save_webp_preference( $this->attachment_id );

		// WebPファイルが生成されることを確認
		$this->assertTrue( file_exists( $webp_path ), 'WebP file should be generated at: ' . $webp_path );

		// メタデータが設定されることを確認
		$this->assertSame( '1', get_post_meta( $this->attachment_id, '_wpf_generate_webp', true ) );

		// POSTデータをクリーンアップ
		unset( $_POST['webp_meta_box_nonce'], $_POST['generate_webp'] ); // phpcs:ignore
	}

	/**
	 * AJAX処理のテスト（WebP削除の状態確認）
	 *
	 * @covers ::save_webp_preference
	 */
	public function test_ajax_webp_deletion_state() {
		// 管理者権限を設定
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// 実際のアップロードパスを取得
		$upload_info = wp_upload_dir();
		$upload_path = $upload_info['path'];
		$webp_path   = $upload_path . '/test.webp';

		// WebPファイルを事前に作成
		file_put_contents( $webp_path, 'dummy webp content' ); // phpcs:ignore
		$this->assertTrue( file_exists( $webp_path ) );

		// WebP生成設定を無効にする
		$_POST['webp_meta_box_nonce'] = wp_create_nonce( 'webp_meta_box_nonce' );
		// generate_webp は POST に含めない（チェックボックスが外れた状態）

		$this->handler->save_webp_preference( $this->attachment_id );

		// WebPファイルが削除されることを確認
		$this->assertFalse( file_exists( $webp_path ), 'WebP file should be deleted: ' . $webp_path );

		// メタデータが設定されることを確認
		$this->assertSame( '0', get_post_meta( $this->attachment_id, '_wpf_generate_webp', true ) );

		// POSTデータをクリーンアップ
		unset( $_POST['webp_meta_box_nonce'] ); // phpcs:ignore
	}

	/**
	 * 権限チェックのテスト
	 *
	 * @covers ::save_webp_preference
	 */
	public function test_webp_permission_check() {
		// 権限のないユーザーを設定
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		// nonce なしでの処理
		$this->handler->save_webp_preference( $this->attachment_id );

		// メタデータが変更されないことを確認
		$this->assertEmpty( get_post_meta( $this->attachment_id, '_wpf_generate_webp', true ) );

		// 無効なnonce での処理
		$_POST['webp_meta_box_nonce'] = 'invalid_nonce';
		$_POST['generate_webp']       = '1';

		$this->handler->save_webp_preference( $this->attachment_id );

		// メタデータが変更されないことを確認
		$this->assertEmpty( get_post_meta( $this->attachment_id, '_wpf_generate_webp', true ) );

		// POSTデータをクリーンアップ
		unset( $_POST['webp_meta_box_nonce'], $_POST['generate_webp'] ); // phpcs:ignore
	}

	/**
	 * メディアライブラリカラム追加のテスト
	 *
	 * @covers ::add_webp_column
	 */
	public function test_add_webp_column() {
		$columns = array(
			'title' => 'タイトル',
			'date'  => '日付',
		);

		$result = $this->handler->add_webp_column( $columns );

		$this->assertArrayHasKey( 'webp_status', $result );
		$this->assertSame( 'WebP', $result['webp_status'] );
	}

	/**
	 * 一括操作メニュー追加のテスト
	 *
	 * @covers ::add_bulk_actions
	 */
	public function test_add_bulk_actions() {
		$bulk_actions = array(
			'delete' => '完全に削除する',
		);

		$result = $this->handler->add_bulk_actions( $bulk_actions );

		$this->assertArrayHasKey( 'generate_webp', $result );
		$this->assertArrayHasKey( 'delete_webp', $result );
		$this->assertSame( 'WebP生成', $result['generate_webp'] );
		$this->assertSame( 'WebP削除', $result['delete_webp'] );
	}

	/**
	 * 一括操作処理のテスト（WebP生成）
	 *
	 * @covers ::handle_bulk_actions
	 */
	public function test_handle_bulk_actions_generate() {
		$redirect_to = 'upload.php';
		$action      = 'generate_webp';
		$post_ids    = array( $this->attachment_id );

		// 実際のアップロードパスを取得
		$upload_info = wp_upload_dir();
		$upload_path = $upload_info['path'];
		$webp_path   = $upload_path . '/test.webp';

		$result = $this->handler->handle_bulk_actions( $redirect_to, $action, $post_ids );

		// リダイレクトURLにカウントが追加されることを確認
		$this->assertStringContainsString( 'webp_generated=1', $result );

		// WebPファイルが生成されることを確認
		$this->assertTrue( file_exists( $webp_path ), 'WebP file should be generated at: ' . $webp_path );

		// メタデータが設定されることを確認
		$this->assertSame( '1', get_post_meta( $this->attachment_id, '_wpf_generate_webp', true ) );
	}

	/**
	 * 一括操作処理のテスト（WebP削除）
	 *
	 * @covers ::handle_bulk_actions
	 */
	public function test_handle_bulk_actions_delete() {
		// 実際のアップロードパスを取得
		$upload_info = wp_upload_dir();
		$upload_path = $upload_info['path'];
		$webp_path   = $upload_path . '/test.webp';

		// WebPファイルを事前に作成
		file_put_contents( $webp_path, 'dummy webp content' ); // phpcs:ignore

		$redirect_to = 'upload.php';
		$action      = 'delete_webp';
		$post_ids    = array( $this->attachment_id );

		$result = $this->handler->handle_bulk_actions( $redirect_to, $action, $post_ids );

		// リダイレクトURLにカウントが追加されることを確認
		$this->assertStringContainsString( 'webp_deleted=1', $result );

		// WebPファイルが削除されることを確認
		$this->assertFalse( file_exists( $webp_path ), 'WebP file should be deleted: ' . $webp_path );

		// メタデータが設定されることを確認
		$this->assertSame( '0', get_post_meta( $this->attachment_id, '_wpf_generate_webp', true ) );
	}

	/**
	 * 対応していない画像形式のテスト
	 *
	 * @covers ::create_webp_image
	 */
	public function test_unsupported_image_format() {
		// 実際のアップロードパスを取得
		$upload_info = wp_upload_dir();
		$upload_path = $upload_info['path'];

		// 対応していない形式のファイルを作成
		$unsupported_file = $upload_path . '/test.bmp';
		file_put_contents( $unsupported_file, 'dummy content' ); // phpcs:ignore

		$reflection = new ReflectionClass( $this->handler );
		$method     = $reflection->getMethod( 'create_webp_image' );
		$method->setAccessible( true );

		$result = $method->invoke( $this->handler, $unsupported_file );

		// 失敗することを確認
		$this->assertFalse( $result, 'Unsupported format should fail' );

		// ファイルをクリーンアップ
		if ( file_exists( $unsupported_file ) ) {
			unlink( $unsupported_file );
		}
	}

	/**
	 * 存在しないファイルでのWebP生成テスト
	 *
	 * @covers ::generate_webp_for_attachment
	 */
	public function test_generate_webp_for_nonexistent_file() {
		// 存在しない添付ファイルIDを使用
		$nonexistent_id = 99999;

		$reflection = new ReflectionClass( $this->handler );
		$method     = $reflection->getMethod( 'generate_webp_for_attachment' );
		$method->setAccessible( true );

		$result = $method->invoke( $this->handler, $nonexistent_id );

		// 失敗することを確認
		$this->assertFalse( $result );
	}

	/**
	 * GD拡張を使用したWebP生成のテスト
	 *
	 * @covers ::create_webp_with_gd
	 */
	public function test_create_webp_with_gd() {
		// GD拡張が利用可能な場合のみテスト実行
		if ( ! function_exists( 'imagewebp' ) ) {
			$this->markTestSkipped( 'GD extension with WebP support is not available' );
		}

		// 実際のアップロードパスを取得
		$upload_info = wp_upload_dir();
		$upload_path = $upload_info['path'];

		$reflection = new ReflectionClass( $this->handler );
		$method     = $reflection->getMethod( 'create_webp_with_gd' );
		$method->setAccessible( true );

		$source_path = $this->test_image_path;
		$webp_path   = $upload_path . '/test-gd.webp';

		$result = $method->invoke( $this->handler, $source_path, $webp_path, 'image/jpeg' );

		// 成功することを確認
		$this->assertTrue( $result, 'GD WebP creation should succeed' );

		// WebPファイルが作成されることを確認
		$this->assertTrue( file_exists( $webp_path ), 'WebP file should be created at: ' . $webp_path );

		// クリーンアップ
		if ( file_exists( $webp_path ) ) {
			unlink( $webp_path );
		}
	}

	/**
	 * メタボックス表示のテスト
	 *
	 * @covers ::webp_meta_box_callback
	 */
	public function test_webp_meta_box_callback_for_supported_format() {
		// JPEGファイルの投稿を作成
		global $post;
		$post = get_post( $this->attachment_id ); // phpcs:ignore

		ob_start();
		$this->handler->webp_meta_box_callback( $post );
		$output = ob_get_clean();

		// チェックボックスが表示されることを確認
		$this->assertStringContainsString( 'generate_webp', $output );
		$this->assertStringContainsString( 'WebP版を生成する', $output );
	}

	/**
	 * 対応していない形式でのメタボックス表示テスト
	 *
	 * @covers ::webp_meta_box_callback
	 */
	public function test_webp_meta_box_callback_for_unsupported_format() {
		// 対応していない形式の添付ファイルを作成
		$pdf_id = $this->factory->attachment->create_object(
			'test.pdf',
			0,
			array(
				'post_mime_type' => 'application/pdf',
			)
		);

		global $post;
		$post = get_post( $pdf_id ); // phpcs:ignore

		ob_start();
		$this->handler->webp_meta_box_callback( $post );
		$output = ob_get_clean();

		// エラーメッセージが表示されることを確認
		$this->assertStringContainsString( 'この画像形式ではWebPを生成できません', $output );

		// 添付ファイルをクリーンアップ
		wp_delete_attachment( $pdf_id, true );
	}

	/**
	 * AJAX処理テスト用のヘルパーメソッド
	 * WordPressのAJAX処理を適切にテストするための環境設定
	 */
	private function setup_ajax_environment() {
		// DOING_AJAXフラグを設定
		if ( ! defined( 'DOING_AJAX' ) ) {
			define( 'DOING_AJAX', true );
		}

		// 管理者権限のユーザーを作成・設定
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// $_REQUEST をクリア
		$_REQUEST = array();
	}

	/**
	 * AJAX処理の後処理
	 */
	private function cleanup_ajax_environment() {
        // phpcs:ignore
		// $_POST, $_REQUEST をクリア
		$_POST    = array();
		$_REQUEST = array();

		// ユーザーをリセット
		wp_set_current_user( 0 );
	}

	/**
	 * AJAX処理を実行し、出力をキャプチャするヘルパー
	 *
	 * @param array $post_data POSTデータ
	 * @return array レスポンス情報
	 */
	private function make_ajax_call( $post_data ) {
		// POSTデータを設定
		$_POST    = $post_data;
		$_REQUEST = array_merge( $_REQUEST, $post_data ); // phpcs:ignore

		// 出力バッファリング開始
		ob_start();

		// レスポンス用の変数
		$response = array(
			'output' => '',
			'died'   => false,
		);

		// wp_dieハンドラーを一時的に変更
		add_filter( 'wp_die_ajax_handler', array( $this, 'ajax_die_handler' ) );

		try {
			// AJAX処理を実行
			do_action( 'wp_ajax_toggle_webp_generation' );
		} catch ( WPAjaxDieContinueException $e ) {
			// 正常なAJAX終了
			$response['died'] = true;
		}

		// 出力を取得
		$response['output'] = ob_get_clean();

		// フィルターを削除
		remove_filter( 'wp_die_ajax_handler', array( $this, 'ajax_die_handler' ) );

		return $response;
	}

	/**
	 * AJAX用のwp_dieハンドラー
	 *
	 * @param string $message die メッセージ
	 * @throws WPAjaxDieContinueException 実行終了エクセプションクラス
	 */
	public function ajax_die_handler( $message ) {
		// 例外を投げてテストを継続
		throw new WPAjaxDieContinueException( $message );
	}

	/**
	 * AJAX - WebP生成成功のテスト
	 *
	 * @covers ::ajax_toggle_webp_generation
	 */
	public function test_ajax_toggle_webp_generation_success() {
		$this->setup_ajax_environment();

		// 実際のアップロードパスを取得
		$upload_info = wp_upload_dir();
		$upload_path = $upload_info['path'];
		$webp_path   = $upload_path . '/test.webp';

		// WebPが存在しないことを確認
		if ( file_exists( $webp_path ) ) {
			unlink( $webp_path );
		}

		// AJAX用のPOSTデータを設定
		$post_data = array(
			'action'        => 'toggle_webp_generation',
			'attachment_id' => $this->attachment_id,
			'generate'      => 'true',
			'nonce'         => wp_create_nonce( 'webp_nonce' ),
		);

		$response = $this->make_ajax_call( $post_data );

		// AJAX処理が正常に終了したことを確認
		$this->assertTrue( $response['died'], 'AJAX should call wp_die()' );

		// 出力がJSON形式であることを確認
		$json_response = json_decode( $response['output'], true );

		if ( null !== $json_response ) {
			// JSONレスポンスが成功を示すことを確認
			$this->assertTrue( isset( $json_response['success'] ) );
		}

		// WebPファイルが生成されることを確認
		$this->assertTrue( file_exists( $webp_path ), 'WebP file should be generated at: ' . $webp_path );

		// メタデータが更新されることを確認
		$this->assertSame( '1', get_post_meta( $this->attachment_id, '_wpf_generate_webp', true ) );

		$this->cleanup_ajax_environment();
	}

	/**
	 * AJAX - WebP削除成功のテスト
	 *
	 * @covers ::ajax_toggle_webp_generation
	 */
	public function test_ajax_toggle_webp_deletion_success() {
		$this->setup_ajax_environment();

		// 実際のアップロードパスを取得
		$upload_info = wp_upload_dir();
		$upload_path = $upload_info['path'];
		$webp_path   = $upload_path . '/test.webp';

		// WebPファイルを事前に作成
		file_put_contents( $webp_path, 'dummy webp content' ); // phpcs:ignore
		$this->assertTrue( file_exists( $webp_path ) );

		// AJAX用のPOSTデータを設定
		$post_data = array(
			'action'        => 'toggle_webp_generation',
			'attachment_id' => $this->attachment_id,
			'generate'      => 'false',
			'nonce'         => wp_create_nonce( 'webp_nonce' ),
		);

		$response = $this->make_ajax_call( $post_data );

		// AJAX処理が正常に終了したことを確認
		$this->assertTrue( $response['died'], 'AJAX should call wp_die()' );

		// WebPファイルが削除されることを確認
		$this->assertFalse( file_exists( $webp_path ), 'WebP file should be deleted: ' . $webp_path );

		// メタデータが更新されることを確認
		$this->assertSame( '0', get_post_meta( $this->attachment_id, '_wpf_generate_webp', true ) );

		$this->cleanup_ajax_environment();
	}

	/**
	 * AJAX - 無効なnonce エラーのテスト
	 *
	 * @covers ::ajax_toggle_webp_generation
	 */
	public function test_ajax_toggle_webp_generation_invalid_nonce() {
		$this->setup_ajax_environment();

		// 無効なnonceでPOSTデータを設定
		$post_data = array(
			'action'        => 'toggle_webp_generation',
			'attachment_id' => $this->attachment_id,
			'generate'      => 'true',
			'nonce'         => 'invalid_nonce',
		);

		$response = $this->make_ajax_call( $post_data );

		// nonce検証でエラーになることを確認
		// WordPressは無効なnonceの場合-1を返すか、wp_die()を呼び出す
		$this->assertTrue(
			$response['died'] || '-1' === $response['output'],
			'Invalid nonce should result in error'
		);

		// WebPファイルが生成されないことを確認
		$upload_info = wp_upload_dir();
		$upload_path = $upload_info['path'];
		$webp_path   = $upload_path . '/test.webp';
		$this->assertFalse( file_exists( $webp_path ), 'WebP should not be generated with invalid nonce' );

		$this->cleanup_ajax_environment();
	}

	/**
	 * AJAX - 権限不足エラーのテスト
	 *
	 * @covers ::ajax_toggle_webp_generation
	 */
	public function test_ajax_toggle_webp_generation_insufficient_permission() {
		// 権限のないユーザーを設定
		if ( ! defined( 'DOING_AJAX' ) ) {
			define( 'DOING_AJAX', true );
		}

		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		// POSTデータを設定
		$post_data = array(
			'action'        => 'toggle_webp_generation',
			'attachment_id' => $this->attachment_id,
			'generate'      => 'true',
			'nonce'         => wp_create_nonce( 'webp_nonce' ),
		);

		$response = $this->make_ajax_call( $post_data );

		// 権限エラーが発生することを確認
		$this->assertTrue( $response['died'], 'Insufficient permission should result in wp_die()' );

		// JSONレスポンスがエラーを示すことを確認
		$json_response = json_decode( $response['output'], true );
		if ( null !== $json_response && isset( $json_response['success'] ) ) {
			$this->assertFalse( $json_response['success'], 'Response should indicate failure' );
		}

		$this->cleanup_ajax_environment();
	}

	/**
	 * AJAX - attachment_id未設定エラーのテスト
	 *
	 * @covers ::ajax_toggle_webp_generation
	 */
	public function test_ajax_toggle_webp_generation_missing_attachment_id() {
		$this->setup_ajax_environment();

		// attachment_idを含まないPOSTデータを設定
		$post_data = array(
			'action'   => 'toggle_webp_generation',
			'generate' => 'true',
			'nonce'    => wp_create_nonce( 'webp_nonce' ),
		);

		$response = $this->make_ajax_call( $post_data );

		// パラメータエラーが発生することを確認
		$this->assertTrue( $response['died'], 'Missing attachment_id should result in wp_die()' );

		// JSONレスポンスがエラーを示すことを確認
		$json_response = json_decode( $response['output'], true );
		if ( null !== $json_response && isset( $json_response['success'] ) ) {
			$this->assertFalse( $json_response['success'], 'Response should indicate failure' );
		}

		$this->cleanup_ajax_environment();
	}

	/**
	 * AJAX - generate未設定エラーのテスト
	 *
	 * @covers ::ajax_toggle_webp_generation
	 */
	public function test_ajax_toggle_webp_generation_missing_generate() {
		$this->setup_ajax_environment();

		// generateを含まないPOSTデータを設定
		$post_data = array(
			'action'        => 'toggle_webp_generation',
			'attachment_id' => $this->attachment_id,
			'nonce'         => wp_create_nonce( 'webp_nonce' ),
		);

		$response = $this->make_ajax_call( $post_data );

		// パラメータエラーが発生することを確認
		$this->assertTrue( $response['died'], 'Missing generate parameter should result in wp_die()' );

		$this->cleanup_ajax_environment();
	}

	/**
	 * AJAX - 存在しない添付ファイルエラーのテスト
	 *
	 * @covers ::ajax_toggle_webp_generation
	 */
	public function test_ajax_toggle_webp_generation_nonexistent_attachment() {
		$this->setup_ajax_environment();

		// 存在しない添付ファイルIDでPOSTデータを設定
		$post_data = array(
			'action'        => 'toggle_webp_generation',
			'attachment_id' => 99999, // 存在しないID
			'generate'      => 'true',
			'nonce'         => wp_create_nonce( 'webp_nonce' ),
		);

		$response = $this->make_ajax_call( $post_data );

		// 添付ファイル不存在エラーが発生することを確認
		$this->assertTrue( $response['died'], 'Nonexistent attachment should result in wp_die()' );

		$this->cleanup_ajax_environment();
	}

	/**
	 * 代替テスト手法：直接メソッド呼び出しによる検証
	 * AJAX環境での問題を回避するために、メソッドを直接呼び出してテスト
	 *
	 * @covers ::ajax_toggle_webp_generation
	 */
	public function test_ajax_method_direct_call() {
		// 管理者権限を設定
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// POSTデータを設定
		$_POST = array(
			'action'        => 'toggle_webp_generation',
			'attachment_id' => $this->attachment_id,
			'generate'      => 'true',
			'nonce'         => wp_create_nonce( 'webp_nonce' ),
		);

		// 実際のアップロードパスを取得
		$upload_info = wp_upload_dir();
		$upload_path = $upload_info['path'];
		$webp_path   = $upload_path . '/test.webp';

		// WebPファイルを削除（存在する場合）
		if ( file_exists( $webp_path ) ) {
			unlink( $webp_path );
		}

		// 出力バッファリングでJSONレスポンスをキャプチャ
		ob_start();

		// wp_dieハンドラーを一時的に変更（何もしないハンドラー）
		add_filter(
			'wp_die_ajax_handler',
			function() {
				return '__return_false';
			}
		);

		// AJAXメソッドを直接呼び出し
		try {
			$this->handler->ajax_toggle_webp_generation();
		} catch ( Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// wp_die() による終了を想定した例外なので無視してOK。
		}

		$output = ob_get_clean();

		// フィルターを削除
		remove_all_filters( 'wp_die_ajax_handler' );

		// WebPファイルが生成されることを確認（メインの動作確認）
		$this->assertTrue( file_exists( $webp_path ), 'WebP file should be generated' );

		// メタデータが更新されることを確認
		$this->assertSame( '1', get_post_meta( $this->attachment_id, '_wpf_generate_webp', true ) );

		// クリーンアップ
		$_POST = array();
		wp_set_current_user( 0 );
	}
}

/**
 * AJAX処理テスト用の例外クラス
 * WordPressのテスト環境で使用
 */
if ( ! class_exists( 'WPAjaxDieContinueException' ) ) {
	class WPAjaxDieContinueException extends Exception {} // phpcs:ignore
}
