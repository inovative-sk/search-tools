<?php
namespace SearchToolsPlugin;

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
class SETO_ExtendOptions {

	/**
	 * Class instance.
	 *
	 * @since 1.0.0
	 * @var SETO_ExtendOptions
	 */
	public static $instance = false;

	/**
	 * Plugin settings.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $seto_settings = '';

	/**
	 * Current setting option name.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $option_key_name = 'seto_options';

	/**
	 * Default Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Load settings.
		$this->seto_settings = $this->seto_options();

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
	 * @return SETO_ExtendOptions
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
	 * @since 1.0.0
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
		add_submenu_page(
			'wp-search-tools',
			esc_html__( 'Extend Search', 'search-tools' ),
			esc_html__( 'Extend Search', 'search-tools' ),
			'manage_options',
			'search-tools-extend',
			[$this, 'extend_search_content']
		);
	}

	/**
	 * Print options page content.
	 *
	 * @since 1.0.0
	 */
	public function extend_search_content(){ ?>
		<div class="wrap">
			<h2><?php esc_html_e( 'Extend WordPress Search', 'search-tools' ); ?></h2>
			<?php settings_errors(); ?>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'seto_option_group' );
				do_settings_sections( 'wp-st' );

				submit_button( esc_html__( 'Save Changes', 'search-tools' ), 'primary', 'submit', false );
				echo esc_html('&nbsp;&nbsp;');
				submit_button( esc_html__( 'Reset to WP Default', 'search-tools' ), 'secondary', 'reset', false );
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Add Section settings and settings fields
	 *
	 * @since 1.0.0
	 */
	public function admin_init() {
		// Register Settings.
		register_setting( 'seto_option_group', $this->option_key_name, array( $this, 'save' ) );

		// Add Sections.
		add_settings_section( 'seto_section_1', esc_html__( 'Select Fields to include in WordPress default Search', 'search-tools' ), array( $this, 'section_content' ), 'wp-st' );
		if ( in_array( 'attachment', $this->seto_settings['post_types'], true ) ) {
			add_settings_section( 'seto_section_media', esc_html__( 'Media Search Settings', 'search-tools' ), null, 'wp-st' );
		}
		add_settings_section( 'seto_section_misc', esc_html__( 'Miscellaneous Settings', 'search-tools' ), null, 'wp-st' );

		// Add fields.
		add_settings_field( 'seto_title_and_post_content', esc_html__( 'General Search Setting', 'search-tools' ), array( $this, 'title_content_checkbox' ), 'wp-st', 'seto_section_1' );
		add_settings_field( 'seto_list_custom_fields', esc_html__( 'Select Meta Key Names', 'search-tools' ), array( $this, 'custom_field_name_list' ), 'wp-st', 'seto_section_1' );
		add_settings_field( 'seto_list_taxonomies', esc_html__( 'Select Taxonomies', 'search-tools' ), array( $this, 'taxonomies_settings' ), 'wp-st', 'seto_section_1' );
		add_settings_field( 'seto_include_authors', esc_html__( 'Author Setting', 'search-tools' ), array( $this, 'author_settings' ), 'wp-st', 'seto_section_1' );
		add_settings_field( 'seto_list_post_types', esc_html__( 'Select Post Types', 'search-tools' ), array( $this, 'post_types_settings' ), 'wp-st', 'seto_section_1' );
		add_settings_field( 'seto_terms_relation_type', esc_html__( 'Terms Relation Type', 'search-tools' ), array( $this, 'terms_relation_type' ), 'wp-st', 'seto_section_misc', array( 'label_for' => 'terms_relation' ) );

		add_settings_field( 'seto_exact_search', esc_html__( 'Match the search term exactly', 'search-tools' ), array( $this, 'exact_search' ), 'wp-st', 'seto_section_misc' );
		add_settings_field( 'seto_exclude_older_results', esc_html__( 'Select date to exclude older results', 'search-tools' ), array( $this, 'exclude_results' ), 'wp-st', 'seto_section_misc', array( 'label_for' => 'st_exclude_date' ) );
		add_settings_field( 'seto_number_of_posts', esc_html__( 'Posts per page', 'search-tools' ), array( $this, 'posts_per_page' ), 'wp-st', 'seto_section_misc', array( 'label_for' => 'seto_posts_per_page' ) );
		add_settings_field( 'seto_search_results_order', esc_html__( 'Search Results Order', 'search-tools' ), array( $this, 'search_results_order' ), 'wp-st', 'seto_section_misc', array( 'label_for' => 'search_results_order' ) );
		add_settings_field( 'seto_media_types', esc_html__( 'Media Types', 'search-tools' ), array( $this, 'media_types' ), 'wp-st', 'seto_section_media' );
	}

	/**
	 * Enqueue admin style and scripts.
	 *
	 * @param string $hook Current page name.
	 * @since 1.0.0
	 */
	public function enqueue_assets( $hook ) {
		// Register scripts for main setting page.
		if ( 'search-tools_page_search-tools-extend' === $hook ) {
			wp_enqueue_style("search-tools-select2", SETO_ASSETS_URL . "external/select2/css/select2.min.css", false, "4.0.13", "all");
			wp_enqueue_style( 'search-tools-jquery_ui', SETO_ASSETS_URL . 'jQueryUI/jquery-ui.min.css' );
			wp_enqueue_style( 'search-tools-jquery_ui_theme', SETO_ASSETS_URL . 'jQueryUI/jquery-ui.theme.min.css' );

			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script("search-tools-select2", SETO_ASSETS_URL . "external/select2/js/select2.full.min.js", ["jquery"], "4.0.13", ["in_footer" => true]);
			wp_enqueue_script("search-tools-extend", SETO_ASSETS_URL . "js/search-tools.js", ["jquery"], "1.0.0.0", ["in_footer" => true]);
		} 
	}

	/**
	 * Get all meta keys.
	 *
	 * @since 1.0.0
	 * @global Object $wpdb WPDB object.
	 * @return Array array of meta keys.
	 */
	public function fields() {
		global $wpdb;
		/**
		 * Filter query for meta keys in admin options.
		 *
		 * @since 1.0.0
		 * @param string SQL query.
		 */
		// phpcs:ignore
		$fields = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DISTINCT meta_key
				FROM $wpdb->postmeta
				WHERE meta_key NOT LIKE %s
				ORDER BY meta_key ASC",
				"\_%"
			)
		);
		$meta_keys = array();

		if ( is_array( $fields ) && ! empty( $fields ) ) {
			foreach ( $fields as $field ) {
				if ( isset( $field->meta_key ) ) {
					$meta_keys[] = $field->meta_key;
				}
			}
		}

		/**
		 * Filter results of SQL query for meta keys.
		 *
		 * @since 1.0.0
		 * @param array $meta_keys array of meta keys.
		 */
		return apply_filters( 'seto_meta_keys', $meta_keys );
	}

	/**
	 * Validate input settings.
	 *
	 * @since 1.0.0
	 * @param array $input input array by user.
	 * @return array validated input for saving.
	 */
	public function save( $input ) {
		$settings = $this->seto_settings;

		if ( isset( $_POST['reset'] ) ) {
			add_settings_error( 'seto_error', 'seto_error_reset', esc_html__( 'Settings has been changed to WordPress default search setting.', 'search-tools' ), 'updated' );
			return $this->default_options();
		}

		if ( ! isset( $input['post_types'] ) || empty( $input['post_types'] ) ) {
			add_settings_error( 'seto_error', 'seto_error_post_type', esc_html__( 'Select at least one post type!', 'search-tools' ) );
			return $settings;
		}

		if ( empty( $input['title'] ) && empty( $input['content'] ) && empty( $input['excerpt'] ) && empty( $input['meta_keys'] ) && empty( $input['taxonomies'] ) && empty( $input['authors'] ) ) {
			add_settings_error( 'seto_error', 'seto_error_all_empty', esc_html__( 'Select at least one setting to search!', 'search-tools' ) );
			return $settings;
		}

		if ( ! empty( $input['exclude_date'] ) && ! strtotime( $input['exclude_date'] ) ) {
			add_settings_error( 'seto_error', 'seto_error_invalid_date', esc_html__( 'Date seems to be in invalid format!', 'search-tools' ) );
			return $settings;
		}

		return $input;
	}

	/**
	 * Default settings checkbox.
	 *
	 * @since 1.0.0
	 */
	public function title_content_checkbox() {
		?>
		<input type="hidden" name="<?php echo esc_attr($this->option_key_name); ?>[title]" value="0" />
		<input <?php checked( esc_attr($this->seto_settings['title']) ); ?> type="checkbox" id="seto_title" name="<?php echo esc_attr($this->option_key_name); ?>[title]" value="1" />&nbsp;
		<label for="seto_title"><?php esc_html_e( 'Search in Title', 'search-tools' ); ?></label>
		<br />
		<input type="hidden" name="<?php echo esc_attr($this->option_key_name); ?>[content]" value="0" />
		<input <?php checked( esc_attr($this->seto_settings['content']) ); ?> type="checkbox" id="seto_content" name="<?php echo esc_attr($this->option_key_name); ?>[content]" value="1" />&nbsp;
		<label for="seto_content"><?php esc_html_e( 'Search in Content', 'search-tools' ); ?></label>
		<br />
		<input type="hidden" name="<?php echo esc_attr($this->option_key_name); ?>[excerpt]" value="0" />
		<input <?php checked( esc_attr($this->seto_settings['excerpt']) ); ?> type="checkbox" id="seto_excerpt" name="<?php echo esc_attr( $this->option_key_name ); ?>[excerpt]" value="1" />&nbsp;
		<label for="seto_excerpt"><?php esc_html_e( 'Search in Excerpt', 'search-tools' ); ?></label>
		<?php
	}

	/**
	 * Meta keys checkboxes.
	 *
	 * @since 1.0.0
	 */
	public function custom_field_name_list() {
		$meta_keys = $this->fields();
		if ( ! empty( $meta_keys ) ) {
			?>
			<select class="seto-select2" multiple="multiple" name="<?php echo esc_attr($this->option_key_name); ?>[meta_keys][]">
			<?php
			foreach ( (array) $meta_keys as $meta_key ) {
				?>
				<option <?php echo esc_attr( $this->seto_checked( $meta_key, $this->seto_settings['meta_keys'], true ) ); ?> value="<?php echo esc_attr($meta_key); ?>"><?php echo esc_html($meta_key); ?></option>
				<?php
			}
			?>
			</select>
			<?php
		} else {
			?>
			<em><?php esc_html_e( 'No meta key found!', 'search-tools' ); ?></em>
			<?php
		}
	}

	/**
	 * Taxonomies checkbox.
	 *
	 * @since 1.0.0
	 */
	public function taxonomies_settings() {

		/**
		 * Filter taxonomies arguments.
		 *
		 * @since 1.0.0
		 * @param array arguments array.
		 */
		$tax_args = apply_filters(
			'seto_tax_args',
			array(
				'show_ui' => true,
				'public'  => true,
			)
		);

		/**
		 * Filter taxonomy list return by get_taxonomies function.
		 *
		 * @since 1.0.0
		 * @param $all_taxonomies Array of taxonomies.
		 */
		$all_taxonomies = apply_filters( 'seto_tax', get_taxonomies( $tax_args, 'objects' ) );
		if ( is_array( $all_taxonomies ) && ! empty( $all_taxonomies ) ) {
			?>
			<select multiple="multiple" class="seto-select2" name="<?php echo esc_attr($this->option_key_name); ?>[taxonomies][]">
			<?php
			foreach ( $all_taxonomies as $tax_name => $tax_obj ) {
				?>
				<option <?php echo esc_attr( $this->seto_checked( $tax_name, $this->seto_settings['taxonomies'], true ) ); ?> value="<?php echo esc_attr($tax_name); ?>">
				<?php echo ! empty( $tax_obj->labels->name ) ? esc_html($tax_obj->labels->name) : esc_html($tax_name); ?>
				</option>
				<?php
			}
			?>
			</select>
			<?php
		} else {
			?>
			<em><?php esc_html_e( 'No public taxonomy found!', 'search-tools' ); ?></em>
			<?php
		}
	}

	/**
	 * Author settings meta box.
	 *
	 * @since 1.0.0
	 */
	public function author_settings() {
		?>
		<input name="<?php echo esc_attr( $this->option_key_name ); ?>[authors]" type="hidden" value="0" />
		<input id="include_authors" <?php checked( esc_attr($this->seto_settings['authors'] ) ); ?> type="checkbox" value="1" name="<?php echo esc_attr($this->option_key_name); ?>[authors]" />
		<label for="include_authors"><?php esc_html_e( 'Search in Author display name', 'search-tools' ); ?></label>
		<p class="description"><?php esc_html_e( 'If checked then it will display those results whose Author "Display name" match the search terms.', 'search-tools' ); ?></p>
		<?php
	}

	/**
	 * Post type checkboexes.
	 *
	 * @since 1.0.0
	 */
	public function post_types_settings() {

		/**
		 * Filter post type arguments.
		 *
		 * @since 1.0.0
		 * @param array arguments array.
		 */
		$post_types_args = apply_filters(
			'seto_post_types_args',
			array(
				'show_ui' => true,
				'public'  => true,
			)
		);

		/**
		 * Filter post type array return by get_post_types function.
		 *
		 * @since 1.0.0
		 * @param array $all_post_types Array of post types.
		 */
		$all_post_types = apply_filters( 'seto_post_types', get_post_types( $post_types_args, 'objects' ) );

		if ( is_array( $all_post_types ) && ! empty( $all_post_types ) ) {
			?>
			<select multiple="multiple" class="seto-select2" name="<?php echo esc_attr($this->option_key_name); ?>[post_types][]">
			<?php
			foreach ( $all_post_types as $post_name => $post_obj ) {
				?>
				<option <?php echo esc_attr($this->seto_checked( $post_name, $this->seto_settings['post_types'], true )); ?> value="<?php echo esc_attr($post_name); ?>" >
				<?php echo isset( $post_obj->labels->name ) ? esc_html($post_obj->labels->name) : esc_html($post_name); ?>
				</option>
				<?php
			}
			?>
			</select>
			<p class="description">
				<?php
					esc_html_e( 'If you are selecting Media post type then save the settings once to enable more media settings.', 'search-tools' );
				?>
			</p>
			<?php
		} else {
			?>
			<em><?php esc_html_e( 'No public post type found!', 'search-tools' ); ?></em>
			<?php
		}
	}

	/**
	 * Terms relation type meta box.
	 *
	 * @since 1.0.0
	 */
	public function terms_relation_type() {
		?>
		<select <?php echo esc_attr($this->disabled( $this->seto_settings['exact_match'], 'yes' )); ?> id="terms_relation" name="<?php echo esc_attr($this->option_key_name); ?>[terms_relation]">
			<option <?php selected( esc_attr($this->seto_settings['terms_relation']), 1 ); ?> value="1"><?php esc_html_e( 'AND', 'search-tools' ); ?></option>
			<option <?php selected( esc_attr($this->seto_settings['terms_relation']), 2 ); ?> value="2"><?php esc_html_e( 'OR', 'search-tools' ); ?></option>
		</select>
		<p class="description">
			<?php
			if ( 'yes' === $this->seto_settings['exact_match'] ) {
				esc_html_e( 'This option is disabled because you have selected "Match the search term exactly".  When using the exact match option, the sentence is not broken into terms instead the whole sentence is matched thus this option has no meaning.', 'search-tools' );
			} else {
				esc_html_e( 'Type of query relation between search terms. e.g. someone searches for "my query" then define the relation between "my" and "query". The default value is AND.', 'search-tools' );
			}
			?>
		</p>
		<?php
	}

	/**
	 * Exclude older results.
	 *
	 * @since 1.0.0
	 */
	public function exclude_results() {
		?>
		<input class="regular-text" type="text" value="<?php echo esc_attr( $this->seto_settings['exclude_date'] ); ?>" name="<?php echo esc_attr($this->option_key_name); ?>[exclude_date]" id="seto_exclude_date" />
		<p class="description"><?php esc_html_e( 'Contents will not appear in search results older than this date OR leave blank to disable this feature.', 'search-tools' ); ?></p>
		<?php
	}

	/**
	 * Posts per search results page.
	 *
	 * @since 1.0.0
	 */
	public function posts_per_page() {
		?>
		<input min="-1" class="small-text" type="number" value="<?php echo esc_attr( $this->seto_settings['posts_per_page'] ); ?>" name="<?php echo esc_attr($this->option_key_name); ?>[posts_per_page]" id="seto_posts_per_page" />
		<p class="description"><?php esc_html_e( 'Number of posts to display on search result page OR leave blank for default value.', 'search-tools' ); ?></p>
		<?php
	}

	/**
	 * Search results order.
	 *
	 * @since 1.0.0
	 */
	public function search_results_order() {
		?>
		<select id="search_results_order" name="<?php echo esc_attr($this->option_key_name); ?>[orderby]">
			<option <?php selected( esc_attr($this->seto_settings['orderby']), '' ); ?> value=""><?php esc_html_e( 'Relevance', 'search-tools' ); ?></option>
			<option <?php selected( esc_attr($this->seto_settings['orderby']), 'date' ); ?> value="date"><?php esc_html_e( 'Date', 'search-tools' ); ?></option>
			<option <?php selected( esc_attr($this->seto_settings['orderby']), 'modified' ); ?> value="modified"><?php esc_html_e( 'Last Modified Date', 'search-tools' ); ?></option>
			<option <?php selected( esc_attr($this->seto_settings['orderby']), 'title' ); ?> value="title"><?php esc_html_e( 'Post Title', 'search-tools' ); ?></option>
			<option <?php selected( esc_attr($this->seto_settings['orderby']), 'name' ); ?> value="name"><?php esc_html_e( 'Post Slug', 'search-tools' ); ?></option>
			<option <?php selected( esc_attr($this->seto_settings['orderby']), 'type' ); ?> value="type"><?php esc_html_e( 'Post Type', 'search-tools' ); ?></option>
			<option <?php selected( esc_attr($this->seto_settings['orderby']), 'comment_count' ); ?> value="comment_count"><?php esc_html_e( 'Number of Comments', 'search-tools' ); ?></option>
			<option <?php selected( esc_attr($this->seto_settings['orderby']), 'rand' ); ?> value="rand"><?php esc_html_e( 'Random', 'search-tools' ); ?></option>
		</select>
		<p class="description">
			<?php
			/* translators: %1$s: anchor tag opening, %2$s: anchor tag closed. */
			echo sprintf( esc_html__( 'Sort search results based on metadata of items. The default value is %1$sRelevance%2$s.', 'search-tools' ), '<a href="https://developer.wordpress.org/reference/classes/wp_query/#order-orderby-parameters" target="_blank">', '</a>' );
			?>
		</p>
		<br />
		<label><input <?php echo esc_attr($this->seto_checked( $this->seto_settings['order'], array( 'DESC' ) )); ?> type="radio" value="DESC" name="<?php echo esc_attr( $this->option_key_name); ?>[order]" /><?php esc_html_e( 'Descending', 'search-tools' ); ?></label>
		<label><input <?php echo esc_attr($this->seto_checked( $this->seto_settings['order'], array( 'ASC' ) )); ?> type="radio" value="ASC" name="<?php echo esc_attr($this->option_key_name); ?>[order]" /><?php esc_html_e( 'Ascending', 'search-tools' ); ?></label>
		<p class="description"><?php esc_html_e( 'Order the sorted search items in Descending or Ascending. Default is Descending.', 'search-tools' ); ?></p>
		<?php
	}

	/**
	 * Select exact or partial term matching.
	 *
	 * @since 1.0.0
	 */
	public function exact_search() {
		?>
		<label>
			<input <?php echo esc_attr($this->seto_checked( $this->seto_settings['exact_match'], array( 'yes' ) )); ?> type="radio" value="yes" name="<?php echo esc_attr($this->option_key_name); ?>[exact_match]" /><?php esc_html_e( 'Yes', 'search-tools' ); ?>
		</label>
		<label>
			<input <?php echo esc_attr($this->seto_checked( $this->seto_settings['exact_match'], array( 'no' ) )); ?> type="radio" value="no" name="<?php echo esc_attr($this->option_key_name); ?>[exact_match]" /><?php esc_html_e( 'No', 'search-tools' ); ?>
		</label>

		<p class="description"><?php esc_html_e( 'Whether to match search term exactly or partially e.g. If someone search "Word" it will display items matching "WordPress" or "Word" but if you select Yes then it will display items only matching "Word". The default value is No.', 'search-tools' ); ?></p>
		<?php
	}

	/**
	 * Section content before displaying search settings.
	 *
	 * @since 1.0.0
	 */
	public function section_content() {

	}

	/**
	 * Select mime type.
	 *
	 * @since 1.0.0
	 */
	public function media_types() {
		?>
		<select multiple="multiple" class="st-select2" name="<?php esc_attr($this->option_key_name); ?>[media_types][]">
			<?php
			foreach ( (array) get_allowed_mime_types() as $ext => $type ) {
				?>
				<option <?php esc_attr($this->seto_checked( $type, $this->seto_settings['media_types'], true )); ?> value="<?php echo esc_attr( $type ); ?>" >
					<?php echo esc_html($ext); ?>
				</option>
				<?php
			}
			?>
		</select>
		<p class="description"><?php esc_html_e( 'Select the media types to limit the results by type. Leave blank to search all media types.', 'search-tools' ); ?></p>
		<?php
	}

	/**
	 * Return checked or selected if value exist in array.
	 *
	 * @since 1.0.0
	 * @param mixed $value value to check against array.
	 * @param array $array haystack array.
	 * @param bool  $selected Set to <code>true</code> when using in select else <code>false</code>.
	 * @return string checked="checked" or selected="selected" or blank string.
	 */
	public function seto_checked( $value = false, $array = array(), $selected = false ) {
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
	 * @since 1.0.0
	 * @param mixed $first_value First value to compare.
	 * @param mixed $second_value Second value to compare.
	 * @return string disabled="disabled" or blank string.
	 */
	public function disabled( $first_value, $second_value = true ) {
		if ( $first_value == $second_value ) { // phpcs:ignore loose comparison
			return 'disabled="disabled"';
		}

		return '';
	}

	/**
	 * Get plugin options.
	 *
	 * @since 1.0.0
	 */
	public function seto_options() {

		if ( ! empty( $this->seto_settings ) ) {
			return $this->seto_settings;
		}

		$settings = wp_parse_args( get_option( $this->option_key_name ), $this->default_options() );

		return $settings;
	}
}
