.. include:: ../Includes.txt

.. _quickstart:

============
Quick Start
============

Up and running in 5 minutes
-------------------------------

.. rst-class:: bignums

1. Install the nnrestapi extension

   Follow the instructions under :ref:`installation` to install the ``nnrestapi`` extension.

2. Create an own extension

   To implement your own endpoints, you will need to create a new extension - or use one of your existing extensions.
   
   Define the dependencies to the ``nnrestapi`` extension in your extension.
   This is important, so TYPO3 loads your extension **after** the ``nnrestapi`` extension.

   This goes in the ``ext_emconf.php`` of your extension:

   .. code-block:: php

      $EM_CONF[$_EXTKEY] = [
         ...
         'constraints' => [
            'depends' => [
               'nnrestapi' => '1.1.0-0.0.0',
            ],
         ],
      ];

   In case your installation is running in composer-mode, this must be added to the ``composer.json`` of your extension.

   .. code-block:: json

      {
         ...
         "require": {
            "nng/nnrestapi": "^1.1"
         },
      }

3. Create your first endpoint

   Create a file located at ``Classes/Api/Demo.php`` in your extension with this code.

   **Important**: Note that the comments with ``@Api\Endpoint()`` and ``@Api\Access()`` are not just comments!
   They actually register your class as an Endpoint and define, who is allowed to access your method.

   .. code-block:: php

      <?php   
      namespace My\Extension\Api;

      use Nng\Nnrestapi\Annotations as Api;

      /**
       * @Api\Endpoint()
       */
      class Demo extends \Nng\Nnrestapi\Api\AbstractApi {

         /**
          * @Api\Access("public")
          * @return array
          */
         public function getExampleAction()
         {
            return ['great'=>'it works!'];
         }
      }


4. Clear the cache!

   Click on the "clear cache" icon in the backend. This will make sure, that ``nnrestapi`` rebuilds the cache file and includes 
   your classes and endpoints. 
   
   We had a focus on performance and caching, so get used to having to do this whenever you add new endpoints or make changes 
   to the ``@Api``-annotations of a method.

5. Call your endpoint

   Enter the URL ``https://www.yourdomain.com/api/demo/example`` to see the result!
