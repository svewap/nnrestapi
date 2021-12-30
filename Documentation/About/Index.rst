.. include:: ../Includes.txt

.. _introduction:

============
About the extension
============

What does this extension do?
----------------

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


But WHY IN HELL in TYPO3?
------------------

Why not Symfony, Laravel, Node, Meteor or any other sophisticated solution?

Well, because TYPO3 does not only have **one of the best architectures** out there – it is the only solution that comes with beautiful
code **AND** a beautiful Content-Management-System like the TYPO3 backend. And on-top it is one of the most secure CMS available.

Many other solutions only offer one of the two: Either you have a great architecture with Routing, File-Abstraction and solid request-handling - 
but the moment you also need to have pages with editable content-elements, things tend to get extremly cumbersome. 

Or you have content-based CMS (Joomla, WordPress) with an architecture that makes the integration of a Restful Api feel as if you were
glueing a shelf to the wall because you're missing the right screws.

With TYPO3 and a Restful Api integrated directly in one and the same system, many things become possible that would require a lot 
of work in other environments and systems: You can have a website, that has "normal" content pages that can be comfortably edited in the
backend. The backend offers everything you've been dreaming of: A nice pagetree, modular content-elements, plugins, referencing of
content - to name a few. Aside of the static content there can also be dynamic content that is stored in "lists" like news-articles, a directory of movies 
or books. And to round it all off, the same installation and website can offer a Restful Service making it possible for external services like 
apps or singlepage application to connected to the website and retrieve the data.

With Version 11 LTS and PHP 8+ TYPO3 has not only gained in speed. It has accelerated to lightspeed.