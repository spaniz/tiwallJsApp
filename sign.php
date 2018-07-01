<?php
	error_reporting(0);
	require_once('php/consts.php');
	require_once('php/tokener.php');
	
	header('Content-Type: text/plain');
	echo signReservePayload($_GET);
?>
