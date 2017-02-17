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
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


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

    add_action('admin_init', array($this,'dmfu_admin_metabox') );
    add_action('save_post', array($this,'dmfu_save_metabox') );
    add_shortcode( 'download_user', array( $this, 'dmfu_download_user' ) );
  }

  /**
  * Init for metabox
  */
  function dmfu_admin_metabox(){
    add_meta_box('dmfu_users', __('Filter by user','dmfu'), array($this,'meta_options'), 'dlm_download', 'side', 'high');
  }

  /**
  * Metbox Select user
  */
  function meta_options(){

    $checkboxMeta = get_post_meta(get_the_id());

    //* Gets the forms in array
    $users = get_users();
    echo '<p>'.__('Only this users will see this file:','dmfu').'</p>';
    foreach( $users as $user ):
        echo '<p><input type="checkbox" name="dmfu_userid_'.$user->ID.'" id="dmfu_userid_'.$user->ID.'" value="yes" ';
        if ( isset ( $checkboxMeta['dmfu_userid_'.$user->ID] ) ) checked( $checkboxMeta['dmfu_userid_'.$user->ID][0], 'yes' );
        echo ' />'.$user->display_name.'</p>';
    endforeach;
  }

  function dmfu_save_metabox( $post_id ) {

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        return;

    //* Gets the forms in array
    $users = get_users();
    foreach( $users as $user ):
      //saves checbox meta
      if( isset( $_POST[ 'dmfu_userid_'.$user->ID ] ) ) {
          update_post_meta( $post_id, 'dmfu_userid_'.$user->ID, 'yes' );
      } else {
          update_post_meta( $post_id, 'dmfu_userid_'.$user->ID, 'no' );
      }
    endforeach;

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
  function dmfu_download_user() {
      $current_user = wp_get_current_user();

      $args = array(
      	'post_type'  => 'dlm_download',
      	'meta_query' => array(
      		array(
      			'key'     => 'dmfu_userid_'.$current_user->ID,
      			'value'   => 'yes',
      			'compare' => 'IN',
      		),
      	),
      );
      $query = new WP_Query( $args );
      if ( $query->have_posts() ) {
      	while ( $query->have_posts() ) {
          $query->the_post();
          echo do_shortcode( '[download id="'.get_the_id().'"]' );
      	}
      	/* Restore original Post Data */
      	wp_reset_postdata();
      }
  }


} //from class

global $dmfuser_plugin;

$dmfuser_plugin = new DMFUserPlugin( __FILE__ );
