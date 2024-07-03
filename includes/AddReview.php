<?php
namespace SearchToolsPlugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class SETO_AddReview
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
        add_action( 'admin_notices', [$this, 'ask_for_review_admin_notice'], 10, 0 );
        add_action( 'admin_enqueue_scripts', [$this, 'enqueue_assets'], 10, 0 );
        add_action( 'wp_ajax_disable_review_notice', [ $this, 'disable_review_notice'], 10, 0 );
	}

    /**
     * Validate the show notice request
     *
     * @since   1.2.0
     *
     * @return  void
     */
    private function check_if_show_notice() {
        $show = false;
        $user = wp_get_current_user();

        $activation_date = get_option("seto_plugin_activation_date");
        $activation_date_time = new \DateTime($activation_date);
        $now = new \DateTime("now");
        $interval = $now->diff($activation_date_time);
        $days_active = $interval->days;

        $disabled = get_option("seto_disable_reviews") ?? false;

        if ( $days_active >= 30 && in_array( 'administrator', (array) $user->roles ) && !$disabled ) {
            $show = true;
        }

        return $show;
    }

    /**
     * Show Admin Notice
     *
     * @since   1.2.0
     *
     * @return  void
     */
    public function ask_for_review_admin_notice() {
        $check = $this->check_if_show_notice();

        if( !$check ){
            return;
        }

        $title = __('Good news! Happy to see that Search Tools plugin works for you.', 'search-tools');
        $content = __('Would you be so kind to rate the plugin in the wordpress.org?', 'search-tools');
        $link1 = __('Yes, of course', 'search-tools');
        $link3 = __('The plugin doesn\'t deserve it', 'search-tools');

        $notice_html =
        '<div class="notice notice-success is-dismissible" id="seto_notice_reviews" style="padding-bottom: 1rem;">
            <h3>' . $title . '</h3>

            <p>' . $content . '</p>

            <a href="https://wordpress.org/support/plugin/search-tools/reviews/#new-post" class="js-seto-accept-review" style="display; inline-block; margin-right: 21rem;" target="_blank">' . $link1 . '</a>
            <a href="#" class="js-seto-refuse-review">' . $link3 . '</a>
        </div>';

        echo $notice = wp_kses($notice_html, 'post');
    }

    /**
     * Handle admin AJAX request
     *
     * @since   1.2.0
     *
     * @return  void
     */
    public function disable_review_notice() {
		$disable = wp_unslash( $_POST['disable'] );

        update_option("seto_disable_reviews", true);

        wp_send_json_success( $disable );

        wp_die();
    }

    /**
     * Append necessary assets, JS file
     *
     * @since   1.2.0
     *
     * @return  void
     */
    public function enqueue_assets() {
        $check = $this->check_if_show_notice();

        if( !$check ){
            return;
        }

        $path = plugin_dir_url( __DIR__ );

        wp_enqueue_script("refuse-review-js", $path . "assets/js/refuse-review.js", ["jquery"], "1.0.3", ["in_footer" => true]);
    }

    /**
	 * If an instance exists, this returns it.  If not, it creates one and
	 * returns it.
	 *
     * @since   1.2.0
     * 
	 * @return SETO_AddReview
	 */
	public static function init() {

		if ( !self::$instance ){
			self::$instance = new self;
        }

		return self::$instance;

	}
}