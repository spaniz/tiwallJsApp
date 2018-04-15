function initSizingSync() {
    $(parent.document).on('scroll', () => $('#ti-listHolder').trigger('scroll'));
}

function syncViewSize() {
    let hx = $('#ti-listHolder').outerHeight();
    let hs = $('#ti-listHolder').get(0).scrollHeight;
    if (DEBUG) {
        console.log("frame height: " + hs + "/" + hx);
        console.warn(parent.document.firstElementChild);
    }
    if (hs > hx)
        parent.document.firstElementChild.style.setProperty('--ti-plugin-height', hs + 'px');
    else 
        parent.document.firstElementChild.style.removeProperty('--ti-plugin-height');
}

function desyncViewSize() {
    parent.document.firstElementChild.style.removeProperty('--ti-plugin-height');
}