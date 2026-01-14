<?php
defined('ABSPATH') || exit;

class WAF_Settings {

	const OPTION_KEY = 'waf_settings';

	public static function ensure_defaults() {
		$defaults = [
			'ip_mode' => 'masked',  // full | masked | hashed | off
			'retention_days' => 90, // 0 = no borrar
			'export_limit' => 5000, // límite anti-petadas
		];

		$current = get_option(self::OPTION_KEY);
		if (!is_array($current)) {
			update_option(self::OPTION_KEY, $defaults, false);
			return;
		}

		$merged = array_merge($defaults, $current);
		update_option(self::OPTION_KEY, $merged, false);
	}

	public static function get($key, $fallback = null) {
		$opts = get_option(self::OPTION_KEY, []);
		return isset($opts[$key]) ? $opts[$key] : $fallback;
	}

	public function __construct() {
		add_action('admin_init', [$this, 'register']);
	}

	public function register() {
		register_setting('waf_settings_group', self::OPTION_KEY, [$this, 'sanitize']);

		add_settings_section(
			'waf_main',
			'Ajustes',
			function(){ echo '<p>Privacidad, retención y exportación.</p>'; },
			'waf_settings'
		);

		add_settings_field('ip_mode', 'Modo IP', [$this,'field_ip_mode'], 'waf_settings', 'waf_main');
		add_settings_field('retention_days', 'Retención de logs (días)', [$this,'field_retention'], 'waf_settings', 'waf_main');
		add_settings_field('export_limit', 'Límite exportación', [$this,'field_export_limit'], 'waf_settings', 'waf_main');
	}

	public function sanitize($input) {
		$out = [];

		$ip_mode = isset($input['ip_mode']) ? (string)$input['ip_mode'] : 'masked';
		$allowed_ip = ['full','masked','hashed','off'];
		$out['ip_mode'] = in_array($ip_mode, $allowed_ip, true) ? $ip_mode : 'masked';

		$ret = isset($input['retention_days']) ? (int)$input['retention_days'] : 90;
		$out['retention_days'] = max(0, $ret);

		$limit = isset($input['export_limit']) ? (int)$input['export_limit'] : 5000;
		$out['export_limit'] = min(20000, max(100, $limit));

		// Reprogramar cron si cambia retención
		WAF_Cron::schedule(true);

		return $out;
	}

	public function field_ip_mode() {
		$val = esc_attr(self::get('ip_mode','masked'));
		?>
		<select name="<?php echo esc_attr(self::OPTION_KEY); ?>[ip_mode]">
			<option value="full"   <?php selected($val,'full'); ?>>Completa</option>
			<option value="masked" <?php selected($val,'masked'); ?>>Enmascarada (recomendado)</option>
			<option value="hashed" <?php selected($val,'hashed'); ?>>Hasheada (irreversible)</option>
			<option value="off"    <?php selected($val,'off'); ?>>No guardar IP</option>
		</select>
		<p class="description">Para cumplir GDPR: usa “Enmascarada” o “Hasheada”.</p>
		<?php
	}

	public function field_retention() {
		$val = (int) self::get('retention_days', 90);
		?>
		<input type="number" min="0" step="1" name="<?php echo esc_attr(self::OPTION_KEY); ?>[retention_days]" value="<?php echo esc_attr($val); ?>" />
		<p class="description">0 = no borrar automáticamente. Recomendado 90.</p>
		<?php
	}

	public function field_export_limit() {
		$val = (int) self::get('export_limit', 5000);
		?>
		<input type="number" min="100" max="20000" step="100" name="<?php echo esc_attr(self::OPTION_KEY); ?>[export_limit]" value="<?php echo esc_attr($val); ?>" />
		<p class="description">Máximo de filas por exportación para evitar timeouts.</p>
		<?php
	}
}