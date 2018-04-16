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