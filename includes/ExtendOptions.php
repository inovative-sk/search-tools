<?php
namespace SearchTools;

/*
	This file is modified part of "WP Extended Search plugin" which is released under "GNU GENERAL PUBLIC LICENSE version 3".
	See file https://github.com/5um17/wp-extended-search/blob/master/LICENSE for full license details.
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Register option page an other hooks.
 */
class ExtendOptions {

	/**
	 * Class instance.
	 *
	 * @since 2.0
	 * @var ExtendOptions
	 */
	public static $instance = false;

	/**
	 * Plugin settings.
	 *
	 * @since 1.0
	 * @var array
	 */
	private $st_settings = '';

	/**
	 * Current setting option name.
	 *
	 * @since 2.0
	 * @var string
	 */
	private $option_key_name = 'st_options';

	/**
	 * Default Constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		// Load settings.
		$this->st_settings = $this->st_options();

		// Add option page.
		add_action( 'admin_menu', array( $this, 'add_menu_page' ), 10, 1 );

		add_action( 'admin_init', array( $this, 'admin_init' ) );

		// Register scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

    /**
	 * If an instance exists, this returns it.  If not, it creates one and
	 * returns it.
	 *
     * @since   1.0.0
     * 
	 * @return ExtendOptions
	 */
	public static function init() {

		if ( !self::$instance ){
			self::$instance = new self;
        }

		return self::$instance;

	}

	/**
	 * Get Default options.
	 *
	 * @since 1.0
	 */
	public function default_options() {
		$settings = array(
			'disabled'       => false,
			'wc_search'      => false,
			'title'          => true,
			'content'        => true,
			'excerpt'        => true,
			'meta_keys'      => array(),
			'taxonomies'     => array(),
			'authors'        => false,
			'post_types'     => array( 'post', 'page' ),
			'exclude_date'   => '',
			'posts_per_page' => '',
			'terms_relation' => 1,
			'orderby'        => '',
			'order'          => 'DESC',
			'exact_match'    => 'no',
			'media_types'    => array(),
		);
		return $settings;
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
			__( 'Search Tools', 'search-tools' ),
			__( 'Search Tools', 'search-tools' ),
			'manage_options',
			'wp-search-tools',
			[$this, 'search_tools_content'],
			'dashicons-search',
			50
		);

		add_submenu_page(
			'wp-search-tools',
			__( 'Extend Search', 'search-tools' ),
			__( 'Extend Search', 'search-tools' ),
			'manage_options',
			'search-tools-extend',
			[$this, 'extend_search_content']
		);
	}

	public function search_tools_content(){ ?>
		<div class="wrap">
			<h2><?php _e( 'WP Search Tools Plugin', 'search-tools' ); ?></h2>

			<p>
				<?php _e( 'PRO version is yet to come. Check the website for more information', 'search-tools' ); ?>
				<a href="https://www.wpsearchtools.com" target="_blank">wpsearchtools.com</a>
			</p>
		</div>
	<?php
	}

	/**
	 * Print options page content.
	 *
	 * @since 1.0
	 */
	public function extend_search_content(){ ?>
		<div class="wrap">
			<h2><?php _e( 'Extend WordPress Search', 'search-tools' ); ?></h2>
			<?php settings_errors(); ?>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'st_option_group' );
				do_settings_sections( 'wp-st' );

				submit_button( __( 'Save Changes', 'search-tools' ), 'primary', 'submit', false );
				echo '&nbsp;&nbsp;';
				submit_button( __( 'Reset to WP Default', 'search-tools' ), 'secondary', 'reset', false );
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Add Section settings and settings fields
	 *
	 * @since 1.0
	 */
	public function admin_init() {
		// Register Settings.
		register_setting( 'st_option_group', $this->option_key_name, array( $this, 'st_save' ) );

		// Add Sections.
		add_settings_section( 'st_section_1', __( 'Select Fields to include in WordPress default Search', 'search-tools' ), array( $this, 'st_section_content' ), 'wp-st' );
		if ( in_array( 'attachment', $this->st_settings['post_types'], true ) ) {
			add_settings_section( 'st_section_media', __( 'Media Search Settings', 'search-tools' ), null, 'wp-st' );
		}
		add_settings_section( 'st_section_misc', __( 'Miscellaneous Settings', 'search-tools' ), null, 'wp-st' );

		// Add fields.
		add_settings_field( 'st_title_and_post_content', __( 'General Search Setting', 'search-tools' ), array( $this, 'st_title_content_checkbox' ), 'wp-st', 'st_section_1' );
		add_settings_field( 'st_list_custom_fields', __( 'Select Meta Key Names', 'search-tools' ), array( $this, 'st_custom_field_name_list' ), 'wp-st', 'st_section_1' );
		add_settings_field( 'st_list_taxonomies', __( 'Select Taxonomies', 'search-tools' ), array( $this, 'st_taxonomies_settings' ), 'wp-st', 'st_section_1' );
		add_settings_field( 'st_include_authors', __( 'Author Setting', 'search-tools' ), array( $this, 'st_author_settings' ), 'wp-st', 'st_section_1' );
		add_settings_field( 'st_list_post_types', __( 'Select Post Types', 'search-tools' ), array( $this, 'st_post_types_settings' ), 'wp-st', 'st_section_1' );
		add_settings_field( 'st_terms_relation_type', __( 'Terms Relation Type', 'search-tools' ), array( $this, 'st_terms_relation_type' ), 'wp-st', 'st_section_misc', array( 'label_for' => 'es_terms_relation' ) );

		add_settings_field( 'st_exact_search', __( 'Match the search term exactly', 'search-tools' ), array( $this, 'st_exact_search' ), 'wp-st', 'st_section_misc' );
		add_settings_field( 'st_exclude_older_results', __( 'Select date to exclude older results', 'search-tools' ), array( $this, 'st_exclude_results' ), 'wp-st', 'st_section_misc', array( 'label_for' => 'st_exclude_date' ) );
		add_settings_field( 'st_number_of_posts', __( 'Posts per page', 'search-tools' ), array( $this, 'st_posts_per_page' ), 'wp-st', 'st_section_misc', array( 'label_for' => 'es_posts_per_page' ) );
		add_settings_field( 'st_search_results_order', __( 'Search Results Order', 'search-tools' ), array( $this, 'st_search_results_order' ), 'wp-st', 'st_section_misc', array( 'label_for' => 'es_search_results_order' ) );
		add_settings_field( 'st_media_types', __( 'Media Types', 'search-tools' ), array( $this, 'st_media_types' ), 'wp-st', 'st_section_media' );
	}

	/**
	 * Enqueue admin style and scripts.
	 *
	 * @param string $hook Current page name.
	 * @since 1.0
	 */
	public function enqueue_assets( $hook ) {
		// Register scripts for main setting page.
		if ( 'search-tools_page_search-tools-extend' === $hook ) {
			wp_enqueue_style("search-tools-select2", SEARCH_TOOLS_ASSETS_URL . "external/select2/css/select2.min.css", false, "1.1.0", "all");
			wp_enqueue_style( 'search-tools-jquery_ui', SEARCH_TOOLS_ASSETS_URL . 'jQueryUI/jquery-ui.min.css' );
			wp_enqueue_style( 'search-tools-jquery_ui_theme', SEARCH_TOOLS_ASSETS_URL . 'jQueryUI/jquery-ui.theme.min.css' );

			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script("search-tools-select2", SEARCH_TOOLS_ASSETS_URL . "external/select2/js/select2.full.min.js", ["jquery"], "1.0", ["in_footer" => true]);
			wp_enqueue_script("search-tools-extend", SEARCH_TOOLS_ASSETS_URL . "js/search-tools.js", ["jquery"], "1.0", ["in_footer" => true]);
		} 
	}

	/**
	 * Get all meta keys.
	 *
	 * @since 1.0
	 * @global Object $wpdb WPDB object.
	 * @return Array array of meta keys.
	 */
	public function st_fields() {
		global $wpdb;
		/**
		 * Filter query for meta keys in admin options.
		 *
		 * @since 1.0.0
		 * @param string SQL query.
		 */
		// phpcs:ignore
		$st_fields = $wpdb->get_results( apply_filters( 'st_meta_keys_query', "select DISTINCT meta_key from $wpdb->postmeta where meta_key NOT LIKE '\_%' ORDER BY meta_key ASC" ) );
		$meta_keys    = array();

		if ( is_array( $st_fields ) && ! empty( $st_fields ) ) {
			foreach ( $st_fields as $field ) {
				if ( isset( $field->meta_key ) ) {
					$meta_keys[] = $field->meta_key;
				}
			}
		}

		/**
		 * Filter results of SQL query for meta keys.
		 *
		 * @since 1.1
		 * @param array $meta_keys array of meta keys.
		 */
		return apply_filters( 'st_meta_keys', $meta_keys );
	}

	/**
	 * Validate input settings.
	 *
	 * @since 1.0
	 * @global object $WP_ES Main class object.
	 * @param array $input input array by user.
	 * @return array validated input for saving.
	 */
	public function st_save( $input ) {
		$settings = $this->st_settings;

		if ( isset( $_POST['reset'] ) ) {
			add_settings_error( 'st_error', 'st_error_reset', __( 'Settings has been changed to WordPress default search setting.', 'search-tools' ), 'updated' );
			return $this->default_options();
		}

		if ( ! isset( $input['post_types'] ) || empty( $input['post_types'] ) ) {
			add_settings_error( 'st_error', 'st_error_post_type', __( 'Select at least one post type!', 'search-tools' ) );
			return $settings;
		}

		if ( empty( $input['title'] ) && empty( $input['content'] ) && empty( $input['excerpt'] ) && empty( $input['meta_keys'] ) && empty( $input['taxonomies'] ) && empty( $input['authors'] ) ) {
			add_settings_error( 'st_error', 'st_error_all_empty', __( 'Select at least one setting to search!', 'search-tools' ) );
			return $settings;
		}

		if ( ! empty( $input['exclude_date'] ) && ! strtotime( $input['exclude_date'] ) ) {
			add_settings_error( 'st_error', 'st_error_invalid_date', __( 'Date seems to be in invalid format!', 'search-tools' ) );
			return $settings;
		}

		return $input;
	}

	/**
	 * Default settings checkbox.
	 *
	 * @since 1.0
	 */
	public function st_title_content_checkbox() {
		?>
		<input type="hidden" name="<?php echo $this->option_key_name; ?>[title]" value="0" />
		<input <?php checked( $this->st_settings['title'] ); ?> type="checkbox" id="estitle" name="<?php echo $this->option_key_name; ?>[title]" value="1" />&nbsp;
		<label for="estitle"><?php _e( 'Search in Title', 'search-tools' ); ?></label>
		<br />
		<input type="hidden" name="<?php echo $this->option_key_name; ?>[content]" value="0" />
		<input <?php checked( $this->st_settings['content'] ); ?> type="checkbox" id="escontent" name="<?php echo $this->option_key_name; ?>[content]" value="1" />&nbsp;
		<label for="escontent"><?php _e( 'Search in Content', 'search-tools' ); ?></label>
		<br />
		<input type="hidden" name="<?php echo $this->option_key_name; ?>[excerpt]" value="0" />
		<input <?php checked( $this->st_settings['excerpt'] ); ?> type="checkbox" id="esexcerpt" name="<?php echo $this->option_key_name; ?>[excerpt]" value="1" />&nbsp;
		<label for="esexcerpt"><?php _e( 'Search in Excerpt', 'search-tools' ); ?></label>
		<?php
	}

	/**
	 * Meta keys checkboxes.
	 *
	 * @since 1.0
	 */
	public function st_custom_field_name_list() {
		$meta_keys = $this->st_fields();
		if ( ! empty( $meta_keys ) ) {
			?>
			<select class="st-select2" multiple="multiple" name="<?php echo $this->option_key_name; ?>[meta_keys][]">
			<?php
			foreach ( (array) $meta_keys as $meta_key ) {
				?>
				<option <?php echo $this->st_checked( $meta_key, $this->st_settings['meta_keys'], true ); ?> value="<?php echo $meta_key; ?>"><?php echo $meta_key; ?></option>
				<?php
			}
			?>
			</select>
			<?php
		} else {
			?>
			<em><?php _e( 'No meta key found!', 'search-tools' ); ?></em>
			<?php
		}
	}

	/**
	 * Taxonomies checkbox.
	 *
	 * @since 1.0
	 */
	public function st_taxonomies_settings() {

		/**
		 * Filter taxonomies arguments.
		 *
		 * @since 1.0.1
		 * @param array arguments array.
		 */
		$tax_args = apply_filters(
			'st_tax_args',
			array(
				'show_ui' => true,
				'public'  => true,
			)
		);

		/**
		 * Filter taxonomy list return by get_taxonomies function.
		 *
		 * @since 1.1
		 * @param $all_taxonomies Array of taxonomies.
		 */
		$all_taxonomies = apply_filters( 'st_tax', get_taxonomies( $tax_args, 'objects' ) );
		if ( is_array( $all_taxonomies ) && ! empty( $all_taxonomies ) ) {
			?>
			<select multiple="multiple" class="st-select2" name="<?php echo $this->option_key_name; ?>[taxonomies][]">
			<?php
			foreach ( $all_taxonomies as $tax_name => $tax_obj ) {
				?>
				<option <?php echo $this->st_checked( $tax_name, $this->st_settings['taxonomies'], true ); ?> value="<?php echo $tax_name; ?>">
				<?php echo ! empty( $tax_obj->labels->name ) ? $tax_obj->labels->name : $tax_name; ?>
				</option>
				<?php
			}
			?>
			</select>
			<?php
		} else {
			?>
			<em><?php _e( 'No public taxonomy found!', 'search-tools' ); ?></em>
			<?php
		}
	}

	/**
	 * Author settings meta box.
	 *
	 * @since 1.1
	 */
	public function st_author_settings() {
		?>
		<input name="<?php echo $this->option_key_name; ?>[authors]" type="hidden" value="0" />
		<input id="st_include_authors" <?php checked( $this->st_settings['authors'] ); ?> type="checkbox" value="1" name="<?php echo $this->option_key_name; ?>[authors]" />
		<label for="st_include_authors"><?php _e( 'Search in Author display name', 'search-tools' ); ?></label>
		<p class="description"><?php _e( 'If checked then it will display those results whose Author "Display name" match the search terms.', 'search-tools' ); ?></p>
		<?php
	}

	/**
	 * Post type checkboexes.
	 *
	 * @since 1.0
	 */
	public function st_post_types_settings() {

		/**
		 * Filter post type arguments.
		 *
		 * @since 1.0.1
		 * @param array arguments array.
		 */
		$post_types_args = apply_filters(
			'st_post_types_args',
			array(
				'show_ui' => true,
				'public'  => true,
			)
		);

		/**
		 * Filter post type array return by get_post_types function.
		 *
		 * @since 1.1
		 * @param array $all_post_types Array of post types.
		 */
		$all_post_types = apply_filters( 'st_post_types', get_post_types( $post_types_args, 'objects' ) );

		if ( is_array( $all_post_types ) && ! empty( $all_post_types ) ) {
			?>
			<select multiple="multiple" class="st-select2" name="<?php echo $this->option_key_name; ?>[post_types][]">
			<?php
			foreach ( $all_post_types as $post_name => $post_obj ) {
				?>
				<option <?php echo $this->st_checked( $post_name, $this->st_settings['post_types'], true ); ?> value="<?php echo $post_name; ?>" >
				<?php echo isset( $post_obj->labels->name ) ? $post_obj->labels->name : $post_name; ?>
				</option>
				<?php
			}
			?>
			</select>
			<p class="description">
				<?php
					_e( 'If you are selecting Media post type then save the settings once to enable more media settings.', 'search-tools' );
				?>
			</p>
			<?php
		} else {
			?>
			<em><?php _e( 'No public post type found!', 'search-tools' ); ?></em>
			<?php
		}
	}

	/**
	 * Terms relation type meta box.
	 *
	 * @since 1.1
	 */
	public function st_terms_relation_type() {
		?>
		<select <?php echo $this->st_disabled( $this->st_settings['exact_match'], 'yes' ); ?> id="es_terms_relation" name="<?php echo $this->option_key_name; ?>[terms_relation]">
			<option <?php selected( $this->st_settings['terms_relation'], 1 ); ?> value="1"><?php _e( 'AND', 'search-tools' ); ?></option>
			<option <?php selected( $this->st_settings['terms_relation'], 2 ); ?> value="2"><?php _e( 'OR', 'search-tools' ); ?></option>
		</select>
		<p class="description">
			<?php
			if ( 'yes' === $this->st_settings['exact_match'] ) {
				_e( 'This option is disabled because you have selected "Match the search term exactly".  When using the exact match option, the sentence is not broken into terms instead the whole sentence is matched thus this option has no meaning.', 'search-tools' );
			} else {
				_e( 'Type of query relation between search terms. e.g. someone searches for "my query" then define the relation between "my" and "query". The default value is AND.', 'search-tools' );
			}
			?>
		</p>
		<?php
	}

	/**
	 * Exclude older results.
	 *
	 * @since 1.0.2
	 */
	public function st_exclude_results() {
		?>
		<input class="regular-text" type="text" value="<?php echo esc_attr( $this->st_settings['exclude_date'] ); ?>" name="<?php echo $this->option_key_name; ?>[exclude_date]" id="st_exclude_date" />
		<p class="description"><?php _e( 'Contents will not appear in search results older than this date OR leave blank to disable this feature.', 'search-tools' ); ?></p>
		<?php
	}

	/**
	 * Posts per search results page.
	 *
	 * @since 1.1
	 */
	public function st_posts_per_page() {
		?>
		<input min="-1" class="small-text" type="number" value="<?php echo esc_attr( $this->st_settings['posts_per_page'] ); ?>" name="<?php echo $this->option_key_name; ?>[posts_per_page]" id="es_posts_per_page" />
		<p class="description"><?php _e( 'Number of posts to display on search result page OR leave blank for default value.', 'search-tools' ); ?></p>
		<?php
	}

	/**
	 * Search results order.
	 *
	 * @since 1.3
	 */
	public function st_search_results_order() {
		?>
		<select id="es_search_results_order" name="<?php echo $this->option_key_name; ?>[orderby]">
			<option <?php selected( $this->st_settings['orderby'], '' ); ?> value=""><?php _e( 'Relevance', 'search-tools' ); ?></option>
			<option <?php selected( $this->st_settings['orderby'], 'date' ); ?> value="date"><?php _e( 'Date', 'search-tools' ); ?></option>
			<option <?php selected( $this->st_settings['orderby'], 'modified' ); ?> value="modified"><?php _e( 'Last Modified Date', 'search-tools' ); ?></option>
			<option <?php selected( $this->st_settings['orderby'], 'title' ); ?> value="title"><?php _e( 'Post Title', 'search-tools' ); ?></option>
			<option <?php selected( $this->st_settings['orderby'], 'name' ); ?> value="name"><?php _e( 'Post Slug', 'search-tools' ); ?></option>
			<option <?php selected( $this->st_settings['orderby'], 'type' ); ?> value="type"><?php _e( 'Post Type', 'search-tools' ); ?></option>
			<option <?php selected( $this->st_settings['orderby'], 'comment_count' ); ?> value="comment_count"><?php _e( 'Number of Comments', 'search-tools' ); ?></option>
			<option <?php selected( $this->st_settings['orderby'], 'rand' ); ?> value="rand"><?php _e( 'Random', 'search-tools' ); ?></option>
		</select>
		<p class="description">
			<?php
			/* translators: %1$s: anchor tag opening, %2$s: anchor tag closed. */
			echo sprintf( __( 'Sort search results based on metadata of items. The default value is %1$sRelevance%2$s.', 'search-tools' ), '<a href="https://developer.wordpress.org/reference/classes/wp_query/#order-orderby-parameters">', '</a>' );
			?>
		</p>
		<br />
		<label><input <?php echo $this->st_checked( $this->st_settings['order'], array( 'DESC' ) ); ?> type="radio" value="DESC" name="<?php echo $this->option_key_name; ?>[order]" /><?php _e( 'Descending', 'search-tools' ); ?></label>
		<label><input <?php echo $this->st_checked( $this->st_settings['order'], array( 'ASC' ) ); ?> type="radio" value="ASC" name="<?php echo $this->option_key_name; ?>[order]" /><?php _e( 'Ascending', 'search-tools' ); ?></label>
		<p class="description"><?php _e( 'Order the sorted search items in Descending or Ascending. Default is Descending.', 'search-tools' ); ?></p>
		<?php
	}

	/**
	 * Select exact or partial term matching.
	 *
	 * @since 1.3
	 */
	public function st_exact_search() {
		?>
		<label><input <?php echo $this->st_checked( $this->st_settings['exact_match'], array( 'yes' ) ); ?> type="radio" value="yes" name="<?php echo $this->option_key_name; ?>[exact_match]" /><?php _e( 'Yes', 'search-tools' ); ?></label>
		<label><input <?php echo $this->st_checked( $this->st_settings['exact_match'], array( 'no' ) ); ?> type="radio" value="no" name="<?php echo $this->option_key_name; ?>[exact_match]" /><?php _e( 'No', 'search-tools' ); ?></label>
		<p class="description"><?php _e( 'Whether to match search term exactly or partially e.g. If someone search "Word" it will display items matching "WordPress" or "Word" but if you select Yes then it will display items only matching "Word". The default value is No.', 'search-tools' ); ?></p>
		<?php
	}

	/**
	 * Section content before displaying search settings.
	 *
	 * @since 1.0
	 */
	public function st_section_content() {

	}

	/**
	 * Select mime type.
	 *
	 * @since 2.1
	 */
	public function st_media_types() {
		?>
		<select multiple="multiple" class="st-select2" name="<?php echo $this->option_key_name; ?>[media_types][]">
			<?php
			foreach ( (array) get_allowed_mime_types() as $ext => $type ) {
				?>
				<option <?php echo $this->st_checked( $type, $this->st_settings['media_types'], true ); ?> value="<?php echo esc_attr( $type ); ?>" >
					<?php echo $ext; ?>
				</option>
				<?php
			}
			?>
		</select>
		<p class="description"><?php _e( 'Select the media types to limit the results by type. Leave blank to search all media types.', 'search-tools' ); ?></p>
		<?php
	}

	/**
	 * Return checked or selected if value exist in array.
	 *
	 * @since 1.0
	 * @param mixed $value value to check against array.
	 * @param array $array haystack array.
	 * @param bool  $selected Set to <code>true</code> when using in select else <code>false</code>.
	 * @return string checked="checked" or selected="selected" or blank string.
	 */
	public function st_checked( $value = false, $array = array(), $selected = false ) {
		if ( in_array( $value, $array, true ) ) {
			$checked = $selected ? 'selected="selected"' : 'checked="checked"';
		} else {
			$checked = '';
		}

		return $checked;
	}

	/**
	 * Return disabled if both values are equal.
	 *
	 * @since 1.3
	 * @param mixed $first_value First value to compare.
	 * @param mixed $second_value Second value to compare.
	 * @return string disabled="disabled" or blank string.
	 */
	public function st_disabled( $first_value, $second_value = true ) {
		if ( $first_value == $second_value ) { // phpcs:ignore loose comparison
			return 'disabled="disabled"';
		}

		return '';
	}

	/**
	 * Get plugin options.
	 *
	 * @since 1.0
	 */
	public function st_options() {

		if ( ! empty( $this->st_settings ) ) {
			return $this->st_settings;
		}

		$settings = wp_parse_args( get_option( $this->option_key_name ), $this->default_options() );

		return $settings;
	}
}
