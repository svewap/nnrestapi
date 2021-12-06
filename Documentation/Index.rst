.. every .rst file should include Includes.txt
.. use correct path!

.. include:: Includes.txt

.. Every manual should have a start label for cross-referencing to
.. start page. Do not remove this!

.. _start:

=============================================================
99° REST API for Typo3 (nnrestapi)
=============================================================

:Version:
   |release|

:Language:
   en

:Authors:
   99°

:Email:
   info@99grad.de

:License:
   This extension documentation is published under the
   `CC BY-NC-SA 4.0 <https://creativecommons.org/licenses/by-nc-sa/4.0/>`__ (Creative Commons)
   license

Restful Api for Typo3
=====================

This extension allows to realise a REST API (Restful Api) in Typo3.
It includes many features:

**Frontend**

* Supports all common request types (GET, POST, DELETE, PUT, PATCH)
* Shipped with endpoint for full frontend-user authentication
* Supports JSON Web Tokens (JWT) for authentication
* Supports CORS for accessing the REST API from cross-origins and while developing in localhost environments

**Backend module**

* Automatic listing of all registered endpoints
* Testbed to send requests with parameters and file-uploads from backend

.. toctree::
   :maxdepth: 6

   Introduction/Index
   Installation/Index
   Screenshots/Index
   Quickstart/Index
   Requests/Index
   Responses/Index
   Annotations/Index
   Access/Index
   FileUploads/Index
   WritingDoc/Index
   KnownProblems/Index
   Changelog/Index
   Sitemap
