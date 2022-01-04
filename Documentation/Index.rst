.. every .rst file should include Includes.txt
.. use correct path!

.. include:: Includes.txt

.. Every manual should have a start label for cross-referencing to
.. start page. Do not remove this!

.. _start:

=============================================================
RESTful API for Typo3 (nnrestapi by 99°)
=============================================================

Everything you need to create a RESTful API in TYPO3.

.. container:: row m-0 p-0

   .. container:: col-12 col-md-6 pl-0 pr-3 py-3 m-0

      .. container:: card px-0 h-100

         .. rst-class:: card-header h4

            .. rubric:: :ref:`➔ Screenshots <screenshots>`

         .. container:: card-body

            See what you get. Screenshots of the front- and backend.

   .. container:: col-12 col-md-6 pl-0 pr-3 py-3 m-0

      .. container:: card px-0 h-100

         .. rst-class:: card-header h4

            .. rubric:: :ref:`★ Features <introduction>`

         .. container:: card-body

            :ref:`Motivation <introduction>` and overview of the :ref:`main features <about_features>`.

   .. container:: col-12 col-md-6 pl-0 pr-3 py-3 m-0

      .. container:: card px-0 h-100

         .. rst-class:: card-header h4

            .. rubric:: :ref:`⚑ Installation <installation>`

         .. container:: card-body

            How to :ref:`install <installation>` the extension. How to set up your first TYPO3 RESTful Api in 5 minutes
            using this :ref:`quick-start <quickstart>`.

   .. container:: col-12 col-md-6 pl-0 pr-3 py-3 m-0

      .. container:: card px-0 h-100

         .. rst-class:: card-header h4

            .. rubric:: :ref:`⌘ Routing <requests>`

         .. container:: card-body

            Routing requests / URLs to your classes and methods (endpoints).

   .. container:: col-12 col-md-6 pl-0 pr-3 py-3 m-0

      .. container:: card px-0 h-100

         .. rst-class:: card-header h4

            .. rubric:: :ref:`↹ Responses <response>`

         .. container:: card-body

            How to create a response and convert Models, ObjectStorages and FileReferences to JSON
            (by doing nothing ;)

   .. container:: col-12 col-md-6 pl-0 pr-3 py-3 m-0

      .. container:: card px-0 h-100

         .. rst-class:: card-header h4

            .. rubric:: :ref:`@ Annotations <annotations>`

         .. container:: card-body

            Configure almost anything using Annotations, directly at your method.

   .. container:: col-12 col-md-6 pl-0 pr-3 py-3 m-0

      .. container:: card px-0 h-100

         .. rst-class:: card-header h4

            .. rubric:: :ref:`✜ Access <access>`

         .. container:: card-body

            How to only allow certain users to access your Api.

   .. container:: col-12 col-md-6 pl-0 pr-3 py-3 m-0

      .. container:: card px-0 h-100

         .. rst-class:: card-header h4

            .. rubric:: :ref:`⊛ Authentication <authentication>`

         .. container:: card-body

            How to authenticate using :ref:`HTTP Basic Auth <authentication_http>`,
            :ref:`JSON Web Tokens <authentication_jwt>` and :ref:`Cookies <authentication_cookies>`.

   .. container:: col-12 col-md-6 pl-0 pr-3 py-3 m-0

      .. container:: card px-0 h-100

         .. rst-class:: card-header h4

            .. rubric:: :ref:`❖ Uploading Files <fileupload>`

         .. container:: card-body

            How to upload files from the frontend and create FileReferences (FAL) 
            in a single request using multipart/form-data.

   .. container:: col-12 col-md-6 pl-0 pr-3 py-3 m-0

      .. container:: card px-0 h-100

         .. rst-class:: card-header h4

            .. rubric:: :ref:`❉ Localization <localization>`

         .. container:: card-body

            Retrieving translated (localized) data from the TYPO3 Rest Api.

   .. container:: col-12 col-md-6 pl-0 pr-3 py-3 m-0

      .. container:: card px-0 h-100

         .. rst-class:: card-header h4

            .. rubric:: :ref:`✿ Kickstarter <kickstarter>`

         .. container:: card-body

            Create a namespaced Rest Api extension with a single click.

   .. container:: col-12 col-md-6 pl-0 pr-3 py-3 m-0

      .. container:: card px-0 h-100

         .. rst-class:: card-header h4

            .. rubric:: :ref:`❂ Writing documentation <access_writedocs>`

         .. container:: card-body

            How to create a beautiful documentation of your endpoints. 
            Without leaving your code editor.

   .. container:: col-12 col-md-6 pl-0 pr-3 py-3 m-0

      .. container:: card px-0 h-100

         .. rst-class:: card-header h4

            .. rubric:: :ref:`✲ Examples <examples>`

         .. container:: card-body

            Examples for the backend, examples for the frontend. Examples in
            VanillaJS, jQuery, axios. And examples on CodePen.

   .. container:: col-12 col-md-6 pl-0 pr-3 py-3 m-0

      .. container:: card px-0 h-100

         .. rst-class:: card-header h4

            .. rubric:: :ref:`⨳ Configuration <configuration>`

         .. container:: card-body

            Overview of the :ref:`TypoScript setup <configuration_typoscript>` and 
            configuration you can set in :ref:`yaml <configuration_yaml>`


Is the extension free?
-----------------------

**Yes**, :ref:`but ...! <support>`

License
-----------------------

This extension documentation is published under the
`CC BY-NC-SA 4.0 <https://creativecommons.org/licenses/by-nc-sa/4.0/>`__ (Creative Commons)
license


Authors
-----------------------

:Version:
   |release|

:Language:
   en

:Authors:
   `www.99grad.de <https://www.99grad.de>`__, David Bascom

:Email:
   info@99grad.de

.. toctree::
   :hidden:
   :maxdepth: 3

   About/Index
   Introduction/Index
   Installation/Index
   Screenshots/Index
   Quickstart/Index
   Requests/Index
   Responses/Index
   Annotations/Index
   Access/Index
   Authentication/Index
   FileUploads/Index
   Localization/Index
   HiddenRecords/Index
   Kickstarter/Index
   WritingDoc/Index
   Examples/Index
   Configuration/Index
   KnownProblems/Index
   Changelog/Index
   Sitemap