.. include:: ../Includes.txt

.. _annotations_upload:

============
@Api\\Upload
============

Control where files are uploaded to in your TYPO3 RestAPi
---------

With the ``@Api\Upload(...)`` annotation you can control, where the file uploads of a multipart/form-data request
are moved to.

The syntax is:

.. code-block:: php

   @Api\Upload( option )

Where ``option`` can have one of the following expressions to either define a direct file path, a custom Class to 
return the upload-path or the key to a configuration in the TypoScript setup: 

+--------------------------------------------------------+----------------------------------------------------------------+
| syntax                                                 | description                                                    |
+========================================================+================================================================+
| ``@Api\Upload(FALSE)``                                 | Explicitly **disables** the file-upload. Any file attached to  |
|                                                        | the request will be discarded and removed from the JSON        |
|                                                        | without further processing or parsing.                         |
|                                                        | This is the **default behavior** to prevent unwanted          |
|                                                        | file-uploads to the fileadmin.                                 |
+--------------------------------------------------------+----------------------------------------------------------------+
| ``@Api\Upload("1:/path/to/upload/folder")``            | The **file path** to the folder in the combined identifier      |
|                                                        | syntax (by default, ``1:/`` would be interpreted as            |
|                                                        | the default storage ``fileadmin/``)                            |
+--------------------------------------------------------+----------------------------------------------------------------+
| ``@Api\Upload("config[name]")``                        | Use a **predefined configuration** defined in the TypoScript   |
|                                                        | setup at ``plugin.tx_nnrestapi.settings.fileUploads.[name]``   |
+--------------------------------------------------------+----------------------------------------------------------------+
| ``@Api\Upload("config[default]")``                     | Uses the **default settings** from the TypoScript setup.       |
|                                                        | If set to ``default``, the files will be uploaded to the path  |
|                                                        | ``fileadmin/api/``                                             |
+--------------------------------------------------------+----------------------------------------------------------------+
| ``@Api\Upload(\My\Extname\UploadProcessor::class)``    | Use a **custom class** to return the upload-path for the       |
|                                                        | files. The class must have a method called ``getUploadPath``   |
|                                                        | and return an array as described                               |
|                                                        | :ref:`here <annotations_upload_custom>`                        |
+--------------------------------------------------------+----------------------------------------------------------------+

.. important::

   Note that ``@Api\Upload(...)`` **must explicitly be set** as an Annotation on the endpoint - otherwise the nnrestapi 
   will ignore any file upload passed during the request. This is to prevent uncontrolled uploads and misuse of the Api. 


The Annotation is placed in the comment block above your method / endpoint:

.. code-block:: php

   <?php
   
   namespace My\Extension\Api;

   use Nng\Nnrestapi\Annotations as Api;
   use Nng\Nnrestapi\Api\AbstractApi;
   
   class Example extends AbstractApi
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

Let’s have a look at the configuration in TypoScript setup for ``plugin.tx_nnrestapi.settings.fileUploads``:

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


.. _annotations_upload_custom:

Custom method for resolving the upload path
~~~~~~~~~~

You can define a custom class that resolves the upload-path for each individual file.
This can either be done by ... 

-  Setting the class name in the **Annotation itself** like this:

   .. code-block:: php

      // will call \My\Extname\UploadProcessor->getUploadPath()
      @Api\Upload( \My\Extname\UploadProcessor::class )

   In this case, the nnrestapi will automatically try to call the method ``getUploadPath()`` of
   your class and will expect an array as return value. Refer to the examples below to see, which
   values need to be returned in the array.

-  Creating a configuration in the **TypoScript setup** at ``plugin.tx_nnrestapi.settings.fileUploads.[name].pathFinderClass``. 
   In this case, you can also set the method name to call:

   .. code-block:: typoscript

      plugin.tx_nnrestapi.settings.fileUploads {
         myconf {
            pathFinderClass = My\Extension\Helper\UploadPathHelper::getPathForDate
         }
      }

   Then use the configuration name in your Annotations like this:

   .. code-block:: php

      @Api\Upload("config[myconf]")


.. tip::

   Have a look at the ``Nng\Nnrestapi\Helper\UploadPathHelper`` for detailled examples.


.. _upload_custom_pathresolver:

Example of custom path resolvers
~~~~~~~~~~

Let's create an UploadPathHelper that uploads the files to a folder-structure depending
on the current month and date. You probably have seen this structure in WordPress.

The Helper will return a configuration array which has the same keys and structure
that the TypoScript setup uses. You can keep things simple and just return the
key ``defaultStoragePath`` which will upload all fileUploads to the same location, 
independent of their fileKey/name in the POST-data:

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
   use Nng\Nnrestapi\Api\AbstractApi;
   
   class Example extends AbstractApi
   {
      /**
       * @Api\Upload("config[monthdate]")
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
