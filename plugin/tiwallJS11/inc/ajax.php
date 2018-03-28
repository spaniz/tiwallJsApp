<?php
add_action('wp_ajax_insert_db','insertdb');
add_action( "wp_ajax_nopriv_insert_db", "insertdb" );
function insertdb(){
	 $id=$_POST['id'];
	// echo $id;
	echo "<script type='text/javascript'>alert('gooood');</script>";
	echo "<script type='text/javascript'>alert(" + $id + ");</script>";
	// $result = "https://api.tiwall.com/v1/news/getNews?newsId=".$id;
	// echo $result['data'] ;
	
}

?>