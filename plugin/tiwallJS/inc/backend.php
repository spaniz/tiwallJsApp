<?php 
function menu_admin()
{
$load_menu=add_menu_page('تیوال', 'TiwallJS', 'manage_options', __FILE__ ,'loadjs');
$load_submenu1=add_submenu_page( __FILE__,'manage' ,'صفحه اصلی' , 'manage_options', __FILE__);
$load_submenu=add_submenu_page( __FILE__,'manage' ,'تنظیمات' , 'manage_options', '' ,'submenu2' );	

add_action("load-{$load_menu}","load_menu_css");
	
}
 function load_menu_css()
{
	//wp_register_style( 'menu_style', Tiwall_css.'style.css' );
	//wp_enqueue_style( 'menu_style' );
	wp_register_script( 'script_menu1', Tiwall_js.'displayengine.js', array('jquery') );
	wp_register_script( 'script_menu2', Tiwall_js.'itemparser.js', array('jquery') );
	wp_register_script( 'script_menu3', Tiwall_js.'ti-get.js', array('jquery') );
	wp_register_script( 'script_menu4', Tiwall_js.'utility.js', array('jquery') );
	wp_enqueue_script( 'script_menu1' );
	wp_enqueue_script( 'script_menu2' );
	wp_enqueue_script( 'script_menu3' );
	wp_enqueue_script( 'script_menu4' );
}


?>