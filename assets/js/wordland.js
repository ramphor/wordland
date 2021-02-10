function wordland_show_login_modal() {
    if (typeof MicroModal !== 'undefined' && document.getElementById('modal-loggin')) {
        MicroModal.show('modal-loggin');
    }
}

function wordland_parse_price(price, decimals = 2) {
    const str_price = price.toString();
    return str_price.replace(/\.\d{1,}/, function (sub_str) {
        sub_str = sub_str.replace('.', '');
        if (sub_str.length <= decimals) {
            return '.' + sub_str;
        }
        decimal_number = sub_str.slice(0, decimals);
        round_number = sub_str.slice(decimals, decimals+1);
        if (parseInt(decimal_number) === 0) {
            return '';
        }
        if (parseInt(round_number) < 5) {
            return '.' + decimal_number;
        }
        return '.' + (parseInt(decimal_number) + 1);
    });
}
