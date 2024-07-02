<?php
namespace SearchToolsPlugin;

/*
	This file is modified part of "Disable Search plugin" which is released under "GNU GENERAL PUBLIC LICENSE version 2".
	See file https://github.com/coffee2code/disable-search/blob/master/LICENSE for full license details.
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class SETO_Disable
{
    /**
	 * Constructor
	 *
     * @since   1.2.0
     * 
	 * @return void
	 */
	private function __construct() {}

	public function __wakeup() {
		/* translators: %s: Name of plugin class. */
		throw new \Error( sprintf( __( '%s cannot be unserialized.', 'search-tools' ), __CLASS__ ) );
	}

    public static function disable_search() {
		// Register hooks.
		if ( ! is_admin() ) {
			add_action( 'parse_query',              array( __CLASS__, 'parse_query' ), 5 );
		}
		add_filter( 'get_search_form',              '__return_empty_string', 999 );

		add_action( 'admin_bar_menu',               array( __CLASS__, 'admin_bar_menu' ), 11 );

		add_filter( 'disable_wpseo_json_ld_search', '__return_true' );

		// Disable core search block.
		add_action( 'init',                         array( __CLASS__, 'disable_core_search_block' ), 11 );
		add_action( 'enqueue_block_editor_assets',  array( __CLASS__, 'enqueue_block_editor_assets' ) );
    }

	/**
	 * Unregisters the core/search block (at least for PHP).
	 *
	 * Though this technically works (the block gets unregistered), it doesn't
	 * actually disable the block, which is at least still available via JS and
	 * thus is functionally equivalent to this doing nothing.
	 *
	 * The use of the `'allowed_block_types_all'` filter seems ideal for this
	 * sort of thing, but it has its issues at present (see associated link).
	 *
	 * @link https://github.com/WordPress/gutenberg/issues/12931
	 * @since 1.2.0
	 */
	public static function disable_core_search_block() {
		if ( function_exists( 'unregister_block_type' ) ) {
			$block = 'core/search';
			if ( \WP_Block_Type_Registry::get_instance()->is_registered( $block ) ) {
				unregister_block_type( $block );
			}
		}
	}

	/**
	 * Enqueues block editor assets, notable to disable the search block.
	 *
	 * @since 1.2.0
	 */
	public static function enqueue_block_editor_assets() {
		wp_enqueue_script( 'seto-disable-search-js', plugins_url( '/assets/js/disable-search.js', __FILE__ ), array( 'wp-blocks', 'wp-dom' ), '1.0', true );
	}

	/**
	 * Unsets all search-related variables in WP_Query object and sets the
	 * request as a 404 if a search was attempted.
	 *
	 * @param WP_Query $obj A query object.
	 * 
	 * @since 1.2.0 
	 */
	public static function parse_query( $obj ) {
		if ( $obj->is_search && $obj->is_main_query() ) {
			unset( $_GET['s'] );
			unset( $_POST['s'] );
			unset( $_REQUEST['s'] );
			unset( $obj->query['s'] );
			$obj->set( 's', '' );
			$obj->is_search = false;
			$obj->set_404();
			status_header( 404 );
			nocache_headers();
		}
	}

	/**
	 * Removes the search item from the admin bar.
	 *
	 * @since 1.2
	 *
	 * @param WP_Admin_Bar $wp_admin_bar The WP admin bar object.
	 */
	public static function admin_bar_menu( $wp_admin_bar ) {
		$wp_admin_bar->remove_menu( 'search' );
	}
	
}