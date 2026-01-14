<?php
defined('ABSPATH') || exit;

class WAF_Cron {

	const HOOK = 'waf_prune_logs_event';

	public static function schedule($reschedule = false) {
		if ($reschedule) {
			self::unschedule();
		}

		if (!wp_next_scheduled(self::HOOK)) {
			wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', self::HOOK);
		}

		add_action(self::HOOK, [__CLASS__, 'run_prune']);
	}

	public static function unschedule() {
		$ts = wp_next_scheduled(self::HOOK);
		if ($ts) wp_unschedule_event($ts, self::HOOK);
	}

	public static function run_prune() {
		$days = (int) WAF_Settings::get('retention_days', 90);
		if ($days > 0) {
			WAF_DB::prune_older_than_days($days);
		}
	}
}