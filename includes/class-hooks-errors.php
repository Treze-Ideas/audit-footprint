<?php
defined('ABSPATH') || exit;

class WAF_Hooks_Errors {

	public function __construct() {
		register_shutdown_function([$this, 'catch_fatal_error']);
	}

	public function catch_fatal_error() {
		$error = error_get_last();

		if (!$error) return;

		$fatal_types = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];

		if (!in_array($error['type'], $fatal_types)) return;

		WAF_Logger::log('php_fatal_error', [
			'description' => sprintf(
				'%s in %s on line %d',
				$error['message'],
				$error['file'],
				$error['line']
			)
		]);
	}
}

new WAF_Hooks_Errors();