<?php
/**
 * WordPress WebP選択的生成・削除機能
 *
 * 指定した画像のみWebP版を生成し、削除時にWebPファイルも削除する
 *
 * @package wordpressfoundation
 */

/**
 * WordPress WebP選択的生成・削除クラス
 */
class WPF_Selective_WebP_Image_Handler {

	/**
	 * メタキー
	 *
	 * @var string
	 */
	private $meta_key = '_wpf_generate_webp';

	/**
	 * コンストラクタ
	 */
	public function __construct() {
		// 管理画面のフック
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_webp_meta_box' ) );
		add_action( 'edit_attachment', array( $this, 'save_webp_preference' ) );

		// AJAX処理
		add_action( 'wp_ajax_toggle_webp_generation', array( $this, 'ajax_toggle_webp_generation' ) );
		add_action( 'wp_ajax_bulk_webp_action', array( $this, 'ajax_bulk_webp_action' ) );

		// 画像削除時のフック
		add_action( 'delete_attachment', array( $this, 'delete_webp_versions' ) );

		// メディアライブラリのカラム追加
		add_filter( 'manage_media_columns', array( $this, 'add_webp_column' ) );
		add_action( 'manage_media_custom_column', array( $this, 'display_webp_column' ), 10, 2 );

		// 一括操作メニュー追加
		add_filter( 'bulk_actions-upload', array( $this, 'add_bulk_actions' ) );
		add_filter( 'handle_bulk_actions-upload', array( $this, 'handle_bulk_actions' ), 10, 3 );

		// 管理画面の通知メッセージ
		add_action( 'admin_notices', array( $this, 'display_admin_notices' ) );

		// メディアライブラリ用のインラインスクリプト
		add_action( 'admin_footer-upload.php', array( $this, 'output_media_library_script' ) );
	}

	/**
	 * 管理画面用スクリプトの読み込み
	 *
	 * @param string $hook_suffix 現在の管理ページ
	 * @return void
	 */
	public function enqueue_admin_scripts( $hook_suffix ) {
		if ( 'upload.php' === $hook_suffix || 'post.php' === $hook_suffix ) {
			wp_enqueue_script( 'jquery' );
			wp_localize_script(
				'jquery',
				'webp_ajax',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'webp_nonce' ),
				)
			);
		}
	}

	/**
	 * 添付ファイル編集画面にWebPオプションを追加
	 */
	public function add_webp_meta_box() {
		add_meta_box(
			'webp-options',
			'WebP設定',
			array( $this, 'webp_meta_box_callback' ),
			'attachment',
			'side',
			'default'
		);
	}

	/**
	 * WebPメタボックスの内容
	 *
	 * @param WP_Post $post WP_Post オブジェクト
	 * @return void
	 */
	public function webp_meta_box_callback( $post ) {
		$mime_type = get_post_mime_type( $post->ID );

		if ( ! in_array( $mime_type, array( 'image/jpeg', 'image/png', 'image/gif' ), true ) ) {
			echo '<p>この画像形式ではWebPを生成できません。</p>';
			return;
		}

		$generate_webp = get_post_meta( $post->ID, $this->meta_key, true );
		$webp_exists   = $this->check_webp_exists( $post->ID );

		wp_nonce_field( 'webp_meta_box_nonce', 'webp_meta_box_nonce' );

		echo '<p>';
		echo '<label>';
		echo '<input type="checkbox" name="generate_webp" value="1" ' . checked( $generate_webp, '1', false ) . '>';
		echo ' WebP版を生成する';
		echo '</label>';
		echo '</p>';

		if ( $webp_exists ) {
			echo '<p><strong style="color: green;">✓ WebPファイルが存在します</strong></p>';
			echo '<p><button type="button" class="button" onclick="deleteWebP(' . esc_attr( $post->ID ) . ')">WebPファイルを削除</button></p>';

			// 設定がオフなのにWebPが存在する場合の警告
			if ( '1' !== $generate_webp ) {
				echo '<p style="color: orange;"><small>※ WebP生成設定がオフですが、WebPファイルが存在します</small></p>';
			}
		} else {
			echo '<p style="color: #666;">WebPファイルは未生成です</p>';

			// WebPが存在しない場合は常に生成ボタンを表示
			echo '<p><button type="button" class="button button-primary" onclick="generateWebP(' . esc_attr( $post->ID ) . ')">今すぐWebP生成</button></p>';

			// 設定がオンなのにWebPが存在しない場合の注意
			if ( '1' === $generate_webp ) {
				echo '<p style="color: orange;"><small>※ WebP生成設定がオンですが、ファイルが見つかりません</small></p>';
			}
		}

		?>
		<script>
		function generateWebP(attachmentId) {
			if (confirm('WebPファイルを生成しますか？')) {
				var button = event.target;
				button.disabled = true;
				button.textContent = '生成中...';

				jQuery.post(webp_ajax.ajax_url, {
					action: 'toggle_webp_generation',
					attachment_id: attachmentId,
					generate: true,
					nonce: webp_ajax.nonce
				})
				.done(function(response) {
					if (response.success) {
						alert('WebPを生成しました！');
						location.reload();
					} else {
						alert('WebP生成に失敗しました: ' + (response.data || '不明なエラー'));
						button.disabled = false;
						button.textContent = '今すぐWebP生成';
					}
				})
				.fail(function(xhr, status, error) {
					alert('通信エラーが発生しました: ' + error);
					button.disabled = false;
					button.textContent = '今すぐWebP生成';
				});
			}
		}

		function deleteWebP(attachmentId) {
			if (confirm('WebPファイルを削除しますか？')) {
				var button = event.target;
				button.disabled = true;
				button.textContent = '削除中...';

				jQuery.post(webp_ajax.ajax_url, {
					action: 'toggle_webp_generation',
					attachment_id: attachmentId,
					generate: false,
					nonce: webp_ajax.nonce
				})
				.done(function(response) {
					if (response.success) {
						alert('WebPを削除しました！');
						location.reload();
					} else {
						alert('WebP削除に失敗しました: ' + (response.data || '不明なエラー'));
						button.disabled = false;
						button.textContent = 'WebPファイルを削除';
					}
				})
				.fail(function(xhr, status, error) {
					alert('通信エラーが発生しました: ' + error);
					button.disabled = false;
					button.textContent = 'WebPファイルを削除';
				});
			}
		}
		</script>
		<?php
	}

	/**
	 * WebP設定の保存
	 *
	 * @param int $attachment_id アタッチメントID
	 * @return void
	 */
	public function save_webp_preference( $attachment_id ) {
		if ( ! isset( $_POST['webp_meta_box_nonce'] ) ||
			! wp_verify_nonce( sanitize_key( $_POST['webp_meta_box_nonce'] ), 'webp_meta_box_nonce' ) ) {
			return;
		}

		$generate_webp = isset( $_POST['generate_webp'] ) ? '1' : '0';
		update_post_meta( $attachment_id, $this->meta_key, $generate_webp );

		// チェックが入っている場合はWebPを生成
		if ( '1' === $generate_webp ) {
			$this->generate_webp_for_attachment( $attachment_id );
		} else {
			// チェックが外れている場合はWebPを削除
			$this->delete_webp_versions( $attachment_id );
		}
	}

	/**
	 * AJAX: WebP生成/削除の切り替え
	 */
	public function ajax_toggle_webp_generation() {
		check_ajax_referer( 'webp_nonce', 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( '権限がありません' );
		}

		if ( ! isset( $_POST['attachment_id'] ) ) {
			wp_send_json_error( 'attachment_idキーがありません' );
		}

		if ( ! isset( $_POST['generate'] ) ) {
			wp_send_json_error( 'generateキーがありません' );
		}

		$attachment_id = intval( $_POST['attachment_id'] );
		$generate      = filter_var( sanitize_key( $_POST['generate'] ), FILTER_VALIDATE_BOOLEAN );

		// 添付ファイルの存在確認
		if ( ! get_post( $attachment_id ) || get_post_type( $attachment_id ) !== 'attachment' ) {
			wp_send_json_error( '添付ファイルが見つかりません' );
		}

		if ( $generate ) {
			$result = $this->generate_webp_for_attachment( $attachment_id );
			if ( $result ) {
				update_post_meta( $attachment_id, $this->meta_key, '1' );

				// 生成後に実際にファイルが存在するか確認
				if ( $this->check_webp_exists( $attachment_id ) ) {
					wp_send_json_success( 'WebPを生成しました' );
				} else {
					wp_send_json_error( 'WebPファイルは生成されましたが、確認できませんでした' );
				}
			} else {
				wp_send_json_error( 'WebP生成処理に失敗しました。サーバーログを確認してください。' );
			}
		} else {
			$this->delete_webp_versions( $attachment_id );
			update_post_meta( $attachment_id, $this->meta_key, '0' );

			// 削除後に実際にファイルが削除されたか確認
			if ( ! $this->check_webp_exists( $attachment_id ) ) {
				wp_send_json_success( 'WebPを削除しました' );
			} else {
				wp_send_json_error( 'WebPファイルの削除を試みましたが、まだ存在しています' );
			}
		}
	}

	/**
	 * メディアライブラリにWebPカラムを追加
	 *
	 * @param string[] $columns メディア一覧表に表示される列の配列
	 * @return string[]
	 */
	public function add_webp_column( $columns ) {
		$columns['webp_status'] = 'WebP';
		return $columns;
	}

	/**
	 * WebPカラムの内容を表示
	 *
	 * @param string $column_name カスタムカラムの名前
	 * @param int    $attachment_id アタッチメントID
	 * @return void
	 */
	public function display_webp_column( $column_name, $attachment_id ) {
		if ( 'webp_status' === $column_name ) {
			$mime_type = get_post_mime_type( $attachment_id );

			if ( ! in_array( $mime_type, array( 'image/jpeg', 'image/png', 'image/gif' ), true ) ) {
				echo '-';
				return;
			}

			$generate_webp = get_post_meta( $attachment_id, $this->meta_key, true );
			$webp_exists   = $this->check_webp_exists( $attachment_id );

			echo '<div class="webp-status-' . esc_attr( $attachment_id ) . '">';

			if ( $webp_exists ) {
				echo '<span style="color: green;">✓ 生成済み</span><br>';
				echo '<button type="button" class="button-link" onclick="toggleWebPInline(' . esc_attr( $attachment_id ) . ', false)">削除</button>';
			} else {
				echo '<span style="color: #666;">未生成</span><br>';
				echo '<button type="button" class="button-link" onclick="toggleWebPInline(' . esc_attr( $attachment_id ) . ', true)">生成</button>';
			}

			echo '</div>';
		}
	}

	/**
	 * 一括操作メニューの追加
	 *
	 * @param array $bulk_actions 利用可能なバルクアクションの配列
	 * @return array
	 */
	public function add_bulk_actions( $bulk_actions ) {
		$bulk_actions['generate_webp'] = 'WebP生成';
		$bulk_actions['delete_webp']   = 'WebP削除';
		return $bulk_actions;
	}

	/**
	 * 一括操作の処理
	 *
	 * @param string $redirect_to リダイレクトURL
	 * @param string $action アクション
	 * @param array  $post_ids アクションを実行する項目。投稿、コメント、用語、リンク、プラグイン、添付ファイル、ユーザーのIDの配列を受け入れる
	 * @return string
	 */
	public function handle_bulk_actions( $redirect_to, $action, $post_ids ) {
		if ( 'generate_webp' === $action ) {
			$count = 0;
			foreach ( $post_ids as $post_id ) {
				if ( $this->generate_webp_for_attachment( $post_id ) ) {
					update_post_meta( $post_id, $this->meta_key, '1' );
					$count++;
				}
			}
			$redirect_to = add_query_arg( 'webp_generated', $count, $redirect_to );
		} elseif ( 'delete_webp' === $action ) {
			$count = 0;
			foreach ( $post_ids as $post_id ) {
				$this->delete_webp_versions( $post_id );
				update_post_meta( $post_id, $this->meta_key, '0' );
				$count++;
			}
			$redirect_to = add_query_arg( 'webp_deleted', $count, $redirect_to );
		}

		return $redirect_to;
	}

	/**
	 * 管理画面の通知メッセージを表示
	 *
	 * @return void
	 */
	public function display_admin_notices() {
		// WebP生成完了の通知
		if ( isset( $_GET['webp_generated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$count = intval( $_GET['webp_generated'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			echo '<div class="notice notice-success is-dismissible">';
			echo '<p>' . esc_html( sprintf( '%d個の画像にWebPを生成しました。', $count ) ) . '</p>';
			echo '</div>';
		}

		// WebP削除完了の通知
		if ( isset( $_GET['webp_deleted'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$count = intval( $_GET['webp_deleted'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			echo '<div class="notice notice-success is-dismissible">';
			echo '<p>' . esc_html( sprintf( '%d個の画像のWebPを削除しました。', $count ) ) . '</p>';
			echo '</div>';
		}
	}

	/**
	 * メディアライブラリ用のインラインスクリプトを出力
	 *
	 * @return void
	 */
	public function output_media_library_script() {
		$nonce = wp_create_nonce( 'webp_nonce' );
		?>
		<script>
		function toggleWebPInline(attachmentId, generate) {
			jQuery.post(ajaxurl, {
				action: 'toggle_webp_generation',
				attachment_id: attachmentId,
				generate: generate,
				nonce: '<?php echo esc_js( $nonce ); ?>'
			}, function(response) {
				if (response.success) {
					location.reload();
				} else {
					alert('操作に失敗しました: ' + response.data);
				}
			});
		}
		</script>
		<?php
	}

	/**
	 * 特定の添付ファイルのWebPを生成
	 *
	 * @param int $attachment_id アタッチメントID
	 * @return bool
	 */
	private function generate_webp_for_attachment( $attachment_id ) {
		$metadata = wp_get_attachment_metadata( $attachment_id );

		if ( ! $metadata || ! isset( $metadata['file'] ) ) {
			return false;
		}

		$upload_dir = wp_upload_dir();
		$file_path  = $upload_dir['basedir'] . '/' . $metadata['file'];

		if ( ! file_exists( $file_path ) ) {
			return false;
		}

		// 元画像のWebP版を生成
		$success = $this->create_webp_image( $file_path );

		if ( ! $success ) {
			return false;
		}

		// サイズバリエーションのWebP版を生成
		if ( isset( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) ) {
			$file_dir = dirname( $file_path );

			foreach ( $metadata['sizes'] as $size => $size_data ) {
				$size_file_path = $file_dir . '/' . $size_data['file'];
				if ( file_exists( $size_file_path ) ) {
					$size_result = $this->create_webp_image( $size_file_path );
				}
			}
		}

		return $success;
	}

	/**
	 * WebP画像を生成
	 *
	 * @param string $source_path 元画像のパス
	 * @return bool
	 */
	private function create_webp_image( $source_path ) {
		if ( ! file_exists( $source_path ) ) {
			return false;
		}

		$path_info = pathinfo( $source_path );
		$webp_path = $path_info['dirname'] . '/' . $path_info['filename'] . '.webp';

		$mime_type = wp_check_filetype( $source_path )['type'];

		// サポートされている画像形式かチェック
		if ( ! in_array( $mime_type, array( 'image/jpeg', 'image/png', 'image/gif' ), true ) ) {
			return false;
		}

		// Imagickを優先して使用
		if ( extension_loaded( 'imagick' ) ) {
			return $this->create_webp_with_imagick( $source_path, $webp_path );
		}

		// フォールバック: imagewebpを使用
		if ( function_exists( 'imagewebp' ) ) {
			return $this->create_webp_with_gd( $source_path, $webp_path, $mime_type );
		}

		return false;
	}

	/**
	 * Imagickを使用してWebP画像を生成
	 *
	 * @param string $source_path 元画像のパス
	 * @param string $webp_path webp画像のパス
	 * @return bool
	 */
	private function create_webp_with_imagick( $source_path, $webp_path ) {
		try {
			$imagick = new Imagick( $source_path );
			$imagick->setImageFormat( 'webp' );
			$imagick->setImageCompressionQuality( 80 );
			$result = $imagick->writeImage( $webp_path );
			$imagick->clear();
			$imagick->destroy();
			return $result;
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * GD拡張を使用してWebP画像を生成
	 *
	 * @param string $source_path 元画像のパス
	 * @param string $webp_path webp画像のパス
	 * @param string $mime_type MIMEタイプ
	 * @return bool
	 */
	private function create_webp_with_gd( $source_path, $webp_path, $mime_type ) {
		try {
			switch ( $mime_type ) {
				case 'image/jpeg':
					$image = imagecreatefromjpeg( $source_path );
					break;
				case 'image/png':
					$image = imagecreatefrompng( $source_path );
					imagealphablending( $image, false );
					imagesavealpha( $image, true );
					break;
				case 'image/gif':
					$image = imagecreatefromgif( $source_path );
					break;
				default:
					return false;
			}

			if ( ! $image ) {
				return false;
			}

			$result = imagewebp( $image, $webp_path, 80 );
			imagedestroy( $image );
			return $result;
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * WebPファイルの存在確認
	 *
	 * @param int $attachment_id アタッチメントID
	 * @return bool
	 */
	private function check_webp_exists( $attachment_id ) {
		$metadata = wp_get_attachment_metadata( $attachment_id );

		if ( ! $metadata || ! isset( $metadata['file'] ) ) {
			return false;
		}

		$upload_dir = wp_upload_dir();
		$file_path  = $upload_dir['basedir'] . '/' . $metadata['file'];
		$path_info  = pathinfo( $file_path );
		$webp_path  = $path_info['dirname'] . '/' . $path_info['filename'] . '.webp';

		// ファイルの存在と読み取り可能性をチェック
		return file_exists( $webp_path ) && is_readable( $webp_path );
	}

	/**
	 * 画像削除時にWebP版も削除
	 *
	 * @param int $attachment_id アタッチメントID
	 * @return void
	 */
	public function delete_webp_versions( $attachment_id ) {
		$metadata = wp_get_attachment_metadata( $attachment_id );

		if ( ! $metadata || ! isset( $metadata['file'] ) ) {
			return;
		}

		$upload_dir = wp_upload_dir();
		$file_path  = $upload_dir['basedir'] . '/' . $metadata['file'];

		// 元画像のWebP版を削除
		$this->delete_webp_file( $file_path );

		// サイズバリエーションのWebP版を削除
		if ( isset( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) ) {
			$file_dir = dirname( $file_path );

			foreach ( $metadata['sizes'] as $size => $size_data ) {
				$size_file_path = $file_dir . '/' . $size_data['file'];
				$this->delete_webp_file( $size_file_path );
			}
		}
	}

	/**
	 * WebPファイルを削除
	 *
	 * @param string $source_path webp画像のパス
	 * @return void
	 */
	private function delete_webp_file( $source_path ) {
		$path_info = pathinfo( $source_path );
		$webp_path = $path_info['dirname'] . '/' . $path_info['filename'] . '.webp';

		if ( file_exists( $webp_path ) ) {
			wp_delete_file( $webp_path );
		}
	}
}
