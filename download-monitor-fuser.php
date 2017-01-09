<?php
/*
Plugin Name: Filter Users Addon for Download Monitor
Plugin URI: http://www.closemarketing.es/servicios/wordpress-plugins/gravity-forms-es/
Description: Adds to Download monitor the ability to filter Downloads by current user

Version: 1.0
Requires at least: 3.9

Author: Closemarketing
Author URI: https://www.closemarketing.es/

Text Domain: dmfu
Domain Path: /languages/

License: GPL
*/
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
	public function __construct( $file ) {
		$this->file = $file;
  }
} //from class

global $dmfuser_plugin;

$dmfuser_plugin = new DMFUserPlugin( __FILE__ );
