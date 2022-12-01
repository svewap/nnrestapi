.. include:: ../Includes.txt

.. _known-problems:

==============
Known Problems
==============

Backend Module
--------------

The backend module does not support Internet Explorer. It would be relatively easy do get it to work.
But we simply don't think anybody should be using Internet Explorer anymore.

Conflicts with EXT:autoloaded
--------------

| There seems to be a conflict with `EXT:autoloader <https://extensions.typo3.org/extension/autoloader>`__ which was reported in `this ticket <https://bitbucket.org/99grad-team/nnrestapi/issues/11/extension-do-not-work-with-ext-autoloader>`__.
| Alexander was nice enough to describe a workaround by moving the Api-Code to a folder that is not scanned by EXT:autoloader:
| 

.. code-block:: php

   [Creation Error] An error occurred while instantiating the annotation @Api\Access declared on method
   
   I moved the api code from Vendor/Ext/Controller/ItemsController.php to Vendor/Ext/Api/Items.php
   This way, EXT:autoloader do not scan it anymore, because it is only looking inside Controller Folder/NS