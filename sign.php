<?php
	error_reporting(0);
	require_once('php/consts.php');
	require_once('php/tokener.php');
<<<<<<< HEAD
=======

	function signReservePayload($args) {
		if (!isset($args['mode']))
			die;
		
		$payload = array();
		if ($args['mode'] == "wp") {
			$order = ['reserve', 'trace', 'mode', 'userxid', 'fullname', 'email'];
			foreach ($order as $i)
				array_push($payload, base64url_encode($args[$i]));
		}
		else if ($args['mode'] == "mx") {
			$order = ['reserve', 'trace', 'mode', 'fullname', 'email', 'mobile'];
			foreach ($order as $i)
				array_push($payload, base64url_encode($args[$i]));
		}
		
		return implode('.', $payload) . '.' . hash_hmac('sha256', implode('.', $payload), _ZB_SECRET);
	}
>>>>>>> 2cb6273f76f41a2a5eeda3b62f666c251681162b

	header('Content-Type: text/plain');
	echo signReservePayload($_GET);
?>
