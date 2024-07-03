<?php
namespace SearchToolsPlugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class SETO_OptionsPage
{
    /**
	 * Static property to hold our singleton instance
     * 
     * @since   1.1.0
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
        add_action( 'admin_menu', array( $this, 'add_menu_page' ), 10, 1 );
        add_action( 'admin_init', array( $this, 'settings_init' ) );
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
	}

	/**
     * Register Admin Tools Menu Item
     *
     * @since   1.0.0
     *
     * @return  void
     */
	public function add_menu_page() {
		add_menu_page(
			esc_html__( 'Search Tools', 'search-tools' ),
			esc_html__( 'Search Tools', 'search-tools' ),
			'manage_options',
			'wp-search-tools',
			[$this, 'seto_content'],
			'dashicons-search',
			50
		);
	}

    /**
	 * Create the Options Page
	 *
     * @since   1.1.0
     * 
	 * @return void
	 */
	public function seto_content(){
        // check if the user have submitted the settings
        // WordPress will add the "settings-updated" $_GET parameter to the url
        if ( isset( $_GET['settings-updated'] ) ) {
            // add settings saved message with the class of "updated"
            add_settings_error( 'seto_messages', 'seto_message', __( 'Settings Saved', 'search-tools' ), 'updated' );
        }

       	// show error/update messages
        settings_errors( 'seto_messages' );
        ?>
		<div class="wrap">
			<h2><?php esc_html_e( 'Search Tools Options', 'search-tools' ); ?></h2>

            <form action="options.php" method="post">
                <?php
                // output security fields for the registered setting
                settings_fields( 'highlight_background' );

                // output setting sections and their fields
                do_settings_sections( 'highlight_background' );

                // output save settings button
                submit_button( __('Save Changes', 'search-tools') );

                // output setting sections and their fields
                do_settings_sections( 'disable_search' );

                // output save settings button
                submit_button( __('Save Changes', 'search-tools') );
                ?>
            </form>
		</div>
	<?php
	}

    /**
	 * Register Settings and Fields
	 *
     * @since   1.1.0
     * 
	 * @return void
	 */
    public function settings_init() {
        // Register a new setting
        register_setting( 'highlight_background', 'seto_free_options' );

        // Register a new section
        add_settings_section(
            'section_highlight_background',
            __( 'Highlight Searched Term', 'search-tools' ),
            [$this, 'section_highlight_background_callback'],
            'highlight_background'
        );

        add_settings_section(
            'section_disable_search',
            __( 'Disable Search', 'search-tools' ),
            [$this, 'section_disable_search_callback'],
            'disable_search'
        );

        // register a new field in a 'highlight' section
        add_settings_field(
            'field_highlight_enable',
            __( 'Enable Highlight', 'search-tools' ),
            [$this, 'field_highlight_enable_cb'],
            'highlight_background',
            'section_highlight_background',
            array(
                'label_for'         => 'field_highlight_enable',
                'class'             => 'st_row',
                'custom_data'    => 'custom',
            )
        );

        add_settings_field(
            'field_highlight_background',
            __( 'Background Colour', 'search-tools' ),
            [$this, 'field_highlight_background_cb'],
            'highlight_background',
            'section_highlight_background',
            array(
                'label_for'         => 'field_highlight_background',
                'class'             => 'st_row',
                'custom_data'    => 'custom',
            )
        );

        add_settings_field(
            'field_highlight_colour',
            __( 'Text Colour', 'search-tools' ),
            [$this, 'field_highlight_colour_cb'],
            'highlight_background',
            'section_highlight_background',
            array(
                'label_for'         => 'field_highlight_colour',
                'class'             => 'st_row',
                'custom_data'    => 'custom',
            )
        );

        // register a new field in a 'disable' section
        add_settings_field(
            'field_disable_search',
            __( 'Disable Search', 'search-tools' ),
            [$this, 'field_disable_search_cb'],
            'disable_search',
            'section_disable_search',
            array(
                'label_for'         => 'field_disable_search',
                'class'             => 'st_row',
                'custom_data'    => 'custom',
            )
        );
    }

    public function section_highlight_background_callback( $args ) {
        ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Manage the colours of the highlighted term in the search results.', 'search-tools' ); ?></p>
        <?php
    }

    public function section_disable_search_callback( $args ) {
        ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Remove search functionality completely?', 'search-tools' ); ?></p>
        <?php
    }

    public function field_highlight_enable_cb( $args ) {
        $options = get_option( 'seto_free_options' );
        ?>
            <input
                type="checkbox"
                name="seto_free_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
                id="<?php echo esc_attr( $args['label_for'] ); ?>"
                value="enable"
                <?php echo isset( $options['field_highlight_enable'] ) ? ( checked(esc_attr($options['field_highlight_enable']), 'enable', false) ) : (''); ?>
            >
        <?php 
    }

    public function field_highlight_background_cb( $args ) {
        $options = get_option( 'seto_free_options' );
        ?>
            <input
                type="text"
                name="seto_free_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
                id="<?php echo esc_attr( $args['label_for'] ); ?>"
                value="<?php echo isset( $options['field_highlight_background'] ) ? esc_attr($options['field_highlight_background']) : ''; ?>"
            >

            <script>
                (function( $ ) {
                    $(function() {
                        $('#field_highlight_background').wpColorPicker();
                    });
                })( jQuery );
            </script>
        <?php 
    }

    public function field_highlight_colour_cb( $args ) {
        $options = get_option( 'seto_free_options' );
        ?>
            <input
                type="text"
                name="seto_free_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
                id="<?php echo esc_attr( $args['label_for'] ); ?>"
                value="<?php echo isset( $options['field_highlight_colour'] ) ? esc_attr($options['field_highlight_colour']) : ''; ?>"
            >

            <script>
                (function( $ ) {
                    $(function() {
                        $('#field_highlight_colour').wpColorPicker();
                    });
                })( jQuery );
            </script>
        <?php 
    }

    public function field_disable_search_cb( $args ) {
        $options = get_option( 'seto_free_options' );
        ?>
            <input
                type="checkbox"
                name="seto_free_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
                id="<?php echo esc_attr( $args['label_for'] ); ?>"
                value="disable"
                <?php echo isset( $options['field_disable_search'] ) ? ( checked(esc_attr($options['field_disable_search']), 'disable', false) ) : (''); ?>
            >
        <?php 
    }

    /**
     * Enqueue JS & CSS assets for color picker
     *
     * @since   1.1.0
     *
     * @return  void
     */
    public function enqueue_assets() {			
        if( $GLOBALS['hook_suffix'] === "toplevel_page_wp-search-tools" ){   
            wp_enqueue_style( 'wp-color-picker');
            wp_enqueue_script( 'wp-color-picker');
        }
    }

    /**
	 * If an instance exists, this returns it.  If not, it creates one and
	 * returns it.
     * 
     * @since   1.1.0
	 *
	 * @return SETO_OptionsPage
	 */
	public static function init() {

		if ( !self::$instance ){
			self::$instance = new self;
        }

		return self::$instance;

	}
}