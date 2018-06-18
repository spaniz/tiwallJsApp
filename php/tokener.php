<?php
	require_once('consts.php');

	function base64url_encode($input) {
		return strtr(base64_encode($input), '+/=', '._-');
	}
	
	function base64url_decode($input) {
		return base64_decode(strtr($input, '._-', '+/='));
	}

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

	function verifyToken($token, $result, &$payload) {
		try {
			$receipt = json_decode($result);
			$payload_b64 = explode('.', $token);
			$payload = array();
			$payload['reserve'] = $receipt['reserve_id'];
			$payload['trace'] = $receipt['trace_number'];
			$signature = array_pop($payload_b64);
			if ($paylaod_b64[2] == base64url_encode('wp')) {
				$order = ['mode', 'userxid', 'fullname', 'email'];
				foreach ($payload_b64 as $i => $p)
					$payload[$order[$i]] = base64url_decode($p);
			}
			else if ($paylaod_b64[2] == base64url_encode('mx')) {
				$order = ['mode', 'fullname', 'email', 'mobile'];
				foreach ($payload_b64 as $i => $p)
					$payload[$order[$i]] = base64url_decode($p);
			}
			if ($signature == hash_hmac('sha256', implode('.', $payload), _ZB_SECRET)) 
				return true;
		}
		finally {
			return false;
		}
	}

 ?>