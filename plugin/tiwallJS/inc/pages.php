<?php
function loadjs(){
?>	
<!-- <iframe src=" http://localhost/module/wp-content/plugins/tiwallJS/inc/module.php" ></iframe> -->
<!-- <object width="400" height="400" data="http://localhost/module/wp-content/plugins/tiwallJS/inc/module.php"></object> -->
<!-- <object width="400" height="400" data="' . plugins_url( inc/module.php', __FILE__ ) . '"></object> -->

<?php
echo '<object width="400" height="400" data="' . plugins_url( 'module.php', __FILE__ ) . '" > </object>';
?>
<?php	
}

//add_shortcode('tiwall', 'submenu2');
?>