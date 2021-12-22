.. include:: ../Includes.txt

.. _introduction:

============
Introduction
============

.. _what-it-does:

What does it do?
================

This extension makes implementing your own **TYPO3 Restful Api** simple. 

It takes care of all of the "dirty work" like **parsing the JSON requests**, moving uploaded files to their destination, converting JSON-data to 
``Models`` and ``Models``, ``ObjectStorages``, ``SysFileRefences`` etc. back to a JSON for the Reponse.

It supports the typical **HTTP Request Methods** like ``GET``, ``POST`` - and even can handle ``PUT``, ``DELETE`` and ``PATCH`` including
file-uploads which usally is a headache when working with PHP and TYPO3.

You can implement a new endpoint with 3 - 5 lines of code. You can limit access-rights and configure file-upload-paths, 
caching and result-parsing using **Annotations**. And you can easily extend your Api with your own **custom Annotations**, if you like.

The extension comes equipped with endpoints for **authenticating Frontend-Users** via JWT (JSON Web Token). 

The backend module offers a **testbed** similar to `Postman <https://www.postman.com/>`__ to compose requests without having to
use an additional tool. This saves you time and keeps development and testing centralized in your project!

Comments and annotations above the methods of your endpoints are automatically parsed and converted to a beautiful
**REST Api documentation** in the backend module. Development and documentation stay centralized!
