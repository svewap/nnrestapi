.. include:: ../Includes.txt

.. _quickstart:

============
Quick Start
============

Get your TYPO3 Rest Api up and running in 5 minutes
-------------------------------

.. rst-class:: bignums

1. Install the nnrestapi extension

   Follow the instructions under :ref:`installation` to install the `nnrestapi` extension.

2. Create an own extension for your TYPO3 REST Api endpoints

   To implement your own endpoints you will need to create a new extension - or use one of your existing extensions.
   
   Define the dependencies to the `nnrestapi` extension in your extension.
   This is important so TYPO3 loads your extension **after** the `nnrestapi` extension.

   This goes in the `ext_emconf.php` of your extension:

   .. code-block:: php

      $EM_CONF[$_EXTKEY] = [
         ...
         'constraints' => [
            'depends' => [
               'nnrestapi' => '1.0.0-0.0.0',
            ],
         ],
      ];

   In case your installation is running in composer-mode, this must be added to the `composer.json` of your extension.

   .. code-block:: json

      {
         ...
         "require": {
            "nng/nnrestapi": "^1.0"
         },
      }

3. Create your first TYPO3 REST Api endpoint

   Create a file located at `Classes/Api/Demo.php` in your extension with this code:

   .. code-block:: php

      <?php   
      namespace My\Extension\Api;

      use Nng\Nnrestapi\Annotations as Api;

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


4. Register your TYPO3 REST Api-Classes

   In your `ext_localconf.php` you can now let `nnrestapi` automatically register all endpoints in your namespace `My\Extension\Api\*`.

   .. code-block:: php

      // Register path to my endpoints		
      \nn\rest::Endpoint()->register([
         'namespace' => 'My\Extension\Api'
      ]);

5. Clear the cache!

   Click on the "clear cache" icon in the backend. This will make sure, that `nnrestapi` rebuilds the cache file and includes 
   your classes and endpoints. 
   
   We had a focus on performance and caching, so get used to having to do this whenever you add new endpoints or make changes 
   to the `@Api`-annotations of a method.

6. Call your endpoint

   Enter the URL `https://www.yourdomain.com/api/demo/example` to see the result!

