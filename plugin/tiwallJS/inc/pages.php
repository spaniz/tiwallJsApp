<?php
function load($atts){
	  $cat = $atts['cat'];
	  $placeid = $atts['venue_id'];
	global $current_user; get_currentuserinfo();
	$user_info='user_login='. $current_user->user_login.'&user_email='.$current_user->user_email.'&user_firstname='.$current_user->user_firstname.'&user_lastname='.$current_user->user_firstname.'&display_name='.$current_user->display_name.'&user_id='.$current_user->ID.'&user_level='.$current_user->user_level;
?>
	<script type="text/javascript" src="https://cdn.zirbana.com/js/jquery/1.7.2/jquery.min.js"></script>
	<script type="text/javascript" src="/wp-content/plugins/tiwallJS/engine/utility.js"></script>
	<script type="text/javascript" src="/wp-content/plugins/tiwallJS/engine/scrollsync.js"></script>
	<object  id="anozb-plugfrm" style="width:100%;  height:var(--ti-plugin-height, 500px); margin-top:-30px;"
	 data="<?php echo plugins_url('module.php?categories~_filter='.$cat.'&list~venue='.$placeid.'&'.$user_info, __FILE__ )?>"> </object>
	<div style="height: 3px"></div>
<?php
	}
?>
<?php function loadreceipt() { ?>
<object style="width: 100%; height: 500px" data='<?php echo plugins_url("module.php?zb_result=" . urlencode($_GET['zb_result']), __FILE__ ); ?>'></object>
<?php } ?>
<?php
function submenu2(){
?>
<object style="width: 100%;height: 100vh"  data="../wp-content/plugins/tiwallJS/.setup/settings.php"></object>
<?php
}
?>
<?php
function submenu3(){
?>
<object style="width: 100%;height: 100vh"  data="../wp-content/plugins/tiwallJS/.setup/shortcode.php"></object>
<?php
}
?>
<?php function loadreceipt($urn, $zb_result, $callback) { ?>
	<object style="width: 100%; height: 500px" data="<?php echo plugins_url('module.php?urn=$urn&callback=' . urlencode($callback) . '&zb_result=$zb_result'); ?>"></object>
<?php } ?>

<?php function loadreceipt() { ?>
	<object style="width: 100%; height: 500px" data="<?php echo plugins_url('module.php?urn=' . $_GET['urn'] . '&callback=' . urlencode($_GET['callback']) . '&zb_result=' . $_GET['zb_result']); ?>"></object>
<?php } ?>