<?php

namespace SearchToolsPlugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class SETO_DashboardWidget
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
	 * Define how many results it is taken from db
     * 
     * @since   1.0.0
     * 
     * @var integer
	 *
	 */
    private $results_limit = 10;

    /**
	 * Constructor
	 *
     * @since   1.0.0
     * 
	 * @return void
	 */
	private function __construct() {
		add_action( 'wp_dashboard_setup', array( $this, 'register_dashboard_widget' ) );
	}

    /**
	 * If an instance exists, this returns it.  If not, it creates one and
	 * returns it.
	 *
	 * @since   1.0.0
     * 
	 * @return SETO_DashboardWidget
	 */
	public static function init() {

		if ( !self::$instance ){
			self::$instance = new self;
        }

		return self::$instance;

	}

    /**
     * Register Widget on Dashboard for Admin
     *
     * @since   1.0.0
     *
     * @return  void
     */
    public function register_dashboard_widget() {   
		if( current_user_can('manage_options') ){
			wp_add_dashboard_widget('search_insights_widget', __('Search Insights', 'search-tools'), [$this, 'insights_dashboard_widget']);
		}    
    }

	/**
     * Dashboard Widget data & template
     *
     * @since   1.0.0
     *
     * @return  void
     */
    public function insights_dashboard_widget(){
        global $wpdb;

		$today = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT query, count(*) AS query_count
				FROM %i
				WHERE created_at > DATE(NOW())
				GROUP BY query
				ORDER BY query_count DESC, query ASC LIMIT %d",
				SETO_DB_TABLE,
				$this->results_limit
			)
		);
		$today_html = "";

		$last_7_days = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT query, count(*) AS query_count
				FROM %i
				WHERE created_at >= DATE_ADD(CURDATE(), INTERVAL -7 DAY)
				GROUP BY query
				ORDER BY query_count DESC, query ASC LIMIT %d",
				SETO_DB_TABLE,
				$this->results_limit
			)
		);
		$last_7_days_html = "";

		$overall = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT query, count(*) AS query_count
				FROM %i
				GROUP BY query
				ORDER BY query_count DESC, query ASC LIMIT %d",
				SETO_DB_TABLE,
				$this->results_limit
			)
		);
		$overall_html = "";

		foreach( $today as $key => $result ){
			$today_html .= $this->list_item_helper($key, $result);			
		}

		foreach( $last_7_days as $key => $result ){
            $last_7_days_html .= $this->list_item_helper($key, $result);			
		}

        foreach( $overall as $key => $result ){
            $overall_html .= $this->list_item_helper($key, $result);
        }

		if( file_exists( SETO_PLUGIN_DIR_PATH . "/templates/dashboard-widget.php" ) ){
			require_once( SETO_PLUGIN_DIR_PATH . "/templates/dashboard-widget.php" );
		}
    }

	/**
     * Add item to the list of search results
     *
     * @since   1.0.0
     *
     * @return  void
     */
	private function list_item_helper($key, $result){
		$index = $key + 1;
		$extra_css = '';
		if( $key < 3 ) {
			$extra_css = "font-weight: bold;";
		}
		$item = "
			<tr style='$extra_css'>
				<td class='seto-text-center'>$index.</td> 
				<td>$result->query</td>
				<td class='seto-text-center'> $result->query_count x</td>
			</tr>
		";

		return $item;
	}
}