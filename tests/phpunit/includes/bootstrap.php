<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package wordpressfoundation
 */

use Yoast\WPTestUtils\WPIntegration;

require_once 'vendor/yoast/wp-test-utils/src/WPIntegration/bootstrap-functions.php';

$_tests_dir = WPIntegration\get_path_to_wp_test_dir();

// Get access to tests_add_filter() function.
require_once $_tests_dir . 'includes/functions.php';

$argv = $_SERVER['argv']; // phpcs:ignore

/**
 * Load theme and plugins.
 */
tests_add_filter(
	'muplugins_loaded',
	function() use ( $argv ) {
		$theme_dir     = dirname( __DIR__ );
		$current_theme = basename( $theme_dir );
		$theme_root    = dirname( $theme_dir );

		add_filter(
			'theme_root',
			function () use ( $theme_root ) {
				return $theme_root;
			}
		);

		register_theme_directory( $theme_root );

		add_filter(
			'pre_option_template',
			function () use ( $current_theme ) {
				return $current_theme;
			}
		);

		add_filter(
			'pre_option_stylesheet',
			function () use ( $current_theme ) {
				return $current_theme;
			}
		);

		if ( file_exists( ABSPATH . '/wp-content/plugins/rewrite-rules-inspector/rewrite-rules-inspector.php' ) ) {
			require_once ABSPATH . '/wp-content/plugins/rewrite-rules-inspector/rewrite-rules-inspector.php';
		}

		while ( current( $argv ) ) {
			$option = current( $argv );
			$value  = next( $argv );

			switch ( $option ) {
				case '--testsuite':
					$testsuites = explode( ',', $value );
					foreach ( $testsuites as $testsuite ) {
						if ( 'polylang' === $testsuite ) {
							if ( file_exists( ABSPATH . '/wp-content/plugins/polylang/polylang.php' ) ) {
								require_once ABSPATH . '/wp-content/plugins/polylang/polylang.php';
								require_once ABSPATH . '/wp-content/plugins/polylang/include/api.php';
							} else {
								print( 'NOTICE: Polylang plugin does not exist. Run \'bash tests/bin/install-plugins.sh\'' . PHP_EOL );
							}
						}
					}
					continue 2;
			}
		}
	}
);

/*
 * Bootstrap WordPress. This will also load the Composer autoload file, the PHPUnit Polyfills
 * and the custom autoloader for the TestCase and the mock object classes.
 */
WPIntegration\bootstrap_it();

require_once 'tests/phpunit/includes/class-wpf-test-utils-trait.php';
require_once 'tests/phpunit/includes/class-wpf-cptp-trait.php';
require_once 'tests/phpunit/includes/class-wpf-testcase.php';

while ( current( $argv ) ) {
	$option = current( $argv );
	$value  = next( $argv );

	switch ( $option ) {
		case '--testsuite':
			$testsuites = explode( ',', $value );
			foreach ( $testsuites as $testsuite ) {
				if ( 'polylang' === $testsuite ) {
					require_once 'tests/phpunit/includes/integrations/polylang/class-wpf-doing-it-wrong-trait.php';
					require_once 'tests/phpunit/includes/integrations/polylang/class-wpf-testcase-polylang-trait.php';
					require_once 'tests/phpunit/includes/integrations/polylang/class-wpf-testcase-polylang.php';
				}
			}
			continue 2;
	}
}

printf(
	'Testing WordPress Foundation theme with WordPress %1$s...' . PHP_EOL,
	$GLOBALS['wp_version'] // phpcs:ignore WordPress.Security.EscapeOutput
);
