.. include:: ../Includes.txt

.. _annotations_upload:

============
@Api\\Upload
============

Control where files are uploaded to in your TYPO3 RestAPi
---------

If the method of an endpoint has the ``@Api\Cache`` annotion set, then its result
will be cached. The next time this endpoint is called, the result will be retrieved
from the cache without calling the method.

Useful, if static data should be loaded like settings, dropdown-values or country-lists etc.
The cache will only be cleared and rebuilt, if the "clear cache" button is clicked in the backend.

The syntax is:

.. code-block:: php

   @Api\Upload("default")

It is defined above the endpoint of your method:

.. code-block:: php

   <?php
   
   namespace My\Extension\Api;

   use Nng\Nnrestapi\Annotations as Api;

   class Example
   {
      /**
       * @Api\Upload("default")
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

Where ``name`` is the key to the configuration in TypoScript settings. Let`s have a look at the
option for ``plugin.tx_nnrestapi.settings.fileUploads``:

.. code-block:: typoscript

   plugin.tx_nnrestapi.settings.fileUploads {

      # Use this key in your endpoint annotation "@api\upload default"
      default {

         // if nothing else fits, use fileadmin/api/
         defaultStoragePath = 1:/api/

         // Optional: Use a custom class to return configuration
         //pathFinderClass = Nng\Nnrestapi\Helper\UploadPathHelper::getUserUidPath

         // target-path for file, file-0, file-1, ...
         file = 1:/api/tests/
      }		
   }

Make sure the upload-folder exists and has the correct rights for reading/writing.


Custom method for resolving the upload path
~~~~~~~~~~

You can define a custom class that resolves the upload-path for each individual file by 
setting ``pathFinderClass`` and creating your own Helper-class. Have a look at the
``Nng\Nnrestapi\Helper\UploadPathHelper`` for detailles examples.

Let's create an UploadPathHelper that uploads the files to a folder-structure depending
on the current month and date. You probably have seen this structure in WordPress.

The Helper will return a configuration array which has the same keys and structure
that the TypoScript setup uses. You can keep things simple and just return the
key ``defaultStoragePath`` which will upload all fileUploads to the same location, 
independant of their fileKey/name in the POST-data:

.. code-block:: php

   return ['defaultStoragePath'=>'1:/my/custom/path']

And/or you can return a path for the individual fileKeys:

.. code-block:: php

   return ['file'=>'1:/files/', 'image-0':'1:/images/', ...];

Here is a full example for a UploadPathHelper that returns a combined identifier folder
name in the style of ``1:/api/YYYY-MM``

.. code-block:: php

   <?php
   
   namespace My\Extension\Helper;

   class UploadPathHelper
   {
      /**
       * Return upload-path based on current date (e.g. `1:/api/2021-12/`)
       * 
       * @return array
       */
      public static function getPathForDate( $request = null, $settings = null ) 
      {
         return [
            'defaultStoragePath' => '1:/api/' . date('Y-m') . '/'
         ];
      }

   }

Next, create a **TypoScript setting** that points to your helper-method:

.. code-block:: typoscript

   plugin.tx_nnrestapi.settings.fileUploads {
      monthdate {
         pathFinderClass = My\Extension\Helper\UploadPathHelper::getPathForDate
      }		
   }

Last step: Use the key ``monthdate`` in the ``@Api\Upload("monthdate")`` annotation of your endpoint:

.. code-block:: php

   <?php

   namespace My\Extension\Api;

   use Nng\Nnrestapi\Annotations as Api;

   class Example
   {
      /**
       * @Api\Upload("monthdate")
       * @Api\Access("public")
       *
       * @param \My\Extension\Domain\Model\ApiTest $apiTest
       * @return array
       */
      public function getAllAction( $apiTest = null ) 
      {
         return $apiTest;
      }

   }

Check out the :ref:`File uploads(...)<fileupload>` section of this documentation for more 
information and examples.
