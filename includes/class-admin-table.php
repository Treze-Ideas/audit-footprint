<?php
defined('ABSPATH') || exit;

if ( ! class_exists('WP_List_Table') ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class WAF_Admin_Table extends WP_List_Table {

	public function get_columns() {
		return [
			'cb'          => '<input type="checkbox" />',
			'created_at' => 'Fecha',
			'user_login' => 'Usuario',
			'action'     => 'Acción',
			'description'=> 'Descripción',
			'ip_address' => 'IP'
		];
	}

	protected function column_cb($item) {
		return sprintf('<input type="checkbox" name="log_id[]" value="%d" />', $item['id']);
	}

	public function prepare_items() {
		global $wpdb;

		$table = WAF_DB::table_name();
		$per_page = 25;
		$paged = max(1, $this->get_pagenum());
		$offset = ($paged - 1) * $per_page;

		$where = 'WHERE 1=1';

		if (!empty($_GET['waf_action'])) {
			$where .= $wpdb->prepare(" AND action = %s", sanitize_text_field($_GET['waf_action']));
		}

		if (!empty($_GET['waf_user'])) {
			$where .= $wpdb->prepare(" AND user_login = %s", sanitize_text_field($_GET['waf_user']));
		}

		$total_items = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table $where");

		$this->items = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $table $where ORDER BY created_at DESC LIMIT %d OFFSET %d",
				$per_page,
				$offset
			),
			ARRAY_A
		);

		$this->_column_headers = [
			$this->get_columns(),
			[],
			[]
		];

		$this->set_pagination_args([
			'total_items' => $total_items,
			'per_page'    => $per_page
		]);
	}

	protected function column_action($item) {
		$action = esc_html($item['action']);
		$cls = 'waf-badge ' . esc_attr($this->get_action_class($action));
		return '<span class="'.$cls.'">'.strtoupper(str_replace('_',' ',$action)).'</span>';
	}

	protected function column_default($item, $column_name) {
		return esc_html($item[$column_name]);
	}

	protected function get_bulk_actions() {
		return [
			'delete' => 'Borrar logs seleccionados'
		];
	}

	public function process_bulk_action() {
		if ($this->current_action() === 'delete' && !empty($_POST['log_id']) && check_admin_referer('waf_bulk_delete')) {
			global $wpdb;
			$table = WAF_DB::table_name();

			foreach ((array) $_POST['log_id'] as $id) {
				$wpdb->delete($table, ['id' => (int)$id]);
			}
		}
	}

	protected function extra_tablenav($which) {
		if ($which !== 'top') return;

		global $wpdb;
		$table = WAF_DB::table_name();

		$actions = $wpdb->get_col("SELECT DISTINCT action FROM $table ORDER BY action ASC");
		$users   = $wpdb->get_col("SELECT DISTINCT user_login FROM $table ORDER BY user_login ASC");

		$sel_action = $_GET['waf_action'] ?? '';
		$sel_user   = $_GET['waf_user'] ?? '';

		echo '<div class="alignleft actions waf-filters">';

		echo '<select name="waf_action">';
		echo '<option value="">Todas las acciones</option>';
		foreach ($actions as $a) {
			printf('<option value="%s" %s>%s</option>', esc_attr($a), selected($sel_action, $a, false), esc_html($a));
		}
		echo '</select>';

		echo '<select name="waf_user">';
		echo '<option value="">Todos los usuarios</option>';
		foreach ($users as $u) {
			printf('<option value="%s" %s>%s</option>', esc_attr($u), selected($sel_user, $u, false), esc_html($u));
		}
		echo '</select>';

		submit_button('Filtrar', '', 'filter_action', false);

		wp_nonce_field('waf_bulk_delete');

		echo '</div>';
	}

	private function get_action_class($action) {
		if (strpos($action, 'login') !== false) return 'waf-green';
		if (strpos($action, 'logout') !== false) return 'waf-gray';
		if (strpos($action, 'failed') !== false) return 'waf-red';
		if (strpos($action, 'error') !== false) return 'waf-red';
		if (strpos($action, 'update') !== false) return 'waf-purple';
		if (strpos($action, 'theme') !== false) return 'waf-blue';
		if (strpos($action, 'plugin') !== false) return 'waf-orange';
		return 'waf-default';
	}
}