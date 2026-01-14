<?php
defined('ABSPATH') || exit;

class WAF_Hooks_Auth {

	public function __construct() {
		add_action('wp_login', [$this,'login'], 10, 2);
		add_action('clear_auth_cookie', [$this,'logout']);
		add_action('wp_login_failed', [$this,'login_failed']);
		add_action('password_reset', [$this,'password_reset']);
		add_action('set_user_role', [$this,'role_change'], 10, 3);
	}

	public function login($user_login, $user) {
		wp_set_current_user($user->ID);
		WAF_Logger::log('login', ['description' => 'User logged in'], $user);
	}

	public function logout() {
		$user_id = get_current_user_id();
		if ($user_id) {
			$user = get_user_by('id', $user_id);
			if ($user) WAF_Logger::log('logout', ['description' => 'User logged out'], $user);
		}
	}

	public function login_failed($username) {
		WAF_Logger::log('login_failed', ['description' => 'Failed login: ' . sanitize_text_field($username)]);
	}

	public function password_reset($user) {
		if ($user) WAF_Logger::log('password_reset', ['description' => 'Password reset'], $user);
	}

	public function role_change($user_id, $role, $old_roles) {
		$user = get_user_by('id', (int)$user_id);
		$from = is_array($old_roles) ? implode(',', array_map('sanitize_text_field', $old_roles)) : '';
		$to   = sanitize_text_field($role);

		if ($user) {
			WAF_Logger::log('role_changed', [
				'description' => 'Role changed from ' . $from . ' to ' . $to
			], $user);
		}
	}
}