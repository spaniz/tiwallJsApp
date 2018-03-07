<?php
    require_once('../php/consts.php');

    $config_path = "../config.json";
    $consts_path = "../php/consts.php";
    $app_config = null;

    if ($_POST)
    {
        try {
            if ($_POST["app_id"] != _ZB_APPID || $_POST["app_token"] != _ZB_SECRET)
                updateConsts($_POST["app_id"], $_POST["app_token"]);
            updateConfig(array(
                'js' => array(
                    'debug' => !empty($_POST["js_debug"])
                ),
                'categories' => array(
                    'mode' => isset($_POST["categories_mode"]) ? $_POST["categories_mode"] : null
                ),
                'list' => array(
                    'venue' => array(
                        'id' => $_POST["list_venue_id"]
                    )
                )
            ));
            header("Refresh:0;url=?result=ok");
            exit();
        }
        catch (Exception $exception) {
            header("Refresh:0;url=?result=fail");
        }
    }

    $f = file_get_contents($config_path);
    if ($f)
        $app_config = json_decode($f);

    function updateConfig($update) {
        global $config_path;
        file_put_contents( $config_path, json_encode($update));
    }
    function updateConsts($id, $auth) {
        global $consts_path;
        $f = '<?php define("_ZB_APPID", "' . $id . '"); define("_ZB_SECRET", "' . $auth . '"); ?>';
        file_put_contents( $consts_path, $f);
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <link rel="stylesheet" href="../style/core.css" />
        <style>
            body {
                background: var(--ti-accent);
                color: white;
                font-family: 'IRANSans';
                direction: rtl;
            }
            form {
                display: flex;
                align-items: center;
                flex-direction: column;
            }
            h1, h2 , h3 {
                text-align: center;
                opacity: .87;
                font-weight: 200;
                border-bottom: 2px solid rgba(255,255,255,.2);
            }
            form > div {
                margin: calc(1% + 5px);
                background-color: rgba(255,255,255,.7);
                box-shadow: 0px 1px 1px 0 rgba(0,0,0,0.2);
                border-radius: 4px;
                padding: 15px;
                max-width: 600px;
                width: calc(98% - 10px);
                box-sizing: border-box;
                color: var(--ti-dead);
            }
            #settings-security {
                display: flex;
                justify-content: space-evenly;
            }
            #settings-custom div {
                display: flex;
                justify-content: space-evenly;
            }
            #appid {
                --width: 150px;
            }
            #apptoken {
                --width: 300px;
            }
            input.ti-btn {
                border: 4px solid white;
                opacity: .6;
                margin: 15px;
                height: unset;
                background-color: transparent !important;
                background-image: radial-gradient(white 50%, transparent 50%) !important;
                background-position: 50% 50% !important;
                background-repeat: no-repeat !important;;
                background-size: 0px 0px !important;
                transition: .3s ease-out !important;
                font-size: 20px !important;
                font-weight: 400;
                width: 180px;
            }
            input.ti-btn:hover {
                opacity: .9;
            }
            input.ti-btn:active {
                opacity: 1;
                background-size: 400px 400px !important;
                color: black;
            }
        </style>
    </head>
    <body>
        <script type="text/javascript" src="http://cdn.zirbana.com/js/jquery/1.7.2/jquery.min.js"></script>
        <script type="text/javascript" src="../engine/exoticengine.js"></script>
        <script type="text/javascript">
            $(document).ready(() => {
                <?php 
                    if (isset($_GET["result"]))
                    {
                        echo "$('#" . $_GET["result"] . "-msg').css('display', 'block');";
                    }
                        if ($app_config->js->debug)
                        echo "$('#jsdebug').click();\n";
                    echo "$('#cat_" . $app_config->categories->mode . "').click();\n";
                ?>
            });
        </script>
        <div id="ok-msg" style="display: none;padding: 20px;background: rgba(0,0,0,.5);text-align: center;">تغییرات با موفقیت ثبت شدند</div>
        <div id="fail-msg" style="display: none;padding: 20px;background: rgba(255,0,0,.7);text-align: center;">در حین ثبت تغییرات با مشکلی بر خوردیم</div>
        <form method="post">
            <h1>تنظیمات فنی</h1>
            <div id="settings-technical">
                <div id="jsdebug" class="exotic-input checkbox">
                    <input type="checkbox" name="js.debug" />
                </div>
                <span>حالت دیباگ جاوااسکریپت</span>
            </div>

            <h1>شناسه امنیتی</h1>
            <div id="settings-security">
                <input class="exotic-input textbox" name="app.token" id="apptoken" type="text" placeholder="Application Token" value="<?= _ZB_SECRET ?>" />
                <input class="exotic-input textbox" name="app.id" id="appid" type="text" placeholder="Application ID" value="<?= _ZB_APPID ?>" />
            </div>
            
            <h1>محلی سازی اطلاعات</h1>
            <div id="settings-custom">
                <div class="radiogroup">
                    <div tooltip="فقط بلیط های قابل خرید در تیوال">
                        <div id="cat_ticket_store" class="exotic-input radiobox">
                            <input type="radio" name="categories.mode" value="ticket_store" />
                        </div>
                        <span>بلیت‌ها</span>
                    </div>
                    <div tooltip="بلیط ها و رویداد های قابل خرید در تیوال">
                        <div id="cat_event_store" class="exotic-input radiobox">
                            <input type="radio" name="categories.mode" value="event_store" />
                        </div>
                        <span>بلیت‌ها و رویدادها</span>
                    </div>
                    <div tooltip="همه محصولات قابل خرید در تیوال و زیربنا">
                        <div id="cat_store" class="exotic-input radiobox">
                            <input type="radio" name="categories.mode" value="store" />
                        </div>
                        <span>همه</span>
                    </div>
                </div>
                <br />
                <span style="margin-left: 15px;">محل/سالن</span>
                <input class="exotic-input textbox" name="list.venue.id" id="venueid" type="text" placeholder="Venue ID" value="<?= isset($app_config->list->venue->id) ? $app_config->list->venue->id : "" ?>" />
            </div>

            <input class="ti-btn" type="submit" value="ثبت تغییرات" />
        </form>
    </body>
</html>