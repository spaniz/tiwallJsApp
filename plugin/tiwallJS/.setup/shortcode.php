<?php
    error_reporting(0);

    require_once('../inc/php/paths.php');
    $app_config = null;

    $f = file_get_contents($config_path);
    if ($f)
        $app_config = json_decode($f);
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="../style/core.css" />
        <link rel="stylesheet" href="../style/setup.css" />
    </head>
    <body>
        <script type="text/javascript" src="https://cdn.zirbana.com/js/jquery/1.7.2/jquery.min.js"></script>
        <script type="text/javascript" src="../engine/exoticengine.js"></script>
        <script>
            function generateShortcode() {
                let s = '[zb-page';
                $.map($('input[type="text"]'), k => {
                    if ($(k).val())
                        s += ` ${$(k).attr('name')}="${$(k).val()}"`;
                });
                s += ']';
                $('#output').text(s);
            }

            $(document).ready(() => {
                generateShortcode();
                $('input[type="text"]').on('input', generateShortcode);
            });
        </script>
        <form>
            <h1>ساخت کد کوتاه</h1>
            <div id="settings-custom" class="main-settings">
                <span class="duo-right">زمینه‌ها</span>
                <input class="exotic-input textbox duo-left" name="cat" type="text" placeholder="<?= isset($app_config->categories->_filter) ? $app_config->categories->_filter : '' ?>" />
                <br />
                <span class="duo-right">محل/سالن‌ها</span>
                <input class="exotic-input textbox duo-left" name="venue_id" type="text" placeholder="<?= isset($app_config->list->venue) ? $app_config->list->venue : '' ?>" />
                <br style="margin-bottom: 30px" />
                <span>شناسه سالن‌ها یا زمینه‌ها را با ویرگول انگلیسی "," از هم جدا کنید.</span>
            </div>
            <div id="output"></div>
        </form>
        <center style="margin-bottom: 10px; margin-top: 15px;">Powered by <a><img src="http://x.anovase.com/logo-wide-w.svg" height="30px" style="vertical-align: baseline; margin-bottom: -5px;" /></a> 2018</center>
    </body>
</html> 