<?php
namespace SearchToolsPlugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class SETO_Plugin
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
        add_action( 'template_redirect', array( $this, 'save_search_query' ), 20, 0 );
	}

    /**
	 * If an instance exists, this returns it.  If not, it creates one and
	 * returns it.
	 *
     * @since   1.0.0
     * 
	 * @return SETO_Plugin
	 */
	public static function init() {

		if ( !self::$instance ){
			self::$instance = new self;
        }

		return self::$instance;

	}

    /**
     * Save searched query with the results
     *
     * @since   1.0.0
     *
     * @return  void
     */
    public function save_search_query() {
        if( !is_search() || !is_main_query() ){
            return false;
        }

        if( !get_search_query() || empty( get_search_query() ) ){
            return false;
        }

        $query = get_search_query();

        $user_roles = "GUEST";

        if( is_user_logged_in() ){
            $user = wp_get_current_user();
            $user_roles = implode(",", $user->roles);
        }

        $ids_arr = [];
        $ids = "";

        global $wp_the_query;
        global $wp_query;
        
        foreach( $wp_the_query->posts as $item ){
            if( isset( $item->ID ) ){
                array_push($ids_arr, $item->ID);
            }
        }
        
        if( count($ids_arr) ){
            $ids = implode(',', $ids_arr);
        }

        global $wpdb;

        $wpdb->insert(
            SETO_DB_TABLE,
            [
                'query' => $query,
                'user_roles' => $user_roles,
                'created_at' => current_time('mysql', false),
                'ids_first_top_results' => $ids,
                'count_total_results' => $wp_query->found_posts,
            ],
            ["%s", "%s", "%s", "%s", "%d"]
        );
    }

}