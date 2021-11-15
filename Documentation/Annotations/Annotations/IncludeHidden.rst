.. include:: ../Includes.txt

.. _annotations:

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

   <?php

   use Nng\Nnrestapi\Annotations as Api;

   class Example
   {
      /**
       * @Api\IncludeHidden
       * @Api\Access("public")
       *
       * @return array
       */
      public function getAllAction() {
         return $this->someRepository->findAll();
      }

   }
