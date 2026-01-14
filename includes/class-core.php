<?php
defined('ABSPATH') || exit;

class WAF_Core {

	public static function init() {
		// Carga módulos
		new WAF_Hooks_Auth();
		new WAF_Hooks_Content();
		new WAF_Hooks_System();
		new WAF_Hooks_Errors();

		new WAF_Settings();
		new WAF_Admin_UI();

		add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_assets']);
	}

	public static function enqueue_admin_assets($hook) {
		// Solo en páginas del plugin
		if (strpos($hook, 'wp-audit-footprint') === false) return;

		wp_enqueue_style('waf-admin', WAF_URL . 'assets/css/admin.css', [], WAF_VERSION);
		wp_enqueue_script('waf-admin', WAF_URL . 'assets/js/admin.js', ['jquery'], WAF_VERSION, true);
	}

	public static function activate() {
		WAF_DB::install();
		WAF_Capabilities::add_caps();
		WAF_Settings::ensure_defaults();
		WAF_Cron::schedule();
	}

	public static function deactivate() {
		WAF_Cron::unschedule();
	}
}