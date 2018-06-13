<?php
	error_reporting(0);
	require_once('php/consts.php');

	function signReservePayload($args) {
		if (!isset($args['mode']))
			die;
		
		$payload = array();
		if ($args['mode'] == "wp") {
			$order = ['reserve', 'trace', 'mode', 'userxid', 'fullname', 'email'];
			foreach ($order as $i)
				array_push($payload, base64_encode($args[$i]));
		}
		else if ($args['mode'] == "mx") {
			$order = ['reserve', 'trace', 'mode', 'fullname', 'email', 'mobile'];
			foreach ($order as $i)
				array_push($payload, base64_encode($args[$i]));
		}
		
		return implode('.', $payload) . '.' . hash_hmac('sha256', implode('.', $payload), _ZB_SECRET);
	}

	header('Content-Type: text/plain');
	echo signReservePayload($_GET);
?>