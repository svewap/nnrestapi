.. include:: ../Includes.txt

.. _annotations_access:

============
@Api\\Access
============

Restricting access to your endpoint
---------

The ``@Api\Access()`` annotation can be used to restrict the access to an endpoint to certain ...

* Frontend-Users (fe_users)
* Frontend-User-Groups (fe_user_groups)
* Api-Users (defined in the Extension Manager)
* Backend-Users or Admins
* IP-adresses

**The basic syntax is:**

.. code-block:: php

   @Api\Access("options")


**Full example:**

.. code-block:: php
   
   <?php

   namespace My\Extension\Api;

   use Nng\Nnrestapi\Annotations as Api;
   use Nng\Nnrestapi\Api\AbstractApi;
   
   /**
    * @Api\Endpoint()
    */
   class Example extends AbstractApi
   {
      /**
       * Only Frontend-Users will be able to access this endpoint
       *
       * @Api\Access("fe_users")
       * @return array
       */
      public function getIndexAction() 
      {
         return ['nice'=>'works!'];
      }
   }


Examples and details?
---------

Pleaser check out the section :ref:`"how to restrict access" <access>` for detailed information and examples.