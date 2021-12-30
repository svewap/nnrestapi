.. every .rst file should include Includes.txt
.. use correct path!

.. include:: Includes.txt

.. Every manual should have a start label for cross-referencing to
.. start page. Do not remove this!

.. _start:

=============================================================
99° Restful API for Typo3 by 99° (nnrestapi)
=============================================================

:Version:
   |release|

:Language:
   en

:Authors:
   99°, David Bascom

:Email:
   info@99grad.de

:License:
   This extension documentation is published under the
   `CC BY-NC-SA 4.0 <https://creativecommons.org/licenses/by-nc-sa/4.0/>`__ (Creative Commons)
   license

Restful Api for Typo3
=====================

Get started with everything you need to create a Restful API in TYPO3 including all common request types,
frontend-user authentication and file-uploads.

Motivation:
-------------

The goal of this extension was to develop a solution that was **simple to integrate, but as scalable and 
configurable as possible**.

It should offer simple, comprehensible solutions for the three big challenges when developing a Restful Api:
:ref:`user-authentication <authentication>`, :ref:`file-upload <fileupload>` (including automatic FAL conversion) 
and :ref:`localization <localization>`. 

We wanted to offer the possibility to **route requests** to Controller-methods - following a standardized scheme, but
also allowing custom route definitions, including user defined request parameters.  

We wanted to offer a **good documentation** with as many **examples** as possible. So you (and we) can copy and 
paste - no matter if you are a front- or backend-developer. And no matter if this is the first time you are building 
a REST Api - or if you're an expert.

We've invested a lot of time to create working and editable examples on **CodePen** and also integrated a 
miniature "Postman" in the backend-moduel so you can test your endpoints without having to leave the
TYPO3 environment.


Frontend features:
-------------

* Supports all common request types (GET, POST, DELETE, PUT, PATCH)
* Shipped with an endpoint for full frontend-user authentication
* Multiple authentication methods: JSON Web Tokens (JWT), fe-user-Cookies and HTTP-Authorization
* Supports CORS for accessing the REST API from cross-origins and while developing in localhost environments
* Automatic conversion of JSON-data to Models
* Full support for creating, adding and removing FileReferences from the frontend application

Developing features:
-------------

* Highly customizable and configurable
* Automatic and custom routing to endpoints
* Fastest possible integration, setup your REST Api in 5 minutes
* Optional Caching-layer with one line of code
* Many, many examples

Backend module:
-------------

* Automatic listing of all registered endpoints
* Automatic documentation of your endpoints from comments and annotations
* Testbed to send requests with parameters and file-uploads from backend

Limitations and demarcations:
-------------

* We have completely focussed on the JSON-format. We currently see no need to support XML or other formats. But let's discuss!
* We are 

We've sneaked something in:
-------------

This extension depends on `EXT:nnhelpers <https://extensions.typo3.org/extension/nnhelpers>`__ which takes care of 
most of the hardcore conversions: from JSON to Model, Array to FAL, FAL-Uploads and other strenuous things... Sorry
for "sneaking this in" when you install nnrestapi... but it saved us many, many hours of development.


Want to git it?
-----------------------
You can get the latest version from bitbucket.org by using the git command:

.. code-block:: bash

   git clone https://bitbucket.org/99grad-team/nnrestapi/src/master/


Let's get started:
-------------

.. toctree::
   :maxdepth: 6

   Introduction/Index
   About/Index
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
   Kickstarter/Index
   WritingDoc/Index
   Examples/Index
   KnownProblems/Index
   Changelog/Index
   Sitemap
