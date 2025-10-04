<?php

/**
 * WPF_Template_Tags::get_term_navigation のユニットテスト。
 *
 * @group template_tags
 * @covers WPF_Template_Tags
 * @coversDefaultClass WPF_Template_Tags
 */
class TestGetTermNavigation extends WPF_UnitTestCase {

	/**
	 * @covers ::get_term_navigation
	 * @preserveGlobalState disabled
	 */
	public function test_get_term_navigation() {
		$term  = self::factory()->term->create(
			array(
				'taxonomy' => 'category',
				'name'     => 'Foo',
			)
		);
		$posts = self::factory()->post->create_many( 10 );
		foreach ( $posts as $post ) {
			wp_set_post_terms( $post, array( $term ), 'category' );
		}

		$this->go_to( get_post_type_archive_link( 'post' ) );

		$this->assertQueryTrue( 'is_front_page', 'is_home' );

		$actual = WPF_Template_Tags::get_term_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="カテゴリー".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org"[^>][^>]*aria-current="page"[^>]*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org\/category\/foo\/"[^>]*>\s*Foo\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		$this->go_to( get_category_link( $term ) );

		$this->assertQueryTrue( 'is_archive', 'is_category' );

		$actual = WPF_Template_Tags::get_term_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="カテゴリー".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org"[^>]*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org\/category\/foo\/"[^>][^>]*aria-current="page"[^>]*>\s*Foo\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		$this->go_to( next_posts( 0, false ) );

		$this->assertQueryTrue( 'is_archive', 'is_category', 'is_paged' );

		$actual = WPF_Template_Tags::get_term_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="カテゴリー".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org"[^>]*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org\/category\/foo\/"[^>][^>]*aria-current="page"[^>]*>\s*Foo\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( get_post_type_archive_link( 'post' ) );

		$this->assertQueryTrue( 'is_front_page', 'is_home' );

		$actual = WPF_Template_Tags::get_term_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="カテゴリー".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org"[^>][^>]*aria-current="page"[^>]*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org\/\?cat=' . get_cat_ID( 'foo' ) . '"[^>]*>\s*Foo\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		$this->go_to( get_category_link( $term ) );

		$this->assertQueryTrue( 'is_archive', 'is_category' );

		$actual = WPF_Template_Tags::get_term_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="カテゴリー".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org"[^>]*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org\/\?cat=' . get_cat_ID( 'foo' ) . '"[^>][^>]*aria-current="page"[^>]*>\s*Foo\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		$this->go_to( get_category_link( $term ) . '&paged=2' );

		$this->assertQueryTrue( 'is_archive', 'is_category', 'is_paged' );

		$actual = WPF_Template_Tags::get_term_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="カテゴリー".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org"[^>]*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org\/\?cat=' . get_cat_ID( 'foo' ) . '"[^>][^>]*aria-current="page"[^>]*>\s*Foo\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );
	}

	/**
	 * @covers ::get_term_navigation
	 * @preserveGlobalState disabled
	 */
	public function test_get_term_navigation_with_ctax() {
		register_taxonomy(
			'ctax',
			'post',
			array(
				'public' => true,
			)
		);

		$term  = self::factory()->term->create(
			array(
				'taxonomy' => 'ctax',
				'name'     => 'Foo',
			)
		);
		$posts = self::factory()->post->create_many( 10 );
		foreach ( $posts as $post ) {
			wp_set_post_terms( $post, array( $term ), 'ctax' );
		}

		$this->go_to( get_post_type_archive_link( 'post' ) );

		$this->assertQueryTrue( 'is_front_page', 'is_home' );

		$args   = 'ctax';
		$actual = WPF_Template_Tags::get_term_navigation( $args );

		$this->assertMatchesRegularExpression( '/<nav aria-label="カテゴリー".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org"[^>][^>]*aria-current="page"[^>]*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org\/ctax\/foo\/"[^>]*>\s*Foo\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		$this->go_to( get_term_link( $term, 'ctax' ) );

		$this->assertQueryTrue( 'is_archive', 'is_tax' );

		$actual = WPF_Template_Tags::get_term_navigation( $args );

		$this->assertMatchesRegularExpression( '/<nav aria-label="カテゴリー".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org"[^>]*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org\/ctax\/foo\/"[^>][^>]*aria-current="page"[^>]*>\s*Foo\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		$this->go_to( next_posts( 0, false ) );

		$this->assertQueryTrue( 'is_archive', 'is_tax', 'is_paged' );

		$actual = WPF_Template_Tags::get_term_navigation( $args );

		$this->assertMatchesRegularExpression( '/<nav aria-label="カテゴリー".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org"[^>]*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org\/ctax\/foo\/"[^>][^>]*aria-current="page"[^>]*>\s*Foo\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( get_post_type_archive_link( 'post' ) );

		$this->assertQueryTrue( 'is_front_page', 'is_home' );

		$actual = WPF_Template_Tags::get_term_navigation( $args );

		$this->assertMatchesRegularExpression( '/<nav aria-label="カテゴリー".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org"[^>][^>]*aria-current="page"[^>]*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org\/\?ctax=foo"[^>]*>\s*Foo\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		$this->go_to( get_term_link( $term, 'ctax' ) );

		$this->assertQueryTrue( 'is_archive', 'is_tax' );

		$actual = WPF_Template_Tags::get_term_navigation( $args );

		$this->assertMatchesRegularExpression( '/<nav aria-label="カテゴリー".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org"[^>]*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org\/\?ctax=foo"[^>][^>]*aria-current="page"[^>]*>\s*Foo\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		$this->go_to( get_term_link( $term, 'ctax' ) . '&paged=2' );

		$this->assertQueryTrue( 'is_archive', 'is_tax', 'is_paged' );

		$actual = WPF_Template_Tags::get_term_navigation( $args );

		$this->assertMatchesRegularExpression( '/<nav aria-label="カテゴリー".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org"[^>]*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org\/\?ctax=foo"[^>][^>]*aria-current="page"[^>]*>\s*Foo\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		_unregister_taxonomy( 'ctax' );
	}

	/**
	 * @covers ::get_term_navigation
	 * @preserveGlobalState disabled
	 */
	public function test_get_term_navigation_with_cpt_ctax() {
		register_taxonomy(
			'ctax',
			'cpt',
			array(
				'public' => true,
			)
		);
		register_post_type(
			'cpt',
			array(
				'public'      => true,
				'has_archive' => true,
			)
		);

		$term  = self::factory()->term->create(
			array(
				'taxonomy' => 'ctax',
				'name'     => 'Foo',
			)
		);
		$posts = self::factory()->post->create_many( 10, array( 'post_type' => 'cpt' ) );
		foreach ( $posts as $post ) {
			wp_set_post_terms( $post, array( $term ), 'ctax' );
		}

		$this->go_to( get_post_type_archive_link( 'cpt' ) );

		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive' );

		$actual = WPF_Template_Tags::get_term_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="カテゴリー".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org\/cpt\/"[^>][^>]*aria-current="page"[^>]*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org\/ctax\/foo\/"[^>]*>\s*Foo\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		$this->go_to( get_term_link( $term, 'ctax' ) );

		$this->assertQueryTrue( 'is_archive', 'is_tax' );

		$actual = WPF_Template_Tags::get_term_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="カテゴリー".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org\/cpt\/"[^>]*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org\/ctax\/foo\/"[^>][^>]*aria-current="page"[^>]*>\s*Foo\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		$this->go_to( next_posts( 0, false ) );

		$this->assertQueryTrue( 'is_archive', 'is_tax', 'is_paged' );

		$actual = WPF_Template_Tags::get_term_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="カテゴリー".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org\/cpt\/"[^>]*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org\/ctax\/foo\/"[^>][^>]*aria-current="page"[^>]*>\s*Foo\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '' );

		$this->go_to( get_post_type_archive_link( 'cpt' ) );

		$this->assertQueryTrue( 'is_archive', 'is_post_type_archive' );

		$actual = WPF_Template_Tags::get_term_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="カテゴリー".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org\/\?post_type=cpt"[^>][^>]*aria-current="page"[^>]*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org\/\?ctax=foo"[^>]*>\s*Foo\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		$this->go_to( get_term_link( $term, 'ctax' ) );

		$this->assertQueryTrue( 'is_archive', 'is_tax' );

		$actual = WPF_Template_Tags::get_term_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="カテゴリー".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org\/\?post_type=cpt"[^>]*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org\/\?ctax=foo"[^>][^>]*aria-current="page"[^>]*>\s*Foo\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		$this->go_to( get_term_link( $term, 'ctax' ) . '&paged=2' );

		$this->assertQueryTrue( 'is_archive', 'is_tax', 'is_paged' );

		$actual = WPF_Template_Tags::get_term_navigation();

		$this->assertMatchesRegularExpression( '/<nav aria-label="カテゴリー".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org\/\?post_type=cpt"[^>]*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org\/\?ctax=foo"[^>][^>]*aria-current="page"[^>]*>\s*Foo\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		_unregister_taxonomy( 'ctax' );
	}

	/**
	 * @covers ::get_term_navigation
	 * @preserveGlobalState disabled
	 */
	public function test_get_term_navigation_with_no_ctax() {
		$this->go_to( home_url() );

		$actual = WPF_Template_Tags::get_term_navigation( 'noctax' );

		$this->assertSame( '', $actual );
	}

	/**
	 * @covers ::get_term_navigation
	 * @preserveGlobalState disabled
	 */
	public function test_get_term_navigation_with_args_patterns() {
		$cat = self::factory()->term->create(
			array(
				'taxonomy' => 'category',
				'name'     => 'Foo',
			)
		);

		$tag = self::factory()->term->create(
			array(
				'taxonomy' => 'post_tag',
				'name'     => 'Bar',
			)
		);

		$posts = self::factory()->post->create_many( 10 );
		foreach ( $posts as $post ) {
			wp_set_post_terms( $post, array( $cat ), 'category' );
			wp_set_post_terms( $post, array( $tag ), 'post_tag' );
		}

		$this->go_to( get_post_type_archive_link( 'post' ) );

		$this->assertQueryTrue( 'is_front_page', 'is_home' );

		$args   = 'category';
		$actual = WPF_Template_Tags::get_term_navigation( $args );

		$this->assertMatchesRegularExpression( '/<nav aria-label="カテゴリー".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org"[^>][^>]*aria-current="page"[^>]*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org\/category\/foo\/"[^>]*>\s*Foo\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		$args   = array(
			'taxonomy' => 'category',
		);
		$actual = WPF_Template_Tags::get_term_navigation( $args );

		$this->assertMatchesRegularExpression( '/<nav aria-label="カテゴリー".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org"[^>][^>]*aria-current="page"[^>]*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org\/category\/foo\/"[^>]*>\s*Foo\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		$args   = array(
			'taxonomy' => array( 'category' ),
		);
		$actual = WPF_Template_Tags::get_term_navigation( $args );

		$this->assertMatchesRegularExpression( '/<nav aria-label="カテゴリー".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org"[^>][^>]*aria-current="page"[^>]*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org\/category\/foo\/"[^>]*>\s*Foo\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		$args   = array(
			'taxonomy' => array( 'category', 'post_tag' ),
		);
		$actual = WPF_Template_Tags::get_term_navigation( $args );

		$this->assertMatchesRegularExpression( '/<nav aria-label="カテゴリー".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org"[^>][^>]*aria-current="page"[^>]*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org\/category\/foo\/"[^>]*>\s*Foo\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );

		$args   = array(
			'taxonomy' => 'category',
			'exclude'  => array( $cat ),
		);
		$actual = WPF_Template_Tags::get_term_navigation( $args );

		$this->assertMatchesRegularExpression( '/<nav aria-label="カテゴリー".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org"[^>][^>]*aria-current="page"[^>]*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );
	}

	/**
	 * @covers ::get_term_navigation
	 * @preserveGlobalState disabled
	 */
	public function test_get_term_navigation_with_change_label() {
		$term  = self::factory()->term->create(
			array(
				'taxonomy' => 'category',
				'name'     => 'Foo',
			)
		);
		$posts = self::factory()->post->create_many( 10 );
		foreach ( $posts as $post ) {
			wp_set_post_terms( $post, array( $term ), 'category' );
		}

		$this->go_to( get_post_type_archive_link( 'post' ) );

		$this->assertQueryTrue( 'is_front_page', 'is_home' );

		$actual = WPF_Template_Tags::get_term_navigation( '', 'Custom label' );

		$this->assertMatchesRegularExpression( '/<nav aria-label="Custom label".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org"[^>][^>]*aria-current="page"[^>]*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org\/category\/foo\/"[^>]*>\s*Foo\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );
	}

	/**
	 * @covers ::get_term_navigation
	 * @preserveGlobalState disabled
	 */
	public function test_get_term_navigation_with_hide_top_link() {
		$term  = self::factory()->term->create(
			array(
				'taxonomy' => 'category',
				'name'     => 'Foo',
			)
		);
		$posts = self::factory()->post->create_many( 10 );
		foreach ( $posts as $post ) {
			wp_set_post_terms( $post, array( $term ), 'category' );
		}

		$this->go_to( get_post_type_archive_link( 'post' ) );

		$this->assertQueryTrue( 'is_front_page', 'is_home' );

		$actual = WPF_Template_Tags::get_term_navigation( '', '', false );

		$this->assertMatchesRegularExpression( '/<nav aria-label="カテゴリー".*>/', $actual );
		$this->assertMatchesRegularExpression( '/<ul/', $actual );
		$this->assertMatchesRegularExpression( '/<li/', $actual );
		$this->assertDoesNotMatchRegularExpression( '/<a href="http:\/\/example.org".*>\s*すべて\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<a [^>]*href="http:\/\/example.org\/category\/foo\/"[^>]*>\s*Foo\s*<\/a>/', $actual );
		$this->assertMatchesRegularExpression( '/<\/li/', $actual );
		$this->assertMatchesRegularExpression( '/<\/ul/', $actual );
	}
}
