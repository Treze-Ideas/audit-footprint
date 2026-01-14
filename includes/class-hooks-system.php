<?php
defined('ABSPATH') || exit;

class WAF_Hooks_System {

	public function __construct() {
		add_action('activated_plugin', [$this,'plugin_activated']);
		add_action('deactivated_plugin', [$this,'plugin_deactivated']);
		add_action('switch_theme', [$this,'theme_switched'], 10, 3);
		add_action('upgrader_process_complete', [$this,'upgrader_complete'], 10, 2);
	}

	public function plugin_activated($plugin){
		WAF_Logger::log('plugin_activated', [
			'description' => 'Activated plugin: ' . $plugin
		], wp_get_current_user());
	}

	public function plugin_deactivated($plugin){
		WAF_Logger::log('plugin_deactivated', [
			'description' => 'Deactivated plugin: ' . $plugin
		], wp_get_current_user());
	}

	public function theme_switched($new_name, $new_theme, $old_theme){
		WAF_Logger::log('theme_switched', [
			'description' => 'Theme changed from ' . $old_theme->get('Name') . ' to ' . $new_theme->get('Name')
		], wp_get_current_user());
	}

	public function upgrader_complete($upgrader, $data){

		if (empty($data['type'])) return;

		$type = $data['type']; // plugin, theme, core
		$action = $data['action']; // update, install, delete

		$result = isset($upgrader->skin->result) ? $upgrader->skin->result : true;
		$status = is_wp_error($result) ? 'failed' : 'success';

		$what = $type . '_' . $action . '_' . $status;

		$desc = strtoupper($type) . ' ' . $action . ' ' . $status;

		if (!empty($data['plugins'])) {
			$desc .= ': ' . implode(', ', $data['plugins']);
		}

		if (!empty($data['themes'])) {
			$desc .= ': ' . implode(', ', $data['themes']);
		}

		WAF_Logger::log($what, [
			'description' => $desc
		], wp_get_current_user());
	}
}