<?php

namespace SearchTools;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Insights
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
    private $results_limit = 100;

    /**
	 * Constructor
	 *
     * @since   1.0.0
     * 
	 * @return void
	 */
	private function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu_page' ), 50, 1 );
	}

    /**
	 * If an instance exists, this returns it.  If not, it creates one and
	 * returns it.
	 *
	 * @since   1.0.0
	 * 
	 * @return ToolsInsights
	 */
	public static function init() {

		if ( !self::$instance ){
			self::$instance = new self;
        }

		return self::$instance;

	}

	/**
     * Register Admin Tools Menu Item
     *
     * @since   1.0.0
     *
     * @return  void
     */
	public function add_menu_page() {
		add_submenu_page(
			'wp-search-tools',
			esc_html__( 'Insights', 'search-tools' ),
			esc_html__( 'Insights', 'search-tools' ),
			'manage_options',
			'search-tools-insights',
			[$this, 'search_insights_content'],
		);
	}

	/**
     * Tools - Search Insights Page Content
     *
     * @since   1.0.0
     *
     * @return  void
     */
	public function search_insights_content() {
		$default_tab = 'all_users';
		$tab = isset($_GET['tab']) ? sanitize_text_field( $_GET['tab'] ) : $default_tab;

		global $wpdb;
						
		if( $tab === "all_users" ){
			$last_90_days = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT count(*) AS search_count, created_at
					FROM %i
					WHERE created_at >= DATE_ADD(CURDATE(), INTERVAL -90 DAY)
					GROUP BY DATE(created_at)",
					SEARCH_TOOLS_DB_TABLE
				)								
			);								
		} elseif( $tab === "logged_in" ) {
			$last_90_days = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT count(*) AS search_count, created_at
					FROM %i
					WHERE user_roles!='' AND user_roles!='GUEST' AND created_at >= DATE_ADD(CURDATE(), INTERVAL -90 DAY)
					GROUP BY DATE(created_at)",
					SEARCH_TOOLS_DB_TABLE
				)
			);
		} elseif( $tab === "guests" ) {
			$last_90_days = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT count(*) AS search_count, created_at
					FROM %i
					WHERE user_roles='GUEST' AND created_at >= DATE_ADD(CURDATE(), INTERVAL -90 DAY)
					GROUP BY DATE(created_at)",
					SEARCH_TOOLS_DB_TABLE
				)
			);
		}

		$days = $day_counts = [];

		foreach( $last_90_days as $item ){
			array_push($days, date("Y-m-d", strtotime($item->created_at)));
			array_push($day_counts, $item->search_count);
		}

		?>
			<div class="wrap">
				<div class="st-header">
					<div>
						<h1 class="wp-heading-inline">
							<?php esc_html_e( 'Search Insights', 'search-tools' ); ?>
						</h1>
						
						<a href="?page=search-tools-insights&tab=all_users" class="st-header-navitem <?php if($tab==='all_users'):?>is-active<?php endif; ?>"><?php esc_html_e("All Users", "search-tools"); ?></a>
						<a href="?page=search-tools-insights&tab=logged_in" class="st-header-navitem <?php if($tab==='logged_in'):?>is-active<?php endif; ?>"><?php esc_html_e("Logged in", "search-tools"); ?></a>
						<a href="?page=search-tools-insights&tab=guests" class="st-header-navitem <?php if($tab==='guests'):?>is-active<?php endif; ?>"><?php esc_html_e("Guests", "search-tools"); ?></a>
					</div>
						
					<div style="display:none;">
						<a class="st-btn" href="#" target="_blank"><?php esc_html_e("support", "search-tools"); ?></a>
						<a class="st-btn st-btn-primary" href="#" target="_blank"><?php esc_html_e("upgrade to pro", "search-tools"); ?></a>
					</div>
				</div>
								

				<?php
					if( file_exists( SEARCH_TOOLS_PLUGIN_DIR_PATH . "/templates/tools-chart.php" ) ){
						require_once( SEARCH_TOOLS_PLUGIN_DIR_PATH . "/templates/tools-chart.php" );
					}

					if( file_exists( SEARCH_TOOLS_PLUGIN_DIR_PATH . "/templates/tools-premium.php" ) ){
						require_once( SEARCH_TOOLS_PLUGIN_DIR_PATH . "/templates/tools-premium.php" );
					}					
				?>

				<div class="tab-content">
					<?php switch($tab) :
						case 'all_users':
							$this->all_users_html();
							break;
						case 'logged_in':
							$this->logged_in_users_html();
							break;
						case 'guests':
							$this->guest_users_html();
							break;
					endswitch; ?>
				</div>
			</div>
		<?php
	}

	/**
     * Tools - Search Insights 'All Users' Tab
     *
     * @since   1.0.0
     *
     * @return  void
     */
	private function all_users_html()
	{
        global $wpdb;
		
		$today = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT query, count(*) AS query_count, count_total_results
				FROM %i
				WHERE created_at > DATE(NOW())
				GROUP BY query
				ORDER BY query_count DESC, query ASC LIMIT %d",
				SEARCH_TOOLS_DB_TABLE,
				$this->results_limit
			)
		);

		$last_7_days = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT query, count(*) AS query_count, count_total_results
				FROM %i
				WHERE created_at >= DATE_ADD(CURDATE(), INTERVAL -7 DAY)
				GROUP BY query
				ORDER BY query_count DESC, query ASC LIMIT %d",
				SEARCH_TOOLS_DB_TABLE,
				$this->results_limit
			)
		);

		$last_30_days = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT query, count(*) AS query_count, count_total_results
				FROM %i
				WHERE created_at >= DATE_ADD(CURDATE(), INTERVAL -30 DAY)
				GROUP BY query
				ORDER BY query_count DESC, query ASC LIMIT %d",
				SEARCH_TOOLS_DB_TABLE,
				$this->results_limit
			)
		);

		$current_year = date("Y");
		$next_year = date('Y', strtotime('+1 year'));
		$this_year = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT query, count(*) AS query_count, count_total_results
				FROM %i
				WHERE `created_at` >= '%d-01-01 00:00:00' AND `created_at` < '%d-01-01 00:00:00'
				GROUP BY `query`
				ORDER BY query_count DESC, query ASC LIMIT %d;",
				SEARCH_TOOLS_DB_TABLE,
				$current_year,
				$next_year,
				$this->results_limit
			)
		);

		$overall = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT query, count(*) AS query_count, count_total_results
				FROM %i
				GROUP BY query
				ORDER BY query_count DESC, query ASC LIMIT %d",
				SEARCH_TOOLS_DB_TABLE,
				$this->results_limit
			)
		);

		$data = [
			'today' => $today,
			'last_7_days' => $last_7_days,
			'last_30_days' => $last_30_days,
			'this_year' => $this_year,
			'overall' => $overall,
		];

		echo $this->html_builder($data);
	}

	/**
     * Tools - Search Insights 'Logged in Users' Tab
     *
     * @since   1.0.0
     *
     * @return  void
     */
	private function logged_in_users_html()
	{
        global $wpdb;

		$today = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT query, count(*) AS query_count, count_total_results
				FROM %i
				WHERE user_roles!='' AND user_roles!='GUEST' AND created_at > DATE(NOW())
				GROUP BY query ORDER BY query_count DESC, query ASC LIMIT %d",
				SEARCH_TOOLS_DB_TABLE,
				$this->results_limit
			)
		);

		$overall = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT query, count(*) AS query_count, count_total_results
				FROM %i
				WHERE user_roles!='' AND user_roles!='GUEST'
				GROUP BY query
				ORDER BY query_count DESC, query ASC LIMIT %d",
				SEARCH_TOOLS_DB_TABLE,
				$this->results_limit
			)
		);
		
		$last_7_days = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT query, count(*) AS query_count, count_total_results
				FROM %i
				WHERE user_roles!='' AND user_roles!='GUEST' AND created_at >= DATE_ADD(CURDATE(), INTERVAL -7 DAY)
				GROUP BY query
				ORDER BY query_count DESC, query ASC LIMIT %d",
				SEARCH_TOOLS_DB_TABLE,
				$this->results_limit
			)
		);

		$last_30_days = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT query, count(*) AS query_count, count_total_results
				FROM %i
				WHERE user_roles!='' AND user_roles!='GUEST' AND created_at >= DATE_ADD(CURDATE(), INTERVAL -30 DAY)
				GROUP BY query
				ORDER BY query_count DESC, query ASC LIMIT %d",
				SEARCH_TOOLS_DB_TABLE,
				$this->results_limit
			)
		);

		$current_year = date("Y");
		$next_year = date('Y', strtotime('+1 year'));
		$this_year = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT query, count(*) AS query_count, count_total_results
				FROM %i
				WHERE user_roles!='' AND user_roles!='GUEST' AND `created_at` >= '%d-01-01 00:00:00' AND `created_at` < '%d-01-01 00:00:00'
				GROUP BY `query`
				ORDER BY query_count DESC, query ASC LIMIT %d;",
				SEARCH_TOOLS_DB_TABLE,
				$current_year,
				$next_year,
				$this->results_limit
			)
		);

		$data = [
			'today' => $today,
			'last_7_days' => $last_7_days,
			'last_30_days' => $last_30_days,
			'this_year' => $this_year,
			'overall' => $overall,
		];

		echo $this->html_builder($data);
	}

	/**
     * Tools - Search Insights 'Guests' Tab
     *
     * @since   1.0.0
     *
     * @return  void
     */
	private function guest_users_html()
	{
        global $wpdb;

        $overall = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT query, count(*) AS query_count, count_total_results
				FROM %i
				WHERE user_roles='GUEST'
				GROUP BY query
				ORDER BY query_count DESC, query ASC LIMIT %d",
				SEARCH_TOOLS_DB_TABLE,
				$this->results_limit
			)
		);

		$today = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT query, count(*) AS query_count, count_total_results
				FROM %i
				WHERE user_roles='GUEST' AND created_at > DATE(NOW())
				GROUP BY query
				ORDER BY query_count DESC, query ASC LIMIT %d",
				SEARCH_TOOLS_DB_TABLE,
				$this->results_limit
			)
		);

		$last_7_days = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT query, count(*) AS query_count, count_total_results
				FROM %i
				WHERE user_roles='GUEST' AND created_at >= DATE_ADD(CURDATE(), INTERVAL -7 DAY)
				GROUP BY query
				ORDER BY query_count DESC, query ASC LIMIT %d",
				SEARCH_TOOLS_DB_TABLE,
				$this->results_limit
			)
		);

		$last_30_days = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT query, count(*) AS query_count, count_total_results
				FROM %i
				WHERE user_roles='GUEST' AND created_at >= DATE_ADD(CURDATE(), INTERVAL -30 DAY)
				GROUP BY query
				ORDER BY query_count DESC, query ASC LIMIT %d",
				SEARCH_TOOLS_DB_TABLE,
				$this->results_limit
			)
		);

		$current_year = date("Y");
		$next_year = date('Y', strtotime('+1 year'));
		$this_year = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT query, count(*) AS query_count, count_total_results
				FROM %i
				WHERE user_roles='GUEST' AND `created_at` >= '%d-01-01 00:00:00' AND `created_at` < '%d-01-01 00:00:00'
				GROUP BY `query`
				ORDER BY query_count DESC, query ASC LIMIT %d;",
				SEARCH_TOOLS_DB_TABLE,
				$current_year,
				$next_year,
				$this->results_limit
			)
		);

		$data = [
			'today' => $today,
			'last_7_days' => $last_7_days,
			'last_30_days' => $last_30_days,
			'this_year' => $this_year,
			'overall' => $overall,
		];

		echo $this->html_builder($data);	
	}

	/**
     * Tools - Single Row of Statistics Column
     *
     * @since   1.0.0
     *
     * @return  void
     */
	private function statisticsItem($key, $result, $last){
		$index = $key + 1;
		$extra_css = '';
		$extra_class = '';
		if( $key < 3 ) {
			$extra_css = "font-weight: bold;";
		}
		$results = $result->count_total_results ?? 0;
		if( $results == 0 ) {
			$extra_css .= "color: red;";
		}
		$item = "
			<tr class=' $extra_class ' style=' $extra_css'>
				<td>$index.</td>				
				<td>$result->query</td>
				<td class='st-searches-cell st-text-center'>$result->query_count x</td>
				<td class='st-text-center'>$results</td>
			</tr>
		";

		return $item;
	}

	/**
     * Tools - Search Insights Single Statistics
     *
     * @since   1.0.0
     *
     * @return  void
     */
	private function html_helper( $data = [], $label = "Overview" ){
		if( empty( $data ) ){
			return;
		}

		$items = "";
		$prep = array_keys($data);
		$last_key_today = end($prep);
		foreach( $data as $key => $result ){
			$items .= $this->statisticsItem($key, $result, $last_key_today);			
		}

		$html = "
			<h3 class='st-insights-heading'>$label:</h3>

			<table class='st-table st-insights-table'>
				<thead>
					<tr>
						<th>#</th>
						<th>" . esc_html__('query', 'search-tools') . "</th>
						<th class='st-searches-cell st-text-center'>" . esc_html__('searched', 'search-tools') . "</th>
						<th class='st-text-center'>" . esc_html__('results', 'search-tools') . "</th>
					</tr>
				<thead>	
			</table>
			
			<div class='st-insights-table-wrap'>
				<table class='st-table st-insights-table'>
					<tbody>
						$items
					</tbody>
				</table>
			</div>
		";

		return $html;
	}

	/**
     * Tools - Search Insights Single Tab Content
     *
     * @since   1.0.0
     *
     * @return  void
     */
	private function html_builder($data)
	{
		$today_html = $this->html_helper($data["today"], esc_html__("Today", "search-tools"));
		$last_7_days_html = $this->html_helper($data["last_7_days"], esc_html__("Last 7 days", "search-tools"));
		$last_30_days_html = $this->html_helper($data["last_30_days"], esc_html__("Last 30 days", "search-tools"));
		$this_year_html = $this->html_helper($data["this_year"], esc_html__("This year", "search-tools"));
		$overal_html = $this->html_helper($data["overall"], esc_html__("Overall", "search-tools"));

		if( file_exists( SEARCH_TOOLS_PLUGIN_DIR_PATH . "/templates/tools-statistics.php" ) ){
			require_once( SEARCH_TOOLS_PLUGIN_DIR_PATH . "/templates/tools-statistics.php" );
		}
	}

}