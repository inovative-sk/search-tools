<?php
/*
* Plugin Name: Search Tools
* Plugin URI: https://www.wpsearchtools.com/
* Description: Highlights search term (customizable colours), collects data (privacy friendly analytics), extends default engine. New 2024 WP plugin.
* Version: 1.3.1
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
define("SETO_PLUGIN_DIR_PATH", plugin_dir_path( __FILE__ ));
define("SETO_INCLUDES_PATH", SETO_PLUGIN_DIR_PATH . 'includes/' );
define("SETO_ASSETS_URL", plugin_dir_url( __FILE__ ) . 'assets/' );
define("SETO_DB_TABLE", $wpdb->prefix . 'seto_search_insights');
define("SETO_RESULTS_LIMIT", 100);

/**
 * Check for PHP and WP compatibility
 */
define("SETO_MINIMUM_REQUIRED_PHP_VERSION", '7.3');
define("SETO_MINIMUM_REQUIRED_WP_VERSION", '6.2');

function seto_deactivate_plugin() {
	deactivate_plugins(plugin_basename(__FILE__));
	if (!empty($_GET['activate'])) {
		unset($_GET['activate']);
	}
}

// Check for minimum supported WP version
if (version_compare(get_bloginfo('version'), SETO_MINIMUM_REQUIRED_WP_VERSION, '<')) {
	add_action('admin_notices', 'seto_wp_version_notice');
	// deactivate the plugin
	add_action('admin_init', 'seto_deactivate_plugin');
	return;
}

// Check for minimum supported PHP version
if (version_compare(phpversion(), SETO_MINIMUM_REQUIRED_PHP_VERSION, '<')) {
	add_action('admin_notices', 'seto_php_version_notice');
	// deactivate the plugin
	add_action('admin_init', 'seto_deactivate_plugin');
	return;
}

// Display WP version error notice
function seto_wp_version_notice() {
	$notice = str_replace(
		'[link]',
		'<a href="/wp-admin/update-core.php">',
		sprintf(
			// translators: %s is the number of minimum WordPress version that Search Tools requires
			__('Search Tools plugin requires WordPress version %s or newer. Please update your [link]WordPress[/link] installation.', 'search-tools'),
			SETO_MINIMUM_REQUIRED_WP_VERSION
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
function seto_php_version_notice() {
	$notice = str_replace(
		'[version]',
		phpversion(),
		sprintf(
			// translators: %s is the number of minimum PHP version that Search Tools requires
			__('Search Tools requires PHP version %s or newer. You are running version [version]. Please upgrade your site.', 'search-tools'),
			SETO_MINIMUM_REQUIRED_PHP_VERSION
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
 * 
 * @since   1.0.0
 * 
 */
if( file_exists( SETO_INCLUDES_PATH . "/Activate.php" ) ){
	require_once SETO_INCLUDES_PATH . '/Activate.php';
	register_activation_hook( __FILE__, ['\SearchToolsPlugin\SETO_Activate', 'init'] );
}

/**
 * Add plugin menu items.
 * 
 * @since   1.3.1
 * 
 */
add_filter( 'plugin_action_links_search-tools/search-tools.php', 'seto_settings_link' );
function seto_settings_link( $links ) {
	// Build and escape the URL.
	$settings_url = esc_url( add_query_arg(
		'page',
		'wp-search-tools',
		get_admin_url() . 'admin.php'
	) );
	// Create the link.
	$settings_link = "<a href='$settings_url'>" . __( 'Settings', 'search-tools' ) . '</a>';
	$go_pro_link = "<a href='https://www.wpsearchtools.com/pro-features/' target='_blank'>" . __( 'Premium', 'search-tools' ) . '</a>';
	// Adds the link to the end of the array.
	array_push(
		$links,
		$settings_link,
		$go_pro_link
	);
	return $links;
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
		public $version = '1.3.1';

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

		/**
		 * Enqueue JS & CSS assets only on admin pages where necessary
		 *
		 * @since   1.0.0
		 *
		 * @return  void
		 */
		public function enqueue_assets() {			
			if( $GLOBALS['hook_suffix'] === "index.php" ){
				wp_enqueue_style("search-tools-dashboard-widget", SETO_ASSETS_URL . "css/dashboard-widget.css", false, "1.0.1", "all");
				wp_enqueue_script("search-tools-dashboard-widget", SETO_ASSETS_URL . "js/dashboard-widget.js", ["jquery"], "1.0.1", ["in_footer" => true]);
			}

			if( $GLOBALS['hook_suffix'] === "search-tools_page_search-tools-insights" ){
				wp_enqueue_style("search-tools-insights", SETO_ASSETS_URL . "css/tools-insights.css", false, "1.0.5", "all");
				wp_enqueue_script("search-tools-apexcharts", SETO_ASSETS_URL . "external/apexcharts/apexcharts.min.js", [], "3.48.0", ["in_footer" => true]);
				wp_enqueue_script("search-tools-insights", SETO_ASSETS_URL . "js/tools-insights.js", [], "1.0.1", ["defer" => true, "in_footer" => true]);
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
			if( file_exists( SETO_INCLUDES_PATH . "Plugin.php" ) ){
				require_once SETO_INCLUDES_PATH . 'Plugin.php';
				
				\SearchToolsPlugin\SETO_Plugin::init();
			}

			if( file_exists( SETO_INCLUDES_PATH . "Extend.php" ) ){
				require_once SETO_INCLUDES_PATH . 'Extend.php';
				
				\SearchToolsPlugin\SETO_Extend::init();
			}

			if( file_exists( SETO_INCLUDES_PATH . "Highlight.php" ) ){
				require_once SETO_INCLUDES_PATH . 'Highlight.php';
				
				\SearchToolsPlugin\SETO_Highlight::init();
			}

			if( file_exists( SETO_INCLUDES_PATH . "Disable.php" ) ){
				require_once SETO_INCLUDES_PATH . 'Disable.php';
				
				$options = get_option( 'seto_free_options' );

				if( isset( $options["field_disable_search"] ) && $options["field_disable_search"] === 'disable' ){
					add_action( 'plugins_loaded', [ '\SearchToolsPlugin\SETO_Disable', 'disable_search' ] );
				}
			}
			
			if( file_exists( SETO_INCLUDES_PATH . "OptionsPage.php" ) ){
				require_once SETO_INCLUDES_PATH . 'OptionsPage.php';

				if( is_admin() ){
					\SearchToolsPlugin\SETO_OptionsPage::init();
				}
			}

			if( file_exists( SETO_INCLUDES_PATH . "DashboardWidget.php" ) ){
				require_once SETO_INCLUDES_PATH . 'DashboardWidget.php';

				if( is_admin() ){
					\SearchToolsPlugin\SETO_DashboardWidget::init();
				}
			}
			
			if( file_exists( SETO_INCLUDES_PATH . "Insights.php" ) ){
				require_once  SETO_INCLUDES_PATH . 'Insights.php';

				if( is_admin() ){
					\SearchToolsPlugin\SETO_Insights::init();
				}
			}

			if( file_exists( SETO_INCLUDES_PATH . "ExtendOptions.php" ) ){
				require_once SETO_INCLUDES_PATH . 'ExtendOptions.php';

				if( is_admin() ){
					\SearchToolsPlugin\SETO_ExtendOptions::init();
				}
			}

			if( file_exists( SETO_INCLUDES_PATH . "ExportData.php" ) ){
				require_once SETO_INCLUDES_PATH . 'ExportData.php';

				if( is_admin() ){
					\SearchToolsPlugin\SETO_ExportData::init();
				}
			}

			if( file_exists( SETO_INCLUDES_PATH . "AddReview.php" ) ){
				require_once SETO_INCLUDES_PATH . 'AddReview.php';

				if( is_admin() ){
					\SearchToolsPlugin\SETO_AddReview::init();
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