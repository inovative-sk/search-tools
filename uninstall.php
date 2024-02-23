<?php
// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

delete_option( 'st_options' );
delete_option( 'st_db_version' );
delete_option( 'search_tools_plugin_activation_date' );

global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}st_search_insights" );