.. include:: ../Includes.txt

.. _annotations_route:

============
@Api\\Route
============

Use custom routing for your TYPO3 RestAPI endpoint
---------

The ``@Api\Route`` annotation allows you to define custom URLs (Routes) to your endpoint
and define the order of arguments passed to your method. It is very similar to the
Symfony Routing syntax.

A basic example would be:

.. code-block:: php

   @Api\Route("/your/custom/url")

After clearing the cache, the method that has this annotation will be reachable at the URL:
``https://www.yourwebsite.com/api/your/custom/url``. 

By using custom Routing, the method name can be whatever you like – you don't not have to use
the standard method-name **{requestMethod}{pathPart}Action()**

.. code-block:: php

   <?php
   
   namespace My\Extension\Api;

   use Nng\Nnrestapi\Annotations as Api;
   use Nng\Nnrestapi\Api\AbstractApi;
   
   class Example extends AbstractApi
   {
      /**
       * @Api\Route("/your/custom/url")
       * @Api\Access("public")
       *
       * @return array
       */
      public function anyMethodNameYouLike() 
      {
         return ['nice'=>'result'];
      }

   }


.. hint::

   In case you want to also change the URL prefix ``/api`` you can override the default settings in the
   YAML configuration by setting a custom value for the ``basePath`` in your YAML-site configuration:

   .. code-block:: yaml

      nnrestapi:
         routing:
            basePath: '/api'


Parsing request parameters
~~~~~~~

If you want to parse request parameters from the URL you can use the following syntax in you ``@Api\Route`` definition.
In this example, every URL like ``https://www.mywebsite.com/api/test/demo/123`` will be routed to your endpoint and
``123`` will be parsed as argument ``['uid' => 123]`` 

.. code-block:: php

   // https://www.mywebsite.com/api/test/demo/123
   @Api\Route("/test/demo/{uid}") 

You can add as many path segments as you like:

.. code-block:: php

   // https://www.mywebsite.com/api/test/demo/123/whatever
   @Api\Route("/test/demo/{uid}/{test}")

In the two above examples, the routing will only work, if ``{uid}`` or ``{uid}/{test}`` is set. Calling an URL
without these path-segments (e.g. ``https://www.mywebsite.com/api/test/demo``) will **not** route to your method.

To make the parameters optional, you can use the following route patterns:

.. code-block:: php

   // https://www.mywebsite.com/api/test/demo/123
   // https://www.mywebsite.com/api/test/demo
   @Api\Route("/test/demo/{uid?}")

Or for multiple arguments:

.. code-block:: php

   // https://www.mywebsite.com/api/test/demo/123/456
   // https://www.mywebsite.com/api/test/demo/123
   // https://www.mywebsite.com/api/test/demo
   @Api\Route("/test/demo/{uid?}/{test?}")

In the above example, the routing will forward the request to your endpoint even if the ``{uid}`` URL path segment
is not passed. It becomes optional.


Accessing the parameters
~~~~~~~

When using the ``@Api\Route("/test/demo/{uid}/{test}")`` pattern, you can access the variables using one
of the following method:

- use the variable name as an **argument of your method**. Dependency Injection will take care of the rest

- use ``$this->request->getArguments()`` to get the values

.. code-block:: php

   <?php

   namespace My\Extension\Api;

   use Nng\Nnrestapi\Annotations as Api;
   use Nng\Nnrestapi\Api\AbstractApi;
   
   class Example extends AbstractApi
   {
      /**
      * @Api\Route("GET /test/route/{name}")
      * @Api\Access("public")
      * 
      * @return array
      */
      public function customRoutingTest( $name = null )
      {
         return ['message'=>"Hello, {$name}!"];
      }
   }

You can always use ``$this->request->getArguments()`` as an alternative:

.. code-block:: php

   <?php

   namespace My\Extension\Api;

   use Nng\Nnrestapi\Annotations as Api;
   use Nng\Nnrestapi\Api\AbstractApi;
   
   class Example extends AbstractApi
   {
      /**
       * @Api\Route("GET /test/route/{name}")
       * @Api\Access("public")
       * 
       * @return array
       */
      public function customRoutingTest()
      {
         $args = $this->request->getArguments();
         return ['message'=>"Hello, {$args['name']}!"];
      }
   }


Restrict routing to certain HTTP Request Methods
~~~~~~~

If not further specified in your ``@Api\Route`` annotation, **ALL** requests matching the Route-pattern will
resolve to your endpoint, no matter if they were sent using ``GET``, ``POST``, ``PUT`` or any other HTTP Request
Method.

You can limit the Routing to certain HTTP Request Methods with this pattern:

.. code-block:: php

   // listen to ALL requests (GET, POST, PUT, DELETE, PATCH)
   @Api\Route("/test/demo/something")

   // only listen to GET requests
   @Api\Route("GET /test/demo/something")

   // listen to GET, POST and PUT requests
   @Api\Route("GET|POST|PUT /test/demo/something")

   // listen to GET and parse URL parameters
   @Api\Route("GET /auth/log_me_out/{uid}/{something}")
