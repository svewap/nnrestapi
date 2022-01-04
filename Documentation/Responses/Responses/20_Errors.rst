.. include:: ../Includes.txt

.. _responses_errors:

============
Error Responses
============

Returning an 404 NOT FOUND
~~~~~~~~~~

The ``nnrestapi`` has a few shortcuts built in to respond with error codes, if the request parameters were
invalid or the requested data could not be retrieved.

Have a look at the class ``Nng\Nnrestapi\Mvc\Response`` to see all available options.

Here we are checking for a model. If it can't be found, we return a ``404 NOT FOUND`` error:

.. code-block:: php

   <?php   
   namespace My\Extension\Api;

   use Nng\Nnrestapi\Annotations as Api;

   class Test extends \Nng\Nnrestapi\Api\AbstractApi {

      /**
       * Call via GET-request with an uid: https://www.mywebsite.com/api/test/1 
       *
       * @Api\Access("public")
       * @return array
       */
      public function getIndexAction( $uid = null )
      {
         $entry = $this->entryRepository->findByUid( $uid );
         if (!$entry) {
            return $this->response->notFound('Model with uid [' . $uid . '] was not found.');
         }
         return $entry;
      }
   }

If you open the URL ``https://www.mywebsite.com/api/test/1`` in your browser and pass the ``uid``
of an entry that does not exist, you will see the following JSON response. It will be sent with
a ``404 NOT FOUND`` header:

.. code-block:: json

   {"status":404, "error":"Model with uid [1] was not found."}


Overview of error codes
~~~~~~~~~~


+------------------------------------------------------+------+-------------------------------------------------------+
| shortcut                                             | code | description                                           |
+======================================================+======+=======================================================+
| ``$this->response->success([...], 'OK')``            | 200  | OK - sent, if no other option was called              |
+------------------------------------------------------+------+-------------------------------------------------------+
| ``$this->response->noContent('message')``            | 204  | Empty response                                        |
+------------------------------------------------------+------+-------------------------------------------------------+
| ``$this->response->unauthorized('message')``         | 403  | Unauthorized (not logged in)                          |
+------------------------------------------------------+------+-------------------------------------------------------+
| ``$this->response->forbidden('message')``            | 403  | Alias to unauthorized                                 |
+------------------------------------------------------+------+-------------------------------------------------------+
| ``$this->response->notFound('message')``             | 404  | Not found                                             |
+------------------------------------------------------+------+-------------------------------------------------------+
| ``$this->response->invalid('message')``              | 422  | Invalid request parameters                            |
+------------------------------------------------------+------+-------------------------------------------------------+
| ``$this->response->error($code, 'message')``         | any  | Custom response                                       |
+------------------------------------------------------+------+-------------------------------------------------------+
