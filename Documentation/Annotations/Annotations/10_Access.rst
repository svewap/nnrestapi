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

The basic syntax is:

.. code-block:: php
   
   namespace My\Extension\Api;
   
   use Nng\Nnrestapi\Annotations as Api;

   ... 
   
   /**
    * @Api\Access("options")
    */

Check out the :ref:`@Api\\Access(...)<access>` section of this documentation that has detailed 
information and several examples.