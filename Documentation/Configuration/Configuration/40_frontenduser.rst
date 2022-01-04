.. include:: ../Includes.txt

.. _configuration_feuser:

============
Frontend-user configuration
============

Settings for individual frontend users
-------------------------------

In the tab "RestAPI" of the individual frontend-users you can edit these settings: 

.. figure:: ../../Images/hidden-records.jpg
   :class: with-shadow
   :alt: Frontend User configuration
   :width: 100%

Rest-Api Key
""""""""""""""
.. container:: table-row

   Property
        Rest-Api Key
   Data type
        string
   Description
        This is the API key (password) the user can use when authenticating using 
        :ref:`HTTP basic auth <authentication_http>` to access an endpoint. 

   Default
        not set

Admin Mode
""""""""""""""
.. container:: table-row

   Property
        Admin Mode
   Data type
        boolean
   Description
        Allows a user to access hidden records in the frontend. 
        See :ref:`this section <hiddenrecords>` for more information.

   Default
        FALSE