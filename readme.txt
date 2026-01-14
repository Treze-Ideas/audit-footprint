=== Audit Footprint ===
Contributors: trezeideas
Tags: activity log, audit log, user activity, security, administration
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Complete audit trail of user activity for WordPress administrators. Every action leaves a trace.

== Description ==

Audit Footprint provides a clean audit trail of key events in your WordPress site:
* User login, logout and failed login attempts
* Password resets and role changes
* Content activity (create/update/delete and status changes)
* Plugin activation and deactivation
* Admin-only interface with export (CSV/JSON)
* Privacy-first IP handling (full/masked/hashed/off) and configurable retention

Designed for agencies and teams: know who did what, when, and from where.

== Installation ==

1. Upload the plugin folder to /wp-content/plugins/ or install via WordPress.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Go to Tools → Audit Footprint.

== Frequently Asked Questions ==

= Who can see the logs? =
Only administrators (capability: audit_footprint_view).

= Does it work with Elementor? =
Yes. It logs real saves and avoids most Elementor autosave noise.

= Does it store IP addresses? =
You can choose: full, masked (recommended), hashed (irreversible), or off.

= Does it delete old logs automatically? =
Yes. You can configure retention days in the settings.

== Screenshots ==

1. Audit logs table (Tools → Audit Footprint)
2. Settings page (privacy, retention and exports)

== Changelog ==

= 1.0.0 =
* Initial release: authentication, content and system audit logs
* Admin UI with badges and exports
* Privacy (IP modes) + retention cron