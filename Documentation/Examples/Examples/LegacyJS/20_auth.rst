.. include:: ../Includes.txt

.. _examples_legacy_auth:

============
Authentication
============

.. warning::

    | This example uses an JavaScript with a very "old" way of creating and sending XHR-requests.
    | You will only need this, if you are still forced to optimize for **Internet Explorer 11 and below**.
    For a more modern approach we would recommend using the promise-based :ref:`fetch() <examples_plain_auth>` or
    a library that saves a lot of headaches like :ref:`Axios <examples_axios_auth>`

How to login as a Frontend-User using pure JavaScript (no libraries) for IE11 and post data to an endpoint
------------

In most cases you will want to restrict access to certain users or usergroups.
The basic way to do this in your classes and methods, is to use the :ref:`@Api\Access() Annotation <access>`.

The nnrestapi-extension comes with a default endpoint to authenticate as a Frontend User using the
credentials set in the standard ``fe_user``-record.

To keep the frontend user logged in, TYPO3 usually sets a **cookie**. Cookies tend to get rather ugly when you
are sending cross-domain requests, e.g. from your Single Page Application (SPA) or from a localhost
environment.

The nnrestapi solves this by also allowing authentication via **JWT (Json Web Token)**. 

Let's have a look, how to authenticate, retrieve a JWT with pure JavaScript ("VanillaJS") and pass it to the 
server when making follow-up request.

.. tip::

    | **Play with this chapter on codepen**
    | Want to play, not read? Then head on to `Codepen <https://codepen.io/99grad/pen/OJxgdye>`__ and learn by playing with the example.


Authentication with pure JavaScript (IE11, no libraries)
~~~~~~~~~~~~

Use a simple ``POST``-request to the endpoint ``/api/auth`` and pass your credentials wrapped in a JSON to
authenticate as a TYPO3 Frontend-User. If you were successfully logged in, you will get an array with 
information about the frontend-user and the JSON Web Token (JWT).

In the following script we are simply "memorizing" the JWT by storing it in the 
`localStorage <https://www.w3schools.com/jsref/prop_win_localstorage.asp>`__ for later requests.

.. code-block:: javascript

    // This endpoint is part of the nnrestapi
    var authUrl = 'https://www.mywebsite.com/api/auth';

    var credentials = {
        username: 'john',
        password: 'xxxx'
    };

    var xhr = new XMLHttpRequest();
    xhr.overrideMimeType('application/json');
    xhr.open('POST', authUrl);

    xhr.onload = function () {
        var data = JSON.parse( xhr.responseText );
        if (xhr.status != 200) {
            alert( 'Error ' + xhr.status + ': ' + data.error );   
            return false;
        }
        console.log( data );
        localStorage.setItem('token', data.token);
    };

    xhr.onerror = function () {
        alert( 'Some other error... probably wrong url?' );
    };
        
    xhr.send( JSON.stringify(credentials) );


If you were ``john`` and we guessed your password right, the response of the above example will look something like this:

.. code-block:: javascript

    {
        uid: 9,
        username: "john",
        usergroup: [3, 5],
        first_name: "John",
        last_name: "Malone",
        lastlogin: 1639749538,
        token: "some_damn_long_token"
    }

The most important part of the response is the ``token``. You will need to store the value of the token in a variable
or localStorage like we did in the example above.

Sending authenticated requests
~~~~~~~~~~~~

After you retrieved your JSON Web Token (JWT) you can compose requests with the ``Authentication Bearer`` header.

Let's send a request to an endpoint that has an restricted access and only allows requests from ``fe_users``. 
This can be done, by setting ``@Api\Access("fe_users")`` as Annotation in the endpoints method.

.. code-block:: javascript

    // Your endpoint. Only fe_users may access it.
    var url = 'https://www.mywebsite.com/api/test/something';

    var xhr = new XMLHttpRequest();
    xhr.overrideMimeType('application/json');
    xhr.open('GET', url);

    // The JWT we stored after authenticating
    var token = localStorage.getItem('token');
    xhr.setRequestHeader('Authorization', 'Bearer ' + token);

    xhr.onload = function () {
        var data = JSON.parse( xhr.responseText );
        if (xhr.status != 200) {
            alert( 'Error ' + xhr.status + ': ' + data.error );   
            return false;
        }
        console.log( data );
    };

    xhr.onerror = function () {
        alert( 'Some other error... probably wrong url?' );
    };

    xhr.send();

Checking the login status 
~~~~~~~~~~~~

The nnrestapi comes with an endpoint to check, if the JWT is still valid. Or, another words, If the frontend-user
is still logged in and has a valid session.

.. hint::

    The session lifetime (the time the frontend-user session is valid) can be set in the backend.
    Have a look at the extension configuration for ``nnrestapi`` in the Extension Manager.

Simply send a ``GET``-request to the endpoint ``/api/user`` and pass the ``Authentication Bearer`` header.
If the session is stil valid, the API will return information about the current frontend-user.

.. code-block:: javascript

    // This endpoint is part of the nnrestapi 
    var checkUserUrl = 'https://www.mywebsite.com/api/user';

    var xhr = new XMLHttpRequest();
    xhr.overrideMimeType('application/json');
    xhr.open('GET', checkUserUrl);

    // The JWT we stored after authenticating
    var token = localStorage.getItem('token');
    xhr.setRequestHeader('Authorization', 'Bearer ' + token);

    xhr.onload = function () {
        var data = JSON.parse( xhr.responseText );
        if (xhr.status != 200) {
            alert( 'Error ' + xhr.status + ': ' + data.error );   
            return false;
        }
        console.log( data );
    };

    xhr.onerror = function () {
        alert( 'Some other error... probably wrong url?' );
    };

    xhr.send();

The result will be very similar to the object returned during authentication, but the response
will not contain the ``token``:

.. code-block:: javascript

    {
        uid: 9,
        username: "john",
        usergroup: [3, 5],
        first_name: "John",
        last_name: "Malone",
        lastlogin: 1639749538
    }

Full plain JavaScript ("VanillaJS") Starter Template with login-form
~~~~~~~~~~~~

Here is a template with login-form and testbed to get you started. It will show a login-form and - after successful authentication -
a testform to send JSON-requests with ``GET``, ``POST``, ``PUT``, ``DELETE`` and ``PATCH`` requests:


.. tip::

    | **Fastest way to play with this code?**
    | Head on to `Codepen <https://codepen.io/99grad/pen/OJxgdye>`__ and have fun!


.. code-block:: html

    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>nnrestapi axios Demo with pure JavaScript for older browser</title>

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

        <div class="container my-5" id="login-form">
            <div class="form-floating mb-4">
                <input class="form-control" id="url-auth" value="https://www.mysite.com/api/auth" />
                <label for="url-auth">URL to auth-endpoint</label>
            </div>
            <div class="form-floating mb-4">
                <input class="form-control" id="username" value="" />
                <label for="username">Username</label>
            </div>
            <div class="form-floating mb-4">
                <input type="password" class="form-control" id="password" value="" />
                <label for="password">password</label>
            </div>
            <div class="form-floating mb-4">
                <button id="btn-login" class="btn btn-primary">Login</button>
            </div>		
        </div>

        <div class="container my-5 d-none" id="test-form">
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
                <input class="form-control" id="url-request" value="https://www.mysite.com/api/endpoint/somewhere" />
                <label for="url">URL to endpoint</label>
            </div>
            <div class="form-floating mb-4">
                <textarea class="form-control" id="json-data">{"title":"Test"}</textarea>
                <label for="json-data">JSON data</label>
            </div>
            <div class="form-floating mb-4">
                <button id="btn-request" class="btn btn-primary">Send to API</button>
            </div>
            <pre id="result"></pre>
        </div> 

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
        <script>
            
            /**
             * Helper-function to send requests for older
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

            /**
             * Login form
             * 
             */
            document.getElementById('btn-login').addEventListener('click', function () {

                var authUrl = document.getElementById('url-auth').value;
                
                var credentials = {
                    username: document.getElementById('username').value,
                    password: document.getElementById('password').value
                };
                
                sendRequest( authUrl, credentials, 'POST', authSuccessful, authFailed  );

                function authSuccessful( data ) {

                    // everything ok. Store the token.
                    localStorage.setItem('token', data.token);

                    // show the request-form
                    document.getElementById('login-form').classList.add('d-none');
                    document.getElementById('test-form').classList.remove('d-none');
                }

                function authFailed( data ) {
                    alert( `Error ${data.status}: ${data.error}` ); 
                }

            });

            /**
             * Test form
             * 
             */
            document.getElementById('btn-request').addEventListener('click', function () {

                var requestUrl = document.getElementById('url-request').value;
                var method = document.getElementById('request-method').value;
                var json = document.getElementById('json-data').value;

                sendRequest( requestUrl, json, method, requestSuccessful, requestFailed  );

                function requestSuccessful( data ) {
                    document.getElementById('result').innerText = JSON.stringify( data );
                }

                function requestFailed( data ) {
                    alert( `Error ${data.status}: ${data.error}` );   
                }
            });

        </script>
    </body>
    </html>
