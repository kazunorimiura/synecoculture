<?php
/**
 * Smart Custom Fieldsプラグインのフィールド登録。
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

/**
 * `WPF_Smart_Cf_Register_Fields` クラス。
 */
class WPF_Smart_Cf_Register_Fields {
	/**
	 * コンストラクタ。
	 */
	public function __construct() {
		add_filter( 'smart-cf-register-fields', array( $this, 'register_fields' ), 10, 4 );
		add_filter( SCF_Config::PREFIX . 'custom_related_posts_args', array( $this, 'custom_related_posts_args' ), 10, 3 );
	}

	/**
	 * カスタムフィールドを定義
	 *
	 * @param array  $settings  Smart_Custom_Fields_Setting オブジェクトの配列
	 * @param string $type      投稿タイプ or ロール
	 * @param int    $id        投稿ID or ユーザーID
	 * @param string $meta_type post | user
	 * @return array
	 */
	public static function register_fields( $settings, $type, $id, $meta_type ) {
		if ( 'page' === $type ) {
			$page_slug = get_post_field( 'post_name', $id );

			if ( 'home' === $page_slug ) {
				$_setting = SCF::add_setting( '_wpf_top__branding', 'ブランディング' );
				$_setting->add_group(
					'_wpf_top__branding',
					false,
					array(
						array(
							'name'  => '_wpf_top__branding__tagline',
							'label' => __( 'タグライン', 'wordpressfoundation' ),
							'type'  => 'text',
						),
						array(
							'name'  => '_wpf_top__branding__body_copy',
							'label' => __( 'ボディコピー', 'wordpressfoundation' ),
							'type'  => 'textarea',
							'rows'  => 3,
						),
					)
				);
				$settings[] = $_setting;

				$_setting = SCF::add_setting( '_wpf_top__slider', 'スライドショー' );
				$_setting->add_group(
					'_wpf_top__slider',
					true,
					array(
						array(
							'name'  => '_wpf_top__slider__slide_image',
							'label' => __( '画像', 'wordpressfoundation' ),
							'type'  => 'image',
							'size'  => 'thumbnail',
							'notes' => __( '推奨画像サイズ: 1125x978px', 'wordpressfoundation' ),
						),
						array(
							'name'  => '_wpf_top__slider__slide_title',
							'label' => __( 'タイトル', 'wordpressfoundation' ),
							'type'  => 'textarea',
							'rows'  => 3,
						),
						array(
							'name'  => '_wpf_top__slider__slide_link_url',
							'label' => __( 'リンクURL', 'wordpressfoundation' ),
							'type'  => 'text',
						),
					)
				);
				$settings[] = $_setting;

				$_setting = SCF::add_setting( '_wpf_top__our_story', 'Our Story' );
				$_setting->add_group(
					'_wpf_top__our_story',
					false,
					array(
						array(
							'name'  => '_wpf_top__our_story__heading',
							'label' => __( '見出し', 'wordpressfoundation' ),
							'type'  => 'textarea',
							'rows'  => 2,
						),
						array(
							'name'  => '_wpf_top__our_story__body',
							'label' => __( '本文', 'wordpressfoundation' ),
							'type'  => 'wysiwyg',
						),
						array(
							'name'  => '_wpf_top__our_story__image_1',
							'label' => __( '画像（１）', 'wordpressfoundation' ),
							'type'  => 'image',
							'size'  => 'thumbnail',
							'notes' => __( '推奨画像サイズ: 幅408px', 'wordpressfoundation' ),
						),
						array(
							'name'  => '_wpf_top__our_story__image_2',
							'label' => __( '画像（２）', 'wordpressfoundation' ),
							'type'  => 'image',
							'size'  => 'thumbnail',
							'notes' => __( '推奨画像サイズ: 幅408px', 'wordpressfoundation' ),
						),
						array(
							'name'  => '_wpf_top__our_story__image_3',
							'label' => __( '画像（３）', 'wordpressfoundation' ),
							'type'  => 'image',
							'size'  => 'thumbnail',
							'notes' => __( '推奨画像サイズ: 幅408px', 'wordpressfoundation' ),
						),
					)
				);
				$settings[] = $_setting;

				$_setting = SCF::add_setting( '_wpf_top__our_approach', 'Our Approach' );
				$_setting->add_group(
					'_wpf_top__our_approach',
					false,
					array(
						array(
							'name'  => '_wpf_top__our_approach__cover_image',
							'label' => __( 'カバー画像', 'wordpressfoundation' ),
							'type'  => 'image',
							'size'  => 'thumbnail',
							'notes' => __( '推奨画像サイズ: 2520x1604px', 'wordpressfoundation' ),
						),
						array(
							'name'  => '_wpf_top__our_approach__cover_text',
							'label' => __( 'カバーテキスト', 'wordpressfoundation' ),
							'type'  => 'textarea',
							'rows'  => 2,
						),
						array(
							'name'  => '_wpf_top__our_approach__heading',
							'label' => __( '見出し', 'wordpressfoundation' ),
							'type'  => 'textarea',
							'rows'  => 2,
						),
						array(
							'name'  => '_wpf_top__our_approach__body',
							'label' => __( '説明', 'wordpressfoundation' ),
							'type'  => 'textarea',
							'rows'  => 3,
						),
					)
				);
				$settings[] = $_setting;

				$_setting = SCF::add_setting( '_wpf_top__benefit', 'Our Approach - 拡張生態系の恩恵' );
				$_setting->add_group(
					'_wpf_top__benefit',
					false,
					array(
						array(
							'name'  => '_wpf_top__benefit__heading',
							'label' => __( '見出し', 'wordpressfoundation' ),
							'type'  => 'textarea',
							'rows'  => 1,
						),
					)
				);
				$_setting->add_group(
					'_wpf_top__benefit_topics',
					true,
					array(
						array(
							'name'  => '_wpf_top__benefit_topics__icon',
							'label' => __( 'アイコン', 'wordpressfoundation' ),
							'type'  => 'textarea',
							'rows'  => 2,
							'notes' => __( 'SVG形式のアイコンを設定してください', 'wordpressfoundation' ),
						),
						array(
							'name'  => '_wpf_top__benefit_topics__heading',
							'label' => __( '見出し', 'wordpressfoundation' ),
							'type'  => 'textarea',
							'rows'  => 1,
						),
						array(
							'name'  => '_wpf_top__benefit_topics__body',
							'label' => __( '説明', 'wordpressfoundation' ),
							'type'  => 'textarea',
							'rows'  => 3,
						),
					)
				);
				$settings[] = $_setting;

				$_setting = SCF::add_setting( '_wpf_top__strategy', 'Our Approach - 具体的な戦略' );
				$_setting->add_group(
					'_wpf_top__strategies',
					true,
					array(
						array(
							'name'  => '_wpf_top__strategies__heading',
							'label' => __( '見出し', 'wordpressfoundation' ),
							'type'  => 'textarea',
							'rows'  => 2,
						),
						array(
							'name'  => '_wpf_top__strategies__body',
							'label' => __( '説明', 'wordpressfoundation' ),
							'type'  => 'textarea',
							'rows'  => 3,
						),
						array(
							'name'  => '_wpf_top__strategies__link_text',
							'label' => __( 'リンクテキスト', 'wordpressfoundation' ),
							'type'  => 'text',
						),
						array(
							'name'  => '_wpf_top__strategies__link_url',
							'label' => __( 'リンクURL', 'wordpressfoundation' ),
							'type'  => 'text',
						),
						array(
							'name'  => '_wpf_top__strategies__bg_image',
							'label' => __( '背景画像', 'wordpressfoundation' ),
							'type'  => 'image',
							'size'  => 'thumbnail',
							'notes' => __( '推奨画像サイズ: 2520x1086px', 'wordpressfoundation' ),
						),
					)
				);
				$settings[] = $_setting;

				$_setting = SCF::add_setting( '_wpf_top__our_purpose', 'Our Purpose' );
				$_setting->add_group(
					'_wpf_top__our_purpose',
					false,
					array(
						array(
							'name'  => '_wpf_top__our_purpose__cover_image',
							'label' => __( 'トリレンマ画像', 'wordpressfoundation' ),
							'type'  => 'image',
							'size'  => 'thumbnail',
							'notes' => __( '推奨画像サイズ: 2520x1604px', 'wordpressfoundation' ),
						),
						array(
							'name'  => '_wpf_top__our_purpose__heading',
							'label' => __( '見出し', 'wordpressfoundation' ),
							'type'  => 'textarea',
							'rows'  => 2,
						),
						array(
							'name'  => '_wpf_top__our_purpose__body',
							'label' => __( '説明', 'wordpressfoundation' ),
							'type'  => 'textarea',
							'rows'  => 3,
						),
					)
				);
				$settings[] = $_setting;

				$_setting = SCF::add_setting( '_wpf_top__trilemma', 'Our Purpose - トリレンマ図' );
				$_setting->add_group(
					'_wpf_top__trilemma',
					true,
					array(
						array(
							'name'  => '_wpf_top__trilemma__heading',
							'label' => __( '見出し', 'wordpressfoundation' ),
							'type'  => 'textarea',
							'rows'  => 2,
						),
						array(
							'name'  => '_wpf_top__trilemma__body',
							'label' => __( '説明', 'wordpressfoundation' ),
							'type'  => 'textarea',
							'rows'  => 3,
						),
						array(
							'name'  => '_wpf_top__trilemma__link_text',
							'label' => __( 'リンクテキスト', 'wordpressfoundation' ),
							'type'  => 'text',
						),
						array(
							'name'  => '_wpf_top__trilemma__link_url',
							'label' => __( 'リンクURL', 'wordpressfoundation' ),
							'type'  => 'text',
						),
					)
				);
				$settings[] = $_setting;

				$_setting = SCF::add_setting( '_wpf_top__our_initiatives', 'Our Initiatives' );
				$_setting->add_group(
					'_wpf_top__our_initiatives',
					false,
					array(
						array(
							'name'  => '_wpf_top__our_initiatives__heading',
							'label' => __( '見出し', 'wordpressfoundation' ),
							'type'  => 'textarea',
							'rows'  => 2,
						),
						array(
							'name'  => '_wpf_top__our_initiatives__body',
							'label' => __( '説明', 'wordpressfoundation' ),
							'type'  => 'textarea',
							'rows'  => 3,
						),
					)
				);
				$settings[] = $_setting;

				$_setting = SCF::add_setting( '_wpf_top__our_initiative_topics', 'Our Initiatives - トピックス' );
				$_setting->add_group(
					'_wpf_top__our_initiative_topics',
					true,
					array(
						array(
							'name'  => '_wpf_top__our_initiative_topics__heading',
							'label' => __( '見出し', 'wordpressfoundation' ),
							'type'  => 'textarea',
							'rows'  => 2,
						),
						array(
							'name'  => '_wpf_top__our_initiative_topics__icon',
							'label' => __( 'アイコン', 'wordpressfoundation' ),
							'type'  => 'textarea',
							'rows'  => 2,
							'notes' => __( 'SVG形式のアイコンを設定してください', 'wordpressfoundation' ),
						),
						array(
							'name'  => '_wpf_top__our_initiative_topics__body',
							'label' => __( '説明', 'wordpressfoundation' ),
							'type'  => 'textarea',
							'rows'  => 3,
						),
						array(
							'name'  => '_wpf_top__our_initiative_topics__link_text',
							'label' => __( 'リンクテキスト', 'wordpressfoundation' ),
							'type'  => 'text',
						),
						array(
							'name'  => '_wpf_top__our_initiative_topics__link_url',
							'label' => __( 'リンクURL', 'wordpressfoundation' ),
							'type'  => 'text',
						),
					)
				);
				$settings[] = $_setting;

				$_setting = SCF::add_setting( '_wpf_top__dive_into_synecoculture', 'シネコカルチャーの世界へようこそ' );
				$_setting->add_group(
					'_wpf_top__dive_into_synecoculture',
					false,
					array(
						array(
							'name'  => '_wpf_top__dive_into_synecoculture__heading',
							'label' => __( '見出し', 'wordpressfoundation' ),
							'type'  => 'textarea',
							'rows'  => 2,
						),
						array(
							'name'  => '_wpf_top__dive_into_synecoculture__body',
							'label' => __( '説明', 'wordpressfoundation' ),
							'type'  => 'textarea',
							'rows'  => 3,
						),
					)
				);
				$settings[] = $_setting;

				$_setting = SCF::add_setting( '_wpf_top__learn', 'シネコカルチャーの世界へようこそ - 学ぶ' );
				$_setting->add_group(
					'_wpf_top__learn',
					true,
					array(
						array(
							'name'  => '_wpf_top__learn__heading',
							'label' => __( '見出し', 'wordpressfoundation' ),
							'type'  => 'textarea',
							'rows'  => 2,
						),
						array(
							'name'  => '_wpf_top__learn__body',
							'label' => __( '説明', 'wordpressfoundation' ),
							'type'  => 'textarea',
							'rows'  => 3,
						),
						array(
							'name'  => '_wpf_top__learn__link_text',
							'label' => __( 'リンクテキスト', 'wordpressfoundation' ),
							'type'  => 'text',
						),
						array(
							'name'  => '_wpf_top__learn__link_url',
							'label' => __( 'リンクURL', 'wordpressfoundation' ),
							'type'  => 'text',
						),
						array(
							'name'  => '_wpf_top__learn__thumbnail',
							'label' => __( 'サムネイル', 'wordpressfoundation' ),
							'type'  => 'image',
							'size'  => 'thumbnail',
							'notes' => __( '推奨画像サイズ: 幅945px', 'wordpressfoundation' ),
						),
					)
				);
				$settings[] = $_setting;

				$_setting = SCF::add_setting( '_wpf_top__join', 'シネコカルチャーの世界へようこそ - 参加・支援' );
				$_setting->add_group(
					'_wpf_top__join',
					true,
					array(
						array(
							'name'  => '_wpf_top__join__heading',
							'label' => __( '見出し', 'wordpressfoundation' ),
							'type'  => 'textarea',
							'rows'  => 2,
						),
						array(
							'name'  => '_wpf_top__join__link_url',
							'label' => __( 'リンクURL', 'wordpressfoundation' ),
							'type'  => 'text',
						),
					)
				);
				$settings[] = $_setting;

				$_setting = SCF::add_setting( '_wpf_top__blog_banner', 'シネコな話バナー' );
				$_setting->add_group(
					'_wpf_top__blog_banner',
					false,
					array(
						array(
							'name'  => '_wpf_top__blog_banner__syneco_blog_logo',
							'label' => __( 'シネコな話ロゴ', 'wordpressfoundation' ),
							'type'  => 'textarea',
							'rows'  => 2,
						),
						array(
							'name'  => '_wpf_top__blog_banner__body',
							'label' => __( '説明', 'wordpressfoundation' ),
							'type'  => 'textarea',
							'rows'  => 2,
						),
						array(
							'name'  => '_wpf_top__blog_banner__bg_image',
							'label' => __( '背景画像', 'wordpressfoundation' ),
							'type'  => 'image',
							'size'  => 'thumbnail',
							'notes' => __( '推奨画像サイズ: 2520x1080px', 'wordpressfoundation' ),
						),
					)
				);
				$settings[] = $_setting;

				$_setting = SCF::add_setting( '_wpf_top__news', 'ニュース' );
				$_setting->add_group(
					'_wpf_top__news',
					false,
					array(
						array(
							'name'  => '_wpf_top__news__heading',
							'label' => __( '見出し', 'wordpressfoundation' ),
							'type'  => 'textarea',
							'rows'  => 2,
						),
						array(
							'name'  => '_wpf_top__news__body',
							'label' => __( '説明', 'wordpressfoundation' ),
							'type'  => 'textarea',
							'rows'  => 2,
						),
					)
				);
				$settings[] = $_setting;
			}

			if ( 'about' === $page_slug ) {
				$_setting = SCF::add_setting( '_wpf_about__intro', 'イントロダクション' );
				$_setting->add_group(
					'_wpf_about__intro',
					false,
					array(
						array(
							'name'  => '_wpf_about__intro__body',
							'label' => __( '本文', 'wordpressfoundation' ),
							'type'  => 'textarea',
							'rows'  => 2,
						),
						array(
							'name'  => '_wpf_about__intro__image',
							'label' => __( '画像', 'wordpressfoundation' ),
							'type'  => 'image',
							'size'  => 'thumbnail',
							'notes' => __( '推奨画像サイズ: 1000x563px', 'wordpressfoundation' ),
						),
					)
				);
				$settings[] = $_setting;

				$_setting = SCF::add_setting( '_wpf_about__trilemma', '食・環境・健康のトリレンマ' );
				$_setting->add_group(
					'_wpf_about__trilemma',
					false,
					array(
						array(
							'name'    => '_wpf_about__trilemma__message',
							'type'    => 'message',
							'default' => __( '※食・環境・健康のトリレンマに関するコンテンツは「ホーム」固定ページで設定してください。', 'wordpressfoundation' ),
						),
					)
				);
				$settings[] = $_setting;

				$_setting = SCF::add_setting( '_wpf_about__our_values', '行動指針' );
				$_setting->add_group(
					'_wpf_about__our_values__header',
					false,
					array(
						array(
							'name'  => '_wpf_about__our_values__header__heading',
							'label' => __( '大見出し', 'wordpressfoundation' ),
							'type'  => 'text',
						),
					)
				);
				$settings[] = $_setting;
				$_setting->add_group(
					'_wpf_about__our_values',
					true,
					array(
						array(
							'name'  => '_wpf_about__our_values__heading',
							'label' => __( '見出し', 'wordpressfoundation' ),
							'type'  => 'text',
						),
						array(
							'name'  => '_wpf_about__our_values__body',
							'label' => __( '本文', 'wordpressfoundation' ),
							'type'  => 'textarea',
							'rows'  => 2,
						),
					)
				);
				$settings[] = $_setting;

				$_setting = SCF::add_setting( '_wpf_about__child_page_links', '下層ページへのリンク' );
				$_setting->add_group(
					'_wpf_about__child_page_links',
					true,
					array(
						array(
							'name'  => '_wpf_about__child_page_links__heading',
							'label' => __( '見出し', 'wordpressfoundation' ),
							'type'  => 'text',
						),
						array(
							'name'  => '_wpf_about__child_page_links__body',
							'label' => __( '本文', 'wordpressfoundation' ),
							'type'  => 'textarea',
							'rows'  => 2,
						),
						array(
							'name'  => '_wpf_about__child_page_links__image',
							'label' => __( 'サムネイル', 'wordpressfoundation' ),
							'type'  => 'image',
							'size'  => 'thumbnail',
							'notes' => __( '推奨画像サイズ: 500x500px', 'wordpressfoundation' ),
						),
						array(
							'name'  => '_wpf_about__child_page_links__link_url',
							'label' => __( 'リンクURL', 'wordpressfoundation' ),
							'type'  => 'text',
						),
					)
				);
				$settings[] = $_setting;
			}

			if ( 'company-profile' === $page_slug ) {
				$_setting = SCF::add_setting( '_wpf_company_profile', '組織概要' );
				$_setting->add_group(
					'_wpf_company_profile',
					true,
					array(
						array(
							'name'  => '_wpf_company_profile__heading',
							'label' => __( '見出し', 'wordpressfoundation' ),
							'type'  => 'text',
						),
						array(
							'name'  => '_wpf_company_profile__body',
							'label' => __( '本文', 'wordpressfoundation' ),
							'type'  => 'wysiwyg',
						),
					)
				);
				$settings[] = $_setting;
			}

			if ( 'history' === $page_slug ) {
				$_setting = SCF::add_setting( '_wpf_history', '沿革' );
				$_setting->add_group(
					'_wpf_history',
					true,
					array(
						array(
							'name'  => '_wpf_history__heading',
							'label' => __( '見出し', 'wordpressfoundation' ),
							'type'  => 'text',
						),
						array(
							'name'  => '_wpf_history__body',
							'label' => __( '本文', 'wordpressfoundation' ),
							'type'  => 'textarea',
							'rows'  => 2,
						),
					)
				);
				$settings[] = $_setting;
			}

			if ( 'manual' === $page_slug ) {
				$_setting = SCF::add_setting( '_wpf_manual__link_banner', 'リンクバナー' );
				$_setting->add_group(
					'_wpf_manual__link_banner',
					true,
					array(
						array(
							'name'  => '_wpf_manual__link_banner__image',
							'label' => __( '画像', 'wordpressfoundation' ),
							'type'  => 'image',
							'size'  => 'thumbnail',
							'notes' => __( '推奨画像サイズ: 幅1000px', 'wordpressfoundation' ),
						),
						array(
							'name'  => '_wpf_manual__link_banner__url',
							'label' => __( 'リンクURL（任意）', 'wordpressfoundation' ),
							'type'  => 'text',
						),
					)
				);
				$settings[] = $_setting;

				$_setting = SCF::add_setting( '_wpf_manual__copyright', 'メタデータ' );
				$_setting->add_group(
					'_wpf_manual__copyright',
					false,
					array(
						array(
							'name'    => '_wpf_manual__author',
							'label'   => __( '著者', 'wordpressfoundation' ),
							'type'    => 'text',
							'default' => '編著：舩橋真俊',
						),
						array(
							'name'    => '_wpf_manual__metadata',
							'label'   => __( 'メタデータ', 'wordpressfoundation' ),
							'type'    => 'wysiwyg',
							'default' => '<p>ISSN 2432-3950<br>引用形式：舩橋真俊 編著 『シネコカルチャー実践マニュアル 2025年度版』 (日本語版) Research and Education material of UniTwin UNESCO Complex Systems Digital Campus, e-laboratory: Human Augmentation of Ecosystems, No.4, ver:2025.10.20<br>編著 : (株)ソニーコンピュータサイエンス研究所/一般社団法人シネコカルチャー 舩橋真俊<br>図表デザイン: 玉木明</p>',
						),
						array(
							'name'    => '_wpf_manual__copyright__footer',
							'label'   => __( 'コピーライト表示（フッター）', 'wordpressfoundation' ),
							'type'    => 'wysiwyg',
							'default' => '<p style="text-align: right;">著者 Masatoshi Funabashi<br>&copy; Copyright 2023, Masatoshi Funabashi.</p>',
						),
					)
				);
				$settings[] = $_setting;
			}
		}

		if ( 'case-study' === $type ) {
			$_setting = SCF::add_setting( '_wpf_case_study__basic', '基本情報' );
			$_setting->add_group(
				'_wpf_case_study__basic',
				true,
				array(
					array(
						'name'  => '_wpf_case_study__basic__heading',
						'label' => __( '見出し', 'wordpressfoundation' ),
						'type'  => 'text',
					),
					array(
						'name'  => '_wpf_case_study__basic__body',
						'label' => __( '説明', 'wordpressfoundation' ),
						'type'  => 'textarea',
						'rows'  => 5,
					),
				)
			);
			$settings[] = $_setting;

			$_setting = SCF::add_setting( '_wpf_case_study__detail', '実践内容' );
			$_setting->add_group(
				'_wpf_case_study__detail',
				true,
				array(
					array(
						'name'  => '_wpf_case_study__detail__heading',
						'label' => __( '見出し', 'wordpressfoundation' ),
						'type'  => 'text',
					),
					array(
						'name'  => '_wpf_case_study__detail__body',
						'label' => __( '説明', 'wordpressfoundation' ),
						'type'  => 'textarea',
						'rows'  => 5,
					),
				)
			);
			$settings[] = $_setting;

			$_setting = SCF::add_setting( '_wpf_case_study__results', '成果・気づき' );
			$_setting->add_group(
				'_wpf_case_study__results',
				true,
				array(
					array(
						'name'  => '_wpf_case_study__results__heading',
						'label' => __( '見出し', 'wordpressfoundation' ),
						'type'  => 'text',
					),
					array(
						'name'  => '_wpf_case_study__results__body',
						'label' => __( '説明', 'wordpressfoundation' ),
						'type'  => 'textarea',
						'rows'  => 5,
					),
				)
			);
			$settings[] = $_setting;

			$_setting = SCF::add_setting( '_wpf_case_study__log', '観察記録' );
			$_setting->add_group(
				'_wpf_case_study__log',
				true,
				array(
					array(
						'name'  => '_wpf_case_study__log__date',
						'label' => __( '年月', 'wordpressfoundation' ),
						'type'  => 'text',
					),
					array(
						'name'  => '_wpf_top__learn__image',
						'label' => __( '写真', 'wordpressfoundation' ),
						'type'  => 'image',
						'size'  => 'thumbnail',
						'notes' => __( '推奨画像サイズ: 幅1040px', 'wordpressfoundation' ),
					),
					array(
						'name'  => '_wpf_case_study__log__body',
						'label' => __( '説明', 'wordpressfoundation' ),
						'type'  => 'textarea',
						'rows'  => 5,
					),
				)
			);
			$settings[] = $_setting;
		}

		return $settings;
	}

	/**
	 * SCFの関連投稿フィールドのクエリをカスタムする。
	 *
	 * @param array  $args WP_Query引数オブジェクト。
	 * @param string $field_name inputフィールド名。
	 * @param string $post_type 投稿タイプ。
	 * @return array
	 */
	public static function custom_related_posts_args( $args, $field_name, $post_type ) {
		// 親投稿のみを対象とする
		$args['post_parent'] = 0;

		return $args;
	}
}
