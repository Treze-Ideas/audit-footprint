<?php
defined('ABSPATH') || exit;

class WAF_Logger {

	public static function normalize_ip($ip) {
		$mode = WAF_Settings::get('ip_mode', 'masked');

		if ($mode === 'off') return null;
		if (!$ip) return null;

		if ($mode === 'full') return $ip;

		if ($mode === 'hashed') {
			// hash con salt del sitio (irreversible)
			return hash_hmac('sha256', $ip, wp_salt('auth'));
		}

		// masked (default)
		if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
			$parts = explode('.', $ip);
			return $parts[0] . '.' . $parts[1] . '.xxx.xxx';
		}

		// IPv6: enmascarado simple
		if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
			$chunks = explode(':', $ip);
			$chunks = array_slice($chunks, 0, 3);
			return implode(':', $chunks) . ':xxxx:xxxx:xxxx:xxxx:xxxx';
		}

		return $ip;
	}

	public static function log($action, $args = [], $user = null) {
		global $wpdb;

		if (!$user) $user = wp_get_current_user();

		$ip_raw = isset($_SERVER['REMOTE_ADDR']) ? (string)$_SERVER['REMOTE_ADDR'] : null;
		$ip = self::normalize_ip($ip_raw);

		$ua = isset($_SERVER['HTTP_USER_AGENT']) ? (string)$_SERVER['HTTP_USER_AGENT'] : null;

		$data = [
			'user_id'     => $user && $user->ID ? (int)$user->ID : null,
			'user_login'  => $user && $user->user_login ? sanitize_text_field($user->user_login) : 'guest',
			'user_role'   => ($user && !empty($user->roles)) ? sanitize_text_field($user->roles[0]) : 'none',
			'action'      => sanitize_text_field($action),
			'object_type' => isset($args['object_type']) ? sanitize_text_field($args['object_type']) : null,
			'object_id'   => isset($args['object_id']) ? (int)$args['object_id'] : null,
			'description' => isset($args['description']) ? wp_kses_post($args['description']) : null,
			'ip_address'  => $ip ? sanitize_text_field($ip) : null,
			'user_agent'  => $ua ? sanitize_textarea_field($ua) : null,
			'created_at'  => current_time('mysql'),
		];

		$wpdb->insert(WAF_DB::table_name(), $data);
	}
}