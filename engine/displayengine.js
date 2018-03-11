var __current_data = null;
var __last_count = 0;
var __load_lock = false;
var __open_pageid = null;
var __active_event = null;
var __current_cat = null;
var __eol = false;

function displayEventItem(htmlx, coords) {
    var list = $('#ti-listHolder');
    list.append(htmlx);
    if (DEBUG)
        console.log("rendering item " + coords.i + "/" + coords.max);
    $('#ti-listHolder .ti-witem:last-child').click(function (event) {
        var i = $(this).attr('itemid');
        __active_event = __current_data.data[i];
        if (DEBUG) console.log(__active_event);
        switchToEvent();
        $('#ti-cardWrapper #ti-pickHolder ~ tr').removeClass('fulfilled');
        $('#ti-cardWrapper .ti-prefix').text(__current_data.data[i].title_prefix);
        $('#ti-cardWrapper .ti-title').text(__current_data.data[i].title);
        $('#ti-bannerHolder img').attr('src', "");
        if (__current_data.data[i].image)
            $('#ti-bannerHolder img').attr('src', __current_data.data[i].image.normal_url || "");

        _nxaut = processMiniCast(__current_data.data[i]);
        if (_nxaut) {
            $('#ti-eventHolder .ti-xcontainer p:nth-child(1)').removeClass('ti-hidden');
            $('#ti-eventHolder .ti-xcontainer .ti-nxaut').text(_nxaut);
        }
        else
            $('#ti-eventHolder .ti-xcontainer p:nth-child(1)').addClass('ti-hidden');

        var _nxloc = __current_data.data[i].spec.venue;
        _nxloc = (!_nxloc) ? null : _nxloc.title;
        if (_nxloc) {
            $('#ti-eventHolder .ti-xcontainer p:nth-child(2)').removeClass('ti-hidden');
            $('#ti-eventHolder .ti-xcontainer .ti-nxloc').text(toLocalisedNumbers(_nxloc));
        }
        else
            $('#ti-eventHolder .ti-xcontainer p:nth-child(2)').addClass('ti-hidden');

        var _nxtime = __current_data.data[i].spec.time;
        _nxtime = (!_nxtime) ? "" : _nxtime.text;
        var _nxdat = __current_data.data[i].spec.date_duration_text || "";
        if (_nxdat || _nxtime) {
            $('#ti-eventHolder .ti-xcontainer p:nth-child(3)').removeClass('ti-hidden');
            $('#ti-eventHolder .ti-xcontainer .ti-nxdat').text(toLocalisedNumbers(_nxdat + ' ' + _nxtime));
        }
        else
            $('#ti-eventHolder .ti-xcontainer p:nth-child(3)').addClass('ti-hidden');

        var _nxprc = __current_data.data[i].price;
        _nxprc = (!_nxprc) ? null : _nxprc.text;
        if (_nxprc) {
            $('#ti-eventHolder .ti-xcontainer p:nth-child(4)').removeClass('ti-hidden');
            $('#ti-eventHolder .ti-xcontainer .ti-nxprc').text(toLocalisedNumbers(_nxprc));
        }
        else
            $('#ti-eventHolder .ti-xcontainer p:nth-child(4)').addClass('ti-hidden');
        $('#ti-eventHolder .ti-seperator').text(__current_data.data[i].short_desc || "");
    });
    if (coords.i === (coords.max || 0) - 1)
        setTimeout(finaliseListLoad, 500);
}

function addPick(datZ) {

    lockLoader(true);
    var ops = { 'id': datZ.id, 'name': datZ.title || "خرید", 'info': datZ.remained_text };
    getEventPickHtml(ops, function (xhtml) {
        $('#ti-pickHolder .ti-xcontainer').append(xhtml);
        if (!datZ.remained) $('#ti-pickHolder .ti-witem:last-child').addClass('disabled');
        $('#ti-pickHolder .ti-witem:last-child').click(function (event) {
            $('#ti-pickHolder .ti-witem').removeClass('selected');
            $(this).addClass('selected');
            switchToSeat();
            $('#ti-seatHolder').addClass('fulfilled');
            if (datZ.title)
                $('#ti-seatHolder .ti-prefix').text(datZ.title);
            $('#ti-seatHolder .ti-xframe').empty();
            $('#ti-seatHolder .ti-xcontainer').empty();
            lockLoader(true);
            getSeatmap(__active_event.urn, { 'showtime_id': $(this).attr('itemid') },
                function (_jsdat) {
                    //console.warn(jsdat);
                    var jsdat = JSON.parse(_jsdat);
                    //if (DEBUG)
                    //    jsdat.data.html = jsdat.data.html.replace('https://store.zirbana.com/resource/js/hallRenderer-v2.js', '/engine/hallRenderer-v2.js');
                    $('#ti-seatHolder .ti-xframe').html(jsdat.data.html);
                    $('#ti-seatHolder .ti-seperator').empty();
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
        lockLoader(false);
    });
}

function onSeatSelectionChange(data) {
    if (DEBUG) console.log(data);
    //_dat = JSON.parse(data);
    $('#ti-seatHolder .ti-xcontainer').text = data.summary;
}

function addItem(i, offset, datX, max) {
    lockLoader(true);
    var _spec = datX.spec.director;
    if (_spec && _spec != undefined)
        _spec = _spec.text;
    else
        _spec = "";

    getEventItemHtml({
        'name': toLocalisedNumbers(datX.title),
        'info': _spec,
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
    if (DEBUG)
        console.log("call ended, finaliseListLoad on " + __current_cat + " with #" + __last_count);
}

function loadMore(force) {
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
    let ctx = null;
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
            if (ctx && ctx.find(x => x == datJ.data[i].key) != undefined)
            {
                addCat(datJ.data[i]);
                ctn++;
                ctc = datJ.data[i];
            }
        }
        if (ctn == 1)
        {
            __current_cat = ctc.key;
            var ti = $(this);
            $('#ti-catSel').css('top', '-100%');
            $('#ti-catSel + div').removeClass('ti-hidden');
            $('#ti-catSel + div').css('top', '-100%').css('background', ctc.color);
            $('#ti-listHeader').css('top', '0px').css('background', ctc.color).children('span').text(ctc.text);
            loadMore(true);
        }
        lockLoader(false);
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
    //$('#ti-cardWrapper').addClass('ti-hidden');
    $('#ti-cardWrapper').get(0).style.setProperty('--shift', '-1');
    $('#ti-cardWrapper').get(0).style.setProperty('--stage', '-2');
}
function switchToEvent() {
    $('#ti-listHolder').addClass('ti-unfocus');
    //$('#ti-cardWrapper').removeClass('ti-hidden');
    $('#ti-cardWrapper').get(0).style.setProperty('--shift', '0');
    $('#ti-cardWrapper').get(0).style.setProperty('--stage', '0');
}
function switchToPick() {
    $('#ti-listHolder').addClass('ti-unfocus');
    //$('#ti-cardWrapper').removeClass('ti-hidden');
    $('#ti-cardWrapper').get(0).style.setProperty('--shift', '1');
    $('#ti-cardWrapper').get(0).style.setProperty('--stage', '2');
}
function switchToSeat() {
    $('#ti-listHolder').addClass('ti-unfocus');
    //$('#ti-cardWrapper').removeClass('ti-hidden');
    $('#ti-cardWrapper').get(0).style.setProperty('--shift', '2');
    $('#ti-cardWrapper').get(0).style.setProperty('--stage', '2');
}

var __err_pass = null;
function showError(message, retry_callback, return_callback, pass) {
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