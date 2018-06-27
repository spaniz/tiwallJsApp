<?php 
	require_once('php/tokener.php');
	error_reporting(E_ALL);
	ini_set('html_errors', false);
	$cb_payload = array();
	$cb_auth = verifyToken($_GET['backtoken'], $_GET['zb_result'], $cb_payload);
	header('Content-Type: text/plain');
	echo "%==-- THIS IS ONLY FOR DEBUGGING PURPOSES --==%\n";
	echo $cb_auth == true ? "VERIFIED" : "INVALID";
	echo "\n";
	var_dump($cb_payload);
?>