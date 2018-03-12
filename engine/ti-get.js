const DEBUG = __config.js.debug;

// specify your framework type (based on zb-agent file extension)
const __FRAMEWORK = "php";

// Tiwall API Engine
var __lastTiResponse = null;

function getLastTi() {
    return __lastTiResponse;
}

const TI_BASE_URL = "https://store.zirbana.com/v2";

function getTiPages(path, callback, error, passable) {
    var addr = TI_BASE_URL + "/pages/" + path;
    $.ajax(addr, { 
        success: function(result) {
            __lastTiResponse = JSON.parse(result);
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
            addr += '&' + key + '=' + params[key];
    $.ajax(addr, { 
        success: d => callback(JSON.parse(d)),
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
            addr += key + '=' + params[key] + '&';
    $.ajax(addr, { 
        success: d => callback(JSON.parse(d)),
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

function goForPayment(args) {
    getZbInsecureData(__active_event.urn, "reserve", args, dat => {
        if (!dat.ok) {
            return;
        }
    }, () => switchToFinal());
}