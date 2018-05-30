<?php 
function menu_admin()
{
$load_menu=add_menu_page('تیوال', 'TiwallJS', 'manage_options', __FILE__ ,'loadjs');
$load_submenu1=add_submenu_page( __FILE__,'صفحه اصلی' ,'صفحه اصلی' , 'read', __FILE__);

//$load_submenu=add_submenu_page( __FILE__,'تنظیمات' ,'تنظیمات' , 'manage_options', __FILE__  );
    $load_submenu=add_submenu_page( __FILE__,'manage' ,'تنظیمات' , 'manage_options', '' ,'submenu2' );
	$load_submenu=add_submenu_page( __FILE__,'manage' ,'ایجاد shortcode' , 'manage_options', '' ,'submenu3' );

//add_action("load-{$load_menu}","load_menu_css");
	
}
?>