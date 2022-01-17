.. include:: ../Includes.txt

.. _access_checkaccess:

============
Restricting access by sending a 403 response
============

How to respond with a 403 - Forbidden from inside your method
---------

If for some reason using the :ref:`@Api\\Access(...)<access>` annotation or implementing a custom
:ref:`checkAccess(...)<access_checkaccess>`-method are not sufficient, you can always use 
``return $this->response->unauthorized()`` to abort the further processing inside your TYPO3 Rest Api 
endpoint and send a ``HTTP 403 Forbidden`` response to the frontend.

Here is an example:

.. code-block:: php

   <?php   
   namespace My\Extension\Api;
   
   use Nng\Nnrestapi\Annotations as Api;
   use Nng\Nnrestapi\Api\AbstractApi;
   
   /**
    * @Api\Endpoint()
    */
   class Test extends AbstractApi {

      /**
       * @Api\Access("public")
       * @return array
       */
      public function getExampleAction()
      {
         // Only allow access on Fridays
         if (date('w') != 4) {
            return $this->response->unauthorized("Not today, my dear. I've got a headache.");
         }
         return ['result'=>'welcome!'];
      }
   }

