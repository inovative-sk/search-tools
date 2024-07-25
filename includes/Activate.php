<?php

namespace SearchToolsPlugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class SETO_Activate
{
    /**
	 * Static property to hold our singleton instance
     * 
     * @since   1.0.0
     * 
     * @var object
	 *
	 */
    private static $instance = null;

    /**
	 * Constructor
	 *
     * @since   1.0.0
     * 
	 * @return void
	 */
	private function __construct() {
		$this->save_plugin_data();
		$this->create_db_tables();
	}

    /**
	 * If an instance exists, this returns it.  If not, it creates one and
	 * returns it.
	 *
     * @since   1.0.0
     * 
	 * @return SETO_Activate
	 */
	public static function init() {

		if ( !self::$instance ){
			self::$instance = new self;
        }

		return self::$instance;

	}

    /**
     * Create DB table on plugin activation
     *
     * @since   1.0.0
     *
     * @return  void
     */
    private function create_db_tables()
    {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $table_queries = SETO_DB_TABLE;
        
        $sql_create_table_queries = "CREATE TABLE IF NOT EXISTS $table_queries (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            query varchar(55) DEFAULT '' NOT NULL,
            user_roles varchar(150) DEFAULT '' NOT NULL,
            count_total_results mediumint(9) NOT NULL,
            ids_first_top_results text DEFAULT '' NULL,
            created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta( $sql_create_table_queries );

    }

    /**
     * Store information, e.g. 'date of activation'
     *
     * @since   1.0.0
     * 
     * @updated 1.3.0
     *
     * @return  void
     */
    private function save_plugin_data()
    {
        global $seto_db_version;
        $seto_db_version = '1.0';

        add_option( 'seto_db_version', $seto_db_version, '', false );

        add_option('seto_plugin_activation_date', current_time('mysql', false), '', false);
 
        $serialized = 'a:3:{s:22:"field_highlight_enable";s:6:"enable";s:26:"field_highlight_background";s:7:"#000000";s:22:"field_highlight_colour";s:7:"#ffffff";}';

        add_option( 'seto_free_options', unserialize($serialized), '', false );

        // add_option( 'seto_free_options[field_highlight_enable]', 'enable', '', false );
    }

}