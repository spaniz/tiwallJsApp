const DEBUG = __config.js.debug;

// specify your framework type (based on zb-agent file extension)
const __FRAMEWORK = "php";

const __RESERVETIME = 900;

// Tiwall API Engine
let __lastTiResponse = null;

function getLastTi() {
    return __lastTiResponse;
}

const TI_BASE_URL = "https://store.zirbana.com/v2";

function getTiPages(path, callback, error, passable) {
    var addr = TI_BASE_URL + "/pages/" + path;
    $.ajax(addr, { 
        data: { get_param: 'value' }, 
        dataType: 'json',
        success: function(result) {
            __lastTiResponse = result;
            if (!__lastTiResponse.ok) {
                showError("بارگذاری اطلاعات با مشکل بر خورد.", 
                    function(e) { getTiPages(e.path, e.callback, e.error, e.passable); lockLoader(true); },
                    error,
                    { path: path, callback: callback, error: error, passable: passable });
                return;
            }
            callback(__lastTiResponse);
        },
        error: function() {
            showError("بارگذاری اطلاعات با مشکل بر خورد.", 
                function(e) { getTiPages(e.path, e.callback, e.error, e.passable); lockLoader(true); },
                error,
                { path: path, callback: callback, error: error, passable: passable });
        },
        headers: {
            'Accept': 'text/json'
        },
        type: 'GET',
        timeout: 7000
    });
}

function getTiCats(attrs, callback, passable) {
    __current_data = null;
    __last_count = 0;
    __open_pageid = null;
    __active_event = null;
    __current_cat = null;
    __eol = false;
    var addr = "categories?";
    if (attrs)
        for (var key in attrs)
            addr += key + "=" + attrs[key] + '&';
    if (__config.categories && __config.categories.mode)
        addr += "mode=" + __config.categories.mode + '&';
    getTiPages(addr, callback, passable);
}

function getTiEventList(cat, attrs, callback, passable) {
    var addr = "list?" + (DEBUG ? "include_samples=1&" : "");
    if (cat)
        addr += "cat=" + cat + '&';
    if (__config.list.venue)
        addr += "venue=" + __config.list.venue + '&';
    if (attrs)
        for (var key in attrs)
            addr += key + "=" + attrs[key] + '&';
    getTiPages(addr, callback, passable);
}

function getTiEventItem(pageId, callback, passable) {
    var addr = "get?";
    if (pageId !== null )
        addr += "id=" + pageId;
    getTiPages(addr, callback, passable);
}

// Zirbana API Engine
const ZB_BASE_URL = "zb-agent." + __FRAMEWORK;
const ZB_MAIN_URL = "https://store.zirbana.com/v2/";

function getZbData(urn, action, params, callback, error) {
    var addr = ZB_BASE_URL + "?urn=" + urn + "&action=" + action;
    if (params)
        for (var key in params)
            addr += '&' + key + '=' + encodeURI(params[key]);
    console.warn('calling ' + addr);
    $.ajax(addr, { 
        data: { get_param: 'value' }, 
        dataType: 'json',
        success: callback,
        error: function() {
            showError("بارگذاری اطلاعات با مشکل بر خورد.", 
                function(e) { getZbData(e.urn, e.action, e.params, e.callback, e.error); lockLoader(true); },
                error,
                { urn, callback, params, action, error });
        },
        timeout: 10000
    });
}

function getZbInsecureData(urn, action, params, callback, error) {
    var addr = ZB_MAIN_URL + urn + '/' + action + '?';
    if (params)
        for (var key in params)
            addr += key + '=' + encodeURI(params[key]) + '&';
    $.ajax(addr, { 
        data: { get_param: 'value' }, 
        dataType: 'json',
        success: callback,
        error: function() {
            showError("بارگذاری اطلاعات با مشکل بر خورد.", 
                function(e) { getZbData(e.urn, e.action, e.params, e.callback, e.error); lockLoader(true); },
                error,
                { urn, callback, params, action, error });
        },
        timeout: 10000
    });
}

function getShowtimes(urn, callback, error) {
    getZbInsecureData(urn, "instances", null, callback, error);
}

function getSeatmap(urn, params, callback, error) {
    params['format'] = 'html';
    getZbInsecureData(urn, "seatmap", params, callback, error);
}

let __paymentClause = { reserve_id: null, trace_number: null, total_price: null, time: null };
function goForPayment(args) {
    lockLoader(true);
    getZbData(__active_event.urn, "reserve", args, dat => {
        lockLoader(false);
        if (DEBUG) console.log(dat);
        if (!dat.ok) {
            switch (dat.error.code) {
                case 400:
                    showError("ورودی های شما نادرست‌اند، بازبینی کنید.", null, () => {});
                    break;

                case 404:
                    showError("چنین سانسی وجود ندارد!", null, () => swicthToPick());
                    break;

                case 502:
                    showError("صندلی هایی که انتخاب کرده‌اید رزرو شده اند.", null, () => swicthToSeat());
                    break;

                case 500:
                    showError("این سانس به اندازه کافی ظرفیت ندارد.", null, () => swicthToPick());
                    break;

                case 501:
                    showError("ظرفیت این سانس تکمیل است.", null, () => swicthToPick());
                    break;

                default:
                    showError("خطایی ناشناس رخ داد.", null, () => {});
                    break;
            }
            return;
        }
        __paymentClause.reserve_id = dat.data.reserve_id;
        __paymentClause.trace_number = dat.data.trace_number;
        __paymentClause.total_price = dat.data.total_price;
        __paymentClause.time = __RESERVETIME;
        setupAftemath();
    }, 
    () => { switchToFinal(); });
}

function cancelAftermath(callback) {
    lockLoader(true);
    getZbData(__active_event.urn, "cancel", __paymentClause, dat => {
        lockLoader(false);
        if (!dat.ok)
            showError("درخواست شما با مشکل بر خورد.", 
                function(e) { cancelAftermath(e); },
                () => { },
                callback);
        else {
            clearInterval(__aftermath_timer);
            __aftermath_timer = null;
            callback();
        }
    }, 
    () => { });
}

function getVoucherState(vouch, callback) {
    getZbInsecureData(__active_event.urn, "checkVoucher", { voucher: vouch }, dat => {
        if (!dat.ok) 
            callback("نادرست است", false);
        else if (dat.data.state === 'valid' || dat.data.state === 'conditional')
            callback("درست است", true);
    }, 
    () => callback("خطایی رخ داد", false));
}