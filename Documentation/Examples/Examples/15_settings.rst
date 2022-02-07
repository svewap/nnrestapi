.. include:: ../Includes.txt

.. _examples_settings:

============
Getting settings
============

How to get configurations and settings
------------

The first thing most Single Page Applications (SPAs) do when they are booting is: Load settings, configurations and
other static data from the server. This could be:

- Static URLs to media-folders
- TypoScript settings that are needed in the frontend 
- A list of countries or languages that should be selectable in the frontend

This example illustrates how you could create an public endpoint that **returns various 
settings and configurations** to the frontend application.

Almost all the values are retrieved using oneliners from the extension `nnhelpers <https://extensions.typo3.org/extension/nnhelpers>`__ 
which is installed as a dependency for EXT:nnrestapi. In other words: They are there and ready to be used.


.. code-block:: php

   <?php   
   namespace My\Extension\Api;

   use Nng\Nnrestapi\Annotations as Api;
   use Nng\Nnrestapi\Api\AbstractApi;

   /**
    * @Api\Endpoint()
    */
   class Settings extends AbstractApi 
   {
      /**
       * @Api\Access("public")
       * 
       * @return array
       */
      public function getIndexAction()
      {
         $settings = [];
        
         // Get baseUrl of current website
         $settings['baseUrl'] = \nn\t3::Environment()->getBaseUrl();

         // Get all languages from site config.yaml (e.g. for a language switcher)
         $languages = \nn\t3::Settings()->getSiteConfig()['languages'] ?? [];
         $settings['languages'] = \nn\t3::Arrays($languages)->key('hreflang')->pluck('title')->toArray();

         // Get full TypoScript settings for plugin.tx_myextension.settings
         $settings['settings'] = \nn\t3::Settings()->get('myextension');

         // Get TypoScript setup from plugin.tx_myextension.settings.somedeep.path
         $settings['paths'] = \nn\t3::Settings()->get('myextension', 'somedeep.path');

         // Get list of countries (EXT:static_info_tables needs to be installed)
         $settings['countries'] = \nn\t3::Environment()->getCountries();

         // Get configuration for an extension from the extension manager
         $settings['extConf'] = \nn\t3::Settings()->getExtConf( 'myextension' );

         // Get an absolute link to a page in the frontend
         $settings['imprintUrl'] = \nn\t3::Page()->getLink( 2, true );

         return $settings;
      }
   }
   
To see the results, send a ``GET`` request to:

.. code-block:: php

   https://www.mysite.com/api/settings


**Example result** of what you get:

.. code-block:: json

   {
      "baseUrl": "https://www.mysite.com/",
      "languages": {
         "de-de": "Deutsch",
         "en-US": "English"
      },
      "paths": {
         "images": "https://www.mysite.com/fileadmin/images/",
         "docs": "https://www.mysite.com/fileadmin/documents/"
      },
      "countries": {
         "de": "Deutschland",
         "it": "Italien",
         "es": "Spanien"
      },
      "extConf": {
         "maxSessionLifetime": 3600
      },
      "imprintUrl": "https://www.mysite.com/imprint"
   }