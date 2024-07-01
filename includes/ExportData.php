<?php
namespace SearchToolsPlugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class SETO_ExportData
{
    /**
	 * Static property to hold our singleton instance
     * 
     * @since   1.2.0
     * 
     * @var object
	 *
	 */
    private static $instance = null;

    /**
	 * Constructor
	 *
     * @since   1.2.0
     * 
	 * @return void
	 */
	private function __construct() {
        add_action( 'admin_enqueue_scripts', [$this, 'enqueue_assets'], 50, 0 );
	}

    /**
     * Append necessary assets, JS & CSS files
     *
     * @since   1.2.0
     *
     * @return  void
     */
    public function enqueue_assets() {
        $path = plugin_dir_url( __DIR__ );

        if( $GLOBALS['hook_suffix'] === "search-tools_page_search-tools-insights" ){
            wp_enqueue_script("file-saver-js", $path . "assets/external/tableExport/libs/FileSaver/FileSaver.min.js", ["jquery"], "1.30.0", ["in_footer" => true]);
            wp_enqueue_script("table-export-js", $path . "assets/external/tableExport/tableExport.min.js", ["jquery"], "1.30.0", ["in_footer" => true]);
            wp_enqueue_script("insights-export-js", $path . "assets/js/insights-export.js", ["jquery"], "1.0.4", ["in_footer" => true]);
        }
    }

    /**
	 * If an instance exists, this returns it.  If not, it creates one and
	 * returns it.
	 *
     * @since   1.2.0
     * 
	 * @return SETO_ExportData
	 */
	public static function init() {

		if ( !self::$instance ){
			self::$instance = new self;
        }

		return self::$instance;

	}
}