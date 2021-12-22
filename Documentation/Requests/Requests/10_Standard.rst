.. include:: ../Includes.txt

.. _routing_standard:

============
Routing by method-name
============

The class- and method-name are the key!
------------

The ``nnrestapi`` has a standardized way to route a request to a controller and method.

Let's look at the following two URL examples: 

.. code-block:: php

   https://www.mywebsite.com/api/article/all
   https://www.mywebsite.com/api/article/1

**The URLs parts are:**

- every URL is prefixed with ``api`` as first part of the path. This is the default setting for 
  every Api. It can be changed in the configuration YAML. In TYPO3 this is important so the
  RouteEnhancer can kick in.

- the second part of the URL is the Classname of your Controller. 

  **Example:** The URL ``/api/article/`` will be routed to your ``Article`` class.

- if the third part is a string, it will look for a method in your class with that name that is 
  prefixed with the Request Type and suffixed by the word ``Action``. 

  **Example:** If you are sending a ``GET`` Request to ``/api/article/all``, the method ``Article->getAllAction()`` 
  will be called. 

- if the third part is an integer, it will look for the ``indexAction`` in your class, prefixed by
  the Request method. 
  
  **Example:** Sending a ``POST`` request to ``/api/article/1`` would call the 
  method ``Article->postIndexAction()``. The ``1`` will automatically be passed as ``uid`` in 
  the request arguments. 

The following table illustrates the basic principles:

+------------+-------------------------------------+----------------------------------------------------+
| Method     | URL Example                         | ...will route by default to:                       |
+============+=====================================+====================================================+
| ``GET``    | ``/api/article/1``                  | ``My\Extension\Api\Article->getIndexAction()``     |
+------------+-------------------------------------+----------------------------------------------------+
| ``GET``    | ``/api/article/all``                | ``My\Extension\Api\Article->getAllAction()``       |
+------------+-------------------------------------+----------------------------------------------------+
| ``GET``    | ``/api/article/page/1``             | ``My\Extension\Api\Article->getPageAction()``      |
+------------+-------------------------------------+----------------------------------------------------+
| ``PUT``    | ``/api/article/1``                  | ``My\Extension\Api\Article->putIndexAction()``     |
+------------+-------------------------------------+----------------------------------------------------+
| ``PATCH``  | ``/api/article/1``                  | ``My\Extension\Api\Article->patchIndexAction()``   |
+------------+-------------------------------------+----------------------------------------------------+
| ``DELETE`` | ``/api/article/1``                  | ``My\Extension\Api\Article->deleteIndexAction()``  |
+------------+-------------------------------------+----------------------------------------------------+
| ``POST``   | ``/api/article``                    | ``My\Extension\Api\Article->postIndexAction()``    |
+------------+-------------------------------------+----------------------------------------------------+
| ``POST``   | ``/api/article/image``              | ``My\Extension\Api\Article->postImageAction()``    |
+------------+-------------------------------------+----------------------------------------------------+
| ``POST``   | ``/api/article/image/1/2/3``        | ``My\Extension\Api\Article->postImageAction()``    |
+------------+-------------------------------------+----------------------------------------------------+

.. hint::

   If you want to use **custom routes** that don't follow this standard pattern, you can always define 
   them with the ``@Api\Route()`` annotation in the comment of your method. 
   
   Find out more here: :ref:`custom_routing`.
