<?php
namespace SearchToolsPlugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class SETO_Highlight
{
    /**
	 * Static property to hold our singleton instance
     * 
     * @since   1.1.0
     * 
     * @var object
	 *
	 */
    private static $instance = null;

    /**
	 * Constructor
	 *
     * @since   1.1.0
     * 
	 * @return void
	 */
	private function __construct() {
        add_action( 'wp_footer', array( $this, 'highlight_searched_term' ), 10, 0 );
        add_action( 'wp_enqueue_scripts', [$this, 'enqueue_assets'], 50, 0 );
	}

    /**
	 * If an instance exists, this returns it.  If not, it creates one and
	 * returns it.
	 *
     * @since   1.1.0
     * 
	 * @return SETO_Highlight
	 */
	public static function init() {

		if ( !self::$instance ){
			self::$instance = new self;
        }

		return self::$instance;

	}

    /**
     * Highlight the searched query
     *
     * @since   1.1.0
     *
     * @return  void
     */
    public function highlight_searched_term() {
        if( !is_search() || !is_main_query() ){
            return false;
        }

        if( !get_search_query() || empty( get_search_query() ) ){
            return false;
        }

        $options = get_option("seto_free_options");
        $enable = $options["field_highlight_enable"] ?? false;

        if( !$enable ) {
            return;
        }

        $bg_color = $options["field_highlight_background"];
        $text_color = $options["field_highlight_colour"];

        ?>
            <style>
                .seto-highlight {
                    background: linear-gradient(to right, <?php echo $bg_color; ?> 50%, transparent 50%);
                    background-size: 200% 100%;
                    background-position: right bottom;
                    transition: all .5s ease-out;
                    padding-left: 4px;
                    padding-right: 4px;
                }
                .seto-highlight.animate {
                    background-position: left bottom;
                    color: <?php echo $text_color; ?>;
                }
            </style>

            <script>
                document.addEventListener('DOMContentLoaded', function() {    
                    if (typeof Mark === 'undefined') {
                        console.error("Mark.js is not defined!");
                    } else {   
                        var setoMarkInstance = new Mark(document.querySelector("body"));
                        
                        setoMarkInstance.mark("<?php echo get_search_query(); ?>", {
                            "className": "seto-highlight",
                            "each": function(element) {
                                setTimeout(function() {
                                    element.classList.add("animate");
                                }, 250);
                            }
                        });
                    }
                }, false);
            </script>
        <?php
    }

    /**
     * Append necessary assets, JS & CSS files
     *
     * @since   1.1.0
     *
     * @return  void
     */
    public function enqueue_assets() {
        if( !is_search() || !is_main_query() ){
            return false;
        }

        if( !get_search_query() || empty( get_search_query() ) ){
            return false;
        }
        
        $options = get_option("seto_free_options");
        $enable = $options["field_highlight_enable"] ?? false;

        if( !$enable ) {
            return;
        }

        $path = plugin_dir_url( __DIR__ );

        wp_enqueue_script("mark-js", $path . "assets/external/markjs/mark.min.js", false, "9.0.0", ["in_footer" => true]);
    }

}