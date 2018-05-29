let __current_data = null;
let __last_count = 0;
let __load_lock = false;
let __open_pageid = null;
let __active_event = null;
let __current_cat = null;
let __eol = false;
let __vouchtimer = null;

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

function displayEventItem(htmlx, coords) {
    var list = $('#ti-listHolder');
    list.append(htmlx);
    if (DEBUG)
        console.log("rendering item " + coords.i + "/" + coords.max);
    $('#ti-listHolder .ti-witem:last-child').click(function() { 
        initEventPage($(this).attr('itemid')); 
    });
    if (coords.i === (coords.max || 0) - 1)
        setTimeout(finaliseListLoad, 200);
}

function initEventPage(i) {
    __active_event = __current_data.data[i];
    if (DEBUG) console.log(__active_event);
    switchToEvent();
    $('#ti-cardWrapper #ti-pickHolder ~ .flex-tr').removeClass('fulfilled');
    $('#ti-cardWrapper .ti-prefix').text(__current_data.data[i].title_prefix);
    $('#ti-cardWrapper .ti-title').text(__current_data.data[i].title);
    $('#ti-bannerHolder img').attr('src', "");
    if (__current_data.data[i].image)
        $('#ti-bannerHolder img').attr('src', __current_data.data[i].image.normal_url || "");

    // DIR & AUTH
    _nxaut = processMiniCast(__current_data.data[i]);
    if (_nxaut) {
        $('#ti-eventHolder .ti-xcontainer p:nth-child(1)').removeClass('ti-hidden');
        $('#ti-eventHolder .ti-xcontainer .ti-nxaut').text(_nxaut);
    }
    else
        $('#ti-eventHolder .ti-xcontainer p:nth-child(1)').addClass('ti-hidden');

    // VENUE
    var _nxloc = __current_data.data[i].spec.hasOwnProperty('venue') ? __current_data.data[i].spec.venue : null;
    _nxloc = (!_nxloc) ? null : _nxloc.title;
    if (_nxloc) {
        $('#ti-eventHolder .ti-xcontainer p:nth-child(2)').removeClass('ti-hidden');
        $('#ti-eventHolder .ti-xcontainer .ti-nxloc').text(toLocalisedNumbers(_nxloc));
    }
    else
        $('#ti-eventHolder .ti-xcontainer p:nth-child(2)').addClass('ti-hidden');

    // DATETIME
    var _nxtime = __current_data.data[i].spec.hasOwnProperty('time') ? __current_data.data[i].spec.time : "";
    _nxtime = (!_nxtime) ? "" : _nxtime.text;
    var _nxdat = __current_data.data[i].spec.hasOwnProperty('date_duration_text') ? (__current_data.data[i].spec.date_duration_text || "") : "";
    if (_nxdat || _nxtime) {
        $('#ti-eventHolder .ti-xcontainer p:nth-child(3)').removeClass('ti-hidden');
        $('#ti-eventHolder .ti-xcontainer .ti-nxdat').text(toLocalisedNumbers(_nxdat + ' ' + _nxtime));
    }
    else
        $('#ti-eventHolder .ti-xcontainer p:nth-child(3)').addClass('ti-hidden');

    // PRICE
    var _nxprc = __current_data.data[i].hasOwnProperty('price') ? __current_data.data[i].price : "";
    _nxprc = (!_nxprc) ? null : _nxprc.text;
    if (_nxprc) {
        $('#ti-eventHolder .ti-xcontainer p:nth-child(4)').removeClass('ti-hidden');
        $('#ti-eventHolder .ti-xcontainer .ti-nxprc').text(toLocalisedNumbers(_nxprc));
    }
    else
        $('#ti-eventHolder .ti-xcontainer p:nth-child(4)').addClass('ti-hidden');
    $('#ti-eventHolder .ti-seperator').text(__current_data.data[i].short_desc || "");
    $('#ti-eventHolder .ti-xplate').text(__current_data.data[i].promo_desc || "");
}

let __current_instance = null;
let __instances = null;
function addPick(datZ) {
    lockLoader(true);
    var ops = { 'id': datZ.id || "", 'name': datZ.title || "خرید", 'info': datZ.remained_text || "" };
    getEventPickHtml(ops, function (xhtml) {
        $('#ti-pickHolder .ti-xcontainer').append(xhtml);
        if (!datZ.remained) $('#ti-pickHolder .ti-witem:last-child').addClass('disabled');
        if (__active_event.sale.method === 'event_seat') {
            $('#ti-pickHolder .ti-witem:last-child').click(function (event) {
                $('#ti-seatHolder').removeClass('ti-numeric');
                $('#ti-seatHolder').addClass('ti-seatmap');
                $('#ti-pickHolder .ti-witem').removeClass('selected');
                $(this).addClass('selected');
                switchToSeat();
                $('#ti-seatHolder').addClass('fulfilled');
                if (datZ.title)
                    $('#ti-pickHolder ~ tr .ti-prefix').text(datZ.title);
                $('#ti-seatHolder .ti-xframe').empty();
                $('#ti-seatHolder .ti-xcontainer').empty();
                lockLoader(true);
                __current_instance = $(this).attr('itemid');
                getSeatmap(__active_event.urn, { 'showtime_id': $(this).attr('itemid') },
                    function (jsdat) {
                        //if (DEBUG)
                        //    console.log(jsdat.data);
                        //var jsdat = JSON.parse(_jsdat);
                        //if (DEBUG)
                        //    jsdat.data.html = jsdat.data.html.replace('https://store.zirbana.com/resource/js/hallRenderer-v2.js', '/engine/hallRenderer-v2.js');
                        $('#ti-seatHolder .ti-xframe').html(jsdat.data.html);
                        $('#ti-hallstyle').html(jsdat.data.css);
                        $('#ti-seatHolder .ti-seperator').empty();
                        if (DEBUG) 
                            console.warn(jsdat.data.sections.length + " sections");
                        for (var seat in jsdat.data.sections) {
                            $('#ti-seatHolder .ti-seperator').append(
                                '<span itemid="' + jsdat.data.sections[seat].id + '">' + jsdat.data.sections[seat].title + '</span>');
                            $('#ti-seatHolder .ti-seperator span:last-child').click(function () {
                                $('#ti-seatHolder .ti-seperator span').removeClass('selected');
                                $(this).addClass('selected');
                                selectSectionById($(this).attr('itemid'));
                            });
                        }
                        $('#ti-seatHolder .ti-seperator span:first-child').addClass('selected');
                        lockLoader(false);
                    })
            });
        } else {
            $('#ti-pickHolder .ti-witem:last-child').click(function () {
                __current_instance = $(this).attr('itemid');
                if (datZ.title)
                    $('#ti-pickHolder ~ tr .ti-prefix').text(datZ.title);
                $('#ti-seatHolder').addClass('ti-numeric');
                $('#ti-seatHolder').removeClass('ti-seatmap');
                $('#ti-seatHolder .ti-spinner span.value').text(toLocalisedNumbers(1));
                $('#ti-seatHolder .ti-spinner .numeric input').val(1);
                onExoticNumericChange($('#ti-seatHolder .ti-spinner .numeric input'));
                switchToSeat();
            });
        }
        lockLoader(false);
    });
}

function onExoticNumericChange(numeric) {
    if (DEBUG) console.log(numeric);
    if ($('#ti-seatHolder .ti-spinner input').is(numeric)) {
        onNumericSeatChange(parseInt(numeric.val()));
    }
}

let __finalSeatData = null;
function onSeatSelectionChange(data) {
    let _data = JSON.parse(data);
    if (DEBUG) console.log(_data);
    $('#ti-seatHolder .ti-xcontainer').text(_data.summary);
    __finalSeatData = _data;
    if (count) {
        $("#ti-seatHolder .ti-btnwrap .ti-btn:first-child").removeClass('ti-locked');
    }
    else {
        $("#ti-seatHolder .ti-btnwrap .ti-btn:first-child").addClass('ti-locked');
    }
}
function onNumericSeatChange(num) {
    $("#ti-seatHolder .ti-btnwrap .ti-btn:first-child").removeClass('ti-locked');
    thisIns = __instances.find(x => x.id == __current_instance);
    __finalSeatData = { seats: '', count: num, total_price: (__current_instance.price || __active_event.price.list[0]) * num };
}

function addItem(i, offset, datX, max) {
    lockLoader(true);
    // DIR & AUTH
    _nxaut = processMiniCast(__current_data.data[i]);
    // VENUE
    var _nxloc = __current_data.data[i].spec.hasOwnProperty('venue') ? __current_data.data[i].spec.venue : null;
    _nxloc = (!_nxloc) ? null : _nxloc.title;
    // DATETIME
    var _nxtime = __current_data.data[i].spec.hasOwnProperty('time') ? __current_data.data[i].spec.time : "";
    _nxtime = (!_nxtime) ? "" : _nxtime.text;
    var _nxdat = __current_data.data[i].spec.hasOwnProperty('date_duration_text') ? (__current_data.data[i].spec.date_duration_text || "") : "";

    getEventItemHtml({
        'name': toLocalisedNumbers(datX.title),
        'aut': _nxaut,
        'ac': _nxaut ? '' : 'ti-hidden',
        'time': _nxtime,
        'tc': _nxtime ? '' : 'ti-hidden',
        'place': _nxloc,
        'pc': _nxloc ? '' : 'ti-hidden',
        'image': datX.image.thumb_url,
        'id': i + offset
    }, displayEventItem, { max: max, i: i });
    lockLoader(false);
}

function addCat(datX) {
    lockLoader(true);
    getCategoryHtml({
        'name': toLocalisedNumbers(datX.text),
        'color': datX.color || "var(--ti-accent)",
        'img': datX.image === undefined ? "https://zbcdn.cloud/files/icons/icon_general_white.png" : datX.image.normal_url,
        'key': datX.key
    }, function (htmlx) {
        $('#ti-catSel > div > div').append(htmlx);
        $('#ti-catSel > div > div .ti-citem:last-child')
            .hover(function () {
                $('#ti-catSel').css('background', $(this).attr('catcol')).addClass('ti-contrive');
            }, function () {
                $('#ti-catSel').css('background', 'var(--ti-blind)').removeClass('ti-contrive');
            })
            .click(function () {
                var ti = $(this);
                $('#ti-catSel').css('top', '-100%');
                $('#ti-catSel + div').removeClass('ti-hidden');
                $('#ti-catSel + div').css('top', '-100%').css('background', ti.attr('catcol'));
                $('#ti-listHeader').css('top', '0px').css('background', ti.attr('catcol')).children('span').text(ti.children('.ti-name').text());
                __current_cat = ti.attr('itemid');
                loadMore(true);
            });
    });
    lockLoader(false);
}

function finaliseListLoad() {
    if (DEBUG)
        console.log("calling finaliseListLoad on " + __current_cat + " with #" + __last_count);
    $('#ti-listHolder').trigger("scroll");
    if (__config.js.scroll)
        syncViewSize();
    if (DEBUG)
        console.log("call ended, finaliseListLoad on " + __current_cat + " with #" + __last_count);
}

function loadMore(force) {
    syncViewSize();
    $('#ti-singlePic').addClass('ti-hidden');
    $('#ti-eventHolder .ti-btn.ti-dead').removeClass('ti-hidden');
    if (__load_lock) {
        if (DEBUG)
            console.warn("called @loadMore with lock[" + __load_lock + "]");
        return;
    }
    if ((__last_count < 20 && !force) || __eol) return;
    if (force)
        clearList();
    lockLoader(true);
    getTiEventList(__current_cat, force ? null : {
        'order_token': __current_data.meta.order_token,
        'offset': __current_data.data.length
    }, function (datJ) {
        $('#ti-listHolder .ti-retryItem').remove();
        if (force) {
            __current_data = datJ;
            __last_count = 0;
        }
        if (datJ.data.length)
            __last_count += datJ.data.length;
        else
            __eol = true;
        for (var i = 0; i < datJ.data.length; i++) {
            if (!force)
                __current_data.data.push(datJ.data[i]);
            addItem(i, datJ.meta.offset, datJ.data[i], datJ.data.length);
        }
        lockLoader(false);
    }, function (e) {
        syncViewSize();
        getEventItemHtml({
            'name': "تلاش مجدد",
            'info': "خطایی رخ داد، این دکمه را بزنید تا مجددا بارگذاری انجام شود."
        }, function (htmlx) {
            var rti = $(htmlx).addClass('ti-retryItem');
            $('#ti-listHolder').append(rti);
            $('#ti-listHolder .ti-witem.ti-retryItem > div > span').addClass('material-icons').text('refresh').click(function() { loadMore(force); });
            if (DEBUG) console.log(rti);
        });
    });
}

function loadCats() {
    desyncViewSize();
    $('#ti-singlePic').addClass('ti-hidden');
    $('#ti-catSel').removeClass('ti-hidden');
    $('#ti-eventHolder .ti-btn.ti-dead').removeClass('ti-hidden');
    let ctx = "";
    let ctn = 0;
    let ctc = "";
    if (__config.categories && __config.categories._filter)
        ctx = __config.categories._filter.split(',');
    $('#ti-catSel').css('top', '0%');
    $('#ti-catSel + div').addClass('ti-hidden').css('top', '0%').css('background', 'var(--ti-blind)');
    $('#ti-listHeader').css('top', '100%').css('background', 'var(--ti-blind)').children('span').empty();
    $('#ti-catSel > div > div').empty();
    lockLoader(true);
    getTiCats(null, function (datJ) {
        for (var i = 0; i < datJ.data.length; i++) {
            if (!ctx || ctx.find(x => x == datJ.data[i].key) != undefined)
            {
                addCat(datJ.data[i]);
                ctn++;
                ctc = datJ.data[i];
            }
        }
        if (ctn == 1) {
            __current_cat = ctc.key;
            var ti = $(this);
            $('#ti-catSel').css('top', '-100%');
            $('#ti-catSel + div').removeClass('ti-hidden');
            $('#ti-catSel + div').css('top', '-100%').css('background', ctc.color);
            $('#ti-listHeader').css('top', '0px').css('background', ctc.color).children('span').text(ctc.text);
            $('#ti-listHeader').addClass('ti-disabled');
            loadMore(true);
        }
        else {
            $('#ti-listHeader').removeClass('ti-disabled');
        }
        lockLoader(false);
    });
}

function loadSingleView(urn) {
    lockLoader(true);
    $('#ti-singlePic').removeClass('ti-hidden');
    $('#ti-catSel').addClass('ti-hidden');
    $('#ti-eventHolder .ti-btn.ti-dead').addClass('ti-hidden');
    getZbInsecureData(urn, "info", null, function(xdata) {
        __current_data = { data: [
            xdata.data
        ]};
        var vpic = 
            xdata.data.cover.normal_url ||
            xdata.data.image.big_url ||
            xdata.data.image.normal_url
        $('#ti-singlePic').css('background-image', 'url(' + vpic + ')');
        lockLoader(false);
        initEventPage(0);
    });
}

function clearList() {
    $('#ti-listHolder').empty();
    __eol = false;
}

$(document).ready(function () {
    /*$('#ti-mastercontain').on('widthChanged', function (event, newW, oldW) {
        var stls = document.styleSheets;
        var largeCss = null;
        for (var i in stls)
            if (stls[i].title == 'largeCSS')
                largeCss = stls[i];
        if (newW > 500)
            largeCss.disabled = false;
        else
            largeCss.disabled = true;
    });*/
    $('#ti-seatHolder .ti-xcontainer div:first-child div').click(function (event) {
        $('#ti-seatHolder .ti-xcontainer div:first-child div').removeClass('ti-active');
        $(this).addClass('ti-active');
        selectSectionById($(this).attr('itemid'));
    });
});

function switchToDead() {
    $('#ti-listHolder').removeClass('ti-unfocus');
    $('#ti-cardWrapper .flex-tr').addClass('fulfilled');
    $('#ti-cardWrapper #ti-pickHolder ~ .flex-tr').removeClass('fulfilled');
    $('#ti-cardWrapper').get(0).style.setProperty('--shift', '-1');
    $('#ti-cardWrapper').get(0).style.setProperty('--stage', '-2');
    syncViewSize(true);
}
function switchToEvent() {
    $('#ti-listHolder').addClass('ti-unfocus');
    $('#ti-cardWrapper .flex-tr').addClass('fulfilled');
    $('#ti-cardWrapper #ti-pickHolder ~ tr').removeClass('fulfilled');
    $('#ti-cardWrapper').get(0).style.setProperty('--shift', '0');
    $('#ti-cardWrapper').get(0).style.setProperty('--stage', '0');
    desyncViewSize();
}
function switchToPick() {
    $('#ti-listHolder').addClass('ti-unfocus');
    $('#ti-cardWrapper .flex-tr').addClass('fulfilled');
    $('#ti-cardWrapper #ti-pickHolder ~ .flex-tr').removeClass('fulfilled');
    $('#ti-cardWrapper').get(0).style.setProperty('--shift', '1');
    $('#ti-cardWrapper').get(0).style.setProperty('--stage', '2');
    desyncViewSize();
}
function switchToSeat() {
    $('#ti-listHolder').addClass('ti-unfocus');
    $('#ti-cardWrapper .flex-tr').addClass('fulfilled');
    $('#ti-cardWrapper #ti-seatHolder ~ .flex-tr').removeClass('fulfilled');
    $('#ti-cardWrapper').get(0).style.setProperty('--shift', '2');
    $('#ti-cardWrapper').get(0).style.setProperty('--stage', '2');
    desyncViewSize();
}
function switchToFinal() {
    $('#ti-listHolder').addClass('ti-unfocus');
    $('#ti-cardWrapper .flex-tr').addClass('fulfilled');
    $('#ti-cardWrapper #ti-finalHolder ~ .flex-tr').removeClass('fulfilled');
    $('#ti-cardWrapper').get(0).style.setProperty('--shift', '3');
    $('#ti-cardWrapper').get(0).style.setProperty('--stage', '4');
    $('#ti-cardWrapper #ti-finalHolder').removeClass('ti-disabled');
    $('#ti-finalHolder #ti-xusecup input').off();
    $('#ti-finalHolder #ti-xusecup input').on('exotic:check', () => cuponUseCheck());
    checkFinalForm();
    desyncViewSize();
}
function switchToAftermath() {
    $('#ti-listHolder').addClass('ti-unfocus');
    $('#ti-cardWrapper .flex-tr').addClass('fulfilled');
    $('#ti-cardWrapper #ti-aftermathHolder ~ .flex-tr').removeClass('fulfilled');
    $('#ti-cardWrapper').get(0).style.setProperty('--shift', '4');
    $('#ti-cardWrapper').get(0).style.setProperty('--stage', '4');
    $('#ti-cardWrapper #ti-finalHolder').addClass('ti-disabled');
    desyncViewSize();
}

let __err_pass = null;
function showError(message, retry_callback, return_callback, pass) {
    desyncViewSize();
    $('#ti-mastercontain').addClass('errored');
    $('#ti-errorHandle span.ti-xname').text(message);
    lockLoader(false);

    $('#ti-errorHandle .ti-btnwrap .ti-btn:not(.ti-dead)').off();
    if (retry_callback) {
        $('#ti-errorHandle .ti-btnwrap .ti-btn:not(.ti-dead)').removeClass('ti-hidden');
        $('#ti-errorHandle .ti-btnwrap .ti-btn:not(.ti-dead)').click(function () {
            $('#ti-mastercontain').removeClass('errored');
            retry_callback(__err_pass);
        });
    }
    else {
        $('#ti-errorHandle .ti-btnwrap .ti-btn:not(.ti-dead)').addClass('ti-hidden');
    }

    $('#ti-errorHandle .ti-btnwrap .ti-btn.ti-dead').off();
    if (return_callback) {
        $('#ti-errorHandle .ti-btnwrap .ti-btn.ti-dead').removeClass('ti-hidden');
        $('#ti-errorHandle .ti-btnwrap .ti-btn.ti-dead').click(function () {
            $('#ti-mastercontain').removeClass('errored');
            return_callback(__err_pass);
        });
    }
    else {
        $('#ti-errorHandle .ti-btnwrap .ti-btn.ti-dead').addClass('ti-hidden');
    }

    if (pass)
        __err_pass = pass;
    else
        __err_pass = null;
}

function cuponUseCheck() {
    var isx = $('#ti-finalHolder #ti-xusecup').attr('check') === 'true';
    if (DEBUG) console.warn('Use cupon? >>' + isx);
    if (!isx) {
        $('#ti-finalHolder #ti-xcupon').addClass('ti-hidden');
        $('#ti-finalHolder #ti-xvouchstat').addClass('ti-hidden');
        $('#ti-finalHolder #ti-bvouch').addClass('ti-locked');
    }
    else {
        $('#ti-finalHolder #ti-xcupon').removeClass('ti-hidden');
        $('#ti-finalHolder #ti-xvouchstat').removeClass('ti-hidden');
        $('#ti-finalHolder #ti-bvouch').removeClass('ti-locked');
    }

    if ($('#ti-finalHolder #ti-xcupon').val().length >= 5)
        updateVouch($('#ti-finalHolder #ti-xcupon').val());
    checkFinalForm();
}

let __aftermath_timer = null;
function setupAftemath() {
    $('#ti-aftermathHolder #ti-xreserve').text(toLocalisedNumbers(__paymentClause.reserve_id));
    $('#ti-aftermathHolder #ti-xtrace').text(toLocalisedNumbers(__paymentClause.trace_number));
    $('#ti-aftermathHolder #ti-xfinalprice').text(toLocalisedNumbers(seperateDigits(__paymentClause.total_price, ',') + " تومان"));
    $('#ti-aftermathHolder .ti-btnwrap').removeClass('ti-hidden');
    switchToAftermath();
    $('#ti-aftermathHolder #ti-xrtimer').text("");
    __aftermath_timer = setInterval(reserveTimerTick, 1000);
}

function reserveTimerTick() {
    __paymentClause.time--;
    var time = __paymentClause.time;
    var strtime = Math.floor(time / 60) + ':' + (time % 60);
    $('#ti-aftermathHolder #ti-xrtimer').text(toLocalisedNumbers(strtime));
    if (time < 60)
        $('#ti-aftermathHolder #ti-xrtimer').addClass('ti-error');
    else
        $('#ti-aftermathHolder #ti-xrtimer').removeClass('ti-error');
    if (time <= 0) {
        getZbData(__active_event.urn, "check", __paymentClause, dat => {
            if (!dat.ok)
                switchToFinal();
            else {
                clearTimeout(__aftermath_timer);
                switch (dat.data.state) {
                    case "reserved":
                        switchToDead();
                        break;
                    case "pending": 
                        break;
                    default:
                        switchToFinal();
                        break;
                }
            }
        }, switchToDead)
    }
}

function causeAftermathPayment() {
    var payUrl = `${ZB_MAIN_URL}${__active_event.urn}/payment?reserve_id=${__paymentClause.reserve_id}&trace_number=${__paymentClause.trace_number}&callback=${encodeURI(decodeURI(__config.js.callback) + `?userxid=${__userid}`)}`;
    var nwo = window.open(payUrl, '_blank');
    if (window.focus)
        nwo.focus();
}

function updateVouch(voucher) {
    $('#ti-finalHolder #ti-xvouchstat').attr('valid', 'false');
    checkFinalForm();
    $('#ti-finalHolder #ti-xvouchstat').removeClass('ti-error');
    $('#ti-finalHolder #ti-xvouchstat').removeClass('ti-success');
    $('#ti-finalHolder #ti-xvouchstat').text('در حال بررسی...');
    getVoucherState(voucher, (msg, ok) => {
        $('#ti-finalHolder #ti-xvouchstat').text(msg);
        if (ok) {
            $('#ti-finalHolder #ti-xvouchstat').attr('valid', 'true');
            $('#ti-finalHolder #ti-xvouchstat').addClass('ti-success');
        }
        else {
            $('#ti-finalHolder #ti-xvouchstat').addClass('ti-error');
            $('#ti-finalHolder #ti-xvouchstat').attr('valid', 'false');
        }
        checkFinalForm();
    });
}

function checkFinalForm() {
    let arc = [];
    let validvouch = true;
    if ($('#ti-finalHolder #ti-xusecup').attr('check') === 'true') {
        arc = $('#ti-finalHolder input[type="text"]');
        if ($('#ti-finalHolder #ti-xvouchstat').attr('valid') !== 'true')
            validvouch = false;
    }
    else
        arc = $('#ti-finalHolder input[type="text"]:not(#ti-xcupon)');

    if (DEBUG) {
        console.log("check = " + $('#ti-finalHolder #ti-xusecup').attr('check'));
        console.warn("valid voucher? >>" + validvouch);
    }

    if (arc.is((i, e) => !$(e).val()) || !validvouch)
        $('#ti-finalHolder #ti-bpay').addClass('ti-locked');
    else    
        $('#ti-finalHolder #ti-bpay').removeClass('ti-locked');
}