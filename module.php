<head>
    <meta charset="UTF-8" />
</head>
<body>
    <?php
        define('ROOTDIR', "");
        require_once("php/paths.php")
    ?>
    <div id="ti-mastercontain">
        <link rel="stylesheet" href="style/core.css" />
        <link title="largeCSS" rel="stylesheet" href="style/large.css" />
        <link type="font/woff2" href="https://fonts.gstatic.com/s/materialicons/v34/2fcrYFNaTjcS6g4U3t-Y5ZjZjT5FdEJ140U2DJYC3mY.woff2" as="font" rel="preload" />
        <script type="text/javascript">const __config = <?= file_get_contents($config_path) ?>;</script>
        <script type="text/javascript" src="http://cdn.zirbana.com/js/jquery/1.7.2/jquery.min.js"></script>
        <script type="text/javascript" src="engine/utility.js"></script>
        <script type="text/javascript" src="engine/ti-get.js"></script>
        <script type="text/javascript" src="engine/itemparser.js"></script>
        <script type="text/javascript" src="engine/displayengine.js"></script>
        <script type="text/javascript" src="engine/exoticengine.js"></script>

        <div id="ti-listHolder"></div>
        <div id="ti-listHeader" style="top: 100%">
        <i class="material-icons" style="margin-top: -4px;">expand_less</i>
        <span></span>
        </div>

        <script type="text/javascript">
            var __scroll_pos = 0;
            var __scroll_anchor = 0;
            $(document).ready(function() {
                $('#ti-listHeader').click(loadCats);
                $('#ti-mastercontain').trigger('widthChanged');
                $('#ti-listHolder').scroll(function(eventScr) {
                    if (DEBUG)
                        console.log("handler triggered, finaliseListLoad on " + __current_cat + " with " + __scroll_pos + "%" + $('#ti-listHolder').scrollTop());
                    if (DEBUG) console.warn("awaiting retry: " + ($('#ti-listHolder .ti-retryItem').length > 0));
                    if (!$('#ti-listHolder .ti-retryItem').length)
                        if ($('.ti-witem:last-child').visible(true, true, 'both', $('#ti-listHolder'))) {
                                console.warn('loading more on primescroll trigger...');
                                loadMore();
                            }
                    var list = $('#ti-listHolder');
                    var head = $('#ti-listHeader');
                    if (__scroll_pos <= list.scrollTop())
                    {
                        __scroll_pos = list.scrollTop();
                        head.css('top', -Math.min(__scroll_pos - __scroll_anchor, 50) + 'px');
                    }
                    else
                    {
                        __scroll_pos = list.scrollTop();
                        head.css('top', '0px');
                        __scroll_anchor = __scroll_pos;
                    }
                });
                getTiConf(function() {
                    loadCats();
                    /*loadMore(true);*/
                });
                // DEAD BTNS
                $('#ti-eventHolder .ti-btn.ti-dead').click(function (event) {
                    switchToDead();
                });
                $('#ti-pickHolder .ti-btn.ti-dead').click(function (event) {
                    switchToEvent();
                });
                $('#ti-seatHolder .ti-btn.ti-dead').click(function (event) {
                    switchToPick();
                });
                // ACCEPT BTNS
                $('#ti-eventHolder .ti-btn:not(.ti-dead)').click(function(event) {
                    switchToPick();
                    $('#ti-pickHolder .ti-xcontainer').empty();
                    lockLoader(true);
                    getShowtimes(__active_event.urn, function(zirdat) {
                        if (!zirdat.ok) {
                            showError("بارگذاری اطلاعات با مشکل بر خورد.", 
                                null,
                                () => switchToEvent());
                        }
                        console.log(zirdat);
                        for (var i = 0; i < zirdat.data.length; i++)
                        {
                            var _dt = zirdat.data[i];
                            //console.log(_dt);
                            addPick(_dt);
                        }
                        lockLoader(false);
                    },
                    () => switchToEvent());
                });
            });
        </script>

        <table id="ti-cardWrapper">
            <tr id="ti-bannerHolder" class="ti-rightside">
                <td>
                    <div>
                        <img />
                    </div>
                </td>
            </tr>
            <tr id="ti-eventHolder" class="fulfilled ti-leftside">
                <td>
                    <div>
                        <div class="ti-prefix"></div>
                        <div class="ti-title"></div>
                        <div class="ti-seperator"></div>
                        <div class="ti-xcontainer">
                            <p><i class="material-icons">people</i><span class="ti-nxaut"></span></p>
                            <p><i class="material-icons">room</i><span class="ti-nxloc"></span></p>
                            <p><i class="material-icons">event</i><span class="ti-nxdat"></span></p>
                            <p><i class="material-icons">credit_card</i><span class="ti-nxprc"></span></p>
                        </div>
                        <span class="ti-btnwrap">
                            <div class="ti-btn">خرید</div>
                            <div class="ti-btn ti-dead">بستن</div>
                        </span>
                    </div>
                </td>
            </tr>
            <tr id="ti-pickHolder" class="fulfilled ti-rightside">
                <td>
                    <div>
                        <div class="ti-prefix"></div>
                        <div class="ti-title"></div>
                        <div class="ti-seperator"></div>
                        <div class="ti-xcontainer">
                        </div>
                        <span class="ti-btnwrap">
                            <!--<div class="ti-btn">ادامه</div>-->
                            <div class="ti-btn ti-dead">برگشت</div>
                        </span>
                    </div>
                </td>
            </tr>
            <tr id="ti-seatHolder" class="ti-leftside">
                <td>
                    <div>
                        <div class="ti-prefix"></div>
                        <div class="ti-title"></div>
                        <div class="ti-seperator"></div>
                        <div class="ti-xframe">
                        </div>
                        <div class="ti-xcontainer">
                            <div></div>
                            <div></div>
                        </div>
                        <span class="ti-btnwrap">
                            <div class="ti-btn ti-locked">ادامه</div>
                            <div class="ti-btn ti-dead">برگشت</div>
                        </span>
                    </div>
                </td>
            </tr>
        </table>

        <div id="ti-catSel" class="ti-xHolder ti-currentcard">
            <div>
                <div></div>
            </div>
        </div>
        <div class="ti-xHolder ti-currentcard ti-hidden"></div>

        <div id="ti-loader" class="ti-xHolder ti-currentcard">
            <i class="material-icons">people</i>
            <span><img src="https://zbcdn.cloud/images/tiwall_loader.gif" /></span>
        </div>

        <script type="text/javascript">


            function lockLoader(toggle) {
                    if (toggle) {
                        __load_lock = true;
                        $('#ti-loader').removeClass('ti-hidden');
                    }
                    else {
                        __load_lock = false;
                        $('#ti-loader').addClass('ti-hidden');
                    }
            }
        </script>
    </div>
    <div id="ti-errorHandle" class="ti-xHolder ti-currentcard">
        <h2>خطا</h2>
        <span class="ti-xname"></span>
        <span class="ti-btnwrap">
            <div class="ti-btn">تلاش مجدد</div>
            <div class="ti-btn ti-dead">برگشت</div>
        </span>
    </div>
</body>