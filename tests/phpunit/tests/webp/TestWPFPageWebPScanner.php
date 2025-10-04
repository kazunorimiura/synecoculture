<?php

/**
 * WPF_Page_WebP_Scanner のユニットテスト
 *
 * @group page_webp_scanner
 * @covers WPF_Page_WebP_Scanner
 * @coversDefaultClass WPF_Page_WebP_Scanner
 */
class TestWPFPageWebPScanner extends WPF_UnitTestCase {

	/**
	 * @var WPF_Page_WebP_Scanner
	 */
	private $scanner;

	/**
	 * @var WPF_Selective_WebP_Image_Handler
	 */
	private $webp_handler;

	/**
	 * @var int
	 */
	private $post_id;

	/**
	 * @var int
	 */
	private $category_id;

	/**
	 * @var int
	 */
	private $attachment_id;

	/**
	 * @var int
	 */
	private $additional_attachment_id;

	/**
	 * @var string
	 */
	private $test_image_path;

	/**
	 * @var string
	 */
	private $upload_dir;

	/**
	 * @var array
	 */
	private $test_files = array();

	/**
	 * テストの実行前に必要なデータを準備する
	 */
	public function set_up() {
		parent::set_up();

		// WebPハンドラーのインスタンスを作成
		$this->webp_handler = new WPF_Selective_WebP_Image_Handler();

		// Page WebP Scanner のインスタンスを作成
		$this->scanner = new WPF_Page_WebP_Scanner( $this->webp_handler );

		// アップロードディレクトリの設定
		$upload_info      = wp_upload_dir();
		$this->upload_dir = $upload_info['basedir'];

		// アップロードディレクトリが存在しない場合は作成
		if ( ! file_exists( $this->upload_dir ) ) {
			wp_mkdir_p( $this->upload_dir );
		}

		// 複数のテスト用画像を作成
		$this->create_test_images();

		// テスト用添付ファイルを作成
		$this->create_test_attachments();

		// テスト用投稿とカテゴリを作成
		$this->create_test_posts_and_categories();
	}

	/**
	 * テストの実行後にデータをクリーンアップする
	 */
	public function tear_down() {
		$this->cleanup_test_files();
		$this->cleanup_test_posts();
		parent::tear_down();
	}

	/**
	 * 複数のテスト用画像を作成
	 */
	private function create_test_images() {
		$upload_info = wp_upload_dir();
		$upload_path = $upload_info['path'];

		$images = array(
			'test.jpg'          => $this->get_test_jpeg_data(),
			'test-150x150.jpg'  => $this->get_test_jpeg_data(),
			'test2.png'         => $this->get_test_png_data(),
			'test2-300x200.png' => $this->get_test_png_data(),
		);

		foreach ( $images as $filename => $data ) {
			$image_path = $upload_path . '/' . $filename;

			if ( ! file_exists( $upload_path ) ) {
				wp_mkdir_p( $upload_path );
			}

			file_put_contents( $image_path, $data ); // phpcs:ignore
			$this->test_files[] = $image_path;

			if ( 'test.jpg' === $filename ) {
				$this->test_image_path = $image_path;
			}
		}
	}

	/**
	 * テスト用添付ファイルを作成
	 */
	private function create_test_attachments() {
		$upload_info = wp_upload_dir();
		$subdir      = $upload_info['subdir'];

		// 第1の添付ファイル（JPEG）
		$this->attachment_id = $this->factory->attachment->create_object(
			'test.jpg',
			0,
			array(
				'post_mime_type' => 'image/jpeg',
				'post_title'     => 'Test Image',
			)
		);

		wp_update_attachment_metadata(
			$this->attachment_id,
			array(
				'file'   => ltrim( $subdir . '/test.jpg', '/' ),
				'width'  => 100,
				'height' => 100,
				'sizes'  => array(
					'thumbnail' => array(
						'file'   => 'test-150x150.jpg',
						'width'  => 150,
						'height' => 150,
					),
				),
			)
		);

		// 第2の添付ファイル（PNG）
		$this->additional_attachment_id = $this->factory->attachment->create_object(
			'test2.png',
			0,
			array(
				'post_mime_type' => 'image/png',
				'post_title'     => 'Test Image 2',
			)
		);

		wp_update_attachment_metadata(
			$this->additional_attachment_id,
			array(
				'file'   => ltrim( $subdir . '/test2.png', '/' ),
				'width'  => 300,
				'height' => 200,
				'sizes'  => array(
					'medium' => array(
						'file'   => 'test2-300x200.png',
						'width'  => 300,
						'height' => 200,
					),
				),
			)
		);
	}

	/**
	 * テスト用投稿とカテゴリを作成
	 */
	private function create_test_posts_and_categories() {
		// カテゴリを作成
		$this->category_id = $this->factory->category->create(
			array(
				'name' => 'Test Category',
				'slug' => 'test-category',
			)
		);

		// 投稿を作成
		$content = sprintf(
			'<p>This is a test post with images.</p>
			<img src="%s" class="wp-image-%d" alt="Test Image">
			<p>More content here.</p>
			<img src="%s" class="wp-image-%d" alt="Test Image 2">
			<div class="gallery">
				<img src="%s" class="wp-image-%d" alt="Test Image in gallery">
			</div>',
			wp_get_attachment_url( $this->attachment_id ),
			$this->attachment_id,
			wp_get_attachment_url( $this->additional_attachment_id ),
			$this->additional_attachment_id,
			wp_get_attachment_url( $this->attachment_id ),
			$this->attachment_id
		);

		$this->post_id = $this->factory->post->create(
			array(
				'post_title'    => 'Test Post with Multiple Images',
				'post_content'  => $content,
				'post_status'   => 'publish',
				'post_category' => array( $this->category_id ),
			)
		);

		set_post_thumbnail( $this->post_id, $this->attachment_id );
	}

	/**
	 * テストファイルのクリーンアップ
	 */
	private function cleanup_test_files() {
		foreach ( $this->test_files as $file ) {
			if ( file_exists( $file ) ) {
				unlink( $file );
			}
		}

		// WebPファイルも削除
		$upload_info = wp_upload_dir();
		$upload_path = $upload_info['path'];
		$webp_files  = glob( $upload_path . '/*.webp' );
		foreach ( $webp_files as $webp_file ) {
			if ( file_exists( $webp_file ) ) {
				unlink( $webp_file );
			}
		}
	}

	/**
	 * テスト投稿のクリーンアップ
	 */
	private function cleanup_test_posts() {
		if ( $this->post_id ) {
			wp_delete_post( $this->post_id, true );
		}
		if ( $this->attachment_id ) {
			wp_delete_attachment( $this->attachment_id, true );
		}
		if ( $this->additional_attachment_id ) {
			wp_delete_attachment( $this->additional_attachment_id, true );
		}
		if ( $this->category_id ) {
			wp_delete_category( $this->category_id );
		}
	}

	/**
	 * テスト用JPEG画像データを取得
	 */
	private function get_test_jpeg_data() {
		return base64_decode( '/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/gA==' ); // phpcs:ignore
	}

	/**
	 * テスト用PNG画像データを取得
	 */
	private function get_test_png_data() {
		return base64_decode( 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChAGA2hQTxwAAAABJRU5ErkJggg==' ); // phpcs:ignore
	}

	// ========================================
	// 基本機能のテスト
	// ========================================

	/**
	 * フックが正しく登録されているかテスト
	 *
	 * @covers ::__construct
	 */
	public function test_hooks_are_registered() {
		$this->assertTrue( has_action( 'admin_menu' ) );
		$this->assertTrue( has_action( 'wp_ajax_scan_page_images' ) );
		$this->assertTrue( has_action( 'wp_ajax_generate_selected_webp' ) );
		$this->assertTrue( has_action( 'wp_ajax_delete_selected_webp' ) );
	}

	/**
	 * 管理画面ページの追加テスト
	 *
	 * @covers ::add_admin_page
	 */
	public function test_add_admin_page() {
		global $submenu;

		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		$this->scanner->add_admin_page();

		$this->assertArrayHasKey( 'tools.php', $submenu );

		$found = false;
		foreach ( $submenu['tools.php'] as $menu_item ) {
			if ( 'page-webp-generator' === $menu_item[2] ) {
				$found = true;
				break;
			}
		}

		$this->assertTrue( $found, 'Admin page should be added to tools menu' );
	}

	/**
	 * 管理画面の表示テスト
	 *
	 * @covers ::admin_page_callback
	 */
	public function test_admin_page_callback() {
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		ob_start();
		$this->scanner->admin_page_callback();
		$output = ob_get_clean();

		// タイトルの変更を確認
		$this->assertStringContainsString( 'ページ単位WebP生成・削除', $output );
		$this->assertStringContainsString( 'URL指定してWebP操作', $output );

		// 既存機能の確認
		$this->assertStringContainsString( 'page_url', $output );
		$this->assertStringContainsString( 'post_id', $output );
		$this->assertStringContainsString( 'scanPageImages()', $output );
		$this->assertStringContainsString( 'generateSelectedWebP', $output );
		$this->assertStringContainsString( 'deleteSelectedWebP', $output );
	}

	/**
	 * 同一サイト内URL判定テスト
	 *
	 * @covers ::is_same_site_url
	 */
	public function test_is_same_site_url() {
		$reflection = new ReflectionClass( $this->scanner );
		$method     = $reflection->getMethod( 'is_same_site_url' );
		$method->setAccessible( true );

		$home_url = home_url();

		// 同一サイト内URL
		$this->assertTrue( $method->invoke( $this->scanner, $home_url ) );
		$this->assertTrue( $method->invoke( $this->scanner, home_url( '/category/test/' ) ) );
		$this->assertTrue( $method->invoke( $this->scanner, home_url( '/search/?s=test' ) ) );

		// 外部URL
		$this->assertFalse( $method->invoke( $this->scanner, 'https://external.com/page/' ) );
		$this->assertFalse( $method->invoke( $this->scanner, 'http://malicious.site.com/bad' ) );
	}

	/**
	 * ページタイトル取得テスト
	 *
	 * @covers ::get_page_title
	 */
	public function test_get_page_title() {
		$reflection = new ReflectionClass( $this->scanner );
		$method     = $reflection->getMethod( 'get_page_title' );
		$method->setAccessible( true );

		// 投稿が存在する場合
		$post  = get_post( $this->post_id );
		$title = $method->invoke( $this->scanner, get_permalink( $post ), $post, null );
		$this->assertEquals( get_the_title( $post ), $title );

		// HTMLからタイトルを抽出
		$html_with_title = '<html><head><title>Test Page Title</title></head><body>Content</body></html>';
		$title           = $method->invoke( $this->scanner, home_url( '/test/' ), null, $html_with_title );
		$this->assertEquals( 'Test Page Title', $title );

		// URLパスからタイトルを推測
		$title = $method->invoke( $this->scanner, home_url( '/category/sample-category/' ), null, null );
		$this->assertEquals( 'Sample Category', $title );

		// ホームページ
		$title = $method->invoke( $this->scanner, home_url( '/' ), null, null );
		$this->assertEquals( 'ホームページ', $title );
	}

	/**
	 * URLでの画像スキャンテスト
	 *
	 * @covers ::scan_url_images
	 */
	public function test_scan_url_images() {
		$reflection = new ReflectionClass( $this->scanner );
		$method     = $reflection->getMethod( 'scan_url_images' );
		$method->setAccessible( true );

		// モックHTMLを作成
		$mock_html = sprintf(
			'<html><head><title>Test Page</title></head><body>
				<img src="%s" class="wp-image-%d" alt="Test Image">
				<p>Some content</p>
			</body></html>',
			wp_get_attachment_url( $this->attachment_id ),
			$this->attachment_id
		);

		// get_url_htmlメソッドをモック
		$scanner_mock = $this->getMockBuilder( get_class( $this->scanner ) )
			->setConstructorArgs( array( $this->webp_handler ) )
			->onlyMethods( array( 'get_url_html' ) )
			->getMock();

		$scanner_mock->method( 'get_url_html' )
			->willReturn( $mock_html );

		$reflection_mock = new ReflectionClass( $scanner_mock );
		$method_mock     = $reflection_mock->getMethod( 'scan_url_images' );
		$method_mock->setAccessible( true );

		$result = $method_mock->invoke( $scanner_mock, home_url( '/test/' ), null );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'page_title', $result );
		$this->assertArrayHasKey( 'page_url', $result );
		$this->assertArrayHasKey( 'total_images', $result );
		$this->assertArrayHasKey( 'pending_images', $result );
		$this->assertArrayHasKey( 'existing_webp', $result );
		$this->assertArrayHasKey( 'scan_method', $result );

		$this->assertEquals( 'Test Page', $result['page_title'] );
		$this->assertEquals( 'html', $result['scan_method'] );
		$this->assertGreaterThan( 0, $result['total_images'] );
	}

	// ========================================
	// AJAX機能のテスト（更新版）
	// ========================================

	/**
	 * AJAX環境設定
	 */
	private function setup_ajax_environment() {
		if ( ! defined( 'DOING_AJAX' ) ) {
			define( 'DOING_AJAX', true );
		}

		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		$_REQUEST = array();
	}

	/**
	 * AJAX環境クリーンアップ
	 */
	private function cleanup_ajax_environment() {
		$_POST    = array();
		$_REQUEST = array();
		wp_set_current_user( 0 );
	}

	/**
	 * AJAX処理実行ヘルパー
	 *
	 * @param string $action_name アクション名
	 * @param array  $post_data 投稿データ
	 * @return array
	 */
	private function make_ajax_call( $action_name, $post_data ) {
		$_POST    = $post_data;
		$_REQUEST = array_merge( $_REQUEST, $post_data ); // phpcs:ignore

		ob_start();

		$response = array(
			'output'      => '',
			'died'        => false,
			'json_output' => null,
		);

		add_filter( 'wp_die_ajax_handler', array( $this, 'ajax_die_handler' ) );

		try {
			do_action( 'wp_ajax_' . $action_name );
		} catch ( WPAjaxDieContinueException $e ) {
			$response['died'] = true;
		}

		$response['output'] = ob_get_clean();

		$json_response = json_decode( $response['output'], true );
		if ( null !== $json_response ) {
			$response['json_output'] = $json_response;
		}

		remove_filter( 'wp_die_ajax_handler', array( $this, 'ajax_die_handler' ) );

		return $response;
	}

	/**
	 * AJAX用wp_dieハンドラー
	 *
	 * @param string $message メッセージ
	 * @return void
	 * @throws WPAjaxDieContinueException WPAjaxDieContinueException
	 */
	public function ajax_die_handler( $message ) {
		throw new WPAjaxDieContinueException( $message );
	}

	/**
	 * URL指定での画像スキャンAJAXテスト
	 *
	 * @covers ::ajax_scan_page_images
	 */
	public function test_ajax_scan_page_images_by_url() {
		$this->setup_ajax_environment();

		$post_url = get_permalink( $this->post_id );

		$post_data = array(
			'action'   => 'scan_page_images',
			'page_url' => $post_url,
			'mode'     => 'scan',
			'nonce'    => wp_create_nonce( 'page_webp_nonce' ),
		);

		$response = $this->make_ajax_call( 'scan_page_images', $post_data );

		$this->assertTrue( $response['died'] );
		$this->assertNotNull( $response['json_output'] );

		if ( $response['json_output'] ) {
			$this->assertTrue( $response['json_output']['success'] );

			$data = $response['json_output']['data'];
			$this->assertArrayHasKey( 'page_title', $data );
			$this->assertArrayHasKey( 'page_url', $data );
			$this->assertArrayHasKey( 'total_images', $data );
			$this->assertArrayHasKey( 'pending_images', $data );
			$this->assertArrayHasKey( 'existing_webp', $data );
			$this->assertArrayHasKey( 'scan_method', $data );

			$this->assertEquals( $post_url, $data['page_url'] );
		}

		$this->cleanup_ajax_environment();
	}

	/**
	 * 外部URLでの画像スキャン拒否テスト
	 *
	 * @covers ::ajax_scan_page_images
	 */
	public function test_ajax_scan_page_images_external_url_rejected() {
		$this->setup_ajax_environment();

		$post_data = array(
			'action'   => 'scan_page_images',
			'page_url' => 'https://external.com/malicious-page/',
			'mode'     => 'scan',
			'nonce'    => wp_create_nonce( 'page_webp_nonce' ),
		);

		$response = $this->make_ajax_call( 'scan_page_images', $post_data );

		$this->assertTrue( $response['died'] );
		$this->assertNotNull( $response['json_output'] );

		if ( $response['json_output'] ) {
			$this->assertFalse( $response['json_output']['success'] );
			$this->assertStringContainsString( '同じサイト内のものではありません', $response['json_output']['data'] );
		}

		$this->cleanup_ajax_environment();
	}

	/**
	 * 投稿ID指定での画像スキャンAJAXテスト（既存機能の確認）
	 *
	 * @covers ::ajax_scan_page_images
	 */
	public function test_ajax_scan_page_images_by_post_id() {
		$this->setup_ajax_environment();

		$post_data = array(
			'action'  => 'scan_page_images',
			'post_id' => $this->post_id,
			'mode'    => 'scan',
			'nonce'   => wp_create_nonce( 'page_webp_nonce' ),
		);

		$response = $this->make_ajax_call( 'scan_page_images', $post_data );

		$this->assertTrue( $response['died'] );
		$this->assertNotNull( $response['json_output'] );

		if ( $response['json_output'] ) {
			$this->assertTrue( $response['json_output']['success'] );

			$data = $response['json_output']['data'];
			$this->assertArrayHasKey( 'page_title', $data );
			$this->assertArrayHasKey( 'total_images', $data );
			$this->assertArrayHasKey( 'pending_images', $data );
			$this->assertArrayHasKey( 'existing_webp', $data );

			$this->assertGreaterThan( 0, $data['total_images'] );
		}

		$this->cleanup_ajax_environment();
	}

	/**
	 * 存在しない投稿IDでの情報取得エラーテスト
	 *
	 * @covers ::ajax_scan_page_images
	 */
	public function test_ajax_scan_page_images_info_mode_invalid_post_id() {
		$this->setup_ajax_environment();

		$post_data = array(
			'action'  => 'scan_page_images',
			'post_id' => 99999, // 存在しないID
			'mode'    => 'info',
			'nonce'   => wp_create_nonce( 'page_webp_nonce' ),
		);

		$response = $this->make_ajax_call( 'scan_page_images', $post_data );

		$this->assertTrue( $response['died'] );
		$this->assertNotNull( $response['json_output'] );

		if ( $response['json_output'] ) {
			$this->assertFalse( $response['json_output']['success'] );
		}

		$this->cleanup_ajax_environment();
	}

	/**
	 * URLのみでの情報取得モード拒否テスト
	 *
	 * @covers ::ajax_scan_page_images
	 */
	public function test_ajax_scan_page_images_info_mode_url_only_rejected() {
		$this->setup_ajax_environment();

		$post_data = array(
			'action'   => 'scan_page_images',
			'page_url' => home_url( '/category/test/' ),
			'mode'     => 'info',
			'nonce'    => wp_create_nonce( 'page_webp_nonce' ),
		);

		$response = $this->make_ajax_call( 'scan_page_images', $post_data );

		$this->assertTrue( $response['died'] );
		$this->assertNotNull( $response['json_output'] );

		if ( $response['json_output'] ) {
			$this->assertFalse( $response['json_output']['success'] );
			$this->assertStringContainsString( '有効な投稿IDが必要です', $response['json_output']['data'] );
		}

		$this->cleanup_ajax_environment();
	}

	/**
	 * WebP生成AJAXテスト（既存機能の確認）
	 *
	 * @covers ::ajax_generate_selected_webp
	 */
	public function test_ajax_generate_selected_webp() {
		$this->setup_ajax_environment();

		$post_data = array(
			'action'        => 'generate_selected_webp',
			'attachment_id' => $this->attachment_id,
			'nonce'         => wp_create_nonce( 'page_webp_nonce' ),
		);

		$response = $this->make_ajax_call( 'generate_selected_webp', $post_data );

		$this->assertTrue( $response['died'] );
		$this->assertNotNull( $response['json_output'] );

		if ( $response['json_output'] ) {
			$this->assertTrue( $response['json_output']['success'] );

			// メタデータが更新されることを確認
			$this->assertSame( '1', get_post_meta( $this->attachment_id, '_wpf_generate_webp', true ) );
		}

		$this->cleanup_ajax_environment();
	}

	/**
	 * WebP削除AJAXテスト（既存機能の確認）
	 *
	 * @covers ::ajax_delete_selected_webp
	 */
	public function test_ajax_delete_selected_webp() {
		$this->setup_ajax_environment();

		// 事前にWebPファイルを作成
		$upload_info = wp_upload_dir();
		$webp_path   = $upload_info['path'] . '/test.webp';
		file_put_contents( $webp_path, 'dummy webp content' ); // phpcs:ignore
		$this->test_files[] = $webp_path;

		$post_data = array(
			'action'        => 'delete_selected_webp',
			'attachment_id' => $this->attachment_id,
			'nonce'         => wp_create_nonce( 'page_webp_nonce' ),
		);

		$response = $this->make_ajax_call( 'delete_selected_webp', $post_data );

		$this->assertTrue( $response['died'] );
		$this->assertNotNull( $response['json_output'] );

		if ( $response['json_output'] ) {
			$this->assertTrue( $response['json_output']['success'] );

			// メタデータが更新されることを確認
			$this->assertSame( '0', get_post_meta( $this->attachment_id, '_wpf_generate_webp', true ) );
		}

		$this->cleanup_ajax_environment();
	}

	// ========================================
	// エラーハンドリングのテスト
	// ========================================

	/**
	 * 権限チェックテスト（スキャン）
	 *
	 * @covers ::ajax_scan_page_images
	 */
	public function test_ajax_permission_check_scan() {
		if ( ! defined( 'DOING_AJAX' ) ) {
			define( 'DOING_AJAX', true );
		}

		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		$post_data = array(
			'action'  => 'scan_page_images',
			'post_id' => $this->post_id,
			'mode'    => 'scan',
			'nonce'   => wp_create_nonce( 'page_webp_nonce' ),
		);

		$response = $this->make_ajax_call( 'scan_page_images', $post_data );

		$this->assertTrue( $response['died'] );

		if ( $response['json_output'] ) {
			$this->assertFalse( $response['json_output']['success'] );
		}

		$this->cleanup_ajax_environment();
	}

	/**
	 * 無効なnonceテスト（スキャン）
	 *
	 * @covers ::ajax_scan_page_images
	 */
	public function test_ajax_invalid_nonce_scan() {
		$this->setup_ajax_environment();

		$post_data = array(
			'action'  => 'scan_page_images',
			'post_id' => $this->post_id,
			'mode'    => 'scan',
			'nonce'   => 'invalid_nonce',
		);

		$response = $this->make_ajax_call( 'scan_page_images', $post_data );

		$this->assertTrue(
			$response['died'] || '-1' === $response['output'],
			'Invalid nonce should result in error'
		);

		$this->cleanup_ajax_environment();
	}

	/**
	 * URLと投稿ID両方未設定エラーテスト
	 *
	 * @covers ::ajax_scan_page_images
	 */
	public function test_ajax_scan_page_images_missing_url_and_post_id() {
		$this->setup_ajax_environment();

		$post_data = array(
			'action' => 'scan_page_images',
			'mode'   => 'scan',
			'nonce'  => wp_create_nonce( 'page_webp_nonce' ),
		);

		$response = $this->make_ajax_call( 'scan_page_images', $post_data );

		$this->assertTrue( $response['died'] );

		if ( $response['json_output'] ) {
			$this->assertFalse( $response['json_output']['success'] );
			$this->assertStringContainsString( 'URLを指定してください', $response['json_output']['data'] );
		}

		$this->cleanup_ajax_environment();
	}

	// ========================================
	// エッジケースのテスト
	// ========================================

	/**
	 * アイキャッチ画像なしの投稿での任意URLスキャンテスト
	 *
	 * @covers ::scan_url_images
	 */
	public function test_scan_url_images_without_featured_image() {
		delete_post_thumbnail( $this->post_id );

		$reflection = new ReflectionClass( $this->scanner );
		$method     = $reflection->getMethod( 'scan_url_images' );
		$method->setAccessible( true );

		$post = get_post( $this->post_id );
		$url  = get_permalink( $post );

		$result = $method->invoke( $this->scanner, $url, $post );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'page_title', $result );
		$this->assertArrayHasKey( 'page_url', $result );

		// アイキャッチ画像がない場合でも、コンテンツ内の画像は検出される
		$this->assertGreaterThan( 0, $result['total_images'] );
	}

	/**
	 * HTMLが取得できない場合のフォールバックテスト
	 *
	 * @covers ::scan_url_images
	 */
	public function test_scan_url_images_html_fetch_failure_fallback() {
		$reflection = new ReflectionClass( $this->scanner );
		$method     = $reflection->getMethod( 'scan_url_images' );
		$method->setAccessible( true );

		// get_url_htmlが失敗する場合をシミュレート
		$scanner_mock = $this->getMockBuilder( get_class( $this->scanner ) )
			->setConstructorArgs( array( $this->webp_handler ) )
			->onlyMethods( array( 'get_url_html' ) )
			->getMock();

		$scanner_mock->method( 'get_url_html' )
			->willReturn( false );

		$reflection_mock = new ReflectionClass( $scanner_mock );
		$method_mock     = $reflection_mock->getMethod( 'scan_url_images' );
		$method_mock->setAccessible( true );

		$post = get_post( $this->post_id );
		$url  = get_permalink( $post );

		$result = $method_mock->invoke( $scanner_mock, $url, $post );

		$this->assertIsArray( $result );
		$this->assertEquals( 'content', $result['scan_method'] );
		// フォールバック方法でも画像が検出される
		$this->assertGreaterThan( 0, $result['total_images'] );
	}

	/**
	 * 投稿が存在しない URL での処理テスト
	 *
	 * @covers ::scan_url_images
	 */
	public function test_scan_url_images_without_post() {
		$reflection = new ReflectionClass( $this->scanner );
		$method     = $reflection->getMethod( 'scan_url_images' );
		$method->setAccessible( true );

		// モックHTMLを作成（投稿に関連しない任意のページ）
		$mock_html = '<html><head><title>Archive Page</title></head><body><h1>Category Archive</h1><p>No images here</p></body></html>';

		$scanner_mock = $this->getMockBuilder( get_class( $this->scanner ) )
			->setConstructorArgs( array( $this->webp_handler ) )
			->onlyMethods( array( 'get_url_html' ) )
			->getMock();

		$scanner_mock->method( 'get_url_html' )
			->willReturn( $mock_html );

		$reflection_mock = new ReflectionClass( $scanner_mock );
		$method_mock     = $reflection_mock->getMethod( 'scan_url_images' );
		$method_mock->setAccessible( true );

		$result = $method_mock->invoke( $scanner_mock, home_url( '/category/test/' ), null );

		$this->assertIsArray( $result );
		$this->assertEquals( 'Archive Page', $result['page_title'] );
		$this->assertEquals( 'html', $result['scan_method'] );
		$this->assertEquals( 0, $result['total_images'] );
	}

	/**
	 * 複雑なHTMLコンテンツでの画像抽出テスト
	 *
	 * @covers ::extract_images_from_html
	 */
	public function test_extract_images_from_complex_html_any_url() {
		$html = sprintf(
			'<!DOCTYPE html>
			<html>
			<head><title>Complex Archive Page</title></head>
			<body>
				<div class="archive-content">
					<article class="post">
						<img src="%s" class="wp-image-%d alignleft" alt="Test Image" width="100" height="100">
						<p>Post excerpt</p>
					</article>
					<article class="post">
						<img src="%s" class="wp-image-%d gallery-item" alt="Gallery Image">
						<p>Another post</p>
					</article>
					<aside class="sidebar">
						<img src="https://external.com/external.jpg" alt="External Image">
					</aside>
				</div>
			</body>
			</html>',
			wp_get_attachment_url( $this->attachment_id ),
			$this->attachment_id,
			wp_get_attachment_url( $this->additional_attachment_id ),
			$this->additional_attachment_id
		);

		$reflection = new ReflectionClass( $this->scanner );
		$method     = $reflection->getMethod( 'extract_images_from_html' );
		$method->setAccessible( true );

		$result = $method->invoke( $this->scanner, $html );

		$this->assertIsArray( $result );
		$this->assertCount( 2, $result ); // 外部画像は除外される

		foreach ( $result as $image ) {
			$this->assertContains( $image['attachment_id'], array( $this->attachment_id, $this->additional_attachment_id ) );
		}
	}

	// ========================================
	// 新機能の統合テスト
	// ========================================

	/**
	 * 任意URL機能の完全なワークフローテスト
	 *
	 * @covers ::ajax_scan_page_images
	 * @covers ::ajax_generate_selected_webp
	 * @covers ::ajax_delete_selected_webp
	 * @covers ::scan_url_images
	 */
	public function test_any_url_complete_workflow() {
		$this->setup_ajax_environment();

		// 1. 任意のURLをスキャン（投稿URL）
		$target_url = get_permalink( $this->post_id );
		$scan_data  = array(
			'action'   => 'scan_page_images',
			'page_url' => $target_url,
			'mode'     => 'scan',
			'nonce'    => wp_create_nonce( 'page_webp_nonce' ),
		);

		$scan_response = $this->make_ajax_call( 'scan_page_images', $scan_data );
		$this->assertTrue( $scan_response['died'] );
		$this->assertNotNull( $scan_response['json_output'] );
		$this->assertTrue( $scan_response['json_output']['success'] );

		$scan_result = $scan_response['json_output']['data'];
		$this->assertArrayHasKey( 'pending_images', $scan_result );
		$this->assertGreaterThan( 0, count( $scan_result['pending_images'] ) );
		$this->assertEquals( $target_url, $scan_result['page_url'] );

		// 2. 選択した画像のWebPを生成
		$first_image_id = $scan_result['pending_images'][0]['attachment_id'];

		$generate_data = array(
			'action'        => 'generate_selected_webp',
			'attachment_id' => $first_image_id,
			'nonce'         => wp_create_nonce( 'page_webp_nonce' ),
		);

		$generate_response = $this->make_ajax_call( 'generate_selected_webp', $generate_data );
		$this->assertTrue( $generate_response['died'] );
		$this->assertNotNull( $generate_response['json_output'] );
		$this->assertTrue( $generate_response['json_output']['success'] );

		// 3. 再スキャンして生成済みリストに移動したことを確認
		$rescan_response = $this->make_ajax_call( 'scan_page_images', $scan_data );
		$this->assertTrue( $rescan_response['died'] );
		$this->assertNotNull( $rescan_response['json_output'] );
		$this->assertTrue( $rescan_response['json_output']['success'] );

		$rescan_result = $rescan_response['json_output']['data'];
		$this->assertArrayHasKey( 'existing_webp', $rescan_result );

		// 生成済みリストに該当画像が含まれていることを確認
		$found_in_existing = false;
		foreach ( $rescan_result['existing_webp'] as $existing_image ) {
			if ( $existing_image['attachment_id'] === $first_image_id ) {
				$found_in_existing = true;
				break;
			}
		}
		$this->assertTrue( $found_in_existing, 'Generated image should appear in existing_webp list' );

		// 4. 生成したWebPを削除
		$delete_data = array(
			'action'        => 'delete_selected_webp',
			'attachment_id' => $first_image_id,
			'nonce'         => wp_create_nonce( 'page_webp_nonce' ),
		);

		$delete_response = $this->make_ajax_call( 'delete_selected_webp', $delete_data );
		$this->assertTrue( $delete_response['died'] );
		$this->assertNotNull( $delete_response['json_output'] );
		$this->assertTrue( $delete_response['json_output']['success'] );

		$this->cleanup_ajax_environment();
	}

	/**
	 * 管理画面表示内容の詳細テスト
	 *
	 * @covers ::admin_page_callback
	 */
	public function test_admin_page_callback_any_url_support() {
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		ob_start();
		$this->scanner->admin_page_callback();
		$output = ob_get_clean();

		// プレースホルダーURLの確認（カテゴリページの例）
		$this->assertStringContainsString( '/category/sample/', $output );

		// 既存機能の確認
		$this->assertStringContainsString( '.webp-image-checkbox', $output );
		$this->assertStringContainsString( '.webp-selection-controls', $output );
		$this->assertStringContainsString( 'getSelectedImages', $output );
		$this->assertStringContainsString( 'processSelectedImages', $output );
	}
}

/**
 * AJAX処理テスト用の例外クラス
 */
if ( ! class_exists( 'WPAjaxDieContinueException' ) ) {
	class WPAjaxDieContinueException extends Exception {} // phpcs:ignore
}
