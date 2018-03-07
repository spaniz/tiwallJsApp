<?php
    require_once('consts.php');

    $conf = file_get_contents('../config.json');
    $def_conf = file_get_contents('../config.default.json');

    echo "var __app_defaults = JSON.parse('" . str_replace('\n', ' ', $def_conf) . "');";
    echo "var __app_config = JSON.parse('" . str_replace('\n', ' ', $conf) . "');";
?>