<?php
/**
 * テーマのためのカスタマイザー設定を定義
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

if ( ! class_exists( 'WPF_Customizer' ) ) {
	/**
	 * テーマのためのカスタマイザー設定を定義する。
	 *
	 * @since 0.1.0
	 */
	class WPF_Customizer {
		/**
		 * コンストラクタ。オブジェクトをインスタンス化します。
		 *
		 * @access public
		 * @since 0.1.0
		 */
		public function __construct() {
			add_action( 'customize_register', array( $this, 'register' ) );
		}

		/**
		 * カスタム項目を登録する
		 *
		 * @param WP_Customize_Manager $wp_customize WP_Customize_Managerインスタンス
		 *
		 * @return void
		 * @since 0.1.0
		 */
		public function register( $wp_customize ) {
			// site-titleをpostMessageに変更する
			$wp_customize->get_setting( 'blogname' )->transport = 'postMessage';

			// blognameのパーシャルを追加する
			$wp_customize->selective_refresh->add_partial(
				'blogname',
				array(
					'selector'        => '.site-title',
					'render_callback' => array( $this, 'partial_blogname' ),
				)
			);

			/**
			 * 「テーマ設定」セクションを追加
			 */
			$wp_customize->add_section(
				'wpf_theme_settings',
				array(
					'title'    => esc_html__( 'テーマ設定', 'wordpressfoundation' ),
					'priority' => 0,
				)
			);

			// OGP画像
			$wp_customize->add_setting(
				'wpf_ogp_image',
				array(
					'default'   => 0,
					'transport' => 'refresh',
				)
			);
			$wp_customize->add_control(
				new WP_Customize_Cropped_Image_Control(
					$wp_customize,
					'wpf_ogp_image',
					array(
						'label'       => esc_html__( 'OGP画像', 'wordpressfoundation' ),
						'description' => esc_html__( '推奨画像サイズ: 1200×630（px）', 'wordpressfoundation' ),
						'section'     => 'wpf_theme_settings',
						'priority'    => 0,
						'settings'    => 'wpf_ogp_image',
						'height'      => 630,
						'width'       => 1200,
						'flex_height' => 630,
						'flex_width'  => 1200,
					)
				)
			);

			// No image
			$wp_customize->add_setting(
				'wpf_no_image',
				array(
					'default'   => '',
					'transport' => 'refresh',
				)
			);
			$wp_customize->add_control(
				new WP_Customize_Cropped_Image_Control(
					$wp_customize,
					'wpf_no_image',
					array(
						'label'       => esc_html__( 'No image', 'wordpressfoundation' ),
						'description' => esc_html__( 'サムネイルがない場合の代替画像を設定します。推奨画像サイズ: 936×527（px）', 'wordpressfoundation' ),
						'section'     => 'wpf_theme_settings',
						'priority'    => 0,
						'settings'    => 'wpf_no_image',
						'height'      => 527,
						'width'       => 936,
						'flex_height' => 527,
						'flex_width'  => 936,
					)
				)
			);

			// Googleタグマネージャー
			$wp_customize->add_setting(
				'wpf_gtm_tag_id',
				array(
					'default'   => '',
					'transport' => 'postMessage',
				)
			);
			$wp_customize->add_control(
				'wpf_gtm_tag_id',
				array(
					'settings'    => 'wpf_gtm_tag_id',
					'label'       => esc_html__( 'Googleタグマネージャー', 'wordpressfoundation' ),
					'description' => esc_html__( '「GTM-」から始まるIDを入力します（例, GTM-PQPQ54K）。※Googleアナリティクスは、プライバシーポリシーによって設定すべき項目が異なるため、自己責任で使用してください。', 'wordpressfoundation' ),
					'priority'    => 0,
					'section'     => 'wpf_theme_settings',
					'type'        => 'text',
				)
			);
		}

		/**
		 * チェックボックスのブール値をサニタイズ
		 *
		 * @param bool $checked ボックスにチェックが入っているかどうか。
		 * @return bool
		 * @since 0.1.0
		 */
		public static function sanitize_checkbox( $checked = null ) {
			return (bool) isset( $checked ) && true === $checked;
		}

		/**
		 * セレクティブリフレッシュパーシャルのサイトタイトルをレンダリングする
		 *
		 * @access public
		 * @return void
		 * @since 0.1.0
		 */
		public function partial_blogname() {
			bloginfo( 'name' );
		}
	}
}
