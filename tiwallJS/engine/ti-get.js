const DEBUG = true;

// specify your framework type (based on zb-agent file extension)
var __FRAMEWORK = "php";

// Tiwall API Engine
var __lastTiResponse = null;

function getLastTi() {
    return __lastTiResponse;
}

var TI_BASE_URL = "https://store.zirbana.com/v2";

function getTiPages(path, callback, error, passable) {
    var addr = TI_BASE_URL + "/pages/" + path;
    $.ajax(addr, { 
        success: function(result) {
            __lastTiResponse = JSON.parse(result);
            callback(__lastTiResponse);
        },
        error: function() {
            showError("بارگذاری اطلاعات با مشکل بر خورد.", 
                function(e) { getTiPages(e.path, e.callback, e.error, e.passable) },
                error,
                { path: path, callback: callback, error: error, passable: passable });
        },
        headers: {
            'Accept': 'text/json'
        },
        type: 'GET',
        timeout: 8000
    });
}

function getTiCats(attrs, callback, passable) {
    var addr = "categories?";
    if (attrs)
        for (var key in attrs)
            addr += key + "=" + attrs[key] + '&';
    getTiPages(addr, callback, passable);
}

function getTiEventList(cat, attrs, callback, passable) {
    var addr = "list?" + (DEBUG ? "include_samples=1&" : "");
    if (cat)
        addr += "cat=" + cat + '&';
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
var ZB_BASE_URL = "zb-agent." + __FRAMEWORK;

function getZbData(urn, action, params, callback) {
    var addr = ZB_BASE_URL + "?urn=" + urn + "&action=" + action;
    if (params)
        for (var key in params)
            addr += '&' + key + '=' + params[key];
    $.ajax(addr, { success: callback });
}

function getShowtimes(urn, params, callback) {
    var addr = ZB_BASE_URL + "?urn=" + urn + "&action=instances";
    if (params)
        for (var key in params)
            addr += '&' + key + '=' + params[key];
    $.ajax(addr, { success: callback });
}

function getSeatmap(urn, params, callback) {
    var addr = ZB_BASE_URL + "?urn=" + urn + "&action=seatmap&format=html";
    if (params)
        for (var key in params)
            addr += '&' + key + '=' + params[key];
    $.ajax(addr, { success: callback });
}