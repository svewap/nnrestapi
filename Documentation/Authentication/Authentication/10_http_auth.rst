.. include:: ../Includes.txt

.. _authentication_http:

============
HTTP Basic Auth
============

How to authenticate a request to your TYPO3 RestApi using HTTP Basic Auth
---------

.. tip::

   HTTP Basic Auth is one of the three ways you can authenticate to the TYPO3 Rest Api when making a request to the backend.
   The alternative methods are using :ref:`JWT (JSON Web Tokens)<examples_axios_auth>` or the standard TYPO3 fe_user-cookie.

Basic access authentication (or "HTTP Basic Auth") is a very simple method for an HTTP user agent (browser) to provide user 
credentials (username and password) when making a request to the TYPO3 Rest api. In basic HTTP authentication, a request contains 
a header field in the form of ``Authorization: Basic <credentials>`` where credentials is the Base64 encoding of ID and password 
joined by a single colon.

You can define the credentials either on a per-user base - or as "global" API-keys that can be used my multiple users. 

Setting HTTP Basic Auth credentials for a single frontend-users
---------

Follow these steps to set up a username and password for a frontend user that can be used for HTTP basic access authentication.

.. rst-class:: bignums-tip

   1. | **Create a frontend user**
      In the TYPO3-backend: Create a SysFolder for your frontend users, switch to the listview and **add a frontend user**
      to the folder. Depending on your TYPO3 version, you will need to first create a **frontend user group**. 
   
   2. | **Set the username**
      In the tab "General" of the new frontend user, enter a **Username** and Password.
      The Username will be used for the HTTP Basic Auth. The password you set in the tab "Genereal" is **not relevant** 
      for the HTTP Basic Auth, it will only be used for the standard TYPO3 login form.
      
   3. | **Set the Rest-Api Key**
      Switch to the **tab "RestAPI"**. Enter a password in the field **"Rest-Api Key"**. This will be the password 
      that must be used when sending requests with HTTP Basic Authentication to the TYPO3 Rest Api.

   4. | **Check your @Api\Access()-annotations**
      Make sure, the endpoints that should be accessible by the user have the correct rights set using the ``@Api\Access()``
      annotation. Examples could be:

      .. code-block:: php

         // Expose this endpoint to ALL fe_users that have an apiKey
         @Api\Access("api_users")

         // ... or only to the fe_user "john"
         @Api\Access("api_users[john]")

         // ... or to the users defined in the TypoScript setup
         @Api\Access("config[myUsers]")
      
      You can find detailled configuration options in :ref:`this section <access>` of the documentation.

See the :ref:`examples below <authentication_http_examples>` on how to create a request in JavaScript using Basic 
HTTP Authorization.

.. hint::

   **The frontend user is logged in!**

   Note that a frontend user authenticating via HTTP basic auth with his username and the apiKey will also be logged in 
   as a normal frontend user. Consequently you could also use the Annotation ``@Api\Access("fe_users")`` or 
   ``@Api\Access("fe_users[john]")`` as an alternative to ``api_users``.

   The advantage of using ``@Api\Access("api_users")`` is, that you can have seperate passwords for the "normal" frontend
   user login and the API usage. This way, in case you allow frontend users to reset their password, they will not
   automatically have access to the api!


Setting global HTTP Basic Auth credentials
---------

Follow these steps, if you would like to create a global API Key that is not bound to a certain TYPO3 frontend user.

.. rst-class:: bignums-tip

   1. | **Edit extension settings in the backend**
      Switch to the backend module "Settings", then click on the "Configure Extensions" tile.

   2. | **Set credentials in the extension configuration**
      In the field "API-Keys for BasicAuth" (basic.apiKeys): Enter one user and key per line. In every line, 
      seperate the user and key with a colon. The list of users will look like this:

      .. code-block:: bash

         username_1:password_1
         username_2:password_2
         username_3:password_3
         ...

   3. | **Check your @Api\Access()-annotations**
      Make sure, the endpoints that should be accessible by the user have the correct rights set using the ``@Api\Access()``
      annotation. Examples could be:

      .. code-block:: php

         // Expose this endpoint to ALL api_users
         @Api\Access("api_users")

         // ... or only to the user "john"
         @Api\Access("api_users[john]")

         // ... or to the users defined in the TypoScript setup
         @Api\Access("config[myUsers]")

   4. | **Clear the TYPO3 cache**
      Click the "clear cache" button (red lightning-icon to "clear all caches")


.. hint::

   **Global users are not frontend-users!**

   If you are defining usernames and apiKeys in the extension configuration manager, these users will **not** be logged in
   as a frontend-user.

   To avoid conflicts, make sure, the usernames you are using in the extension configuration manager are unique and 
   don't already exist as a username for a TYPO3 frontend user!

.. _authentication_http_examples:

Sending request to the TYPO3 Rest Api using HTTP Basic Auth
---------

Here are some basic examples of how to send requests to your API using HTTP basic authentication:

.. tabs::

   .. tab:: PHP

      .. code-block:: php

         <?php

         $username = 'john';
         $apiKey = 'xxxx';
         $uri = 'www.yourserver.com/api/your/endpoint';

         $result = file_get_contents("https://{$username}:{$apiKey}@{$uri}');
         $arr = json_decode($result, true);

         print_r( $arr );

   .. tab:: axios

      .. code-block:: javascript

         const requestUrl = 'https://www.yourserver.com/api/your/endpoint';
         const method = 'get';
         const json = {title:'Test'};
         
         const auth = {
            username: 'john',
            password: 'xxxx'
         };

         axios({
               method: method,
               url: requestUrl,
               data: json,
               auth: auth
         }).then( ({data}) => {
               console.log( data );
         }).catch( ({response}) => {
               console.log( response.data );
         });
         
   .. tab:: jQuery

      .. code-block:: javascript

         var method = 'GET';
         var url = 'https://www.mywebsite.com/api/endpoint';
         
         var username = 'john';
         var password = 'xxxx';
         
         var payload = JSON.stringify({
            title: 'Test'
         });

         $.ajax({
            url: url,
            type: method,
            data: payload,
            crossDomain: true,
            beforeSend: function (xhr) {
               xhr.setRequestHeader('Authorization', 'Basic ' + btoa(username + ':' + password));
            },
         }).done(function (result) {
            $('#result').text( JSON.stringify(result) );
         }).fail(function (error) {
            alert( 'Error ' + error.status + ': ' + error.responseJSON.error );
            $('#result').text( 'ERROR: ' + JSON.stringify(error) );
         });

   .. tab:: pure JS

      .. code-block:: javascript

         const url = 'https://www.mywebsite.com/api/your/endpoint';

         const auth = {
            username: 'john',
            password: 'xxxx'
         };

         const xhrConfig = {
            method: 'GET',
            headers: {
               'Content-Type': 'application/json',
               'Authorization': 'Basic ' + btoa(`${auth.username}:${auth.password}`)
            },
         };

         fetch( url, xhrConfig )
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
            
   .. tab:: pure JS (old browsers)

      .. code-block:: javascript

         var method = 'GET';
         var url = 'https://www.mywebsite.com/api/endpoint';
         
         var username = 'john';
         var password = 'xxxx';
         
         var payload = JSON.stringify({
            title: 'Test'
         });

         var xhr = new XMLHttpRequest();
         xhr.overrideMimeType('application/json');
         xhr.open(method, url);

         if (username) {
            xhr.setRequestHeader('Authorization', 'Basic ' + btoa(username + ':' + password));
         }

         xhr.onload = function () {
            var data = JSON.parse( xhr.responseText );
            if (xhr.status != 200) {
               alert('Error!');
            }
            console.log( data );               
         };

         xhr.onerror = function () {
            alert('Some other error... probably wrong url?');
         };

         if (['GET', 'DELETE'].indexOf(method) == -1) {
            xhr.send( payload );
         } else {
            xhr.send();
         }



Full Testscript
----------

.. tabs::

   .. tab:: axios

      Edit this code on `CodePen <https://codepen.io/99grad/pen/YzrayZe>`__

      .. code-block:: html

         <!doctype html>
         <html lang="en">
            <head>
               <meta charset="utf-8">
               <meta name="viewport" content="width=device-width, initial-scale=1">
               <title>nnrestapi Demo with HTTP basic authentication</title>

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

               <div class="container my-5" id="test-form">
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
                     <input class="form-control" id="url-request" value="https://www.mywebsite.com/api/endpoint" />
                     <label for="url">URL to endpoint</label>
                  </div>
                  <div class="row">
                     <div class="col">
                        <div class="form-floating mb-4">
                           <input class="form-control" id="username" value="" />
                           <label for="username">Username</label>
                        </div>
                     </div>
                     <div class="col">
                        <div class="form-floating mb-4">
                           <input type="password" class="form-control" id="password" value="" />
                           <label for="password">password</label>
                        </div>	
                     </div>
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

               document.getElementById('btn-request').addEventListener('click', () => {
                  
                  const requestUrl = document.getElementById('url-request').value;
                  const method = document.getElementById('request-method').value;
                  const json = document.getElementById('json-data').value;
                  const $result = document.getElementById('result');
                  
                  const auth = {
                     username: document.getElementById('username').value,
                     password: document.getElementById('password').value
                  };

                  axios({
                     method: method.toLowerCase(),
                     url: requestUrl,
                     data: json,
                     auth: auth
                  }).then( ({data}) => {
                     $result.innerText = JSON.stringify( data );
                  }).catch( ({response}) => {
                     $result.innerText = JSON.stringify( response.data );
                  });
                  
               });

               </script>
            </body>
         </html>


   .. tab:: jQuery

      Edit this code on `CodePen <https://codepen.io/99grad/pen/OJxvyzd>`__

      .. code-block:: html

         <!doctype html>
         <html lang="en">
            <head>
               <meta charset="utf-8">
               <meta name="viewport" content="width=device-width, initial-scale=1">
               <title>nnrestapi Demo with HTTP basic authentication</title>

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

               <div class="container my-5" id="test-form">
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
                     <input class="form-control" id="url-request" value="https://www.mywebsite.com/api/endpoint" />
                     <label for="url">URL to endpoint</label>
                  </div>
                  <div class="row">
                     <div class="col">
                        <div class="form-floating mb-4">
                           <input class="form-control" id="username" value="" />
                           <label for="username">Username</label>
                        </div>
                     </div>
                     <div class="col">
                        <div class="form-floating mb-4">
                           <input type="password" class="form-control" id="password" value="" />
                           <label for="password">password</label>
                        </div>	
                     </div>
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
               <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
               <script>

                  $('#btn-request').click(function () {
                     $('#result').show().text('Loading...');

                     $.ajax({
                        url: $('#url-request').val(),
                        type: $('#request-method').val(),
                        data: $('#json-data').val(),
                        crossDomain: true,
                        beforeSend: function (xhr) {
                           xhr.setRequestHeader("Authorization", "Basic " + btoa($('#username').val() + ":" + $('#password').val()));
                        },
                     }).done((result) => {
                        $('#result').text( JSON.stringify(result) );
                     }).fail((error) => {
                        alert( `Error ${error.status}: ${error.responseJSON.error}` );
                        $('#result').text( 'ERROR: ' + JSON.stringify(error) );
                     });
                  });

               </script>
            </body>
         </html>

   .. tab:: pure JS

      Edit this code on `CodePen <https://codepen.io/99grad/pen/JjrLdJo>`__

      .. code-block:: html

         <!doctype html>
         <html lang="en">
            <head>
               <meta charset="utf-8">
               <meta name="viewport" content="width=device-width, initial-scale=1">
               <title>nnrestapi Demo with HTTP basic authentication</title>

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

               <div class="container my-5" id="test-form">
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
                     <input class="form-control" id="url-request" value="https://extensions.99grad.dev/api/entry/4" />
                     <label for="url">URL to endpoint</label>
                  </div>
                  <div class="row">
                     <div class="col">
                        <div class="form-floating mb-4">
                           <input class="form-control" id="username" value="david" />
                           <label for="username">Username</label>
                        </div>
                     </div>
                     <div class="col">
                        <div class="form-floating mb-4">
                           <input type="password" class="form-control" id="password" value="malone" />
                           <label for="password">password</label>
                        </div>	
                     </div>
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

                  document.getElementById('btn-request').addEventListener('click', () => {

                     var requestUrl = document.getElementById('url-request').value;
                     var method = document.getElementById('request-method').value;
                     var json = document.getElementById('json-data').value;

                     var auth = {
                        username: document.getElementById('username').value,
                        password: document.getElementById('password').value
                     };

                     const xhrConfig = {
                        method: method,
                        headers: {
                           'Content-Type': 'application/json',
                           'Authorization': 'Basic ' + btoa(auth.username + ':' + auth.password)
                        },
                     };

                     fetch( requestUrl, xhrConfig )
                        .then( async response => {

                           // convert the result to a JavaScript-object
                           let data = await response.json()

                           if ( !response.ok ) {
                              // reponse was not 200
                              alert( `Error ${response.status}: ${data.error}` );
                           } else {
                              // everything ok!
                              console.log( data );
                              document.getElementById('result').innerText = JSON.stringify( data );
                           }
                        });
                  });

               </script>
            </body>
         </html>

   .. tab:: pure JS (old browsers)

      Edit this code on `CodePen <https://codepen.io/99grad/pen/RwLMwEq>`__

      .. code-block:: html

         <!doctype html>
         <html lang="en">
            <head>
               <meta charset="utf-8">
               <meta name="viewport" content="width=device-width, initial-scale=1">
               <title>nnrestapi Demo with HTTP basic authentication</title>

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

               <div class="container my-5" id="test-form">
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
                     <input class="form-control" id="url-request" value="https://www.mywebsite.com/api/endpoint" />
                     <label for="url">URL to endpoint</label>
                  </div>
                  <div class="row">
                     <div class="col">
                        <div class="form-floating mb-4">
                           <input class="form-control" id="username" value="" />
                           <label for="username">Username</label>
                        </div>
                     </div>
                     <div class="col">
                        <div class="form-floating mb-4">
                           <input type="password" class="form-control" id="password" value="" />
                           <label for="password">Password</label>
                        </div>	
                     </div>
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
                   * Helper-function to send REST Api requests for older
                   * browsers not supporting fetch()
                   * 
                   */
                  function sendRequest( url, payload, method, auth, done, fail ) {

                     if (typeof payload == 'object') {
                        payload = JSON.stringify(payload);
                     }

                     var xhr = new XMLHttpRequest();
                     xhr.overrideMimeType('application/json');
                     xhr.open(method, url);

                     if (auth.username) {
                        xhr.setRequestHeader('Authorization', 'Basic ' + btoa(auth.username + ':' + auth.password));
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
                   * Test form
                   * 
                   */
                  document.getElementById('btn-request').addEventListener('click', function () {

                     var requestUrl = document.getElementById('url-request').value;
                     var method = document.getElementById('request-method').value;
                     var json = document.getElementById('json-data').value;

                     var auth = {
                        username: document.getElementById('username').value,
                        password: document.getElementById('password').value
                     };

                     sendRequest( requestUrl, json, method, auth, requestSuccessful, requestFailed  );

                     function requestSuccessful( data ) {
                        document.getElementById('result').innerText = JSON.stringify( data );
                     }

                     function requestFailed( data ) {
                        alert( 'Error ' + data.status + ': ' + data.error );   
                     }

                  });

               </script>
            </body>
         </html>