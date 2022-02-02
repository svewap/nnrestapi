.. include:: ../Includes.txt

.. _authentication_jwt:

============
JSON Web Token (JWT)
============

How to authenticate a user with your TYPO3 RestApi using a JSON Web Token
---------

.. tip::

   **Examples, examples, examples!**

   We have spent many hours putting together nice recipes and CodePens that will help you get started on the topic 
   "retrieving, storing and authenticating with a JWT" in no time at all. Look at the examples for your favorite 
   framework: :ref:`axios <examples_axios_auth>`, :ref:`jQuery <examples_jquery_auth>`, :ref:`Pure JS <examples_plain_auth>` 
   or :ref:`older browsers <examples_legacy_auth>`.


To keep the frontend user logged in, TYPO3 usually sets a cookie. This cookie (``fe_typo_user``) serves fine in most contexts - 
but relying on the TYPO3 cookie has a few limitations:

-  **Cookies are domain-bound**. Out of the box, TYPO3 only allows cookies from the same domain. 
   Although there are ways to let TYPO3 accept cross domain (or subdomain) cookies, the main focus in TYPO3 was not
   to have authenticated users sending requests from other origins than the server that hosts the TYPO3 backend.

-  **The session-ID of the cookie might change**. Depending on your configuration, TYPO3 will do a great job on
   keeping your session safe by changing the session-ID stored in the ``fe_typo_user``-cookie and invalidating
   the old session-ID. While this is great in some contexts, in a SPA (Single Page Application) with a JS-frontend
   this can be a little "stressful". You will need to keep track of expiring cookies and sessions.

Many applications nowadays have decided to replace the session-cookie with a new way of authenticating: The JSON Web Token (JWT).
The nnrestapi extension comes equipped with everything you need to log in as a frontend-user, retrieve a JWT and send 
authenticated requests using the ``Authentication: Bearer`` header.  


How to use JSON Web Tokens in TYPO3:
---------

The following steps outline the basic principles.

For a full description with examples, have a look at the recipes and CodePens for :ref:`axios <examples_axios_auth>`,
:ref:`jQuery <examples_jquery_auth>`, :ref:`Pure JS <examples_plain_auth>` or :ref:`older browsers <examples_legacy_auth>`.

.. rst-class:: bignums-tip

   1. | **Create a frontend user**
      In the TYPO3-backend: Create a SysFolder for your frontend users, switch to the list view and **add a frontend user**
      to the folder. Depending on your TYPO3 version, you will need to first create a **frontend user group**. 
   
   2. | **Set username and password**
      In the tab "General" of the new frontend user, enter a **Username and Password**.
      
   3. | **Check your @Api\Access()-annotations**
      Make sure, the endpoints that should be accessible by the user have the correct rights set using the ``@Api\Access()``
      annotation. Examples could be:

      .. code-block:: php

         // Expose this endpoint to ALL fe_users that have an apiKey
         @Api\Access("fe_users")

         // ... or only to the fe_user "john"
         @Api\Access("fe_users[john]")

         // ... or only to the fe_users with uid 2 or 3
         @Api\Access("fe_users[2,3]")

         // ... or to the users defined in the TypoScript setup
         @Api\Access("config[myUsers]")
      
      You can find detailed configuration options in :ref:`this section <access>` of the documentation.

   4. | **Authenticate the user**
      From your frontend application: Send your credentials in a POST-request to the endpoint 
      ``https://www.mywebsite.com/api/auth``. This endpoint is part of the nnrestap-extension.

      .. code-block:: php

         // POST this to https://www.mywebsite.com/api/auth

         {"username":"john", "password":"xxxx"}

   5. | **Get the JWT from the response**
      If the username and password were correct, you will get a response with information about the 
      frontend user. The JSON also contains the JWT in the field "token":

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

   6. | **Save the token**
      Remember the token for the next requests by setting a variable or by storing it in the localStorage.

   7. | **Send requests with token**
      On every request you subsequently make: Pass the token in the **authorization**-header:
      
      .. code-block:: php

         Authorization: Bearer some_damn_long_token

Checking, if the JSON Web Tokens (JWT) is still valid:
---------

In order to check if the JWT is still valid or the frontend user session has expired, you can send a ``GET``
request to the endpoint

.. code-block:: php

   // Send a GET request to this endpoint ...
   https://www.mywebsite.com/api/user
   
   // ... and add the header:
   Authorization: Bearer some_damn_long_token

This endpoint is part of the nnrestapi extension. Again, include the token in the header of the request using 
``Authorization: Bearer token_string``. If the token is still valid, you will get a JSON with the current
user information like above.

Head on to :ref:`this section <examples_plain_auth>` for more information.