.. include:: ../Includes.txt

.. _annotations_hidden:

============
@Api\\IncludeHidden
============

Enable retrieving of hidden records and relations from database.
---------

This makes an endpoint behave like the Typo3 Backend: Hidden records and records with ``fe_group`` 
or ``starttime/endtime``-restrictions will be returned to frontend although they usually would be
hidden in requests from the frontend.

The syntax is:

.. code-block:: php

   @Api\IncludeHidden

.. tip::

   If you are using frontend-user authentication, you can also set the option to include hidden records
   on a per-user base by setting the checkbox "Admin-Mode: Show hidden records" in the tab "RestApi" 
   of the frontend user entry.

Here is a full example:

.. code-block:: php

   <?php
   
   namespace My\Extension\Api;

   use Nng\Nnrestapi\Annotations as Api;

   class Example
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

   class Example
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
