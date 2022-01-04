.. include:: ../Includes.txt

.. _annotations_json:

============
@Api\\Json
============

Control how your TYPO3 RestAPi renders the JSON result
---------

| Options and settings for converting the response-data to JSON.
Currently, only ``depth`` is implemented.

With ``depth`` you can control, how deep the returned object will be 
parsed when it is converted to the JSON-array. 

This is helpful, if you are returning Objects with many nested relations or recursions, 
but you only need the first few levels of the data in the frontend.

**The syntax is:**

.. code-block:: php

   @Api\Json(depth=4)

**Here is a full example:**

.. code-block:: php

   <?php
   
   namespace My\Extension\Api;

   use Nng\Nnrestapi\Annotations as Api;
   use Nng\Nnrestapi\Api\AbstractApi;
   
   class Example extends AbstractApi
   {
      /**
       * @Api\Json(depth=4)
       * @Api\Access("public")
       *
       * @return array
       */
      public function getAllAction() 
      {
         $result = $this->someVeryDeepObject();
         return $result;
      }

   }
