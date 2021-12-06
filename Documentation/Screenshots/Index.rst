.. include:: ../Includes.txt

.. _screenshots:

====================
Screenshots
====================

Backend module with testbed
-----------------------------

While creating your own RestApi you don't need to use external tools like Postman.
All registered endpoints automatically get listed in the backend module. By clicking
on the compose-icon you can create your custom request in the backend including
Frontend-User authentication and file-uploading.

.. figure:: ../Images/01.gif
   :class: with-shadow
   :alt: nnrestapi Backend Module
   :width: 100%

Automatic documentation
-----------------------------

Use Markdown in your method annotations to automatically create the
documentation for your TYPO3 Restful Api. This saves a lot of time and keeps code and
documentation at one place.

.. figure:: ../Images/03.gif
   :class: with-shadow
   :alt: nnrestapi Backend Module
   :width: 100%

FrontendUser Authentication
-----------------------------

The ``nnrestapi`` extensions ships with a Authentication-layer for logging in frontend-users
and setting Json Web Tokens (JWT). This allows development from localhost-environments which
connect to a external development-server without CORS-problems.

You can test the login / logout from the testbed in the backend module:

.. figure:: ../Images/04.gif
   :class: with-shadow
   :alt: nnrestapi Backend Module
   :width: 100%
