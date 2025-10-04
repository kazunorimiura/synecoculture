<?php
/**
 * Walker_Pageを拡張して、sub-menu-wrapper要素やアクセシビリティ関連の要素を追加
 *
 * アクセシビリティに関わるコード以外は公式のコードをそのまま利用している。
 * 参照: https://developer.wordpress.org/reference/classes/walker_nav_menu/start_el/
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

if ( ! class_exists( 'WPF_Walker_Page' ) ) {
	/**
	 * Core walker class used to create an HTML list of pages.
	 *
	 * @since 2.1.0
	 *
	 * @see Walker
	 */
	class WPF_Walker_Page extends Walker_Page {
		/**
		 * ページタイトルを保存する。
		 *
		 * @var string
		 */
		private $page_title;

		/**
		 * Outputs the beginning of the current level in the tree before elements are output.
		 *
		 * @since 2.1.0
		 *
		 * @see Walker::start_lvl()
		 *
		 * @param string $output Used to append additional content (passed by reference).
		 * @param int    $depth  Optional. Depth of page. Used for padding. Default 0.
		 * @param array  $args   Optional. Arguments for outputting the next level.
		 *                       Default empty array.
		 */
		public function start_lvl( &$output, $depth = 0, $args = array() ) {
			if ( isset( $args['item_spacing'] ) && 'preserve' === $args['item_spacing'] ) {
				$t = "\t";
				$n = "\n";
			} else {
				$t = '';
				$n = '';
			}
			$indent = str_repeat( $t, $depth );

			/* translators: ページタイトル */
			$screen_reader_open_text = sprintf( __( '%sのサブメニューを開く', 'wordpressfoundation' ), $this->page_title );
			/* translators: ページタイトル */
			$screen_reader_close_text = sprintf( __( '%sのサブメニューを閉じる', 'wordpressfoundation' ), $this->page_title );

			$toggle  = '<button class="menu-item-toggle" aria-expanded="false"><span class="screen-reader-text" data-open-text="' . $screen_reader_open_text . '" data-close-text="' . $screen_reader_close_text . '">' . $screen_reader_open_text . '</span>' . WPF_Icons::get_svg( 'ui', 'angle_down', 24 ) . '</button>';
			$output .= "{$n}{$indent}{$toggle}<div class='sub-menu-wrapper'><ul class='children'>{$n}";
		}

		/**
		 * Outputs the end of the current level in the tree after elements are output.
		 *
		 * @since 2.1.0
		 *
		 * @see Walker::end_lvl()
		 *
		 * @param string $output Used to append additional content (passed by reference).
		 * @param int    $depth  Optional. Depth of page. Used for padding. Default 0.
		 * @param array  $args   Optional. Arguments for outputting the end of the current level.
		 *                       Default empty array.
		 */
		public function end_lvl( &$output, $depth = 0, $args = array() ) {
			if ( isset( $args['item_spacing'] ) && 'preserve' === $args['item_spacing'] ) {
				$t = "\t";
				$n = "\n";
			} else {
				$t = '';
				$n = '';
			}
			$indent  = str_repeat( $t, $depth );
			$output .= "{$indent}</ul></div>{$n}";
		}

		/**
		 * Outputs the beginning of the current element in the tree.
		 *
		 * @see Walker::start_el()
		 * @since 2.1.0
		 * @since 5.9.0 Renamed `$page` to `$data_object` and `$current_page` to `$current_object_id`
		 *              to match parent class for PHP 8 named parameter support.
		 *
		 * @param string  $output            Used to append additional content. Passed by reference.
		 * @param WP_Post $data_object       Page data object.
		 * @param int     $depth             Optional. Depth of page. Used for padding. Default 0.
		 * @param array   $args              Optional. Array of arguments. Default empty array.
		 * @param int     $current_object_id Optional. ID of the current page. Default 0.
		 */
		public function start_el( &$output, $data_object, $depth = 0, $args = array(), $current_object_id = 0 ) {
			// Restores the more descriptive, specific name for use within this method.
			$page            = $data_object;
			$current_page_id = $current_object_id;

			// ページタイトルを取得する。
			$this->page_title = $page->post_title;

			if ( isset( $args['item_spacing'] ) && 'preserve' === $args['item_spacing'] ) {
				$t = "\t";
				$n = "\n";
			} else {
				$t = '';
				$n = '';
			}
			if ( $depth ) {
				$indent = str_repeat( $t, $depth );
			} else {
				$indent = '';
			}

			$css_class = array( 'page_item', 'page-item-' . $page->ID );

			if ( isset( $args['pages_with_children'][ $page->ID ] ) ) {
				$css_class[] = 'page_item_has_children';
			}

			if ( ! empty( $current_page_id ) ) {
				$_current_page = get_post( $current_page_id );

				if ( $_current_page && in_array( $page->ID, $_current_page->ancestors, true ) ) {
					$css_class[] = 'current_page_ancestor';
				}

				if ( $page->ID === $current_page_id ) {
					$css_class[] = 'current_page_item';
				} elseif ( $_current_page && $page->ID === $_current_page->post_parent ) {
					$css_class[] = 'current_page_parent';
				}
			} elseif ( get_option( 'page_for_posts' ) === $page->ID ) {
				$css_class[] = 'current_page_parent';
			}

			/**
			 * Filters the list of CSS classes to include with each page item in the list.
			 *
			 * @since 2.8.0
			 *
			 * @see wp_list_pages()
			 *
			 * @param string[] $css_class       An array of CSS classes to be applied to each list item.
			 * @param WP_Post  $page            Page data object.
			 * @param int      $depth           Depth of page, used for padding.
			 * @param array    $args            An array of arguments.
			 * @param int      $current_page_id ID of the current page.
			 */
			$css_classes = implode( ' ', apply_filters( 'page_css_class', $css_class, $page, $depth, $args, $current_page_id ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- For core filter
			$css_classes = $css_classes ? ' class="' . esc_attr( $css_classes ) . '"' : '';

			if ( '' === $page->post_title ) {
				/* translators: %d: ID of a post. */
				$page->post_title = sprintf( __( '#%d (no title)', 'wordpressfoundation' ), $page->ID );
			}

			$args['link_before'] = empty( $args['link_before'] ) ? '' : $args['link_before'];
			$args['link_after']  = empty( $args['link_after'] ) ? '' : $args['link_after'];

			$atts                 = array();
			$atts['href']         = get_permalink( $page->ID );
			$atts['aria-current'] = ( $page->ID === $current_page_id ) ? 'page' : '';

			/**
			 * Filters the HTML attributes applied to a page menu item's anchor element.
			 *
			 * @since 4.8.0
			 *
			 * @param array $atts {
			 *     The HTML attributes applied to the menu item's `<a>` element, empty strings are ignored.
			 *
			 *     @type string $href         The href attribute.
			 *     @type string $aria-current The aria-current attribute.
			 * }
			 * @param WP_Post $page            Page data object.
			 * @param int     $depth           Depth of page, used for padding.
			 * @param array   $args            An array of arguments.
			 * @param int     $current_page_id ID of the current page.
			 */
			$atts = apply_filters( 'page_menu_link_attributes', $atts, $page, $depth, $args, $current_page_id ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- For core filter

			$attributes = '';
			foreach ( $atts as $attr => $value ) {
				if ( is_scalar( $value ) && '' !== $value && false !== $value ) {
					$value       = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
					$attributes .= ' ' . $attr . '="' . $value . '"';
				}
			}

			$output .= $indent . sprintf(
				'<li%s><a%s>%s%s%s</a>',
				$css_classes,
				$attributes,
				$args['link_before'],
				/** This filter is documented in wp-includes/post-template.php */
				apply_filters( 'the_title', $page->post_title, $page->ID ), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- For core filter
				$args['link_after']
			);

			if ( ! empty( $args['show_date'] ) ) {
				if ( 'modified' === $args['show_date'] ) {
					$time = $page->post_modified;
				} else {
					$time = $page->post_date;
				}

				$date_format = empty( $args['date_format'] ) ? '' : $args['date_format'];
				$output     .= ' ' . mysql2date( $date_format, $time );
			}
		}
	}
}
