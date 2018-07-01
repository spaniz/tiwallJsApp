<?php
	require_once('consts.php');

	function base64url_encode($input) {
		return strtr(base64_encode($input), '+/=', '~_-');
	}
	
	function base64url_decode($input) {
		return base64_decode(strtr($input, '~_-', '+/='));
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
		array_push(time());
		return implode('.', array_slice($payload, 2)) . '.' . hash_hmac('sha256', implode('.', $payload), _ZB_SECRET);
	}

	function verifyToken($token, $result, &$payload) {
		try {
			$_receipt = json_decode($result);
			$receipt = null;
			if (!$_receipt->ok)
				return false;
			else 
				$receipt = $_receipt->data;
			//trigger_error("Received token as " . $token);
			$payload_b64 = explode('.', $token);
			//trigger_error("Received reserveid as " . $receipt->reserve_id);
			$payload = array();
			$payload['reserve'] = $receipt->reserve_id;
			$payload['trace'] = $receipt->trace_number;

			$signature = array_pop($payload_b64);

			$cpayload = array_merge(
				array(base64url_encode($payload['reserve']), base64url_encode($payload['trace'])), 
				$payload_b64, 
				array(array_pop($payload_b64))
			);
			if ($payload_b64[0] == base64url_encode('wp')) {
				$order = ['mode', 'userxid', 'fullname', 'email'];
				foreach ($payload_b64 as $i => $p)
					$payload[$order[$i]] = base64url_decode($p);
			}
			else if ($payload_b64[0] == base64url_encode('mx')) {
				$order = ['mode', 'fullname', 'email', 'mobile'];
				foreach ($payload_b64 as $i => $p)
					$payload[$order[$i]] = base64url_decode($p);
			}
			
			if (strcmp($signature, hash_hmac('sha256', implode('.', $cpayload), _ZB_SECRET)) == 0) 
				return true;
		}
		catch (Exception $e) {
			return false;
		}
		return false;
	}

	function makeCypherKey($mode, $fullname, $email, $userxid) {
		$jdata = json_encode([
			'mode' => $mode,
			'fullname' => $name,
			'email' => $email,
			'userxid' => $userxid,
			'igni' => time()
		]);
		return openssl_encrypt($jdata, 'aes-128-cbc', _ZB_SECRET);
	}

	function openCypherKey($cypher) {
		$jdata = openssl_decrypt($cypher, 'aes-128-cbc', _ZB_SECRET);
		$dec = json_decode($jdata, true);
		if (isset($dec['mode'], $dec['igni']))
			return $dec;
		else 
			return null;
	}

 ?>