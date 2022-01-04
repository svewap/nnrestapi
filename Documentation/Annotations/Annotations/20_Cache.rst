.. include:: ../Includes.txt

.. _annotations_cache:

============
@Api\\Cache
============

Enable caching for a TYPO3 RestAPi endpoint
---------

If the method of an endpoint has the ``@Api\Cache`` annotation set, then its result
will be cached. The next time this endpoint is called, the result will be retrieved
from the cache without calling the method.

Useful, if static data should be loaded like settings, dropdown-values or country-lists etc.
The cache will only be cleared and rebuilt, if the "clear cache" button is clicked in the backend.

**The syntax is:**

.. code-block:: php

   @Api\Cache

**Here is a full example:**

.. code-block:: php

   <?php

   namespace My\Extension\Api;

   use Nng\Nnrestapi\Annotations as Api;
   use Nng\Nnrestapi\Api\AbstractApi;
   
   class Example extends AbstractApi
   {
      /**
       * @Api\Cache
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


Handling the cache yourself
---------

In case you would like to handle the caching of data yourself, the ``nnhelpers`` 
Cache-methods are very useful. Here is a basic example – have a look at the 
`nnhelpers documentation <https://docs.typo3.org/p/nng/nnhelpers/main/en-us/Helpers/Classes/cache.html>`__
for more info:

.. code-block:: php

   <?php

   namespace My\Extension\Api;
   
   use Nng\Nnrestapi\Annotations as Api;
   use Nng\Nnrestapi\Api\AbstractApi;
   
   class Example extends AbstractApi
   {
      /**
       * @Api\Access("public")
       *
       * @return array
       */
      public function getAllAction() 
      {
         $cacheIdentifier = 'example';

         if ($cache = \nn\t3::Cache()->get($cacheIdentifier)) {
            return $cache;
         }

         $result = $this->someComplicatedOperation();
         return \nn\t3::Cache()->set($cacheIdentifier, $result);
      }

   }