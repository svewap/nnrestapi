.. include:: ../Includes.txt

.. _response:

============
Creating Responses
============

How to send a response from your endpoint
---------

Your Api can return almost anything. The ``nnrestapi`` extension will take care  
of converting your return value to a JSON, no matter if you pass a simple array, a Domain Model
or an ``ObjectStorage``.

If no other response header or status code is specified, the nnrestapi will create all headers
for a ``200 OK`` HTTP Response. It will also automatically send the correct CORS and Credential-headers 
like ``Access-Control-Allow-Credentials`` etc.

Check :ref:`this section <responses_headers>` to see all default headers generated and find out how
to remove or add custom headers to the response.

Let's dive into details:
~~~~~~~~~~

.. toctree::
   :glob:
   :maxdepth: 2

   Responses/*
