<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <?php
        define('ROOTDIR', "");
        require_once("php/paths.php")
    ?>
    <div id="ti-mastercontain">
        <!--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">-->
        <link rel="stylesheet" href="../style/core.css" />
        <link title="largeCSS" rel="stylesheet" href="style/large.css" />
        <link type="font/woff2" href="https://fonts.gstatic.com/s/materialicons/v34/2fcrYFNaTjcS6g4U3t-Y5ZjZjT5FdEJ140U2DJYC3mY.woff2" as="font" rel="preload" />
        <script type="text/javascript">
            let __config = <?= file_get_contents($config_path_madule) ?>;
            <?php
                foreach ($_GET as $k => $v)
                    echo "__config." . str_replace('~', '.', $k) . " = '$v';";
            ?>
        </script>   
        <script type="text/javascript" src="https://cdn.zirbana.com/js/jquery/1.7.2/jquery.min.js"></script>
        <script type="text/javascript" src="../engine/utility.js"></script>
        <script type="text/javascript" src="../engine/ti-get.js"></script>
        <script type="text/javascript" src="../engine/itemparser.js"></script>
        <script type="text/javascript" src="../engine/displayengine.js"></script>
        <script type="text/javascript" src="../engine/exoticengine.js"></script>

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
                $('#ti-finalHolder .ti-btn.ti-dead').click(function (event) {
                    switchToSeat();
                });
                $('#ti-aftermathHolder .ti-btn.ti-dead').click(function (event) {
                    cancelAftermath(switchToFinal);
                });
                // ACCEPT BTNS
                $('#ti-eventHolder .ti-btn:not(.ti-dead)').click(function(event) {
                    switchToPick();
                    $('#ti-pickHolder .ti-xcontainer').empty();
                    lockLoader(true);
                    getShowtimes(__active_event.urn, function(zirdat) {
                        if (!zirdat.ok) {
                            addPick({ title: 'سانسی برای این برنامه وجود ندارد' });
                            return;
                        }
                        console.log(zirdat);
                        for (var i = 0; i < zirdat.data.length; i++)
                        {
                            __instances = zirdat.data;
                            var _dt = zirdat.data[i];
                            //console.log(_dt);
                            addPick(_dt);
                        }
                        lockLoader(false);
                    },
                    () => switchToEvent());
                });
                $('#ti-seatHolder .ti-btn:not(.ti-dead)').click(function(event) {
                    $('#ti-finalHolder #ti-xseats').text(toLocalisedNumbers(__finalSeatData.seats || "") || toLocalisedNumbers(__finalSeatData.count));
                    $('#ti-finalHolder #ti-xcost').text(toLocalisedNumbers(seperateDigits(__finalSeatData.total_price, ',') + " تومان"));
                    switchToFinal();
                });
                $('#ti-finalHolder #ti-bvouch').click(function() {
                    getVoucherState($('#ti-finalHolder #ti-xcupon').val(), (msg, ok) => {
                        $('#ti-finalHolder #ti-xvouchstat').text(msg);
                        if (ok)
                            $('#ti-finalHolder #ti-xvouchstat').removeClass('ti-error');
                        else 
                            $('#ti-finalHolder #ti-xvouchstat').addClass('ti-error');
                    })
                });
                $('#ti-finalHolder #ti-bpay').click(function() {
                    goForPayment({ 'instance_id': __current_instance, 
                        'seats': __finalSeatData.seats, 
                        'count': __finalSeatData.count, 
                        'user_fullname': $('#ti-finalHolder #ti-uname').val(),
                        'user_mobile': $('#ti-finalHolder #ti-umobile').val(),
                        'user_email': $('#ti-finalHolder #ti-umail').val(),
                        'voucher': $('#ti-finalHolder #ti-xcupon').val(),
                        'send_sms': true, 
                        'send_email': true, 
                        'use_internal_receipt': true });
                });
                $('#ti-aftermathHolder #ti-bxpay').click(() => causeAftermathPayment());
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
            <tr id="ti-seatHolder" class="ti-leftside ti-seatmap">
                <td>
                    <div>
                        <div class="ti-prefix"></div>
                        <div class="ti-title"></div>
                        <div class="ti-seperator"></div>
                        <div class="ti-spinner">
                            <span style="display: inline-block; margin: 10px;">تعداد صندلی</span>
                            <div class="numeric">
                                <div class="rem">remove_circle</div>
                                <span class="value">۱</span>
                                <input id="ti-seatcount" type="number" value="1" min="1" max="20" />
                                <div class="add">add_circle</div>
                            </div>
                        </div>
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
            <tr id="ti-finalHolder" class="ti-rightside">
                <td>
                    <div>
                        <div class="ti-prefix"></div>
                        <div class="ti-title"></div>
                        <div class="ti-seperator"></div>
                        <div class="ti-xcontainer">
                            <div class="ti-duo">
                                <div class="ti-rightside">
                                    <span>صندلی‌ها</span>
                                </div>
                                <div class="ti-leftside">
                                    <span id="ti-xseats"></span>
                                </div>
                            </div>  
                            <div class="ti-duo">
                                <div class="ti-rightside">
                                    <span>بهای کل</span>
                                </div>
                                <div class="ti-leftside">
                                    <span id="ti-xcost"></span>
                                </div>
                            </div> 
                            <div class="ti-duo">
                                <div class="ti-rightside">
                                    <span>نام و نام خانوادگی</span>
                                </div>
                                <div class="ti-leftside">
                                    <input id="ti-uname" class="exotic-input textbox" name="u_name" />
                                </div>
                            </div> 
                            <div class="ti-duo">
                                <div class="ti-rightside">
                                    <span>آدرس ایمیل</span>
                                </div>
                                <div class="ti-leftside">
                                    <input id="ti-umail" class="exotic-input textbox" name="u_mail" />
                                </div>
                            </div> 
                            <div class="ti-duo">
                                <div class="ti-rightside">
                                    <span>شماره موبایل</span>
                                </div>
                                <div class="ti-leftside">
                                    <input id="ti-umobile" class="exotic-input textbox" name="u_mobile" />
                                </div>
                            </div>
                            <div class="ti-duo">
                                <div class="ti-rightside">
                                    <div id="ti-xusecup" class="exotic-input checkbox">
                                        <input type="checkbox" name="u_usecupon" />
                                    </div>
                                    <span>کد تخفیف</span>
                                    <br/>
                                    <span id="ti-xvouchstat"></span>
                                </div>
                                <div class="ti-leftside">
                                    <input style="display: none" id="ti-xcupon" class="exotic-input textbox" name="u_cupon">
                                </div>
                            </div>
                        </div>
                        <span class="ti-btnwrap">
                            <div id="ti-bpay" class="ti-btn">رزرو</div>
                            <div id="ti-bvouch" class="ti-btn ti-locked">چک کد تخفیف</div>
                            <div class="ti-btn ti-dead">برگشت</div>
                        </span>
                    </div>
                </td>
            </tr>
            <tr id="ti-aftermathHolder" class="ti-leftside">
                <td>
                    <div>
                        <div class="ti-prefix"></div>
                        <div class="ti-title"></div>
                        <div class="ti-seperator"></div>
                        <div class="ti-xcontainer">
                            <div class="ti-duo">
                                <div class="ti-rightside">
                                    <span>کد رزرو</span>
                                </div>
                                <div class="ti-leftside">
                                    <span id="ti-xreserve"></span>
                                </div>
                            </div>
                            <div class="ti-duo">
                                <div class="ti-rightside">
                                    <span>کد رهگیری</span>
                                </div>
                                <div class="ti-leftside">
                                    <span id="ti-xtrace"></span>
                                </div>
                            </div>
                            <div class="ti-duo">
                                <div class="ti-rightside">
                                    <span>بهای نهایی</span>
                                </div>
                                <div class="ti-leftside">
                                    <span id="ti-xfinalprice" style="font-size: 20rem"></span>
                                </div>
                            </div>
                        </div>
                        <div style="margin-top: 15px; display: flex; justify-content: space-around; font-size: 36px; color: var(--ti-accent)">
                            <div id="ti-xrtimer" class="ti-error"></div>
                        </div>
                        <span class="ti-btnwrap ti-hidden">
                            <div id="ti-bxpay" class="ti-btn">پرداخت</div>
                            <div class="ti-btn ti-dead">لغو</div>
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