.. include:: ../Includes.txt

.. _examples_axios:

============
Basic requests
============

How to make a request with axios
------------

**axios** is a JavaScript library to create promise based HTTP requests. Many frameworks like `VueJs <https://www.npmjs.com/package/vue-axios>`__,
`Angular <https://www.bennadel.com/blog/3444-proof-of-concept-using-axios-as-your-http-client-in-angular-6-0-0.htm>`__ or 
`React <https://www.arubacloud.com/tutorial/how-to-make-http-requests-with-axios-and-reactjs.aspx>`__ work great together with axios. 

But even with plain JS ("VanillaJS") axios can really make life easier.

You can find out more about axios `here <https://github.com/axios/axios>`__

.. important::

    Make sure to include the **axios** library to use the scripts in this section. 
    Simply add this line to the ``<head>`` of your HTML-document:

    .. code-block:: html

        <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>


.. tip::

    | **Want to play, not read?**
    | Here is a ready-to-go codepen that demonstrates how to use axios to connect to your endpoint. Run and have fun!
    | `nnrestapi AXIOS demo <https://codepen.io/99grad/full/LYzbzaW>`__


Sending a simple request
~~~~~~~~~~~~

AXIOS offers a great Promise based syntax which makes requests very easy.

Let's create a ``GET`` request to the nnrestapi backend:

.. code-block:: javascript

    // By default this will be routed to Index->getIndexAction()
    const url = 'https://www.mywebsite.com/api/index';

    axios.get( url ).then(({data}) => {
       console.log( data );
    }).catch(({response}) => {
        alert( `Error ${response.data.status}: ${response.data.error}` );   
    });

Sending a payload / JSON data with the ``POST``, ``PUT`` or ``PATCH`` request
is also very easy: Axios takes a javascript object as second argument and will automatically serialize
it to a string. No need to ``JSON.stringify`` before sending the request:

.. code-block:: javascript

    // By default this will be routed to Index->postIndexAction()
    const url = 'https://www.mywebsite.com/api/index';

    const json = {
        title: 'Test',
    };

    axios.post( url, json ).then(({data}) => {
       console.log( data );
    }).catch(({response}) => {
        alert( `Error ${response.data.status}: ${response.data.error}` );   
    });

AXIOS also has methods ready for all other request-types:

.. code-block:: javascript

    axios.post( url, )
    axios.put(url[, data[, config]])
    axios.patch(url[, data[, config]])
    axios.delete(url[, config])

If you are looking for more options – or would like to switch between request methods
dynamically, then ``axios()`` offers a very generic method to create your request:

.. code-block:: javascript

    axios({
        method: 'post',
        url: 'https://...',
        data: {...}
    }).then(( result ) => {
        ...
    }).catch(( error ) => {
        ...   
    });


AXIOS Starter Template
~~~~~~~~~~~~

Here is a full example you can copy and paste to get you started.

You can also test and play with it on `this codepen <https://codepen.io/99grad/full/LYzbzaW>`__

.. code-block:: html

    <!doctype html>
    <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>nnrestapi axios Demo</title>

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
                    <input class="form-control" id="url" value="https://www.mysite.com/api/your/endpoint/" />
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
            <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
            <script>

                const $method = document.getElementById('request-method');
                const $url = document.getElementById('url');
                const $title = document.getElementById('title');
                const $text = document.getElementById('text');
                const $button = document.getElementById('submit');
                const $result = document.getElementById('result');

                $button.addEventListener('click', submitData);

                function submitData() {
                    const data = {
                        title: $title.value,
                        text: $text.value
                    };
                    axios({
                        method: $method.value.toLowerCase(),
                        url: $url.value,
                        data: data
                    }).then(({data}) => {
                        $result.innerText = JSON.stringify( data );
                    }).catch(( error ) => {
                        $result.innerText = JSON.stringify( error );    
                    });
                }
                
            </script>
        </body>
    </html>
