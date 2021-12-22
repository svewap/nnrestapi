.. include:: ../Includes.txt

.. _responses_simple:

============
Simple Responses
============

Returning an Array
~~~~~~~~~~

The most basic return value is an array:

.. code-block:: php

   <?php   
   namespace My\Extension\Api;

   use Nng\Nnrestapi\Annotations as Api;

   class Test extends \Nng\Nnrestapi\Api\AbstractApi {

      /**
       * @Api\Access("public")
       * 
       * @return array
       */
      public function getExampleAction()
      {
         return ['result'=>'welcome!'];
      }
   }

If you open the URL ``https://www.youwebsite.com/api/test/example`` in your browser,
you will see this JSON response:

.. code-block:: json

   {"result":"welcome!"}

Returning a Domain Model
~~~~~~~~~~

You can also return a Model as response from your method:

.. code-block:: php

   public function getExampleAction()
   {
      $model = $this->exampleRepository->findByUid( 123 );
      return $model;
   }

In the result the model will be automatically converted to an array. Also notice that
even relations like ``SysFileReferences`` get converted.

.. code-block:: json

   {"uid":123, "title":"nice!", "image":{"publicUrl":"path/to/some/image.jpg"}}

Returning an ObjectStorage
~~~~~~~~~~

If you return an ``ObjectStorage``, e.g. with multiple Domain Models - or SysFileReferences,
the ``ObjectStorage`` will automatically be converted to an array:

.. code-block:: php

   public function getExampleAction()
   {
      $allExamples = $this->exampleRepository->findAll();
      return $allExamples;
   }

The result will be an array of Objects:

.. code-block:: json

   [
      {"uid":1, "title":"One", "image":{"publicUrl":"one.jpg"}},
      {"uid":2, "title":"Two", "image":null},
      {"uid":3, "title":"Three", "image":{"publicUrl":"three.jpg"}}
   ]   

Example: Get list of all countries
~~~~~~~~~~

Here are a few examples using the ``EXT:nnhelpers`` which can make life a lot easier
when fetching data from the database.

.. code-block:: php

   /**
    * Get list of all static countries from the database
    *
    * @Api\Access("public")
    * @return array
    */
   public function getCountriesAction()
   {
      $countries = \nn\t3::Environment()->getCountries();
      return $countries;
   }

Example: Return TypoScript-Setup
~~~~~~~~~~

.. code-block:: php

   /**
    * Get TypoScript settings for given plugin
    *
    * @Api\Access("public")
    * @return array
    */
   public function getSettingsAction()
   {
      $settings = \nn\t3::Settings()->get('myextname');
      return $settings;
   }

Example: Returning MANY rows of data
~~~~~~~~~~

Sometimes – especially when retrieving many Object from a database table, the standard ``DataMapper`` of
TYPO3 can be extremely slow. If there is no need to fetch relations, but simple get the raw data array
from the database, this recipe will speed things up:

.. code-block:: php

   /**
    * Fastest way to get massive amount of data from database and return it as JSON
    *
    * @Api\Access("public")
    * @return array
    */
   public function getBiglistAction()
   {
      $rows = \nn\t3::Db()->findAll('tx_myext_domain_model_example');
      return $rows;
   }
