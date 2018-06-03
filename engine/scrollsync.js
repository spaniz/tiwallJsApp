let __sync_enabled = true;
let allowSync = null;
let parentWin = null;
let childWin = null;

function initInnerSync() {
    allowSync = true;
    childWin = window;
    parentWin = parent;
}

function configSync(toggle) {
    __sync_enabled = toggle;
}

function initOuterSync() {
    if (__sync_enabled) {
        //allowSync = true;
        childWin = $('#anozb-plugfrm').get(0).contentWindow;
        parentWin = window;
        $(parentWin.document).on('scroll', () => { 
            //if (allowSync)
            childWin.syncOuterScroll(
                $('#anozb-plugfrm+div', parentWin.document).visible(true, false, 'vertical', parentWin.frameElement)
            );
        });
        $(childWin).on('resize', () => { 
            //if (allowSync)
            if (childWin.DEBUG)
                console.error('Resizing triggered...');
            childWin.syncOuterScroll(
                $('#anozb-plugfrm+div', parentWin.document).visible(true, false, 'vertical', parentWin.frameElement)
            );
        });
    }
}

function syncOuterScroll(visibility) {
    if (__sync_enabled) {
        if (allowSync && visibility )
            $('#ti-listHolder', childWin.document).trigger('sync:scroll', visibility);
    }
}

function syncViewSize(enable) {
    if (__sync_enabled) {
        let hx = $('#ti-listHolder').outerHeight();
        let hs = $('#ti-listHolder').get(0).scrollHeight;
        if (DEBUG) {
            console.log("frame height: " + hs + "/" + hx);
            console.warn(parent.document.firstElementChild);
        }
        if (hs > hx)
            parent.document.firstElementChild.style.setProperty('--ti-plugin-height', hs + 'px');
        //else 
        //    parent.document.firstElementChild.style.removeProperty('--ti-plugin-height');

        if (enable)
            allowSync = true;
    }
}

function desyncViewSize() {
    if (__sync_enabled) {
        parent.document.firstElementChild.style.removeProperty('--ti-plugin-height');
        allowSync = false;
    }
}