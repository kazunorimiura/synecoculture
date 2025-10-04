<?php
/**
 * ページベースWebP生成・削除機能
 *
 * 指定されたURL（投稿、アーカイブ、検索結果等）で使用されている画像を解析し、
 * WebP未生成の画像に対して一括生成、または既存WebPファイルの一括削除を行う
 *
 * @package wordpressfoundation
 */

/**
 * ページベースWebP生成・削除クラス
 */
class WPF_Page_WebP_Scanner {

	/**
	 * webp生成ハンドラクラスのインスタンス
	 *
	 * @var WPF_Selective_WebP_Image_Handler
	 */
	private $webp_handler;

	/**
	 * メタキー
	 *
	 * @var string
	 */
	private $meta_key = '_wpf_generate_webp';

	/**
	 * コンストラクタ
	 *
	 * @param WPF_Selective_WebP_Image_Handler $webp_handler webp生成ハンドラクラスのインスタンス
	 */
	public function __construct( $webp_handler ) {
		$this->webp_handler = $webp_handler;

		// 管理画面のフック
		add_action( 'admin_menu', array( $this, 'add_admin_page' ) );
		add_action( 'wp_ajax_scan_page_images', array( $this, 'ajax_scan_page_images' ) );
		add_action( 'wp_ajax_generate_selected_webp', array( $this, 'ajax_generate_selected_webp' ) );
		add_action( 'wp_ajax_delete_selected_webp', array( $this, 'ajax_delete_selected_webp' ) );
	}

	/**
	 * 管理画面にページを追加
	 */
	public function add_admin_page() {
		add_management_page(
			'ページWebP生成・削除',
			'ページWebP生成・削除',
			'manage_options',
			'page-webp-generator',
			array( $this, 'admin_page_callback' )
		);
	}

	/**
	 * 管理画面の表示
	 */
	public function admin_page_callback() {
		// jQueryを確実にエンキュー
		wp_enqueue_script( 'jquery' );

		// Dashiconsも読み込み
		wp_enqueue_style( 'dashicons' );

		// AJAX用の変数を直接出力
		$ajax_nonce = wp_create_nonce( 'page_webp_nonce' );
		?>
		<script type="text/javascript">
		var pageWebpAjax = {
			ajax_url: '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>',
			nonce: '<?php echo esc_js( $ajax_nonce ); ?>'
		};
		</script>
		<div class="wrap">
			<h1>ページ単位WebP生成・削除</h1>

			<div class="card">
				<h2>URL指定してWebP操作</h2>

				<!-- URL指定 -->
				<table class="form-table">
					<tr>
						<th><label for="page_url">ページURL</label></th>
						<td>
							<input type="url" id="page_url" class="regular-text" placeholder='<?php echo esc_url( home_url( '/category/sample/' ) ); ?>' />
							<p class="description">
								操作対象のページURLを入力してください
							</p>
						</td>
					</tr>
				</table>

				<!-- 投稿ID指定 -->
				<h3>または投稿IDで指定</h3>
				<table class="form-table">
					<tr>
						<th><label for="post_id">投稿ID</label></th>
						<td>
							<input type="number" id="post_id" class="small-text" />
							<button type="button" class="button" onclick="loadPostInfo()">投稿情報を取得</button>
							<p class="description">
								投稿IDを入力して投稿情報を確認できます
							</p>
						</td>
					</tr>
				</table>

				<div id="post_info" style="display:none; margin-top: 15px; padding: 10px; background: #f9f9f9; border-left: 4px solid #00a0d2;">
					<!-- 投稿情報がここに表示される -->
				</div>

				<p class="submit">
					<button type="button" class="button button-primary" onclick="scanPageImages()">
						<span class="dashicons dashicons-search"></span>
						画像をスキャン
					</button>
				</p>
			</div>

			<!-- スキャン結果 -->
			<div id="scan_results" style="display:none;">
				<div class="card">
					<h2>スキャン結果</h2>
					<div id="scan_content">
						<!-- スキャン結果がここに表示される -->
					</div>
				</div>
			</div>

			<!-- 生成進捗 -->
			<div id="generation_progress" style="display:none;">
				<div class="card">
					<h2>WebP生成進捗</h2>
					<div class="progress-container">
						<div id="progress_bar" style="width: 100%; background: #f0f0f0; border-radius: 4px;">
							<div id="progress_fill" style="width: 0%; height: 20px; background: #00a0d2; border-radius: 4px; transition: width 0.3s;"></div>
						</div>
						<p id="progress_text">準備中...</p>
					</div>
					<div id="generation_log" style="max-height: 300px; overflow-y: auto; margin-top: 15px; padding: 10px; background: #f9f9f9; font-family: monospace; font-size: 12px;">
						<!-- 生成ログがここに表示される -->
					</div>
				</div>
			</div>

			<!-- 削除進捗 -->
			<div id="deletion_progress" style="display:none;">
				<div class="card">
					<h2>WebP削除進捗</h2>
					<div class="progress-container">
						<div id="delete_progress_bar" style="width: 100%; background: #f0f0f0; border-radius: 4px;">
							<div id="delete_progress_fill" style="width: 0%; height: 20px; background: #dc3232; border-radius: 4px; transition: width 0.3s;"></div>
						</div>
						<p id="delete_progress_text">準備中...</p>
					</div>
					<div id="deletion_log" style="max-height: 300px; overflow-y: auto; margin-top: 15px; padding: 10px; background: #f9f9f9; font-family: monospace; font-size: 12px;">
						<!-- 削除ログがここに表示される -->
					</div>
				</div>
			</div>
		</div>

		<script>
		function loadPostInfo() {
			const postId = document.getElementById('post_id').value;
			if (!postId) {
				alert('投稿IDを入力してください');
				return;
			}

			jQuery.post(pageWebpAjax.ajax_url, {
				action: 'scan_page_images',
				post_id: postId,
				mode: 'info',
				nonce: pageWebpAjax.nonce
			})
			.done(function(response) {
				if (response.success) {
					const data = response.data;
					document.getElementById('post_info').innerHTML = `
						<h4>${data.title}</h4>
						<p><strong>URL:</strong> <a href="${data.url}" target="_blank">${data.url}</a></p>
						<p><strong>投稿タイプ:</strong> ${data.post_type}</p>
						<p><strong>ステータス:</strong> ${data.status}</p>
					`;
					document.getElementById('post_info').style.display = 'block';
					document.getElementById('page_url').value = data.url;
				} else {
					alert('投稿情報の取得に失敗しました: ' + response.data);
				}
			})
			.fail(function() {
				alert('通信エラーが発生しました');
			});
		}

		function scanPageImages() {
			const pageUrl = document.getElementById('page_url').value;
			const postId = document.getElementById('post_id').value;

			if (!pageUrl && !postId) {
				alert('ページURLまたは投稿IDを指定してください');
				return;
			}

			// 結果エリアをリセット
			document.getElementById('scan_results').style.display = 'none';
			document.getElementById('generation_progress').style.display = 'none';
			document.getElementById('deletion_progress').style.display = 'none';

			const data = {
				action: 'scan_page_images',
				mode: 'scan',
				nonce: pageWebpAjax.nonce
			};

			if (postId) {
				data.post_id = postId;
			} else {
				data.page_url = pageUrl;
			}

			jQuery.post(pageWebpAjax.ajax_url, data)
			.done(function(response) {
				if (response.success) {
					displayScanResults(response.data);
				} else {
					alert('スキャンに失敗しました: ' + response.data);
				}
			})
			.fail(function() {
				alert('通信エラーが発生しました');
			});
		}

		function displayScanResults(data) {
			let html = `
				<p><strong>スキャンしたページ:</strong> ${data.page_title}</p>
				<p><strong>対象URL:</strong> <a href="${data.page_url}" target="_blank">${data.page_url}</a></p>
				<p><strong>スキャン方法:</strong> ${data.scan_method === 'html' ? '完全ページHTML' : 'コンテンツのみ（フォールバック）'}</p>
				<p><strong>発見した画像:</strong> ${data.total_images}個</p>
				<p><strong>WebP未生成:</strong> ${data.pending_images.length}個</p>
				<p><strong>WebP生成済み:</strong> ${data.existing_webp.length}個</p>
			`;

			// WebP生成対象の画像がある場合
			if (data.total_images > 0 && data.pending_images.length > 0) {
				html += '<h3>WebP生成対象の画像</h3>';
				html += `
					<div class="webp-selection-controls">
						<div class="webp-bulk-controls">
							<label>
								<input type="checkbox" id="select_all_pending" onchange="toggleSelectAll('pending')">
								<strong>すべて選択</strong>
							</label>
							<button type="button" class="button button-primary" onclick="generateSelectedWebP('pending')" disabled id="generate_selected_btn">
								<span class="dashicons dashicons-images-alt2"></span>
								選択した画像のWebPを生成 (<span id="pending_selected_count">0</span>個)
							</button>
						</div>
					</div>
				`;
				html += '<div class="webp-image-grid" id="pending_images_grid">';
				data.pending_images.forEach(function(image, index) {
					const thumbnailUrl = getThumbnailUrl(image.src);
					html += `
						<div class="webp-image-item webp-selectable-card" data-attachment-id="${image.attachment_id}" data-type="pending">
							<div class="webp-image-preview">
								<div class="webp-image-checkbox">
									<input type="checkbox" id="pending_${image.attachment_id}" 
										value="${image.attachment_id}" 
										onchange="updateSelectionButtons('pending')"
										data-filename="${image.filename}"
										data-src="${image.src}"
										data-type="${image.type}">
								</div>
								<img src="${thumbnailUrl}" alt="${image.filename}" loading="lazy" onerror="this.src='${image.src}'" />
								<div class="webp-image-overlay">
									<span class="webp-image-type">${getImageTypeLabel(image.type)}</span>
								</div>
							</div>
							<div class="webp-image-info">
								<div class="webp-image-title">
									<p><strong>${image.filename}</strong></p>
									<p><small>ID: ${image.attachment_id}</small></p>
									<p><small class="webp-image-url">${truncateUrl(image.src, 40)}</small></p>
								</div>
							</div>
							<div class="webp-card-selection-indicator">
								<span class="dashicons dashicons-yes-alt"></span>
							</div>
						</div>
					`;
				});
				html += '</div>';

			} else if (0 === data.total_images) {
				html += '<p style="color: #767676;"><strong>WebP生成対象の画像はありません</strong></p>';
			} else {
				html += '<p style="color: green;"><strong>✓ すべての画像でWebPが生成済みです！</strong></p>';
			}

			// WebP生成済みの画像がある場合
			if (data.existing_webp.length > 0) {
				html += '<h3>WebP生成済みの画像</h3>';
				html += `
					<div class="webp-selection-controls">
						<div class="webp-bulk-controls">
							<label>
								<input type="checkbox" id="select_all_existing" onchange="toggleSelectAll('existing')">
								<strong>すべて選択</strong>
							</label>
							<button type="button" class="button button-secondary webp-delete-button" onclick="deleteSelectedWebP('existing')" disabled id="delete_selected_btn">
								<span class="dashicons dashicons-trash"></span>
								選択した画像のWebPを削除 (<span id="existing_selected_count">0</span>個)
							</button>
						</div>
					</div>
				`;
				html += '<div class="webp-image-grid webp-existing" id="existing_images_grid">';
				data.existing_webp.forEach(function(image, index) {
					const thumbnailUrl = getThumbnailUrl(image.src);
					html += `
						<div class="webp-image-item webp-selectable-card" data-attachment-id="${image.attachment_id}" data-type="existing">
							<div class="webp-image-preview">
								<div class="webp-image-checkbox">
									<input type="checkbox" id="existing_${image.attachment_id}" 
										value="${image.attachment_id}" 
										onchange="updateSelectionButtons('existing')"
										data-filename="${image.filename}"
										data-src="${image.src}"
										data-type="${image.type}">
								</div>
								<img src="${thumbnailUrl}" alt="${image.filename}" loading="lazy" onerror="this.src='${image.src}'" />
								<div class="webp-image-overlay">
									<span class="webp-image-type">${getImageTypeLabel(image.type)}</span>
									<span class="webp-status-badge">✓ WebP</span>
								</div>
							</div>
							<div class="webp-image-info">
								<div class="webp-image-title">
									<strong>${image.filename}</strong><br>
									<small>ID: ${image.attachment_id}</small><br>
									<small class="webp-image-url">${truncateUrl(image.src, 40)}</small>
								</div>
							</div>
							<div class="webp-card-selection-indicator">
								<span class="dashicons dashicons-yes-alt"></span>
							</div>
						</div>
					`;
				});
				html += '</div>';
			}

			document.getElementById('scan_content').innerHTML = html;
			document.getElementById('scan_results').style.display = 'block';

			// カード選択のイベントリスナーを追加
			initializeCardSelection();
		}

		/**
		 * 全選択/全解除の切り替え
		 */
		function toggleSelectAll(type) {
			const selectAllCheckbox = document.getElementById(`select_all_${type}`);
			const isChecked = selectAllCheckbox.checked;
			const gridSelector = type === 'pending' ? '#pending_images_grid' : '#existing_images_grid';
			const checkboxes = document.querySelectorAll(`${gridSelector} input[type="checkbox"]`);

			checkboxes.forEach(function(checkbox) {
				checkbox.checked = isChecked;

				// カードの選択状態も更新
				const attachmentId = checkbox.value;
				const card = document.querySelector(`.webp-selectable-card[data-attachment-id="${attachmentId}"][data-type="${type}"]`);
				if (card) {
					updateCardSelectionState(card, isChecked);
				}
			});

			updateSelectionButtons(type);
		}

		/**
		 * カード選択の初期化
		 */
		function initializeCardSelection() {
			const selectableCards = document.querySelectorAll('.webp-selectable-card');

			selectableCards.forEach(function(card) {
				// カードクリック時の処理
				card.addEventListener('click', function(e) {
					// チェックボックス自体をクリックした場合は何もしない
					if (e.target.type === 'checkbox') {
						return;
					}

					// 他の要素（画像、ラベルなど）をクリックした場合はイベントの伝播を止める
					e.preventDefault();
					e.stopPropagation();

					const attachmentId = this.dataset.attachmentId;
					const type = this.dataset.type;
					const checkbox = document.getElementById(`${type}_${attachmentId}`);

					if (checkbox) {
						// チェックボックスの状態を切り替え
						checkbox.checked = !checkbox.checked;

						// 選択状態の視覚的更新
						updateCardSelectionState(this, checkbox.checked);

						// 選択ボタンの状態を更新
						updateSelectionButtons(type);
					}
				});

				// ホバー効果を追加
				card.addEventListener('mouseenter', function() {
					if (!this.classList.contains('webp-card-selected')) {
						this.classList.add('webp-card-hover');
					}
				});

				card.addEventListener('mouseleave', function() {
					this.classList.remove('webp-card-hover');
				});

				// チェックボックスの初期状態に基づいてカードの状態を設定
				const attachmentId = card.dataset.attachmentId;
				const type = card.dataset.type;
				const checkbox = document.getElementById(`${type}_${attachmentId}`);
				if (checkbox && checkbox.checked) {
					updateCardSelectionState(card, true);
				}
			});
		}

		/**
		 * カードの選択状態を視覚的に更新
		 */
		function updateCardSelectionState(card, isSelected) {
			if (isSelected) {
				card.classList.add('webp-card-selected');
				card.classList.remove('webp-card-hover');
			} else {
				card.classList.remove('webp-card-selected');
			}
		}

		/**
		 * 選択ボタンの状態を更新
		 */
		function updateSelectionButtons(type) {
			const gridSelector = type === 'pending' ? '#pending_images_grid' : '#existing_images_grid';
			const checkboxes = document.querySelectorAll(`${gridSelector} input[type="checkbox"]`);
			const selectedCheckboxes = document.querySelectorAll(`${gridSelector} input[type="checkbox"]:checked`);

			const selectedCount = selectedCheckboxes.length;
			const totalCount = checkboxes.length;

			// カードの選択状態も更新
			checkboxes.forEach(function(checkbox) {
				const attachmentId = checkbox.value;
				const card = document.querySelector(`.webp-selectable-card[data-attachment-id="${attachmentId}"][data-type="${type}"]`);
				if (card) {
					updateCardSelectionState(card, checkbox.checked);
				}
			});

			// カウント表示を更新
			const countSpan = document.getElementById(`${type}_selected_count`);
			if (countSpan) {
				countSpan.textContent = selectedCount;
			}

			// ボタンの有効/無効を切り替え
			const buttonId = type === 'pending' ? 'generate_selected_btn' : 'delete_selected_btn';
			const button = document.getElementById(buttonId);
			if (button) {
				button.disabled = selectedCount === 0;
			}

			// 全選択チェックボックスの状態を更新
			const selectAllCheckbox = document.getElementById(`select_all_${type}`);
			if (selectAllCheckbox) {
				selectAllCheckbox.checked = selectedCount === totalCount && totalCount > 0;
				selectAllCheckbox.indeterminate = selectedCount > 0 && selectedCount < totalCount;
			}
		}

		/**
		 * 選択されたWebPの生成
		 */
		function generateSelectedWebP(type) {
			const selectedImages = getSelectedImages(type);

			if (selectedImages.length === 0) {
				alert('画像を選択してください');
				return;
			}

			if (!confirm(`選択した${selectedImages.length}個の画像のWebPを生成しますか？`)) {
				return;
			}

			// 進捗表示を初期化
			document.getElementById('generation_progress').style.display = 'block';
			document.getElementById('deletion_progress').style.display = 'none';
			document.getElementById('progress_fill').style.width = '0%';
			document.getElementById('progress_text').textContent = '生成を開始します...';
			document.getElementById('generation_log').innerHTML = '';

			processSelectedImages(selectedImages, 'generate_selected_webp', 'generation');
		}

		/**
		 * 選択されたWebPの削除
		 */
		function deleteSelectedWebP(type) {
			const selectedImages = getSelectedImages(type);

			if (selectedImages.length === 0) {
				alert('画像を選択してください');
				return;
			}

			if (!confirm(`選択した${selectedImages.length}個の画像のWebPファイルを削除しますか？`)) {
				return;
			}

			// 進捗表示を初期化
			document.getElementById('deletion_progress').style.display = 'block';
			document.getElementById('generation_progress').style.display = 'none';
			document.getElementById('delete_progress_fill').style.width = '0%';
			document.getElementById('delete_progress_text').textContent = '削除を開始します...';
			document.getElementById('deletion_log').innerHTML = '';

			processSelectedImages(selectedImages, 'delete_selected_webp', 'deletion');
		}

		/**
		 * 選択された画像の情報を取得
		 */
		function getSelectedImages(type) {
			const gridSelector = type === 'pending' ? '#pending_images_grid' : '#existing_images_grid';
			const selectedCheckboxes = document.querySelectorAll(`${gridSelector} input[type="checkbox"]:checked`);
			const selectedImages = [];

			selectedCheckboxes.forEach(function(checkbox) {
				selectedImages.push({
					attachment_id: parseInt(checkbox.value),
					filename: checkbox.dataset.filename,
					src: checkbox.dataset.src,
					type: checkbox.dataset.type
				});
			});

			return selectedImages;
		}

		/**
		 * 選択された画像を順次処理
		 */
		function processSelectedImages(selectedImages, ajaxAction, progressType) {
			let completed = 0;
			const total = selectedImages.length;

			function processNext() {
				if (completed >= total) {
					const progressText = document.getElementById(`${progressType === 'generation' ? 'progress' : 'delete_progress'}_text`);
					const logDiv = document.getElementById(`${progressType}_log`);

					progressText.textContent = progressType === 'generation' ? '生成完了しました！' : '削除完了しました！';
					logDiv.innerHTML += `<div style="color: green;">✓ すべての処理が完了しました (${total}個)</div>`;

					// スキャンを再実行して結果を更新
					setTimeout(scanPageImages, 1000);
					return;
				}

				const image = selectedImages[completed];

				const progressFill = document.getElementById(`${progressType === 'generation' ? 'progress' : 'delete_progress'}_fill`);
				const progressText = document.getElementById(`${progressType === 'generation' ? 'progress' : 'delete_progress'}_text`);

				jQuery.post(pageWebpAjax.ajax_url, {
					action: ajaxAction,
					attachment_id: image.attachment_id,
					nonce: pageWebpAjax.nonce
				})
				.done(function(response) {
					const logDiv = document.getElementById(`${progressType}_log`);
					if (response.success) {
						const actionText = progressType === 'generation' ? 'WebP生成成功' : 'WebP削除成功';
						logDiv.innerHTML += `<div style="color: green;">✓ ${image.filename}: ${actionText}</div>`;
					} else {
						logDiv.innerHTML += `<div style="color: red;">✗ ${image.filename}: ${response.data}</div>`;
					}
					logDiv.scrollTop = logDiv.scrollHeight;
				})
				.fail(function() {
					const logDiv = document.getElementById(`${progressType}_log`);
					logDiv.innerHTML += `<div style="color: red;">✗ ${image.filename}: 通信エラー</div>`;
					logDiv.scrollTop = logDiv.scrollHeight;
				})
				.always(function() {
					completed++;

					const progress = Math.round((completed / total) * 100);
					progressFill.style.width = progress + '%';
					progressText.textContent = `${completed}/${total} 処理中: ${image.filename}`;

					setTimeout(processNext, 500); // 0.5秒間隔で処理
				});
			}

			processNext();
		}

		/**
		* サムネイルURLを生成（WordPressの画像サイズを利用）
		*/
		function getThumbnailUrl(originalUrl) {
			// WordPressの150x150サムネイルサイズを試行
			const url = new URL(originalUrl, window.location.origin);
			const pathParts = url.pathname.split('.');
			const extension = pathParts.pop();
			const basePath = pathParts.join('.');

			// -150x150のサムネイルサイズを試行
			const thumbnailUrl = basePath + '-150x150.' + extension;

			return thumbnailUrl;
		}

		/**
		* 画像タイプのラベルを取得
		*/
		function getImageTypeLabel(type) {
			switch(type) {
				case 'featured':
					return 'アイキャッチ';
				case 'content':
					return 'コンテンツ';
				case 'widget':
					return 'ウィジェット';
				case 'custom':
					return 'カスタム';
				default:
					return 'その他';
			}
		}

		/**
		* URLを指定文字数で省略
		*/
		function truncateUrl(url, maxLength) {
			if (url.length <= maxLength) {
				return url;
			}

			const start = url.substring(0, maxLength / 2);
			const end = url.substring(url.length - maxLength / 2);
			return start + '...' + end;
		}
		</script>

		<style>
		/* 既存のCSSは同じなので省略 */
		.webp-image-grid {
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
			gap: 15px;
			margin: 20px 0;
			padding: 15px;
			background: #f9f9f9;
			border: 1px solid #e0e0e0;
			border-radius: 4px;
		}

		.webp-image-item {
			background: #fff;
			border: 1px solid #ddd;
			border-radius: 6px;
			overflow: hidden;
			transition: all 0.3s ease;
			box-shadow: 0 2px 4px rgba(0,0,0,0.1);
			position: relative;
		}

		.webp-selectable-card {
			cursor: pointer;
			user-select: none;
		}

		.webp-selectable-card:hover,
		.webp-card-hover {
			transform: translateY(-2px);
			box-shadow: 0 4px 8px rgba(0,0,0,0.15);
			border-color: #0073aa;
		}

		.webp-card-selected {
			border-color: #0073aa !important;
			box-shadow: 0 0 0 2px rgba(0, 115, 170, 0.3) !important;
			background: #f0f7ff !important;
			transform: translateY(-1px);
		}

		.webp-card-selected .webp-image-preview {
			border-bottom-color: rgba(0, 115, 170, 0.2);
		}

		.webp-card-selection-indicator {
			position: absolute;
			top: 8px;
			right: 8px;
			width: 24px;
			height: 24px;
			background: #0073aa;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			opacity: 0;
			transform: scale(0);
			transition: all 0.2s ease;
			z-index: 5;
		}

		.webp-card-selected .webp-card-selection-indicator {
			opacity: 1;
			transform: scale(1);
		}

		.webp-card-selection-indicator .dashicons {
			color: white;
			font-size: 14px;
			width: 14px;
			height: 14px;
		}

		.webp-image-preview {
			position: relative;
			height: 120px;
			overflow: hidden;
			background: #f5f5f5;
			display: flex;
			align-items: center;
			justify-content: center;
			border-bottom: 1px solid #eee;
			transition: border-color 0.3s ease;
		}

		.webp-image-checkbox {
			opacity: 0;
		}

		.webp-image-checkbox input[type="checkbox"] {
			width: 18px;
			height: 18px;
			cursor: pointer;
		}

		.webp-image-preview img {
			max-width: 100%;
			max-height: 100%;
			object-fit: cover;
			border: none;
			transition: opacity 0.3s ease;
		}

		.webp-card-selected .webp-image-preview img {
			opacity: 0.9;
		}

		.webp-image-overlay {
			position: absolute;
			top: 0;
			left: 0;
			right: 0;
			padding: 8px;
			background: linear-gradient(to bottom, rgba(0,0,0,0.7), transparent);
			display: flex;
			flex-direction: column;
			gap: 0.25em;
			justify-content: space-between;
			align-items: flex-start;
		}

		.webp-image-type {
			background: rgba(0, 115, 170, 0.9);
			color: white;
			padding: 2px 6px;
			border-radius: 3px;
			font-size: 11px;
			font-weight: 500;
		}

		.webp-status-badge {
			background: rgba(70, 180, 70, 0.9);
			color: white;
			padding: 2px 6px;
			border-radius: 3px;
			font-size: 11px;
			font-weight: 500;
		}

		.webp-image-info {
			padding: 12px;
		}

		.webp-image-title {
			transition: color 0.3s ease;
		}

		.webp-image-title > * {
			margin-block: 0.75em 0;
		}

		.webp-image-title > *:first-child {
			margin-block: 0;
		}

		.webp-card-selected .webp-image-title {
			color: #0073aa;
		}

		.webp-image-info strong {
			display: block;
			margin-bottom: 4px;
			font-size: 13px;
			color: #333;
			word-break: break-word;
			transition: color 0.3s ease;
		}

		.webp-card-selected .webp-image-info strong {
			color: #0073aa;
		}

		.webp-image-info small {
			display: block;
			color: #666;
			font-size: 11px;
			line-height: 1.4;
		}

		.webp-image-url {
			font-family: monospace;
			background: #f8f8f8;
			padding: 2px 4px;
			border-radius: 2px;
			margin-top: 4px;
			transition: background-color 0.3s ease;
			word-break: break-word;
			overflow-wrap: break-word;
		}

		.webp-card-selected .webp-image-url {
			background: rgba(0, 115, 170, 0.1);
		}

		.webp-selection-controls {
			margin: 15px 0;
			padding: 15px;
			background: #f8f9fa;
			border: 1px solid #dee2e6;
			border-radius: 6px;
		}

		.webp-bulk-controls {
			display: flex;
			align-items: center;
			gap: 15px;
			flex-wrap: wrap;
		}

		.webp-bulk-controls label {
			display: flex;
			align-items: center;
			gap: 8px;
			font-size: 14px;
			cursor: pointer;
		}

		.webp-bulk-controls input[type="checkbox"] {
			width: 18px;
			height: 18px;
		}

		.webp-existing .webp-image-item {
			border-color: #46b450;
		}

		.webp-existing .webp-image-item:hover,
		.webp-existing .webp-card-hover {
			border-color: #46b450;
			box-shadow: 0 4px 8px rgba(70, 180, 80, 0.2);
		}

		.webp-existing .webp-card-selected {
			border-color: #46b450 !important;
			box-shadow: 0 0 0 2px rgba(70, 180, 80, 0.3) !important;
			background: #f0fff0 !important;
		}

		.webp-existing .webp-card-selection-indicator {
			background: #46b450;
		}

		.webp-delete-button {
			color: #a00 !important;
			border-color: #a00 !important;
		}

		.webp-delete-button:hover:not(:disabled) {
			background: #a00 !important;
			color: #fff !important;
		}

		.webp-delete-button .dashicons {
			font-size: 16px;
			width: 16px;
			height: 16px;
			margin-right: 5px;
		}

		.button .dashicons {
			font-size: 16px;
			width: 16px;
			height: 16px;
			margin-right: 5px;
			vertical-align: middle;
		}

		.button:disabled {
			opacity: 0.6;
			cursor: not-allowed;
		}

		.progress-container {
			margin: 15px 0;
		}

		#generation_log div,
		#deletion_log div {
			margin: 2px 0;
			padding: 2px 5px;
		}

		.card {
			max-width: 100%;
			margin: 20px 0;
		}

		@media (max-width: 768px) {
			.webp-image-grid {
				grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
				gap: 10px;
			}

			.webp-image-preview {
				height: 100px;
			}

			.webp-image-info {
				padding: 10px;
			}

			.webp-bulk-controls {
				flex-direction: column;
				align-items: flex-start;
				gap: 10px;
			}

			.webp-card-selection-indicator {
				width: 20px;
				height: 20px;
			}

			.webp-card-selection-indicator .dashicons {
				font-size: 12px;
				width: 12px;
				height: 12px;
			}
		}

		@media (max-width: 480px) {
			.webp-image-grid {
				grid-template-columns: 1fr 1fr;
				gap: 8px;
			}

			.webp-image-preview {
				height: 80px;
			}

			.webp-image-info {
				padding: 8px;
			}

			.webp-image-info strong {
				font-size: 12px;
			}

			.webp-image-info small {
				font-size: 10px;
			}
		}
		</style>
		<?php
	}

	/**
	 * AJAX: ページの画像をスキャン
	 */
	public function ajax_scan_page_images() {
		check_ajax_referer( 'page_webp_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( '権限がありません' );
		}

		$mode = sanitize_text_field( isset( $_POST['mode'] ) ? sanitize_key( $_POST['mode'] ) : 'scan' );

		// 投稿IDまたはURLから処理を分岐
		$post       = null;
		$target_url = '';

		if ( isset( $_POST['post_id'] ) && ! empty( $_POST['post_id'] ) ) {
			$post_id = intval( $_POST['post_id'] );
			$post    = get_post( $post_id );

			if ( ! $post ) {
				wp_send_json_error( '指定された投稿が見つかりません' );
			}

			$target_url = get_permalink( $post );
		} elseif ( isset( $_POST['page_url'] ) && ! empty( $_POST['page_url'] ) ) {
			$target_url = esc_url_raw( $_POST['page_url'] ); // phpcs:ignore

			// 同じサイト内のURLかチェック
			if ( ! $this->is_same_site_url( $target_url ) ) {
				wp_send_json_error( '指定されたURLは同じサイト内のものではありません' );
			}

			// URLから投稿IDを取得（存在する場合のみ）
			$post_id = url_to_postid( $target_url );
			if ( $post_id ) {
				$post = get_post( $post_id );
			}
		} else {
			wp_send_json_error( '投稿IDまたはURLを指定してください' );
		}

		// 投稿情報のみを返す場合（投稿が存在する場合のみ）
		if ( 'info' === $mode ) {
			if ( ! $post ) {
				wp_send_json_error( '投稿情報を取得するには有効な投稿IDが必要です' );
			}

			wp_send_json_success(
				array(
					'title'     => get_the_title( $post ),
					'url'       => get_permalink( $post ),
					'post_type' => $post->post_type,
					'status'    => $post->post_status,
				)
			);
		}

		// 画像スキャンを実行
		$images_data = $this->scan_url_images( $target_url, $post );

		wp_send_json_success( $images_data );
	}

	/**
	 * AJAX: 選択されたWebPの生成
	 */
	public function ajax_generate_selected_webp() {
		check_ajax_referer( 'page_webp_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( '権限がありません' );
		}

		$attachment_id = intval( isset( $_POST['attachment_id'] ) ? $_POST['attachment_id'] : 0 );

		if ( ! $attachment_id ) {
			wp_send_json_error( '添付ファイルIDが指定されていません' );
		}

		// WebP生成を実行
		$reflection = new ReflectionClass( $this->webp_handler );
		$method     = $reflection->getMethod( 'generate_webp_for_attachment' );
		$method->setAccessible( true );

		$result = $method->invoke( $this->webp_handler, $attachment_id );

		if ( $result ) {
			// メタデータを更新
			update_post_meta( $attachment_id, $this->meta_key, '1' );
			wp_send_json_success( 'WebPを生成しました' );
		} else {
			wp_send_json_error( 'WebP生成に失敗しました' );
		}
	}

	/**
	 * AJAX: 選択されたWebPの削除
	 */
	public function ajax_delete_selected_webp() {
		check_ajax_referer( 'page_webp_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( '権限がありません' );
		}

		$attachment_id = intval( isset( $_POST['attachment_id'] ) ? $_POST['attachment_id'] : 0 );

		if ( ! $attachment_id ) {
			wp_send_json_error( '添付ファイルIDが指定されていません' );
		}

		// 添付ファイルの存在確認
		if ( ! get_post( $attachment_id ) || get_post_type( $attachment_id ) !== 'attachment' ) {
			wp_send_json_error( '添付ファイルが見つかりません' );
		}

		// WebP削除を実行
		$this->webp_handler->delete_webp_versions( $attachment_id );

		// メタデータを更新
		update_post_meta( $attachment_id, $this->meta_key, '0' );

		// 削除後に実際にファイルが削除されたか確認
		$reflection = new ReflectionClass( $this->webp_handler );
		$method     = $reflection->getMethod( 'check_webp_exists' );
		$method->setAccessible( true );

		if ( ! $method->invoke( $this->webp_handler, $attachment_id ) ) {
			wp_send_json_success( 'WebPを削除しました' );
		} else {
			wp_send_json_error( 'WebPファイルの削除を試みましたが、まだ存在しています' );
		}
	}

	/**
	 * 指定URLの画像をスキャン
	 *
	 * @param string  $target_url 対象URL
	 * @param WP_Post $post WP_Post オブジェクト（存在する場合）
	 * @return array
	 */
	private function scan_url_images( $target_url, $post = null ) {
		// ページHTMLを取得
		$page_html = $this->get_url_html( $target_url );

		$scan_method = 'html';
		$images      = array();

		if ( $page_html ) {
			// HTMLから画像を抽出
			$images = $this->extract_images_from_html( $page_html );
		} else {
			$scan_method = 'content';
			// HTMLが取得できない場合で、投稿が存在する場合はフォールバック
			if ( $post ) {
				$images = $this->extract_images_from_content( apply_filters( 'the_content', $post->post_content ) ); // phpcs:ignore
			}
		}

		// 投稿が存在する場合、アイキャッチ画像も追加
		if ( $post && has_post_thumbnail( $post->ID ) ) {
			$thumbnail_id  = get_post_thumbnail_id( $post->ID );
			$thumbnail_url = wp_get_attachment_url( $thumbnail_id );

			// 既に抽出済みでない場合のみ追加
			$thumbnail_exists = false;
			foreach ( $images as $image ) {
				if ( isset( $image['attachment_id'] ) && $image['attachment_id'] === $thumbnail_id ) {
					$thumbnail_exists = true;
					break;
				}
			}

			if ( ! $thumbnail_exists ) {
				$images[] = array(
					'attachment_id' => $thumbnail_id,
					'src'           => $thumbnail_url,
					'type'          => 'featured',
				);
			}
		}

		// 画像データを分類
		$pending_images = array();
		$existing_webp  = array();

		foreach ( $images as $image ) {
			if ( ! isset( $image['attachment_id'] ) || ! $image['attachment_id'] ) {
				continue;
			}

			$attachment_id = $image['attachment_id'];
			$filename      = basename( get_attached_file( $attachment_id ) );
			$image_data    = array(
				'attachment_id' => $attachment_id,
				'filename'      => $filename,
				'src'           => $image['src'],
				'type'          => isset( $image['type'] ) ? $image['type'] : 'content',
			);

			// WebP生成状況をチェック
			$generate_webp = get_post_meta( $attachment_id, $this->meta_key, true );
			$webp_exists   = $this->check_webp_exists( $attachment_id );

			if ( '1' === $generate_webp || $webp_exists ) {
				$existing_webp[] = $image_data;
			} else {
				// 対応形式かチェック
				$mime_type = get_post_mime_type( $attachment_id );
				if ( in_array( $mime_type, array( 'image/jpeg', 'image/png', 'image/gif' ), true ) ) {
					$pending_images[] = $image_data;
				}
			}
		}

		// ページタイトルを取得
		$page_title = $this->get_page_title( $target_url, $post, $page_html );

		return array(
			'page_title'     => $page_title,
			'page_url'       => $target_url,
			'total_images'   => count( $images ),
			'pending_images' => $pending_images,
			'existing_webp'  => $existing_webp,
			'scan_method'    => $scan_method,
		);
	}

	/**
	 * 指定URLのHTMLを取得
	 *
	 * @param string $url 対象URL
	 * @return string|false
	 */
	protected function get_url_html( $url ) {
		if ( ! $url ) {
			return false;
		}

		// ユーザーエージェントを設定してWordPressサイトからHTMLを取得
		$response = wp_remote_get(
			$url,
			array(
				'timeout'    => 30,
				'user-agent' => 'WordPress WebP Scanner',
				'cookies'    => $_COOKIE, // 認証が必要な場合のためにクッキーを送信
				'headers'    => array(
					'Cache-Control' => 'no-cache',
				),
				'sslverify'  => apply_filters( 'wpf_https_local_ssl_verify', false ), // ローカル環境での証明書エラーを回避
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $status_code ) {
			return false;
		}

		return wp_remote_retrieve_body( $response );
	}

	/**
	 * 同じサイト内のURLかチェック
	 *
	 * @param string $url チェック対象URL
	 * @return bool
	 */
	private function is_same_site_url( $url ) {
		$site_url  = home_url();
		$site_host = wp_parse_url( $site_url, PHP_URL_HOST );
		$url_host  = wp_parse_url( $url, PHP_URL_HOST );

		return $site_host === $url_host;
	}

	/**
	 * ページタイトルを取得
	 *
	 * @param string  $url 対象URL
	 * @param WP_Post $post 投稿オブジェクト（存在する場合）
	 * @param string  $html ページHTML（存在する場合）
	 * @return string
	 */
	private function get_page_title( $url, $post = null, $html = null ) {
		// 投稿が存在する場合は投稿タイトルを使用
		if ( $post ) {
			return get_the_title( $post );
		}

		// HTMLからタイトルを抽出
		if ( $html && preg_match( '/<title[^>]*>([^<]+)<\/title>/i', $html, $matches ) ) {
			return trim( $matches[1] );
		}

		// URLパスから推測
		$path = wp_parse_url( $url, PHP_URL_PATH );
		$path = trim( $path, '/' );

		if ( empty( $path ) ) {
			return 'ホームページ';
		}

		// パスの最後の部分をタイトルとして使用
		$path_parts = explode( '/', $path );
		$last_part  = end( $path_parts );

		// URLデコードとサニタイズ
		$title = urldecode( $last_part );
		$title = str_replace( array( '-', '_' ), ' ', $title );
		$title = ucwords( $title );

		return $title ? $title : 'ページ';
	}

	/**
	 * HTMLから画像を抽出
	 *
	 * @param string $html HTMLコンテンツ
	 * @return array
	 */
	private function extract_images_from_html( $html ) {
		$images = array();

		// HTMLから全てのimg要素を抽出
		if ( preg_match_all( '/<img[^>]+>/i', $html, $img_matches ) ) {
			foreach ( $img_matches[0] as $img_tag ) {
				$image_data = $this->parse_img_tag( $img_tag );
				if ( $image_data ) {
					$images[] = $image_data;
				}
			}
		}

		// 重複を除去（同じattachment_idの画像）
		$unique_images = array();
		$seen_ids      = array();

		foreach ( $images as $image ) {
			if ( isset( $image['attachment_id'] ) && $image['attachment_id'] && ! in_array( $image['attachment_id'], $seen_ids, true ) ) {
				$unique_images[] = $image;
				$seen_ids[]      = $image['attachment_id'];
			}
		}

		return $unique_images;
	}

	/**
	 * img要素を解析して画像データを取得
	 *
	 * @param string $img_tag img要素のHTML
	 * @return array|false
	 */
	private function parse_img_tag( $img_tag ) {
		// src属性を取得
		if ( ! preg_match( '/src=["\']([^"\']+)["\']/', $img_tag, $src_matches ) ) {
			return false;
		}

		$src           = $src_matches[1];
		$attachment_id = 0;
		$type          = 'content';

		// wp-image-{ID} クラスから添付ファイルIDを取得
		if ( preg_match( '/wp-image-(\d+)/', $img_tag, $id_matches ) ) {
			$attachment_id = intval( $id_matches[1] );
		} else {
			// クラスが見つからない場合は、URLから添付ファイルIDを特定
			$attachment_id = $this->get_attachment_id_by_url( $src );
		}

		// LazyLoadやCDN対応（data-src属性もチェック）
		if ( ! $attachment_id && preg_match( '/data-src=["\']([^"\']+)["\']/', $img_tag, $data_src_matches ) ) {
			$data_src      = $data_src_matches[1];
			$attachment_id = $this->get_attachment_id_by_url( $data_src );
			if ( $attachment_id ) {
				$src = $data_src;
			}
		}

		// 添付ファイルIDが見つからない場合、または画像でない場合はスキップ
		if ( ! $attachment_id || get_post_type( $attachment_id ) !== 'attachment' ) {
			return false;
		}

		// MIMEタイプをチェック
		$mime_type = get_post_mime_type( $attachment_id );
		if ( ! in_array( $mime_type, array( 'image/jpeg', 'image/png', 'image/gif' ), true ) ) {
			return false;
		}

		return array(
			'attachment_id' => $attachment_id,
			'src'           => $src,
			'type'          => $type,
		);
	}

	/**
	 * URLから添付ファイルIDを取得
	 *
	 * @param string $url 画像URL
	 * @return int
	 */
	private function get_attachment_id_by_url( $url ) {
		// 相対URLを絶対URLに変換
		if ( strpos( $url, '//' ) === 0 ) {
			$url = ( is_ssl() ? 'https:' : 'http:' ) . $url;
		} elseif ( strpos( $url, '/' ) === 0 ) {
			$url = home_url( $url );
		}

		// WordPressの標準関数を使用
		$attachment_id = attachment_url_to_postid( $url );

		if ( $attachment_id ) {
			return $attachment_id;
		}

		// サムネイルURLの場合は、元画像のURLから特定を試行
		$upload_dir = wp_upload_dir();
		$base_url   = $upload_dir['baseurl'];

		if ( strpos( $url, $base_url ) === false ) {
			return 0;
		}

		// URLからファイル名を抽出
		$file_path = str_replace( $base_url, '', $url );
		$file_path = ltrim( $file_path, '/' );

		// サムネイルサイズの接尾辞を除去して元ファイル名を取得
		$path_info = pathinfo( $file_path );
		$filename  = $path_info['filename'];
		$extension = isset( $path_info['extension'] ) ? $path_info['extension'] : '';

		// サムネイルの接尾辞パターン（例：-150x150、-300x200など）を除去
		$original_filename = preg_replace( '/-\d+x\d+$/', '', $filename );

		if ( $original_filename !== $filename ) {
			$original_file_path = $path_info['dirname'] . '/' . $original_filename . '.' . $extension;
			$original_url       = $base_url . '/' . $original_file_path;
			$attachment_id      = attachment_url_to_postid( $original_url );

			if ( $attachment_id ) {
				return $attachment_id;
			}
		}

		// データベースから検索（最後の手段）
		$basename    = basename( $file_path );
		$attachments = get_posts(
			array(
				'post_type'      => 'attachment',
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => '_wp_attached_file',
						'value'   => $basename,
						'compare' => 'LIKE',
					),
				),
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		return $attachments ? intval( $attachments[0] ) : 0;
	}

	/**
	 * コンテンツから画像を抽出（フォールバック用）
	 *
	 * @param string $content コンテンツ
	 * @return array
	 */
	private function extract_images_from_content( $content ) {
		$images = array();

		// wp-image-{ID} クラスを持つ画像を抽出
		if ( preg_match_all( '/<img[^>]+wp-image-(\d+)[^>]*>/i', $content, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
				$attachment_id = intval( $match[1] );

				// src属性を取得
				if ( preg_match( '/src=["\']([^"\']+)["\']/', $match[0], $src_matches ) ) {
					$images[] = array(
						'attachment_id' => $attachment_id,
						'src'           => $src_matches[1],
						'type'          => 'content',
					);
				}
			}
		}

		// 重複を除去
		$unique_images = array();
		$seen_ids      = array();

		foreach ( $images as $image ) {
			if ( ! in_array( $image['attachment_id'], $seen_ids, true ) ) {
				$unique_images[] = $image;
				$seen_ids[]      = $image['attachment_id'];
			}
		}

		return $unique_images;
	}

	/**
	 * WebPファイルの存在確認
	 *
	 * @param int $attachment_id アタッチメントID
	 * @return bool
	 */
	private function check_webp_exists( $attachment_id ) {
		$reflection = new ReflectionClass( $this->webp_handler );
		$method     = $reflection->getMethod( 'check_webp_exists' );
		$method->setAccessible( true );

		return $method->invoke( $this->webp_handler, $attachment_id );
	}
}
