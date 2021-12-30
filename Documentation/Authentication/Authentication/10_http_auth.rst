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

         // Expose this endpoint to ALL fe_users
         @Api\Access("fe_users")

         // ... or only to the fe_user "john"
         @Api\Access("fe_users[john]")

         // ... or to the users defined in the TypoScript setup
         @Api\Access("config[myUsers]")
      
      You can find detailled configuration options in :ref:`this section <access>` of the documentation.

See the :ref:`examples below <authentication_http_examples>` on how to create a request in JavaScript using Basic 
HTTP Authorization.

.. info::

   | **Why are we not using the standard fe_user-password?** 
   Simple answer: Think of an installation with a frontend user login **AND** a REST API. As soon as you offer 
   a "reset password" function for the frontend-users, they would also be able to access the REST Api with the
   password they set! You would loose control about which user can access the API.

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

   3. | **Clear the TYPO3 cache**
      Click the "clear cache" button (red lightning-icon to "clear all caches")


.. _authentication_http_examples:

Sending request to the TYPO3 Rest Api using HTTP Basic Auth
---------

