.. include:: ../Includes.txt

.. _annotations_security_maxrequests:

==================
@Api\\Security\\MaxRequestsPerMinute
==================

Limiting number of requests to an endpoint
---------

This annotation allows you limit the number of request to an endpoint per minute from the current IP-address.

**The basic syntax is:**

.. code-block:: php

   @Api\Security\MaxRequestsPerMinute( $limit, $identifier )

An example would be:

.. code-block:: php

   // Limit access to all endpoints with "my_id" to 10 per IP and minute
   @Api\Security\MaxRequestsPerMinute( 10, "my_id" )

   // Limit overall access to all endpoints using this annotation to 10 per IP and minute
   @Api\Security\MaxRequestsPerMinute( 10 )

Exceeding the given number will result in an ``403`` Error response.

The optional argument ``my_id`` can be any arbitrary key.

- When using the same key in multiple endpoints, all endpoint calls with the same key will be counted
- Without an id, all endpoints using the annotation will be counted


.. code-block:: php

   <?php
   
   namespace My\Extension\Api;

   use Nng\Nnrestapi\Annotations as Api;
   use Nng\Nnrestapi\Api\AbstractApi;
   
   /**
    * @Api\Endpoint()
    */
   class Example extends AbstractApi
   {
      /**
       * @Api\Security\MaxRequestsPerMinute(5, "getSettings")
       * @Api\Access("public")
       *
       * @return array
       */
      public function getSettingsAction() 
      {
         return ['nice'=>'result'];
      }

   }


.. hint::

   The ``\nn\rest::Security()``-Helper has many useful methods in case you would like
   to handle checking for limits and locking users manually.

   Have a look at ``\Nng\Nnrestapi\Utilities\Security`` for more details.

   .. code-block:: php

      // returns FALSE if IP has exceeded number of requests for `my_key`
      $isBelowLimit = \nn\rest::Security( $this->request )->maxRequestsPerMinute(['my_key'=>60]);

      // manually lock an IP for 5 minutes
      \nn\rest::Security( $this->request )->lockIp( 300, 'Reason why...' );

      // unlock the IP
      \nn\rest::Security( $this->request )->unlockIp();


