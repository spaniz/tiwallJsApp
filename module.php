<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/core.css" />
    <link title="largeCSS" rel="stylesheet" href="style/large.css" />
    <link type="font/woff2" href="https://fonts.gstatic.com/s/materialicons/v34/2fcrYFNaTjcS6g4U3t-Y5ZjZjT5FdEJ140U2DJYC3mY.woff2" as="font" rel="preload" />
</head>
<body>
    <?php
        define('ROOTDIR', "");
        require_once("php/paths.php");
        $cfg = file_get_contents($config_path);
        $uconf = json_decode($cfg);
        if (!(!isset($_GET['user_id']) && $uconf->wordpress->forcelogin)) {
    ?>
    <div id="ti-mastercontain">
        <!--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">-->
        <style id="ti-hallstyle">
        </style>
    <?php if ($uconf->js->responsive) { ?>
        <style>
            @media only screen and (max-device-width: 480px) {
                :root {
                    font-size: 2.35px;
                }
            }
        </style>
    <?php } ?>
        <script type="text/javascript">
            let __config = <?php echo $cfg; ?>;
        <?php
            if (!isset($_GET['zb_result'])) {
                foreach ($_GET as $k => $v)
                    echo "__config." . str_replace('~', '.', $k) . " = '$v';";
            } else {
        ?>
            let callData = JSON.parse('<?php echo $_GET['zb_result']; ?>');
            let userReturn = JSON.parse('<?php echo $_GET['userxid']; ?>');
        <?php } ?>
        </script>   
        <script type="text/javascript" src="https://cdn.zirbana.com/js/jquery/1.7.2/jquery.min.js"></script>
        <script type="text/javascript" src="engine/utility.js"></script>
        <script type="text/javascript" src="engine/zb-engine.js"></script>
        <script type="text/javascript" src="engine/ti-get.js"></script>
        <script type="text/javascript" src="engine/itemparser.js"></script>
        <script type="text/javascript" src="engine/scrollsync.js"></script>
        <script type="text/javascript" src="engine/displayengine.js"></script>
        <script type="text/javascript" src="engine/exoticengine.js"></script>

        <div id="ti-listHolder"></div>
        <div id="ti-listHeader" style="top: 100%">
            <i class="material-icons" style="margin-top: -4px; margin-left: 7px">expand_less</i>
            <span></span>
        </div>
        <div id="ti-singlePic">
        </div>

        <script type="text/javascript">
            var __scroll_pos = 0;
            var __scroll_anchor = 0;
            var __userid = "<?php echo isset($_GET['user_id']) ? $_GET['user_id'] : 'null' ?>";
            //var __scroll_origin = null;
            $(document).ready(function() {
                $('#ti-mastercontain').trigger('widthChanged');
                configSync(__config.js.scroll);
            <?php if (!isset($_GET['zb_result'])) { ?>
                $('#ti-listHeader').click(loadCats);
                //__scroll_origin = $('#ti-listHolder');
                $('#ti-listHolder').on((__config.js.scroll ? 'sync:' : '') + 'scroll', function(eventScr) {
                    if (DEBUG)
                        console.log("handler triggered, finaliseListLoad on " + __current_cat + " with " + __scroll_pos + "%" + $('#ti-listHolder').scrollTop());
                    if (DEBUG) console.warn("awaiting retry: " + ($('#ti-listHolder .ti-retryItem').length > 0));
                    if (!$('#ti-listHolder .ti-retryItem').length) {
                        if (__config.js.scroll && eventScr) {
                            console.warn('loading more on forced trigger...');
                            loadMore();
                        }
                        else if (!__config.js.scroll && $('.ti-witem:last-child').visible(true, true, 'vertical', $('#ti-listHolder'))) {
                            console.log('loading more on primescroll trigger...');
                            loadMore();
                        }
                    }
                    var list = $('#ti-listHolder');
                    var head = $('#ti-listHeader');
                    if (__scroll_pos < list.scrollTop())
                    {
                        __scroll_pos = list.scrollTop();
                        head.css('top', '-50rem');
                    }
                    else
                    {
                        __scroll_pos = list.scrollTop();
                        head.css('top', '0rem');
                        __scroll_anchor = __scroll_pos;
                    }
                });
                if (__config.view == "normal")
                    loadCats();
                else if (__config.view == "single")
                    loadSingleView(__config.get.urn);
                if (__config.user.override) {
                    $('.ti-foruser').addClass('ti-hidden');
                    $('.ti-nonuser').removeClass('ti-hidden');
                } else {
                    $('.ti-foruser').removeClass('ti-hidden');
                    $('.ti-nonuser').addClass('ti-hidden');
                }
                if (__config.js.scroll) {
                    if (DEBUG) 
                        console.warn(">> scroll-sync allowed!");
                    //__scroll_origin = parent.document.firstElementChild;
                    parent.initOuterSync();
                    initInnerSync();
                }
                getTiConf(function() {
                    
                    /*loadMore(true);*/
                });
                $('#ti-finalHolder #ti-xcupon').keypress(function (event) {
                    if (event.keyCode === 13) {
                        if (__vouchtimer)
                            clearTimeout(__vouchtimer);
                        __vouchtimer = null;
                        $('#ti-finalHolder #ti-xvouchstat').text(""); 
                        updateVouch($('#ti-finalHolder #ti-xcupon').val());
                        return false;
                    }
                });
                $('#ti-finalHolder input[type="text"]').on('input', function (event) {
                    checkFinalForm();
                });
                $('#ti-finalHolder #ti-xcupon').on('input', function (event) {
                    $('#ti-finalHolder #ti-xvouchstat').text(""); 
                    $('#ti-finalHolder #ti-xvouchstat').attr('valid', 'false');
                    if (__vouchtimer)
                        clearTimeout(__vouchtimer);
                    __vouchtimer = null;
                    if ($('#ti-finalHolder #ti-xcupon').val().length >= 5) {
                        __vouchtimer = setTimeout(() => updateVouch($('#ti-finalHolder #ti-xcupon').val()), 1000);
                        if (DEBUG) console.log(__vouchtimer)
                    }
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
                        if (DEBUG) console.log(zirdat);
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
                    updateVouch($('#ti-finalHolder #ti-xcupon').val());
                });
                $('#ti-finalHolder #ti-bpay').click(function() {
                    goForPayment({ 'instance_id': __current_instance, 
                        'seats': __finalSeatData.seats, 
                        'count': __finalSeatData.count, 
                        'user_fullname': (__config.user.override) ? __config.user.fullname : $('#ti-finalHolder #ti-uname').val(),
                        'user_mobile': (__config.user.override) ? __config.user.mobile : $('#ti-finalHolder #ti-umobile').val(),
                        'user_email': (__config.user.override) ? __config.user.email : $('#ti-finalHolder #ti-umail').val(),
                        'voucher': $('#ti-finalHolder #ti-xusecup').attr('check') === 'true' ? $('#ti-finalHolder #ti-xcupon').val() : '',
                        'send_sms': (!__config.user.override), 
                        'send_email': (!__config.user.override), 
                        'use_internal_receipt': false });
                });
                $('#ti-aftermathHolder #ti-bxpay').click(() => causeAftermathPayment());
            <?php } else { ?>
                
                $('#ti-receiptHolder .ti-btn:not(.ti-dead)').click(function (event) {
                    var link = document.createElement('a');
                    link.setAttribute('href', callData.data.attachment_url);
                    link.setAttribute('download', 'tiwall${callData.data.trace_number}.pdf');
                    link.setAttribute('target', '_blank');
                    link.style.display = 'none';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                });
                loadSingleView(callData.ok ? callData.data.sale.urn : 'sampleEvent');
                if (callData.ok) {
                    $('#ti-receiptHolder #okCBD').removeClass('ti-hidden');
                    $('#ti-receiptHolder .ti-title').text(callData.data.sale.title);
                    $('#ti-receiptHolder .ti-suffix').text(callData.data.item_behavior == "event" ? callData.data.venue.title : "");
                    switch (callData.data.sale.deliver_type) {
                        case "receipt_station":
                            $('#ti-receiptHolder .ti-seperator img').removeClass('ti-hidden').attr('src', callData.data.attachment_url);
                            break;
                        case "ticket":
                            $('#ti-receiptHolder .ti-btnwrap ti-btn').removeClass('ti-hidden');
                            break;
                    }
                    $('#ti-receiptHolder .ti-rcinst').text(callData.data.instance.title);
                    $('#ti-receiptHolder .ti-rcseat').text(callData.data.sale.method == "event_seat" ? toLocalisedNumbers(callData.data.seats) : (toLocalisedNumbers(callData.data.seats) + " عدد"));
                    $('#ti-receiptHolder .ti-rctrace').text("کد پیگیری " + toLocalisedNumbers(callData.data.trace_number));
                    if (callData.data.item_behavior == "event") {
                        $('#ti-receiptHolder #ti-rcaddr').removeClass('ti-hidden');
                        $('#ti-receiptHolder .ti-rcaddr').text(callData.data.venue.address);
                    }
                }
                else {
                    $('#failCBD').removeClass('ti-hidden');
                }
            <?php } ?>
            });
        </script>

        <div id="ti-cardWrapper">
        <?php if (isset($_GET['zb_result'])) { ?>
            <div id="ti-receiptHolder" class="flex-tr ti-centrespan">
                <script>
                    
                </script>
                <div>
                    <div>
                        <div class="ti-title"></div>
                        <div class="ti-suffix"></div>
                        <div class="ti-seperator">
                            <img class="ti-hidden" style="max-height: 100rem;" src="" />
                        </div>

                        <div id="okCBD" class="ti-xcontainer ti-hidden">
                            <p><i class="material-icons">event</i><span class="ti-rcinst"></span></p>
                            <p><i class="material-icons">event_seat</i><span class="ti-rcseat"></span></p>
                            <p><i class="material-icons">label</i><span class="ti-rctrace"></span></p>
                            <p id="ti-rcaddr" class="ti-hidden"><i class="material-icons">not_listed_location</i><span class="ti-rcaddr"></span></p>
                        </div>

                        <div id="failCBD" class="ti-xcontainer ti-hidden">
                            <p><i class="material-icons">warning</i><span>پرداخت شما با شکست مواجه شد، لطفا دوباره تلاش کنید.</span></p>
                        </div>

                        <span class="ti-btnwrap">
                            <div class="ti-btn ti-hidden">دانلود بلیت</div>
                        </span>
                    </div>
                </div>
            </div>
        <?php } else { ?>
            <div id="ti-bannerHolder" class="flex-tr ti-rightside">
                <div>
                    <div>
                        <img />
                    </div>
                </div>
            </div>
            <div id="ti-eventHolder" class="flex-tr fulfilled ti-leftside">
                <div>
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
                        <div class="ti-xplate">
                            
                        </div>
                        <span class="ti-btnwrap">
                            <div class="ti-btn">خرید</div>
                            <div class="ti-btn ti-dead">بستن</div>
                        </span>
                    </div>
                </div>
            </div>
            <div id="ti-pickHolder" class="flex-tr fulfilled ti-rightside">
                <div>
                    <div>
                        <div class="ti-prefix"></div>
                        <div class="ti-title"></div>
                        <div class="ti-seperator"></div>
                        <div class="ti-xcontainer">
                        </div>
                        <span class="ti-btnwrap">
                            <!-- <div class="ti-btn">ادامه</div> -->
                            <div class="ti-btn ti-dead">برگشت</div>
                        </span>
                    </div>
                </div>
            </div>
            <div id="ti-seatHolder" class="flex-tr ti-leftside ti-seatmap">
                <div>
                    <div>
                        <div class="ti-prefix"></div>
                        <div class="ti-title"></div>
                        <div class="ti-seperator"></div>
                        <div class="ti-spinner">
                            <span style="display: inline-block; margin: 10px;">به تعداد</span>
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
                </div>
            </div>
            <div id="ti-finalHolder" class="flex-tr ti-rightside">
                <div>
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
                            <div class="ti-duo ti-nonuser">
                                <div class="ti-rightside">
                                    <span>مشخصات خرید</span>
                                </div>
                                <div class="ti-leftside">
                                    <span class="ti-uplate"></span>
                                </div>
                            </div>
                            <div class="ti-duo ti-foruser">
                                <div class="ti-rightside">
                                    <span>نام و نام خانوادگی</span>
                                </div>
                                <div class="ti-leftside">
                                    <input type="text" id="ti-uname" style="direction: rtl; text-align: right;" class="exotic-input textbox" name="u_name" />
                                </div>
                            </div> 
                            <div class="ti-duo ti-foruser">
                                <div class="ti-rightside">
                                    <span>آدرس ایمیل</span>
                                </div>
                                <div class="ti-leftside">
                                    <input type="text" id="ti-umail" style="direction: ltr; text-align: left;" class="exotic-input textbox" name="u_mail" />
                                </div>
                            </div> 
                            <div class="ti-duo ti-foruser">
                                <div class="ti-rightside">
                                    <span>شماره موبایل</span>
                                </div>
                                <div class="ti-leftside">
                                    <input type="text" id="ti-umobile" style="direction: ltr; text-align: left;" class="exotic-input textbox" name="u_mobile" />
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
                                    <input type="text" style="direction: ltr; text-align: left;"  id="ti-xcupon" class="exotic-input textbox ti-hidden" name="u_cupon">
                                </div>
                            </div>
                        </div>
                        <span class="ti-btnwrap">
                            <div id="ti-bpay" class="ti-btn">رزرو</div>
                            <div id="ti-bvouch" class="ti-btn ti-locked">چک کد تخفیف</div>
                            <div class="ti-btn ti-dead">برگشت</div>
                        </span>
                    </div>
                </div>
            </div>
            <div id="ti-aftermathHolder" class="flex-tr ti-leftside">
                <div>
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
                </div>
            </div>
        <?php } ?>
        </div>

        <div id="ti-catSel" class="ti-xHolder ti-currentcard">
            <div>
                <div></div>
            </div>
        </div>
        <div class="ti-xHolder ti-currentcard ti-hidden"></div>

        <div id="ti-loader" class="ti-xHolder ti-currentcard">
            <i class="material-icons">people</i>
            <span>
                <object style="max-height: 250px" data="<?= json_decode($cfg)->js->loading ?>">
                    <img src="https://zbcdn.cloud/images/tiwall_loader.gif"/>
                </object>
            </span>
        </div>
    </div>
    <div id="ti-errorHandle" class="ti-xHolder ti-currentcard">
        <h2>خطا</h2>
        <span class="ti-xname"></span>
        <span class="ti-btnwrap">
            <div class="ti-btn">تلاش مجدد</div>
            <div class="ti-btn ti-dead">برگشت</div>
        </span>
    </div>
    <?php } else { ?>
    <div id="ti-errorHandle" class="ti-xHolder ti-cutru" style="opacity: 0">
        <h2>خطا</h2>
        <span class="ti-xname">دسترسی شما بدون لاگین در این بخش میسر نمی‌باشد.</span>
    </div>
    <?php } ?>
</body>