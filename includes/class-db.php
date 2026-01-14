<?php
defined('ABSPATH') || exit;

class WAF_DB {

	public static function table_name() {
		global $wpdb;
		return $wpdb->prefix . 'audit_footprint_logs';
	}

	public static function install() {
		global $wpdb;
		$table = self::table_name();
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT UNSIGNED NULL,
			user_login VARCHAR(60) NULL,
			user_role VARCHAR(100) NULL,
			action VARCHAR(100) NOT NULL,
			object_type VARCHAR(100) NULL,
			object_id BIGINT NULL,
			description TEXT NULL,
			ip_address VARCHAR(64) NULL,
			user_agent TEXT NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY action (action),
			KEY created_at (created_at)
		) $charset;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta($sql);
	}

	public static function prune_older_than_days($days) {
		if (!$days || $days <= 0) return;

		global $wpdb;
		$table = self::table_name();

		$cutoff = gmdate('Y-m-d H:i:s', time() - (int)$days * DAY_IN_SECONDS);

		// created_at está en hora WP; usamos string compare, suficiente aquí.
		$wpdb->query(
			$wpdb->prepare("DELETE FROM $table WHERE created_at < %s", $cutoff)
		);
	}
}