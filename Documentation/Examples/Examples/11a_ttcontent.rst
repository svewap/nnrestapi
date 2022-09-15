.. include:: ../Includes.txt

.. _examples_ttcontent:

============
Creating Content-Elements (tt_content)
============

How to create or modify a TYPO3 content-element with your RESTful Api
------------

You might have the crazy idea to replace parts of the TYPO3 backend with a custom interface so your user
can create or modify the standard TYPO3 content-elements using your own frontend application.

Everything you need for retrieving and modifying the fields of the ``tt_content``-table including FAL (FileReferences)
for the fields ``image``, ``media`` and ``assets`` are implemented and ready to use.

The only thing that might be new and confusing: You will need a Domain-Model and Repository to create an object that
can be modified with getters and setters. For some reason, TYPO3 has not implemented a Model for ContentElements from 
the ``tt_content``-table yet (or we couldn't find it ;) - so we will need to do it ourselves:

Step-by-step
----------

.. rst-class:: bignums

1. Creating the Content-Model

   Inside of your own extension, let's start by defining the ``Content`` model. 
   
   Create a file inside of your extension: ``Classes/Domain/Model/TtContent.php`` and add the following
   code. Make sure to replace the namespace with your vendor- and extension-name.

   .. code-block:: php

      <?php
      // Classes/Domain/Model/TtContent.php

      namespace My\Extension\Domain\Model;

      use \TYPO3\CMS\Extbase\Domain\Model\FileReference;
      use \Nng\Nnrestapi\Domain\Model\AbstractRestApiModel;

      /**
      * A simple Model to test things with.
      * 
      */
      class TtContent extends AbstractRestApiModel
      {
         /**
          * @var string
          */
         protected $cType = 'textmedia';
         
         /**
          * @var int
          */
         protected $colPos = 0;

         /**
          * @var string
          */
         protected $header;

         /**
          * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
          */
         protected $assets;

         /**
          * constructor
          * 
          */
         public function __construct() {
            $this->initStorageObjects();
         }
         
         /**
          * Initializes all \TYPO3\CMS\Extbase\Persistence\ObjectStorage properties.
          *
          * @return void
          */
         protected function initStorageObjects() {
            $this->assets = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
         }

         /**
          * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
          */
         public function getAssets() {
            return $this->assets;
         }
         
         /**
          * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $assets
          * @return self
          */
         public function setAssets(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $assets) {
            $this->assets = $assets;
            return $this;
         }

         /**
          * @return  string
          */
         public function getCType() {
            return $this->cType;
         }

         /**
          * @param   string  $cType  
          * @return  self
          */
         public function setCType($cType) {
            $this->cType = $cType;
            return $this;
         }

         /**
          * @return  int
          */
         public function getColPos() {
            return $this->colPos;
         }

         /**
          * @param   int  $colPos  
          * @return  self
          */
         public function setColPos($colPos) {
            $this->colPos = $colPos;
            return $this;
         }

         /**
          * @return  string
          */
         public function getHeader() {
            return $this->header;
         }

         /**
          * @param   string  $header  
          * @return  self
          */
         public function setHeader($header) {
            $this->header = $header;
            return $this;
         }
      }



2. Create the Repository

   Next, let's create the Repository to handle our ``TtContent``-Model.

   This file is located at ``Classes/Domain/Repository/TtContentRepository.php``:

   .. code-block:: php

      <?php
      // Classes/Domain/Repository/TtContentRepository.php

      namespace My\Extension\Domain\Repository;

      class TtContentRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {}


3. Link your new Model to the ``tt_content`` table

   TYPO3 needs to understand, that our ``TtContent``-Model is not being stored in a new table, but
   is actually linked to the ``tt_content``-table.

   Create a file in your extension: ``Configuration/Extbase/Persistence/Classes.php``
   with this content:

   .. code-block:: php

      <?php
      // Configuration/Extbase/Persistence/Classes.php

      return [
         \My\Extension\Domain\Model\TtContent::class => [
            'tableName' => 'tt_content',
            'properties' => [
                  'cType' => [
                     'fieldName' => 'CType'
                  ],
            ],
         ],
      ];

4. Define where the files should be uploaded to

   In the TypoScript-setup of your extension, define a defaultStoragePath to use for file-uploads.
   
   This setting will be referred to in the endpoint to create new content-elements 
   using the Annotation ``@Api\Upload("config[apidemo]")``

   .. code-block:: typoscript

      plugin.tx_nnrestapi {
         settings {
            
            # where to upload new files. Use @Api\Upload("config[apidemo]")
            fileUploads {
               apidemo {
                  defaultStoragePath = 1:/apidemo/
               }		
            }

         }
      }

5. Create an endpoint

   Next we will need to define an REST-Api endpoint to handle the ``GET`` and ``POST`` requests.
   
   Here is the file located at ``Classes/Api/Content.php``

   .. code-block:: php

      <?php
      // Classes/Api/Content.php

      namespace My\Extension\Api;

      use My\Extension\Domain\Repository\TtContentRepository;
      use My\Extension\Domain\Model\TtContent as TtContent;

      use Nng\Nnrestapi\Annotations as Api;

      /**
      * This annotation registers this class as an Endpoint!
      *  
      * @Api\Endpoint()
      */
      class Content extends \Nng\Nnrestapi\Api\AbstractApi 
      {
         /**
          * @var TtContentRepository
          */
         private $ttContentRepository = null;

         /**
          * Constructor
          * Inject the TtContentRepository. 
          * Ignore storagePid.
          * 
          * @return void
          */
         public function __construct() 
         {
            $this->ttContentRepository = \nn\t3::injectClass( TtContentRepository::class );
            \nn\t3::Db()->ignoreEnableFields( $this->ttContentRepository );
         }

         /**
          * # Retrieve an existing Content-Element
          * 
          * Send a simple GET request to retrieve a content-element by its uid from the database.
          * 
          * Replace `{uid}` with the uid of the Entry:
          * ```
          * https://www.mysite.com/api/content/{uid}
          * ```
          * 
          * @Api\Access("public")
          * @Api\Localize()
          * @Api\Label("/api/content/{uid}")
          * 
          * @param TtContent $entry
          * @param int $uid
          * @return array
          */
         public function getIndexAction( TtContent $ttContent = null, int $uid = null )
         {
            if (!$uid) {
               return $this->response->notFound("No uid passed in URL. Send the request with `api/content/{uid}`");
            }
            if (!$ttContent) {
               return $this->response->notFound("Content-Element with uid [{$uid}] was not found.");
            }
            return $ttContent;
         }

         /**
          * # Create a new Content-Element
          * 
          * Send a POST request to this endpoint including a JSON to create a
          * new ContentElement in the tt_content-table. You can also upload file(s).
          * 
          * You __must be logged in__ as a frontend OR backend user to access 
          * this endpoint.
          * 
          * @Api\Access("be_users,fe_users")
          * @Api\Upload("config[apidemo]")
          * @Api\Example("{'pid':1, 'colPos':0, 'header':'Test', 'assets':['UPLOAD:/file-0']}");
          * 
          * @param TtContent $ttContentElement
          * @return array
          */
         public function postIndexAction( TtContent $ttContentElement = null )
         {			
            \nn\t3::Db()->save( $ttContentElement );
            return $ttContentElement;
         }

      }

6. Test it!

   Clear the TYPO3 cache (lightning-button) and use the RestApi backend module to test it!