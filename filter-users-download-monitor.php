<?php
/*
Plugin Name: Filter Users Addon for Download Monitor
Plugin URI: http://www.closemarketing.es/servicios/wordpress-plugins/gravity-forms-es/
Description: Adds to Download monitor the ability to filter Downloads by current user

Version: 1.3
Requires at least: 3.9

Author: Closemarketing
Author URI: https://www.closemarketing.es/

Text Domain: filter-users-download-monitor
Domain Path: /languages/

License: GPL
 */
if (!defined('ABSPATH')) {
	exit;
} // Exit if accessed directly

//Loads translation
load_plugin_textdomain('filter-users-download-monitor', false, dirname(plugin_basename(__FILE__)) . '/languages/');

class DMFUserPlugin {
	/**
	 * The plugin file
	 *
	 * @var string
	 */
	private $file;
	/**
	 * Construct and intialize
	 */
	public function __construct($file) {
		$this->file = $file;

		add_action('admin_init', array($this, 'dmfu_admin_metabox'));
		add_action('save_post', array($this, 'dmfu_save_metabox'));
		add_shortcode('download_user', array($this, 'dmfu_download_user'));
	}

	/**
	 * Init for metabox
	 */
	function dmfu_admin_metabox() {
		add_meta_box('dmfu_users', __('Filter by user', 'filter-users-download-monitor'), array($this, 'meta_options'), 'dlm_download', 'normal', 'high');
		add_meta_box('dmfu_users_role', __('Filter by role', 'filter-users-download-monitor'), array($this, 'meta_options_role'), 'dlm_download', 'normal', 'high');
	}

	/**
	 * Metbox Select user
	 */
	function meta_options() {

		$checkboxMeta = get_post_meta(get_the_id());

		//* Gets the forms in array
		$users = get_users();
		echo '<p>' . __('Only these users will see this file:', 'filter-users-download-monitor') . '</p>';
		foreach ($users as $user):
			echo '<p><input type="checkbox" name="dmfu_userid_' . $user->ID . '" id="dmfu_userid_' . $user->ID . '" value="yes" ';
			if (isset($checkboxMeta['dmfu_userid_' . $user->ID])) {
				checked($checkboxMeta['dmfu_userid_' . $user->ID][0], 'yes');
			}

			echo ' />' . $user->display_name . '</p>';
		endforeach;
	}
	/**
	 * Metbox Select role
	 */
	function meta_options_role() {

		$checkboxMeta = get_post_meta(get_the_id());

		//* Gets the forms in array
		$roles = get_editable_roles();
		echo '<p>' . __('Only the user with these roles will see this file:', 'filter-users-download-monitor') . '</p>';

		while ($role = current($roles)) {
			echo '<p><input type="checkbox" name="dmfu_role_' . key($roles) . '" id="dmfu_role_' . key($roles) . '" value="yes" ';
			if (isset($checkboxMeta['dmfu_role_' . key($roles)])) {
				checked($checkboxMeta['dmfu_role_' . key($roles)][0], 'yes');
			}

			echo ' />' . $role['name'] . '</p>';

			next($roles);
		}
	}

	function dmfu_save_metabox($post_id) {

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		//* Gets the forms in array
		$users = get_users();
		foreach ($users as $user):
			//saves checbox meta
			if (isset($_POST['dmfu_userid_' . $user->ID])) {
				update_post_meta($post_id, 'dmfu_userid_' . $user->ID, 'yes');
			} else {
				update_post_meta($post_id, 'dmfu_userid_' . $user->ID, 'no');
			}
		endforeach;

		//* Gets roles

		$roles = get_editable_roles();
		while ($role = current($roles)) {
			if (isset($_POST['dmfu_role_' . key($roles)])) {
				update_post_meta($post_id, 'dmfu_role_' . key($roles), 'yes');
			} else {
				update_post_meta($post_id, 'dmfu_role_' . key($roles), 'no');
			}

			next($roles);
		}

	} //save metabox

	/**
	 * download function for user.
	 *
	 * @access public
	 *
	 * @param array $atts
	 *
	 * @return string
	 */
	function dmfu_download_user($atts, $content = null) {
		$att = shortcode_atts(array(
			'category' => '',
		), $atts);
		//array of downloads
		$array_dwnld = array();

		$current_user = wp_get_current_user();

		//checks for the user
		$args = array(
			'post_type'  => 'dlm_download',
			'meta_query' => array(
				array(
					'key'     => 'dmfu_userid_' . $current_user->ID,
					'value'   => 'yes',
					'compare' => 'IN',
				),
			),
		);
		if ($att['category']) {
			$args = array_merge($args, array(
				'tax_query' => array(
					array(
						'taxonomy' => 'dlm_download_category',
						'field'    => 'slug',
						'terms'    => $att['category'],
					),
				),
			));
		}

		$query = new WP_Query($args);
		if ($query->have_posts()) {

			while ($query->have_posts()) {
				$query->the_post();
				$array_dwnld[] = get_the_id();
			}
			/* Restore original Post Data */
			wp_reset_postdata();
		}
		//checks for role
		foreach ($current_user->roles as $role_item) {
			$args = array(
				'post_type'  => 'dlm_download',
				'meta_query' => array(
					array(
						'key'     => 'dmfu_role_' . $role_item,
						'value'   => 'yes',
						'compare' => 'IN',
					),
				),
			);
			if ($att['category']) {
				$args = array_merge($args, array(
					'tax_query' => array(
						array(
							'taxonomy' => 'dlm_download_category',
							'field'    => 'slug',
							'terms'    => $att['category'],
						),
					),
				));
			}
			$query = new WP_Query($args);
			if ($query->have_posts()) {

				while ($query->have_posts()) {
					$query->the_post();
					$array_dwnld[] = get_the_id();
				}
				/* Restore original Post Data */
				wp_reset_postdata();
			}
		}

		//Prints the files download
		if ($array_dwnld) {
			$array_dwnld = array_unique($array_dwnld);
			echo '<ul class="dlm-downloads">';
			foreach ($array_dwnld as $dwnld_item):
				echo '<li>' . do_shortcode('[download id="' . $dwnld_item . '"]') . '</li>';
			endforeach;
			echo '</ul>';
		}
	}

} //from class

global $dmfuser_plugin;

$dmfuser_plugin = new DMFUserPlugin(__FILE__);
