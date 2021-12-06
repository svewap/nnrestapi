.. include:: ../Includes.txt

.. _routing_example:

============
Example
============

Creating a TYPO3 Restful Api for articles
------------

In the following example we would like to create an endpoint that can read, update, insert and
delete articles in a news-system.

All operations can be executed by calling the TYPO3 Rest Api endpoint located at  
``https://www.mysite.com/api/article``. Here is an overview of what we are planning to do:

+------------+---------------------+-------------------------------------+--------------------------------------------------+
| Method     | URL                 | Request body / payload              | operation                                        |
+============+=====================+=====================================+==================================================+
| ``GET``    | ``/api/article/1``  | (none)                              | Get entry with uid [1] from database             |
+------------+---------------------+-------------------------------------+--------------------------------------------------+
| ``PUT``    | ``/api/article/1``  | {"title":"News", "text":"nice"}     | Update full entry with uid [1] in database       |
+------------+---------------------+-------------------------------------+--------------------------------------------------+
| ``DELETE`` | ``/api/article/1``  | (none)                              | Delete entry with uid [1] in database            |
+------------+---------------------+-------------------------------------+--------------------------------------------------+
| ``POST``   | ``/api/article``    | {"title":"News", "text":"read it"}  | Insert a new entry in database                   |
+------------+---------------------+-------------------------------------+--------------------------------------------------+

**The URLs above have 3 parts:**

- every URL is prefixed with ``api`` as first part of the path. This is the default setting for 
  every Api. It can be changed in the configuration YAML.
- the second part of the URL is ``article``. This is the name of your custom Api-Class in lowercase.
- the third part is the ``uid`` of the entry to get, update or delete. It is not set in the ``POST``
  request, because here we want to insert a new article 



Step-by-step
----------

Creating the class
~~~~~~~~~~

Let's start by creating the ``Article`` class. Your Api-classes can be located anywhere inside of
the ``Classes`` folder of your extension. We would recommend placing them in the folder 
``Classes/Api/...``.

Every Api Class should extend ``Nng\Nnrestapi\Api\AbstractApi``. You can also reference the 
``Nng\Nnrestapi\Annotations`` as ``Api`` because you will be using these Annotations to define
access-rights and other things later. 

**Here is what you need to get started:**

.. code-block:: php

   <?php   
   namespace My\Extension\Api;

   use Nng\Nnrestapi\Annotations as Api;
   use Nng\Nnrestapi\Api\AbstractApi;

   class Article extends AbstractApi {   
   }


.. code-block:: php

   <?php   
   namespace My\Extension\Api;

   use Nng\Nnrestapi\Annotations as Api;
   use Nng\Nnrestapi\Api\AbstractApi;

   class Article extends AbstractApi {

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
