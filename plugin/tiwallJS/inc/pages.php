<?php
function loaddrama(){
?>	
<!-- <iframe src=" http://localhost/module/wp-content/plugins/tiwallJS/inc/module.php" ></iframe> -->
<!-- <object width="400" height="400" data="http://localhost/module/wp-content/plugins/tiwallJS/inc/module.php"></object> -->
<!-- <object width="400" height="400" data="' . plugins_url( inc/module.php', __FILE__ ) . '"></object> -->
<?php
echo '<object width="100%" height="500" data="' . plugins_url( 'module.php?categories~_filter=drama', __FILE__ ) . '" > </object>';
?>
<?php	
}
//add_shortcode('tiwall', 'submenu2')
?>
<?php
function loadconcert(){
?>	
<!-- <iframe src=" http://localhost/module/wp-content/plugins/tiwallJS/inc/module.php" ></iframe> -->
<!-- <object width="400" height="400" data="http://localhost/module/wp-content/plugins/tiwallJS/inc/module.php"></object> -->
<!-- <object width="400" height="400" data="' . plugins_url( inc/module.php', __FILE__ ) . '"></object> -->
<?php
echo '<object width="100%" height="500" data="' . plugins_url( 'module.php?categories~_filter=concert', __FILE__ ) . '" > </object>';
?>
<?php	
}
//add_shortcode('tiwall', 'submenu2')
?>
<?php
function submenu2(){
?>
<object style="width: 100%;height: 100vh"  data="../wp-content/plugins/tiwallJS/.setup/settings.php"></object>
<?php
}
?>
<?php function loadreceipt($urn, $zb_result, $callback) { ?>
	<object style="width: 100%; height: 500px" data="<?php echo plugins_url('module.php?urn=$urn&callback=' . urlencode($callback) . '&zb_result=$zb_result'); ?>"></object>
<?php } ?>

<?php function loadreceipt() { ?>
	<object style="width: 100%; height: 500px" data="<?php echo plugins_url('module.php?urn=' . $_GET['urn'] . '&callback=' . urlencode($_GET['callback']) . '&zb_result=' . $_GET['zb_result']); ?>"></object>
<?php } ?>