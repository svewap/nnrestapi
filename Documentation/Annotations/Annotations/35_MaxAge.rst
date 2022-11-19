.. include:: ../Includes.txt

.. _annotations_maxage:

============
@Api\\MaxAge
============

Sends Cache-Control headers for a TYPO3 RestAPi endpoint
---------

With the default settings of nnrestapi, the client-side cache will be disabled 
by sending the default ``Cache-Control: no-cache`` and ``Paragma`` headers.

If the data doesn't change very often, it doesn't make sense for the client to 
keep requesting the same data from the endpoint. By sending an appropriate 
Cache-Control header you can tell the client how many seconds it should  
use data stored in the local cache before sending the next request to the Api.

Here is how you can set your custom ``max-age`` Header:

**The syntax is:**

.. code-block:: php

   @Api\MaxAge( seconds )

**Here is a full example:**

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
       * @Api\MaxAge( 600 )
       * @Api\Access("public")
       *
       * @return array
       */
      public function getAllAction() 
      {
         $result = $this->someComplicatedOperation();
         return $result;
      }

   }


Handling the Cache-Control header yourself
---------

If you would like to send the ``Cache-Control`` headers from inside of your method, you can use
``$this->response->setMaxAge( $seconds )``. 

You can also add, modify or remove any other default header sent by the nnrestapi by calling 
:ref:`$this->response->addHeader( $headerArray ) <responses_headers>`. More information can be found 
in :ref:`this chapter <responses_headers>`

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
       * @Api\Access("public")
       *
       * @return array
       */
      public function getAllAction() 
      {
         $this->response->setMaxAge( 100 );

         $result = $this->someComplicatedOperation();
         return \nn\t3::Cache()->set($cacheIdentifier, $result);
      }

   }