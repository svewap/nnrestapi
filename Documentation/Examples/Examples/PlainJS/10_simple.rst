.. include:: ../Includes.txt

.. _examples_plain:

============
Basic requests
============

How to make a request with pure JavaScript
------------

.. tip::

    | **Want to play, not read?**
    | Here is a ready-to-go codepen that demonstrates how to use VanillaJS for requests. Run and have fun!
    | `nnrestapi vanillajs demo <https://codepen.io/99grad/pen/xxXryem>`__


Sending a simple request (for modern browsers)
~~~~~~~~~~~~

If you can drop support for IE11 and below, the easiest way to send a request is using the promise-based ``fetch()``
command that comes with ES6+.

Let's create a ``GET`` request to the nnrestapi backend:

.. code-block:: javascript

    // By default this will be routed to Index->getIndexAction()
    const url = 'https://www.mywebsite.com/api/index';

    fetch( url )
        .then( async response => {

            // convert the result to a JavaScript-object
            let data = await response.json()

            if ( !response.ok ) {
                // reponse was not 200
                alert( `Error ${response.status}: ${data.error}` );   
            } else {
                // everything ok!
                console.log( data );
            }
        });

Without further configuration, ``fetch()`` will send a ``GET``-request. Of course you can also send a payload / JSON data 
to the backend using a ``POST``, ``PUT`` or ``PATCH`` request:

.. code-block:: javascript

    // By default this will be routed to Index->postIndexAction()
    const url = 'https://www.mywebsite.com/api/index';

    const json = {
        title: 'Title',
        text: 'Text to send',
    };

    const xhrConfig = {
        method: 'POST', // or: 'PUT', 'PATCH', 'DELETE' ...
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(json)
    };

    fetch( url, xhrConfig )
        .then( async response => {
            let data = await response.json()
            if ( !response.ok ) {
                alert( `Error ${response.status}: ${data.error}` );   
            } else {
                console.log( data );
            }
        });

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

                const $method = document.getElementById('request-method');
                const $url = document.getElementById('url');
                const $title = document.getElementById('title');
                const $text = document.getElementById('text');
                const $button = document.getElementById('submit');
                const $result = document.getElementById('result');

                $button.addEventListener('click', submitData);

                function submitData() {

                    const url = $url.value;
                    const method = $method.value;

                    const json = {
                        title: $title.value,
                        text: $text.value
                    };

                    const xhrConfig = {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                        }
                    };

                    if (['GET', 'DELETE'].indexOf(method) == -1) {
                         xhrConfig.body = JSON.stringify(json);
                    }
                    
                    fetch( url, xhrConfig )
                        .then( async response => {
                            let data = await response.json()
                            if ( !response.ok ) {
                                alert( `Error ${response.status}: ${data.error}` );   
                            } else {
                                $result.innerText = JSON.stringify( data );
                            }
                        });
                }
                
            </script>
        </body>
    </html>
