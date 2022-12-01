.. include:: ../Includes.txt

.. _annotations_endpoint:

============
@Api\\Endpoint
============

Mark a class as endpoint for the TYPO3 RestAPi
---------

There are two basic ways to register a Class as endpoint so the extension will route
requests to it:

-  by using the :ref:`\nn\rest::Endpoint()->register()<routing_prerequisites>` method in the 
   ``ext_localconf.php`` of your extension. This is useful to register all Classes in
   a certain namespace, e.g. ``My\Extension\Api\*``.

-  By using the ``@Api\Endpoint()`` Annotation in the DocComment of the individual Class as
   described in this chapter.

.. tip::

   **Only use one of both!**

   Note, that by registering the Class as an Endpoint using the ``@Api\Endpoint()`` annotation, there is no need to 
   use ``\nn\rest::Endpoint()->register()`` in the ``ext_localconf.php`` anymore – and vice versa. The nnrestapi 
   extension will automatically traverse through all classes of the extension folder and find classes with
   this annotation in the DocComment.  


Marking individual Classes as Endpoint
-----------

In the following example we will mark the class ``Example`` as an TYPO3 RestApi Endpoint by setting the 
``@Api\Endpoint()`` Annotation in the comment block above the class. 

Requests sent to ``https://www.yourwebsite.com/api/example/...`` will automatically be routed to 
this class. 

**Here is a full example**

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
      // Your methods
   }

The above endpoint can be reached over the URL:

.. code-block:: php

   https://www.mywebsite.com/api/example/...


Override the path segment / class name
-----------

By default, the first path segment after ``api/.../`` is identical with the class name of your endpoint.
If your class is named ``Example``, then you can route the requests to it by calling the URL ``api/example/``.

This can be overridden by setting a value in ``@Api\Endpoint("name")``.

In the following example, we would like to route requests to ``api/apples/...`` to the class ``Oranges``. 
This can be achieved by setting ``@Api\Endpoint("apples")``:

.. code-block:: php

   <?php

   namespace My\Extension\Api;

   use Nng\Nnrestapi\Annotations as Api;
   use Nng\Nnrestapi\Api\AbstractApi;
   
   /**
    * @Api\Endpoint("apples")
    */
   class Oranges extends AbstractApi
   {
      // Your methods
   }

The above endpoint can be reached over the URL:

.. code-block:: php

   https://www.mywebsite.com/api/apples/...

.. attention::

   **Getting an 404 - Endpoint not found?**

   If you are not able to connect to your Endpoint, here is a checklist of things you should try:

   -  **Check your Endpoint registration.** Make sure you are using one of the methods described
      :ref:`in this chapter <routing_prerequisites>`
   -  **Clear the cache.** Not only by clicking on the "red thunderbolt", but also by using the function
      "Flush TYPO3 and PHP Cache" in the backend-module "Admin -> Maintainance"
   -  **Rebuild the PHP Autoload Information.** In a non-composer-installation, this can in the 
      backend-module "Admin -> Maintainance". In a composer based installation this is done
      on the command line with ``composer dumpautoload``.
   -  **Check, if your extension has a composer.json in the root folder**. Since TYPO3 v11 it is 
      mandatory to register the path to your classes with a ``composer.json``. Grab one from an
      other extension or the :ref:`nnrestapi Kickstarter<kickstarter>`.
   -  **Check, if your extension has a Service.yaml**. Since TYPO3 v12 you will probably also need
      a ``Configuration/Service.yaml`` that registers the Classes. Again, steal it from an other
      extension or the :ref:`nnrestapi Kickstarter<kickstarter>`.