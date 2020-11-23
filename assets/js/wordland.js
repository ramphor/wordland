function wordland_show_login_modal() {
    if (typeof MicroModal !== 'undefined' && document.getElementById('modal-loggin')) {
        MicroModal.show('modal-loggin');
    }
}
