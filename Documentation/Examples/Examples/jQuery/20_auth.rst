.. include:: ../Includes.txt

.. _examples_jquery_auth:

============
Authentication
============

How to login as a Frontend-User using jQuery and send requests
------------

In most cases you will want to restrict access to your an endpoint to certain users or usergroups.
The basic way to do this in your classes and methods, is to use the :ref:`@Api\Access() Annotation <access>`.

The nnrestapi-extension comes with a default endpoint to authenticate as a Frontend User using the
credentials set in the standard ``fe_user``-record.

To keep the frontend user logged in, TYPO3 usually sets a **cookie**. Cookies tend to get rather ugly when you
are sending cross-domain requests, e.g. from your Single Page Application (SPA) or from a localhost
environment.

The nnrestapi solves this by also allowing authentication via **JWT (Json Web Token)**. 

Let's have a look, how to authenticate, retrieve a JWT with jQuery and pass it to the server when making follow-up 
request to your TYPO3 Rest Api.


Authentication with jQuery
~~~~~~~~~~~~

Use a simple ``POST``-request to the endpoint ``/api/auth`` and pass your credentials wrapped in a JSON to
authenticate as a TYPO3 Frontend-User. If you were successfully logged in, you will get an array with 
information about the frontend-user and the JSON Web Token (JWT).

In the following script we are simply "memorizing" the JWT by storing it in the 
`localStorage <https://www.w3schools.com/jsref/prop_win_localstorage.asp>`__ for later requests.

.. code-block:: javascript

    // This endpoint is part of the nnrestapi
    const authUrl = 'https://www.mywebsite.com/api/auth';

    const credentials = JSON.stringify({
        username: 'john',
        password: 'xxxx'
    });

    $.post(authUrl, credentials).done((result) => {
        alert( `Welcome ${result.username}!` );
        localStorage.setItem('token', result.token);
    }).fail((error) => {
        alert( `Error ${error.status}: ${error.responseJSON.error}` );
    });

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
    const url = 'https://www.mywebsite.com/api/test/something';

    // The JWT we stored above after authenticating
    const token = localStorage.getItem('token');

    $.ajax({
        url: url, 
        type: 'GET',
        headers: {
            Authorization: `Bearer ${token}`
        }
    }).done((result) => {
       console.log( result );
    }).fail((error) => {
        alert( `Error ${error.status}: ${error.responseJSON.error}` );
    });


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
    const checkUserUrl = 'https://www.mywebsite.com/api/user';

    // The JWT we stored above after authenticating
    const token = localStorage.getItem('token');

    $.ajax({
        url: checkUserUrl, 
        type: 'GET',
        headers: {
            Authorization: `Bearer ${token}`
        }
    }).done((result) => {
       console.log( result );
    }).fail((error) => {
        alert( `Error ${error.status}: ${error.responseJSON.error}` );
    });

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