function wordland_show_login_modal() {
    if (typeof MicroModal !== 'undefined' && document.getElementById('modal-login')) {
        MicroModal.show('modal-login');
    }
}

function wordland_round_number(price, decimals = 2) {
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

window.ajax = function(url, body, successCallback, options = {}) {
    httpRequest;
    // Old compatibility code, no longer needed.
    if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
        httpRequest = new XMLHttpRequest();
    } else if (window.ActiveXObject) { // IE 6 and older
        httpRequest = new ActiveXObject("Microsoft.XMLHTTP");
    }

    var formData = null;
    if (body instanceof FormData) {
        formData = body;
    } else {
        formData = new FormData();
        dataKeys = Object.keys(body);
        for (i = 0; i < dataKeys.length; i++) {
            dataKey = dataKeys[i];
            formData.append(dataKey, body[dataKey]);
        }
    }

    options = Object.assign({
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        beforeSend: function() {},
        complete: function() {},
    }, options);

    queryString = new URLSearchParams(formData).toString()

    request_method = options.method.toUpperCase();

    httpRequest.open(
        request_method,
        url + (request_method === 'GET' && queryString ? '?' + queryString : ''),
        true
    );
    headers = Object.keys(options.headers);
    if ( headers.length > 0) {
        for(i = 0; i < headers.length; i++) {
            header = headers[i];
            httpRequest.setRequestHeader(header, options.headers[header]);
        }
    }

    httpRequest.onreadystatechange = function(xhr) {
        options.beforeSend();

        successCallback(xhr.currentTarget);

        options.complete();
    };
    if (request_method === 'GET') {
        httpRequest.send();
    } else {
        httpRequest.send(queryString);
    }
}

/**
 *
 * @param {string} selector
 * @returns { HTMLElement }
 */
HTMLElement.prototype.parents = function(selector) {
    const parent = this.matches(selector);
    if (parent) {
        return this;
    }

    if (this.parentElement) {
        return this.parentElement.parents(selector);
    }
}

/**
 *
 * @param {HTMLElement} el
 * @param {string} clsName
 * @returns
 */
function wordland_toogle_class(el, clsName, showed = null) {
    if (el.classList) {
        if (showed === null) {
            el.classList.toggle(clsName);
        }
        showed ? el.classList.add(clsName) : el.classList.remove(clsName);
    } else {
        // For IE9
        var classes = el.className.split(" ");
        var i = classes.indexOf(clsName);

        if (showed === null) {
            return toogleWordLandIEClass(el, clsName, i, classes);
        }

        if (showed) {
            if (i < 0) {
                classes.push(clsName);
            }
        } else {
            if (i >= 0) {
                classes.splice(i, 1);
            }
        }
        el.className = classes.join(" ");
    }
}

function wordland_load_more(listing_wrap, append = true, page = undefined) {
    if (listing_wrap) {
        const active_tab = listing_wrap.querySelector('.wordland-tabs .tab.active');
        if (!active_tab) {
            return alert(wordland.languages.listing_by_cat_error);
        }

        const posts_per_page = listing_wrap.dataset.posts_per_page;
        const current_page = (page === undefined) ? listing_wrap.dataset.current_page : 0;
        const item_style = listing_wrap.dataset.item_style;
        const tab_type = active_tab.dataset.tab_type;
        const tab_id = active_tab.dataset.tab_id;
        const tab_data = active_tab.dataset.tab_data;

        if (!posts_per_page || !tab_type || !tab_id) {
            return alert(wordland.languages.listing_by_cat_error);
        }

        ajax(wordland.ajax_url, {
            action: 'wordland_load_more_listing',
            posts_per_page: posts_per_page,
            item_style: item_style,
            current_page: current_page,
            tab_type: tab_type,
            tab_id: tab_id,
            tab_data_type: tab_data,
        }, function(xhr) {
            if (xhr.readyState === 4) {
                xhr.responseJSON = JSON.parse(xhr.responseText);

                if (!xhr.responseJSON.success) {
                    alert(xhr.responseJSON.data.error_message);
                }

                wordland_list = listing_wrap.querySelector('.tab-content .wordland-list');
                if (append) {
                    wordland_list.innerHTML += xhr.responseJSON.data.list_items_html;
                } else {
                    wordland_list.innerHTML = xhr.responseJSON.data.list_items_html;
                }
                listing_wrap.dataset.current_page = xhr.responseJSON.data.current_page || 1;

                if (window.ramphor_wordpress_post_collection_trigger) {
                    ramphor_wordpress_post_collection_trigger(
                        listing_wrap.querySelectorAll('[data-collection-action]')
                    );
                }
            }
        });
    }
}

/**
 *
 * @param { MouseEvent } e
 */
function wordland_loadmore_btn_clicked_event(e) {
    const listing_wrap = e.target.parents('.wordland-property-listing');

    wordland_load_more(listing_wrap);
}

function wordland_category_tab_clicked_event(e) {
    e.preventDefault();

    current_tab = e.target.parents('.tab');
    if (!current_tab.classList) {
        Array.prototype.contains = function(index) {
            return this.includes(index);
        }
        current_tab.classList = current_tab.className.split(' ');
    }
    if (current_tab.classList.contains('active')) {
        return;
    }

    allTabs = current_tab.parentElement.querySelectorAll('.tab.active');
    for(i = 0; i < allTabs.length; i++) {
        wordland_toogle_class(allTabs[i], 'active', false);
    }
    wordland_toogle_class(current_tab, 'active', true);

    wordland_load_more(current_tab.parents('.wordland-property-listing'), false, -1);
}

// Document is ready
window.addEventListener('DOMContentLoaded', function(){
    var loadMoreBtns = document.querySelectorAll('.wordland-button.load-more');
    for (i = 0; i < loadMoreBtns.length; i++) {
        loadMoreBtns[i].addEventListener('click', wordland_loadmore_btn_clicked_event);
    }
    var loadMoreTabs = document.querySelectorAll('.wordland-tabs .tab a');
    for (i = 0; i < loadMoreTabs.length; i++) {
        loadMoreTabs[i].addEventListener('click', wordland_category_tab_clicked_event);
    }
});
