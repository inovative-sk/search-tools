<?php
// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

delete_option( 'seto_options' );
delete_option( 'seto_db_version' );
delete_option( 'seto_plugin_activation_date' );

global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}seto_search_insights" );