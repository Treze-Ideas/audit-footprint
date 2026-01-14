<?php
defined('ABSPATH') || exit;

class WAF_Hooks_Content {

	public function __construct() {
		add_action('save_post', [$this,'save_post'], 10, 3);
		add_action('before_delete_post', [$this,'delete_post']);
		add_action('transition_post_status', [$this,'status_change'], 10, 3);
	}

	public function save_post($post_id, $post, $update) {
		if (!$post || !is_object($post)) return;
		if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) return;
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

		// Elementor spam guard: evita logs del AJAX interno
		if (isset($_POST['action']) && $_POST['action'] === 'elementor_ajax') return;

		$user = wp_get_current_user();
		$action = $update ? 'post_updated' : 'post_created';

		WAF_Logger::log($action, [
			'object_type' => $post->post_type,
			'object_id'   => $post_id,
			'description' => sprintf('%s "%s" (%s)', $update ? 'Updated' : 'Created', $post->post_title, $post->post_type),
		], $user);
	}

	public function delete_post($post_id) {
		$post = get_post($post_id);
		if (!$post) return;

		WAF_Logger::log('post_deleted', [
			'object_type' => $post->post_type,
			'object_id'   => $post_id,
			'description' => sprintf('Deleted "%s" (%s)', $post->post_title, $post->post_type),
		], wp_get_current_user());
	}

	public function status_change($new, $old, $post) {
		if (!$post || $new === $old) return;

		WAF_Logger::log('post_status_changed', [
			'object_type' => $post->post_type,
			'object_id'   => $post->ID,
			'description' => sprintf('Status changed from %s to %s for "%s"', $old, $new, $post->post_title),
		], wp_get_current_user());
	}
}