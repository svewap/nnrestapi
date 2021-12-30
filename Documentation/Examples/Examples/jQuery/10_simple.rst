.. include:: ../Includes.txt

.. _examples_jquery_basic:

============
Basic requests
============

Simple requests without authentication using jQuery 
------------

.. tip::

    | **Want to play, not read?**
    | Here is a ready-to-go codepen that demonstrates how to use jQuery to connect to your REST Api. Run and have fun!
    | `nnrestapi jQuery demo <https://codepen.io/99grad/full/LYzbzaW>`__

.. important::

    | **Don't forget the @Api\Access!**
    | All exemples on this page will only work, if the methods have ``@Api\Access("public")`` set in the annotation.
    | If you need to find out, how to do that please refer to :ref:`this section <access>` or find out, how to create
    request with authentication in :ref:`this section <examples_jquery_auth>`


Sending a GET-Request
~~~~~~~~~~~~

A very basic example of how to send a ``GET`` request to your TYPO3 Restful APi using 
jQuery's `$.get() <https://api.jquery.com/jquery.get/>`__ command.

.. code-block:: javascript

    // By default this will be routed to Index->getIndexAction()
    const url = 'https://www.mywebsite.com/api/index';

    $.get(url).done((result) => {
        alert( result.message );
    }).fail((error) => {
        alert( `Error ${error.status}: ${error.responseJSON.error}` );
    });

Sending a POST-Request
~~~~~~~~~~~~

Here is a example of how to send a ``POST`` request to your Rest APi using 
jQuery's `$.post() <https://api.jquery.com/jquery.post/>`__ command.

Note that the object-data is converted to a JSON-string using `JSON.stringify() <https://developer.mozilla.org/de/docs/Web/JavaScript/Reference/Global_Objects/JSON/stringify>`__.
This makes sure the data can be parsed by the backend.

.. code-block:: javascript

    // By default this will be routed to Index->postIndexAction()
    const url = 'https://www.mywebsite.com/api/index';

    const data = {title:'Test'};
    const jsonData = JSON.stringify(data);
        
    $.post(url, jsonData).done((result) => {
        console.log( result );
    }).fail((error) => {
        alert( `Error ${error.status}: ${error.responseJSON.error}` );
    }); 


Sending a PUT, PATCH or DELETE request
~~~~~~~~~~~~

If you would like to send a ``PUT``, ``PATCH`` or ``DELETE`` request to your Rest APi, you will need to use 
jQuery's `$.ajax() <https://api.jquery.com/jquery.ajax/>`__ method with the appropriate ``type`` in the 
request settings.

.. code-block:: javascript

    // By default this will be routed to Index->putIndexAction()
    const url = 'https://www.mywebsite.com/api/index';

    const data = {title:'Test'};
    const jsonData = JSON.stringify(data);

    $.ajax({
        url: url,
        type: 'PUT', // 'PATCH' or 'DELETE'
        data: jsonData
    }).done((result) => {
        console.log( result );
    }).fail((error) => {
        alert( `Error ${error.status}: ${error.responseJSON.error}` );
    });     


jQuery Starter Template
~~~~~~~~~~~~

Here is a full example you can copy and paste to get you started.

You can also test and play with it on `this codepen <https://codepen.io/99grad/full/LYzbzaW>`__

.. code-block:: html

    <!doctype html>
    <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>nnrestapi jQuery Demo</title>

            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
            <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

            <script>
                $(function () {
                    
                    $('button').click(() => {
                        
                        $('#result').show().text('Loading...');
                        
                        $.ajax({
                            url: $('#url').val(),
                            type: $('#request-method').val(),
                            data: $('#json-data').val()
                        }).done((result) => {
                            $('#result').text( JSON.stringify(result) );
                        }).fail((error) => {
                            $('#result').text( 'ERROR: ' + JSON.stringify(error) );
                        });         
                    
                    });
            
                }); 
            </script>
            <style>
                #json-data {
                    min-height: 100px;
                }
                #result {
                    min-height: 100px;
                    display: none;
                    white-space: pre-wrap;      
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
                        <option>DELETE</option>
                    </select>
                    <label for="request-method">Request method</label>
                </div>
                <div class="form-floating mb-4">
                    <input class="form-control" id="url" value="https://www.mysite.com/api/index" />
                    <label for="url">URL to endpoint</label>
                </div>
                <div class="form-floating mb-4">
                    <textarea class="form-control" id="json-data">{"title":"Test"}</textarea>
                    <label for="json-data">JSON data</label>
                </div>
                <div class="form-floating mb-4">
                    <button class="btn btn-primary">Send to API</button>
                </div>
                <pre id="result"></pre>
            </div>      
        </body>
    </html>
