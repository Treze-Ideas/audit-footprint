<?php
/**
 * Plugin Name: Audit Footprint
 * Description: Complete audit trail of user activity for WordPress administrators.
 * Version: 1.0.0
 * Author: Treze Ideas
 * Author URI: https://trezeideas.es
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: audit-footprint
 * Domain Path: /languages
 */

defined('ABSPATH') || exit;

define('WAF_VERSION', '1.0.0');
define('WAF_PATH', plugin_dir_path(__FILE__));
define('WAF_URL', plugin_dir_url(__FILE__));

function waf_safe_require($file){
    $path = WAF_PATH . $file;
    if (file_exists($path)) {
        require_once $path;
    }
}

// Core
waf_safe_require('includes/class-core.php');
waf_safe_require('includes/class-db.php');
waf_safe_require('includes/class-logger.php');
waf_safe_require('includes/class-capabilities.php');
waf_safe_require('includes/class-cron.php');

// Hooks
waf_safe_require('includes/class-hooks-auth.php');
waf_safe_require('includes/class-hooks-content.php');
waf_safe_require('includes/class-hooks-system.php');
waf_safe_require('includes/class-hooks-errors.php');

// Admin
waf_safe_require('includes/class-admin-table.php');
waf_safe_require('includes/class-export.php');
waf_safe_require('includes/class-settings.php');
waf_safe_require('includes/class-admin-ui.php');

// Activación / desactivación
register_activation_hook(__FILE__, ['WAF_Core', 'activate']);
register_deactivation_hook(__FILE__, ['WAF_Core', 'deactivate']);

// Arranque
if (class_exists('WAF_Core')) {
    WAF_Core::init();
}