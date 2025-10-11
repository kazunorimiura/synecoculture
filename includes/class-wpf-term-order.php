<?php
/**
 * タクソノミーに順序設定を追加するクラス。
 *
 * @package wordpressfoundation
 */

/**
 * タクソノミーに順序設定を追加するクラス。
 */
class WPF_Term_Order {

	/**
	 * 使用するタクソノミー名の配列。
	 *
	 * @var array
	 */
	public $taxonomies = array();

	/**
	 * コンストラクタ。
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * 初期化
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'create_term', array( $this, 'add_term_order' ), 20, 3 );
		add_action( 'edit_term', array( $this, 'add_term_order' ), 20, 3 );

		// 可視タクソノミーを取得する
		$this->taxonomies = $this->get_taxonomies();

		// ajaxアクションでは、常にこれらをフックする
		foreach ( $this->taxonomies as $value ) {

			// シンプルにカラムを取得する
			add_filter( "manage_edit-{$value}_columns", array( $this, 'add_column_header' ) );
			add_filter( "manage_{$value}_custom_column", array( $this, 'add_column_value' ), 10, 3 );
			add_filter( "manage_edit-{$value}_sortable_columns", array( $this, 'sortable_columns' ) );

			add_action( "{$value}_add_form_fields", array( $this, 'term_order_add_form_field' ) );
			add_action( "{$value}_edit_form_fields", array( $this, 'term_order_edit_form_field' ) );

			// メタ値 `_wpf_term_order` を登録する
			register_term_meta(
				$value,
				'_wpf_term_order',
				array(
					'type'         => 'integer',
					'description'  => esc_html__( 'Numeric order for terms, useful when sorting', 'wordpressfoundation' ),
					'default'      => 0,
					'single'       => true,
					'show_in_rest' => true,
				)
			);
		}

		// ブログの管理画面のみ
		if ( is_blog_admin() || doing_action( 'wp_ajax_inline_save_tax' ) || defined( 'WP_CLI' ) ) {
			// タクソノミーがサポートされている場合のみ進む
			if ( ! empty( $_REQUEST['taxonomy'] ) && $this->taxonomy_supported( $_REQUEST['taxonomy'] ) && ! defined( 'WP_CLI' ) ) { // phpcs:ignore
				add_action( 'load-edit-tags.php', array( $this, 'edit_tags' ) );
			}
		}
	}

	/**
	 * 管理エリアのフック
	 *
	 * @since 0.1.0
	 */
	public function edit_tags() {
		add_action( 'admin_footer', array( $this, 'quick_edit_script' ) );
		add_action( 'admin_head-edit-tags.php', array( $this, 'admin_head' ) );
		add_action( 'quick_edit_custom_box', array( $this, 'quick_edit_term_order' ), 10, 3 );
	}

	/**
	 * 新しいタームを追加するときに `order` フォームフィールドを出力する
	 *
	 * @since 0.1.0
	 */
	public function term_order_add_form_field() {
		$classes = array(
			'form-field',
			'form-required',
			'wp-term-order-form-field',
		);

		?>
		<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
			<label for="_wpf_term_order">
				<?php esc_html_e( '順序', 'wordpressfoundation' ); ?>
			</label>
			<input type="number" pattern="[0-9.]+" name="_wpf_term_order" id="_wpf_term_order" value="0" size="11">
			<p class="description">
				<?php esc_html_e( 'このフィールドに数字を入力し、ソートの順序を設定する。', 'wordpressfoundation' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * 既存のタームを編集するときに `order` フォームフィールドを出力する
	 *
	 * @since 0.1.0
	 * @param object $term Termオブジェクト
	 */
	public function term_order_edit_form_field( $term = false ) {
		$classes = array(
			'form-field',
			'wp-term-order-form-field',
		);

		?>
		<tr class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
			<th scope="row" valign="top">
				<label for="_wpf_term_order">
					<?php esc_html_e( '順序', 'wordpressfoundation' ); ?>
				</label>
			</th>
			<td>
				<input name="_wpf_term_order" id="_wpf_term_order" type="number" value="<?php echo esc_attr( $this->get_term_order( $term->term_id ) ); ?>" size="11" />
				<p class="description">
					<?php esc_html_e( 'Terms are usually ordered alphabetically, but you can choose your own order by entering a number (1 for first, etc.) in this field.', 'wordpressfoundation' ); ?>
				</p>
			</td>
		</tr>
		<?php
	}

	/**
	 * タクソノミータームリストテーブルに「Order」カラムを追加
	 *
	 * @since 0.1.0
	 *
	 * @param array $columns カラム
	 * @return array
	 */
	public function add_column_header( $columns = array() ) {
		$columns['_wpf_term_order'] = esc_html__( '順序', 'wordpressfoundation' );

		return $columns;
	}

	/**
	 * カスタムカラムの値（ここではorderの値）を出力
	 *
	 * @since 0.1.0
	 *
	 * @param string $empty カスタムカラム出力。デフォルトは空
	 * @param string $custom_column カラムの名前
	 * @param int    $term_id タームID
	 * @return mixed
	 */
	public function add_column_value( $empty = '', $custom_column = '', $term_id = 0 ) {
		// タクソノミーが渡されていない場合、または 'order' カラムでない場合は処理を中断
		if ( empty( $_REQUEST['taxonomy'] ) || ( '_wpf_term_order' !== $custom_column ) || ! empty( $empty ) ) { // phpcs:ignore
			return;
		}

		return $this->get_term_order( $term_id );
	}

	/**
	 * 並び順によるソートを許可する
	 *
	 * @since 0.1.0
	 * @param array $columns カラム
	 * @return array
	 */
	public function sortable_columns( $columns = array() ) {
		$columns['_wpf_term_order'] = '_wpf_term_order';

		return $columns;
	}

	/**
	 * タームの順序を返す
	 *
	 * @since 0.1.0
	 * @param int $term_id タームID
	 */
	public function get_term_order( $term_id = 0 ) {
		return (int) get_term_meta( $term_id, '_wpf_term_order', true );
	}

	/**
	 * クイック編集フィールドに`order`を出力する
	 *
	 * @since 0.1.0
	 * @param string $column_name 編集するカラムの名前
	 * @param string $screen 投稿タイプのスラッグ、またはタクソノミーリストテーブルの場合は現在のスクリーンネーム
	 * @param string $name もしあれば、タクソノミー名
	 */
	public function quick_edit_term_order( $column_name = '', $screen = '', $name = '' ) {
		if ( ( '_wpf_term_order' !== $column_name ) || ( 'edit-tags' !== $screen ) || ! $this->taxonomy_supported( $name ) ) {
			return false;
		}

		// Default classes
		$classes = array(
			'inline-edit-col',
			'wp-term-order-edit-col',
		);
		?>
		<fieldset>
			<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
				<label>
					<span class="title"><?php esc_html_e( '順序', 'wordpressfoundation' ); ?></span>
					<span class="input-text-wrap">
						<input type="number" pattern="[0-9.]+" class="ptitle" name="_wpf_term_order" value="" size="11">
					</span>
				</label>
			</div>
		</fieldset>
		<?php
	}

	/**
	 * 更新時にタームに `order` を追加する
	 *
	 * @since 0.1.0
	 * @param  int    $term_id   タームID
	 * @param  int    $tt_id     タームタクソノミーID
	 * @param  string $taxonomy  タクソノミースラッグ
	 */
	public function add_term_order( $term_id = 0, $tt_id = 0, $taxonomy = '' ) {

		// orderの情報がPOSTされていない場合は処理を中断する
		// これは例えば"クイック編集"フォームでタームを更新するような場合に発生
		if ( ! isset( $_POST['_wpf_term_order'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return;
		}

		// 値をサニタイズ
		$order = ! empty( $_POST['_wpf_term_order'] ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
			? (int) $_POST['_wpf_term_order'] // phpcs:ignore WordPress.Security.NonceVerification.Missing
			: 0;

		// キャッシュクリーンは不要
		$this->set_term_order( $term_id, $taxonomy, $order, false );
	}

	/**
	 * 特定のタームの順序を設定する
	 *
	 * @since 0.1.0
	 * @global object $wpdb      WordPressのデータベースオブジェクト
	 *
	 * @param int    $term_id    設定対象のタームのID
	 * @param string $taxonomy   タームが属するタクソノミーのスラッグ
	 * @param int    $order      設定する順序の値。数値以外の場合は0として扱われる
	 * @param bool   $clean_cache キャッシュをクリアするかどうか。デフォルトはfalse
	 */
	public function set_term_order( $term_id = 0, $taxonomy = '', $order = 0, $clean_cache = false ) {
		// Avoid malformed order values
		if ( ! is_numeric( $order ) ) {
			$order = 0;
		}

		// Cast to int
		$order = (int) $order;

		// Get existing term order
		$existing_order = $this->get_term_order( $term_id );

		// Bail if no change
		if ( $order === $existing_order ) {
			return;
		}

		// Update the  `order` meta data value
		update_term_meta( $term_id, '_wpf_term_order', (int) $order );
	}

	/**
	 * 使用するタクソノミーを返す。
	 *
	 * @since 0.1.0
	 *
	 * @param array $args `get_taxonomies` に渡される引数。
	 * @return array
	 */
	private function get_taxonomies( $args = array() ) {
		$parsed_args = wp_parse_args(
			$args,
			array(
				'show_ui' => true,
			)
		);
		$taxonomies  = get_taxonomies( $parsed_args );
		return (array) $taxonomies;
	}

	/**
	 * タクソノミがタームの順序付けをサポートしているかどうかをチェックする
	 *
	 * @since 1.0.0
	 * @param array|string $taxonomy タクソノミー名、またはタクソノミー名の配列。
	 * @return bool
	 */
	public function taxonomy_supported( $taxonomy = array() ) {

		// Defaut return value
		$retval = true;

		if ( is_string( $taxonomy ) ) {
			$taxonomy = (array) $taxonomy;
		}

		if ( is_array( $taxonomy ) ) {
			$taxonomy = array_map( 'sanitize_key', $taxonomy );

			foreach ( $taxonomy as $tax ) {
				if ( ! in_array( $tax, $this->taxonomies, true ) ) {
					$retval = false;
					break;
				}
			}
		}

		return (bool) $retval;
	}

	/**
	 * クイック編集のフォームに値をセットする。
	 *
	 * @return void
	 */
	public function quick_edit_script() {
		?>
		<script>
		jQuery( document ).ready( function( $ ) {
			$('.editinline').on('click', function () {
				var tag_id = $(this).parents('tr').attr('id'),
					order = $('td._wpf_term_order', '#' + tag_id).text();

				$(':input[name="_wpf_term_order"]', '.inline-edit-row').val(order);
			});
		} );
		</script>
		<?php
	}

	/**
	 * `_wpf_term_order` カラムを整列させる。
	 *
	 * @return void
	 */
	public function admin_head() {
		?>
		<style type="text/css">
			.column-_wpf_term_order {
				text-align: center;
				width: 74px;
			}
		</style>
		<?php
	}
}
