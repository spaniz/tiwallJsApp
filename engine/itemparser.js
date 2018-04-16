var __ticache_html = [];
function getProxyHtml(target, options, callback, state) {
    if (__ticache_html[target] === undefined)
    {
        $.ajax("struct/" + target + ".html", { 
            success: function(htmlx) {
                __ticache_html[target] = htmlx;
                var newtml = htmlx;
                for (var key in options)
                {
                    newtml = newtml.replace("$" + target + key + "$", options[key]);
                }
                callback(newtml, state);
            }
        });
    }
    else {
        var newtml = __ticache_html[target];
        for (var key in options)
        {
            newtml = newtml.replace("$" + target + key + "$", options[key]);
        } 
        callback(newtml, state);
    }
}

function getEventItemHtml(itemOptions, callback, state) {
    getProxyHtml('item', itemOptions, callback, state);
}

function getEventPickHtml(pickOptions, callback, state) {
    getProxyHtml('pick', pickOptions, callback, state);
}

function getCategoryHtml(catOptions, callback, state) {
    getProxyHtml('cat', catOptions, callback, state);
}

var __ticonfig = null;
function getTiConf(callback) {
    if (__ticonfig)
        callback(__ticonfig);
    else
    {
        var xhr = new XMLHttpRequest();
        xhr.addEventListener("load", function() {
            __ticonfig = JSON.parse(this.responseText);
            callback(__ticonfig);
        });
        xhr.open("GET", "config.json");
        xhr.send();
    }
}

/*function isUrnAllowed(urn) {
    return ($.inArray(urn, __ticonfig.allowed_urns) > -1);
}*/

function processMiniCast(datx, micro) {
    if (datx.type === "film" || datx.type === "performance")
    {
        var _nxaut1 = datx.spec.director;
        _nxaut1 = (!_nxaut1) ? null : _nxaut1.text;
        var _nxaut2 = datx.spec.writer;
        _nxaut2 = (!_nxaut2) ? null : _nxaut2.text;
        var _nxaut = "";
        if (!(_nxaut1 || _nxaut2))
            return null;
        else if (_nxaut1 === _nxaut2)
            return (!micro ? "ن و ک: " : "") + _nxaut1;
        else
            return (!_nxaut1 ? "" : ((!micro ? "ک: " : "") + _nxaut1)) + ((_nxaut1 && _nxaut2) ? " / " : "") + (!_nxaut2 ? "" : ((!micro ? "ن: " : "") + _nxaut2));
    }
    else if (datx.spec.cast && !micro)
        return datx.spec.cast.text;
    else 
        return null;
}

