.. include:: ../Includes.txt

.. _configuration_yaml:

============
YAML Configuration
============

Configuring with TypoScript Setup
-------------------------------

The extension comes with the following default-settings in the YAML-configuration:

.. code-block:: yaml

    nnrestapi:
     payloadKey: 'json'
     routing:
       basePath: '/api'
     routeEnhancers:
       Nnrestapi:
         type: NnrestapiEnhancer

To include these default settings, you must import the YAML from nnrestapi in your site configuration
like described in the :ref:`installation guide <installation>`:

.. code-block:: yaml

     # Insert this at the end of your site config.yaml 
     imports:
       -
         resource: 'EXT:nnrestapi/Configuration/Yaml/default.yaml'

The following section describes the individual options:

nnrestapi.payloadKey
""""""""""""""
.. container:: table-row

   Property
        nnrestapi.payloadKey
   Data type
        string
   Description
        If you are using multipart/form-data to pass file-attachments **and** JSON-data simultaneously, you will need 
        to move the JSON-data to a own variable like described :ref:`in this chapter <examples_fileuploads_multipart>`.

        If you would like to use a different variable than ``json`` for this, you can override the ``payloadKey`` in 
        the settings: 

        ::

          nnrestapi:
            # use variable 'payload' instead of 'json'
            payloadKey: 'payload'

   Default
        'json'

routing.basePath
""""""""""""""
.. container:: table-row

   Property
        nnrestapi.basePath
   Data type
        string
   Description
        Defines, which base-path is used for the api.

        Everything behind this path will be routed to an endpoint and method of the api.
        Make sure, this path is unique and doesn't conflict with page paths defined in the backend.

        ::

          nnrestapi:
            # use path '/rest' instead of '/api'
            basePath: '/rest'

   Default
        '/api'

routeEnhancers.Nnrestapi.type
""""""""""""""
.. container:: table-row

   Property
        routeEnhancers.Nnrestapi.type
   Data type
        string
   Description
        Under the hood, nnrestapi uses a standard TYPO3 Route Enhancer to map the request to an endpoint.
        Nothing special about this line - and nothing you need to modify.

   Default
        'NnrestapiEnhancer'