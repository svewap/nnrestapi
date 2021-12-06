.. include:: ../Includes.txt

.. _response:

============
Creating Responses
============

How to send a response from your TYPO3 Restful Api
---------

Your REST Api endpoint can return almost anything. The ``nnrestapi`` extension will take care  
of converting your return value to a JSON, no matter if you pass a simple array, a Domain Model
or an ``ObjectStorage``.

If no other response header or status code is specified, the nnrestapi will create all headers
for a ``200 OK`` HTTP Response. It will also automatically send the correct CORS and Credential-headers 
like ``Access-Control-Allow-Credentials`` etc. 

Check the ``EXT:nnrestapi/Classes/Utilities/Header.php`` to see all headers generated.

Lets dive into details:
~~~~~~~~~~

.. toctree::
   :glob:
   :maxdepth: 6

   Responses/*

