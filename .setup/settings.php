<?php
    //ini_set('display_errors', TRUE);
    error_reporting(0);
    define('ROOTDIR', "../");
    include_once('../php/consts.php');
    require_once('../php/paths.php');
    $app_config = null;

    if ($_POST)
    {
        try { 
            if ($_POST["app_id"] != _ZB_APPID || $_POST["app_token"] != _ZB_SECRET)
                updateConsts($_POST["app_id"], $_POST["app_token"]);
            updateConfig(array(
                'view' => $_POST["view"],
                'js' => array(
                    'debug' => !empty($_POST["js_debug"]),
                    'scroll' => !empty($_POST["js_scroll"]),
                    'loading' => $_POST["js_loading"]
                ),
                'categories' => array(
                    'mode' => isset($_POST["categories_mode"]) ? $_POST["categories_mode"] : null,
                    '_filter' => str_replace(' ', '', $_POST["categories_filter"])
                ),
                'list' => array(
                    'venue' => str_replace(' ', '', $_POST["list_venue"])
                ),
                'get' => array(
                    'urn' => str_replace(' ', '', $_POST["get_urn"])
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

    //var_dump($app_config);
    
    function updateConfig($update) {
        global $config_path;
        file_put_contents($config_path, json_encode($update));
    }
    function updateConsts($id, $auth) {
        global $consts_path;
        $f = '<?php define("_ZB_APPID", "' . $id . '"); define("_ZB_SECRET", "' . $auth . '"); ?>';
        file_put_contents($consts_path, $f);
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <link rel="stylesheet" href="../style/core.css" />
        <link rel="stylesheet" href="../style/setup.css" />
    </head>
    <body>
        <script type="text/javascript" src="https://cdn.zirbana.com/js/jquery/1.7.2/jquery.min.js"></script>
        <script type="text/javascript" src="../engine/exoticengine.js"></script>
        <script type="text/javascript">
            $(document).ready(() => {
                <?php 
                    if (isset($_GET["result"]))
                        echo "$('#" . $_GET["result"] . "-msg').css('display', 'block');";
                    if ($app_config->js->debug)
                        echo "$('#jsdebug').click();\n";
                    if ($app_config->js->scroll)
                        echo "$('#jsscroll').click();\n";
                    echo "$('#cat_" . $app_config->categories->mode . "').click();\n";
                    echo "$('#view_" . $app_config->view . "').click();\n";
                ?>
                $('input[name="view"]').bind('change', function() {
                    console.log($(this).attr('value') + ': ' + $(this).prop('checked'));
                    if ($(this).prop('checked') == true) {
                        $('#main-form').attr('tview', $(this).attr('value'));
                    }
                });
                $('input[name="view"]').trigger('change');
            });
        </script>
        <div id="ok-msg" style="display: none;padding: 20px;background: rgba(0,0,0,.5);text-align: center;">تغییرات با موفقیت ثبت شدند</div>
        <div id="fail-msg" style="display: none;padding: 20px;background: rgba(255,0,0,.7);text-align: center;">در حین ثبت تغییرات با مشکلی بر خوردیم</div>
        <form id="main-form" method="post">
            <h1>تنظیمات فنی</h1>
            <div id="settings-technical">
                <div id="jsdebug" class="exotic-input checkbox">
                    <input type="checkbox" name="js.debug" />
                </div>
                <span>حالت دیباگ جاوااسکریپت</span>
                <br />
                <div id="jsscroll" class="exotic-input checkbox">
                    <input type="checkbox" name="js.scroll" />
                </div>
                <span>ارتفاع آزاد پلاگین</span>
                <br/>
                <span class="duo-right">لودینگ دلخواه</span>
                <input class="exotic-input textbox duo-left" name="js.loading" id="jsloading" placeholder="GIF/SVG Url" value="<?= $app_config->js->loading ?>" />
            </div>

            <h1>شناسه امنیتی</h1>
            <div id="settings-security">
                <input class="exotic-input textbox" name="app.token" id="apptoken" type="text" placeholder="App Token" value="<?= _ZB_SECRET ?>" />
                <input class="exotic-input textbox" name="app.id" id="appid" type="text" placeholder="App ID" value="<?= _ZB_APPID ?>" />
            </div>

            <h1>حالت نمایش</h1>
            <div>
                <div class="radiogroup">
                    <div>
                        <div id="view_normal" class="exotic-input radiobox">
                            <input type="radio" name="view" value="normal" />
                        </div>
                        <span>دسته بندی و لیست</span>
                    </div>
                    <div>
                        <div id="view_single" class="exotic-input radiobox">
                            <input type="radio" name="view" value="single" />
                        </div>
                        <span>تک-نما</span>
                    </div>
                </div>
            </div>
            
            <h1>محلی سازی اطلاعات</h1>
            <div id="settings-custom" class="main-settings">
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
                <span class="duo-right">زمینه‌ها</span>
                <input class="exotic-input textbox duo-left" name="categories.filter" id="catid" type="text" placeholder="Category Keys" value="<?= isset($app_config->categories->_filter) ? $app_config->categories->_filter : "" ?>" />
                <br />
                <span class="duo-right">محل/سالن‌ها</span>
                <input class="exotic-input textbox duo-left" name="list.venue" id="venueid" type="text" placeholder="Venue ID(s)" value="<?= isset($app_config->list->venue) ? $app_config->list->venue : "" ?>" />
                <br style="margin-bottom: 30px" />
                <span>شناسه سالن‌ها یا زمینه‌ها را با ویرگول انگلیسی "," از هم جدا کنید.</span>
            </div>
            <div id="settings-single" class="main-settings">
                <span class="duo-right">شناسه صفحه</span>
                <input class="exotic-input textbox duo-left" name="get.urn" id="singleurn" type="text" placeholder="Page URN" value="<?= isset($app_config->get->urn) ? $app_config->get->urn : "" ?>" />
            </div>

            <input class="ti-btn" type="submit" value="ثبت تغییرات" />
        </form>
        <center style="margin-bottom: 10px; margin-top: 15px;">Powered by <a><img src="http://x.anovase.com/logo-wide-w.svg" height="30px" style="vertical-align: baseline; margin-bottom: -5px;" /></a> 2018</center>
    </body>
</html>