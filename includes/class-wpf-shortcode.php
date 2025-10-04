<?php
/**
 * テーマのためのショートコード
 *
 * @package wordpressfoundation
 */

if ( ! class_exists( 'WPF_Shortcode' ) ) {
	/**
	 * テーマのためのショートコード。
	 *
	 * @since 0.1.0
	 */
	class WPF_Shortcode {

		/**
		 * コンストラクタ。ショートコードを追加する。
		 *
		 * @since 0.1.0
		 */
		public function __construct() {
			$this->add_shortcodes();
		}

		/**
		 * ショートコードを追加する。
		 *
		 * @since 0.1.0
		 */
		public function add_shortcodes() {
			add_shortcode( 'wpf_darkmode_switch', array( $this, 'darkmode_switch' ) );
			add_shortcode( 'wpf_syneco_branding', array( $this, 'syneco_branding' ) );
			add_shortcode( 'wpf_our_story', array( $this, 'our_story' ) );
			add_shortcode( 'wpf_our_approach', array( $this, 'our_approach' ) );
			add_shortcode( 'wpf_our_purpose', array( $this, 'our_purpose' ) );
			add_shortcode( 'wpf_trilemma', array( $this, 'trilemma' ) );
			add_shortcode( 'wpf_featured_projects', array( $this, 'featured_projects' ) );
			add_shortcode( 'wpf_dive_into_synecoculture', array( $this, 'dive_into_synecoculture' ) );
			add_shortcode( 'wpf_blog_banner', array( $this, 'blog_banner' ) );
			add_shortcode( 'wpf_news_slider', array( $this, 'news_slider' ) );
			add_shortcode( 'wpf_our_initiatives', array( $this, 'our_initiatives' ) );
		}

		/**
		 * ダークモードスイッチを出力するショートコード。
		 *
		 * @param array $atts ショートコード引数。
		 * @return string
		 */
		public static function darkmode_switch( $atts ) {
			$checked = 'dark' === WPF_Utils::get_site_theme() ? ' checked' : '';

			// デフォルト引数と与えられた引数を結合する
			$atts = shortcode_atts(
				array(),
				$atts,
				'wpf_darkmode_switch'
			);

			ob_start();
			?>
			<label for="darkmode-toggle" data-darkmode-toggle style="--toggle-size: var(--font-size-text--sm)">
				<span class="darkmode-toggle__label screen-reader-text"><?php esc_html_e( 'ダークモード', 'wordpressfoundation' ); ?></span>
				<input class="darkmode-toggle__input" id="darkmode-toggle" role="switch" type="checkbox"<?php echo esc_attr( $checked ); ?>>
				<div class="darkmode-toggle__decor" aria-hidden="true">
					<div class="darkmode-toggle__light"></div>
					<div class="darkmode-toggle__thumb"></div>
					<div class="darkmode-toggle__dark"></div>
				</div>
			</label>
			<?php
			return ob_get_clean();
		}

		/**
		 * トップページのメインビジュアルを出力するショートコード。
		 *
		 * @param array $atts ショートコード引数。
		 * @return string
		 */
		public static function syneco_branding( $atts ) {
			// デフォルト引数と与えられた引数を結合する
			$atts = shortcode_atts(
				array(),
				$atts,
				'wpf_syneco_branding'
			);

			$tagline   = SCF::get( '_wpf_top__branding__tagline' );
			$body_copy = SCF::get( '_wpf_top__branding__body_copy' );
			$slider    = SCF::get( '_wpf_top__slider' );

			ob_start();
			if ( ! empty( $tagline ) || ! empty( $body_copy ) || ! empty( $slider ) ) {
				?>
				<div class="syneco-branding-container">
				<div class="syneco-branding">
				<?php
				if ( ! empty( $tagline ) || ! empty( $body_copy ) ) {
					?>
					<div class="syneco-branding-message">
					<?php
					// タグライン
					if ( ! empty( $tagline ) ) {
						?>
							<div class="syneco-tagline vertical-writing">
							<?php echo wp_kses_post( $tagline ); ?>
							</div>
							<?php
					}

					// ボディコピー
					if ( ! empty( $body_copy ) ) {
						?>
							<div class="syneco-body-copy vertical-writing">
							<?php echo wp_kses_post( $body_copy ); ?>
							</div>
							<?php
					}
					?>
					</div>
					<?php
				}

				// スライドショー
				if ( ! empty( $slider ) ) {
					?>
					<div class="syneco-slider-container">
						<div id="synecoSlider" class="syneco-slider swiper">
							<div class="swiper-wrapper">
							<?php
							foreach ( $slider as $slide ) {
								$slide_image_id = $slide['_wpf_top__slider__slide_image'];
								$slide_title    = $slide['_wpf_top__slider__slide_title'];
								$slide_link_url = $slide['_wpf_top__slider__slide_link_url'];

								if ( ! empty( $slide_image_id ) ) {
									$slide_image = wp_get_attachment_image(
										$slide_image_id,
										'1536x1536',
										false,
										array()
									);
									if ( ! empty( $slide_image ) ) {
										?>
											<div class="syneco-slider__item swiper-slide">
											<?php
											// スライド画像
											$classes = 'syneco-slider__item-image';
											if ( ! empty( $slide_link_url ) ) {
												?>
													<a href="<?php echo esc_url( $slide_link_url ); ?>" class="<?php echo esc_attr( $classes ); ?>">
													<?php
											} else {
												?>
													<div class="<?php echo esc_attr( $classes ); ?>">
													<?php
											}
											echo WPF_Template_Tags::kses_post( $slide_image ); // phpcs:ignore WordPress.Security.EscapeOutput
											if ( ! empty( $slide_link_url ) ) {
												?>
													</a>
												<?php
											} else {
												?>
													</div>
												<?php
											}

											// スライドタイトル
											if ( ! empty( $slide_title ) ) {
												$classes = 'syneco-slider__item-title';
												if ( ! empty( $slide_link_url ) ) {
													?>
														<a 
															href="<?php echo esc_url( $slide_link_url ); ?>" 
															class="<?php echo esc_attr( $classes ); ?>"
														>
														<?php
												} else {
													?>
														<div class="<?php echo esc_attr( $classes ); ?>">
														<?php
												}
												echo wp_kses_post( $slide_title ); // タイトルを出力
												if ( ! empty( $slide_link_url ) ) {
													?>
														</a>
													<?php
												} else {
													?>
														</div>
													<?php
												}
											}
											?>
											</div>
											<?php
									}
								}
							}
							?>
							</div>

							<div class="syneco-slider__nav-container">
								<div class="syneco-slider__nav-button-prev">
										<span class="screen-reader-text"><?php echo esc_html_e( '前へ', 'wordpressfoundation' ); ?></span>
								</div>
								<div class="syneco-slider__nav-button-next">
										<span class="screen-reader-text"><?php echo esc_html_e( '次へ', 'wordpressfoundation' ); ?></span>
								</div>
							</div>
						</div>

						<div class="syneco-branding__cta">
							<div class="syneco-branding__cta-icon">
								<?php echo WPF_Icons::get_svg( 'ui', 'angle_up', 24 ); /* phpcs:ignore WordPress.Security.EscapeOutput */ ?>
							</div>
							<div class="syneco-branding__cta-text">Scroll</div>
						</div>
					</div>
						<?php
				}
				?>
				</div>
				</div>
				<?php
			}
			?>
			<?php
			return ob_get_clean();
		}

		/**
		 * トップページのOur Storyを出力するショートコード。
		 *
		 * @param array $atts ショートコード引数。
		 * @return string
		 */
		public static function our_story( $atts ) {
			// デフォルト引数と与えられた引数を結合する
			$atts = shortcode_atts(
				array(),
				$atts,
				'wpf_our_story'
			);

			$heading    = SCF::get( '_wpf_top__our_story__heading' );
			$body       = SCF::get( '_wpf_top__our_story__body' );
			$image_1_id = SCF::get( '_wpf_top__our_story__image_1' );
			$image_2_id = SCF::get( '_wpf_top__our_story__image_2' );
			$image_3_id = SCF::get( '_wpf_top__our_story__image_3' );

			ob_start();
			if ( ! empty( $heading ) || ! empty( $body ) || ! empty( $image_1_id ) || ! empty( $image_2_id ) || ! empty( $image_3_id ) ) {
				?>
				<div class="our-story">
					<?php
					$image_1 = wp_get_attachment_image(
						$image_1_id,
						'medium_large',
						false,
						array( 'loading' => 'lazy' )
					);
					if ( ! empty( $image_1 ) ) {
						?>
						<div class="our-story__item our-story__item--img1">
							<?php echo WPF_Template_Tags::kses_post( $image_1 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</div>
						<?php
					}
					?>

					<?php
					if ( ! empty( $heading ) || ! empty( $body ) ) {
						?>
						<div class="our-story__item">
							<div class="our-story__item__overline syneco-overline">
								<div class="syneco-overline__icon">
									<?php echo WPF_Icons::get_svg( 'ui', 'syneco', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								</div>
								<div class="syneco-overline__text">Our Story</div>
							</div>

							<h2 class="our-story__item__heading">
								<?php echo wp_kses_post( $heading ); // 見出しを出力 ?>
							</h2>

							<div class="our-story__item__body prose">
								<?php echo wp_kses_post( $body ); // 本文を出力 ?>
							</div>
						</div>
						<?php
					}
					?>

					<?php
					$image_2 = wp_get_attachment_image(
						$image_2_id,
						'medium_large',
						false,
						array( 'loading' => 'lazy' )
					);
					if ( ! empty( $image_2 ) ) {
						?>
						<div class="our-story__item our-story__item--img2">
							<?php echo WPF_Template_Tags::kses_post( $image_2 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</div>
						<?php
					}
					?>

					<?php
					$image_3 = wp_get_attachment_image(
						$image_3_id,
						'medium_large',
						false,
						array( 'loading' => 'lazy' )
					);
					if ( ! empty( $image_3 ) ) {
						?>
						<div class="our-story__item our-story__item--img3">
							<?php echo WPF_Template_Tags::kses_post( $image_3 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</div>
						<?php
					}
					?>
				</div>
				<?php
			}
			return ob_get_clean();
		}

		/**
		 * トップページのOur Approachを出力するショートコード。
		 *
		 * @param array $atts ショートコード引数。
		 * @return string
		 */
		public static function our_approach( $atts ) {
			// デフォルト引数と与えられた引数を結合する
			$atts = shortcode_atts(
				array(),
				$atts,
				'wpf_our_approach'
			);

			$cover_image_id = SCF::get( '_wpf_top__our_approach__cover_image' );
			$cover_text     = SCF::get( '_wpf_top__our_approach__cover_text' );

			ob_start();
			if ( ! empty( $cover_image_id ) || ! empty( $cover_text ) ) {
				?>
				<div class="our-approach">
					<?php
					/**
					 * Our Approach カバー
					 */
					$cover_image = wp_get_attachment_image(
						$cover_image_id,
						'full',
						false,
						array( 'loading' => 'lazy' )
					);
					if ( ! empty( $cover_image ) || ! empty( $cover_text ) ) {
						?>
						<div class="our-approach__cover">
							<div class="our-approach__cover-inner">
								<?php
								if ( ! empty( $cover_text ) ) {
									?>
									<div class="our-approach__cover-text vertical-writing">
										<?php echo WPF_Template_Tags::kses_post( $cover_text ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
									</div>
									<?php
								}
								?>

								<?php
								if ( ! empty( $cover_image ) ) {
									?>
									<div class="our-approach__cover-image">
										<?php echo WPF_Template_Tags::kses_post( $cover_image ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
									</div>
									<?php
								}
								?>
							</div>
						</div>
						<?php
					}
					?>

					<?php
					/**
					 * Our Approach メイン
					 */
					$heading = SCF::get( '_wpf_top__our_approach__heading' );
					$body    = SCF::get( '_wpf_top__our_approach__body' );
					if ( ! empty( $heading ) || ! empty( $body ) ) {
						?>
						<div class="our-approach__main">
							<div class="our-approach__header">
								<div class="our-approach__header__overline syneco-overline">
									<div class="syneco-overline__icon">
										<?php echo WPF_Icons::get_svg( 'ui', 'syneco', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
									</div>
									<div class="syneco-overline__text">Our Approach</div>
								</div>

								<div class="our-approach__header__main">
									<h2 class="our-approach__header__heading">
										<?php echo wp_kses_post( $heading ); // 見出しを出力 ?>
									</h2>

									<div class="our-approach__header__body prose">
										<?php echo wp_kses_post( $body ); // 本文を出力 ?>
									</div>
								</div>
							</div>

							<?php
							/**
							 * 拡張生態系がもたらす恩恵
							 */
							$benefit_heading = SCF::get( '_wpf_top__benefit__heading' );
							$benefit_topics  = SCF::get( '_wpf_top__benefit_topics' );
							if ( ! empty( $benefit_heading ) || ! empty( $benefit_topics ) ) {
								?>
								<div class="our-approach-benefit">
									<?php
									if ( ! empty( $benefit_heading ) ) {
										?>
										<h3 class="our-approach-benefit__header">
											<?php echo wp_kses_post( $benefit_heading ); // 見出しを出力 ?>
										</h3>
										<?php
									}

									if ( ! empty( $benefit_topics ) ) {
										?>
										<div class="our-approach-benefit__main">
											<?php
											foreach ( $benefit_topics as $topic ) {
												?>
												<div class="our-approach-benefit-item">
													<?php
													$icon = $topic['_wpf_top__benefit_topics__icon'];
													if ( ! empty( $icon ) ) {
														?>
														<div class="our-approach-benefit-item__icon">
															<?php
															// アイコン（SVG）を出力
															echo WPF_Template_Tags::kses_post( $icon ); // phpcs:ignore WordPress.Security.EscapeOutput 
															?>
														</div>
														<?php
													}
													?>

													<?php
													$heading = $topic['_wpf_top__benefit_topics__heading'];
													if ( ! empty( $heading ) ) {
														?>
														<h4 class="our-approach-benefit-item__heading">
															<?php echo wp_kses_post( $heading ); // 見出しを出力 ?>
														</h4>
														<?php
													}
													?>

													<?php
													$body = $topic['_wpf_top__benefit_topics__body'];
													if ( ! empty( $body ) ) {
														?>
														<p class="our-approach-benefit-item__body">
															<?php echo wp_kses_post( $body ); // 本文を出力 ?>
														</p>
														<?php
													}
													?>
												</div>
												<?php
											}
											?>
										</div>
										<?php
									}
									?>
								</div>
								<?php
							}
							?>
						</div>

						<?php
						/**
						 * 具体的な戦略
						 */
						$strategies = SCF::get( '_wpf_top__strategies' );
						if ( ! empty( $strategies ) ) {
							?>
							<div class="our-approach-strategies">
								<?php
								foreach ( $strategies as $strategy ) {
									$heading   = $strategy['_wpf_top__strategies__heading'];
									$body      = $strategy['_wpf_top__strategies__body'];
									$link_text = $strategy['_wpf_top__strategies__link_text'];
									$link_url  = $strategy['_wpf_top__strategies__link_url'];
									$image_id  = $strategy['_wpf_top__strategies__bg_image'];
									if ( ! empty( $heading ) || ! empty( $body ) || ! empty( $link_text ) || ! empty( $link_url ) || ! empty( $image_id ) ) {
										?>
										<div class="our-approach-strategies-item">
											<div class="our-approach-strategies-item__main">
												<?php
												if ( ! empty( $heading ) ) {
													?>
													<h2 class="our-approach-strategies-item__heading">
														<?php echo wp_kses_post( $heading ); // 見出しを出力 ?>
													</h2>
													<?php
												}
												?>

												<?php
												if ( ! empty( $body ) ) {
													?>
													<div class="our-approach-strategies-item__body">
														<?php echo wp_kses_post( $body ); // 本文を出力 ?>
													</div>
													<?php
												}
												?>

												<?php
												if ( ! empty( $link_text ) || ! empty( $link_url ) ) {
													?>
													<div class="our-approach-strategies-item__cta">
														<a href="<?php echo esc_url( $link_url ); ?>" class="button:inverse-secondary">
															<?php echo esc_html( $link_text ); // リンクテキストを出力 ?>
														</a>
													</div>
													<?php
												}
												?>
											</div>

											<?php
											if ( ! empty( $image_id ) ) {
												$image = wp_get_attachment_image(
													$image_id,
													'full',
													false,
													array( 'loading' => 'lazy' )
												);
												if ( ! empty( $image ) ) {
													?>
													<div class="our-approach-strategies-item__bg-image">
														<?php echo wp_kses_post( $image ); // 背景画像を出力 ?>
													</div>
													<?php
												}
											}
											?>
										</div>
										<?php
									}
								}
								?>
							</div>
							<?php
						}
						?>
						</div>
						<?php
					}
					?>
				</div>
				<?php
			}
			return ob_get_clean();
		}

		/**
		 * トップページのOur Purposeを出力するショートコード。
		 *
		 * @param array $atts ショートコード引数。
		 * @return string
		 */
		public static function our_purpose( $atts ) {
			// デフォルト引数と与えられた引数を結合する
			$atts = shortcode_atts(
				array(),
				$atts,
				'wpf_our_purpose'
			);

			$cover_image_id = SCF::get( '_wpf_top__our_purpose__cover_image' );
			$heading        = SCF::get( '_wpf_top__our_purpose__heading' );
			$body           = SCF::get( '_wpf_top__our_purpose__body' );

			ob_start();
			if ( ! empty( $heading ) || ! empty( $body ) ) {
				?>
				<div class="our-purpose">
					<?php
					/**
					 * Our Purpose カバー
					 */
					$cover_image = wp_get_attachment_image(
						$cover_image_id,
						'large',
						false,
						array( 'loading' => 'lazy' )
					);
					if ( ! empty( $cover_image ) || ! empty( $cover_text ) ) {
						?>
						<div class="our-purpose__cover">
							<div class="our-purpose__cover-inner">
								<div class="our-purpose__cover-inner__inner">
									<?php
									if ( ! empty( $cover_image ) ) {
										?>
										<div class="our-purpose__cover-image">
											<?php echo WPF_Template_Tags::kses_post( $cover_image ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
										</div>
										<?php
									}
									?>
								</div>
							</div>
						</div>
						<?php
					}
					?>

					<?php
					/**
					 * Our Purpose メイン
					 */
					if ( ! empty( $heading ) || ! empty( $body ) ) {
						?>
						<div class="our-purpose__main">
							<div class="our-purpose__header">
								<div class="our-purpose__header__overline syneco-overline">
									<div class="syneco-overline__icon">
										<?php echo WPF_Icons::get_svg( 'ui', 'syneco', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
									</div>
									<div class="syneco-overline__text">Our Purpose</div>
								</div>

								<div class="our-purpose__header__main">
									<?php
									if ( ! empty( $heading ) ) {
										?>
										<h2 class="our-purpose__header__heading">
											<?php echo wp_kses_post( $heading ); // 見出しを出力 ?>
										</h2>
										<?php
									}
									?>

									<?php
									if ( ! empty( $body ) ) {
										?>
										<div class="our-purpose__header__body prose">
											<?php echo wp_kses_post( $body ); // 本文を出力 ?>
										</div>
										<?php
									}
									?>
								</div>
							</div>

							<?php echo do_shortcode( '[wpf_trilemma]' ); ?>
							<?php echo do_shortcode( '[wpf_our_initiatives]' ); ?>
						</div>
						<?php
					}
					?>
				</div>
				<?php
			}
			return ob_get_clean();
		}

		/**
		 * 食・環境・健康のトリレンマを出力するショートコード。
		 *
		 * @param array $atts ショートコード引数。
		 * @return string
		 */
		public static function trilemma( $atts ) {
			// デフォルト引数と与えられた引数を結合する
			$atts = shortcode_atts(
				array(),
				$atts,
				'wpf_trilemma'
			);

			$trilemma = SCF::get( '_wpf_top__trilemma' );

			ob_start();
			if ( ! empty( $trilemma ) ) {
				?>
				<div class="trilemma">
					<div class="trilemma-image-container">
						<div class="trilemma-image__main">
							<div id="trilemmaImage" class="trilemma-image" aria-label="<?php echo esc_attr_e( '食・環境・健康のトリレンマのイメージ図', 'wordpressfoundation' ); ?>"></div>

							<div class="trilemma-image-nav-container">
								<button 
									class="trilemma-play-pause-button is-playing" 
									type="button"
									aria-controls="trilemmaImage" 
									data-play-text="<?php echo esc_attr_e( 'トリレンマ図のアニメーションを再生', 'wordpressfoundation' ); ?>"
									data-pause-text="<?php echo esc_attr_e( 'トリレンマ図のアニメーションを一時停止', 'wordpressfoundation' ); ?>">
									<span class="screen-reader-text"><?php echo esc_html_e( 'トリレンマ図のアニメーションを一時停止', 'wordpressfoundation' ); ?></span>
									<div data-icon="play">
										<?php echo WPF_Icons::get_svg( 'ui', 'play', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
									</div>
									<div data-icon="pause">
										<?php echo WPF_Icons::get_svg( 'ui', 'pause', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
									</div>
								</button>

								<div class="trilemma-image-nav">
									<button class="trilemma-image-nav__prev-button">
										<span class="screen-reader-text"><?php echo esc_html_e( '前へ', 'wordpressfoundation' ); ?></span>
										<?php echo WPF_Icons::get_svg( 'ui', 'angle_left', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
									</button>
									<button class="trilemma-image-nav__next-button">
										<span class="screen-reader-text"><?php echo esc_html_e( '次へ', 'wordpressfoundation' ); ?></span>
										<?php echo WPF_Icons::get_svg( 'ui', 'angle_right', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
									</button>
								</div>
							</div>
						</div>
					</div>

					<div class="trilemma-explains-container">
						<div class="trilemma-explains-container__inner">
							<div class="trilemma-explains">
								<?php
								foreach ( $trilemma as $trilemma_item ) {
									$heading = $trilemma_item['_wpf_top__trilemma__heading'];
									$body    = $trilemma_item['_wpf_top__trilemma__body'];
									if ( ! empty( $heading ) || ! empty( $body ) ) {
										?>
										<div class="trilemma-explain">
											<div class="trilemma-explain__inner">
												<div class="trilemma-explain__main">
													<h3 class="trilemma-explain__heading">
														<?php echo wp_kses_post( $heading ); ?>
													</h3>

													<div class="trilemma-explain__body">
														<?php echo wp_kses_post( $body ); ?>
													</div>

													<?php
													$link_text = $trilemma_item['_wpf_top__trilemma__link_text'];
													$link_url  = $trilemma_item['_wpf_top__trilemma__link_url'];
													if ( ! empty( $link_text ) && ! empty( $link_url ) ) {
														?>
														<div class="trilemma-explain__cta">
															<a href="<?php echo esc_url( $link_url ); ?>" class="button:secondary">
																<?php echo esc_html( $link_text ); ?>
															</a>
														</div>
														<?php
													}
													?>
												</div>
											</div>
										</div>
										<?php
									}
								}
								?>
							</div>
						</div>
					</div>
				</div>
				<?php
			}
			return ob_get_clean();
		}

		/**
		 * トップページのOur Initiativesを出力するショートコード。
		 *
		 * @param array $atts ショートコード引数。
		 * @return string
		 */
		public static function our_initiatives( $atts ) {
			// デフォルト引数と与えられた引数を結合する
			$atts = shortcode_atts(
				array(),
				$atts,
				'wpf_our_initiatives'
			);

			$heading = SCF::get( '_wpf_top__our_initiatives__heading' );
			$body    = SCF::get( '_wpf_top__our_initiatives__body' );

			ob_start();
			if ( ! empty( $heading ) || ! empty( $body ) ) {
				?>
				<div class="our-initiatives">
					<div class="our-initiatives__header">
						<div class="our-initiatives__header__overline syneco-overline">
							<div class="syneco-overline__icon">
								<?php echo WPF_Icons::get_svg( 'ui', 'syneco', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							</div>
							<div class="syneco-overline__text">Our Initiatives</div>
						</div>

						<div class="our-initiatives__header__main">
							<?php
							if ( ! empty( $heading ) ) {
								?>
								<h2 class="our-initiatives__header__heading">
									<?php echo wp_kses_post( $heading ); // 見出しを出力 ?>
								</h2>
								<?php
							}
							?>

							<?php
							if ( ! empty( $body ) ) {
								?>
								<div class="our-initiatives__header__body prose">
									<?php echo wp_kses_post( $body ); // 本文を出力 ?>
								</div>
								<?php
							}
							?>
						</div>
					</div>

					<?php
					$topics = SCF::get( '_wpf_top__our_initiative_topics' );
					if ( ! empty( $topics ) ) {
						?>
						<div class="our-initiatives__main">
							<?php
							foreach ( $topics as $topic ) {
								$heading = $topic['_wpf_top__our_initiative_topics__heading'];
								$body    = $topic['_wpf_top__our_initiative_topics__body'];
								if ( ! empty( $heading ) || ! empty( $body ) ) {
									?>
									<div class="our-initiatives__item">
										<?php
										if ( ! empty( $heading ) ) {
											?>
											<h3 class="our-initiatives__item__heading">
												<?php echo wp_kses_post( $heading ); // 見出しを出力 ?>
											</h3>
											<?php
										}
										?>

										<?php
										$icon = $topic['_wpf_top__our_initiative_topics__icon'];
										if ( ! empty( $icon ) ) {
											?>
											<div class="our-initiatives__item__icon">
												<?php
												// アイコン（SVG）を出力
												echo WPF_Template_Tags::kses_post( $icon ); // phpcs:ignore WordPress.Security.EscapeOutput 
												?>
											</div>
											<?php
										}
										?>

										<?php
										if ( ! empty( $body ) ) {
											?>
											<div class="our-initiatives__item__body">
												<?php echo wp_kses_post( $body ); // 本文を出力 ?>
											</div>
											<?php
										}
										?>

										<?php
										$link_text = $topic['_wpf_top__our_initiative_topics__link_text'];
										$link_url  = $topic['_wpf_top__our_initiative_topics__link_url'];
										if ( ! empty( $link_text ) && ! empty( $link_url ) ) {
											?>
											<div class="our-initiatives__item__cta">
												<a href="<?php echo esc_url( $link_url ); ?>" class="button:secondary">
													<?php echo esc_html( $link_text ); ?>
												</a>
											</div>
											<?php
										}
										?>
									</div>
									<?php
								}
							}
							?>
						</div>
						<?php
					}
					?>
				</div>
				<?php
			}
			return ob_get_clean();
		}

		/**
		 * Featured Projectsを出力するショートコード。
		 *
		 * @param array $atts ショートコード引数。
		 * @return string
		 */
		public static function featured_projects( $atts ) {
			// デフォルト引数と与えられた引数を結合する
			$atts = shortcode_atts(
				array(),
				$atts,
				'wpf_featured_projects'
			);

			$query = new WP_Query(
				array(
					'post_type'      => 'project',
					'posts_per_page' => 5,
					'meta_key'       => '_wpf_pickup_flag', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'meta_value'     => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				)
			);

			ob_start();
			if ( $query->have_posts() ) {
				$container_classes = 'featured-projects featured-projects--count-' . $query->found_posts;
				?>
				<div class="<?php echo esc_attr( $container_classes ); ?>">
					<div class="featured-projects__bg">
						<div class="featured-projects__header">
							<div class="featured-projects__overline syneco-overline">
								<div class="syneco-overline__icon">
									<?php echo WPF_Icons::get_svg( 'ui', 'syneco', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								</div>
								<div class="syneco-overline__text">Featured Projects</div>
							</div>

							<?php
							$projects_page = get_page_by_path( 'projects' );
							if ( $projects_page ) {
								$projects_page_id = $projects_page->ID;
								if ( function_exists( 'pll_get_post' ) ) {
									$projects_page_id = pll_get_post( $projects_page_id );

									if ( $projects_page->ID !== $projects_page_id ) {
										$projects_page = get_post( $projects_page_id );
									}
								}
								$permalink = get_permalink( $projects_page->ID );
								?>
								<div class="featured-projects__cta">
									<a href="<?php echo esc_url( $permalink ); ?>" class="button:primary">View All</a>
								</div>
								<?php
							}
							?>
						</div>
					</div>

					<?php
					while ( $query->have_posts() ) {
						$query->the_post();

						$cat_terms    = get_the_terms( get_the_ID(), 'project_cat' );
						$domain_terms = get_the_terms( get_the_ID(), 'project_domain' );
						?>
						<div class="featured-projects__item-container">
							<div class="featured-projects__item">
								<div class="featured-projects__item__main">
									<div class="featured-projects__item__header">
										<h3 class="featured-projects__item__heading">
											<?php the_title(); ?>
										</h3>

										<?php
										if ( $cat_terms && ! is_wp_error( $cat_terms ) ) {
											?>
											<div class="featured-projects__item__main-categories">
												<?php
												// 選択しているタームの最祖先を出力
												foreach ( $cat_terms as $term ) {
													// 祖先タームのIDを配列で取得（最も近い親から最上位の順）
													$ancestors = get_ancestors( $term->term_id, 'project_cat', 'taxonomy' );

													if ( ! empty( $ancestors ) ) {
														// 配列の最後が最上位のターム
														$top_parent_id   = end( $ancestors );
														$top_parent      = get_term( $top_parent_id, 'project_cat' );
														$top_parent_link = get_term_link( $top_parent );
														?>
														<a href="<?php echo esc_url( $top_parent_link ); ?>" class="featured-projects__item__main-category pill">
															<?php echo esc_html( $top_parent->name ); ?>
														</a>
														<?php
													} else {
														// 祖先がいない場合は自身が最上位
														$term_link = get_term_link( $term->term_id );
														?>
														<a href="<?php echo esc_url( $term_link ); ?>" class="featured-projects__item__main-category pill">
															<?php echo esc_html( $term->name ); ?>
														</a>
														<?php
													}
												}
												?>
											</div>
											<?php
										}
										?>
									</div>

									<div class="featured-projects__item__excerpt">
										<?php the_excerpt(); ?>
									</div>

									<?php
									// 選択しているタームを出力
									if ( ( $cat_terms && ! is_wp_error( $cat_terms ) ) || ( $domain_terms && ! is_wp_error( $domain_terms ) ) ) {
										?>
										<div class="featured-projects__item__sub-categories">
											<?php
											if ( $cat_terms && ! is_wp_error( $cat_terms ) ) {
												foreach ( $cat_terms as $term ) {
													$term_link = get_term_link( $term->term_id );
													?>
													<a href="<?php echo esc_url( $term_link ); ?>" class="featured-projects__item__sub-category pill-secondary">
														<?php echo esc_html( $term->name ); ?>
													</a>
													<?php
												}
											}

											if ( $domain_terms && ! is_wp_error( $domain_terms ) ) {
												foreach ( $domain_terms as $term ) {
													$term_link = get_term_link( $term->term_id );
													?>
													<a href="<?php echo esc_url( $term_link ); ?>" class="featured-projects__item__sub-category pill-secondary">
														<?php echo esc_html( $term->name ); ?>
													</a>
													<?php
												}
											}
											?>
										</div>
										<?php
									}
									?>
								</div>

								<?php
								if ( has_post_thumbnail() ) {
									?>
									<div class="featured-projects__item__thumbnail-container">
										<a 
											class="d-flex jc-center frame radius featured-projects__item__thumbnail" 
											href="<?php the_permalink(); ?>" 
											aria-hidden="true"
											tabindex="-1">
											<?php WPF_Template_Tags::the_image( get_post_thumbnail_id() ); ?>
										</a>
									</div>
									<?php
								}
								?>
							</div>
						</div>
						<?php
					}
					wp_reset_postdata();
					?>
				</div>
				<?php
			}
			return ob_get_clean();
		}

		/**
		 * シネコカルチャーの世界へようこそを出力するショートコード。
		 *
		 * @param array $atts ショートコード引数。
		 * @return string
		 */
		public static function dive_into_synecoculture( $atts ) {
			// デフォルト引数と与えられた引数を結合する
			$atts = shortcode_atts(
				array(),
				$atts,
				'wpf_dive_into_synecoculture'
			);

			$heading = SCF::get( '_wpf_top__dive_into_synecoculture__heading' );
			$body    = SCF::get( '_wpf_top__dive_into_synecoculture__body' );

			ob_start();
			if ( ! empty( $heading ) || ! empty( $body ) ) {
				?>
				<div class="dive-into-synecoculture">
					<div class="dive-into-synecoculture__header">
						<div class="dive-into-synecoculture__header__overline syneco-overline">
							<div class="syneco-overline__icon">
								<?php echo WPF_Icons::get_svg( 'ui', 'syneco', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							</div>
							<div class="syneco-overline__text">Dive Into Synecoculture</div>
						</div>

						<div class="dive-into-synecoculture__header__main">
							<?php
							if ( ! empty( $heading ) ) {
								?>
								<h2 class="dive-into-synecoculture__header__heading">
									<?php echo wp_kses_post( $heading ); // 見出しを出力 ?>
								</h2>
								<?php
							}
							?>

							<?php
							if ( ! empty( $body ) ) {
								?>
								<div class="dive-into-synecoculture__header__body prose">
									<?php echo wp_kses_post( $body ); // 本文を出力 ?>
								</div>
								<?php
							}
							?>
						</div>
					</div>

					<?php
					$learns = SCF::get( '_wpf_top__learn' );
					if ( ! empty( $learns ) ) {
						?>
						<div class="dive-into-synecoculture__learns">
							<div class="dive-into-synecoculture__learns__header">
								<div class="dive-into-synecoculture__learns__heading">
									<?php
									$learn_page = get_page_by_path( 'learn' );

									if ( $learn_page ) {
										$learn_page_id = $learn_page->ID;
										if ( function_exists( 'pll_get_post' ) ) {
											$learn_page_id = pll_get_post( $learn_page_id );

											if ( $learn_page->ID !== $learn_page_id ) {
												$learn_page = get_post( $learn_page_id );
											}
										}

										$permalink = get_permalink( $learn_page->ID );
										$title     = $learn_page->post_title;
										?>
										<a href="<?php echo esc_url( $permalink ); ?>" class="dive-into-synecoculture__learns__heading__main">
											<div class="dive-into-synecoculture__learns__heading__main__icon" aria-hidden="true">
												<svg width="64" height="65" viewBox="0 0 64 65" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M42.0298 14.8779C42.0298 9.33876 37.5396 4.84792 32.0005 4.84766C26.4612 4.84766 21.9702 9.3386 21.9702 14.8779C21.9705 20.417 26.4613 24.9072 32.0005 24.9072C37.5394 24.907 42.0295 20.4169 42.0298 14.8779ZM42.8657 14.8779C42.8655 20.8785 38.001 25.7429 32.0005 25.7432C25.9997 25.7432 21.1345 20.8787 21.1343 14.8779C21.1343 8.87699 25.9995 4.01172 32.0005 4.01172C38.0012 4.01198 42.8657 8.87715 42.8657 14.8779Z" fill="var(--color-content-primary)"/>
												<path d="M3.58203 39.5336C3.58218 36.5333 6.0144 34.1011 9.01465 34.101C9.32269 34.101 9.62535 34.1267 9.91992 34.1761L9.78125 35.0004C9.5323 34.9586 9.27604 34.9369 9.01465 34.9369C6.47601 34.937 4.41812 36.9949 4.41797 39.5336C4.41797 42.0723 6.47591 44.1311 9.01465 44.1312C9.27601 44.1312 9.53232 44.1095 9.78125 44.0677L9.91992 44.892C9.62536 44.9414 9.32267 44.9672 9.01465 44.9672C6.0143 44.967 3.58203 42.5339 3.58203 39.5336Z" fill="var(--color-content-primary)"/>
												<path d="M59.5819 39.5336C59.5817 36.9949 57.5239 34.937 54.9852 34.9369C54.7237 34.9369 54.4676 34.9586 54.2186 35.0004L54.0799 34.1761C54.3745 34.1267 54.6772 34.101 54.9852 34.101C57.9855 34.101 60.4177 36.5333 60.4178 39.5336C60.4178 42.534 57.9856 44.9671 54.9852 44.9672C54.6772 44.9672 54.3745 44.9414 54.0799 44.892L54.2186 44.0677C54.4676 44.1095 54.7238 44.1312 54.9852 44.1312C57.524 44.1312 59.5819 42.0724 59.5819 39.5336Z" fill="var(--color-content-primary)"/>
												<path d="M31.9998 29.0859C35.7254 29.0859 39.1936 30.1944 42.0906 32.0996L41.8611 32.4492L41.6317 32.7979C38.8668 30.9796 35.5573 29.9219 31.9998 29.9219C28.4451 29.9219 25.1384 30.9782 22.3748 32.7939L22.1453 32.4443L21.9158 32.0957C24.8115 30.1932 28.2771 29.086 31.9998 29.0859Z" fill="var(--color-content-primary)"/>
												<path d="M54.1493 27.832L32 36.1902V60.429L54.1493 52.0708V27.832Z" fill="url(#paint0_linear_8944_28853)"/>
												<path d="M9.85075 27.832L32 36.1902V60.429L9.85075 52.0708V27.832Z" fill="url(#paint1_linear_8944_28853)"/>
												<defs>
												<linearGradient id="paint0_linear_8944_28853" x1="32" y1="60.429" x2="32" y2="27.832" gradientUnits="userSpaceOnUse">
												<stop stop-color="#FFD034"/>
												<stop offset="1" stop-color="var(--color-background-primary)"/>
												</linearGradient>
												<linearGradient id="paint1_linear_8944_28853" x1="32" y1="60.429" x2="32" y2="27.832" gradientUnits="userSpaceOnUse">
												<stop stop-color="#FFD034"/>
												<stop offset="1" stop-color="var(--color-background-primary)"/>
												</linearGradient>
												</defs>
												</svg>
											</div>
											<div class="dive-into-synecoculture__learns__heading__main__text symbolic-writing">
												<?php echo esc_html( $title ); ?>
											</div>
										</a>
										<?php
									}
									?>
								</div>
							</div>

							<?php
							$counter = 0;
							foreach ( $learns as $learn ) {
								$container_classes = 'dive-into-synecoculture__learn';
								if ( 0 === $counter ) {
									$container_classes = $container_classes . ' dive-into-synecoculture__learn--first';
								}
								?>
								<div class="<?php echo esc_attr( $container_classes ); ?>">
									<div class="dive-into-synecoculture__learn__main">
										<?php
										$heading = $learn['_wpf_top__learn__heading'];
										if ( ! empty( $heading ) ) {
											?>
											<h3 class="dive-into-synecoculture__learn__heading">
												<?php echo wp_kses_post( $heading ); // 見出しを出力 ?>
											</h3>
											<?php
										}
										?>

										<div class="dive-into-synecoculture__learn__content">
											<?php
											$body = $learn['_wpf_top__learn__body'];
											if ( ! empty( $body ) ) {
												?>
												<div class="dive-into-synecoculture__learn__body">
													<?php echo wp_kses_post( $body ); // 本文を出力 ?>
												</div>
												<?php
											}
											?>

											<?php
											$link_text = $learn['_wpf_top__learn__link_text'];
											$link_url  = $learn['_wpf_top__learn__link_url'];
											if ( ! empty( $link_text ) && ! empty( $link_url ) ) {
												?>
												<div class="dive-into-synecoculture__learn__cta">
													<a href="<?php echo esc_url( $link_url ); ?>" class="button:secondary">
														<?php echo esc_html( $link_text ); ?>
													</a>
												</div>
												<?php
											}
											?>
										</div>
									</div>

									<?php
									$thumbnail_id = $learn['_wpf_top__learn__thumbnail'];
									if ( ! empty( $thumbnail_id ) ) {
										$thumbnail = wp_get_attachment_image(
											$thumbnail_id,
											'1536x1536',
											false,
											array()
										);
										?>
										<div class="dive-into-synecoculture__learn__image frame">
											<?php echo wp_kses_post( $thumbnail ); // アイキャッチを出力 ?>
										</div>
										<?php
									}
									?>
								</div>
								<?php
								$counter++;
							}
							?>
						</div>
						<?php
					}
					?>

					<?php
					$joins = SCF::get( '_wpf_top__join' );
					if ( ! empty( $joins ) ) {
						?>
						<div class="dive-into-synecoculture__joins">
							<div class="dive-into-synecoculture__joins__header">
								<div class="dive-into-synecoculture__joins__heading">
									<?php
									$join_page = get_page_by_path( 'join' );

									if ( $join_page ) {
										$join_page_id = $join_page->ID;
										if ( function_exists( 'pll_get_post' ) ) {
											$join_page_id = pll_get_post( $join_page_id );

											if ( $join_page->ID !== $join_page_id ) {
												$join_page = get_post( $join_page_id );
											}
										}

										$permalink = get_permalink( $join_page->ID );
										$title     = $join_page->post_title;
										?>
										<a href="<?php echo esc_url( $permalink ); ?>" class="dive-into-synecoculture__joins__heading__main">
											<div class="dive-into-synecoculture__joins__heading__main__icon" aria-hidden="true">
												<svg width="64" height="65" viewBox="0 0 64 65" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M32.0603 10.1678C29.2559 13.0568 29.2586 17.7057 32.0603 20.5976L44.4713 33.4127C48.6131 29.146 52.7549 24.8792 56.8967 20.6124C59.7011 17.7233 59.7011 13.0717 56.8967 10.1826C54.0922 7.2935 49.5768 7.29343 46.7723 10.1826L44.4857 12.5382L42.1847 10.1678C39.3802 7.2787 34.8647 7.2787 32.0603 10.1678Z" fill="url(#paint0_linear_8944_28876)"/>
												<path d="M24.0586 28.4209C25.0971 28.4209 26.2087 28.9314 27.1357 29.5107C28.0759 30.0984 28.8963 30.8016 29.3711 31.2764H29.3701C30.6574 32.4881 32.6068 34.3452 34.2812 35.9932C35.1201 36.8188 35.8926 37.595 36.4795 38.2109C36.7724 38.5184 37.0222 38.7894 37.2119 39.0088C37.3878 39.2122 37.5488 39.4123 37.624 39.5625C37.8595 40.0334 37.6351 40.4881 37.3984 40.79C37.1504 41.1065 36.7795 41.4067 36.3994 41.6699C35.6315 42.2015 34.6953 42.6694 34.2451 42.8945C33.9653 43.0344 33.5566 43.0779 33.166 43.0879C32.7515 43.0985 32.2708 43.0711 31.792 43.0186C31.3122 42.9659 30.8241 42.8873 30.3945 42.791C29.9734 42.6966 29.578 42.579 29.3008 42.4404L29.248 42.4141L29.2031 42.374L25.1299 38.7529L24.957 38.6152C24.5274 38.2848 23.9133 37.902 23.2969 37.6875C22.5719 37.4352 22.0099 37.465 21.6572 37.8174C21.2338 38.2408 21.1425 38.7314 21.207 39.2051C21.272 39.6812 21.4951 40.12 21.6826 40.3779L29.3711 48.0664C30.3333 49.0286 31.3319 48.9636 31.6299 48.8643L31.6992 48.8418H44.9326C47.0125 48.8418 48.4908 50.5205 49.3604 52.1436C49.8043 52.9723 50.1161 53.8318 50.2881 54.5498C50.374 54.9083 50.4272 55.2414 50.4424 55.5244C50.4564 55.7853 50.4428 56.0791 50.3301 56.3047L50.2051 56.5557H48.5635V55.6484H49.5381C49.5376 55.6248 49.5376 55.5997 49.5361 55.5732C49.5247 55.36 49.4824 55.0828 49.4053 54.7607C49.2511 54.1175 48.9673 53.3316 48.5605 52.5723C47.7284 51.0189 46.4829 49.749 44.9326 49.749H31.835C31.1918 49.9251 29.894 49.8725 28.7295 48.708L21.0156 40.9941L20.9922 40.9717L20.9727 40.9453C20.7097 40.5946 20.3995 40.002 20.3076 39.3281C20.2134 38.6372 20.35 37.8414 21.0156 37.1758C21.752 36.4397 22.7781 36.5455 23.5957 36.8301C24.3291 37.0854 25.0296 37.5258 25.5156 37.9004L25.7109 38.0557L25.7158 38.0605L25.7217 38.0654L29.7529 41.6484C29.9344 41.731 30.2239 41.8226 30.5928 41.9053C30.9857 41.9933 31.4402 42.0677 31.8906 42.1172C32.3419 42.1667 32.7801 42.1899 33.1426 42.1807C33.5285 42.1708 33.7554 42.1246 33.8389 42.083C34.2962 41.8543 35.176 41.4132 35.8828 40.9238C36.2395 40.6768 36.5205 40.4384 36.6836 40.2305C36.7642 40.1276 36.7989 40.0538 36.8105 40.0098C36.8195 39.9757 36.8132 39.9701 36.8125 39.9688C36.8123 39.9683 36.8071 39.9586 36.793 39.9375C36.779 39.9166 36.7598 39.8898 36.7344 39.8564C36.6832 39.7893 36.6135 39.7044 36.5254 39.6025C36.3495 39.399 36.1109 39.1399 35.8223 38.8369C35.2454 38.2315 34.4808 37.4637 33.6445 36.6406C31.9725 34.995 30.0235 33.1364 28.7393 31.9277L28.7295 31.9189C28.2967 31.4862 27.529 30.828 26.6543 30.2812C25.7662 29.7262 24.8352 29.3291 24.0586 29.3291H5.4541V44.3037H10.4453C11.9807 44.3037 15.2702 45.3059 19.4043 49.8994C21.4562 52.1793 22.7866 53.617 23.9365 54.4932C25.0524 55.3434 25.978 55.6484 27.2354 55.6484H48.5625C48.5626 55.6517 48.5635 55.6883 48.5635 56.1016L48.5625 56.5557H27.2354C25.77 56.5557 24.6532 56.1798 23.3867 55.2148C22.1543 54.2758 20.7615 52.7637 18.7295 50.5059C14.6957 46.0241 11.6326 45.2109 10.4453 45.2109H4.5459V28.4209H24.0586Z" fill="var(--color-content-primary)"/>
												<defs>
												<linearGradient id="paint0_linear_8944_28876" x1="44.479" y1="8.00098" x2="44.479" y2="33.4127" gradientUnits="userSpaceOnUse">
												<stop stop-color="var(--color-background-primary)"/>
												<stop offset="1" stop-color="#FF2600"/>
												</linearGradient>
												</defs>
												</svg>
											</div>
											<div class="dive-into-synecoculture__joins__heading__main__text symbolic-writing">
												<?php echo esc_html( $title ); ?>
											</div>
										</a>
										<?php
									}
									?>
								</div>
							</div>

							<div class="dive-into-synecoculture__joins__main">
								<?php
								foreach ( $joins as $join ) {
									$heading  = $join['_wpf_top__join__heading'];
									$link_url = $join['_wpf_top__join__link_url'];
									?>
									<?php
									if ( ! empty( $heading ) && ! empty( $link_url ) ) {
										?>
										<div class="dive-into-synecoculture__join clickable-container" data-clickable-link="<?php echo esc_url( $link_url ); ?>">
											<h3 class="dive-into-synecoculture__join__heading">
												<?php echo wp_kses_post( $heading ); // 見出しを出力 ?>
											</h3>

											<a href="<?php echo esc_url( $link_url ); ?>" class="button:secondary:icon">
												<?php echo WPF_Icons::get_svg( 'ui', 'arrow_right', 24 ); /* phpcs:ignore WordPress.Security.EscapeOutput */ ?>
												<span class="screen-reader-text"><?php echo esc_html_e( 'さらに詳しく', 'wordpressfoundation' ); ?></span>
											</a>
										</div>
										<?php
									}
								}
								?>
							</div>
						</div>
						<?php
					}
					?>
				</div>
				<?php
			}
			return ob_get_clean();
		}

		/**
		 * ブログバナーを出力するショートコード。
		 *
		 * @param array $atts ショートコード引数。
		 * @return string
		 */
		public static function blog_banner( $atts ) {
			// デフォルト引数と与えられた引数を結合する
			$atts = shortcode_atts(
				array(),
				$atts,
				'wpf_blog_banner'
			);

			$blog_logo   = SCF::get( '_wpf_top__blog_banner__syneco_blog_logo' );
			$body        = SCF::get( '_wpf_top__blog_banner__body' );
			$bg_image_id = SCF::get( '_wpf_top__blog_banner__bg_image' );

			$blog_page = get_page_by_path( 'blog' );

			ob_start();
			if ( $blog_page && ! empty( $blog_logo ) && ! empty( $body ) && ! empty( $bg_image_id ) ) {
				$blog_page_id = $blog_page->ID;
				if ( function_exists( 'pll_get_post' ) ) {
					$blog_page_id = pll_get_post( $blog_page_id );

					if ( $blog_page->ID !== $blog_page_id ) {
						$blog_page = get_post( $blog_page_id );
					}
				}
				$permalink = get_permalink( $blog_page->ID );
				?>
				<div class="blog-banner-container">
					<div class="blog-banner clickable-container" data-clickable-link="<?php echo esc_url( $permalink ); ?>">
						<div class="blog-banner__main">
							<div class="blog-banner__overline syneco-overline">
								<div class="syneco-overline__icon">
									<?php echo WPF_Icons::get_svg( 'ui', 'syneco', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								</div>
								<div class="syneco-overline__text">Member's Blog</div>
							</div>

							<?php
							if ( ! empty( $blog_logo ) ) {
								?>
								<div class="blog-banner__blog-logo">
									<?php echo WPF_Template_Tags::kses_post( $blog_logo ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								</div>
								<?php
							}
							?>

							<?php
							if ( ! empty( $body ) ) {
								?>
								<p class="blog-banner__body">
									<?php echo wp_kses_post( $body ); // 本文を出力 ?>
								</p>
								<?php
							}
							?>

							<div class="blog-banner__cta">
								<a href="<?php echo esc_url( $permalink ); ?>" class="button:secondary">View More</a>
							</div>
						</div>

						<?php
						if ( ! empty( $bg_image_id ) ) {
							$bg_image = wp_get_attachment_image(
								$bg_image_id,
								'1536x1536',
								false,
								array()
							);
							if ( ! empty( $bg_image ) ) {
								?>
								<div class="blog-banner__bg-image">
									<?php echo WPF_Template_Tags::kses_post( $bg_image ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								</div>
								<?php
							}
						}
						?>
					</div>
				</div>
				<?php
			}
			return ob_get_clean();
		}

		/**
		 * ニューススライダーを出力するショートコード。
		 *
		 * @param array $atts ショートコード引数。
		 * @return string
		 */
		public static function news_slider( $atts ) {
			// デフォルト引数と与えられた引数を結合する
			$atts = shortcode_atts(
				array(),
				$atts,
				'wpf_news_slider'
			);

			$heading = SCF::get( '_wpf_top__news__heading' );
			$body    = SCF::get( '_wpf_top__news__body' );

			$news_page = get_page_by_path( 'news' );

			ob_start();
			if ( $news_page && ! empty( $heading ) && ! empty( $body ) ) {
				$news_page_id = $news_page->ID;
				if ( function_exists( 'pll_get_post' ) ) {
					$news_page_id = pll_get_post( $news_page_id );

					if ( $news_page->ID !== $news_page_id ) {
						$news_page = get_post( $news_page_id );
					}
				}
				$permalink = get_permalink( $news_page->ID );
				?>
				<div class="news-slider-container">
					<div class="news-slider">
						<div class="news-slider__header-container">
							<div class="news-slider__header">
								<div class="news-slider__header__overline syneco-overline">
									<div class="syneco-overline__icon">
										<?php echo WPF_Icons::get_svg( 'ui', 'syneco', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
									</div>
									<div class="syneco-overline__text">Information</div>
								</div>

								<div class="news-slider__header__main">
									<?php
									if ( ! empty( $heading ) ) {
										?>
										<h2 class="news-slider__header__heading">
											<?php echo wp_kses_post( $heading ); // 見出しを出力 ?>
										</h2>
										<?php
									}
									?>

									<?php
									if ( ! empty( $body ) ) {
										?>
										<div class="news-slider__header__body prose">
											<?php echo wp_kses_post( $body ); // 本文を出力 ?>
										</div>
										<?php
									}
									?>
								</div>
							</div>
						</div>

						<div class="news-slider__main-container">
							<div id="newsSlider" class="news-slider__main swiper">
								<div class="news-slides swiper-wrapper">
									<div class="news-slide swiper-slide">Slide 1</div>
									<div class="news-slide swiper-slide">Slide 2</div>
									<div class="news-slide swiper-slide">Slide 3</div>
									<div class="news-slide swiper-slide">Slide 4</div>
									<div class="news-slide swiper-slide">Slide 5</div>
									<div class="news-slide swiper-slide">Slide 6</div>
									<div class="news-slide swiper-slide">Slide 7</div>
								</div>

								<div class="news-slider__footer">
									<div class="news-slider-nav">
										<div class="news-slider-nav-prev">
											<span class="screen-reader-text"><?php echo esc_html_e( '前へ', 'wordpressfoundation' ); ?></span>
										</div>
										<div class="news-slider-nav-next">
											<span class="screen-reader-text"><?php echo esc_html_e( '次へ', 'wordpressfoundation' ); ?></span>
										</div>
									</div>

									<div class="news-slider-cta">
										<a href="<?php echo esc_url( $permalink ); ?>" class="button:primary">View All</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php
			}
			return ob_get_clean();
		}

		/**
		 * カンマ区切りの文字列から、各項目の先頭・末尾のスペースを除去する。
		 *
		 * 例: apple ,orange, lemon => apple,orange,lemon
		 *
		 * @param string $string 対象文字列。
		 * @return string
		 */
		public static function strip( $string ) {
			return preg_replace( '/\s*,\s*/', ',', esc_html( $string ) );
		}
	}
}
