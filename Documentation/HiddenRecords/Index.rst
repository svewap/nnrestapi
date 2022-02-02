.. include:: ../Includes.txt

.. _hiddenrecords:

============
Get hidden records
============

How to retrieve hidden records
---------

Imagine you are developing a frontend based on JavaScript (VueJS or React) to administrate 
news records. Of course, as an admin, you want to be able to edit records that were not published
yet. 

If you were logged in to the backend, you would simply set the "hidden" flag on the record and
happily edit the news until things look fine. This is possible, because you are logged in to the
backend which allows you to view records that are hidden or have a start- and end-date set.

With a normal TYPO3-extension we would be facing a problem. We are sending all requests to the API from 
a **frontend context**. And frontend means: Hidden is hidden!

Yes, you can.
---------

The good news: Retrieving hidden records in the frontend context is possible with nnrestapi.

**You have two options:**

.. rst-class:: bignums

1. Use the @Api\\IncludeHidden() Annotation

   To make TYPO3 include hidden records, you can add the following annotation to your method.
   TYPO3 will now also return hidden data, including hidden, nested FileReferences or other 
   relations. 

   .. code-block:: php

      @Api\IncludeHidden()

   More information and examples can be found :ref:`on this page <annotations_hidden>`

2. Set the "Admin Mode" for a Frontend User

   If you are using authenticated frontend user you can define access rights to hidden records
   on a per-user base.

   Edit the frontend-user in the backend, switch to the "RestAPI" tab and set the checkbox
   "Admin-Mode: Show hidden records":

   .. figure:: ../Images/hidden-records.jpg
      :class: with-shadow
      :alt: Admin Mode: Show hidden records
      :width: 100%
