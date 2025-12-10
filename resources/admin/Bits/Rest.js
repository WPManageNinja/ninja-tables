const request = function (method, route, data = {}) {
    const url = `${window.ninja_table_admin.rest.url}/${route}`;

    const headers = {'X-WP-Nonce': window.ninja_table_admin.rest.nonce};

    if (['PUT', 'PATCH', 'DELETE'].indexOf(method.toUpperCase()) !== -1) {
        headers['X-HTTP-Method-Override'] = method;
        method = 'POST';
    }

    let ajaxContent = {
        url: url,
        type: method,
        data: data,
        headers: headers
    }

    if (data && data instanceof FormData) {
        ajaxContent.contentType = false;
        ajaxContent.processData = false;
        ajaxContent.cache = false;
    }
    ajaxContent.data.query_timestamp = Date.now();


    return new Promise((resolve, reject) => {
        window.jQuery.ajax(ajaxContent)
            .then(response => resolve(response))
            .fail(errors => reject(errors.responseJSON));
    });
}

export default {
    get(route, data = {}) {
        return request('GET', route, data);
    },
    post(route, data = {}) {
        return request('POST', route, data);
    },
    delete(route, data = {}) {
        return request('DELETE', route, data);
    },
    put(route, data = {}) {
        return request('PUT', route, data);
    },
    patch(route, data = {}) {
        return request('PATCH', route, data);
    }
};

jQuery(document).ajaxSuccess((event, xhr, settings) => {
    const nonce = xhr.getResponseHeader('X-WP-Nonce');
    if (nonce) {
        window.ninja_table_admin.rest_nonce = nonce;
    }
});
