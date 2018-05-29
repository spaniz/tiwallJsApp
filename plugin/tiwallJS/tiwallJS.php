<?php
/**
* Plugin Name: tiwallJS
* Plugin URI:tiwall.com
* Description: our new plugin wiyh javascript!
* Version: 1.0
* Author: n s u n
* Author URI: ******
* License: GPL2
*/
 defined( 'ABSPATH' ) || exit;
define( 'Tiwall_DIR', plugin_dir_path(  __FILE__ ) );
define( 'Tiwall_INC_DIR', trailingslashit( Tiwall_DIR .'inc') );
define( 'Tiwall_INC_SETTING', trailingslashit( Tiwall_DIR .'.setup') );
define( 'Tiwall_URL', plugin_dir_url( __FILE__ ) );
define( 'Tiwall_css', trailingslashit( Tiwall_URL .'style') );
define( 'Tiwall_js', trailingslashit( Tiwall_URL .'engine') );
define( 'Tiwall_img', trailingslashit( Tiwall_URL .'img') );
define( 'Tiwall_struct', trailingslashit( Tiwall_URL .'struct') );
include_once Tiwall_INC_DIR.'frontend.php';
//if(is_admin()){
	include_once Tiwall_INC_DIR.'backend.php';
	include_once Tiwall_INC_DIR.'pages.php';
	include_once Tiwall_INC_DIR.'ajax.php';
//	if (is_admin()){
//        include_once Tiwall_INC_SETTING.'settings.php';
        //include_once Tiwall_INC_DIR.'php/consts.php';
    //}
	// include_once Tiwall_INC_DIR.'module.php';
	add_action( 'admin_menu', 'menu_admin' );
//}

// register_activation_hook( __FILE__, 'dataabase' );
// register_deactivation_hook( __FILE__, 'plugin_deactivate' );
// add_action( 'database', 'dataabase' );

?>
