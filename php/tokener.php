<?php
	require_once('../php/consts.php');

	function verifyToken($token, $result, &$payload) {
		try {
			$receipt = json_decode($result);
			$payload_b64 = explode('.', $token);
			$payload = array();
			$payload['reserve'] = $receipt['reserve_id'];
			$payload['trace'] = $receipt['trace_number'];
			$signature = array_pop($payload_b64);
			if ($paylaod_b64[2] == base64_encode('wp')) {
				$order = ['mode', 'userxid', 'fullname', 'email'];
				foreach ($payload_b64 as $i => $p)
					$payload[$order[$i]] = base64_decode($p);
			}
			else if ($paylaod_b64[2] == base64_encode('mx')) {
				$order = ['mode', 'fullname', 'email', 'mobile'];
				foreach ($payload_b64 as $i => $p)
					$payload[$order[$i]] = base64_decode($p);
			}
			if ($signature == hash_hmac('sha256', implode('.', $payload), _ZB_SECRET)) 
				return true;
		}
		finally {
			return false;
		}
	}

 ?>