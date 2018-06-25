<?php
	error_reporting(E_ALL);
	require_once('php/consts.php');
	require_once('php/tokener.php');

	header('Content-Type: text/plain');
	echo signReservePayload($_GET);
?>
