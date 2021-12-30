.. include:: ../Includes.txt

.. _examples_legacy:

============
Basic requests
============

.. warning::

    | This example uses an JavaScript with a very "old" way of creating and sending XHR-requests.
    | You will only need this, if you are still forced to optimize for **Internet Explorer 11 and below**.
    For a more modern approach we would recommend using the promise-based :ref:`fetch() <examples_plain>` or
    a library that saves a lot of headaches like :ref:`Axios <examples_axios>`


How to make a request to your TYPO3 Restful Api with pure JavaScript (no libraries) that supports older browsers (like Internet Explorer 11 and below).
------------

.. tip::

    | **Want to play, not read?**
    | Here is a ready-to-go codepen that demonstrates how to use VanillaJS to connect to your REST Api. Run and have fun!
    | `nnrestapi vanillajs demo <https://codepen.io/99grad/pen/xxXryem>`__


Sending a simple request (for older browsers)
~~~~~~~~~~~~

If you can drop support for IE11 and below, the easiest way to send a request is using the promise-based ``fetch()``
command that comes with ES6+.

Let's create a ``GET`` request to the nnrestapi backend:

.. code-block:: javascript

    // By default this will be routed to Index->getIndexAction()
    var url = 'https://www.mywebsite.com/api/index';

    var xhr = new XMLHttpRequest();
    xhr.open('GET', url);

    xhr.onload = function () {
        var data = JSON.parse( xhr.responseText );
        if (xhr.status != 200) {
            alert("Error " + xhr.status + ": " + data.error );
            return false;
        }
        console.log( data );
    };

    xhr.onerror = function () {
        alert('Some other error... probably wrong url?');
    };

    xhr.send();


Of course you can also send a payload / JSON data to the TYPO3 Restful Api using a ``POST``, ``PUT`` or ``PATCH`` request:

.. code-block:: javascript

    // By default this will be routed to Index->postIndexAction()
    var url = 'https://www.mywebsite.com/api/index';

    var json = {
        title: 'My Title',
        text: 'And some text'
    };

    var xhr = new XMLHttpRequest();
    xhr.overrideMimeType('application/json');
    xhr.open('POST', url);

    xhr.onload = function () {
        var data = JSON.parse( xhr.responseText );
        if (xhr.status != 200) {
            alert("Error " + xhr.status + ": " + data.error );
            return false;
        }
        console.log( data );
    };

    xhr.onerror = function () {
        alert('Some other error... probably wrong url?');
    };

    xhr.send(JSON.stringify(json));


VanillaJS Starter Template
~~~~~~~~~~~~

Here is a full example you can copy and paste to get you started.

You can also test and play with it on `this codepen <https://codepen.io/99grad/pen/xxXryem>`__

.. code-block:: html

    <!doctype html>
    <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>nnrestapi VanillaJS Demo</title>

            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

            <style>
                #json-data {
                    min-height: 100px;
                }
                #result {
                    min-height: 100px;
                    white-space: pre-wrap;      
                    border: 1px dashed #aaa;
                    background: #eee;
                    padding: 0.75rem;
                }
            </style>
        </head>
        <body>
            <div class="container my-5">                
                <div class="form-floating mb-4">
                    <select class="form-select" id="request-method">
                    <option>GET</option>
                    <option>POST</option>
                    <option>PUT</option>
                    <option>PATCH</option>
                    </select>
                    <label for="request-method">Request method</label>
                </div>
                <div class="form-floating mb-4">
                    <input class="form-control" id="url" value="https://www.mysite.com/api/index/" />
                    <label for="url">URL to endpoint</label>
                </div>
                <div class="form-floating mb-4">
                    <input class="form-control" id="title" value="This is the title" />
                    <label for="json-data">Title</label>
                </div>
                <div class="form-floating mb-4">
                    <textarea class="form-control" id="text">This is the bodytext</textarea>
                    <label for="json-data">Text</label>
                </div>
                <div class="form-floating mb-4">
                    <button id="submit" class="btn btn-primary">Send to API</button>
                </div>
                <pre id="result">Result</pre>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
            <script>

                var $method = document.getElementById('request-method');
                var $url = document.getElementById('url');
                var $title = document.getElementById('title');
                var $text = document.getElementById('text');
                var $button = document.getElementById('submit');
                var $result = document.getElementById('result');

                /**
                 * Helper-function to send REST Api requests for older
                 * browsers not supporting fetch()
                 * 
                 */
                function sendRequest( url, payload, method, done, fail ) {

                    if (typeof payload == 'object') {
                        payload = JSON.stringify(payload);
                    }

                    var xhr = new XMLHttpRequest();
                    xhr.overrideMimeType('application/json');
                    xhr.open(method, url);

                    // The JWT we stored after authenticating
                    var token = localStorage.getItem('token');
                    if (token) {
                        xhr.setRequestHeader('Authorization', 'Bearer ' + token);
                    }

                    xhr.onload = function () {
                        var data = JSON.parse( xhr.responseText );
                        if (xhr.status != 200) {
                            if (fail) fail( data );
                            return false;
                        }
                        if (done) done( data );
                    };

                    xhr.onerror = function () {
                        fail({
                            status: 0,
                            error: 'Some other error... probably wrong url?'
                        });
                    };

                    if (['GET', 'DELETE'].indexOf(method) == -1) {
                        xhr.send( payload );
                    } else {				
                        xhr.send();
                    }
                }

                $button.addEventListener('click', submitData);

                function submitData() {

                    var url = $url.value;
                    var method = $method.value;

                    var json = {
                        title: $title.value,
                        text: $text.value
                    };

                    sendRequest( url, json, method, onResponse, onError );

                    function onResponse( data ) {
                         $result.innerText = JSON.stringify( data );
                    }
                    
                    function onError( error ) {
                        alert( `Error ${response.status}: ${data.error}` );   
                    }

                }
                
            </script>
        </body>
    </html>
