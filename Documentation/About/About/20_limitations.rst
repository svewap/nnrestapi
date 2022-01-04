.. include:: ../Includes.txt

.. _about_limitations:

============
Limitations
============

Limitations and demarcations:
-------------

*  We have completely focussed on the **JSON-format**. We currently see no need to support XML or other formats. But let's discuss!
*  We are **not strictly following all principles of a RESTful Api**. One of them says a "RESTful API should be stateless". To a certain
   extend this contradicts the login mechanisms of TYPO3 which relies on sessions that are stored on the server. We like the concept
   of TYPO3 Frontend Users and the idea of having sessions. Sessions that you can store data in. So we have implemented them as one
   of the :ref:`ways to authenticate <authentication>`.
*  We are **mixing some things** here, which are strictly separated from each other in other RESTful Apis. One of them is:
   URLs are not strictly mapped to a certain Model, Storage or Entity. This way, nnrestapi can be used very versatile - even
   if your only intention is to map a URL-route to a method like the extension `YAML Routes <https://extensions.typo3.org/extension/routes>`__
   does.   
