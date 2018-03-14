<?php    
    require_once("php/consts.php");
    header("Content-Type: text/json");
    $params = "?";
    if (!isset($_GET['urn']) || !isset($_GET['action']))
    {
        echo '{"ok":false,"error":{"code":400,"message":"Bad request."}}';
        exit;
    }
    foreach ($_GET as $getKey => $getVal)
        if ($getKey != 'urn' && $getKey != 'action')
            $params .= $getKey . '=' . $getVal . '&';
    $head = array(
        'http' => array(
            'method' => "GET",
            'header' => "Zb-Auth: " . _ZB_APPID . ':' . _ZB_SECRET
        )
    );
    //var_dump($head);
    $cont = stream_context_create($head);
    echo file_get_contents("https://store.zirbana.com/v2/" . $_GET['urn'] . "/" . $_GET['action'] . $params, false, $cont);
    //echo ":)";
	
?>