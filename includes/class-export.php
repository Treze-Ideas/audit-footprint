<?php
defined('ABSPATH') || exit;

class WAF_Export {

	public static function handle() {
		if ( ! current_user_can('audit_footprint_view') ) return;
		if ( ! isset($_GET['waf_export']) ) return;

		check_admin_referer('waf_export');

		$type = sanitize_text_field($_GET['waf_export']); // csv | json
		if (!in_array($type, ['csv','json'], true)) return;

		global $wpdb;
		$table = WAF_DB::table_name();
		$limit = (int) WAF_Settings::get('export_limit', 5000);

		// Respetamos filtros básicos de la tabla si vienen en querystring
		$where = 'WHERE 1=1';
		$params = [];

		$search = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
		if ($search !== '') {
			$where .= " AND (user_login LIKE %s OR action LIKE %s OR description LIKE %s)";
			$like = '%' . $wpdb->esc_like($search) . '%';
			$params[] = $like; $params[] = $like; $params[] = $like;
		}

		$f_action = isset($_GET['waf_action']) ? sanitize_text_field($_GET['waf_action']) : '';
		if ($f_action !== '') { $where .= " AND action = %s"; $params[] = $f_action; }

		$f_user = isset($_GET['waf_user']) ? sanitize_text_field($_GET['waf_user']) : '';
		if ($f_user !== '') { $where .= " AND user_login = %s"; $params[] = $f_user; }

		$sql = "SELECT * FROM $table $where ORDER BY created_at DESC LIMIT %d";
		$params[] = $limit;

		$rows = $params
			? $wpdb->get_results($wpdb->prepare($sql, $params), ARRAY_A)
			: $wpdb->get_results($sql, ARRAY_A);

		if ($type === 'csv') self::export_csv($rows);
		if ($type === 'json') self::export_json($rows);

		exit;
	}

	private static function export_csv($rows) {
		nocache_headers();
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename="wp-audit-footprint.csv"');

		$out = fopen('php://output', 'w');

		if (!empty($rows)) {
			fputcsv($out, array_keys($rows[0]));
			foreach ($rows as $row) {
				fputcsv($out, $row);
			}
		}

		fclose($out);
	}

	private static function export_json($rows) {
		nocache_headers();
		header('Content-Type: application/json; charset=utf-8');
		header('Content-Disposition: attachment; filename="wp-audit-footprint.json"');

		echo wp_json_encode($rows, JSON_PRETTY_PRINT);
	}
}