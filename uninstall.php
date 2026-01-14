<?php
if ( ! defined('WP_UNINSTALL_PLUGIN') ) exit;

$delete = (int) get_option('waf_delete_data_on_uninstall', 0);

// Borrar opciones del plugin
delete_option('waf_settings');
delete_option('waf_delete_data_on_uninstall');

// Borrar evento cron si existe
$hook = 'waf_prune_logs_event';
$ts = wp_next_scheduled($hook);
if ($ts) {
	wp_unschedule_event($ts, $hook);
}

// Si el admin lo pidió explícitamente, borrar tabla
if ($delete === 1) {
	global $wpdb;
	$table = $wpdb->prefix . 'audit_footprint_logs';
	$wpdb->query("DROP TABLE IF EXISTS $table");
}