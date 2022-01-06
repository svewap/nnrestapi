.. include:: ../Includes.txt

.. _requests:

============
Routing & Requests
============

The request type makes the difference!
---------

A typical project setup will have of a frontend application that requests something 
and a backend with a **REST Api** that retrieves this data from the database and returns 
it to the frontend application.

Many Single Page Applications (SPA) and Progressive Web Apps (PWA) nowadays are 
programmed in JavaScript and use the JSON format to communicate between the frontend 
and backend. Typically, the frontend app will want to do a number of things: 

- Get data for an **existing entry** from the backend
- **Create** or insert a new entry, e.g. add a new item to the to-do or shopping list
- **Update** an existing entry
- and finally **delete** an item from the database

The idea behind a **RESTful Api** is to define endpoints (URLs) that the frontend application can
communicate with to get the job done. But instead of defining separate URLs for every
operation needed (``/api/get/entry``, ``/api/save/entry``, ``/api/delete/entry``) or passing
an action as request parameter (``?action=save``) it uses HTTP Request types to make clear,
if an entry should be retrieved, inserted, updated or deleted.

Same URL - different actions!
---------

Most of the time you even have the same URL for every operation - but depending on the
type of request and the request-body passed, different operations are executed:

+------------+---------------------+-------------------------------------+--------------------------------------------------+
| Method     | URL                 | Request body / payload              | typical operation                                |
+============+=====================+=====================================+==================================================+
| ``GET``    | ``/api/example/1``  | (none)                              | Get entry with uid [1] from database             |
+------------+---------------------+-------------------------------------+--------------------------------------------------+
| ``PUT``    | ``/api/example/1``  | {"title":"Update!", "text":"nice"}  | Update full entry with uid [1] in database       |
+------------+---------------------+-------------------------------------+--------------------------------------------------+
| ``PATCH``  | ``/api/example/1``  | {"text":"fine"}                     | Update parts of entry with uid [1] in database   |
+------------+---------------------+-------------------------------------+--------------------------------------------------+
| ``DELETE`` | ``/api/example/1``  | (none)                              | Delete entry with uid [1] in database            |
+------------+---------------------+-------------------------------------+--------------------------------------------------+
| ``POST``   | ``/api/example``    | {"title":"New!", "text":"someone"}  | Insert a new entry in database                   |
+------------+---------------------+-------------------------------------+--------------------------------------------------+

.. hint::

   In many cases, the backend will not make a difference between a ``PUT`` and ``PATCH`` request. 
   Both are intended to update existing data. But if you are interested in the details, you
   can find a good explanation `on this page <https://rapidapi.com/blog/put-vs-patch/>`__.

Route a request to your TYPO3 Rest Api
---------

The whole deal is about getting a certain HTTP Request Type "connected" to a Controller and method of your
Api that will then take care of the rest: Retrieving, updating, inserting or deleting data.

There are two basic ways to accomplish this task:

- :ref:`routing_standard`
- :ref:`custom_routing`


Let's dive into details:
~~~~~~~~~~

.. toctree::
   :glob:
   :maxdepth: 2

   Requests/*
