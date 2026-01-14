<?php
defined('ABSPATH') || exit;

class WAF_Admin_UI {

	public function __construct() {
		add_action('admin_menu', [$this,'register_menu']);
		add_action('admin_init', ['WAF_Export','handle']);
	}

	public function register_menu() {

		add_submenu_page(
			'tools.php',
			'WP Audit Footprint',
			'Audit Footprint',
			'audit_footprint_view',
			'wp-audit-footprint',
			[$this,'render_page']
		);

		add_submenu_page(
			'tools.php',
			'Audit Footprint – Ajustes',
			'Audit Footprint – Ajustes',
			'audit_footprint_view',
			'waf-settings',
			[$this,'render_settings']
		);
	}

	public function render_page() {
		if ( ! current_user_can('audit_footprint_view') ) {
			wp_die('No tienes permisos para ver esta página.');
		}

		echo '<div class="wrap waf-admin">';
		echo '<h1>WP Audit Footprint</h1>';
		echo '<p class="description">Every action leaves a trace.</p>';

		// Botones superiores
		$export_url_csv = wp_nonce_url(admin_url('tools.php?page=wp-audit-footprint&waf_export=csv'), 'waf_export');
		$export_url_json = wp_nonce_url(admin_url('tools.php?page=wp-audit-footprint&waf_export=json'), 'waf_export');
		$settings_url = admin_url('tools.php?page=waf-settings');

		echo '<div class="waf-toolbar">';
		echo '<a class="button button-primary" href="'.$export_url_csv.'">Exportar CSV</a> ';
		echo '<a class="button" href="'.$export_url_json.'">Exportar JSON</a> ';
		echo '<a class="button button-secondary" href="'.$settings_url.'">Ajustes</a>';
		echo '</div>';

		$table = new WAF_Admin_Table();
		$table->process_bulk_action();
		$table->prepare_items();
		$table->display();

		echo '</div>';
	}

	public function render_settings() {
		if ( ! current_user_can('audit_footprint_view') ) {
			wp_die('No tienes permisos.');
		}

		echo '<div class="wrap">';
		echo '<h1>Ajustes – WP Audit Footprint</h1>';
		echo '<form method="post" action="options.php">';

		settings_fields('waf_settings_group');
		do_settings_sections('waf_settings');
		submit_button();

		echo '</form>';
		echo '</div>';
	}
}