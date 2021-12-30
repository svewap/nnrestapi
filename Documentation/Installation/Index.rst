.. include:: ../Includes.txt

.. _installation:

============
Installation
============

High speed walk-through: Installing the TYPO3 Rest Api.
------------

| For people, who know there way around TYPO3 – no words needed, only enough coffee :)
You can find the scripts for the ``YAML``-configuration and ``.htaccess`` below.

.. youtube:: yUAAlnPy3NQ
   :width: 100%


Step-by-step instruction
------------

.. rst-class:: bignums-important

1. Install the extension

   | Press the Retrieve/Update button and search for the extension key `nnrestapi`. 
   Then import the extension from the repository.

   **OR**

   Search for the current version in the `TYPO3 Extension Repository (TER) <https://extensions.typo3.org/extension/nnrestapi/>`__.
   Download the t3x or zip version. Upload the file afterwards in the Extension Manager and activate it.

   **OR**

   Install the the extension using **composer** on the command-line:

   .. code-block:: bash

      composer require nng/nnrestapi

2. Make sure the database-tables were created

   In the TYPO3 backend, switch to the "Maintainance" module and click on "Analyze Database Structure".
   Create the database-tables for ``nnrestapi``, if necessary. 
   For more information `read here <https://docs.typo3.org/m/typo3/guide-installation/10.4/en-us/Upgrade/RunTheDatabaseAnalyzer/Index.html>`__.


3. Include the TypoScript Templates on your Root-page

   Make sure, the static typoscript configuration for **"RestApi Configuration (nnrestapi)"** was included on your root-page.
   To do so, follow the standard instructions on `how to include typoscript from extensions <https://docs.typo3.org/m/typo3/reference-typoscript/main/en-us/UsingSetting/Entering.html#include-typoscript-from-extensions>`__.

4. Include the YAML-Configuration

   Search for your site-configuration YAML, which is usually located either under ``typo3conf/sites/{name}/config.yaml``
   or under ``config/sites/{name}/config.yaml``.

   Include these two lines at the end of the configuration.

   .. code-block:: bash

      imports:
        - { resource: "EXT:nnrestapi/Configuration/Yaml/default.yaml" }

   They take care of the basic Routing to the Api – another words:
   That all requests sent to ``/api/...`` find their way to your classes and methods.

5. Modify the .htaccess

   This step *might* not be necessary - it depends a lot on your hosting environment and PHP-settings. 
   In *most* of our installations these two lines *were* necessary – otherwise we had problems with the 
   frontend user-session / authorization.

   Insert these two lines after ``RewriteEngine On``:

   .. code-block:: bash

      RewriteCond %{HTTP:Authorization} ^(.*)
      RewriteRule .* - [e=HTTP_AUTHORIZATION:%1]

6. Get started!

   Go to the RestApi backend module and have a look!

   Then head on to the :ref:`Quickstart section <quickstart>` to write your first own endpoint in less than 5 minutes!
