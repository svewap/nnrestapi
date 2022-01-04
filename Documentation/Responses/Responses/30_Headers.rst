.. include:: ../Includes.txt

.. _responses_headers:

============
Headers and CORS
============

.. warning::

   The default configuration of the nnrestapi is very "open": It allows cross-domain requests (CORS)
   and cookies. This is great for developing from a localhost environment or testing your API with
   tools like Postman or CodePen.
   
   In a production environment, you should change these settings and make them more secure. Never
   allow access to your Api from domains that you don't know and trust! The same applies to accepting
   cross-domain cookies!

Settings HTTP headers of your TYPO3 RestApi response
~~~~~~~~~~

When creating the response, the ``nnrestapi`` sends a list of headers to make the Rest Api as "compatible" as
possible during development. By default, it also enables cross-domain-requests (CORS) and the setting of 
cross-domain-cookies.

Changing a default header value
~~~~~~~~~~

If you would like to change a default header value sent by the nnrestapi, simply set the new value 
in your TypoScript setup using the existing key:

.. code-block:: typoscript

   plugin.tx_nnrestapi.settings.response {
      headers {
         Access-Control-Allow-Credentials = false
      }
   }

Adding additional headers to your response
~~~~~~~~~~

To add another HTTP header to your response, add it to the TypoScript setup:

.. code-block:: typoscript

   plugin.tx_nnrestapi.settings.response {
      headers {
         X-My-Special-Header = Some value
      }
   }

Removing a header to your response
~~~~~~~~~~

If you would like to remove any of the default headers sent by the TYPO3 Rest Api, simply set the value
in the TypoScript setup to an empty string:

.. code-block:: typoscript

   plugin.tx_nnrestapi.settings.response {
      headers {
         Access-Control-Allow-Origin = 
      }
   }

.. _response_headers_default:

Overview of default headers sent:
~~~~~~~~~~

+------------------------------------------------------+------------------------------------------------------------------+
| HTTP header type                                     | Default value and explanation                                   |
+======================================================+==================================================================+
| ``Access-Control-Allow-Origin``                      | ``*``                                                            |
|                                                      |                                                                  |
|                                                      | The REST Api may be accessed from **any** domain. Makes life     |
|                                                      | easier during development, because you can test from a           |
|                                                      | localhost or CodePen environment.                                |
|                                                      |                                                                  |
|                                                      | Under the hood, nnrestapi is actually responding with the        |
|                                                      | exact ``HTTP_ORIGIN`` so ``Access-Control-Allow-Credentials``    |
|                                                      | can be set to  ``true`` and Cookies can be shared across domains |
|                                                      |                                                                  |
|                                                      | Please refer to the :ref:`configuration section <configuration>` |
|                                                      | for more information.                                            |
|                                                      |                                                                  |
|                                                      | **Security Risk**: Please consider changing this value!          |
|                                                      | `Find out why you should change it <https://bit.ly/3EOoxzo>`__   |
+------------------------------------------------------+------------------------------------------------------------------+
| ``Access-Control-Allow-Credentials``                 | ``true``                                                         |
|                                                      |                                                                  |
|                                                      | Normally, cookies are not sent when making requests from a       |
|                                                      | foreign domain or localhost environment. By sending ``true``     |
|                                                      | here in combination with using the ``withCredentials`` option    |
|                                                      | in the JavaScript frontend application, Cookies can be shared    |
|                                                      | across domains.                                                  |
|                                                      |                                                                  |
|                                                      | This is useful to allow authenticating the TYPO3 Frontend User   |
|                                                      | using the standard ``fe_typo_user``-cookie.                      |
|                                                      |                                                                  |
|                                                      | You should consider changing this to ``false`` in a production   |
|                                                      | environment, if the only application accessing the API is        |
|                                                      | running under the same domain or you are not authenticating      |
|                                                      | using cookies.                                                   |
+------------------------------------------------------+------------------------------------------------------------------+
| ``Access-Control-Allow-Methods``                     | ``GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS``                 |
|                                                      |                                                                  |
|                                                      | Before the "real" request is sent to the server, the frontend    |
|                                                      | might send a `preflight request <https://mzl.la/3sTjIT9>`__ to   |
|                                                      | make sure, the request method for the real request is allowed    |
|                                                      |                                                                  |
|                                                      | With this header, our REST Api is saying: All request methods    |
|                                                      | are allowed, so go on and send the actual request.               |
+------------------------------------------------------+------------------------------------------------------------------+
| ``Allow``                                            | ``GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS``                 |
|                                                      |                                                                  |
|                                                      | Similar to above, `find out more <https://mzl.la/3HrMrTb>`__     |
+------------------------------------------------------+------------------------------------------------------------------+
| ``Cache-Control``                                    | ``no-store, no-cache, must-revalidate, max-age=0, post-check=0,  |
|                                                      | pre-check=0, false``                                             |
|                                                      |                                                                  |
|                                                      | We are telling the browser / app **not** to cache the results.   |
|                                                      | This will increase the number of requests to your server.        |
|                                                      |                                                                  |
|                                                      | Consider changing this value - or at least using                 |
|                                                      | :ref:`@Api\Cache() <annotations_cache>` wherever you can.        |
+------------------------------------------------------+------------------------------------------------------------------+
| ``Pragma``                                           | ``no-cache``                                                     |
|                                                      |                                                                  |
|                                                      | Same intention here as described under ``Cache-Control``         |
+------------------------------------------------------+------------------------------------------------------------------+