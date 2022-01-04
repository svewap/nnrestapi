.. include:: ../Includes.txt

.. _annotations_hidden:

============
@Api\\IncludeHidden
============

Retrieve hidden records and relations from the database.
---------

This makes the TYPO3 Frontend behave like the Typo3 Backend: Hidden records and records with ``fe_group`` 
or ``starttime/endtime``-restrictions will be returned to the frontend, although they usually would only
be visible in the TYPO3 backend for admins.

**The syntax is:**

.. code-block:: php

   @Api\IncludeHidden()

.. tip::

   If you are using frontend-user authentication, you can also set the option to include hidden records
   on a per-user basis by setting the checkbox :ref:`"Admin-Mode" <configuration_feuser>` in the tab "RestApi" 
   of the frontend user entry.

**Here is a full example:**

.. code-block:: php

   <?php
   
   namespace My\Extension\Api;

   use Nng\Nnrestapi\Annotations as Api;
   use Nng\Nnrestapi\Api\AbstractApi;
   
   class Example extends AbstractApi
   {
      /**
       * @Api\IncludeHidden
       * @Api\Access("public")
       *
       * @return array
       */
      public function getAllAction() 
      {
         return $this->someRepository->findAll();
      }

   }


If you would like to handle the access to hidden records yourself, you can use
the ``\nn\rest::Settings()->setIgnoreEnableFields()`` helper before retrieving
your data from the repository.
 
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
         if ($this->yourOwnCheckMethod()) {
            \nn\rest::Settings()->setIgnoreEnableFields( true );
         }
         return $this->someRepository->findAll();
      }

   }
