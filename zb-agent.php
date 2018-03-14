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
            $params .= $getKey . '=' . urlencode($getVal) . '&';
    $head = array(
        'http' => array(
            'ignore_errors' => true,
            'method' => "GET",
            'header' => "Zb-Auth: " . _ZB_APPID . ':' . _ZB_SECRET
        )
    );
    $cont = stream_context_create($head);
    $uri = "https://store.zirbana.com/v2/" . $_GET['urn'] . "/" . $_GET['action'] . $params;
    echo file_get_contents($uri, false, $cont);
?>