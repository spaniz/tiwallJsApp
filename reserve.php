<?php    
    define('ROOTDIR', "");
    require_once('php/consts.php');
	require_once('php/paths.php');
	require_once('php/tokener.php');
	header("Content-Type: text/json");
	$fx = file_get_contents($config_path);
	if ($fx)
		$confx = json_decode($fx);
	else 
		die;
	$enforceParams = array(
		'fullname' => null,
		'email' => null,
		'mobile' => null,
		'send_sms' => false,
		'send_email' => false,
		'cypherkey' => null
	);
	if ($confx->user->override) {
		$enforceParams['user_fullname'] = $confx->user->fullname;
		$enforceParams['user_email'] = $confx->user->email;
		$enforceParams['user_mobile'] = $confx->user->mobile;
	}
	if (!empty($_GET['cypherkey'])) {
		$userCypher = openCypherKey($_GET['cypherkey']);
		if (empty($userCypher) && $confx->wordpress->forcelogin)
			die '{"ok":false,"error":{"code":401,"message":"Need to login."}}';
	}
	else
		die '{"ok":false,"error":{"code":401,"message":"Need to login."}}';
    $params = "?";
    if (!isset($_GET['urn']))
    {
        die '{"ok":false,"error":{"code":400,"message":"Bad request."}}';
    }
    foreach ($_GET as $getKey => $getVal)
        if ($getKey != 'urn' && $getKey != 'cypherkey' && (!isset($enforceParams[$getKey]) || $enforceParams[$getKey] != null)) {
			$params .= $getKey . '=' . urlencode($getVal) . '&';
		}
    $head = array(
        'http' => array(
            'ignore_errors' => true,
            'method' => "GET",
            'header' => "Zb-Auth: " . _ZB_APPID . ':' . _ZB_SECRET
        )
    );
    $cont = stream_context_create($head);
    $uri = "https://store.zirbana.com/v2/" . $_GET['urn'] . "/reserve" . $params;
	$vrx = file_get_contents($uri, false, $cont);
	try {
		$jdat = json_decode($vrx);
		if ($jdat->ok) {
			$payload = array(
				'reserve' => $jdat->data->reserve_id,
				'trace' => $jdat->data->trace_number,
				'mode' => (empty($userCypher)) ? 'mx' : $userCypher['mode']
			);
			if (!empty($userCypher))
				$payload = array_merge($payload, $userCypher);
			else
				$payload = array_merge($payload, array(
					'fullname' => $_GET['fullname'],
					'email' => $_GET['email'],
					'mobile' => $_GET['mobile']
				));
			$payload['igni'] = time();
			$jdat->token = signReservePayload($payload);
			die json_encode($jdat);
		}
		else
			die $vrx;
	}
	catch (Exception $e) {
		die '{"ok":false,"error":{"code":500,"message":"Response was not acceptable."}}';
	}
?>