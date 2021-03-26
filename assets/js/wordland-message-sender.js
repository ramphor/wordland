var httpRequest;
// Old compatibility code, no longer needed.
if (window.XMLHttpRequest) { // Mozilla, Safari, IE7+ ...
    httpRequest = new XMLHttpRequest();
} else if (window.ActiveXObject) { // IE 6 and older
    httpRequest = new ActiveXObject("Microsoft.XMLHTTP");
}

var message_sender_form = document.getElementById('wordland-agent-message-sender');
if (message_sender_form) {
    function wordland_message_sender() {
        if (httpRequest.readyState === XMLHttpRequest.DONE) {
            // Everything is good, the response was received.
            if (httpRequest.status === 200) {
            }
        } else {
            // Not ready yet.
        }
    }

    message_sender_form.addEventListener('submit', function(e) {
        e.preventDefault();

        httpRequest.open('POST', e.target.action, true);
        httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        httpRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        httpRequest.onreadystatechange = wordland_message_sender;

        requestBody = new FormData(e.target);
        requestBody.append(e.submitter.name, e.submitter.value);

        queryString = new URLSearchParams(requestBody).toString()

        httpRequest.send(queryString);
    });
}
