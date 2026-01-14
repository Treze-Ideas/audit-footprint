<?php
defined('ABSPATH') || exit;

class WAF_Capabilities {

	public static function add_caps() {
		$role = get_role('administrator');
		if ($role && ! $role->has_cap('audit_footprint_view')) {
			$role->add_cap('audit_footprint_view');
		}
	}
}