.. include:: ../Includes.txt

.. _custom_routing:

============
Routing by custom Routes
============

Defining custom URL paths to your TYPO3 Restful Api
------------

In certain cases you might want to define a custom routing instead of using the :ref:`routing_standard`

This can be accomplished using this annotation:

.. code-block:: php

   @Api\Route("/your/custom/url")

The Annotation gets placed in the comment above the method of your Api class.
Here is a full example:

.. code-block:: php

   <?php

   namespace My\Extension\Api;

   use Nng\Nnrestapi\Annotations as Api;
   use Nng\Nnrestapi\Api\AbstractApi;

   class Example extends AbstractApi
   {
      /**
       * @Api\Route("GET /test/route")
       * @Api\Access("public")
       * 
       * @return array
       */
      public function customRoutingTest()
      {
         return ['message'=>'Hello!'];
      }
   }

When using custom routing, the method name is irrelevant and does not have to follow the pattern 
``{request_method}{Classname}Action``. 

The above method ``customRoutingTest()``  would be executed when sending a ``GET`` Request to

.. code-block:: php

   https://www.mysite.com/api/test/route


Want to find out more?
------------

Please refer to the :ref:`annotations_route` section of this documentation for more details and examples.