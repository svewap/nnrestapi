.. include:: ../Includes.txt

.. _authentication_cookies:

============
Cookies
============

How to authenticate a user using the TYPO3 frontend user cookie
---------

When you log in as a frontend user, TYPO3 will automatically set a cookie named ``fe_typo_user`` containing
a session ID to identify the user. In a standard TYPO3 website, this cookie is sent with every subsequential request.

Same applies to AJAX-requests you make from JavaScript. As long as your frontend application and TYPO3 Rest Api are 
hosted on the same domain, things should run pretty smooth.

Using the fe_typo_user-cookie on same domain
---------

If your frontend application is running under the same domain that the REST Api is located on, there is really 
not much to pay attention to.

Simply send your credentials in a POST-request to the endpoint 
``https://www.mywebsite.com/api/auth``. This endpoint is part of the nnrestap-extension.

.. code-block:: php

   // POST this to https://www.mywebsite.com/api/auth

   {"username":"john", "password":"xxxx"}

TYPO3 will respond with information about the user. TYPO3 will also send a cookie named ``fe_typo_user`` containing
a session ID. This cookie will automatically be set in the browser and passed back to the server in your next request.

Here is a full script to test cookie based authentication. Please upload it to the **same domain** that your 
TYPO3 REST Api is located on.

.. tabs::

   .. tab:: axios

      .. code-block:: html

         <!doctype html>
         <html lang="en">
            <head>
               <meta charset="utf-8">
               <meta name="viewport" content="width=device-width, initial-scale=1">
               <title>nnrestapi Demo with axios and cookies</title>

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
                     <input class="form-control" id="url-request" value="https://www.mysite.com/api/user" />
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
               <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

               <script>

                  /**
                   * Login form
                   *
                   */
                  document.getElementById('btn-login').addEventListener('click', () => {

                     const authUrl = document.getElementById('url-auth').value;

                     const credentials = {
                        username: document.getElementById('username').value,
                        password: document.getElementById('password').value
                     };

                     // Authenticate frontend user
                     axios.post(authUrl, credentials).then(({data}) => {

                        axios.defaults.withCredentials = true;

                        document.getElementById('login-form').classList.add('d-none');
                        document.getElementById('test-form').classList.remove('d-none');

                     }).catch(({response}) => {
                        alert( `Error ${response.status}: ${response.data.error}` );
                     });

                  });

                  /**
                   * Test form
                   *
                   */
                  document.getElementById('btn-request').addEventListener('click', () => {

                     const requestUrl = document.getElementById('url-request').value;

                     axios({
                        url: requestUrl,
                        method: document.getElementById('request-method').value,
                        data: document.getElementById('json-data').value
                     }).then(({data}) => {
                        document.getElementById('result').innerText = JSON.stringify( data );
                     }).catch(({response}) => {
                        alert( `Error ${response.status}: ${response.data.error}` );
                     });

                  });

               </script>
            </body>
         </html>

   .. tab:: pure JS

      .. code-block:: html

         <!doctype html>
         <html lang="en">
            <head>
               <meta charset="utf-8">
               <meta name="viewport" content="width=device-width, initial-scale=1">
               <title>nnrestapi Demo with cookie based authentication</title>

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
                     <input class="form-control" id="url-request" value="https://www.mysite.com/api/user" />
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
                   * Login form
                   * 
                   */
                  document.getElementById('btn-login').addEventListener('click', () => {

                     const authUrl = document.getElementById('url-auth').value;

                     const credentials = {
                        username: document.getElementById('username').value,
                        password: document.getElementById('password').value
                     };

                     const xhrConfig = {
                        method: 'POST',
                        headers: {
                           'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(credentials)
                     };

                     fetch( authUrl, xhrConfig )
                        .then( async response => {

                        // convert the result to a JavaScript-object
                        let data = await response.json()

                        if ( !response.ok ) {
                           // reponse was not 200
                           alert( `Error ${response.status}: ${data.error}` );   
                        } else {
                           // show the request-form
                           document.getElementById('login-form').classList.add('d-none');
                           document.getElementById('test-form').classList.remove('d-none');
                        }
                     });

                  });

                  /**
                   * Test form
                   * 
                   */
                  document.getElementById('btn-request').addEventListener('click', () => {

                     const requestUrl = document.getElementById('url-request').value;
                     const method = document.getElementById('request-method').value;
                     const json = document.getElementById('json-data').value;

                     const xhrConfig = {
                        method: method,
                        credentials: 'include',
                        headers: {
                           'Content-Type': 'application/json',
                        }
                     };

                     if (['GET', 'DELETE'].indexOf(method) == -1) {
                        xhrConfig.body = JSON.stringify(json);
                     }

                     fetch( requestUrl, xhrConfig )
                        .then( async response => {

                        // convert the result to a JavaScript-object
                        let data = await response.json()

                        if ( !response.ok ) {
                           // reponse was not 200
                           alert( `Error ${response.status}: ${data.error}` );   
                        } else {
                           document.getElementById('result').innerText = JSON.stringify( data );
                        }
                     });
                  });

               </script>
            </body>
         </html>


Cross domain fe_typo_user-cookie
---------

For cross domain requests, e.g. if your Rest API backend is running on a different server than your application or
you are connection from a localhost environment to a remote server, using cookies will get complicated. 

In this case we would recommend using one of the other methods for authentication:  :ref:`HTTP basic auth <authentication_http>` 
or :ref:`JSON Web Tokens <authentication_jwt>`.
