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
* Backend-Users or Admins
* IP-adresses

**The basic syntax is:**

.. code-block:: php
   
   namespace My\Extension\Api;
   
   use Nng\Nnrestapi\Annotations as Api;

   ... 
   
   /**
    * @Api\Access("options")
    */


Examples and details?
---------

Pleaser check out the section :ref:`"how to restrict access" <access>` for detailed information and examples.