.. include:: ../Includes.txt

.. _about:

============
About the extension
============

Simple, scaleable, flexible
-------------

The goal of this extension was to develop a **REST Api framework for TYPO3** that was **simple to integrate**, but as scalable and 
:ref:`configurable <configuration>` as possible. Using :ref:`Annotations <annotations>` to accomplish this, seemed 
obvious and intuitive to us (and `others <https://docs.typo3.org/p/sourcebroker/t3api/main/en-us/Index.html>`__).

It should offer simple, comprehensible solutions for the three big challenges during the implementation:
:ref:`user-authentication <authentication>`, :ref:`file-upload <fileupload>` (including automatic FAL conversion) 
and :ref:`localization <localization>`. 

We wanted to offer the possibility to **route requests** to Controller-methods - offering a 
:ref:`standardized scheme <routing_standard>`, but also allowing :ref:`custom route configurations <custom_routing>`, 
that includes parsing of custom request parameter.  

We wanted to offer a **good documentation** with as many :ref:`examples <examples>` as possible. So you (and we) can copy and 
paste - no matter if you are a front- or backend-developer. And no matter if this is the first time you are building 
an Api in TYPO3 - or if you're an expert.

We've invested a lot of time in creating functional and editable examples on **CodePen** and also integrated a 
miniature "Postman" in the :ref:`backend-module <screenshots>` so you can test your endpoints without having to leave the
TYPO3 environment.


Quick Walkthrough
-----------------------------

Overview of the installation and backend features of the extension.

.. youtube:: Za07kam3Odc
   :width: 100%


What does this extension do?
----------------

This extension makes implementing your own **Rest Api** with TYPO3 as a backend simple. 

It takes care of all of the "dirty work" like **parsing the JSON requests**, moving uploaded files to their destination, converting JSON-data to 
``Models`` and ``Models``, ``ObjectStorages``, ``SysFileRefences`` etc. back to a JSON for the Reponse.

It supports all standard **HTTP Request Methods** like ``GET``, ``POST`` - and even can handle ``PUT``, ``DELETE`` and ``PATCH`` including
file-uploads, which is usually a headache when working with PHP and TYPO3.

You can implement a new endpoint with 3 - 5 lines of code. You can limit :ref:`access-rights <access>` and configure 
:ref:`file-upload-paths <configuration_fileuploads>`, :ref:`caching <annotations_cache>` and result-parsing using 
:ref:`Annotations <annotations>`. And you can easily extend your Api with your own :ref:`custom Annotations <annotations_custom>`, 
if you like.

The extension comes equipped with endpoints for **authenticating Frontend-Users** via :ref:`JWT (JSON Web Token) <authentication_jwt>`,
:ref:`HTTP basic auth <authentication_http>` and :ref:`cookies <authentication_cookies>`.

The backend module offers a :ref:`test bed <screenshots>` similar to `Postman <https://www.postman.com/>`__ to compose 
requests without having to use an additional tool. This saves you time and keeps development and testing centralized in your project!

Comments and annotations above the methods of your endpoints are automatically parsed and converted to a beautiful
:ref:`documentation <access_writedocs>` in the backend module. Development and documentation stay centralized!

|

.. admonition:: But WHY THE HELL in TYPO3?

   **Why not Symfony, Laravel, Node, Meteor or any other sophisticated solution?**

   Well, because TYPO3 does not only have **one of the best architectures** out there – it is the only solution that comes with beautiful
   code **AND** a beautiful Content-Management-System like the TYPO3 backend. And on-top, it is one of the most secure CMS available.

   Many other solutions only offer one of the two: Either you have a great architecture with Routing, File-Abstraction and solid request-handling - 
   but the moment you also need to have pages with editable content-elements, things tend to get extremely cumbersome. 

   Or you have content-based CMS (Joomla, WordPress) with an architecture that makes the implementation feel as if you were
   gluing a shelf to the wall because you're missing the right screws.

   With TYPO3 and a Restful Api integrated directly in one and the same system, many things become possible that would require a lot 
   of work in other environments and systems: You can have a website, that has "normal" content pages that can be comfortably edited in the
   backend. 
   
   The backend offers everything you've been dreaming of: A nice page tree, modular content-elements, plugins, referencing of
   content - to name a few. Aside of the static content there can also be dynamic content that is stored in "lists" like news-articles, a directory of movies 
   or books. 
   
   And to round it all off, the same installation and website can offer a Restful Service making it possible for external services like 
   apps or single page application to connected to the website and retrieve the data.

   With Version 11 LTS and PHP 8+ TYPO3 has not only gained in speed. It has accelerated to light speed.


Read on
----------

.. toctree::
   :glob:
   :maxdepth: 1

   About/*