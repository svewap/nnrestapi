.. include:: ../Includes.txt

.. _annotations_localize:

============
@Api\\Localize
============

Enable/disable localization (translation) of data received from TYPO3 Rest Api requests
---------

The ``@Api\Localize()`` annotation can be used to force or disable the localization of models and entries retrieved
from the database.

It allows overriding the default settings defined in the TypoScript setup. 
By default localization is disabled. This can be changed by setting the following flag in the TypoScript setup:

.. code-block:: typoscript

    plugin.tx_nnrestapi.settings {
        localization.enabled = 1
    }

The basic syntax of ``@Api\Localize()`` is:

.. code-block:: php
   
   namespace My\Extension\Api;
   
   use Nng\Nnrestapi\Annotations as Api;

   ... 
   
   /**
    * @Api\Localize()
    */

Enabling localization
~~~~~~~~~~~

To **enable localization / translation** even if the TypoScript settings are set to ``enabled = 0`` you can use:

.. code-block:: php
      
   /**
    * @Api\Localize(TRUE)
    */

As the Annotation defaults to ``TRUE`` you can also omit the ``TRUE`` in the Annotation:

.. code-block:: php
      
   /**
    * @Api\Localize()
    */

Disabling localization
~~~~~~~~~~~

To **disable localization / translation** even if the TypoScript settings are set to ``enabled = 1`` you can use:

.. code-block:: php
      
   /**
    * @Api\Localize(FALSE)
    */


Typical use-cases
~~~~~~~~~

Two exemplary cases for enabling / disabling the localization settings:

-   Let's assume, the main focus of your Rest Api is to return a list of news-feed, calendar-events or a list
    of the latest movies on Netflix. You have most of these lists stored in multiple languages in the database
    using the standard TYPO3 ways of localizing data. 

    In this case, it would make sense to globally set ``plugin.tx_nnrestapi.settings.localization.enabled = 1``,
    so that any query to the database will retrieve the data in the requested language.
    
    If you now need to **disable** the localization for certain methods, this can be accomplished by passing 
    ``FALSE`` as argument to the Annotation: ``@Api\Localize(FALSE)``

-   Next, let's imagine you have created a frontend-application which only needs translated data for the labels,
    text information and dialog-texts. The rest of the application's main purpose is reading and updating data-rows
    that don't need localization.

    This would be a typical case for leaving the default setting to "disabled" by setting  
    ``plugin.tx_nnrestapi.settings.localization.enabled = 0``.

    If you now have an endpoint that **does** need translation handling, you can override these settings by
    using ``@Api\Localize(TRUE)`` (or simply ``@Api\Localize()``) above your method.
    