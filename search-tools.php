<?php
/*
* Plugin Name: Search Tools
* Plugin URI: https://www.wpsearchtools.com/
* Description: Brings a bunch of useful tools to boost the search functionality. Extends default search engine and shows statistics what users look for.
* Version: 1.0.0
* Author: Peter Stehlik
* Author URI: https://www.toptal.com/resume/peter-stehlik
* Text Domain: search-tools
* License: GPLv2 or later
* License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
* Domain Path: /languages
* Requires PHP: 7.3
* Requires at least: 6.2
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Define useful constants
 */
global $wpdb;
define("SEARCH_TOOLS_PLUGIN_DIR_PATH", plugin_dir_path( __FILE__ ));
define("SEARCH_TOOLS_INCLUDES_PATH", SEARCH_TOOLS_PLUGIN_DIR_PATH . 'includes/' );
define("SEARCH_TOOLS_ASSETS_URL", plugin_dir_url( __FILE__ ) . 'assets/' );
define("SEARCH_TOOLS_DB_TABLE", $wpdb->prefix . 'st_search_insights');
define("SEARCH_TOOLS_RESULTS_LIMIT", 100);

/**
 * Check for PHP and WP compatibility
 */
define("SEARCH_TOOLS_MINIMUM_REQUIRED_PHP_VERSION", '7.3');
define("SEARCH_TOOLS_MINIMUM_REQUIRED_WP_VERSION", '6.2');

function search_tools_deactivate_plugin() {
	deactivate_plugins(plugin_basename(__FILE__));
	if (!empty($_GET['activate'])) {
		unset($_GET['activate']);
	}
}

// Check for minimum supported WP version
if (version_compare(get_bloginfo('version'), SEARCH_TOOLS_MINIMUM_REQUIRED_WP_VERSION, '<')) {
	add_action('admin_notices', 'search_tools_wp_version_notice');
	// deactivate the plugin
	add_action('admin_init', 'search_tools_deactivate_plugin');
	return;
}

// Check for minimum supported PHP version
if (version_compare(phpversion(), SEARCH_TOOLS_MINIMUM_REQUIRED_PHP_VERSION, '<')) {
	add_action('admin_notices', 'search_tools_php_version_notice');
	// deactivate the plugin
	add_action('admin_init', 'search_tools_deactivate_plugin');
	return;
}

// Display WP version error notice
function search_tools_wp_version_notice() {
	$notice = str_replace(
		'[link]',
		'<a href="/wp-admin/update-core.php">',
		sprintf(
			// translators: %s is the number of minimum WordPress version that Search Tools requires
			__('Search Tools plugin requires WordPress version %s or newer. Please update your [link]WordPress[/link] installation.', 'search-tools'),
			SEARCH_TOOLS_MINIMUM_REQUIRED_WP_VERSION
		)
	);
	$notice = str_replace('[/link]', '</a>', $notice);
	printf(
		'<div class="error"><p>%1$s</p></div>',
		wp_kses(
		  $notice,
		  [
			'a' => [
			  'href' => true,
			],
		  ]
		)
	);
}

// Display PHP version error notice
function search_tools_php_version_notice() {
	$notice = str_replace(
		'[version]',
		phpversion(),
		sprintf(
			// translators: %s is the number of minimum PHP version that Search Tools requires
			__('Search Tools requires PHP version %s or newer. You are running version [version]. Please upgrade your site.', 'search-tools'),
			SEARCH_TOOLS_MINIMUM_REQUIRED_PHP_VERSION
		)
	);
	printf(
		'<div class="error"><p>%1$s</p></div>',
		wp_kses(
			$notice,
			''
		)
	);
}

/**
 * Activate the plugin.
 */
if( file_exists( SEARCH_TOOLS_INCLUDES_PATH . "/Activate.php" ) ){
	require_once SEARCH_TOOLS_INCLUDES_PATH . '/Activate.php';
	register_activation_hook( __FILE__, ['\SearchTools\Activate', 'init'] );
}

/**
 * Init the plugin.
 */
if ( ! class_exists( 'SETO_SearchTools' ) ) {

	/**
	 * The main SearchTools class
	 */
	class SETO_SearchTools {

		/**
		 * The plugin version number.
		 *
		 * @since   1.0.0
		 * 
		 * @var string
		 */
		public $version = '1.0.0';

		/**
		 * A dummy constructor to ensure SearchTools is only setup once.
		 *
		 * @since   1.0.0
		 *
		 * @return  void
		 */
		public function __construct() {

			load_plugin_textdomain( 'search-tools', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ));

		}

		public function enqueue_assets() {			
			if( $GLOBALS['hook_suffix'] === "index.php" ){
				wp_enqueue_style("search-tools-dashboard-widget", SEARCH_TOOLS_ASSETS_URL . "css/dashboard-widget.css", false, "1.2.0", "all");
				wp_enqueue_script("search-tools-dashboard-widget", SEARCH_TOOLS_ASSETS_URL . "js/dashboard-widget.js", ["jquery"], "1.03", ["in_footer" => true]);
			}

			if( $GLOBALS['hook_suffix'] === "search-tools_page_search-tools-insights" ){
				wp_enqueue_style("search-tools-insights", SEARCH_TOOLS_ASSETS_URL . "css/tools-insights.css", false, "1.2.0", "all");
				wp_enqueue_script("search-tools-apexcharts", SEARCH_TOOLS_ASSETS_URL . "external/apexcharts/apexcharts.js", [], "1.0", ["in_footer" => true]);
				wp_enqueue_script("search-tools-insights", SEARCH_TOOLS_ASSETS_URL . "js/tools-insights.js", [], "1.1.0", ["defer" => true, "in_footer" => true]);
			}
		}

		/**
		 * Completes the setup process on "init" of earlier.
		 *
		 * @since   1.0.0
		 *
		 * @return  void
		 */
		public function init() {
			if( file_exists( SEARCH_TOOLS_INCLUDES_PATH . "Plugin.php" ) ){
				require_once SEARCH_TOOLS_INCLUDES_PATH . 'Plugin.php';
				
				\SearchTools\Plugin::init();
			}

			if( file_exists( SEARCH_TOOLS_INCLUDES_PATH . "Extend.php" ) ){
				require_once SEARCH_TOOLS_INCLUDES_PATH . 'Extend.php';
				
				\SearchTools\Extend::init();
			}
			
			if( file_exists( SEARCH_TOOLS_INCLUDES_PATH . "DashboardWidget.php" ) ){
				require_once SEARCH_TOOLS_INCLUDES_PATH . 'DashboardWidget.php';

				if( is_admin() ){
					\SearchTools\DashboardWidget::init();
				}
			}
			
			if( file_exists( SEARCH_TOOLS_INCLUDES_PATH . "Insights.php" ) ){
				require_once  SEARCH_TOOLS_INCLUDES_PATH . 'Insights.php';

				if( is_admin() ){
					\SearchTools\Insights::init();
				}
			}

			if( file_exists( SEARCH_TOOLS_INCLUDES_PATH . "ExtendOptions.php" ) ){
				require_once SEARCH_TOOLS_INCLUDES_PATH . 'ExtendOptions.php';

				if( is_admin() ){
					\SearchTools\ExtendOptions::init();
				}
			}
		}

	} // class SETO_SearchTools

	/**
	 *
	 * @since   1.0.0
	 *
	 * @return  SETO_SearchTools
	 */
	function seto_search_tools() {
		global $seto_search_tools;

		// Instantiate only once.
		if ( ! isset( $seto_search_tools ) ) {
			$seto_search_tools = new SETO_SearchTools();
			$seto_search_tools->init();
		}
		return $seto_search_tools;
	}

	// Init.
	seto_search_tools();

} // class_exists check