.. include:: ../Includes.txt

.. _routing_prerequisites:

============
Registering Endpoints
============

.. important::

    **Define the depencies!** 

    Make sure, that you have defined a dependency of your own extension to ``nnrestapi`` - otherwise your
    extension might be loaded **before** the RestApi Extension which would lead to an Error.

    Refer to the :ref:`Quickstart chapter<quickstart>` to see how this can be done.



Preparing a class to be used as an TYPO3 RestAPI Endpoint
------------

To make sure that the TYPO3 RestApi "knows" it can route a request to your class and method, you need to register
the class.

**There are two alternative ways this can be done:**

-   On a **per-class base** using the ``@Api\Endpoint()`` Annotation – or
-   **globally for a complete namespace** using ``\nn\rest::Endpoint()->register()`` in 
    the ``ext_localconf.php`` of your extension.

You should decide using **one of both**, depending on your individual architecture.

.. rst-class:: bignums

1.  Alternative 1: Registering an Endpoint using Annotations

    Simply add the ``@Api\Endpoint()`` Annotation to the comment block above your class. The ``nnrestapi`` will automatically
    parse the DocComment and register your endpoint.

    You can find out more on :ref:`this page <annotations_endpoint>`.

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

2.  Alternative 2: Global registry of a namespace

    In your ``ext_localconf.php`` you can let ``nnrestapi`` automatically register all endpoints in a certain namespace.

    **Example:** If all of your Endpoints are in the namespace ``My\Extension\Api\*`` then you could have them all automatically
    registered by adding this code to your ``ext_localconf.php``.

    .. code-block:: php

        // Register path to my endpoints		
        \nn\rest::Endpoint()->register([
            'namespace' => 'My\Extension\Api'
        ]);

    By registering a global namespace for all your endpoints you can now dismiss the ``@Api\Endpoint()`` Annotation
    in the DocComment of your class:

    .. code-block:: php

        <?php

        namespace My\Extension\Api;

        use Nng\Nnrestapi\Annotations as Api;
        use Nng\Nnrestapi\Api\AbstractApi;
        
        /**
         * Nothing needed here :)
         */
        class Example extends AbstractApi
        {
          // Your methods
        }

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