
var ajax_url = ajax_object.ajax_url;
var callback = ajax_object.callback;
var message = ajax_object.message;
var type = ajax_object.type;

class clientController
{
     question(question,  callback)
    {
        function fetch_request(method, res, callback)
        {
            $url = ajax_url;
                fetch(url, {
                    method: method,
                    body: JSON.stringify({
                        response: res,
                        action: callback
                    }),
                    headers: {
                        'Content-Type': 'application/json'
                    }
                }).then(response => response.json())
                .then(data => {
                    console.log(data);
                }).catch(error => {
                    console.error(error);
                });
        }
        if (confirm(question)) {
            fetch_request("POST", "yes", callback);
        } else {
            fetch_request("POST", "no", callback);
        }
    }
}

let client = new clientController();
client[type](message, callback);


